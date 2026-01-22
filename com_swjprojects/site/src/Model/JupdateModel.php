<?php
/**
 * @package       SW JProjects
 * @version       2.6.1-dev
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
 */

namespace Joomla\Component\SWJProjects\Site\Model;

use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\Helpers\StringHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\Exception\ResourceNotFound;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;
use Joomla\Component\SWJProjects\Administrator\Helper\ServerschemeHelper;
use Joomla\Component\SWJProjects\Administrator\Traits\CacheAwareTrait;
use Joomla\Component\SWJProjects\Site\Helper\RouteHelper;
use Joomla\Filesystem\Folder;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

use function defined;
use function implode;
use function is_array;
use function is_numeric;
use function md5;
use function property_exists;
use function str_replace;

defined('_JEXEC') or die;

class JUpdateModel extends BaseDatabaseModel
{
    use CacheAwareTrait;
    /**
     * Update server data.
     *
     * @var  array
     *
     * @since  1.0.0
     */
    protected array $_data = [];

    /**
     * Extension xml.
     *
     * @var  array<string, array{data:string, mimetype:string, charset:string}>
     *
     * @since  1.0.0
     */
    protected array $_extensionData = [];

    /**
     * Collection xml.
     *
     * @var  array<string, array{data:string, mimetype:string, charset:string}>
     *
     * @since  1.0.0
     */
    protected array $_collectionData = [];

    /**
     * Enabled Joomla update server in project.
     *
     * @var  array
     *
     * @since  1.0.0
     */
    protected array $_updateServer = [];

    /**
     * Project id by element.
     *
     * @var  array
     *
     * @since  1.0.0
     */
    protected array $_projectID = [];

    /**
     * Translates languages.
     *
     * @var  array
     *
     * @since  1.0.0
     */
    protected array $translates = [];

    /**
     * Path to files.
     *
     * @var  array
     *
     * @since  1.0.0
     */
    protected array $filesPath = [];

    /**
     * Cache time in hours.
     *
     * @var  int
     *
     * @since  1.0.0
     */
    protected int $cacheTimeout = 0;


    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @throws  Exception
     *
     * @since  1.0.0
     */
    public function __construct($config = [])
    {
        $params = ComponentHelper::getParams('com_swjprojects');
        // Set files paths
        $root            = $params->get('files_folder');
        $this->filesPath = [
            'root'     => $root,
            'versions' => $root . '/versions',
            'cache'    => JPATH_CACHE . '/com_swjprojects',
        ];

        // Set translates
        $this->translates = [
            'current' => Factory::getApplication()->getLanguage()->getTag(),
            'default' => ComponentHelper::getParams('com_languages')->get('site', 'en-GB'),
        ];

        // Set cache timeout
        $this->cacheTimeout = (int)$params->get('jupdate_cachetimeout', 0);

        parent::__construct($config);
    }

    /**
     * Method to get update server data.
     *
     * @param   ?int  $pk  The id of the project.
     *
     * @return array{data:string, mimetype:string, charset:string}
     *
     * @throws  Exception
     *
     * @since  1.0.0
     */
    public function getData(?int $pk = null):array
    {
        $pk = (!empty($pk)) ? $pk : (int)$this->getState('project.id');

        if (empty($pk))
        {
            $pk = $this->getProjectID();
        }

        $hash = ($download_key = $this->getState('download.key')) ? md5($download_key . '_' . $pk) : md5($pk);
        if (!isset($this->_data[$hash]))
        {
            $cache = $this->getCache(['cacheTimeout' => $this->cacheTimeout]);

            if (!$data = $cache->get($hash))
            {
                $data = ($pk > 0) ? $this->getProjectData($pk) : $this->getCollectionData();
                // Save cache
                if (!$this->state->get('debug'))
                {
                    $cache->store($data, $hash);
                }
            }
            $this->_data[$hash] = $data;
        }

        return $this->_data[$hash];
    }

    /**
     * Method to get project id from element.
     *
     * @param   string  $pk  The id of the project.
     *
     * @return  int|Exception  Project id on success, \Exception on failure.
     *
     * @throws  Exception
     *
     * @since  1.0.0
     */
    public function getProjectID($pk = null)
    {
        $pk = (!empty($pk)) ? $pk : $this->getState('project.element');

        if (!empty($this->getState('project.id')))
        {
            return $this->getState('project.id');
        }

        if (empty($pk))
        {
            return -1;
        }

        if (!isset($this->_projectID[$pk]))
        {
            try
            {
                $db    = $this->getDatabase();
                $query = $db->getQuery(true)
                            ->select('p.id')
                            ->from($db->quoteName('#__swjprojects_projects', 'p'))
                            ->leftJoin($db->quoteName('#__swjprojects_categories', 'c') . ' ON c.id = p.catid')
                            ->where($db->quoteName('p.element') . ' = ' . $db->quote($pk));

                // Filter by published state
                $published = $this->getState('filter.published');
                if (is_numeric($published))
                {
                    $query->where($db->quoteName('p.state'). ' = ' . $db->quote((int)$published))
                          ->where($db->quoteName('c.state'). ' = ' . $db->quote((int)$published));
                } elseif (is_array($published))
                {
                    $published = ArrayHelper::toInteger($published);
                    $published = implode(',', $published);

                    $query->where($db->quoteName('p.state').' IN (' . $published . ')')
                          ->where($db->quoteName('c.state').' IN (' . $published . ')');
                }

                $data = $db->setQuery($query)->loadResult();

                if (empty($data))
                {
                    throw new ResourceNotFound(Text::_('COM_SWJPROJECTS_ERROR_PROJECT_NOT_FOUND'), 404);
                }

                $this->_projectID[$pk] = $data;
            }
            catch (Exception $e)
            {
                throw new Exception(Text::_($e->getMessage()), $e->getCode());
            }
        }

        return $this->_projectID[$pk];
    }

    /**
     * Method to get extension data.
     *
     * @param   ?int  $pk  The id of the project.
     *
     * @return array{data:string, mimetype:string, charset:string}
     *
     * @throws  Exception
     *
     * @since  1.0.0
     */
    public function getProjectData(?int $pk = null):array
    {
        $pk = (!empty($pk)) ? $pk : (int)$this->getState('project.id');

        if (empty($pk))
        {
            $pk = $this->getProjectID();
        }

        if (!$this->checkUpdateServer($pk))
        {
            throw new ResourceNotFound(Text::_('COM_SWJPROJECTS_ERROR_PROJECT_NOT_FOUND'), 404);
        }

        $hash = ($download_key = $this->getState('download.key')) ? md5($download_key . '_' . $pk) : md5($pk);
        if (!isset($this->_extensionData[$hash]))
        {
            try
            {
                $db    = $this->getDatabase();
                $query = $db->getQuery(true)
                            ->select(['v.*'])
                            ->from($db->quoteName('#__swjprojects_versions', 'v'))
                            ->where($db->quoteName('v.project_id').' = ' . $db->quote((int)$pk));

                // Join over the projects
                $query->select([
                    $db->quoteName('p.id','project_id'),
                    $db->quoteName('p.catid','catid'),
                    $db->quoteName('p.alias','project_alias'),
                    $db->quoteName('p.element','project_element'),
                    $db->quoteName('p.joomla','project_joomla'),
                ])
                      ->leftJoin($db->quoteName('#__swjprojects_projects', 'p'),$db->quoteName('p.id').' = '.$db->quoteName('v.project_id'));

                // Join over the categories
                $query->select(['c.id as category_id', 'c.alias as category_alias'])
                      ->leftJoin($db->quoteName('#__swjprojects_categories', 'c') . ' ON c.id = p.catid');

                // Join over current translates
                $current = $this->translates['current'];
                $query->select(['t_p.title as project_title', 't_p.introtext as project_introtext'])
                      ->leftJoin(
                          $db->quoteName('#__swjprojects_translate_projects', 't_p')
                          . ' ON t_p.id = p.id AND ' . $db->quoteName('t_p.language') . ' = ' . $db->quote($current)
                      );

                // Join over default translates
                $default = $this->translates['default'];
                if ($current != $default)
                {
                    $query->select(
                        ['td_p.title as default_project_title', 'td_p.introtext as default_project_introtext']
                    )
                          ->leftJoin(
                              $db->quoteName('#__swjprojects_translate_projects', 'td_p')
                              . ' ON td_p.id = p.id AND ' . $db->quoteName('td_p.language') . ' = ' . $db->quote(
                                  $default
                              )
                          );
                }

                // Filter by published state
                $published = $this->getState('filter.published');
                if (is_numeric($published))
                {
                    $query->where('v.state = ' . (int)$published)
                          ->where('p.state = ' . (int)$published)
                          ->where('c.state = ' . (int)$published);
                } elseif (is_array($published))
                {
                    $published = ArrayHelper::toInteger($published);
                    $published = implode(',', $published);

                    $query->where('v.state IN (' . $published . ')')
                          ->where('p.state IN (' . $published . ')')
                          ->where('c.state IN (' . $published . ')');
                }

                // Add the list ordering clause
                $query->order($db->escape('major') . ' ' . $db->escape('desc'))
                      ->order($db->escape('minor') . ' ' . $db->escape('desc'))
                      ->order($db->escape('patch') . ' ' . $db->escape('desc'))
                      ->order($db->escape('hotfix') . ' ' . $db->escape('desc'))
                      ->order($db->escape('stability') . ' ' . $db->escape('desc'))
                      ->order($db->escape('stage') . ' ' . $db->escape('desc'));

                $db->setQuery($query);

                $items      = $db->loadObjectList();
                $files_root = $this->filesPath['versions'];

                foreach ($items as $item)
                {
                    // Set default translates data
                    if ($this->translates['current'] != $this->translates['default'])
                    {
                        $item->project_title = (empty($item->project_title)) ? $item->default_project_title
                            : $item->project_title;

                        $item->project_introtext = (empty($item->project_introtext)) ? $item->default_project_introtext
                            : $item->project_introtext;
                    }

                    // Set link
                    $item->slug     = $item->id . ':' . $item->alias;
                    $item->pslug    = $item->project_id . ':' . $item->project_alias;
                    $item->cslug    = $item->category_id . ':' . $item->category_alias;
                    $item->link     = Route::_(RouteHelper::getVersionRoute($item->slug, $item->pslug, $item->cslug));
                    $item->download = Route::_(
                        RouteHelper::getDownloadRoute(
                            $item->id,
                            null,
                            $item->project_element,
                            $download_key
                        )
                    );

                    // Set version & name
                    $item->version = $item->major . '.' . $item->minor . '.' . $item->patch;
                    if (property_exists($item, 'hotfix') && !empty($item->hotfix))
                    {
                        $item->version .= '.' . $item->hotfix;
                    }
                    $item->name = $item->project_title . ' ' . $item->version;
                    if ($item->tag !== 'stable')
                    {
                        $item->version .= '-' . $item->tag;
                        $item->name    .= ' ' . Text::_('COM_SWJPROJECTS_VERSION_TAG_' . $item->tag);

                        if ($item->tag !== 'dev' && !empty($item->stage))
                        {
                            $item->version .= $item->stage;
                            $item->name    .= ' ' . $item->stage;
                        }
                    }

                    // Set description
                    $item->description = StringHelper::truncate($item->project_introtext, 150, false, false);

                    // Set joomla
                    $item->project_joomla = new Registry($item->project_joomla);

                    // Set type
                    $item->type = $item->project_joomla->get('type', 'file');

                    // Set folder
                    $item->folder = $item->project_joomla->get('folder', '');

                    // Set element
                    $item->element = $item->project_joomla->get('element', $item->project_element);
                    if ($item->type == 'plugin')
                    {
                        $item->element = str_replace([$item->folder . '_', 'plg_'], '', $item->element);
                    }
                    if ($item->type == 'template')
                    {
                        $item->element = str_replace(['tmpl_', 'tpl_', 'tmp_'], '', $item->element);
                    }

                    // Set client
                    $client = (int)$item->project_joomla->get('client_id', 0);
                    if ($client === 0)
                    {
                        $item->client = 'site';
                    } elseif ($client === 1)
                    {
                        $item->client = 'administrator';
                    }
                    // Set files format
                    $item->files = Folder::files($files_root . '/' . $item->id, 'download', false);
                    // Set file
                    $item->file = (!empty($item->files)) ? $item->files[0] : false;

                }

                $scheme_config = [
                    'filesPath'               => $this->filesPath,
                    'translates'              => $this->translates,
                    'cacheTimeout'            => $this->cacheTimeout,
                ];

                $scheme = ServerschemeHelper::getServerScheme(ServerschemeHelper::getServerSchemaNameForProject($pk), $scheme_config);
                $updates_data = [
                    'data'     => $scheme->setScheme('updates')->renderOutput($items),
                    'mimetype' => $scheme->getMimeType(),
                    'charset'  => $scheme->getCharset(),
                ];

                $this->_extensionData[$hash] = $updates_data;
            }
            catch (Exception $e)
            {
                throw new Exception(Text::_($e->getMessage()), $e->getCode());
            }
        }

        return $this->_extensionData[$hash];
    }

    /**
     * Check if project enable update server.
     *
     * @param   ?int  $pk  The id of the project.
     *
     * @return  bool  True if project enable update server, \Exception on failure.
     *
     * @throws  Exception
     *
     * @since  1.0.0
     */
    public function checkUpdateServer(?int $pk = null):bool
    {
        $pk = (!empty($pk)) ? $pk : (int)$this->getState('project.id');

        if (empty($pk))
        {
            $pk = $this->getProjectID();
        }

        if (empty($pk) || $pk < 0)
        {
            throw new ResourceNotFound(Text::_('COM_SWJPROJECTS_ERROR_PROJECT_NOT_FOUND'), 404);
        }

        if (!isset($this->_updateServer[$pk]))
        {
            try
            {
                $db    = $this->getDatabase();
                $query = $db->getQuery(true)
                            ->select('p.id')
                            ->from($db->quoteName('#__swjprojects_projects', 'p'))
                            ->leftJoin($db->quoteName('#__swjprojects_categories', 'c'),$db->quoteName('c.id').' = '.$db->quoteName('p.catid'))
                            ->where($db->quoteName('p.id').' = ' . $db->quote((int) $pk))
                            ->where($db->quoteName('p.update_server') . ' = ' . $db->quote(1));
//                            ->where($db->quoteName('p.joomla') . ' LIKE' . $db->quote('%"update_server":"1"%'));

                // Filter by published state
                $published = $this->getState('filter.published');
                if (is_numeric($published))
                {
                    $query->where($db->quoteName('p.state').' = ' . (int)$published)
                          ->where($db->quoteName('c.state').' = ' . (int)$published);
                } elseif (is_array($published))
                {
                    $published = ArrayHelper::toInteger($published);
                    $published = implode(',', $published);

                    $query->where($db->quoteName('p.state').' IN (' . $published . ')')
                          ->where($db->quoteName('c.state').' IN (' . $published . ')');
                }

                $data = $db->setQuery($query)->loadResult();

                if (empty($data))
                {
                    throw new ResourceNotFound(Text::_('COM_SWJPROJECTS_ERROR_PROJECT_NOT_FOUND'), 404);
                }

                $this->_updateServer[$pk] = true;
            }
            catch (Exception $e)
            {
                if ($e->getCode() == 404) {
                    throw new ResourceNotFound(Text::_($e->getMessage()), $e->getCode());
                } else {
                    throw new Exception(Text::_($e->getMessage()), $e->getCode());
                }
            }
        }

        return (bool) ($this->_updateServer[$pk] ?? false);
    }

    /**
     * Method to get collection data.
     *
     * @return  array  Array of projects collection \SimpleXMLElement on success, \Exception on failure.
     *
     * @throws  Exception
     *
     * @since  1.0.0
     */
    public function getCollectionData()
    {
        $hash = ($download_key = $this->getState('download.key')) ? md5($download_key) : md5('0');
        if (!isset($this->_collectionData[$hash]))
        {
            try
            {
                $db    = $this->getDatabase();
                $query = $db->createQuery()
                            ->select('p.*')
                            ->from($db->quoteName('#__swjprojects_projects', 'p'))
                            ->leftJoin($db->quoteName('#__swjprojects_categories', 'c') . ' ON c.id = p.catid')
                            ->where($db->quoteName('p.update_server') . ' = ' . $db->quote(1));
//                            ->where($db->quoteName('p.joomla') . ' LIKE' . $db->quote('%"update_server":"1"%'));

                // Join over current translates
                $current = $this->translates['current'];
                $query->select(['t_p.title as title'])
                      ->leftJoin(
                          $db->quoteName('#__swjprojects_translate_projects', 't_p')
                          . ' ON t_p.id = p.id AND ' . $db->quoteName('t_p.language') . ' = ' . $db->quote($current)
                      );

                // Join over default translates
                $default = $this->translates['default'];
                if ($current != $default)
                {
                    $query->select(['td_p.title as default_title'])
                          ->leftJoin(
                              $db->quoteName('#__swjprojects_translate_projects', 'td_p')
                              . ' ON td_p.id = p.id AND ' . $db->quoteName('td_p.language') . ' = ' . $db->quote(
                                  $default
                              )
                          );
                }

                // Join over versions for last version
                $subQuery = $db->createQuery()
                               ->select(
                                   ['CASE WHEN lv.hotfix != 0 THEN CONCAT(lv.major, ".", lv.minor, ".", lv.patch,".", lv.hotfix) ELSE CONCAT(lv.major, ".", lv.minor, ".", lv.patch) END']
                               )
                               ->from($db->quoteName('#__swjprojects_versions', 'lv'))
                               ->where('lv.project_id = p.id')
                               ->where('lv.state = 1')
                               ->where($db->quoteName('lv.tag') . ' = ' . $db->quote('stable'))
                               ->order($db->escape('lv.major') . ' ' . $db->escape('desc'))
                               ->order($db->escape('lv.minor') . ' ' . $db->escape('desc'))
                               ->order($db->escape('lv.patch') . ' ' . $db->escape('desc'))
                               ->order($db->escape('lv.hotfix') . ' ' . $db->escape('desc'))
                               ->setLimit(1);
                $query->select('(' . $subQuery->__toString() . ') as version');

                // Filter by published state
                $published = $this->getState('filter.published');
                if (is_numeric($published))
                {
                    $query->where('p.state = ' . (int)$published)
                          ->where('c.state = ' . (int)$published);
                } elseif (is_array($published))
                {
                    $published = ArrayHelper::toInteger($published);
                    $published = implode(',', $published);

                    $query->where('p.state IN (' . $published . ')')
                          ->where('c.state IN (' . $published . ')');
                }

                // Add the list ordering clause
                $query->order($db->escape('p.ordering') . ' ' . $db->escape('asc'));

                $items = $db->setQuery($query)->loadObjectList() ?? [];
                if(!empty($items)) {
                    foreach ($items as &$item)
                    {
                        // Set default translates data
                        if ($this->translates['current'] != $this->translates['default'])
                        {
                            $item->title = (empty($item->title)) ? $item->default_title : $item->title;
                        }

                        // Set joomla
                        $item->joomla = new Registry($item->joomla);

                        // Set type
                        $item->type = $item->joomla->get('type', 'file');

                        // Set folder only for plugins
                        if ($item->type == 'plugin')
                        {
                            $item->folder = $item->joomla->get('folder', '');
                        }

                        // Set element
                        $item->element = $item->joomla->get('element', $item->element);
                        if ($item->type == 'plugin')
                        {
                            $item->element = str_replace([$item->folder . '_', 'plg_'], '', $item->element);
                        }
                        if ($item->type == 'template')
                        {
                            $item->element = str_replace(['tmpl_', 'tpl_', 'tmp_'], '', $item->element);
                        }

                        // Set client
                        $client = (int)$item->joomla->get('client_id', 0);
                        if ($client === 0)
                        {
                            $item->client = 'site';
                        } elseif ($client === 1)
                        {
                            $item->client = 'administrator';
                        }

                        // Set link
                        $item->link = Route::_(RouteHelper::getJUpdateRoute($item->id, null, $download_key));
                    }
                }

                $scheme_config = [
                    'filesPath'               => $this->filesPath,
                    'translates'              => $this->translates,
                    'cacheTimeout'            => $this->cacheTimeout,
                ];
                $params = ComponentHelper::getParams('com_swjprojects');
                $scheme = ServerschemeHelper::getServerScheme($params->get('server_scheme', 'joomla'), $scheme_config);

                $updates_data = [
                    'data'     => $scheme->setScheme('collection')->renderOutput($items),
                    'mimetype' => $scheme->getMimeType(),
                    'charset'  => $scheme->getCharset(),
                ];

                $this->_collectionData[$hash] = $updates_data;
            }
            catch (Exception $e)
            {
                throw new Exception(Text::_($e->getMessage()).' '.$e->getFile().' : in line '.$e->getLine(), $e->getCode());
            }
        }

        return $this->_collectionData[$hash];
    }

    /**
     * Method to autopopulate the model state.
     *
     * @throws  Exception
     *
     * @since  1.0.0
     */
    protected function populateState()
    {
        $app = Factory::getApplication('site');

        // Set request states
        $this->setState('project.id', $app->getInput()->getInt('project_id', 0));
        $this->setState('project.element', $app->getInput()->getString('element', ''));
        $this->setState('download.key', $app->getInput()->getCmd('download_key', ''));

        // Merge global and menu item params into new object
        $params     = $app->getParams();
        $menuParams = new Registry();
        $menu       = $app->getMenu()->getActive();
        if ($menu)
        {
            $menuParams->loadString($menu->getParams());
        }
        $mergedParams = clone $menuParams;
        $mergedParams->merge($params);

        // Set params state
        $this->setState('params', $mergedParams);

        // Set published && debug state
        if ($app->getInput()->getInt('debug', 0))
        {
            $this->setState('filter.published', [0, 1]);
            $this->setState('debug', 1);
        } else
        {
            $this->setState('filter.published', 1);
        }
    }
}

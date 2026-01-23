<?php
/**
 * @package       SW JProjects
 * @version       2.6.1
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
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\Exception\ResourceNotFound;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Component\SWJProjects\Administrator\Helper\ServerschemeHelper;
use Joomla\Component\SWJProjects\Administrator\Helper\TranslationHelper;
use Joomla\Component\SWJProjects\Administrator\Traits\CacheAwareTrait;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use SimpleXMLElement;

use function defined;
use function implode;
use function is_array;
use function is_numeric;
use function md5;
use function property_exists;
use function str_replace;

defined('_JEXEC') or die;

class JchangelogModel extends BaseDatabaseModel
{
	use CacheAwareTrait;

	/**
	 * Update server xml.
	 *
	 * @var  string
	 *
	 * @since  1.0.0
	 */
	protected ?array $_data = [];

	/**
	 * Extension xml.
	 *
	 * @var  array
	 *
	 * @since  1.0.0
	 */
	protected array $_extensionData = [];

	/**
	 * Project id by element.
	 *
	 * @var  int
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
	protected $translates = null;

	/**
	 * Path to files.
	 *
	 * @var  array
	 *
	 * @since  1.0.0
	 */
	protected $filesPath = null;

	/**
	 * Cache time in hours.
	 *
	 * @var  int
	 *
	 * @since  1.0.0
	 */
	protected ?int $cacheTimeout = 0;

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
			'versions' => $root . DIRECTORY_SEPARATOR. 'versions',
			'cache'    => JPATH_CACHE . DIRECTORY_SEPARATOR. 'com_swjprojects'
		];

		// Set cache timeout
		$this->cacheTimeout = (int) $params->get('jupdate_cachetimeout', 0);
		parent::__construct($config);
	}

	/**
	 * Method to get changelog update server xml.
	 *
	 * @param   int  $pk  The id of the project.
	 *
	 * @return  string|Exception  changelog servers xml string on success, \Exception on failure.
	 *
	 * @throws  Exception
	 *
	 * @since  1.0.0
	 */
	public function getData($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('project.id');

		if (empty($pk))
		{
			$pk = $this->getProjectID();
		}


		$hash = md5('changelog' . $pk);

		if (!isset($this->_data[$hash]))
		{
			$cache = $this->getCache(['cacheTimeout' => $this->cacheTimeout]);

			if (!$data = $cache->get($hash))
			{

				$data = $this->getExtensionData($pk);

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
				$query = $db->createQuery()
					->select('p.id')
					->from($db->quoteName('#__swjprojects_projects', 'p'))
					->leftJoin($db->quoteName('#__swjprojects_categories', 'c'), $db->quoteName('c.id') . ' = ' . $db->quoteName('p.catid'))
					->where($db->quoteName('p.element') . ' = ' . $db->quote($pk));

				// Filter by published state
				$published = $this->getState('filter.published');
				if (is_numeric($published))
				{
					$query->where('p.state = ' . (int) $published)
						->where('c.state = ' . (int) $published);
				}
				elseif (is_array($published))
				{
					$published = ArrayHelper::toInteger($published);
					$published = implode(',', $published);

					$query->where('p.state IN (' . $published . ')')
						->where('c.state IN (' . $published . ')');
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
	 * Method to get extension xml.
	 *
	 * @param   ?int  $pk  The id of the project.
	 *
     * @return array{data:string, mimetype:string, charset:string}
     *
	 * @throws  Exception
	 *
	 * @since  1.0.0
	 *
	 * @see    https://docs.joomla.org/Deploying_an_Update_Server
	 * @see    https://manual.joomla.org/docs/building-extensions/modules/module-development-tutorial/step11_update_server/#update-server-files
	 */
	public function getExtensionData(?int $pk = null):array
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('project.id');

		if (empty($pk))
		{
			$pk = $this->getProjectID();
		}

		$hash = md5($pk);

		if (!isset($this->_extensionData[$hash]))
		{
			try
			{
				$component_params = ComponentHelper::getParams('com_swjprojects');
				// Join over current translates
				$request_lang = $component_params->get('changelogurl_language');
                if(empty($request_lang)) {
                    $request_lang = TranslationHelper::getDefault();
                }

				$db    = $this->getDatabase();
				$query = $db->createQuery()
					->select([
						'v.id',
						'v.major',
						'v.minor',
						'v.patch',
						'v.hotfix',
						'v.tag',
						'v.stage',
						'v_t.changelog',
						'v_t.language',
						'p.joomla as project_joomla',
                        'p.update_server',
					])
					->from($db->quoteName('#__swjprojects_versions', 'v'))
					->where($db->quoteName('v.project_id').' = ' . $db->quote((int) $pk))
					->where($db->quoteName('p.update_server').' = ' . $db->quote(1))
					->leftJoin($db->quoteName('#__swjprojects_translate_versions', 'v_t'), $db->quoteName('v.id').' ='.$db->quoteName('v_t.id'))
					->leftJoin($db->quoteName('#__swjprojects_translate_versions'), $db->quoteName('v_t.language').' = '.$db->quote($request_lang))
					->leftJoin($db->quoteName('#__swjprojects_projects', 'p'), $db->quoteName('p.id').' = ' . $db->quoteName('v.project_id'));

				// Filter by published state
				$published = $this->getState('filter.published');
				if (is_numeric($published))
				{
					$query->where('v.state = ' . (int) $published);
				}
				elseif (is_array($published))
				{
					$published = ArrayHelper::toInteger($published);
					$published = implode(',', $published);

					$query->where('v.state IN (' . $published . ')');
				}

				// Add the list ordering clause
				$query->order($db->escape('major') . ' ' . $db->escape('desc'))
					->order($db->escape('minor') . ' ' . $db->escape('desc'))
					->order($db->escape('patch') . ' ' . $db->escape('desc'))
					->order($db->escape('hotfix') . ' ' . $db->escape('desc'));

				$db->setQuery($query);
				$items = $db->loadObjectList();

                if (empty($items)) {
                    throw new ResourceNotFound(Text::_('COM_SWJPROJECTS_ERROR_PROJECT_NOT_FOUND'), 404);
                }

				foreach ($items as $item)
				{
					// Set version & name
					$item->version = $item->major . '.' . $item->minor . '.' . $item->patch;
					if (property_exists($item, 'hotfix') && !empty($item->hotfix))
					{
						$item->version .= '.' . $item->hotfix;
					}
                    if ($item->tag !== 'stable')
                    {
                        $item->version .= '-' . $item->tag;
                        if ($item->tag !== 'dev' && !empty($item->stage))
                        {
                            $item->version .= $item->stage;
                        }
                    }
					// Set joomla
					$item->project_joomla = new Registry($item->project_joomla);

					// Set type
					$item->type = $item->project_joomla->get('type', 'file');

					// Set element
					$item->element = $item->project_joomla->get('element');

					if ($item->type == 'plugin')
					{
						$item->element = str_replace(array($item->project_joomla->get('folder') . '_', 'plg_'), '', $item->element);
					}
					if ($item->type == 'template')
					{
						$item->element = str_replace(array('tmpl_', 'tpl_', 'tmp_'), '', $item->element);
					}

					// set changelogs
					$item->changelog = new Registry($item->changelog);

				}

				$scheme_config = [
					'filesPath'    => $this->filesPath,
					'translates'   => $this->translates,
					'cacheTimeout' => $this->cacheTimeout,
				];

				$scheme = ServerschemeHelper::getServerScheme(ServerschemeHelper::getServerSchemaNameForProject($pk), $scheme_config);

				$changelogs_data = [
					'data'     => $scheme->setScheme('changelogs')->renderOutput($items),
					'mimetype' => $scheme->getMimeType(),
					'charset'  => $scheme->getCharset(),
				];

				$this->_extensionData[$hash] = $changelogs_data;
			}
			catch (Exception $e)
			{
				throw new Exception(Text::_($e->getMessage()), $e->getCode());
			}
		}

		return $this->_extensionData[$hash];
	}

	/**
	 * Method to auto-populate the model state.
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
		$this->setState('project.element', $app->getInput()->get('element', ''));

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
		}
		else
		{
			$this->setState('filter.published', 1);
		}
	}
}

<?php
/*
 * @package    SW JProjects Component
 * @version    1.6.4
 * @author Septdir Workshop, <https://septdir.com>, Sergey Tolkachyov <https://web-tolk.ru>
 * @сopyright (c) 2018 - April 2023 Septdir Workshop, Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link https://septdir.com, https://web-tolk.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

class SWJProjectsModelJUpdate extends BaseDatabaseModel
{
	/**
	 * Update server xml.
	 *
	 * @var  string
	 *
	 * @since  1.0.0
	 */
	protected $_xml = null;

	/**
	 * Update server xml cache.
	 *
	 * @var  string
	 *
	 * @since  1.0.0
	 */
	protected $_xmlCache = null;

	/**
	 * Extension xml.
	 *
	 * @var  SimpleXMLElement
	 *
	 * @since  1.0.0
	 */
	protected $_extensionXML = null;

	/**
	 * Collection xml.
	 *
	 * @var  SimpleXMLElement
	 *
	 * @since  1.0.0
	 */
	protected $_collectionXML = null;

	/**
	 * Enabled Joomla update server in project.
	 *
	 * @var  bool
	 *
	 * @since  1.0.0
	 */
	protected $_updateServer = null;

	/**
	 * Project id by element.
	 *
	 * @var  int
	 *
	 * @since  1.0.0
	 */
	protected $_projectID = null;

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
	 * Cache time im hours.
	 *
	 * @var  int
	 *
	 * @since  1.0.0
	 */
	protected $cacheTimeout = null;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @throws  Exception
	 *
	 * @since  1.0.0
	 */
	public function __construct($config = array())
	{
		$params = ComponentHelper::getParams('com_swjprojects');

		// Set files paths
		$root            = $params->get('files_folder');
		$this->filesPath = array(
			'root'     => $root,
			'versions' => $root . '/versions',
			'cache'    => JPATH_CACHE . '/com_swjprojects'
		);

		// Set translates
		$this->translates = array(
			'current' => Factory::getLanguage()->getTag(),
			'default' => ComponentHelper::getParams('com_languages')->get('site', 'en-GB'),
		);

		// Set cache timeout
		$this->cacheTimeout = $params->get('jupdate_cachetimeout', 0) . ' hour';

		parent::__construct($config);
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
		$this->setState('project.id', $app->input->getInt('project_id', 0));
		$this->setState('project.element', $app->input->get('element', ''));
		$this->setState('download.key', $app->input->getCmd('download_key', ''));

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
		if ($app->input->getInt('debug', 0))
		{
			$this->setState('filter.published', array(0, 1));
			$this->setState('debug', 1);
		}
		else
		{
			$this->setState('filter.published', 1);
		}
	}

	/**
	 * Method to get joomla update server xml.
	 *
	 * @param   integer  $pk  The id of the project.
	 *
	 * @throws  Exception
	 *
	 * @return  string|Exception  Update servers xml string on success, exception on failure.
	 *
	 * @since  1.0.0
	 */
	public function getXml($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('project.id');

		if (empty($pk))
		{
			$pk = $this->getProjectID();
		}

		if ($this->_xml === null)
		{
			$this->_xml = array();
		}

		$hash = ($download_key = $this->getState('download.key')) ? md5($download_key . '_' . $pk) : md5($pk);
		if (!isset($this->_xml[$hash]))
		{
			if (!$context = $this->getXMLCache($pk))
			{
				$xml     = ($pk > 0) ? $this->getExtensionXML($pk) : $this->getCollectionXML();
				$context = $xml->asXML();

				// Save cache
				if (!$this->state->get('debug'))
				{
					$path = $this->filesPath['cache'] . '/jupdate_' . $hash . '.xml';

					File::append($path, $context);
				}
			}
			$this->_xml[$pk] = $context;
		}

		return $this->_xml[$pk];
	}

	/**
	 * Method to get joomla update server xml cache.
	 *
	 * @param   integer  $pk  The id of the project.
	 *
	 * @throws  Exception
	 *
	 * @return  string|boolean  Cached xml string on success, false on failure.
	 *
	 * @since  1.0.0
	 */
	public function getXMLCache($pk = null)
	{
		if ($this->state->get('debug'))
		{
			return false;
		}

		$pk = (!empty($pk)) ? $pk : (int) $this->getState('project.id');

		if (empty($pk))
		{
			$pk = $this->getProjectID();
		}

		if ($this->_xmlCache === null)
		{
			$this->_xmlCache = array();
		}

		$hash = ($download_key = $this->getState('download.key')) ? md5($download_key . '_' . $pk) : md5($pk);
		if (!isset($this->_xmlCache[$hash]))
		{
			$cache = false;
			$path  = $this->filesPath['cache'] . '/jupdate_' . $hash . '.xml';
			if (File::exists($path))
			{
				$clearTime = Factory::getDate(' - ' . $this->cacheTimeout)->toUnix();
				$fileTime  = stat($path)['mtime'];
				if ($clearTime >= $fileTime)
				{
					File::delete($path);
				}
				else
				{
					$cache = file_get_contents($path);
				}
			}

			$this->_xmlCache[$hash] = $cache;
		}

		return $this->_xmlCache[$hash];
	}

	/**
	 * Method to get extension xml.
	 *
	 * @param   integer  $pk  The id of the project.
	 *
	 * @throws  Exception
	 *
	 * @return  boolean|Exception  True if project enable joomla update server, exception on failure.
	 *
	 * @since  1.0.0
	 */
	public function checkUpdateServer($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('project.id');

		if (empty($pk))
		{
			$pk = $this->getProjectID();
		}

		if (empty($pk))
		{
			throw new Exception(Text::_('COM_SWJPROJECTS_ERROR_PROJECT_NOT_FOUND'), 404);
		}

		if ($pk < 0)
		{
			return $this->getCollectionXML();
		}

		if ($this->_updateServer === null)
		{
			$this->_updateServer = array();
		}

		if (!isset($this->_updateServer[$pk]))
		{
			try
			{
				$db    = $this->getDbo();
				$query = $db->getQuery(true)
					->select('p.id')
					->from($db->quoteName('#__swjprojects_projects', 'p'))
					->leftJoin($db->quoteName('#__swjprojects_categories', 'c') . ' ON c.id = p.catid')
					->where('p.id =' . (int) $pk)
					->where($db->quoteName('p.joomla') . ' LIKE' . $db->quote('%"update_server":"1"%'));

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
					throw new Exception(Text::_('COM_SWJPROJECTS_ERROR_PROJECT_NOT_FOUND'), 404);
				}

				$this->_updateServer[$pk] = true;
			}
			catch (Exception $e)
			{
				throw new Exception(Text::_($e->getMessage()), $e->getCode());
			}
		}

		return $this->_updateServer[$pk];
	}

	/**
	 * Method to get extension xml.
	 *
	 * @param   integer  $pk  The id of the project.
	 *
	 * @throws  Exception
	 *
	 * @return  SimpleXMLElement|Exception  Project updates SimpleXMLElement on success, exception on failure.
	 *
	 * @since  1.0.0
	 */
	public function getExtensionXML($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('project.id');

		if (empty($pk))
		{
			$pk = $this->getProjectID();
		}

		if (!$this->checkUpdateServer($pk))
		{
			throw new Exception(Text::_('COM_SWJPROJECTS_ERROR_PROJECT_NOT_FOUND'), 404);
		}

		if ($this->_extensionXML === null)
		{
			$this->_extensionXML = array();
		}

		$hash = ($download_key = $this->getState('download.key')) ? md5($download_key . '_' . $pk) : md5($pk);
		if (!isset($this->_extensionXML[$hash]))
		{
			try
			{
				$db    = $this->getDbo();
				$query = $db->getQuery(true)
					->select(array('v.*'))
					->from($db->quoteName('#__swjprojects_versions', 'v'))
					->where('v.project_id = ' . (int) $pk);

				// Join over the projects
				$query->select(array('p.id as project_id', 'p.alias as project_alias', 'p.element as project_element',
					'p.joomla as project_joomla'))
					->leftJoin($db->quoteName('#__swjprojects_projects', 'p') . ' ON p.id = v.project_id');

				// Join over the categories
				$query->select(array('c.id as category_id', 'c.alias as category_alias'))
					->leftJoin($db->quoteName('#__swjprojects_categories', 'c') . ' ON c.id = p.catid');

				// Join over current translates
				$current = $this->translates['current'];
				$query->select(array('t_p.title as project_title', 't_p.introtext as project_introtext'))
					->leftJoin($db->quoteName('#__swjprojects_translate_projects', 't_p')
						. ' ON t_p.id = p.id AND ' . $db->quoteName('t_p.language') . ' = ' . $db->quote($current));

				// Join over default translates
				$default = $this->translates['default'];
				if ($current != $default)
				{
					$query->select(array('td_p.title as default_project_title', 'td_p.introtext as default_project_introtext'))
						->leftJoin($db->quoteName('#__swjprojects_translate_projects', 'td_p')
							. ' ON td_p.id = p.id AND ' . $db->quoteName('td_p.language') . ' = ' . $db->quote($default));
				}

				// Filter by published state
				$published = $this->getState('filter.published');
				if (is_numeric($published))
				{
					$query->where('v.state = ' . (int) $published)
						->where('p.state = ' . (int) $published)
						->where('c.state = ' . (int) $published);
				}
				elseif (is_array($published))
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
					->order($db->escape('micro') . ' ' . $db->escape('desc'))
					->order($db->escape('stability') . ' ' . $db->escape('desc'))
					->order($db->escape('stage') . ' ' . $db->escape('desc'));

				$db->setQuery($query);

				$items      = $db->loadObjectList();
				$files_root = $this->filesPath['versions'];
				$site_root  = Uri::getInstance()->toString(array('scheme', 'host', 'port'));
				$updates    = new SimpleXMLElement('<updates/>');

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
					$item->link     = Route::_(SWJProjectsHelperRoute::getVersionRoute($item->slug, $item->pslug, $item->cslug));
					$item->download = Route::_(SWJProjectsHelperRoute::getDownloadRoute($item->id, null,
						$item->project_element, $download_key));

					// Set version & name
					$item->version = $item->major . '.' . $item->minor . '.' . $item->micro;
					$item->name    = $item->project_title . ' ' . $item->version;
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
					$item->description = JHtmlString::truncate($item->project_introtext, 150, false, false);

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
						$item->element = str_replace(array($item->folder . '_', 'plg_'), '', $item->element);
					}
					if ($item->type == 'template')
					{
						$item->element = str_replace(array('tmpl_', 'tpl_', 'tmp_'), '', $item->element);
					}

					// Set client
					$item->client = $item->project_joomla->get('client', 0);

					// Set files format
					$item->files = Folder::files($files_root . '/' . $item->id, 'download', false);

					// Set file
					$item->file = (!empty($item->files)) ? $item->files[0] : false;

					// Add to updates
					$update = $updates->addChild('update');
					$update->addChild('name', $item->name);
					$update->addChild('description', $item->description);
					$update->addChild('element', $item->element);
					$update->addChild('type', $item->type);
					$update->addChild('folder', $item->folder);
					$update->addChild('client', $item->client);
					$update->addChild('version', $item->version);

					$infourl = $update->addChild('infourl', $site_root . $item->link);
					$infourl->addAttribute('title', $item->name);

					if ($item->file)
					{
						$downloads   = $update->addChild('downloads');
						$downloadurl = $downloads->addChild('downloadurl', $site_root . $item->download);
						$downloadurl->addAttribute('type', 'full');
						$downloadurl->addAttribute('format', File::getExt($item->file));

						$file_path_from_root = $files_root . '/' . $item->id.'/'.$item->file;
						$update->addChild('sha256',hash_file('sha256',$file_path_from_root));
						$update->addChild('sha384',hash_file('sha384',$file_path_from_root));
						$update->addChild('sha512',hash_file('sha512',$file_path_from_root));
					}

					$tags = $update->addChild('tags');
					$tags->addChild('tag', $item->tag);

					$targetPlatform = $update->addChild('targetPlatform');
					$targetPlatform->addAttribute('name', 'joomla');
					$targetPlatform->addAttribute('version', '');
				}

				$this->_extensionXML[$hash] = $updates;
			}
			catch (Exception $e)
			{
				throw new Exception(Text::_($e->getMessage()), $e->getCode());
			}
		}

		return $this->_extensionXML[$hash];
	}

	/**
	 * Method to get collection xml.
	 *
	 * @throws  Exception
	 *
	 * @return  SimpleXMLElement|Exception  Projects collection SimpleXMLElement on success, exception on failure.
	 *
	 * @since  1.0.0
	 */
	public function getCollectionXML()
	{
		if ($this->_extensionXML === null)
		{
			$this->_extensionXML = array();
		}

		$hash = ($download_key = $this->getState('download.key')) ? md5($download_key) : md5('0');
		if (!isset($this->_collectionXML[$hash]))
		{
			try
			{
				$db    = $this->getDbo();
				$query = $db->getQuery(true)
					->select('p.*')
					->from($db->quoteName('#__swjprojects_projects', 'p'))
					->leftJoin($db->quoteName('#__swjprojects_categories', 'c') . ' ON c.id = p.catid')
					->where($db->quoteName('p.joomla') . ' LIKE' . $db->quote('%"update_server":"1"%'));

				// Join over current translates
				$current = $this->translates['current'];
				$query->select(array('t_p.title as title'))
					->leftJoin($db->quoteName('#__swjprojects_translate_projects', 't_p')
						. ' ON t_p.id = p.id AND ' . $db->quoteName('t_p.language') . ' = ' . $db->quote($current));

				// Join over default translates
				$default = $this->translates['default'];
				if ($current != $default)
				{
					$query->select(array('td_p.title as default_title'))
						->leftJoin($db->quoteName('#__swjprojects_translate_projects', 'td_p')
							. ' ON td_p.id = p.id AND ' . $db->quoteName('td_p.language') . ' = ' . $db->quote($default));
				}

				// Join over versions for last version
				$subQuery = $db->getQuery(true)
					->select(array('CONCAT(lv.major, ".", lv.minor, ".", lv.micro)'))
					->from($db->quoteName('#__swjprojects_versions', 'lv'))
					->where('lv.project_id = p.id')
					->where('lv.state = 1')
					->where($db->quoteName('lv.tag') . ' = ' . $db->quote('stable'))
					->order($db->escape('lv.major') . ' ' . $db->escape('desc'))
					->order($db->escape('lv.minor') . ' ' . $db->escape('desc'))
					->order($db->escape('lv.micro') . ' ' . $db->escape('desc'))
					->setLimit(1);
				$query->select('(' . $subQuery->__toString() . ') as version');

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

				// Add the list ordering clause
				$query->order($db->escape('p.ordering') . ' ' . $db->escape('asc'));

				$items = $db->setQuery($query)->loadObjectList();

				$extensionset = new SimpleXMLElement('<extensionset/>');
				$site_root    = Uri::getInstance()->toString(array('scheme', 'host', 'port'));

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

					// Set folder
					$item->folder = $item->joomla->get('folder', '');

					// Set element
					$item->element = $item->joomla->get('element', $item->element);
					if ($item->type == 'plugin')
					{
						$item->element = str_replace(array($item->folder . '_', 'plg_'), '', $item->element);
					}
					if ($item->type == 'template')
					{
						$item->element = str_replace(array('tmpl_', 'tpl_', 'tmp_'), '', $item->element);
					}

					// Set client
					$item->client = $item->joomla->get('client', 0);

					// Set link
					$item->link = Route::_(SWJProjectsHelperRoute::getJUpdateRoute($item->id, null, $download_key));

					// Add to extensionset
					$extension = $extensionset->addChild('extension');
					$extension->addAttribute('name', $item->title);
					$extension->addAttribute('element', $item->element);
					$extension->addAttribute('type', $item->type);
					$extension->addAttribute('folder', $item->folder);
					$extension->addAttribute('client', $item->client);
					$extension->addAttribute('detailsurl', $site_root . $item->link);
					$extension->addAttribute('version', $item->version);
				}

				$this->_collectionXML[$hash] = $extensionset;
			}
			catch (Exception $e)
			{
				throw new Exception(Text::_($e->getMessage()), $e->getCode());
			}
		}

		return $this->_collectionXML[$hash];
	}

	/**
	 * Method to get project id from element.
	 *
	 * @param   string  $pk  The id of the project.
	 *
	 * @throws  Exception
	 *
	 * @return  integer|Exception  Project id on success, exception on failure.
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

		if ($this->_projectID === null)
		{
			$this->_projectID = array();
		}

		if (empty($pk))
		{
			return -1;
		}

		if (!isset($this->_projectID[$pk]))
		{
			try
			{
				$db    = $this->getDbo();
				$query = $db->getQuery(true)
					->select('p.id')
					->from($db->quoteName('#__swjprojects_projects', 'p'))
					->leftJoin($db->quoteName('#__swjprojects_categories', 'c') . ' ON c.id = p.catid')
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
					throw new Exception(Text::_('COM_SWJPROJECTS_ERROR_PROJECT_NOT_FOUND'), 404);
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
}
<?php
/*
 * @package    SW JProjects
 * @version    2.2.1
 * @author     Sergey Tolkachyov
 * @сopyright  Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
 */

namespace Joomla\Component\SWJProjects\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use function defined;
use function file_get_contents;
use function implode;
use function is_array;
use function is_numeric;
use function md5;
use function property_exists;
use function stat;
use function str_replace;

class JchangelogModel extends BaseDatabaseModel
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
	 * @var  \SimpleXMLElement
	 *
	 * @since  1.0.0
	 */
	protected $_extensionXML = null;

	/**
	 * Collection xml.
	 *
	 * @var  \SimpleXMLElement
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
	 * @throws  \Exception
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

		// Set cache timeout
		$this->cacheTimeout = $params->get('jupdate_cachetimeout', 0) . ' hour';

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @throws  \Exception
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
			$this->setState('filter.published', array(0, 1));
			$this->setState('debug', 1);
		}
		else
		{
			$this->setState('filter.published', 1);
		}
	}

	/**
	 * Method to get changelog update server xml.
	 *
	 * @param   integer  $pk  The id of the project.
	 *
	 * @throws  \Exception
	 *
	 * @return  string|\Exception  changelog servers xml string on success, \Exception on failure.
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
			$this->_xml = [];
		}

		$hash = md5($pk);
		if (!isset($this->_xml[$hash]))
		{
			if (!$context = $this->getXMLCache($pk))
			{

				// if $pk = 0 or $pk = -1
				if($pk < 1){

					throw new \Exception('There is no project id or element specified','500');

				}

				$xml     = $this->getExtensionXML($pk);
				$context = $xml->asXML();

				// Save cache
				if (!$this->state->get('debug'))
				{
					$path = $this->filesPath['cache'] . '/jchangelog_' . $hash . '.xml';

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
	 * @throws  \Exception
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
			$this->_xmlCache = [];
		}

		$hash =  md5($pk);
		if (!isset($this->_xmlCache[$hash]))
		{
			$cache = false;
			$path  = $this->filesPath['cache'] . '/changelog' . $hash . '.xml';
			if (\is_file($path))
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
	 * @return  \SimpleXMLElement|\Exception  Project updates \SimpleXMLElement on success, \Exception on failure.
	 *
	 * @throws  \Exception
	 *
	 * @since  1.0.0
	 *
	 * @see https://docs.joomla.org/Deploying_an_Update_Server
	 */
	public function getExtensionXML($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('project.id');

		if (empty($pk))
		{
			$pk = $this->getProjectID();
		}

		if ($this->_extensionXML === null)
		{
			$this->_extensionXML = [];
		}

		$hash = md5($pk);
		if (!isset($this->_extensionXML[$hash]))
		{
			try
			{

				$component_params = ComponentHelper::getParams('com_swjprojects');
				// Join over current translates
				$request_lang = $component_params->get('changelogurl_language', 'en-GB');

				$db    = $this->getDatabase();
				$query = $db->getQuery(true)
					->select([
						'v.id',
						'v.major',
						'v.minor',
						'v.patch',
						'v.hotfix',
						'v_t.changelog',
						'v_t.language',
						'p.joomla as project_joomla',
					])
					->from($db->quoteName('#__swjprojects_versions', 'v'))
					->where('v.project_id = ' . (int) $pk)
					->where('v_t.language = ' . $db->quote($request_lang))
					->leftJoin($db->quoteName('#__swjprojects_translate_versions','v_t'),'v.id = v_t.id')
					->leftJoin($db->quoteName('#__swjprojects_projects','p'),'p.id = '.$db->quote((int)$pk));

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

				$items      = $db->loadObjectList();

				$changelogs    = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><changelogs/>');

				foreach ($items   as $item)
				{

					// Set version & name
					$item->version = $item->major . '.' . $item->minor . '.' . $item->patch;
					if(property_exists($item, 'hotfix') && !empty($item->hotfix)){
						$item->version .= '.'.$item->hotfix;
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

					// Add to changelogs
					$changelog = $changelogs->addChild('changelog');

					$changelog->addChild('element', $item->element);
					$changelog->addChild('type', $item->type);
					$changelog->addChild('version', $item->version);
					$changelog_data = [];

					foreach ($item->changelog->toObject() as $value)
					{
						$value_type = (empty($value->type)) ? 'info' : $value->type;
						$changelog_data[$value_type][] = $value->title.' '.$value->description;
					}
					if(!empty($changelog_data)){
						foreach ($changelog_data as $key => $value){
							$changelog_child =	$changelog->addChild($key);
							foreach ($value as $v){
								$changelog_child->addChild('item',$v);
							}
						}
					}

				}

				$this->_extensionXML[$hash] = $changelogs;
			}
			catch (\Exception $e)
			{
				throw new \Exception(Text::_($e->getMessage()), $e->getCode());
			}
		}

		return $this->_extensionXML[$hash];
	}


	/**
	 * Method to get project id from element.
	 *
	 * @param   string  $pk  The id of the project.
	 *
	 * @throws  \Exception
	 *
	 * @return  integer|\Exception  Project id on success, \Exception on failure.
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
			$this->_projectID = [];
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
					throw new \Exception(Text::_('COM_SWJPROJECTS_ERROR_PROJECT_NOT_FOUND'), 404);
				}

				$this->_projectID[$pk] = $data;
			}
			catch (\Exception $e)
			{
				throw new \Exception(Text::_($e->getMessage()), $e->getCode());
			}
		}

		return $this->_projectID[$pk];
	}
}

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

namespace Joomla\Component\SWJProjects\Site\Service;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Categories\CategoryFactoryInterface;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\CMS\Factory;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\Database\DatabaseInterface;
use function explode;
use function md5;
use function strpos;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Routing class of com_content
 *
 * @since  3.3
 */
class Router extends RouterView
{
	/**
	 * Flag to remove IDs
	 *
	 * @var    boolean
	 */
	protected $noIDs = false;

	/**
	 * The category factory
	 *
	 * @var CategoryFactoryInterface
	 *
	 * @since  4.0.0
	 */
	private $categoryFactory;

	/**
	 * The category cache
	 *
	 * @var  array
	 *
	 * @since  4.0.0
	 */
	private $categoryCache = [];

	/**
	 * The segments cache
	 *
	 * @var  array
	 *
	 * @since  4.0.0
	 */
	private $_segments = [];

	/**
	 * The ids cache
	 *
	 * @var  array
	 *
	 * @since  4.0.0
	 */
	private $_ids = [];

	/**
	 * The db
	 *
	 * @var DatabaseInterface
	 *
	 * @since  4.0.0
	 */
	private $db;

	/**
	 * Content Component router constructor
	 *
	 * @param   SiteApplication  $app   The application object
	 * @param   AbstractMenu     $menu  The menu object to work with
	 */
	public function __construct(SiteApplication $app, AbstractMenu $menu)
	{
		$this->db = Factory::getContainer()->get(DatabaseInterface::class);

		// Projects route
		$projects = new RouterViewConfiguration('projects');
		$projects->setKey('id')->setNestable();
		$this->registerView($projects);

		// Project route
		$project = new RouterViewConfiguration('project');
		$project->setKey('id')->setParent($projects, 'catid');
		$this->registerView($project);

		// Versions route
		$versions = new RouterViewConfiguration('versions');
		$versions->setKey('id')->setParent($project, 'project_id');
		$this->registerView($versions);

		// Version route
		$version = new RouterViewConfiguration('version');
		$version->setKey('id')->setParent($versions, 'project_id');
		$this->registerView($version);

		// Documentation route
		$documentation = new RouterViewConfiguration('documentation');
		$documentation->setKey('id')->setParent($project, 'project_id');
		$this->registerView($documentation);

		// document route
		$document = new RouterViewConfiguration('document');
		$document->setKey('id')->setParent($documentation, 'project_id');
		$this->registerView($document);

		// JUpdate route
		$jupdate = new RouterViewConfiguration('jupdate');
		$this->registerView($jupdate);

		// JChangelog route
		$jchangelog = new RouterViewConfiguration('jchangelog');
		$this->registerView($jchangelog);

		// Download route
		$download = new RouterViewConfiguration('download');
		$this->registerView($download);

		// Userkeys route
		$userkeys = new RouterViewConfiguration('userkeys');
		$this->registerView($userkeys);

		parent::__construct($app, $menu);

		$this->attachRule(new MenuRules($this));
		$this->attachRule(new StandardRules($this));
		$this->attachRule(new NomenuRules($this));
	}

	/**
	 * Method to get the segment(s) for projects.
	 *
	 * @param   string  $id     ID of the item to retrieve the segments.
	 * @param   array   $query  The request that is built right now.
	 *
	 * @return  array  The segments of this item.
	 *
	 * @since  1.0.0
	 */
	public function getProjectsSegment($id, $query)
	{
		$path = [];

		while ($id > 1)
		{
			if (strpos($id, ':'))
			{
				$id = explode(':', $id, 2)[0];
			}

			$hash = md5('projects_' . $id);
			if (!isset($this->_segments[$hash]))
			{
				$db      = $this->db;
				$dbquery = $db->getQuery(true)
					->select(array('id', 'alias', 'parent_id'))
					->from('#__swjprojects_categories')
					->where('id =' . $id);
				$db->setQuery($dbquery);
				$this->_segments[$hash] = $db->loadObject();
			}

			$category = $this->_segments[$hash];

			if ($category)
			{
				$path[$category->id] = $category->alias;
			}

			$id = ($category) ? $category->parent_id : 1;
		}

		$path[1] = 'root';

		return $path;
	}

	/**
	 * Method to get the segment(s) for project.
	 *
	 * @param   string  $id     ID of the item to retrieve the segments.
	 * @param   array   $query  The request that is built right now.
	 *
	 * @return  array  The segments of this item.
	 *
	 * @since  1.0.0
	 */
	public function getProjectSegment($id, $query)
	{
		if (!strpos($id, ':'))
		{
			$hash = md5('project_' . $id);
			if (!isset($this->_segments[$hash]))
			{
				$db      = $this->db;
				$dbquery = $db->getQuery(true)
					->select('alias')
					->from('#__swjprojects_projects')
					->where('id = ' . (int) $id);
				$db->setQuery($dbquery);

				$this->_segments[$hash] = $db->loadResult();
			}

			$id .= ':' . $this->_segments[$hash];
		}

		list($void, $segment) = explode(':', $id, 2);

		return [$void => $segment];
	}

	/**
	 * Method to get the segment(s) for versions.
	 *
	 * @param   string  $id     ID of the item to retrieve the segments.
	 * @param   array   $query  The request that is built right now.
	 *
	 * @return  array  The segments of this item.
	 *
	 * @since  1.0.0
	 */
	public function getVersionsSegment($id, $query)
	{
		if (strpos($id, ':'))
		{
			$id = explode(':', $id, 2)[0];
		}

		return [$id => 'versions'];
	}

	/**
	 * Method to get the segment(s) for version.
	 *
	 * @param   string  $id     ID of the item to retrieve the segments.
	 * @param   array   $query  The request that is built right now.
	 *
	 * @return  array|boolean  The segments of this item.
	 *
	 * @since  1.0.0
	 */
	public function getVersionSegment($id, $query)
	{
		if (@$query['view'] == 'version')
		{
			if (!strpos($id, ':'))
			{
				$hash = md5('version_' . $id);
				if (!isset($this->_segments[$hash]))
				{
					$db      = $this->db;
					$dbquery = $db->getQuery(true)
						->select('alias')
						->from('#__swjprojects_versions')
						->where('id = ' . (int) $id);
					$db->setQuery($dbquery);
					$this->_segments[$hash] = $db->loadResult();
				}

				$id .= ':' . $this->_segments[$hash];
			}

			list($void, $segment) = explode(':', $id, 2);

			return [$void => $segment];
		}

		return false;
	}

	/**
	 * Method to get the segment(s) for documentation.
	 *
	 * @param   string  $id     ID of the item to retrieve the segments.
	 * @param   array   $query  The request that is built right now.
	 *
	 * @return  array  The segments of this item.
	 *
	 * @since  1.4.0
	 */
	public function getDocumentationSegment($id, $query)
	{
		if (strpos($id, ':'))
		{
			$id = explode(':', $id, 2)[0];
		}

		return [$id => 'documentation'];
	}

	/**
	 * Method to get the segment(s) for document.
	 *
	 * @param   string  $id     ID of the item to retrieve the segments.
	 * @param   array   $query  The request that is built right now.
	 *
	 * @return  array|boolean  The segments of this item.
	 *
	 * @since  1.4.0
	 */
	public function getDocumentSegment($id, $query)
	{
		if (@$query['view'] == 'document')
		{
			if (!strpos($id, ':'))
			{
				$hash = md5('version_' . $id);
				if (!isset($this->_segments[$hash]))
				{
					$db      = $this->db;
					$dbquery = $db->getQuery(true)
						->select('alias')
						->from('#__swjprojects_documentation')
						->where('id = ' . (int) $id);
					$db->setQuery($dbquery);
					$this->_segments[$hash] = $db->loadResult();
				}

				$id .= ':' . $this->_segments[$hash];
			}

			list($void, $segment) = explode(':', $id, 2);

			return [$void => $segment];
		}

		return false;
	}

	/**
	 * Method to get the segment(s) for jupdate.
	 *
	 * @param   string  $id     ID of the item to retrieve the segments.
	 * @param   array   $query  The request that is built right now.
	 *
	 * @return  array|string  The segments of this item.
	 *
	 * @since  1.0.0
	 */
	public function getJUpdateSegment($id, $query)
	{
		return [1 => 1];
	}

	/**
	 * Method to get the segment(s) for jchangelof.
	 *
	 * @param   string  $id     ID of the item to retrieve the segments.
	 * @param   array   $query  The request that is built right now.
	 *
	 * @return  array|string  The segments of this item.
	 *
	 * @since  1.0.0
	 */
	public function getJChangelogSegment($id, $query)
	{
		return [1 => 1];
	}

	/**
	 * Method to get the segment(s) for download.
	 *
	 * @param   string  $id     ID of the item to retrieve the segments.
	 * @param   array   $query  The request that is built right now.
	 *
	 * @return  array|string  The segments of this item.
	 *
	 * @since  1.2.0
	 */
	public function getDownloadSegment($id, $query)
	{
		return [1 => 1];
	}

	/**
	 * Method to get the id for projects.
	 *
	 * @param   string  $segment  Segment to retrieve the id.
	 * @param   array   $query    The request that is parsed right now.
	 *
	 * @return  integer|false  The id of this item or false.
	 *
	 * @since  1.0.0
	 */
	public function getProjectsId($segment, $query)
	{
		if (!empty($segment))
		{
			$hash = md5('projects_' . $segment);
			if (!isset($this->_ids[$hash]))
			{
				$db      = $this->db;
				$dbquery = $db->getQuery(true)
					->select('id')
					->from('#__swjprojects_categories')
					->where($db->quoteName('alias') . ' = ' . $db->quote($segment))
					->where($db->quoteName('parent_id') . ' = ' . $db->quote($query['id']));
				$db->setQuery($dbquery);

				$this->_ids[$hash] = (int) $db->loadResult();
			}

			return $this->_ids[$hash];
		}

		return false;
	}

	/**
	 * Method to get the id for project.
	 *
	 * @param   string  $segment  Segment to retrieve the id.
	 * @param   array   $query    The request that is parsed right now.
	 *
	 * @return  integer|false  The id of this item or false.
	 *
	 * @since  1.0.0
	 */
	public function getProjectId($segment, $query)
	{
		if (!empty($segment))
		{
			$hash = md5('project_' . $segment);
			if (!isset($this->_ids[$hash]))
			{
				$db      = $this->db;
				$dbquery = $db->getQuery(true)
					->select('id')
					->from('#__swjprojects_projects')
					->where($db->quoteName('alias') . ' = ' . $db->quote($segment))
					->where($db->quoteName('catid') . ' = ' . $db->quote($query['id']));
				$db->setQuery($dbquery);

				$this->_ids[$hash] = (int) $db->loadResult();
			}

			return $this->_ids[$hash];
		}

		return false;
	}

	/**
	 * Method to get the id for versions.
	 *
	 * @param   string  $segment  Segment to retrieve the id.
	 * @param   array   $query    The request that is parsed right now.
	 *
	 * @return  integer|false  The id of this item or false.
	 *
	 * @since  1.0.0
	 */
	public function getVersionsId($segment, $query)
	{
		if (!empty($segment) && $segment == 'versions')
		{
			return (!empty($query['id'])) ? (int) $query['id'] : false;
		}

		return false;
	}

	/**
	 * Method to get the id for version.
	 *
	 * @param   string  $segment  Segment to retrieve the id.
	 * @param   array   $query    The request that is parsed right now.
	 *
	 * @return  integer|false  The id of this item or false.
	 *
	 * @since  1.0.0
	 */
	public function getVersionId($segment, $query)
	{
		if (!empty($segment))
		{
			$hash = md5('version_' . $segment);
			if (!isset($this->_ids[$hash]))
			{
				$db      = $this->db;
				$dbquery = $db->getQuery(true)
					->select('id')
					->from('#__swjprojects_versions')
					->where($db->quoteName('alias') . ' = ' . $db->quote($segment))
					->where($db->quoteName('project_id') . ' = ' . $db->quote($query['id']));
				$db->setQuery($dbquery);

				$this->_ids[$hash] = (int) $db->loadResult();
			}

			return $this->_ids[$hash];
		}

		return false;
	}

	/**
	 * Method to get the id for documentation.
	 *
	 * @param   string  $segment  Segment to retrieve the id.
	 * @param   array   $query    The request that is parsed right now.
	 *
	 * @return  integer|false  The id of this item or false.
	 *
	 * @since  1.4.0
	 */
	public function getDocumentationId($segment, $query)
	{
		if (!empty($segment) && $segment == 'documentation')
		{
			return (!empty($query['id'])) ? (int) $query['id'] : false;
		}

		return false;
	}

	/**
	 * Method to get the id for version.
	 *
	 * @param   string  $segment  Segment to retrieve the id.
	 * @param   array   $query    The request that is parsed right now.
	 *
	 * @return  integer|false  The id of this item or false.
	 *
	 * @since  1.4.0
	 */
	public function getDocumentId($segment, $query)
	{
		if (!empty($segment))
		{
			$hash = md5('document_' . $segment);
			if (!isset($this->_ids[$hash]))
			{
				$db      = $this->db;
				$dbquery = $db->getQuery(true)
					->select('id')
					->from('#__swjprojects_documentation')
					->where($db->quoteName('alias') . ' = ' . $db->quote($segment))
					->where($db->quoteName('project_id') . ' = ' . $db->quote($query['id']));
				$db->setQuery($dbquery);

				$this->_ids[$hash] = (int) $db->loadResult();
			}

			return $this->_ids[$hash];
		}

		return false;
	}

	/**
	 * Method to get the id for jupdate.
	 *
	 * @param   string  $segment  Segment to retrieve the id.
	 * @param   array   $query    The request that is parsed right now.
	 *
	 * @return  integer|false  The id of this item or false.
	 *
	 * @since  1.0.0
	 */
	public function getJUpdateId($segment, $query)
	{
		return 1;
	}

	/**
	 * Method to get the id for jchangelog.
	 *
	 * @param   string  $segment  Segment to retrieve the id.
	 * @param   array   $query    The request that is parsed right now.
	 *
	 * @return  integer|false  The id of this item or false.
	 *
	 * @since  1.0.0
	 */
	public function getJChangelogId($segment, $query)
	{
		return 1;
	}

	/**
	 * Method to get the id for download.
	 *
	 * @param   string  $segment  Segment to retrieve the id.
	 * @param   array   $query    The request that is parsed right now.
	 *
	 * @return  integer|false  The id of this item or false.
	 *
	 * @since  1.2.0
	 */
	public function getDownloadId($segment, $query)
	{
		return 1;
	}

	/**
	 * Method to get the id for user keys list.
	 *
	 * @param   string  $segment  Segment to retrieve the id.
	 * @param   array   $query    The request that is parsed right now.
	 *
	 * @return  integer|false  The id of this item or false.
	 *
	 * @since  2.3.0
	 */
	public function getUserkeysId($segment, $query)
	{
		return 1;
	}
}

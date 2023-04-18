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
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Utilities\ArrayHelper;

class SWJProjectsModelKeys extends ListModel
{
	/**
	 * Site default translate language.
	 *
	 * @var  array
	 *
	 * @since  1.3.0
	 */
	protected $translate = null;

	/**
	 * Users array.
	 *
	 * @var  array
	 *
	 * @since  1.6.0
	 */
	protected $_users = null;

	/**
	 * Keys projects array.
	 *
	 * @var  array
	 *
	 * @since  1.6.0
	 */
	protected $_projects = null;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since  1.3.0
	 */
	public function __construct($config = array())
	{
		// Set translate
		$this->translate = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');

		// Add the ordering filtering fields whitelist
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'v.id',
				'published', 'state', 'k.state',
				'project', 'project_id', 'k.project_id', 'p.id',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @since  1.3.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Set search filter state
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		// Set published filter state
		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		// Set project filter state
		$project = $this->getUserStateFromRequest($this->context . '.filter.project', 'filter_project', '');
		$this->setState('filter.project', $project);

		// List state information
		$ordering  = empty($ordering) ? 'k.date_start' : $ordering;
		$direction = empty($direction) ? 'desc' : $direction;

		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since  1.3.0
	 */
	protected function getStoreId($id = '')
	{
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.project');

		return parent::getStoreId($id);
	}

	/**
	 * Build an sql query to load versions list.
	 *
	 * @return  JDatabaseQuery  Database query to load versions list.
	 *
	 * @since  1.3.0
	 */
	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select(array('k.*'))
			->from($db->quoteName('#__swjprojects_keys', 'k'));

		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published))
		{
			$query->where('k.state = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(k.state = 0 OR k.state = 1)');
		}

		// Filter by project state
		$project = $this->getState('filter.project');
		if (is_numeric($project))
		{
			$project = (int) $project;
			$sql     = array('FIND_IN_SET(' . $project . ', k.projects)');
			$query->where('(' . implode(' OR ', $sql) . ')');
		}

		// Filter by search
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('k.id = ' . (int) substr($search, 3));
			}
			else
			{
				$sql     = array();
				$columns = array('k.key', 'k.note');

				foreach ($columns as $column)
				{
					$sql[] = $db->quoteName($column) . ' LIKE '
						. $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				}

				$query->where('(' . implode(' OR ', $sql) . ')');
			}
		}

		// Group by
		$query->group(array('k.id'));

		// Add the list ordering clause
		$ordering  = $this->state->get('list.ordering', 'k.date_start');
		$direction = $this->state->get('list.direction', 'desc');
		$query->order($db->escape($ordering) . ' ' . $db->escape($direction));

		return $query;
	}

	/**
	 * Method to get an array of keys data.
	 *
	 * @return  mixed  Versions objects array on success, false on failure.
	 *
	 * @since  1.3.0
	 */
	public function getItems()
	{
		if ($items = parent::getItems())
		{
			$projects = $this->getProjects(implode(',',
				array_merge(ArrayHelper::getColumn($items, 'projects'))));
			$users    = $this->getUsers(ArrayHelper::getColumn($items, 'user'));
			$nullDate = $this->getDbo()->getNullDate();
			foreach ($items as &$item)
			{
				// Set projects
				if (!empty($item->projects))
				{
					$ids            = explode(',', $item->projects);
					$item->projects = array();
					foreach ($ids as $id)
					{
						$id = (int) $id;
						if (!empty($projects[$id]))
						{
							$item->projects[$id] = $projects[$id];
						}
					}
					$item->projects = ArrayHelper::sortObjects($item->projects, 'ordering');
				}
				else $item->projects = false;

				// Set date_end
				if ($item->date_end === $nullDate) $item->date_end = false;

				// Set user
				$item->user = (!empty($users[$item->user])) ? $users[$item->user] : false;

				// Mask key
				$item->key = SWJProjectsHelperKeys::maskKey($item->key);
			}
		}

		return $items;
	}


	/**
	 * Method to get categories.
	 *
	 * @param   string|array  $pks  The id of the categories.
	 *
	 * @return  object[] Categories array.
	 *
	 * @since  1.6.0
	 */
	public function getProjects($pks = null)
	{
		if ($this->_projects === null)
		{
			$this->_projects = array();

			$all                 = new stdClass();
			$all->id             = -1;
			$all->title          = Text::_('JALL');
			$all->element        = '';
			$all->ordering       = 0;
			$this->_projects[-1] = $all;
		}

		// Prepare ids
		$projects = array();
		if (!is_array($pks)) $pks = array_unique(ArrayHelper::toInteger(explode(',', $pks)));
		if (empty($pks)) return $projects;

		// Check loaded categories
		$get = array();
		foreach ($pks as $pk)
		{
			if (isset($this->_projects[$pk])) $projects[$pk] = $this->_projects[$pk];
			else $get[] = $pk;
		}

		// Get projects
		if (!empty($get))
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true)
				->select(array('p.id', 'p.element', 'p.ordering'))
				->from($db->quoteName('#__swjprojects_projects', 'p'))
				->where('p.id  IN (' . implode(',', $get) . ')');

			// Join over translates
			$translate = $this->translate;
			$query->select(array('t_p.title as title'))
				->leftJoin($db->quoteName('#__swjprojects_translate_projects', 't_p')
					. ' ON t_p.id = p.id AND ' . $db->quoteName('t_p.language') . ' = ' . $db->quote($translate));

			if ($rows = $db->setQuery($query)->loadObjectList())
			{
				foreach ($rows as $row)
				{
					// Set project title
					$row->title = (empty($row->title)) ? $row->element : $row->title;

					$this->_projects[$row->id] = $row;
					$projects[$row->id]        = $row;
				}
			}
		}

		return $projects;
	}

	/**
	 * Method to get users.
	 *
	 * @param   string|array  $pks  The id of the users.
	 *
	 * @return  object[] Users array.
	 *
	 * @since  1.6.0
	 */
	public function getUsers($pks = null)
	{
		if ($this->_users === null) $this->_users = array();

		// Prepare ids
		$users = array();
		if (!is_array($pks)) $pks = array_unique(ArrayHelper::toInteger(explode(',', $pks)));
		if (empty($pks)) return $users;

		// Check loaded users
		$get = array();
		foreach ($pks as $pk)
		{
			if (isset($this->_users[$pk])) $users[$pk] = $this->_users[$pk];
			else $get[] = $pk;
		}

		// Get users
		if (!empty($get))
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true)
				->select(array('u.id', 'u.name', 'u.username', 'u.email'))
				->from($db->quoteName('#__users', 'u'))
				->where('u.id  IN (' . implode(',', $get) . ')');

			if ($rows = $db->setQuery($query)->loadObjectList())
			{
				foreach ($rows as $row)
				{
					$this->_users[$row->id] = $row;
					$users[$row->id]        = $row;
				}
			}
		}

		return $users;
	}
}
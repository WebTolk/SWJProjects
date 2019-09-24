<?php
/**
 * @package    SW JProjects Component
 * @version    1.3.1
 * @author     Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2018 - 2019 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;

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
				'project_title', 'pl.title',
				'element', 'p.element',
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

		// Join over the projects
		$query->select(array('k.project_id as project_id', 'p.element as project_element'))
			->leftJoin($db->quoteName('#__swjprojects_projects', 'p') . ' ON p.id = k.project_id');

		// Join over translates
		$translate = $this->translate;
		$query->select(array('t_p.title as project_title'))
			->leftJoin($db->quoteName('#__swjprojects_translate_projects', 't_p')
				. ' ON t_p.id = p.id AND ' . $db->quoteName('t_p.language') . ' = ' . $db->quote($translate));

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
			$query->where('k.project_id = ' . (int) $project);
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
				$columns = array('k.key', 'k.note', 'p.element', 't_p.title');

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
			foreach ($items as &$item)
			{
				// Set project title
				$item->project_title = (empty($item->project_title)) ? $item->project_element : $item->project_title;
				if ($item->project_id == -1)
				{
					$item->project_title = Text::_('JALL');
				}

				// Mask key
				$item->key = SWJProjectsHelperKeys::maskKey($item->key);
			}
		}

		return $items;
	}
}
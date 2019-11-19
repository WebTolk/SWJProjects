<?php
/**
 * @package    SW JProjects Component
 * @version    __DEPLOY_VERSION__
 * @author     Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2018 - 2019 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\ListModel;

class SWJProjectsModelDocumentation extends ListModel
{
	/**
	 * Site default translate language.
	 *
	 * @var  array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $translate = null;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function __construct($config = array())
	{
		// Set translate
		$this->translate = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');

		// Add the ordering filtering fields whitelist
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'd.id',
				'published', 'state', 'd.state',
				'title', 't_d.title' .
				'project', 'project_id', 'd.project_id', 'p.id',
				'project_title', 't_p.title',
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
	 * @since  __DEPLOY_VERSION__
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
		$ordering  = empty($ordering) ? 'd.ordering' : $ordering;
		$direction = empty($direction) ? 'asc' : $direction;

		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since  __DEPLOY_VERSION__
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
	 * @since  __DEPLOY_VERSION__
	 */
	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select(array('d.*'))
			->from($db->quoteName('#__swjprojects_documentation', 'd'));

		// Join over the projects
		$query->select(array('p.id as project_id', 'p.element as project_element'))
			->leftJoin($db->quoteName('#__swjprojects_projects', 'p') . ' ON p.id = d.project_id');

		// Join over translates
		$translate = $this->translate;
		$query->select(array('t_d.title as title'))
			->leftJoin($db->quoteName('#__swjprojects_translate_documentation', 't_d')
				. ' ON t_d.id = d.id AND ' . $db->quoteName('t_d.language') . ' = ' . $db->quote($translate));

		$query->select(array('t_p.title as project_title'))
			->leftJoin($db->quoteName('#__swjprojects_translate_projects', 't_p')
				. ' ON t_p.id = p.id AND ' . $db->quoteName('t_p.language') . ' = ' . $db->quote($translate));

		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published))
		{
			$query->where('d.state = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(d.state = 0 OR d.state = 1)');
		}

		// Filter by project state
		$project = $this->getState('filter.project');
		if (is_numeric($project))
		{
			$query->where('d.project_id = ' . (int) $project);
		}

		// Filter by search
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('v.id = ' . (int) substr($search, 3));
			}
			else
			{
				$sql     = array();
				$columns = array('ta_d.title', 'ta_d.introtext', 'ta_d.fulltext', 'p.element', 't_p.title');

				foreach ($columns as $column)
				{
					$sql[] = $db->quoteName($column) . ' LIKE '
						. $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				}

				$query->leftJoin($db->quoteName('#__swjprojects_translate_documentation', 'ta_d') . ' ON ta_d.id = d.id')
					->where('(' . implode(' OR ', $sql) . ')');
			}
		}

		// Group by
		$query->group(array('d.id'));

		// Add the list ordering clause
		$ordering  = $this->state->get('list.ordering', 'd.ordering');
		$direction = $this->state->get('list.direction', 'ask');
		$query->order($db->escape($ordering) . ' ' . $db->escape($direction));

		return $query;
	}

	/**
	 * Method to get an array of documents data.
	 *
	 * @return  mixed  Documents objects array on success, false on failure.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getItems()
	{
		if ($items = parent::getItems())
		{
			foreach ($items as &$item)
			{
				// Set project title
				$item->project_title = (empty($item->project_title)) ? $item->project_element : $item->project_title;

				// Set title
				$item->title = (empty($item->title)) ? $item->alias : $item->title;
			}
		}

		return $items;
	}
}
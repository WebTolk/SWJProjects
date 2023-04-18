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

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;

class SWJProjectsModelVersions extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since  1.0.0
	 */
	public function __construct($config = array())
	{
		// Add the ordering filtering fields whitelist
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'v.id',
				'published', 'state', 'v.state',
				'version',
				'tag', 'v.tag', 'v.stability',
				'downloads', 'v.downloads',
				'project', 'project_id', 'v.project_id', 'p.id',
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
	 * @since  1.0.0
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

		// Set tag filter state
		$tag = $this->getUserStateFromRequest($this->context . '.filter.tag', 'filter_tag', '');
		$this->setState('filter.tag', $tag);

		// List state information
		$ordering  = empty($ordering) ? 'v.date' : $ordering;
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
	 * @since  1.0.0
	 */
	protected function getStoreId($id = '')
	{
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.project');
		$id .= ':' . $this->getState('filter.tag');

		return parent::getStoreId($id);
	}

	/**
	 * Build an sql query to load versions list.
	 *
	 * @return  JDatabaseQuery  Database query to load versions list.
	 *
	 * @since  1.0.0
	 */
	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select(array('v.*', 'CONCAT(v.major, ".", v.minor, ".", v.micro) as version'))
			->from($db->quoteName('#__swjprojects_versions', 'v'));

		// Join over the projects
		$query->select(array('p.id as project_id', 'p.element as project_element'))
			->leftJoin($db->quoteName('#__swjprojects_projects', 'p') . ' ON p.id = v.project_id');

		// Join over translates
		$translate = SWJProjectsHelperTranslation::getDefault();
		$query->select(array('t_p.title as project_title'))
			->leftJoin($db->quoteName('#__swjprojects_translate_projects', 't_p')
				. ' ON t_p.id = p.id AND ' . $db->quoteName('t_p.language') . ' = ' . $db->quote($translate));

		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published))
		{
			$query->where('v.state = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(v.state = 0 OR v.state = 1)');
		}

		// Filter by project state
		$project = $this->getState('filter.project');
		if (is_numeric($project))
		{
			$query->where('v.project_id = ' . (int) $project);
		}

		// Filter by tag state
		$tag = $this->getState('filter.tag');
		if (!empty($tag))
		{
			$query->where('v.tag = ' . $tag);
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
				$columns = array('v.tag', 'p.element', 't_p.title', 'ta_v.changelog');

				foreach ($columns as $column)
				{
					$sql[] = $db->quoteName($column) . ' LIKE '
						. $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				}

				$query->leftJoin($db->quoteName('#__swjprojects_translate_versions', 'ta_v') . ' ON ta_v.id = v.id')
					->where('(' . implode(' OR ', $sql) . ')');
			}
		}

		// Group by
		$query->group(array('v.id'));

		// Add the list ordering clause
		$ordering  = $this->state->get('list.ordering', 'v.date');
		$direction = $this->state->get('list.direction', 'desc');

		if ($ordering == 'title')
		{
			$query->order($db->escape('project_title') . ' ' . $db->escape($direction))
				->order($db->escape('major') . ' ' . $db->escape($direction))
				->order($db->escape('minor') . ' ' . $db->escape($direction))
				->order($db->escape('micro') . ' ' . $db->escape($direction))
				->order($db->escape('stability') . ' ' . $db->escape($direction))
				->order($db->escape('stage') . ' ' . $db->escape($direction));
		}
		elseif ($ordering == 'version')
		{
			$query->order($db->escape('major') . ' ' . $db->escape($direction))
				->order($db->escape('minor') . ' ' . $db->escape($direction))
				->order($db->escape('micro') . ' ' . $db->escape($direction))
				->order($db->escape('stability') . ' ' . $db->escape($direction))
				->order($db->escape('stage') . ' ' . $db->escape($direction));
		}
		else
		{
			$query->order($db->escape($ordering) . ' ' . $db->escape($direction));
		}

		return $query;
	}

	/**
	 * Method to get an array of versions data.
	 *
	 * @return  mixed  Versions objects array on success, false on failure.
	 *
	 * @since  1.0.0
	 */
	public function getItems()
	{
		if ($items = parent::getItems())
		{
			foreach ($items as &$item)
			{
				// Set project title
				$item->project_title = (empty($item->project_title)) ? $item->project_element : $item->project_title;

				// Set version & title
				$item->version = $item->major . '.' . $item->minor . '.' . $item->micro;
				$item->title   = $item->project_title . ' ' . $item->version;
				if ($item->tag !== 'stable')
				{
					$item->version .= ' ' . $item->tag;
					$item->title   .= ' ' . Text::_('COM_SWJPROJECTS_VERSION_TAG_' . $item->tag);
					if ($item->tag !== 'dev' && !empty($item->stage))
					{
						$item->version .= $item->stage;
						$item->title   .= ' ' . $item->stage;
					}
				}
			}
		}

		return $items;
	}
}
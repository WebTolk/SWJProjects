<?php
/*
 * @package    SW JProjects
 * @version    2.0.0
 * @author     Sergey Tolkachyov
 * @сopyright  Copyright (c) 2018 - 2024 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
 */

namespace Joomla\Component\SWJProjects\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Component\SWJProjects\Administrator\Helper\TranslationHelper;

class CategoriesModel extends ListModel
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
				'id', 'c.id',
				'title', 'c.title',
				'published', 'state', 'c.state',
				'ordering', 'lft', 'c.lft',
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

		// List state information
		$ordering  = empty($ordering) ? 'c.lft' : $ordering;
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
	 * @since  1.0.0
	 */
	protected function getStoreId($id = '')
	{
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');

		return parent::getStoreId($id);
	}

	/**
	 * Build an sql query to load categories list.
	 *
	 * @return  DatabaseQuery  Database query to load categories list.
	 *
	 * @since  1.0.0
	 */
	protected function getListQuery()
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
			->select(array('c.*'))
			->from($db->quoteName('#__swjprojects_categories', 'c'))
			->where($db->quoteName('c.alias') . '!=' . $db->quote('root'));

		// Join over translates
		$translate = TranslationHelper::getCurrent();
		$query->select(array('t_c.title as title'))
			->leftJoin($db->quoteName('#__swjprojects_translate_categories', 't_c')
				. ' ON t_c.id = c.id AND ' . $db->quoteName('t_c.language') . ' = ' . $db->quote($translate));

		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published))
		{
			$query->where('c.state = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(c.state = 0 OR c.state = 1)');
		}

		// Filter by search
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('c.id = ' . (int) substr($search, 3));
			}
			else
			{
				$sql     = [];
				$columns = ['c.alias', 'ta_c.title', 'ta_c.description'];

				foreach ($columns as $column)
				{
					$sql[] = $db->quoteName($column) . ' LIKE '
						. $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				}

				$query->leftJoin($db->quoteName('#__swjprojects_translate_categories', 'ta_c') . ' ON ta_c.id = c.id')
					->where('(' . implode(' OR ', $sql) . ')');
			}
		}

		// Group by
		$query->group(array('c.id'));

		// Add the list ordering clause
		$ordering  = $this->state->get('list.ordering', 'c.lft');
		$direction = $this->state->get('list.direction', 'asc');
		$query->order($db->escape($ordering) . ' ' . $db->escape($direction));

		return $query;
	}

	/**
	 * Method to get an array of categories data.
	 *
	 * @return  mixed  Categories objects array on success, false on failure.
	 *
	 * @since  1.0.0
	 */
	public function getItems()
	{
		if ($items = parent::getItems())
		{
			foreach ($items as &$item)
			{
				// Set title
				$item->title = (empty($item->title)) ? $item->alias : $item->title;
			}
		}
		return $items;
	}
}

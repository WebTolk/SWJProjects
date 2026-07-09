<?php
/**
 * @package       SW JProjects
 * @version       2.6.2
 * @Author        Sergey Tolkachyov
 * @copyright  Copyright (c) 2018 - 2026 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link          https://web-tolk.ru
 * @since         2.6.2
 */

namespace Joomla\Component\SWJProjects\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Component\SWJProjects\Administrator\Helper\TranslationHelper;
use function implode;
use function is_numeric;
use function str_replace;
use function stripos;
use function substr;
use function trim;

class MaintainersModel extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since  2.6.2
	 */
	public function __construct($config = [])
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = [
				'id', 'm.id',
				'title', 't_m.title',
				'state', 'm.state',
				'projects_count',
				'ordering', 'm.ordering',
			];
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction.
	 *
	 * @since  2.6.2
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$ordering  = empty($ordering) ? 'm.ordering' : $ordering;
		$direction = empty($direction) ? 'asc' : $direction;

		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string
	 *
	 * @since  2.6.2
	 */
	protected function getStoreId($id = '')
	{
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');

		return parent::getStoreId($id);
	}

	/**
	 * Build an sql query to load maintainers list.
	 *
	 * @return  \Joomla\Database\DatabaseQuery
	 *
	 * @since  2.6.2
	 */
	protected function getListQuery()
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
			->select(['m.*', 'COUNT(DISTINCT p.id) AS projects_count'])
			->from($db->quoteName('#__swjprojects_maintainers', 'm'));

		$translate = TranslationHelper::getCurrent();
		$query->select(['t_m.title as title'])
			->leftJoin($db->quoteName('#__swjprojects_translate_maintainers', 't_m')
				. ' ON t_m.id = m.id AND ' . $db->quoteName('t_m.language') . ' = ' . $db->quote($translate));
		$query->leftJoin($db->quoteName('#__swjprojects_projects', 'p') . ' ON p.maintainer_id = m.id');

		$published = $this->getState('filter.published');
		if (is_numeric($published))
		{
			$query->where('m.state = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(m.state = 0 OR m.state = 1)');
		}

		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('m.id = ' . (int) substr($search, 3));
			}
			else
			{
				$sql     = [];
				$columns = ['m.alias', 'm.website', 'ta_m.title', 'ta_m.description'];

				foreach ($columns as $column)
				{
					$sql[] = $db->quoteName($column) . ' LIKE '
						. $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				}

				$query->leftJoin($db->quoteName('#__swjprojects_translate_maintainers', 'ta_m') . ' ON ta_m.id = m.id')
					->where('(' . implode(' OR ', $sql) . ')');
			}
		}

		$query->group(['m.id']);

		$ordering  = $this->state->get('list.ordering', 'm.ordering');
		$direction = $this->state->get('list.direction', 'asc');
		$query->order($db->escape($ordering) . ' ' . $db->escape($direction));

		return $query;
	}

	/**
	 * Method to get an array of maintainers data.
	 *
	 * @return  mixed
	 *
	 * @since  2.6.2
	 */
	public function getItems()
	{
		if ($items = parent::getItems())
		{
			foreach ($items as &$item)
			{
				$item->title = empty($item->title) ? $item->alias : $item->title;
			}
		}

		return $items;
	}
}

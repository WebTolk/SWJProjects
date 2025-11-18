<?php
/**
 * @package       SW JProjects
 * @version       2.6.0
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
 */

namespace Joomla\Component\SWJProjects\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Router\Route;
use Joomla\Component\SWJProjects\Administrator\Helper\KeysHelper;
use Joomla\Component\SWJProjects\Administrator\Helper\TranslationHelper;
use Joomla\Component\SWJProjects\Site\Helper\ImagesHelper;
use Joomla\Component\SWJProjects\Site\Helper\RouteHelper;
use Joomla\Database\QueryInterface;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use function array_merge;
use function array_unique;
use function boolval;
use function defined;
use function dump;
use function explode;
use function implode;
use function intval;
use function is_array;
use function serialize;
use function str_replace;
use function trim;

class UserkeysModel extends ListModel
{
	/**
	 * Model context string.
	 *
	 * @var  string
	 *
	 * @since  1.4.0
	 */
	protected $_context = 'com_swjprojects.userkeys';

	/**
	 * Site default translate language.
	 *
	 * @var  array
	 *
	 * @since  2.3.0
	 */
	protected $translate = null;

	/**
	 * Keys projects array.
	 *
	 * @var  array
	 *
	 * @since  2.3.0
	 */
	protected $_projects = null;


	public function __construct($config = [])
	{
		// Set translates
		$this->translate = TranslationHelper::getCurrent();
		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @throws  \Exception
	 *
	 * @since  1.4.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = Factory::getApplication('site');

		// Set request states
		$this->setState('user.id', $app->getIdentity()->id);

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

		// Set published
		$this->setState('filter.published',1);
		// List state information

		parent::populateState($ordering, $direction);

		// Set ordering for query
		$this->setState('list.ordering', $ordering);
		$this->setState('list.direction', $direction);

		// Set limit & start for query
		$this->setState('list.limit', $params->get('user_keys_list_limit', 10, 'uint'));
		$this->setState('list.start', $app->getInput()->get('start', 0, 'uint'));
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since  1.4.0
	 */
	protected function getStoreId($id = '')
	{
		$id .= ':' . $this->getState('user.id');
		$id .= ':' . serialize($this->getState('filter.published'));

		return parent::getStoreId($id);
	}

	/**
	 * Build an sql query to load documents list.
	 *
	 * @return  QueryInterface  Database query to load documents list.
	 *
	 * @since  1.4.0
	 */
	protected function getListQuery()
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
			->select(['uk.*'])
			->from($db->quoteName('#__swjprojects_keys', 'uk'));

		$query->where($db->quoteName('uk.state').' = '.$db->quote(1));
		$query->where($db->quoteName('uk.user').' = '.$db->quote($this->getState('user.id')));

		// Filter by search
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			$sql     = [];
			$columns = ['uk.key'];

			foreach ($columns as $column)
			{
				$sql[] = $db->quoteName($column) . ' LIKE '
					. $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
			}

			$query->where('(' . implode(' OR ', $sql) . ')');

		}

		// Group by
		$query->group(['uk.id']);

		// Add the list ordering clause
		$ordering  = $this->state->get('list.ordering', 'uk.date_start');
		$direction = $this->state->get('list.direction', 'desc');
		$query->order($db->escape($ordering) . ' ' . $db->escape($direction));

		return $query;
	}

	/**
	 * Method to get an array of documentation data.
	 *
	 * @return  mixed  Versions objects array on success, false on failure.
	 *
	 * @since  1.4.0
	 */
	public function getItems()
	{
		if ($items = parent::getItems())
		{

			$projects = $this->getProjects(implode(',',
				array_merge(ArrayHelper::getColumn($items, 'projects'))));

			$nullDate = $this->getDatabase()->getNullDate();

			foreach ($items as &$key)
			{
				$key->limit = boolval($key->limit);
				$key->limit_count = intval($key->limit_count);

				// Set projects
				if (!empty($key->projects))
				{
					$ids            = explode(',', $key->projects);
					$key->projects = [];
					foreach ($ids as $id)
					{
						$id = (int) $id;
						if (!empty($projects[$id]))
						{
							$key->projects[$id] = $projects[$id];
						}
					}
					$key->projects = ArrayHelper::sortObjects($key->projects, 'ordering');
				}
				else
				{
					$key->projects = false;
				}

				// Set date_end
				if ($key->date_end === $nullDate) $key->date_end = false;

			}
		}

		return $items;
	}

	/**
	 * Method to get Projects.
	 *
	 * @param   string|array  $pks  The ids of the projects.
	 *
	 * @return  object[] Projects array.
	 *
	 * @since  2.3.0
	 */
	public function getProjects($pks = null)
	{
		if ($this->_projects === null)
		{
			$this->_projects = [];

			$all                 = new \stdClass();
			$all->id             = -1;
			$all->title          = Text::_('JALL');
			$all->element        = '';
			$all->ordering       = 0;
			$this->_projects[-1] = $all;
		}

		// Prepare ids
		$projects = [];
		if (!is_array($pks)) $pks = array_unique(ArrayHelper::toInteger(explode(',', $pks)));
		if (empty($pks)) return $projects;

		// Check loaded categories
		$get = [];
		foreach ($pks as $pk)
		{
			if (isset($this->_projects[$pk])) $projects[$pk] = $this->_projects[$pk];
			else $get[] = $pk;
		}

		// Get projects
		if (!empty($get))
		{
			$db    = $this->getDatabase();
			$query = $db->getQuery(true)
				->select(['p.id', 'p.element', 'p.catid', 'p.ordering'])
				->from($db->quoteName('#__swjprojects_projects', 'p'))
				->where($db->quoteName('p.id').' IN (' . implode(',', $get) . ')')
				->where($db->quoteName('p.state').' = '.$db->quote(1));

			// Join over translates
			$translate = $this->translate;

			$query->select(['t_p.title as title'])
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

}

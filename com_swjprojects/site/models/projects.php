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
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

class SWJProjectsModelProjects extends ListModel
{
	/**
	 * Model context string.
	 *
	 * @var  string
	 *
	 * @since  1.0.0
	 */
	protected $_context = 'swjprojects.projects';

	/**
	 * An category.
	 *
	 * @var  array
	 *
	 * @since  1.0.0
	 */
	protected $_item = null;

	/**
	 * Category parent object.
	 *
	 * @var  object
	 *
	 * @since  1.0.0
	 */
	protected $_categoryParent = null;

	/**
	 * Projects categories array
	 *
	 * @var  array
	 *
	 * @since  1.5.0
	 */
	protected $_categories = null;

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
			'versions' => $root . '/versions'
		);

		// Set translates
		$this->translates = array(
			'current' => SWJProjectsHelperTranslation::getCurrent(),
			'default' => SWJProjectsHelperTranslation::getDefault(),
		);

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @throws  Exception
	 *
	 * @since  1.0.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = Factory::getApplication('site');

		// Set request states
		$this->setState('category.id', $app->input->getInt('id', 1));

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

		// List state information
		$ordering  = empty($ordering) ? 'p.ordering' : $ordering;
		$direction = empty($direction) ? 'asc' : $direction;

		parent::populateState($ordering, $direction);

		// Set ordering for query
		$this->setState('list.ordering', $ordering);
		$this->setState('list.direction', $direction);

		// Set limit & start for query
		$this->setState('list.limit', $params->get('projects_limit', 10, 'uint'));
		$this->setState('list.start', $app->input->get('start', 0, 'uint'));
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
		$id .= ':' . $this->getState('category.id');
		$id .= ':' . serialize($this->getState('filter.published'));

		return parent::getStoreId($id);
	}

	/**
	 * Build an sql query to load projects list.
	 *
	 * @return  JDatabaseQuery  Database query to load projects list.
	 *
	 * @since  1.0.0
	 */
	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select(array('p.*'))
			->from($db->quoteName('#__swjprojects_projects', 'p'));

		// Join over the categories
		$query->leftJoin($db->quoteName('#__swjprojects_categories', 'c') . ' ON c.id = p.catid');

		// Join over current translates
		$current = $this->translates['current'];
		$query->select(array('t_p.*', 'p.id as id'))
			->leftJoin($db->quoteName('#__swjprojects_translate_projects', 't_p')
				. ' ON t_p.id = p.id AND ' . $db->quoteName('t_p.language') . ' = ' . $db->quote($current));

		// Join over default translates
		$default = $this->translates['default'];
		if ($current != $default)
		{
			$query->select(array('td_p.title as default_title', 'td_p.payment as default_payment'))
				->leftJoin($db->quoteName('#__swjprojects_translate_projects', 'td_p')
					. ' ON td_p.id = p.id AND ' . $db->quoteName('td_p.language') . ' = ' . $db->quote($default));
		}

		// Join over versions for last version
		$subQuery = $db->getQuery(true)
			->select(array('CONCAT(lv.id, ":", lv.alias, "|", lv.major, ".", lv.minor, ".", lv.micro)'))
			->from($db->quoteName('#__swjprojects_versions', 'lv'))
			->where('lv.project_id = p.id')
			->where('state = 1')
			->where($db->quoteName('lv.tag') . ' = ' . $db->quote('stable'))
			->order($db->escape('lv.major') . ' ' . $db->escape('desc'))
			->order($db->escape('lv.minor') . ' ' . $db->escape('desc'))
			->order($db->escape('lv.micro') . ' ' . $db->escape('desc'))
			->setLimit(1);
		$query->select('(' . $subQuery->__toString() . ') as last_version');

		// Join over versions for download counter
		$query->select(array('SUM(dc.downloads) as downloads'))
			->leftJoin($db->quoteName('#__swjprojects_versions', 'dc') . ' ON dc.project_id = p.id '
				. ' AND dc.state = 1');

		// Join over documentation for documentation link
		$query->select(array('d.id as documentation'))
			->leftJoin($db->quoteName('#__swjprojects_documentation', 'd') .
				' ON d.project_id = p.id AND d.state = 1');

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

		// Filter by category state
		$category = $this->getState('category.id');
		if (is_numeric($category) && $category > 1)
		{
			$subQuery = $db->getQuery(true)
				->select('sub.id')
				->from($db->quoteName('#__swjprojects_categories', 'sub'))
				->innerJoin($db->quoteName('#__swjprojects_categories', 'this') .
					' ON sub.lft > this.lft AND sub.rgt < this.rgt')
				->where('this.id = ' . (int) $category);

			$query->leftJoin($db->quoteName('#__swjprojects_projects_categories', 'pc') .
				' ON pc.project_id = p.id')
				->where('(c.id =' . (int) $category . ' OR c.id IN (' . $subQuery->__toString() . ')'
					. 'OR pc.category_id =' . (int) $category . ' OR pc.category_id IN (' . $subQuery->__toString() . ')' . ')');
		}

		// Group by
		$query->group(array('p.id'));

		// Add the list ordering clause
		$ordering  = $this->state->get('list.ordering', 'p.ordering');
		$direction = $this->state->get('list.direction', 'asc');
		$query->order($db->escape($ordering) . ' ' . $db->escape($direction));

		return $query;
	}

	/**
	 * Method to get an array of projects data.
	 *
	 * @return  mixed  Projects objects array on success, false on failure.
	 *
	 * @since  1.0.0
	 */
	public function getItems()
	{
		if ($items = parent::getItems())
		{
			$categories = $this->getCategories(implode(',', array_merge(
					ArrayHelper::getColumn($items, 'catid'),
					ArrayHelper::getColumn($items, 'additional_categories'))
			));
			foreach ($items as &$item)
			{
				// Set default translates data
				if ($this->translates['current'] != $this->translates['default'])
				{
					$item->title = (empty($item->title)) ? $item->default_title : $item->title;
				}

				// Set title
				$item->title = (empty($item->title)) ? $item->element : $item->title;

				// Set categories
				$item->category = (!empty($categories[$item->catid])) ? $categories[$item->catid] : false;
				if (!empty($item->additional_categories))
				{
					$item->categories = array($item->catid => $item->category);
					foreach (explode(',', $item->additional_categories) as $catid)
					{
						if (!empty($categories[$catid]))
						{
							$item->categories[$catid] = $categories[$catid];
						}
					}

					$item->categories = ArrayHelper::sortObjects($item->categories, 'lft');
				}

				// Set introtext
				$item->introtext = nl2br($item->introtext);

				// Set payment
				$item->payment = new Registry($item->payment);
				if ($item->download_type === 'paid' && $this->translates['current'] != $this->translates['default'])
				{
					$item->default_payment = new Registry($item->default_payment);
					if (!$item->payment->get('link'))
					{
						$item->payment->set('link', $item->default_payment->get('link'));
					}
					if (!$item->payment->get('price'))
					{
						$item->payment->set('price', $item->default_payment->get('price'));
					}
				}

				// Set joomla
				$item->joomla = new Registry($item->joomla);
				if (!$item->joomla->get('type'))
				{
					$item->joomla = false;
				}

				// Set urls
				$item->urls = new Registry($item->urls);

				// Set images
				$item->images = new Registry();
				$item->images->set('icon',
					SWJProjectsHelperImages::getImage('projects', $item->id, 'icon', $item->language));
				$item->images->set('cover',
					SWJProjectsHelperImages::getImage('projects', $item->id, 'cover', $item->language));

				// Set link
				$item->slug          = $item->id . ':' . $item->alias;
				$item->cslug         = ($item->category) ? $item->category->slug : $item->catid;
				$item->link          = Route::_(SWJProjectsHelperRoute::getProjectRoute($item->slug, $item->cslug));
				$item->versions      = Route::_(SWJProjectsHelperRoute::getVersionsRoute($item->slug, $item->cslug));
				$item->download      = Route::_(SWJProjectsHelperRoute::getDownloadRoute(null, null, $item->element));
				$item->documentation = (!$item->documentation) ? false :
					Route::_(SWJProjectsHelperRoute::getDocumentationRoute($item->slug, $item->cslug));

				// Set version
				$item->version = false;
				if (!empty($item->last_version))
				{
					$item->version = new stdClass();
					list($item->version->slug, $item->version->version) = explode('|', $item->last_version, 2);
					list($item->version->id, $item->version->alias) = explode(':', $item->version->slug, 2);
					$item->version->link = Route::_(SWJProjectsHelperRoute::getVersionRoute($item->version->slug,
						$item->slug, $item->cslug));
				}
			}
		}

		return $items;
	}

	/**
	 * Method to get category data.
	 *
	 * @param   integer  $pk  The id of the category.
	 *
	 * @throws  Exception
	 *
	 * @return  object|boolean|Exception  Category object on success, false or exception on failure.
	 *
	 * @since  1.0.0
	 */
	public function getItem($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('category.id');

		if ($this->_item === null)
		{
			$this->_item = array();
		}

		if (!isset($this->_item[$pk]))
		{
			try
			{
				$db    = $this->getDbo();
				$query = $db->getQuery(true)
					->select(array('c.*'))
					->from($db->quoteName('#__swjprojects_categories', 'c'))
					->where('c.id = ' . (int) $pk);

				// Join over current translates
				$current = $this->translates['current'];
				$query->select(array('t_c.*', 'c.id as id'))
					->leftJoin($db->quoteName('#__swjprojects_translate_categories', 't_c')
						. '  ON t_c.id = c.id AND ' . $db->quoteName('t_c.language') . ' = ' . $db->quote($current));

				// Join over default translates
				$default = $this->translates['default'];
				if ($current != $default)
				{
					$query->select(array('td_c.title as default_title'))
						->leftJoin($db->quoteName('#__swjprojects_translate_categories', 'td_c')
							. ' ON td_c.id = c.id AND ' . $db->quoteName('td_c.language') . ' = ' . $db->quote($default));
				}

				// Filter by published state
				$published = $this->getState('filter.published');
				if (is_numeric($published))
				{
					$query->where('c.state = ' . (int) $published);
				}
				elseif (is_array($published))
				{
					$published = ArrayHelper::toInteger($published);
					$published = implode(',', $published);

					$query->where('c.state IN (' . $published . ')');
				}

				$data = $db->setQuery($query)->loadObject();

				if (empty($data))
				{
					throw new Exception(Text::_('COM_SWJPROJECTS_ERROR_CATEGORY_NOT_FOUND'), 404);
				}

				// Set default translates data
				if ($this->translates['current'] != $this->translates['default'])
				{
					$data->title = (empty($data->title)) ? $data->default_title : $data->title;
				}

				// Set title
				$data->title = (empty($data->title)) ? $data->alias : $data->title;

				// Set description
				$data->description = (!empty($data->description) ? nl2br($data->description) : '');

				// Set link
				$data->slug = $data->id . ':' . $data->alias;
				$data->link = Route::_(SWJProjectsHelperRoute::getProjectsRoute($data->slug));

				// Set params
				$params       = new Registry($data->params);
				$data->params = clone $this->getState('params');
				$data->params->merge($params);

				// Set metadata
				$data->metadata = new Registry($data->metadata);
				$data->metadata->set('image',
					SWJProjectsHelperImages::getImage('categories', $data->id, 'meta', $data->language));

				$this->_item[$pk] = $data;
			}
			catch (Exception $e)
			{
				if ($e->getCode() == 404)
				{
					throw new Exception(Text::_($e->getMessage()), 404);
				}
				else
				{
					$this->setError($e);
					$this->_item[$pk] = false;
				}
			}
		}

		return $this->_item[$pk];
	}

	/**
	 * Method to get category parent data.
	 *
	 * @param   integer  $pk  The id of the category.
	 *
	 * @throws  Exception
	 *
	 * @return  object|boolean|Exception  Category object on success, false or exception on failure.
	 *
	 * @since  1.0.0
	 */
	public function getCategoryParent($pk = null)
	{
		if (empty($pk)) return false;

		if ($this->_categoryParent === null)
		{
			$this->_categoryParent = array();
		}

		if (!isset($this->_categoryParent[$pk]))
		{
			try
			{
				$db    = $this->getDbo();
				$query = $db->getQuery(true)
					->select(array('c.id', 'c.alias'))
					->from($db->quoteName('#__swjprojects_categories', 'child'))
					->innerJoin($db->quoteName('#__swjprojects_categories', 'c') . ' ON c.id = child.parent_id')
					->where('child.id = ' . (int) $pk);

				// Join over current translates
				$current = $this->translates['current'];
				$query->select(array('t_c.title as title'))
					->leftJoin($db->quoteName('#__swjprojects_translate_categories', 't_c')
						. '  ON t_c.id = c.id AND ' . $db->quoteName('t_c.language') . ' = ' . $db->quote($current));

				// Join over default translates
				$default = $this->translates['default'];
				if ($current != $default)
				{
					$query->select(array('td_c.title as default_title'))
						->leftJoin($db->quoteName('#__swjprojects_translate_categories', 'td_c')
							. ' ON td_c.id = c.id AND ' . $db->quoteName('td_c.language') . ' = ' . $db->quote($default));
				}

				// Filter by published state
				$published = $this->getState('filter.published');
				if (is_numeric($published))
				{
					$query->where('c.state = ' . (int) $published)
						->where('child.state = ' . (int) $published);
				}
				elseif (is_array($published))
				{
					$published = ArrayHelper::toInteger($published);
					$published = implode(',', $published);

					$query->where('c.state IN (' . $published . ')')
						->where('child.state IN (' . $published . ')');
				}

				$data = $db->setQuery($query)->loadObject();

				if (empty($data))
				{
					throw new Exception(Text::_('COM_SWJPROJECTS_ERROR_CATEGORY_NOT_FOUND'), 404);
				}

				// Set default translates data
				if ($this->translates['current'] != $this->translates['default'])
				{
					$data->title = (empty($data->title)) ? $data->default_title : $data->title;
				}

				// Set link
				$data->link = Route::_(SWJProjectsHelperRoute::getProjectsRoute($data->id . ':' . $data->alias));

				// Set title
				$data->title = (!empty($data->title)) ? $data->title : $data->alias;

				$this->_categoryParent[$pk] = $data;
			}
			catch (Exception $e)
			{
				if ($e->getCode() == 404)
				{
					throw new Exception(Text::_($e->getMessage()), 404);
				}
				else
				{
					$this->setError($e);
					$this->_categoryParent[$pk] = false;
				}
			}
		}

		return $this->_categoryParent[$pk];
	}

	/**
	 * Method to get Categories.
	 *
	 * @param   string|array  $pks  The id of the categories.
	 *
	 * @return  object[] Direction array.
	 *
	 * @since  1.5.0
	 */
	public function getCategories($pks = null)
	{
		if ($this->_categories === null)
		{
			$this->_categories = array();
		}

		// Prepare ids
		$categories = array();
		if (!is_array($pks))
		{
			$pks = array_unique(ArrayHelper::toInteger(explode(',', $pks)));
		}
		if (empty($pks)) return $categories;

		// Check loaded categories
		$get = array();
		foreach ($pks as $pk)
		{
			if (isset($this->_categories[$pk]))
			{
				$categories[$pk] = $this->_categories[$pk];
			}
			else
			{
				$get[] = $pk;
			}
		}

		// Get categories
		if (!empty($get))
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true)
				->select(array('c.id', 'c.alias', 'c.lft'))
				->from($db->quoteName('#__swjprojects_categories', 'c'))
				->where('c.id  IN (' . implode(',', $get) . ')');

			// Join over current translates
			$current = $this->translates['current'];
			$query->select(array('t_c.title'))
				->leftJoin($db->quoteName('#__swjprojects_translate_categories', 't_c')
					. '  ON t_c.id = c.id AND ' . $db->quoteName('t_c.language') . ' = ' . $db->quote($current));

			// Join over default translates
			$default = $this->translates['default'];
			if ($current != $default)
			{
				$query->select(array('td_c.title as default_title'))
					->leftJoin($db->quoteName('#__swjprojects_translate_categories', 'td_c')
						. ' ON td_c.id = c.id AND ' . $db->quoteName('td_c.language') . ' = ' . $db->quote($default));
			}

			// Filter by published state
			$published = $this->getState('filter.published');
			if (is_numeric($published))
			{
				$query->where('c.state = ' . (int) $published);
			}
			elseif (is_array($published))
			{
				$published = ArrayHelper::toInteger($published);
				$published = implode(',', $published);

				$query->where('c.state IN (' . $published . ')');
			}

			// Group by
			$query->group(array('c.id'));

			if ($rows = $db->setQuery($query)->loadObjectList())
			{
				foreach ($rows as $row)
				{
					// Set default translates data
					if ($this->translates['current'] != $this->translates['default'])
					{
						$row->title = (empty($row->title)) ? $row->default_title : $row->title;
					}

					// Set title
					$row->title = (empty($row->title)) ? $row->alias : $row->title;

					$row->slug = $row->id . ':' . $row->alias;
					$row->link = Route::_(SWJProjectsHelperRoute::getProjectsRoute($row->slug));

					$this->_categories[$row->id] = $row;
					$categories[$row->id]        = $row;
				}
			}
		}

		return $categories;
	}
}

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
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

class SWJProjectsModelProject extends ItemModel
{
	/**
	 * Model context string.
	 *
	 * @var  string
	 *
	 * @since  1.0.0
	 */
	protected $_context = 'swjprojects.project';

	/**
	 * Category parent object.
	 *
	 * @var  object
	 *
	 * @since  1.0.0
	 */
	protected $_categoryParent = null;

	/**
	 * Project categories array
	 *
	 * @var  array
	 *
	 * @since  1.5.0
	 */
	protected $_categories = null;

	/**
	 * Project relations array.
	 *
	 * @var  array
	 *
	 * @since  1.1.0
	 */
	protected $_relations = null;

	/**
	 * Project last version object.
	 *
	 * @var  object
	 *
	 * @since  1.3.0
	 */
	protected $_version = null;

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
	 * @throws  Exception
	 *
	 * @since  1.0.0
	 */
	protected function populateState()
	{
		$app = Factory::getApplication('site');

		// Set request states
		$this->setState('project.id', $app->input->getInt('id', 0));
		$this->setState('category.id', $app->input->getInt('catid', 1));

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
	}

	/**
	 * Method to get project data.
	 *
	 * @param   integer  $pk  The id of the project.
	 *
	 * @throws  Exception
	 *
	 * @return  object|boolean|Exception  Project object on success, false or exception on failure.
	 *
	 * @since  1.0.0
	 */
	public function getItem($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('project.id');

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
					->select(array('p.*'))
					->from($db->quoteName('#__swjprojects_projects', 'p'))
					->where('p.id = ' . (int) $pk);

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

				// Join over versions for download counter
				$query->select(array('SUM(dc.downloads) as downloads'))
					->leftJoin($db->quoteName('#__swjprojects_versions', 'dc') . ' ON dc.project_id = p.id'
						. ' AND dc.state = 1');

				// Join over versions for joomla versions
				$subQuery = $db->getQuery(true)
					->select(array('GROUP_CONCAT(joomla_version SEPARATOR ",")'))
					->from($db->quoteName('#__swjprojects_versions', 'jv'))
					->where('jv.project_id = p.id');
				$query->select('(' . $subQuery->__toString() . ') as joomla_versions');

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

				$data = $db->setQuery($query)->loadObject();

				if (!$data->id)
				{
					throw new Exception(Text::_('COM_SWJPROJECTS_ERROR_PROJECT_NOT_FOUND'), 404);
				}

				// Set default translates data
				if ($this->translates['current'] != $this->translates['default'])
				{
					$data->title = (empty($data->title)) ? $data->default_title : $data->title;
				}

				// Set title
				$data->title = (empty($data->title)) ? $data->element : $data->title;

				// Set categories
				$categories     = !empty($data->additional_categories) ?
					explode(',', $data->additional_categories) : array();
				$categories[]   = $data->catid;
				$categories     = $this->getCategories(implode(',', $categories));
				$data->category = (!empty($categories[$data->catid])) ? $categories[$data->catid] : false;
				if (!empty($data->additional_categories))
				{
					$data->categories = array($data->catid => $data->category);
					foreach (explode(',', $data->additional_categories) as $catid)
					{
						if (!empty($categories[$catid]))
						{
							$data->categories[$catid] = $categories[$catid];
						}
					}

					$data->categories = ArrayHelper::sortObjects($data->categories, 'lft');
				}

				// Set introtext
				$data->introtext = nl2br($data->introtext);

				// Set payment
				$data->payment = new Registry($data->payment);
				if ($data->download_type === 'paid' && $this->translates['current'] != $this->translates['default'])
				{
					$data->default_payment = new Registry($data->default_payment);
					if (!$data->payment->get('link'))
					{
						$data->payment->set('link', $data->default_payment->get('link'));
					}
					if (!$data->payment->get('price'))
					{
						$data->payment->set('price', $data->default_payment->get('price'));
					}
				}

				// Set joomla
				$data->joomla = new Registry($data->joomla);
				if (!$data->joomla->get('type'))
				{
					$data->joomla = false;
				}
				else
				{
					$data->joomla->set('version', array_unique(explode(',', $data->joomla_versions)));
				}

				// Set urls
				$data->urls = new Registry($data->urls);

				// Set images
				$data->images = new Registry();
				$data->images->set('icon',
					SWJProjectsHelperImages::getImage('projects', $data->id, 'icon', $data->language));
				$data->images->set('cover',
					SWJProjectsHelperImages::getImage('projects', $data->id, 'cover', $data->language));

				// Set gallery
				$data->gallery = SWJProjectsHelperImages::getImages('projects', $data->id, 'gallery',
					$data->gallery, $data->language);

				// Set link
				$data->slug          = $data->id . ':' . $data->alias;
				$data->cslug         = ($data->category) ? $data->category->slug : $data->catid;
				$data->link          = Route::_(SWJProjectsHelperRoute::getProjectRoute($data->slug, $data->cslug));
				$data->versions      = Route::_(SWJProjectsHelperRoute::getVersionsRoute($data->slug, $data->cslug));
				$data->download      = Route::_(SWJProjectsHelperRoute::getDownloadRoute(null, null, $data->element));
				$data->documentation = (!$data->documentation) ? false :
					Route::_(SWJProjectsHelperRoute::getDocumentationRoute($data->slug, $data->cslug));
				if (!empty($data->urls->get('documentation'))) {
					$data->documentation = false;
				}


				// Set version
				$data->version = $this->getVersion($data->id);

				// Set params
				$params       = new Registry($data->params);
				$data->params = clone $this->getState('params');
				$data->params->merge($params);

				// Set metadata
				$data->metadata = new Registry($data->metadata);
				$data->metadata->set('image',
					SWJProjectsHelperImages::getImage('projects', $data->id, 'meta', $data->language));

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
	 * Increment the hit counter for the project.
	 *
	 * @param   integer  $pk  Optional primary key of the article to increment.
	 *
	 * @throws Exception
	 *
	 * @return  boolean  True if successful; false otherwise and internal error set.
	 *
	 * @since  1.1.0
	 */
	public function hit($pk = 0)
	{
		$pk    = (!empty($pk)) ? $pk : (int) $this->getState('project.id');
		$table = Table::getInstance('Projects', 'SWJProjectsTable');
		$table->load($pk);
		$table->hit($pk);

		return true;
	}

	/**
	 * Method to get project relations data.
	 *
	 * @param   integer  $pk  The ids of the project.
	 *
	 * @throws  Exception
	 *
	 * @return  array|boolean|Exception  Relations array on success, false or exception on failure.
	 *
	 * @since  1.1.0
	 */
	public function getRelations($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('project.id');

		if (empty($pk)) return false;

		if ($this->_relations === null)
		{
			$this->_relations = array();
		}

		if (!isset($this->_relations[$pk]))
		{
			try
			{
				$db        = $this->getDbo();
				$relations = array();

				if (isset($this->_item[$pk]))
				{
					$data = $this->_item[$pk]->relations;
				}
				else
				{
					$query = $db->getQuery('true')
						->select(array('p.relations'))
						->from($db->quoteName('#__swjprojects_projects', 'p'))
						->leftJoin($db->quoteName('#__swjprojects_categories', 'c') . ' ON c.id = p.catid')
						->where('p.id = ' . (int) $pk);

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
				}

				// Prepare relations array
				if (!empty($data))
				{
					$registry = new Registry($data);
					$inners   = array();
					foreach ($registry->toArray() as $relation)
					{
						if ($relation['project'] > 0)
						{
							$inners[] = $relation['project'];
						}
						else
						{
							$relations[] = $relation;
						}
					}

					// Get inners relations
					if (!empty($inners))
					{
						$inners = ArrayHelper::toInteger($inners);
						$inners = implode(',', $inners);

						$query = $db->getQuery(true)
							->select(array('p.id', 'p.alias'))
							->from($db->quoteName('#__swjprojects_projects', 'p'))
							->where('p.id IN (' . $inners . ')');

						// Join over the categories
						$query->select(array('c.id as category_id', 'c.alias as category_alias'))
							->leftJoin($db->quoteName('#__swjprojects_categories', 'c') . ' ON c.id = p.catid');

						// Join over current translates
						$current = $this->translates['current'];
						$query->select(array('t_p.title', 't_p.language'))
							->leftJoin($db->quoteName('#__swjprojects_translate_projects', 't_p')
								. ' ON t_p.id = p.id AND ' . $db->quoteName('t_p.language') . ' = ' . $db->quote($current));

						// Join over default translates
						$default = $this->translates['default'];
						if ($current != $default)
						{
							$query->select(array('td_p.title as default_title'))
								->leftJoin($db->quoteName('#__swjprojects_translate_projects', 'td_p')
									. ' ON td_p.id = p.id AND ' . $db->quoteName('td_p.language') . ' = ' . $db->quote($default));
						}

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

						$items = $db->setQuery($query)->loadObjectList();
						foreach ($items as $item)
						{
							// Set default translates data
							if ($this->translates['current'] != $this->translates['default'])
							{
								$item->title = (empty($item->title)) ? $item->default_title : $item->title;
							}

							// Set images
							$item->images = new Registry();
							$item->images->set('icon',
								SWJProjectsHelperImages::getImage('projects', $item->id, 'icon', $item->language));
							$item->images->set('cover',
								SWJProjectsHelperImages::getImage('projects', $item->id, 'cover', $item->language));

							// Set link
							$item->slug  = $item->id . ':' . $item->alias;
							$item->cslug = $item->category_id . ':' . $item->category_alias;
							$item->link  = Route::_(SWJProjectsHelperRoute::getProjectRoute($item->slug, $item->cslug));

							// Add to relations
							$relations[] = array(
								'project' => $item->id,
								'title'   => $item->title,
								'link'    => $item->link,
								'icon'    => $item->images->get('icon')
							);
						}
					}
				}

				$this->_relations[$pk] = $relations;
			}
			catch (Exception $e)
			{
				$this->setError($e);
				$this->_relations[$pk] = false;
			}
		}

		return $this->_relations[$pk];
	}

	/**
	 * Method to get project last version data.
	 *
	 * @param   integer  $pk      The ids of the project.
	 * @param   boolean  $stable  Get only stable version.
	 *
	 * @return  array|boolean|Exception  Last version object on success, false or exception on failure.
	 *
	 * @since  1.3.0
	 */
	public function getVersion($pk = null, $stable = true)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('project.id');

		if (empty($pk)) return false;

		if ($this->_version === null)
		{
			$this->_version = array();
		}

		if (!isset($this->_version[$pk]))
		{
			try
			{
				$db    = $this->getDbo();
				$query = $db->getQuery(true)
					->select(array('v.*', 'v.tag as tag_key'))
					->from($db->quoteName('#__swjprojects_versions', 'v'))
					->where('v.project_id = ' . (int) $pk);

				// Join over the projects
				$query->select(array('p.id as project_id', 'p.alias as project_alias'))
					->leftJoin($db->quoteName('#__swjprojects_projects', 'p') . ' ON p.id = v.project_id');

				// Join over the categories
				$query->select(array('c.id as category_id', 'c.alias as category_alias'))
					->leftJoin($db->quoteName('#__swjprojects_categories', 'c') . ' ON c.id = p.catid');

				// Join over current translates
				$current = $this->translates['current'];
				$query->select(array('t_v.*', 'v.id as id'))
					->leftJoin($db->quoteName('#__swjprojects_translate_versions', 't_v')
						. ' ON t_v.id = v.id AND ' . $db->quoteName('t_v.language') . ' = ' . $db->quote($current));

				// Join over default translates
				$default = $this->translates['default'];
				if ($current != $default)
				{
					$query->select(array('td_v.changelog as default_changelog'))
						->leftJoin($db->quoteName('#__swjprojects_translate_versions', 'td_v')
							. ' ON td_v.id = v.id AND ' . $db->quoteName('td_v.language') . ' = ' . $db->quote($default));
				}

				// Filter by published state
				$published = $this->getState('filter.published');
				if (is_numeric($published))
				{
					$query->where('v.state = ' . (int) $published)
						->where('p.state = ' . (int) $published)
						->where('c.state = ' . (int) $published);
				}
				elseif (is_array($published))
				{
					$published = ArrayHelper::toInteger($published);
					$published = implode(',', $published);

					$query->where('v.state IN (' . $published . ')')
						->where('p.state IN (' . $published . ')')
						->where('c.state IN (' . $published . ')');
				}

				// Filter by tag
				if ($stable)
				{
					$query->where($db->quoteName('v.tag') . ' = ' . $db->quote('stable'));
				}

				// Set ordering
				$query->order($db->escape('v.major') . ' ' . $db->escape('desc'))
					->order($db->escape('v.minor') . ' ' . $db->escape('desc'))
					->order($db->escape('v.micro') . ' ' . $db->escape('desc'));

				$data = $db->setQuery($query)->loadObject();
				if ((empty($data) || empty($data->id)) && $stable)
				{
					return $this->getVersion($pk, false);
				}
				elseif (empty($data) || empty($data->id))
				{
					$data = false;
				}
				else
				{
					// Set default translates data
					if ($this->translates['current'] != $this->translates['default'])
					{
						$data->changelog = (empty($data->changelog) || $data->changelog == '{}') ? $data->default_changelog
							: $data->changelog;
					}

					// Set link
					$data->slug     = $data->id . ':' . $data->alias;
					$data->pslug    = $data->project_id . ':' . $data->project_alias;
					$data->cslug    = $data->category_id . ':' . $data->category_alias;
					$data->link     = Route::_(SWJProjectsHelperRoute::getVersionRoute($data->slug, $data->pslug, $data->cslug));
					$data->download = Route::_(SWJProjectsHelperRoute::getDownloadRoute($data->id));

					// Set version
					$data->version = $data->major . '.' . $data->minor . '.' . $data->micro;
					if ($data->tag_key !== 'stable')
					{
						$data->version .= ' ' . $data->tag_key;
						if ($data->tag_key !== 'dev' && !empty($data->stage))
						{
							$data->version .= $data->stage;
						}
					}

					// Set changelog
					$registry        = new Registry($data->changelog);
					$data->changelog = $registry->toArray();
					foreach ($data->changelog as &$changelog)
					{
						$changelog['description'] = nl2br($changelog['description']);
					}
				}

				$this->_version[$pk] = $data;
			}
			catch (Exception $e)
			{
				$this->setError($e);
				$this->_version[$pk] = false;
			}
		}

		return $this->_version[$pk];
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

					$row->slug            = $row->id . ':' . $row->alias;
					$row->link            = Route::_(SWJProjectsHelperRoute::getProjectsRoute($row->slug));
					$categories[$row->id] = $row;
				}
			}
		}

		return $categories;
	}
}
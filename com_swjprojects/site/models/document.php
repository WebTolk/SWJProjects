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
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

class SWJProjectsModelDocument extends ItemModel
{
	/**
	 * Model context string.
	 *
	 * @var  string
	 *
	 * @since  1.4.0
	 */
	protected $_context = 'swjprojects.version';

	/**
	 * Category parent object.
	 *
	 * @var  object
	 *
	 * @since  1.4.0
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
	 * Translates languages.
	 *
	 * @var  array
	 *
	 * @since  1.4.0
	 */
	protected $translates = null;

	/**
	 * Path to files.
	 *
	 * @var  array
	 *
	 * @since  1.4.0
	 */
	protected $filesPath = null;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @throws  Exception
	 *
	 * @since  1.4.0
	 */
	public function __construct($config = array())
	{
		$params = ComponentHelper::getParams('com_swjprojects');

		// Set files paths
		$root            = $params->get('files_folder');
		$this->filesPath = array(
			'root'          => $root,
			'documentation' => $root . '/documentation'
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
	 * @since  1.4.0
	 */
	protected function populateState()
	{
		$app = Factory::getApplication('site');

		// Set request states
		$this->setState('document.id', $app->input->getInt('id', 0));
		$this->setState('project.id', $app->input->getInt('project_id', 0));
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
	 * Method to get document data.
	 *
	 * @param   integer  $pk  The id of the document.
	 *
	 * @throws  Exception
	 *
	 * @return  object|boolean|Exception  Version object on success, false or exception on failure.
	 *
	 * @since  1.4.0
	 */
	public function getItem($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('document.id');

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
					->select(array('d.*'))
					->from($db->quoteName('#__swjprojects_documentation', 'd'))
					->where('d.id = ' . (int) $pk);

				// Join over the projects
				$query->select(array('p.id as project_id', 'p.alias as project_alias', 'p.element as project_element',
					'p.download_type', 'p.urls as project_urls', 'p.joomla', 'p.catid', 'p.additional_categories'))
					->leftJoin($db->quoteName('#__swjprojects_projects', 'p') . ' ON p.id = d.project_id');

				// Join over the categories
				$query->leftJoin($db->quoteName('#__swjprojects_categories', 'c') . ' ON c.id = p.catid');

				// Join over current translates
				$current = $this->translates['current'];
				$query->select(array('t_d.*', 'd.id as id'))
					->leftJoin($db->quoteName('#__swjprojects_translate_documentation', 't_d')
						. ' ON t_d.id = d.id AND ' . $db->quoteName('t_d.language') . ' = ' . $db->quote($current));

				$query->select(array('t_p.title as project_title', 't_p.introtext as project_introtext', 't_p.payment',
					't_p.language as project_language'))
					->leftJoin($db->quoteName('#__swjprojects_translate_projects', 't_p')
						. ' ON t_p.id = p.id AND ' . $db->quoteName('t_p.language') . ' = ' . $db->quote($current));

				// Join over default translates
				$default = $this->translates['default'];
				if ($current != $default)
				{
					$query->select(array('td_d.title as default_title'))
						->leftJoin($db->quoteName('#__swjprojects_translate_documentation', 'td_d')
							. ' ON td_d.id = d.id AND ' . $db->quoteName('td_d.language') . ' = ' . $db->quote($default));

					$query->select(array('td_p.title as default_project_title', 'td_p.payment as default_payment'))
						->leftJoin($db->quoteName('#__swjprojects_translate_projects', 'td_p')
							. ' ON td_p.id = p.id AND ' . $db->quoteName('td_p.language') . ' = ' . $db->quote($default));
				}

				// Join over versions for last version
				$subQuery = $db->getQuery(true)
					->select(array('CONCAT(lv.id, ":", lv.alias, "|", lv.major, ".", lv.minor, ".", lv.micro)'))
					->from($db->quoteName('#__swjprojects_versions', 'lv'))
					->where('lv.project_id = p.id')
					->where('lv.state = 1')
					->where($db->quoteName('lv.tag') . ' = ' . $db->quote('stable'))
					->order($db->escape('lv.major') . ' ' . $db->escape('desc'))
					->order($db->escape('lv.minor') . ' ' . $db->escape('desc'))
					->order($db->escape('lv.micro') . ' ' . $db->escape('desc'))
					->setLimit(1);
				$query->select('(' . $subQuery->__toString() . ') as last_version');

				// Join over versions for download counter
				$query->select(array('SUM(dc.downloads) as downloads'))
					->leftJoin($db->quoteName('#__swjprojects_versions', 'dc') . ' ON dc.project_id = p.id'
						. ' AND dc.state = 1');

				// Filter by published state
				$published = $this->getState('filter.published');
				if (is_numeric($published))
				{
					$query->where('d.state = ' . (int) $published)
						->where('p.state = ' . (int) $published)
						->where('c.state = ' . (int) $published);
				}
				elseif (is_array($published))
				{
					$published = ArrayHelper::toInteger($published);
					$published = implode(',', $published);

					$query->where('d.state IN (' . $published . ')')
						->where('p.state IN (' . $published . ')')
						->where('c.state IN (' . $published . ')');
				}

				$data = $db->setQuery($query)->loadObject();

				if (empty($data))
				{
					throw new Exception(Text::_('COM_SWJPROJECTS_ERROR_DOCUMENT_NOT_FOUND'), 404);
				}

				// Set default translates data
				if ($this->translates['current'] != $this->translates['default'])
				{
					$data->title = (empty($data->title)) ? $data->default_title : $data->title;

					$data->project_title = (empty($data->project_title)) ? $data->default_project_title
						: $data->project_title;
				}

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

				// Set link
				$data->slug          = $data->id . ':' . $data->alias;
				$data->pslug         = $data->project_id . ':' . $data->project_alias;
				$data->cslug         = ($data->category) ? $data->category->slug : $data->catid;
				$data->link          = Route::_(SWJProjectsHelperRoute::getDocumentRoute($data->slug, $data->pslug, $data->cslug));
				$data->documentation = Route::_(SWJProjectsHelperRoute::getDocumentationRoute($data->pslug, $data->cslug));

				// Set project
				$data->project                = new stdClass();
				$data->project->id            = $data->project_id;
				$data->project->title         = (!empty($data->project_title)) ? $data->project_title : $data->project_alias;
				$data->project->alias         = $data->project_alias;
				$data->project->elemet        = $data->project_element;
				$data->project->introtext     = nl2br($data->project_introtext);
				$data->project->downloads     = $data->downloads;
				$data->project->urls          = new Registry($data->project_urls);
				$data->project->slug          = $data->pslug;
				$data->project->link          = Route::_(SWJProjectsHelperRoute::getProjectRoute($data->pslug, $data->cslug));
				$data->project->versions      = Route::_(SWJProjectsHelperRoute::getVersionsRoute($data->pslug, $data->cslug));
				$data->project->documentation = Route::_(SWJProjectsHelperRoute::getDocumentationRoute($data->pslug, $data->cslug));
				$data->project->images        = new Registry();
				$data->project->images->set('icon',
					SWJProjectsHelperImages::getImage('projects', $data->project_id, 'icon', $data->project_language));
				$data->project->images->set('cover',
					SWJProjectsHelperImages::getImage('projects', $data->project_id, 'cover', $data->project_language));

				// Set version
				$data->project->version = false;
				if (!empty($data->last_version))
				{
					$data->project->version = new stdClass();
					list($data->project->version->slug, $data->project->version->version) = explode('|', $data->last_version, 2);
					list($data->project->version->id, $data->project->version->alias) = explode(':', $data->project->version->slug, 2);
					$data->project->version->link = Route::_(SWJProjectsHelperRoute::getVersionRoute($data->project->version->slug,
						$data->slug, $data->cslug));
				}

				// Set payment
				$data->payment                = new Registry($data->payment);
				$data->project->download_type = $data->download_type;
				if ($data->project->download_type === 'paid' && $this->translates['current'] != $this->translates['default'])
				{
					$data->project->default_payment = new Registry($data->default_payment);
					if (!$data->payment->get('link'))
					{
						$data->payment->set('link', $data->project->default_payment->get('link'));
					}
					if (!$data->payment->get('price'))
					{
						$data->payment->set('price', $data->project->default_payment->get('price'));
					}
				}

				// Set params
				$params       = new Registry($data->params);
				$data->params = clone $this->getState('params');
				$data->params->merge($params);

				// Set metadata
				$data->metadata = new Registry($data->metadata);
				$data->metadata->set('image',
					SWJProjectsHelperImages::getImage('documentation', $data->id, 'meta', $data->language));

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
	 * @since  1.4.0
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

					$row->slug            = $row->id . ':' . $row->alias;
					$row->link            = Route::_(SWJProjectsHelperRoute::getProjectsRoute($row->slug));
					$categories[$row->id] = $row;
				}
			}
		}

		return $categories;
	}
}
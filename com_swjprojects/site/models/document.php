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
	 * @since  __DEPLOY_VERSION__
	 */
	protected $_context = 'swjprojects.version';

	/**
	 * Category parent object.
	 *
	 * @var  object
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $_categoryParent = null;

	/**
	 * Translates languages.
	 *
	 * @var  array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $translates = null;

	/**
	 * Path to files.
	 *
	 * @var  array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $filesPath = null;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @throws  Exception
	 *
	 * @since  __DEPLOY_VERSION__
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
			'current' => Factory::getLanguage()->getTag(),
			'default' => ComponentHelper::getParams('com_languages')->get('site', 'en-GB'),
		);

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @throws  Exception
	 *
	 * @since  __DEPLOY_VERSION__
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
	 * @since  __DEPLOY_VERSION__
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
					'p.download_type', 'p.urls as project_urls', 'p.joomla'))
					->leftJoin($db->quoteName('#__swjprojects_projects', 'p') . ' ON p.id = d.project_id');

				// Join over the categories
				$query->select(array('c.id as category_id', 'c.alias as category_alias'))
					->leftJoin($db->quoteName('#__swjprojects_categories', 'c') . ' ON c.id = p.catid');

				// Join over current translates
				$current = $this->translates['current'];
				$query->select(array('t_d.*', 'd.id as id'))
					->leftJoin($db->quoteName('#__swjprojects_translate_documentation', 't_d')
						. ' ON t_d.id = d.id AND ' . $db->quoteName('t_d.language') . ' = ' . $db->quote($current));

				$query->select(array('t_p.title as project_title', 't_p.introtext as project_introtext', 't_p.payment',
					't_p.language as project_language'))
					->leftJoin($db->quoteName('#__swjprojects_translate_projects', 't_p')
						. ' ON t_p.id = p.id AND ' . $db->quoteName('t_p.language') . ' = ' . $db->quote($current));

				$query->select(array('t_c.title as category_title'))
					->leftJoin($db->quoteName('#__swjprojects_translate_categories', 't_c')
						. '  ON t_c.id = c.id AND ' . $db->quoteName('t_c.language') . ' = ' . $db->quote($current));

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

					$query->select(array('td_c.title as default_category_title'))
						->leftJoin($db->quoteName('#__swjprojects_translate_categories', 'td_c')
							. ' ON td_c.id = c.id AND ' . $db->quoteName('td_c.language') . ' = ' . $db->quote($default));
				}

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

					$data->category_title = (empty($data->category_title)) ? $data->default_category_title
						: $data->category_title;
				}

				// Set link
				$data->slug          = $data->id . ':' . $data->alias;
				$data->pslug         = $data->project_id . ':' . $data->project_alias;
				$data->cslug         = $data->category_id . ':' . $data->category_alias;
				$data->link          = Route::_(SWJProjectsHelperRoute::getDocumentRoute($data->slug, $data->pslug, $data->cslug));
				$data->documentation = Route::_(SWJProjectsHelperRoute::getDocumentationRoute($data->pslug, $data->cslug));

				// Set project
				$data->project                = new stdClass();
				$data->project->id            = $data->project_id;
				$data->project->title         = (!empty($data->project_title)) ? $data->project_title : $data->project_alias;
				$data->project->alias         = $data->project_alias;
				$data->project->elemet        = $data->project_element;
				$data->project->introtext     = nl2br($data->project_introtext);
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

				// Set category
				$data->category        = new stdClass();
				$data->category->id    = $data->category_id;
				$data->category->title = (!empty($data->category_title)) ? $data->category_title : $data->category_alias;
				$data->category->alias = $data->category_alias;
				$data->category->slug  = $data->cslug;
				$data->category->link  = Route::_(SWJProjectsHelperRoute::getProjectsRoute($data->cslug));

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
	 * @since  __DEPLOY_VERSION__
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
}
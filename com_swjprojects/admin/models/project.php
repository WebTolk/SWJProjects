<?php
/**
 * @package    SW JProjects Component
 * @version    1.1.0
 * @author     Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2018 - 2019 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

class SWJProjectsModelProject extends AdminModel
{
	/**
	 * Site default translate language.
	 *
	 * @var  array
	 *
	 * @since  1.0.0
	 */
	protected $translate = null;

	/**
	 * Constructor.
	 *
	 * @param  array $config An optional associative array of configuration settings.
	 *
	 * @since  1.0.0
	 */
	public function __construct($config = array())
	{
		// Set translate
		$this->translate = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');

		parent::__construct($config);
	}

	/**
	 * Method to get project data.
	 *
	 * @param  integer $pk The id of the project.
	 *
	 * @return  mixed  Project object on success, false on failure.
	 *
	 * @since  1.0.0
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			// Convert the joomla field value to array
			$registry     = new Registry($item->joomla);
			$item->joomla = $registry->toArray();

			// Convert the urls field value to array
			$registry   = new Registry($item->urls);
			$item->urls = $registry->toArray();

			// Convert the relations field value to array
			$registry        = new Registry($item->relations);
			$item->relations = $registry->toArray();

			// Convert the params field value to array
			$registry     = new Registry($item->params);
			$item->params = $registry->toArray();

			// Default values
			$item->translates = array();
			$item->downloads  = 0;

			// Set values
			if (!empty($item->id))
			{
				$db = $this->getDbo();

				// Set translates
				$query = $db->getQuery(true)
					->select('*')
					->from('#__swjprojects_translate_projects')
					->where('id = ' . $item->id);
				$db->setQuery($query);
				$item->translates = $db->loadObjectList('language');

				foreach ($item->translates as &$translate)
				{
					// Convert the images field value to array
					$registry          = new Registry($translate->images);
					$translate->images = $registry->toArray();
				}

				// Set downloads
				$query = $db->getQuery(true)
					->select('SUM(downloads)')
					->from('#__swjprojects_versions')
					->where('project_id = ' . $item->id);
				$db->setQuery($query);
				$item->downloads = $db->loadResult();
			}
		}

		return $item;
	}

	/**
	 * Returns a Table object, always creating it.
	 *
	 * @param  string $type   The table type to instantiate
	 * @param  string $prefix A prefix for the table class name.
	 * @param  array  $config Configuration array for model.
	 *
	 * @return  Table  A database object.
	 *
	 * @since  1.0.0
	 */
	public function getTable($type = 'Projects', $prefix = 'SWJProjectsTable', $config = array())
	{
		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param  Table $table The Table object.
	 *
	 * @since  1.0.0
	 */
	protected function prepareTable($table)
	{
		if (empty($table->id))
		{
			// Set ordering to the last item if not set
			if (empty($table->ordering))
			{
				$db    = $this->getDbo();
				$query = $db->getQuery(true)
					->select('MAX(ordering)')
					->from($db->quoteName('#__swjprojects_projects'));
				$db->setQuery($query);
				$max = $db->loadResult();

				$table->ordering = $max + 1;
			}
		}
	}

	/**
	 * Abstract method for getting the form from the model.
	 *
	 * @param  array   $data     Data for the form.
	 * @param  boolean $loadData True if the form is to load its own data (default case), false if not.
	 *
	 * @throws  Exception
	 *
	 * @return  Form|boolean  A Form object on success, false on failure.
	 *
	 * @since  1.0.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_swjprojects.project', 'project', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}

		// Get item id
		$id = (int) $this->getState('project.id', Factory::getApplication()->input->get('id', 0));

		// Modify the form based on Edit State access controls
		if ($id != 0 && !Factory::getUser()->authorise('core.edit.state', 'com_swjprojects.project.' . $id))
		{
			$form->setFieldAttribute('state', 'disabled', 'true');
			$form->setFieldAttribute('state', 'filter', 'unset');
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @throws  Exception
	 *
	 * @since  1.0.0
	 */
	protected function loadFormData()
	{
		$data = Factory::getApplication()->getUserState('com_swjprojects.edit.project.data', array());
		if (empty($data))
		{
			$data = $this->getItem();
		}
		$this->preprocessData('com_swjprojects.project', $data);

		return $data;
	}

	/**
	 * Method for getting the translate forms from the model.
	 *
	 * @param  boolean $loadData True if the form is to load its own data (default case), false if not.
	 * @param  boolean $clear    Optional argument to force load a new forms.
	 *
	 * @throws  Exception
	 *
	 * @return  array  Translates forms array on success, false on failure.
	 *
	 * @since  1.0.0
	 */
	public function getTranslateForms($loadData = true, $clear = false)
	{
		$languages  = LanguageHelper::getLanguages('lang_code');
		$translates = new Registry();

		// Get data
		if ($loadData)
		{
			$registry   = new Registry($this->loadFormData());
			$translates = new Registry($registry->get('translates'));
		}

		$forms = array();
		$name  = 'com_swjprojects.project';
		$file  = JPATH_COMPONENT . '/models/forms/translate_project.xml';
		if (!File::exists($file))
		{
			throw new RuntimeException('Could not load translate form file', 500);
		}

		foreach ($languages as $code => $language)
		{
			$default = ($code == $this->translate);
			$source  = $name . '_' . str_replace('-', '_', $code);
			$options = array('control' => 'jform[translates][' . $code . ']');

			// Create a signature hash
			$hash = md5($source . serialize($options));

			// Check if we can use a previously loaded form
			if (!$clear && isset($this->_forms[$hash]))
			{
				$forms[$code] = $this->_forms[$hash];

				continue;
			}

			$xml = file_get_contents($file);

			// Set required
			if ($default)
			{
				$xml = str_replace('translate_required', 'required', $xml);
			}

			// Replace translate code
			$xml = str_replace('[translate]', $code, $xml);

			// Load form
			if (!$form = Form::getInstance($source, $xml, $options)) continue;

			// Add fields
			Form::addFieldPath(JPATH_COMPONENT . '/models/fields');

			// Load data
			if ($loadData && !empty($translates->get($code)))
			{
				$formData = $translates->get($code);
			}
			else
			{
				$formData = array();
			}

			// Allow for additional modification of the form, and events to be triggered
			$this->preprocessForm($form, $formData);

			// Load the data into the form after the plugins have operated
			$form->bind($formData);

			$forms[$code]        = $form;
			$this->_forms[$hash] = $form;

		}

		return $forms;
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param  Form   $form  The form to validate against.
	 * @param  array  $data  The data to validate.
	 * @param  string $group The name of the field group to validate.
	 *
	 * @throws  Exception
	 *
	 * @return  array|boolean  Array of filtered data if valid, false otherwise.
	 *
	 * @since  1.0.0
	 */
	public function validate($form, $data, $group = null)
	{
		$translates = (!empty($data['translates'])) ? $data['translates'] : array();

		// Main validate
		if (!$data = parent::validate($form, $data, $group))
		{
			return $data;
		}

		// Translates validate
		$forms = $this->getTranslateForms(false);

		$data['translates'] = array();
		foreach ($forms as $code => $form)
		{
			$translate = (!empty($translates[$code])) ? $translates[$code] : array();

			if (!$validate = parent::validate($form, $translate, $group))
			{
				return $validate;
			}

			$data['translates'][$code] = $validate;
		}

		return $data;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param  array $data The form data.
	 *
	 * @throws  Exception
	 *
	 * @return  boolean  True on success.
	 *
	 * @since  1.0.0
	 */
	public function save($data)
	{
		$pk    = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
		$table = $this->getTable();
		$isNew = true;

		// Load the row if saving an existing item
		if ($pk > 0)
		{
			$table->load($pk);
			$isNew = false;
		}

		// Check element is already exist
		$element      = $data['element'];
		$checkElement = $this->getTable();
		$checkElement->load(array('element' => $element));
		if (!empty($checkElement->id) && ($checkElement->id != $pk || $isNew))
		{
			$element = $this->generateNewElement($element);
			Factory::getApplication()->enqueueMessage(Text::_('COM_SWJPROJECTS_ERROR_ELEMENT_NOT_UNIQUE'), 'warning');
		}
		$data['element'] = $element;

		// Prepare alias field data
		$alias = (!empty($data['alias'])) ? $data['alias'] : $data['translates'][$this->translate]['title'];
		if (Factory::getConfig()->get('unicodeslugs') == 1)
		{
			$alias = OutputFilter::stringURLUnicodeSlug($alias);
		}
		else
		{
			$alias = OutputFilter::stringURLSafe($alias);
		}
		if (empty(($alias))) $alias = Factory::getDate()->toUnix();

		// Check alias is already exist
		$checkAlias = $this->getTable();
		$checkAlias->load(array('alias' => $alias));
		if (!empty($checkAlias->id) && ($checkAlias->id != $pk || $isNew))
		{
			$alias = $this->generateNewAlias($alias);
			Factory::getApplication()->enqueueMessage(Text::_('COM_SWJPROJECTS_ERROR_ALIAS_EXIST'), 'warning');
		}
		$data['alias'] = $alias;

		// Prepare joomla field data
		if (isset($data['joomla']))
		{
			if (!empty($data['joomla']['type']))
			{
				if ($data['joomla']['type'] == 'plugin' || $data['joomla']['type'] == 'package')
				{
					$data['joomla']['client_id'] = 0;
				}
				elseif ($data['joomla']['type'] == 'component' || $data['joomla']['type'] == 'package')
				{
					$data['joomla']['client_id'] = 1;
				}

				$registry       = new Registry($data['joomla']);
				$data['joomla'] = $registry->toString('json', array('bitmask' => JSON_UNESCAPED_UNICODE));
			}
			else
			{
				$data['joomla'] = '';
			}
		}

		// Prepare urls field data
		if (isset($data['urls']))
		{
			$data['urls'] = array_filter($data['urls'], function ($element) {
				return !empty($element);
			});
			$registry     = new Registry($data['urls']);
			$data['urls'] = $registry->toString('json', array('bitmask' => JSON_UNESCAPED_UNICODE));
		}

		// Prepare relations field data
		if (isset($data['relations']))
		{
			foreach ($data['relations'] as $key => $relation)
			{
				if ($relation['project'] < 0 && empty($relation['title']) && empty($relation['link']))
				{
					unset($data['relations'][$key]);
				}
			}

			$registry          = new Registry($data['relations']);
			$data['relations'] = $registry->toString('json', array('bitmask' => JSON_UNESCAPED_UNICODE));
		}

		// Prepare params field data
		if (isset($data['params']))
		{
			$registry       = new Registry($data['params']);
			$data['params'] = $registry->toString('json', array('bitmask' => JSON_UNESCAPED_UNICODE));
		}

		if (parent::save($data))
		{
			$id = $this->getState($this->getName() . '.id');

			// Save translates
			$db    = $this->getDbo();
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__swjprojects_translate_projects'))
				->where('id = ' . $id);
			$db->setQuery($query)->execute();

			foreach ($data['translates'] as $code => $translate)
			{
				// Prepare id field data
				$translate['id'] = $id;

				// Prepare language field data
				$translate['language'] = $code;

				// Prepare images field data
				$registry            = new Registry($translate['images']);
				$translate['images'] = $registry->toString('json', array('bitmask' => JSON_UNESCAPED_UNICODE));

				$translate = (object) $translate;

				$db->insertObject('#__swjprojects_translate_projects', $translate);
			}

			return $id;
		}

		return false;
	}

	/**
	 * Method to generate new element if element already exist.
	 *
	 * @param  string $element The element.
	 *
	 * @throws  Exception
	 *
	 * @return  string  Contains the modified element.
	 *
	 * @since  1.0.0
	 */
	protected function generateNewElement($element)
	{
		$table = $this->getTable();
		while ($table->load(array('element' => $element)))
		{
			$element = StringHelper::increment($element, 'dash');
		}

		return $element;
	}

	/**
	 * Method to generate new alias if alias already exist.
	 *
	 * @param  string $alias The alias.
	 *
	 * @throws  Exception
	 *
	 * @return  string  Contains the modified alias.
	 *
	 * @since  1.0.0
	 */
	protected function generateNewAlias($alias)
	{
		$table = $this->getTable();
		while ($table->load(array('alias' => $alias)))
		{
			$alias = StringHelper::increment($alias, 'dash');
		}

		return $alias;
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param  array &$pks An array of record primary keys.
	 *
	 * @throws  Exception
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since  1.0.0
	 */
	public function delete(&$pks)
	{
		$db = $this->getDbo();

		// Check versions
		$query = $db->getQuery(true)
			->select('project_id')
			->from($db->quoteName('#__swjprojects_versions'))
			->where('project_id IN (' . implode(',', $pks) . ')')
			->group('project_id');
		$db->setQuery($query);
		$versions = ArrayHelper::toInteger($db->loadColumn());

		if ($hasVersions = array_intersect($pks, $versions))
		{
			$pks = array_diff($pks, $versions);
			Factory::getApplication()->enqueueMessage(Text::_('COM_SWJPROJECTS_ERROR_PROJECT_NOT_EMPTY'), 'warning');
		}

		if ($result = parent::delete($pks))
		{
			// Delete translates
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__swjprojects_translate_projects'))
				->where('id IN (' . implode(',', $pks) . ')');
			$db->setQuery($query)->execute();
		}

		return $result;
	}
}
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

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Nested;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

class SWJProjectsModelCategory extends AdminModel
{
	/**
	 * Method to get category data.
	 *
	 * @param   integer  $pk  The id of the category.
	 *
	 * @return  mixed  Category object on success, false on failure.
	 *
	 * @since  1.0.0
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			// Convert the params field value to array
			$registry     = new Registry($item->params);
			$item->params = $registry->toArray();

			$item->translates = array();
			if (!empty($item->id))
			{
				$db    = $this->getDbo();
				$query = $db->getQuery(true)
					->select('*')
					->from('#__swjprojects_translate_categories')
					->where('id = ' . $item->id);
				$db->setQuery($query);
				$item->translates = $db->loadObjectList('language');

				if (!empty($item->translates))
				{
					foreach ($item->translates as &$translate)
					{
						// Convert the metadata field value to array
						$registry            = new Registry($translate->metadata);
						$translate->metadata = $registry->toArray();
					}
				}
			}
		}

		return $item;
	}

	/**
	 * Returns a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name.
	 * @param   array   $config  Configuration array for model.
	 *
	 * @return  Table|Nested   A database object.
	 *
	 * @since  1.0.0
	 */
	public function getTable($type = 'Categories', $prefix = 'SWJProjectsTable', $config = array())
	{
		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Abstract method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @throws  Exception
	 *
	 * @return  Form|boolean  A Form object on success, false on failure.
	 *
	 * @since  1.0.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_swjprojects.category', 'category', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}

		// Get item id
		$id = (int) $this->getState('category.id', Factory::getApplication()->input->get('id', 0));

		// Modify the form based on Edit State access controls
		if ($id != 0 && !Factory::getUser()->authorise('core.edit.state', 'com_swjprojects.category.' . $id))
		{
			$form->setFieldAttribute('state', 'disabled', 'true');
			$form->setFieldAttribute('state', 'filter', 'unset');
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @throws  Exception
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since  1.0.0
	 */
	protected function loadFormData()
	{
		$data = Factory::getApplication()->getUserState('com_swjprojects.edit.category.data', array());
		if (empty($data))
		{
			$data = $this->getItem();
		}
		$this->preprocessData('com_swjprojects.category', $data);

		return $data;
	}

	/**
	 * Method for getting the translate forms from the model.
	 *
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 * @param   boolean  $clear     Optional argument to force load a new forms.
	 *
	 * @throws  Exception
	 *
	 * @return  array  Translates forms array on success, false on failure.
	 *
	 * @since  1.0.0
	 */
	public function getTranslateForms($loadData = true, $clear = false)
	{
		$translates = new Registry();

		// Get data
		if ($loadData)
		{
			$registry   = new Registry($this->loadFormData());
			$translates = new Registry($registry->get('translates'));
		}

		$forms = array();
		$name  = 'com_swjprojects.category';
		$file  = JPATH_COMPONENT . '/models/forms/translate_category.xml';
		if (!File::exists($file))
		{
			throw new RuntimeException('Could not load translate form file', 500);
		}

		foreach (SWJProjectsHelperTranslation::getCodes() as $code)
		{
			$default = ($code == SWJProjectsHelperTranslation::getDefault());
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
	 * @param   Form    $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
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
	 * @param   array  $data  The form data.
	 *
	 * @throws  Exception
	 *
	 * @return  boolean  True on success.
	 *
	 * @since  1.0.0
	 */
	public function save($data)
	{
		$app     = Factory::getApplication();
		$pk      = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
		$table   = $this->getTable();
		$isNew   = true;
		$context = $this->option . '.' . $this->name;

		// Include plugins for save events
		PluginHelper::importPlugin($this->events_map['save']);

		// Load the row if saving an existing item
		if ($pk > 0)
		{
			$table->load($pk);
			$isNew = false;
		}

		// Set new parent id if parent id not matched OR while New
		if ($table->parent_id != $data['parent_id'] || $data['id'] == 0)
		{
			$table->setLocation($data['parent_id'], 'last-child');
		}

		// Prepare alias field data
		$alias = (!empty($data['alias'])) ? $data['alias'] : $data['translates'][SWJProjectsHelperTranslation::getDefault()]['title'];
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
		$checkAlias->load(array('alias' => $alias, 'parent_id' => $data['parent_id']));
		if (!empty($checkAlias->id) && ($checkAlias->id != $pk || $isNew))
		{
			$alias = $this->generateNewAlias($alias, $data['parent_id']);
			$app->enqueueMessage(Text::_('COM_SWJPROJECTS_ERROR_ALIAS_EXIST'), 'warning');
		}
		$data['alias'] = $alias;

		// Prepare params field data
		if (isset($data['params']))
		{
			$registry       = new Registry($data['params']);
			$data['params'] = $registry->toString('json', array('bitmask' => JSON_UNESCAPED_UNICODE));
		}

		// Bind data
		if (!$table->bind($data))
		{
			$this->setError($table->getError());

			return false;
		}

		// Check data
		if (!$table->check())
		{
			$this->setError($table->getError());

			return false;
		}

		// Trigger before save event
		$result = $app->triggerEvent($this->event_before_save, array($context, &$table, $isNew, $data));
		if (in_array(false, $result, true))
		{
			$this->setError($table->getError());

			return false;
		}

		// Store data
		if (!$table->store())
		{
			$this->setError($table->getError());

			return false;
		}

		// Trigger after save event
		$app->triggerEvent($this->event_after_save, array($context, &$table, $isNew, $data));

		// Rebuild path
		if (!$table->rebuildPath($table->id))
		{
			$this->setError($table->getError());

			return false;
		}

		// Rebuild children paths
		if (!$table->rebuild($table->id, $table->lft, $table->level, $table->path))
		{
			$this->setError($table->getError());

			return false;
		}

		// Set id state
		$id = $table->id;
		$this->setState($this->getName() . '.id', $id);

		// Save translates
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__swjprojects_translate_categories'))
			->where('id = ' . $id);
		$db->setQuery($query)->execute();

		foreach ($data['translates'] as $code => $translate)
		{
			// Prepare id field data
			$translate['id'] = $id;

			// Prepare language field data
			$translate['language'] = $code;

			// Prepare metadata field data
			if (isset($translate['metadata']))
			{
				$registry              = new Registry($translate['metadata']);
				$translate['metadata'] = $registry->toString('json', array('bitmask' => JSON_UNESCAPED_UNICODE));
			}

			$translate = (object) $translate;

			$db->insertObject('#__swjprojects_translate_categories', $translate);
		}

		// Clear cache
		$this->cleanCache();

		return $id;
	}

	/**
	 * Method to generate new alias if alias already exist.
	 *
	 * @param   string   $alias      The alias.
	 * @param   integer  $parent_id  The parent category id.
	 *
	 * @return  string  Contains the modified alias.
	 *
	 * @since  1.0.0
	 */
	protected function generateNewAlias($alias, $parent_id)
	{
		$table = $this->getTable();
		while ($table->load(array('alias' => $alias, 'parent_id' => $parent_id)))
		{
			$alias = StringHelper::increment($alias, 'dash');
		}

		return $alias;
	}

	/**
	 * Method to save the reordered nested set tree.
	 *
	 * @param   array    $idArray    An array of primary key ids.
	 * @param   integer  $lft_array  The lft value
	 *
	 * @throws  Exception
	 *
	 * @return  boolean  False on failure or error, True otherwise.
	 *
	 * @since  1.0.0
	 */
	public function saveorder($idArray = null, $lft_array = null)
	{
		$table = $this->getTable();
		if (!$table->saveorder($idArray, $lft_array))
		{
			$this->setError($table->getError());

			return false;
		}

		$this->cleanCache();

		return true;
	}

	/**
	 * Method rebuild the entire nested set tree.
	 *
	 * @return  boolean  False on failure or error, true otherwise.
	 *
	 * @since  1.0.0
	 */
	public function rebuild()
	{
		$table = $this->getTable();
		if (!$table->rebuild())
		{
			$this->setError($table->getError());

			return false;
		}

		$this->cleanCache();

		return true;
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   array &$pks  An array of record primary keys.
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

		// Check projects
		$query = $db->getQuery(true)
			->select('catid')
			->from($db->quoteName('#__swjprojects_projects'))
			->where('catid IN (' . implode(',', $pks) . ')')
			->group('catid');
		$db->setQuery($query);
		$projects = ArrayHelper::toInteger($db->loadColumn());

		if ($hasProjects = array_intersect($pks, $projects))
		{
			$pks = array_diff($pks, $projects);
			Factory::getApplication()->enqueueMessage(Text::_('COM_SWJPROJECTS_ERROR_CATEGORY_NOT_EMPTY'), 'warning');
		}

		if ($result = parent::delete($pks))
		{
			// Delete translates
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__swjprojects_translate_categories'))
				->where('id IN (' . implode(',', $pks) . ')');
			$db->setQuery($query)->execute();
		}

		return $result;
	}
}
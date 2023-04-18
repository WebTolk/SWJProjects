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
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Version;
use Joomla\Registry\Registry;

class SWJProjectsModelVersion extends AdminModel
{
	/**
	 * Project object.
	 *
	 * @var  object
	 *
	 * @since  1.0.0
	 */
	protected $_project = null;

	/**
	 * Method to get version data.
	 *
	 * @param   integer  $pk  The id of the version.
	 *
	 * @return  mixed  Version object on success, false on failure.
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

			$item->file       = false;
			$item->translates = array();
			if (!empty($item->id))
			{
				$db    = Factory::getDbo();
				$query = $db->getQuery(true)
					->select('*')
					->from('#__swjprojects_translate_versions')
					->where('id = ' . $item->id);
				$db->setQuery($query);
				$item->translates = $db->loadObjectList('language');

				if (!empty($item->translates))
				{
					foreach ($item->translates as &$translate)
					{
						// Convert the changelog field value to array
						$registry             = new Registry($translate->changelog);
						$translate->changelog = $registry->toArray();

						// Convert the metadata field value to array
						$registry            = new Registry($translate->metadata);
						$translate->metadata = $registry->toArray();
					}
				}

				// Check file
				$path       = ComponentHelper::getParams('com_swjprojects')->get('files_folder') .
					'/versions/' . $item->id;
				$item->file = (!empty(Folder::files($path, 'download', false)));
			}
		}

		return $item;
	}

	/**
	 * Method to get project data.
	 *
	 * @param   integer  $pk  The id of the project.
	 *
	 * @return  mixed  Project object on success, false on failure.
	 *
	 * @since  1.0.0
	 */
	public function getProject($pk = null)
	{
		if (empty($pk)) return false;

		if ($this->_project === null)
		{
			$this->_project = array();
		}

		if (!isset($this->_project[$pk]))
		{
			$model   = self::getInstance('Project', 'SWJProjectsModel', array('ignore_request' => true));
			$project = $model->getItem($pk);

			$this->_project[$pk] = ($project->id !== null) ? $project : false;
		}

		return $this->_project[$pk];
	}

	/**
	 * Returns a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name.
	 * @param   array   $config  Configuration array for model.
	 *
	 * @return  Table  A database object.
	 *
	 * @since  1.0.0
	 */
	public function getTable($type = 'Versions', $prefix = 'SWJProjectsTable', $config = array())
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
		$form = $this->loadForm('com_swjprojects.version', 'version', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}

		// Get item id
		$id = (int) $this->getState('version.id', Factory::getApplication()->input->get('id', 0));

		// Modify the form based on Edit State access controls
		if ($id != 0 && !Factory::getUser()->authorise('core.edit.state', 'com_swjprojects.version.' . $id))
		{
			$form->setFieldAttribute('state', 'disabled', 'true');
			$form->setFieldAttribute('state', 'filter', 'unset');
		}

		if ((new Version())->isCompatible('4.0'))
		{
			$form->setFieldAttribute('joomla_version', 'type', 'text');
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
		$data = Factory::getApplication()->getUserState('com_swjprojects.edit.version.data', array());
		if (empty($data))
		{
			$data = $this->getItem();
		}
		$this->preprocessData('com_swjprojects.version', $data);

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
		$name  = 'com_swjprojects.version';
		$file  = JPATH_COMPONENT . '/models/forms/translate_version.xml';
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
		// Main validate
		$translates = (!empty($data['translates'])) ? $data['translates'] : array();
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
		$pk    = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
		$table = $this->getTable();
		$isNew = true;

		// Load the row if saving an existing item
		if ($pk > 0)
		{
			$table->load($pk);
			$isNew = false;
		}

		// Check version is already exist
		$checkVersion = $this->getTable();
		$checkVersion->load(array(
			'project_id' => $data['project_id'],
			'major'      => $data['major'],
			'minor'      => $data['minor'],
			'micro'      => $data['micro'],
			'tag'        => $data['tag'],
			'stage'      => $data['stage'],
		));
		if (!empty($checkVersion->id) && ($checkVersion->id != $pk || $isNew))
		{
			$this->setError(Text::_('COM_SWJPROJECTS_ERROR_VERSION_EXIST'));

			return false;
		}

		// Prepare stability field data
		$data['stability'] = array_search($data['tag'], array('dev', 'alpha', 'beta', 'rc', 'stable'));

		// Prepare stage field data
		$data['stage'] = ($data['tag'] !== 'stable' && $data['tag'] !== 'dev') ? $data['stage'] : 0;

		// Prepare alias field data
		$alias = $data['major'] . '-' . $data['minor'] . '-' . $data['micro'];
		if ($data['tag'] !== 'stable')
		{
			$alias .= '-' . $data['tag'];
			if ($data['tag'] !== 'dev' && !empty($data['stage']))
			{
				$alias .= '-' . $data['stage'];
			}
		}
		if (Factory::getConfig()->get('unicodeslugs') == 1)
		{
			$alias = OutputFilter::stringURLUnicodeSlug($alias);
		}
		else
		{
			$alias = OutputFilter::stringURLSafe($alias);
		}
		$data['alias'] = $alias;

		// Prepare date field data
		if (empty($data['date']))
		{
			$data['date'] = Factory::getDate()->toSql();
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
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__swjprojects_translate_versions'))
				->where('id = ' . $id);
			$db->setQuery($query)->execute();

			foreach ($data['translates'] as $code => $translate)
			{
				// Prepare id field data
				$translate['id'] = $id;

				// Prepare language field data
				$translate['language'] = $code;

				// Prepare changelog field data
				if (isset($translate['changelog']))
				{
					$registry               = new Registry($translate['changelog']);
					$translate['changelog'] = $registry->toString('json', array('bitmask' => JSON_UNESCAPED_UNICODE));
				}

				// Prepare metadata field data
				if (isset($translate['metadata']))
				{
					$registry              = new Registry($translate['metadata']);
					$translate['metadata'] = $registry->toString('json', array('bitmask' => JSON_UNESCAPED_UNICODE));
				}

				$translate = (object) $translate;

				$db->insertObject('#__swjprojects_translate_versions', $translate);
			}

			// Check file folder
			$path = ComponentHelper::getParams('com_swjprojects')->get('files_folder') . '/versions/' . $id;
			if (!Folder::exists($path))
			{
				Folder::create($path);
			}

			// Remove old files
			$files = Folder::files($path, 'download', false, true);
			if ((!empty($data['file_upload']['tmp_name']) || !empty($data['file_delete'])) && !empty($files))
			{
				foreach ($files as $file)
				{
					File::delete($file);
				}
			}

			// Upload new file
			$file = $data['file_upload'];
			if (!empty($file['tmp_name']))
			{
				$name = 'download.' . File::getExt($file['name']);
				File::upload($file['tmp_name'], $path . '/' . $name, false, true);
			}

			return $id;
		}

		return false;
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
		if ($result = parent::delete($pks))
		{
			// Delete translates
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__swjprojects_translate_versions'))
				->where('id IN (' . implode(',', $pks) . ')');
			$db->setQuery($query)->execute();

			// Delete files
			$root = ComponentHelper::getParams('com_swjprojects')->get('files_folder') . '/versions';
			foreach ($pks as $pk)
			{
				$path = $root . '/' . $pk;
				if (Folder::exists($path))
				{
					Folder::delete($path);
				}
			}
		}

		return $result;
	}
}
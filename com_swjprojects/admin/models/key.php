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
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;

class SWJProjectsModelKey extends AdminModel
{
	/**
	 * Method to get key data.
	 *
	 * @param   integer  $pk  The id of the key.
	 *
	 * @return  mixed  Key object on success, false on failure.
	 *
	 * @since  1.3.0
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			// Convert the projects field value to array
			$item->projects = !empty($item->projects) ? explode(',', $item->projects) : [];

			// Convert the params field value to array
			$registry      = new Registry($item->plugins);
			$item->plugins = $registry->toArray();
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
	 * @return  Table  A database object.
	 *
	 * @since  1.3.0
	 */
	public function getTable($type = 'Keys', $prefix = 'SWJProjectsTable', $config = array())
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
	 * @since  1.3.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_swjprojects.key', 'key', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}

		// Get item id
		$id = (int) $this->getState('key.id', Factory::getApplication()->input->get('id', 0));

		// Modify the form based on Edit State access controls
		if ($id != 0 && !Factory::getUser()->authorise('core.edit.state', 'com_swjprojects.key.' . $id))
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
	 * @since  1.3.0
	 */
	protected function loadFormData()
	{
		$data = Factory::getApplication()->getUserState('com_swjprojects.edit.key.data', array());
		if (empty($data))
		{
			$data = $this->getItem();
		}
		$this->preprocessData('com_swjprojects.key', $data);

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
	 * @since  1.3.0
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

		// Prepare projects field data
		if (isset($data['projects'])) $data['projects'] = implode(',', $data['projects']);

		// Prepare key field data
		if ($isNew || $data['key_regenerate'] || empty($data['key']))
		{
			$data['key'] = $this->generateNewKey();
		}
		else
		{
			unset($data['key']);
		}

		// Prepare date_start field data
		if ((isset($data['date_start']) || $isNew) && empty($data['date_start']))
		{
			$data['date_start'] = Factory::getDate()->toSql();
		}

		// Prepare date_end field data
		if (isset($data['date_end']) && empty($data['date_end']))
		{
			$data['date_end'] = $this->getDbo()->getNullDate();
		}

		// Prepare plugins field data
		if (isset($data['plugins']))
		{
			$registry        = new Registry($data['plugins']);
			$data['plugins'] = $registry->toString('json', array('bitmask' => JSON_UNESCAPED_UNICODE));
		}

		if (parent::save($data))
		{
			$id = $this->getState($this->getName() . '.id');

			return $id;
		}

		return false;
	}

	/**
	 * Method to generate new key.
	 *
	 * @throws  Exception
	 *
	 * @return  string  New key value.
	 *
	 * @since  1.3.0
	 */
	protected function generateNewKey()
	{
		$key   = SWJProjectsHelperKeys::generateKey();
		$table = $this->getTable();
		while ($table->load(array('key' => $key)))
		{
			$key = SWJProjectsHelperKeys::generateKey();
		}

		return $key;
	}
}
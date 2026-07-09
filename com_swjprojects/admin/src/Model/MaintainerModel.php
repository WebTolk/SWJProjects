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

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\Component\SWJProjects\Administrator\Helper\MaintainerLinksHelper;
use Joomla\Component\SWJProjects\Administrator\Helper\TranslationHelper;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;
use function array_diff;
use function array_filter;
use function array_intersect;
use function filter_var;
use function file_get_contents;
use function implode;
use function is_file;
use function json_encode;
use function md5;
use function serialize;
use function str_replace;
use const JSON_UNESCAPED_UNICODE;
use const FILTER_VALIDATE_EMAIL;
use const FILTER_VALIDATE_URL;

class MaintainerModel extends AdminModel
{
	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed
	 *
	 * @throws  \Exception
	 *
	 * @since  2.6.2
	 */
	protected function loadFormData()
	{
		$data = Factory::getApplication()->getUserState('com_swjprojects.edit.maintainer.data', []);

		if (empty($data))
		{
			$data = $this->getItem();
		}

		$this->preprocessData('com_swjprojects.maintainer', $data);

		return $data;
	}

	/**
	 * Abstract method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data.
	 *
	 * @return  Form|boolean
	 *
	 * @throws  \Exception
	 *
	 * @since  2.6.2
	 */
	public function getForm($data = [], $loadData = true)
	{
		$app  = Factory::getApplication();
		$form = $this->loadForm('com_swjprojects.maintainer', 'maintainer', ['control' => 'jform', 'load_data' => $loadData]);

		if (empty($form))
		{
			return false;
		}

		$id = (int) $this->getState('maintainer.id', $app->getInput()->get('id', 0));

		if ($id !== 0 && !$app->getIdentity()->authorise('core.edit.state', 'com_swjprojects.maintainer.' . $id))
		{
			$form->setFieldAttribute('state', 'disabled', 'true');
			$form->setFieldAttribute('state', 'filter', 'unset');
		}

		return $form;
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   Form    $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  array|boolean
	 *
	 * @throws  \Exception
	 *
	 * @since  2.6.2
	 */
	public function validate($form, $data, $group = null)
	{
		$translates = !empty($data['translates']) ? $data['translates'] : [];

		if (!$data = parent::validate($form, $data, $group))
		{
			return $data;
		}

		if (!$this->validateLinks($data))
		{
			return false;
		}

		$forms = $this->getTranslateForms(false);
		$data['translates'] = [];

		foreach ($forms as $code => $translateForm)
		{
			$translate = !empty($translates[$code]) ? $translates[$code] : [];

			if (!$validate = parent::validate($translateForm, $translate, $group))
			{
				return $validate;
			}

			$data['translates'][$code] = $validate;
		}

		return $data;
	}

	/**
	 * Method for getting the translate forms from the model.
	 *
	 * @param   boolean  $loadData  True if the form is to load its own data.
	 * @param   boolean  $clear     Optional argument to force load new forms.
	 *
	 * @return  array
	 *
	 * @throws  \Exception
	 *
	 * @since  2.6.2
	 */
	public function getTranslateForms($loadData = true, $clear = false)
	{
		$translates = new Registry();

		if ($loadData)
		{
			$registry   = new Registry($this->loadFormData());
			$translates = new Registry($registry->get('translates'));
		}

		$forms = [];
		$name  = 'com_swjprojects.maintainer';
		$file  = JPATH_COMPONENT . '/forms/translate_maintainer.xml';

		if (!is_file($file))
		{
			throw new \RuntimeException('Could not load translate form file', 500);
		}

		foreach (TranslationHelper::getCodes() as $code)
		{
			$default = ($code === TranslationHelper::getDefault());
			$source  = $name . '_' . str_replace('-', '_', $code);
			$options = ['control' => 'jform[translates][' . $code . ']'];
			$hash    = md5($source . serialize($options));

			if (!$clear && isset($this->_forms[$hash]))
			{
				$forms[$code] = $this->_forms[$hash];
				continue;
			}

			$xml = file_get_contents($file);

			if ($default)
			{
				$xml = str_replace('translate_required', 'required', $xml);
			}

			$xml = str_replace('[translate]', $code, $xml);

			if (!$form = Form::getInstance($source, $xml, $options))
			{
				continue;
			}

			Form::addFieldPath(JPATH_COMPONENT . '/models/fields');

			$formData = $loadData && !empty($translates->get($code)) ? $translates->get($code) : [];

			$this->preprocessForm($form, $formData);
			$form->bind($formData);

			$forms[$code]        = $form;
			$this->_forms[$hash] = $form;
		}

		return $forms;
	}

	/**
	 * Method to get maintainer data.
	 *
	 * @param   integer  $pk  The id of the maintainer.
	 *
	 * @return  mixed
	 *
	 * @since  2.6.2
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			$registry     = new Registry($item->links);
			$item->links  = $registry->toArray();
			$registry     = new Registry($item->params);
			$item->params = $registry->toArray();

			$item->translates = [];

			if (!empty($item->id))
			{
				$db = $this->getDatabase();
				$query = $db->getQuery(true)
					->select('*')
					->from('#__swjprojects_translate_maintainers')
					->where('id = ' . (int) $item->id);
				$db->setQuery($query);
				$item->translates = $db->loadObjectList('language');

				foreach ($item->translates as &$translate)
				{
					$registry            = new Registry($translate->metadata);
					$translate->metadata = $registry->toArray();
				}
			}
		}

		return $item;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean|integer
	 *
	 * @throws  \Exception
	 *
	 * @since  2.6.2
	 */
	public function save($data)
	{
		$pk    = !empty($data['id']) ? (int) $data['id'] : (int) $this->getState($this->getName() . '.id');
		$table = $this->getTable();
		$isNew = true;

		if ($pk > 0)
		{
			$table->load($pk);
			$isNew = false;
		}

		$alias = !empty($data['alias']) ? $data['alias'] : $data['translates'][TranslationHelper::getDefault()]['title'];

		if (Factory::getContainer()->get('config')->get('unicodeslugs') == 1)
		{
			$alias = OutputFilter::stringURLUnicodeSlug($alias);
		}
		else
		{
			$alias = OutputFilter::stringURLSafe($alias);
		}

		if (empty($alias))
		{
			$alias = Factory::getDate()->toUnix();
		}

		$checkAlias = $this->getTable();
		$checkAlias->load(['alias' => $alias]);

		if (!empty($checkAlias->id) && ($checkAlias->id != $pk || $isNew))
		{
			$alias = $this->generateNewAlias($alias);
			Factory::getApplication()->enqueueMessage(Text::_('COM_SWJPROJECTS_ERROR_ALIAS_EXIST'), 'warning');
		}

		$data['alias'] = $alias;

		if (isset($data['links']))
		{
			$data['links'] = array_values(array_filter($data['links'], static function ($link) {
				return !empty($link['type']) || !empty($link['title']) || !empty($link['value']);
			}));
			$data['links'] = json_encode($data['links'], JSON_UNESCAPED_UNICODE);
		}

		if (isset($data['params']))
		{
			$registry       = new Registry($data['params']);
			$data['params'] = $registry->toString('json', ['bitmask' => JSON_UNESCAPED_UNICODE]);
		}

		if (parent::save($data))
		{
			$id = (int) $this->getState($this->getName() . '.id');
			$db = $this->getDatabase();

			$query = $db->getQuery(true)
				->delete($db->quoteName('#__swjprojects_translate_maintainers'))
				->where('id = ' . $id);
			$db->setQuery($query)->execute();

			foreach ($data['translates'] as $code => $translate)
			{
				$translate['id']       = $id;
				$translate['language'] = $code;

				if (isset($translate['metadata']))
				{
					$registry              = new Registry($translate['metadata']);
					$translate['metadata'] = $registry->toString('json', ['bitmask' => JSON_UNESCAPED_UNICODE]);
				}

				$translateRow = (object) $translate;
				$db->insertObject('#__swjprojects_translate_maintainers', $translateRow);
			}

			return $id;
		}

		return false;
	}

	/**
	 * Returns a Table object, always creating it.
	 *
	 * @param   string  $name     The table type to instantiate.
	 * @param   string  $prefix   A prefix for the table class name.
	 * @param   array   $options  Configuration array for the model.
	 *
	 * @return  Table
	 *
	 * @since  2.6.2
	 */
	public function getTable($name = 'Maintainers', $prefix = 'Administrator', $options = [])
	{
		return parent::getTable($name, $prefix, $options);
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   array  &$pks  An array of record primary keys.
	 *
	 * @return  boolean
	 *
	 * @throws  \Exception
	 *
	 * @since  2.6.2
	 */
	public function delete(&$pks)
	{
		$db = $this->getDatabase();

		$query = $db->getQuery(true)
			->select('maintainer_id')
			->from($db->quoteName('#__swjprojects_projects'))
			->where('maintainer_id IN (' . implode(',', $pks) . ')')
			->group('maintainer_id');
		$db->setQuery($query);
		$projects = ArrayHelper::toInteger($db->loadColumn());

		if ($hasProjects = array_intersect($pks, $projects))
		{
			$pks = array_diff($pks, $projects);
			Factory::getApplication()->enqueueMessage(Text::_('COM_SWJPROJECTS_ERROR_MAINTAINER_NOT_EMPTY'), 'warning');
		}

		if (empty($pks))
		{
			return false;
		}

		if ($result = parent::delete($pks))
		{
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__swjprojects_translate_maintainers'))
				->where('id IN (' . implode(',', $pks) . ')');
			$db->setQuery($query)->execute();
		}

		return $result;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   Table  $table  The Table object.
	 *
	 * @since  2.6.2
	 */
	protected function prepareTable($table)
	{
		$userId = Factory::getApplication()->getIdentity()->id;
		$date   = Factory::getDate()->toSql();

		if (empty($table->id))
		{
			if (empty($table->ordering))
			{
				$db    = $this->getDatabase();
				$query = $db->getQuery(true)
					->select('MAX(ordering)')
					->from($db->quoteName('#__swjprojects_maintainers'));
				$db->setQuery($query);
				$max = (int) $db->loadResult();

				$table->ordering = $max + 1;
			}

			$table->created    = $date;
			$table->created_by = $userId;
		}
		else
		{
			$table->modified    = $date;
			$table->modified_by = $userId;
		}
	}

	/**
	 * Validate maintainer links against component-level link types.
	 *
	 * @param   array  &$data  Validated form data.
	 *
	 * @return  boolean
	 *
	 * @since  2.6.2
	 */
	protected function validateLinks(array &$data): bool
	{
		if (empty($data['links']))
		{
			return true;
		}

		$types = MaintainerLinksHelper::getTypes();
		$links = [];

		foreach ($data['links'] as $link)
		{
			$type  = (string) ($link['type'] ?? '');
			$title = (string) ($link['title'] ?? '');
			$value = trim((string) ($link['value'] ?? ''));

			if ($type === '' && $title === '' && $value === '')
			{
				continue;
			}

			if ($type === '' || empty($types[$type]))
			{
				Factory::getApplication()->enqueueMessage(Text::_('COM_SWJPROJECTS_ERROR_MAINTAINER_LINK_TYPE_REQUIRED'), 'error');

				return false;
			}

			if ($value === '')
			{
				Factory::getApplication()->enqueueMessage(Text::_('COM_SWJPROJECTS_ERROR_MAINTAINER_LINK_VALUE_REQUIRED'), 'error');

				return false;
			}

			if ($types[$type]['value_type'] === 'email')
			{
				if (!filter_var($value, FILTER_VALIDATE_EMAIL))
				{
					Factory::getApplication()->enqueueMessage(Text::_('COM_SWJPROJECTS_ERROR_MAINTAINER_LINK_EMAIL_INVALID'), 'error');

					return false;
				}
			}
			elseif (!filter_var($value, FILTER_VALIDATE_URL))
			{
				Factory::getApplication()->enqueueMessage(Text::_('COM_SWJPROJECTS_ERROR_MAINTAINER_LINK_URL_INVALID'), 'error');

				return false;
			}

			$links[] = [
				'type'  => $type,
				'title' => $title,
				'value' => $value,
			];
		}

		$data['links'] = $links;

		return true;
	}

	/**
	 * Method to generate new alias if alias already exists.
	 *
	 * @param   string  $alias  The alias.
	 *
	 * @return  string
	 *
	 * @throws  \Exception
	 *
	 * @since  2.6.2
	 */
	protected function generateNewAlias($alias)
	{
		$table = $this->getTable();

		while ($table->load(['alias' => $alias]))
		{
			$alias = StringHelper::increment($alias, 'dash');
		}

		return $alias;
	}
}

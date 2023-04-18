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
use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('list');

class JFormFieldDocumentation extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 *
	 * @since  1.4.0
	 */
	protected $type = 'documentation';

	/**
	 * Field options.
	 *
	 * @var  array
	 *
	 * @since  1.4.0
	 */
	protected $_options = null;

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since  1.4.0
	 */
	protected function getOptions()
	{
		if ($this->_options === null)
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select(array('d.id', 'd.alias'))
				->from($db->quoteName('#__swjprojects_documentation', 'd'));

			// Join over translates
			JLoader::register('SWJProjectsHelperTranslation',
				JPATH_ADMINISTRATOR . '/components/com_swjprojects/helpers/translation.php');
			$translate = SWJProjectsHelperTranslation::getDefault();
			$query->select(array('t_d.title as title'))
				->leftJoin($db->quoteName('#__swjprojects_translate_documentation', 't_d')
					. ' ON t_d.id = d.id AND ' . $db->quoteName('t_d.language') . ' = ' . $db->quote($translate));
			// Group by
			$query->group(array('d.id'));

			// Add the list ordering clause
			$query->order($db->escape('d.ordering') . ' ' . $db->escape('asc'));

			$items = $db->setQuery($query)->loadObjectList('id');

			// Prepare options
			$options = parent::getOptions();
			foreach ($items as $i => $item)
			{
				$option        = new stdClass();
				$option->value = $item->id;
				$option->text  = (!empty($item->title)) ? $item->title : $item->alias;

				// Add option
				$option        = new stdClass();
				$option->value = $item->id;
				$option->text  = $item->title;

				$options[] = $option;
			}

			$this->_options = $options;
		}

		return $this->_options;
	}
}
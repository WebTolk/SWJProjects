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

class JFormFieldProjects extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 *
	 * @since  1.0.0
	 */
	protected $type = 'projects';

	/**
	 * Field options array.
	 *
	 * @var  array
	 *
	 * @since  1.0.0
	 */
	protected $_options = null;

	/**
	 * Method to get the field options.
	 *
	 * @throws  Exception
	 *
	 * @return  array  The field option objects.
	 *
	 * @since  1.0.0
	 */
	protected function getOptions()
	{
		if ($this->_options === null)
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select(array('p.id', 'p.element'))
				->from($db->quoteName('#__swjprojects_projects', 'p'));

			// Join over translates
			JLoader::register('SWJProjectsHelperTranslation',
				JPATH_ADMINISTRATOR . '/components/com_swjprojects/helpers/translation.php');
			$translate = SWJProjectsHelperTranslation::getDefault();
			$query->select(array('t_p.title as title'))
				->leftJoin($db->quoteName('#__swjprojects_translate_projects', 't_p')
					. ' ON t_p.id = p.id AND ' . $db->quoteName('t_p.language') . ' = ' . $db->quote($translate));

			// Group by
			$query->group(array('p.id'));

			// Add the list ordering clause
			$query->order($db->escape('p.ordering') . ' ' . $db->escape('asc'));

			$items = $db->setQuery($query)->loadObjectList('id');

			// Check admin type view
			$app       = Factory::getApplication();
			$component = $app->input->get('option', 'com_swjprojects');
			$view      = $app->input->get('view', 'project');
			$id        = $app->input->getInt('id', 0);
			$sameView  = ($app->isClient('administrator') && $component == 'com_swjprojects' && $view == 'project');

			// Prepare options
			$options = parent::getOptions();
			foreach ($items as $i => $item)
			{
				$option          = new stdClass();
				$option->value   = $item->id;
				$option->text    = (!empty($item->title)) ? $item->title : $item->element;
				$option->disable = ($sameView && $item->id == $id);

				$options[] = $option;
			}

			$this->_options = $options;
		}

		return $this->_options;
	}
}
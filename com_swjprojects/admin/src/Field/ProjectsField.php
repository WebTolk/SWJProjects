<?php
/*
 * @package    SW JProjects
 * @version    2.1.0
 * @author     Sergey Tolkachyov
 * @сopyright  Copyright (c) 2018 - 2024 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
 */

namespace Joomla\Component\SWJProjects\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\Component\SWJProjects\Administrator\Helper\TranslationHelper;
use Joomla\Database\DatabaseInterface;

class ProjectsField extends ListField
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
	 * @throws  \Exception
	 *
	 * @return  array  The field option objects.
	 *
	 * @since  1.0.0
	 */
	protected function getOptions()
	{
		if ($this->_options === null)
		{
			$db    = Factory::getContainer()->get(DatabaseInterface::class);
			$query = $db->getQuery(true)
				->select(array('p.id', 'p.element'))
				->from($db->quoteName('#__swjprojects_projects', 'p'));

			// Join over translates
			$translate = TranslationHelper::getDefault();
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
			$component = $app->getInput()->get('option', 'com_swjprojects');
			$view      = $app->getInput()->get('view', 'project');
			$id        = $app->getInput()->getInt('id', 0);
			$sameView  = ($app->isClient('administrator') && $component == 'com_swjprojects' && $view == 'project');

			// Prepare options
			$options = parent::getOptions();
			foreach ($items as $i => $item)
			{
				$option          = new \stdClass();
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

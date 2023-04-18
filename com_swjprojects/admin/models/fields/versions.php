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
use Joomla\CMS\Language\Text;

FormHelper::loadFieldClass('list');

class JFormFieldVersions extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 *
	 * @since  1.0.0
	 */
	protected $type = 'versions';

	/**
	 * Field options.
	 *
	 * @var  array
	 *
	 * @since  1.0.0
	 */
	protected $_options = null;

	/**
	 * Method to get the field options.
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
				->select(array('v.id', 'v.major', 'v.minor', ' v.micro', 'v.tag', 'v.stage'))
				->from($db->quoteName('#__swjprojects_versions', 'v'));

			// Join over the projects
			$query->select(array('p.element as project_element'))
				->leftJoin($db->quoteName('#__swjprojects_projects', 'p') . ' ON p.id = v.project_id');

			// Join over translates
			JLoader::register('SWJProjectsHelperTranslation',
				JPATH_ADMINISTRATOR . '/components/com_swjprojects/helpers/translation.php');
			$translate = SWJProjectsHelperTranslation::getDefault();
			$query->select(array('t_p.title as project_title'))
				->leftJoin($db->quoteName('#__swjprojects_translate_projects', 't_p')
					. ' ON t_p.id = p.id AND ' . $db->quoteName('t_p.language') . ' = ' . $db->quote($translate));

			// Group by
			$query->group(array('v.id'));

			// Add the list ordering clause
			$query->order($db->escape('v.date') . ' ' . $db->escape('desc'));

			$items = $db->setQuery($query)->loadObjectList('id');

			// Prepare options
			$options = parent::getOptions();
			foreach ($items as $i => $item)
			{
				// Set project title
				$item->project_title = (empty($item->project_title)) ? $item->project_element : $item->project_title;

				// Set version & name
				$item->title = $item->project_title . ' ' . $item->major;
				if (!empty($item->minor) || !empty($item->micro))
				{
					$item->title .= '.' . $item->minor;
				}
				if (!empty($item->micro))
				{
					$item->title .= '.' . $item->micro;
				}
				if ($item->tag !== 'stable')
				{
					$item->title .= ' ' . Text::_('COM_SWJPROJECTS_VERSION_TAG_' . $item->tag);
					if ($item->tag !== 'dev' && !empty($item->stage))
					{
						$item->title .= ' ' . $item->stage;
					}
				}

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
<?php
/**
 * @package       SW JProjects
 * @version       2.4.0.1
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
 */

namespace Joomla\Component\SWJProjects\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\Component\SWJProjects\Administrator\Helper\TranslationHelper;
use Joomla\Database\DatabaseInterface;
use function defined;


class DocumentationField extends ListField
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
			$db    = Factory::getContainer()->get(DatabaseInterface::class);
			$query = $db->getQuery(true)
				->select(['d.id', 'd.alias'])
				->from($db->quoteName('#__swjprojects_documentation', 'd'));

			// Join over translates
			$translate = TranslationHelper::getCurrent() ?? TranslationHelper::getDefault();
			$query->select(['t_d.title as title'])
				->leftJoin($db->quoteName('#__swjprojects_translate_documentation', 't_d')
					. ' ON t_d.id = d.id AND ' . $db->quoteName('t_d.language') . ' = ' . $db->quote($translate));
			// Group by
			$query->group(['d.id']);

			// Add the list ordering clause
			$query->order($db->escape('d.ordering') . ' ' . $db->escape('asc'));

			$items = $db->setQuery($query)->loadObjectList('id');

			// Prepare options
			$options = parent::getOptions();
			foreach ($items as $i => $item)
			{
				// Add option
				$option        = new \stdClass();
				$option->value = $item->id;
				$option->text  = $item->title;

				$options[] = $option;
			}

			$this->_options = $options;
		}

		return $this->_options;
	}
}
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

namespace Joomla\Component\SWJProjects\Administrator\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\Component\SWJProjects\Administrator\Helper\TranslationHelper;
use Joomla\Database\DatabaseInterface;

defined('_JEXEC') or die;

class MaintainersField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 *
	 * @since  2.6.2
	 */
	protected $type = 'maintainers';

	/**
	 * Field options array.
	 *
	 * @var  array|null
	 *
	 * @since  2.6.2
	 */
	protected $_options = null;

	/**
	 * Method to get the field options.
	 *
	 * @return  array
	 *
	 * @throws  \Exception
	 *
	 * @since  2.6.2
	 */
	protected function getOptions()
	{
		if ($this->_options === null)
		{
			$db    = Factory::getContainer()->get(DatabaseInterface::class);
			$query = $db->getQuery(true)
				->select(['m.id', 'm.alias'])
				->from($db->quoteName('#__swjprojects_maintainers', 'm'));

			$translate = TranslationHelper::getCurrent() ?? TranslationHelper::getDefault();
			$query->select(['t_m.title as title'])
				->leftJoin($db->quoteName('#__swjprojects_translate_maintainers', 't_m')
					. ' ON t_m.id = m.id AND ' . $db->quoteName('t_m.language') . ' = ' . $db->quote($translate))
				->group(['m.id'])
				->order($db->escape('m.ordering') . ' ' . $db->escape('asc'));

			$items = $db->setQuery($query)->loadObjectList('id');
			$options = parent::getOptions();

			foreach ($items as $item)
			{
				$option        = new \stdClass();
				$option->value = $item->id;
				$option->text  = !empty($item->title) ? $item->title : $item->alias;

				$options[] = $option;
			}

			$this->_options = $options;
		}

		return $this->_options;
	}
}

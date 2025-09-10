<?php
/**
 * @package       SW JProjects
 * @version       2.5.0-alhpa1
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
use function str_repeat;

class CategoriesField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 *
	 * @since  1.0.0
	 */
	protected $type = 'categories';

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
				->select(['c.id', 'c.parent_id', 'c.level'])
				->from($db->quoteName('#__swjprojects_categories', 'c'))
				->where($db->quoteName('c.alias') . '!=' . $db->quote('root'));

			// Join over translates
			$translate = TranslationHelper::getCurrent() ?? TranslationHelper::getDefault();
			$query->select(['t_c.title as title'])
				->leftJoin($db->quoteName('#__swjprojects_translate_categories', 't_c')
					. ' ON t_c.id = c.id AND ' . $db->quoteName('t_c.language') . ' = ' . $db->quote($translate));

			// Group by
			$query->group(['c.id']);

			// Add the list ordering clause
			$query->order($db->escape('c.lft') . ' ' . $db->escape('asc'));

			$items = $db->setQuery($query)->loadObjectList('id');

			// Check admin type view
			$app       = Factory::getApplication();
			$component = $app->getInput()->get('option', 'com_swjprojects');
			$view      = $app->getInput()->get('view', 'category');
			$id        = $app->getInput()->getInt('id', 0);
			$sameView  = ($app->isClient('administrator') && $component == 'com_swjprojects' && $view == 'category');

			// Prepare options
			$options = parent::getOptions();
			foreach ($items as $i => $item)
			{
				$option        = new \stdClass();
				$option->value = $item->id;
				$option->text  = $item->title;

				if ($item->level > 1)
				{
					$option->text = str_repeat('- ', ($item->level - 1)) . $option->text;
				}

				$option->disable = ($sameView && ($item->id == $id || $item->parent_id == $id));

				$options[] = $option;
			}

			$this->_options = $options;
		}

		return $this->_options;
	}
}
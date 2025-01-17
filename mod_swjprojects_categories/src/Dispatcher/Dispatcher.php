<?php
/**
 * @package    SW JProjects
 *
 * @copyright   (C) 2022 Sergey Tolkachyov
 * @link       https://web-tolk.ru
 * @license     GNU General Public License version 2 or later
 */

namespace Joomla\Module\Swjprojectscategories\Site\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\Module\Swjprojectscategories\Site\Helper\SwjprojectscategoriesHelper;
use function defined;

defined('JPATH_PLATFORM') or die;

/**
 * Dispatcher class for mod_wtyandexmapitems
 *
 * @since  1.0.0
 */
class Dispatcher extends AbstractModuleDispatcher
{

	/**
	 * Returns the layout data.
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	protected function getLayoutData()
	{
		$data = parent::getLayoutData();
		// Вариант использования хелпера через Namespace
		$helper = new SwjprojectscategoriesHelper();
		$data['items'] = $helper->getCategories($data['params'], $this->getApplication());

		return $data;
	}
}
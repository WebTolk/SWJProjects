<?php
/**
 * @package    SW JProjects
 *
 * @copyright   (C) 2022 Sergey Tolkachyov
 * @link       https://web-tolk.ru
 * @license     GNU General Public License version 2 or later
 */

namespace Joomla\Module\Swjprojectsversions\Site\Dispatcher;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Extension\ModuleInterface;
use Joomla\Input\Input;
use Joomla\Module\Swjprojectsversions\Site\Helper\SwjprojectsversionsHelper;
use Joomla\Registry\Registry;

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
		$helper = new SwjprojectsversionsHelper();
		$data['items'] = $helper->getVersions($data['params'], $this->getApplication());

		return $data;
	}
}
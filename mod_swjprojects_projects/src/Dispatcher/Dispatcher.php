<?php
/**
 * @package    SW JProjects
 * @version    2.3.0
 * @author     Sergey Tolkachyov
 * @сopyright  Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
 */

namespace Joomla\Module\Swjprojectsprojects\Site\Dispatcher;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Extension\ModuleInterface;
use Joomla\Input\Input;
use Joomla\Module\Swjprojectsprojects\Site\Helper\SwjprojectsprojectsHelper;
use Joomla\Registry\Registry;
use function defined;

defined('JPATH_PLATFORM') or die;

/**
 * Dispatcher class for mod_swjprojects_projects
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
		$helper = new SwjprojectsprojectsHelper();
		$data['items'] = $helper->getProjects($data['params'], $this->getApplication());

		return $data;
	}
}

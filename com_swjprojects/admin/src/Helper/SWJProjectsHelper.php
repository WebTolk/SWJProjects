<?php
/*
 * @package    SW JProjects
 * @version    2.1.2
 * @author     Sergey Tolkachyov
 * @сopyright  Copyright (c) 2018 - 2024 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
 */

namespace Joomla\Component\SWJProjects\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

class SWJProjectsHelper extends ContentHelper
{
	/**
	 * Configure the linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @since  1.0.0
	 */
	public static function addSubmenu($vName)
	{
//		JHtmlSidebar::addEntry(Text::_('COM_SWJPROJECTS_VERSIONS'),
//			'index.php?option=com_swjprojects&view=versions',
//			$vName == 'versions');
//
//		JHtmlSidebar::addEntry(Text::_('COM_SWJPROJECTS_PROJECTS'),
//			'index.php?option=com_swjprojects&view=projects',
//			$vName == 'projects');
//
//		JHtmlSidebar::addEntry(Text::_('COM_SWJPROJECTS_KEYS'),
//			'index.php?option=com_swjprojects&view=keys',
//			$vName == 'keys');
//
//		JHtmlSidebar::addEntry(Text::_('COM_SWJPROJECTS_DOCUMENTATION'),
//			'index.php?option=com_swjprojects&view=documentation',
//			$vName == 'documentation');
//
//		JHtmlSidebar::addEntry(Text::_('COM_SWJPROJECTS_CATEGORIES'),
//			'index.php?option=com_swjprojects&view=categories',
//			$vName == 'categories');
//
//		JHtmlSidebar::addEntry(Text::_('COM_SWJPROJECTS_CONFIG'),
//			'index.php?option=com_config&view=component&component=com_swjprojects');
	}
}
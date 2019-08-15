<?php
/**
 * @package    SW JProjects Component
 * @version    1.3.0
 * @author     Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2018 - 2019 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

defined('_JEXEC') or die;

JLoader::register('SWJProjectsHelper', __DIR__ . '/helpers/swjprojects.php');
JLoader::register('SWJProjectsHelperImages', __DIR__ . '/helpers/images.php');
JLoader::register('SWJProjectsHelperKeys', __DIR__ . '/helpers/keys.php');

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

if (!Factory::getUser()->authorise('core.manage', 'com_swjprojects'))
{
	throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
}

// Show donate message
SWJProjectsHelper::showDonateMessage();

$controller = BaseController::getInstance('SWJProjects');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();
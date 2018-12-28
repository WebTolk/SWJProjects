<?php
/**
 * @package    SW JProjects Component
 * @version    1.0.2
 * @author     Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2018 - 2018 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

defined('_JEXEC') or die;

JLoader::register('SWJProjectsHelperRoute', JPATH_SITE . '/components/com_swjprojects/helpers/route.php');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

$controller = BaseController::getInstance('SWJProjects');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();
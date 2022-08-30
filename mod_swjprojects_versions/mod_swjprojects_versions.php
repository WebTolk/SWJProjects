<?php
/**
 * @package    SW JProjects - Versions Module
 * @version    1.6.1
 * @author     Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2018 - 2022 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

// Register helpers
JLoader::register('SWJProjectsHelperRoute', JPATH_SITE . '/components/com_swjprojects/helpers/route.php');
JLoader::register('SWJProjectsHelperImages', JPATH_SITE . '/components/com_swjprojects/helpers/images.php');
JLoader::register('SWJProjectsHelperTranslation', JPATH_ADMINISTRATOR . '/components/com_swjprojects/helpers/translation.php');

// Load language
$language = Factory::getLanguage();
$language->load('com_swjprojects', JPATH_SITE, $language->getTag(), true);

// Prepare model
BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_swjprojects/models');
$model = BaseDatabaseModel::getInstance('Versions', 'SWJProjectsModel', array('ignore_request' => true));
$model->setState('project.id', $params->get('project', ''));
$model->setState('params', Factory::getApplication()->getParams());
$model->setState('filter.published', 1);
$model->setState('list.limit', $params->get('limit', 5));
$model->setState('list.start', 0);

// Get items
$items = $model->getItems();

// Show module
require ModuleHelper::getLayoutPath($module->module, $params->get('layout', 'default'));
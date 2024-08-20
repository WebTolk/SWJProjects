<?php
/**
 * @package    SW JProjects
 *
 * @copyright   (C) 2022 Sergey Tolkachyov
 * @link       https://web-tolk.ru
 * @license         GNU General Public License version 2 or later
 */

namespace Joomla\Module\Swjprojectscategories\Site\Helper;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

\defined('_JEXEC') or die;


/**
 * Helper for mod_Swjprojectscategories
 *
 * @since  1.0
 */
class SwjprojectscategoriesHelper
{
	public function getCategories($params, $app):array
	{

		$model = $app->bootComponent('com_swjprojects')
			->getMVCFactory()
			->createModel('Categories', 'Administrator', ['ignore_request' => true]);

		$model->setState('params', Factory::getApplication()->getParams());
		$model->setState('filter.published', 1);
		$model->setState('list.limit', $params->get('limit', 5));

		$ordering = $params->get('ordering');

		if ($ordering === 'rand()')
		{
			$model->setState('list.ordering', Factory::getContainer()->get(DatabaseInterface::class)->getQuery(true)->Rand());
		}
		else
		{
			$direction = $params->get('direction', 1) ? 'DESC' : 'ASC';
			$model->setState('list.direction', $direction);
			$model->setState('list.ordering', $ordering);
		}

		// Load language
		$app->getLanguage()->load('com_swjprojects', JPATH_SITE);

		// Get items
		$items = $model->getItems();
		return $items;
	}
}
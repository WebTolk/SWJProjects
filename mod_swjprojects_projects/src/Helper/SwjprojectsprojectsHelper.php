<?php
/**
 * @package    SW JProjects
 *
 * @copyright   (C) 2022 Sergey Tolkachyov
 * @link       https://web-tolk.ru
 * @license         GNU General Public License version 2 or later
 */

namespace Joomla\Module\Swjprojectsprojects\Site\Helper;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;


\defined('_JEXEC') or die;


/**
 * Helper for mod_Swjprojectsprojects
 *
 * @since  1.0
 */
class SwjprojectsprojectsHelper
{
	public function getProjects($params, $app):array
	{

		$model = $app->bootComponent('com_swjprojects')
			->getMVCFactory()
			->createModel('Projects', 'Site', ['ignore_request' => true]);

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
<?php
/**
 * @package       SW JProjects
 * @version       2.6.1-dev
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
 */

namespace Joomla\Module\Swjprojectsversions\Site\Helper;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use function defined;

defined('_JEXEC') or die;

/**
 * Helper for mod_swjprojects_versions
 *
 * @since  1.0
 */
class SwjprojectsversionsHelper
{
	public function getVersions($params, $app):array
	{

		$model = $app->bootComponent('com_swjprojects')
			->getMVCFactory()
			->createModel('Versions', 'Site', ['ignore_request' => true]);

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

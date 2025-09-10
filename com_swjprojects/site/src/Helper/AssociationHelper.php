<?php
/**
 * @package       SW JProjects
 * @version       2.5.0-alhpa1
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
 */

namespace Joomla\Component\SWJProjects\Site\Helper;

use Joomla\CMS\Factory;
use Joomla\Component\SWJProjects\Administrator\Helper\TranslationHelper;
use function defined;
use function md5;
use function serialize;

defined('_JEXEC') or die;

abstract class AssociationHelper
{
	/**
	 * Item associations.
	 *
	 * @var  array
	 *
	 * @since  1.0.0
	 */
	protected static $_associations = [];

	/**
	 * Method to get the associations for a given item.
	 *
	 * @param   integer  $id          Id of the item.
	 * @param   string   $view        Name of the view.
	 * @param   integer  $catid       Id of the category.
	 * @param   integer  $project_id  Id of the project.
	 * @param   integer  $debug       Enable debug.
	 *
	 * @throws  \Exception
	 *
	 * @return  array  Array of associations for the item.
	 *
	 * @since  1.0.0
	 */
	public static function getAssociations(int $id = 0, string $view = null, int $catid = 0, int $project_id = 0, int $debug = 0)
	{
		$app        = Factory::getApplication();
		$id         = (!empty($id)) ? $id : $app->getInput()->getInt('id', 0);
		$view       = (!empty($view)) ? $view : $app->getInput()->getCmd('view', '');
		$catid      = (!empty($catid)) ? $catid : $app->getInput()->getInt('catid', 1);
		$project_id = (!empty($project_id)) ? $project_id : $app->getInput()->getInt('project_id', 0);
		$debug      = (!empty($debug)) ? $debug : $app->getInput()->getInt('debug', 0);
		$hash       = md5(serialize([$id, $view, $catid, $project_id]));

		if (!isset(self::$_associations[$hash]))
		{
			$associations = [];
			foreach (TranslationHelper::getCodes() as $code)
			{
				$link = false;
				if ($view == 'version')
				{
					$link = RouteHelper::getVersionRoute($id, $project_id, $catid);
				}

				if ($view == 'versions')
				{
					$link = RouteHelper::getVersionsRoute($id, $catid);
				}

				if ($view == 'document')
				{
					$link = RouteHelper::getDocumentRoute($id, $project_id, $catid);
				}

				if ($view == 'documentation')
				{
					$link = RouteHelper::getDocumentationRoute($id, $catid);
				}

				if ($view == 'projects')
				{
					$link = RouteHelper::getProjectsRoute($id);
				}

				if ($view == 'project')
				{
					$link = RouteHelper::getProjectRoute($id, $catid);
				}

				if ($link)
				{
					if (!empty($debug))
					{
						$link .= '&debug=1';
					}
					$link                .= '&lang=' . $code;
					$associations[$code] = $link;
				}
			}
			self::$_associations[$hash] = $associations;
		}

		return self::$_associations[$hash];
	}
}
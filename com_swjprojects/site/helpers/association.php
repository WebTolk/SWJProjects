<?php
/*
 * @package    SW JProjects Component
 * @version    1.6.4
 * @author Septdir Workshop, <https://septdir.com>, Sergey Tolkachyov <https://web-tolk.ru>
 * @сopyright (c) 2018 - April 2023 Septdir Workshop, Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link https://septdir.com, https://web-tolk.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

abstract class SWJProjectsHelperAssociation
{
	/**
	 * Item associations.
	 *
	 * @var  array
	 *
	 * @since  1.0.0
	 */
	protected static $_associations = array();

	/**
	 * Method to get the associations for a given item.
	 *
	 * @param   integer  $id          Id of the item.
	 * @param   string   $view        Name of the view.
	 * @param   integer  $catid       Id of the category.
	 * @param   integer  $project_id  Id of the project.
	 * @param   integer  $debug       Enable debug.
	 *
	 * @throws  Exception
	 *
	 * @return  array  Array of associations for the item.
	 *
	 * @since  1.0.0
	 */
	public static function getAssociations($id = 0, $view = null, $catid = 0, $project_id = 0, $debug = 0)
	{
		$app        = Factory::getApplication();
		$id         = (!empty($id)) ? $id : $app->input->getInt('id', 0);
		$view       = (!empty($view)) ? $view : $app->input->getCmd('view', '');
		$catid      = (!empty($catid)) ? $catid : $app->input->getInt('catid', 1);
		$project_id = (!empty($project_id)) ? $project_id : $app->input->getInt('project_id', 0);
		$debug      = (!empty($debug)) ? $debug : $app->input->getInt('debug', 0);
		$hash       = md5(serialize(array($id, $view, $catid, $project_id)));

		if (!isset(self::$_associations[$hash]))
		{
			$associations = array();
			foreach (SWJProjectsHelperTranslation::getCodes() as $code)
			{
				$link = false;
				if ($view == 'version')
				{
					$link = SWJProjectsHelperRoute::getVersionRoute($id, $project_id, $catid);
				}

				if ($view == 'versions')
				{
					$link = SWJProjectsHelperRoute::getVersionsRoute($id, $catid);
				}

				if ($view == 'document')
				{
					$link = SWJProjectsHelperRoute::getDocumentRoute($id, $project_id, $catid);
				}

				if ($view == 'documentation')
				{
					$link = SWJProjectsHelperRoute::getDocumentationRoute($id, $catid);
				}

				if ($view == 'projects')
				{
					$link = SWJProjectsHelperRoute::getProjectsRoute($id);
				}

				if ($view == 'project')
				{
					$link = SWJProjectsHelperRoute::getProjectRoute($id, $catid);
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
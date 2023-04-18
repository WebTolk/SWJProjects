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

use Joomla\CMS\Helper\RouteHelper;

class SWJProjectsHelperRoute extends RouteHelper
{
	/**
	 * Fetches jupdate route.
	 *
	 * @param   int     $project_id    The id of the project.
	 * @param   string  $element       The element of the project.
	 * @param   string  $download_key  The download key value.
	 *
	 * @return  string  Joomla update server view link.
	 *
	 * @since  1.0.0
	 */
	public static function getJUpdateRoute($project_id = null, $element = null, $download_key = null)
	{
		$link = 'index.php?option=com_swjprojects&view=jupdate';

		if (!empty($project_id))
		{
			$link .= '&project_id=' . $project_id;
		}

		if (!empty($element))
		{
			$link .= '&element=' . $element;
		}

		if (!empty($download_key))
		{
			$link .= '&download_key=' . $download_key;
		}

		return $link;
	}

	/**
	 * Fetches download route.
	 *
	 * @param   int     $version_id    The id of the version.
	 * @param   int     $project_id    The id of the project.
	 * @param   string  $element       The element of the project.
	 * @param   string  $download_key  The download key value.
	 *
	 * @return  string  Download link.
	 *
	 * @since  1.0.0
	 */
	public static function getDownloadRoute($version_id = null, $project_id = null, $element = null, $download_key = null)
	{
		$link = 'index.php?option=com_swjprojects&view=download';

		if (!empty($version_id))
		{
			$link .= '&version_id=' . $version_id;
		}

		if (!empty($project_id))
		{
			$link .= '&project_id=' . $project_id;
		}

		if (!empty($element))
		{
			$link .= '&element=' . $element;
		}

		if (!empty($download_key))
		{
			$link .= '&download_key=' . $download_key;
		}

		return $link;
	}

	/**
	 * Fetches version route.
	 *
	 * @param   int  $id          The id of the version.
	 * @param   int  $project_id  The id of the project.
	 * @param   int  $catid       The id of the category.
	 *
	 * @return  string  Version view link.
	 *
	 * @since  1.0.0
	 */
	public static function getVersionRoute($id = null, $project_id = null, $catid = null)
	{
		$link = 'index.php?option=com_swjprojects&view=version';

		if (!empty($id))
		{
			$link .= '&id=' . $id;
		}

		if (!empty($project_id))
		{
			$link .= '&project_id=' . $project_id;
		}

		if (!empty($catid))
		{
			$link .= '&catid=' . $catid;
		}

		return $link;
	}

	/**
	 * Fetches versions route.
	 *
	 * @param   int  $id     The id of the project.
	 * @param   int  $catid  The id of the category.
	 *
	 * @return  string  Versions view link.
	 *
	 * @since  1.0.0
	 */
	public static function getVersionsRoute($id = null, $catid = null)
	{
		$link = 'index.php?option=com_swjprojects&view=versions';

		if (!empty($id))
		{
			$link .= '&id=' . $id;
		}

		if (!empty($catid))
		{
			$link .= '&catid=' . $catid;
		}

		return $link;
	}

	/**
	 * Fetches document route.
	 *
	 * @param   int  $id          The id of the version.
	 * @param   int  $project_id  The id of the project.
	 * @param   int  $catid       The id of the category.
	 *
	 * @return  string  Document view link.
	 *
	 * @since  1.4.0
	 */
	public static function getDocumentRoute($id = null, $project_id = null, $catid = null)
	{
		$link = 'index.php?option=com_swjprojects&view=document';

		if (!empty($id))
		{
			$link .= '&id=' . $id;
		}

		if (!empty($project_id))
		{
			$link .= '&project_id=' . $project_id;
		}

		if (!empty($catid))
		{
			$link .= '&catid=' . $catid;
		}

		return $link;
	}

	/**
	 * Fetches documentation route.
	 *
	 * @param   int  $id     The id of the project.
	 * @param   int  $catid  The id of the category.
	 *
	 * @return  string  Documentation view link.
	 *
	 * @since  1.4.0
	 */
	public static function getDocumentationRoute($id = null, $catid = null)
	{
		$link = 'index.php?option=com_swjprojects&view=documentation';

		if (!empty($id))
		{
			$link .= '&id=' . $id;
		}

		if (!empty($catid))
		{
			$link .= '&catid=' . $catid;
		}

		return $link;
	}

	/**
	 * Fetches project route.
	 *
	 * @param   int  $id     The id of the project.
	 * @param   int  $catid  The id of the category.
	 *
	 * @return  string  Project view link.
	 *
	 * @since  1.0.0
	 */
	public static function getProjectRoute($id = null, $catid = null)
	{
		$link = 'index.php?option=com_swjprojects&view=project';

		if (!empty($id))
		{
			$link .= '&id=' . $id;
		}

		if (!empty($catid))
		{
			$link .= '&catid=' . $catid;
		}

		return $link;
	}

	/**
	 * Fetches projects route.
	 *
	 * @param   int  $id  The id of the category.
	 *
	 * @return  string  Projects view link.
	 *
	 * @since  1.0.0
	 */
	public static function getProjectsRoute($id = null)
	{
		$link = 'index.php?option=com_swjprojects&view=projects';

		if (!empty($id))
		{
			$link .= '&id=' . $id;
		}

		return $link;
	}
}
<?php
/**
 * @package    SW JProjects Component
 * @version    1.0.0
 * @author     Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2018 - 2018 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

class SWJProjectsController extends BaseController
{
	/**
	 * The default view.
	 *
	 * @var  string
	 *
	 * @since  1.0.0
	 */
	protected $default_view = 'projects';

	/**
	 * Typical view method for MVC based architecture.
	 *
	 * @param  boolean $cachable  If true, the view output will be cached
	 * @param  array   $urlparams An array of safe URL parameters and their variable types.
	 *
	 * @throws  Exception
	 *
	 * @return  BaseController  A BaseController object to support chaining.
	 *
	 * @since  1.0.0
	 */
	public function display($cachable = false, $urlparams = array())
	{
		$view       = $this->input->get('view', $this->default_view);
		$id         = $this->input->get('id', 0);
		$catid      = $this->input->get('catid', 1);
		$project_id = $this->input->get('project_id', 0);

		$uri       = Uri::getInstance();
		$canonical = false;
		$params    = array();
		if (!empty($uri->getVar('start')))
		{
			$params['start'] = $uri->getVar('start');
		}
		if (!empty($uri->getVar('debug')))
		{
			$params['debug'] = $uri->getVar('debug');
		}

		if ($view == 'version')
		{
			$canonical = SWJProjectsHelperRoute::getVersionRoute($id, $project_id, $catid);
		}

		if ($view == 'versions')
		{
			$canonical = SWJProjectsHelperRoute::getVersionsRoute($id, $catid);
		}

		if ($view == 'project')
		{
			$canonical = SWJProjectsHelperRoute::getProjectRoute($id, $catid);
		}

		if ($view == 'projects')
		{
			$canonical = SWJProjectsHelperRoute::getProjectsRoute($id);
		}

		if ($view == 'jupdate')
		{
			$canonical = SWJProjectsHelperRoute::getJUpdateRoute($id);
			if (!empty($uri->getVar('project_id')))
			{
				$params['project_id'] = $uri->getVar('project_id');
			}
			if (!empty($uri->getVar('element')))
			{
				$params['element'] = $uri->getVar('element');
			}
		}

		if ($canonical)
		{
			$url       = urldecode($uri->toString());
			$canonical = rtrim(URI::root(), '/') . Route::_($canonical);

			if ($url !== $canonical)
			{
				Factory::getDocument()->addHeadLink($canonical, 'canonical');

				$redirect = $canonical;
				if (!empty($params))
				{
					$redirect .= '?' . urldecode(http_build_query($params));
				}

				if ($url != $redirect)
				{
					Factory::getApplication()->redirect($redirect, true);
				}
			}
		}

		return parent::display($cachable, $urlparams);
	}

	/**
	 * Method to download version file.
	 *
	 * @throws  Exception
	 *
	 * @since  1.0.0
	 */
	public function download()
	{
		$app   = Factory::getApplication();
		$model = $this->getModel('Download');

		// Get file data
		if (!$download = $model->getFile())
		{
			throw new Exception(Text::_('COM_SWJPROJECTS_ERROR_FILE_NOT_FOUND'), 404);
		}

		// Set headers
		ob_end_clean();
		$app->clearHeaders();
		$app->setHeader('Content-Type', 'application/octet-stream', true);
		$app->setHeader('Content-Disposition', 'attachment; filename=' . $download->name . ';', true);
		$app->sendHeaders();

		// Read file
		if ($context = @file_get_contents($download->path))
		{
			echo $context;
			$model->setDownload();
		}

		// Close application
		$app->close();
	}
}
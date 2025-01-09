<?php
/*
 * @package    SW JProjects
 * @version    2.2.0
 * @author     Sergey Tolkachyov
 * @сopyright  Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
 */

namespace Joomla\Component\SWJProjects\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\SWJProjects\Site\Helper\RouteHelper;

class DisplayController extends BaseController
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
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types.
	 *
	 * @throws  \Exception
	 *
	 * @return  BaseController  A BaseController object to support chaining.
	 *
	 * @since  1.0.0
	 */
	public function display($cachable = false, $urlparams = array())
	{
        $cachable = true;

		$view         = $this->input->get('view', $this->default_view);
		// Duplicates protection
		if ($this->app->getParams()->get('duplicates_protection', 1))
		{

			$id           = $this->input->get('id', 0, 'raw');
			$catid        = $this->input->get('catid', 1, 'raw');
			$project_id   = $this->input->get('project_id', 0, 'raw');
			$element      = $this->input->get('element', '', 'raw');
			$download_key = $this->input->get('download_key', '', 'raw');
			$link         = false;

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

			if ($view == 'project')
			{

				$link = RouteHelper::getProjectRoute($id, $catid);
			}

			if ($view == 'projects')
			{
				$link = RouteHelper::getProjectsRoute($id);
			}

			if ($view == 'jupdate')
			{
				$link = RouteHelper::getJUpdateRoute($project_id, $element, $download_key);
			}

			if ($view == 'jchangelog')
			{
				$link = RouteHelper::getJChangelogRoute($project_id, $element);
			}

			if ($link)
			{
				$uri       = Uri::getInstance();
				$root      = $uri->toString(array('scheme', 'host', 'port'));
				$canonical = Uri::getInstance(Route::_($link))->toString();
				$current   = $uri->toString(array('path', 'query', 'fragment'));

				if ($current !== $canonical)
				{
					$this->app
						->getDocument()
						->addCustomTag('<link href="' . $root . $canonical . '" rel="canonical"/>');

					$redirect = Uri::getInstance(Route::_($link));
					foreach ($uri->getQuery(true) as $key => $value)
					{
						if (!empty($value) && (preg_match('#^utm_#', $key) || $key == 'start' || $key == 'debug'))
						{
							$redirect->setVar($key, $value);
						}
					}
					$redirect = $redirect->toString(array('path', 'query', 'fragment'));

					if (urldecode($current) != urldecode($redirect))
					{
						$this->app->redirect($redirect, 301);
					}
				}
			}
		}

		// Cache
		if ($view !== 'jchangelog'
			&& $view !== 'jupdate'
			&& $view !== 'download'
			&& $this->input->get('task') !== 'download')
		{
			$cachable  = true;
			$urlparams = [
				'id'            => 'INT',
				'catid'         => 'INT',
				'project_id'    => 'INT',
				'element'       => 'STRING',
				'limit'         => 'UINT',
				'limitstart'    => 'UINT',
				'showall'       => 'INT',
				'return'        => 'BASE64',
				'filter'        => 'STRING',
				'filter-search' => 'STRING',
				'lang'          => 'CMD',
				'Itemid'        => 'INT'
			];
		}

		parent::display($cachable, $urlparams);
		return $this;
	}

	/**
	 * Redirect to download view.
	 *
	 * @throws  \Exception
	 *
	 * @since  1.2.0
	 */
	public function download()
	{
		Factory::getApplication()->redirect(
			RouteHelper::getDownloadRoute(
				$this->input->get('version_id', null, 'int'),
				$this->input->get('project_id', null, 'int'),
				$this->input->get('element', null, 'raw')
			)
		);
	}
}
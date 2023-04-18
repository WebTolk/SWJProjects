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
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types.
	 *
	 * @throws  Exception
	 *
	 * @return  BaseController  A BaseController object to support chaining.
	 *
	 * @since  1.0.0
	 */
	public function display($cachable = false, $urlparams = array())
	{
		// Duplicates protection
		if (Factory::getApplication()->getParams()->get('duplicates_protection', 1))
		{
			$view         = $this->input->get('view', $this->default_view);
			$id           = $this->input->get('id', 0, 'raw');
			$catid        = $this->input->get('catid', 1, 'raw');
			$project_id   = $this->input->get('project_id', 0, 'raw');
			$element      = $this->input->get('element', '', 'raw');
			$download_key = $this->input->get('download_key', '', 'raw');
			$link         = false;

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

			if ($view == 'project')
			{

				$link = SWJProjectsHelperRoute::getProjectRoute($id, $catid);
			}

			if ($view == 'projects')
			{
				$link = SWJProjectsHelperRoute::getProjectsRoute($id);
			}

			if ($view == 'jupdate')
			{
				$link = SWJProjectsHelperRoute::getJUpdateRoute($project_id, $element, $download_key);
			}

			if ($link)
			{
				$uri       = Uri::getInstance();
				$root      = $uri->toString(array('scheme', 'host', 'port'));
				$canonical = Uri::getInstance(Route::_($link))->toString();
				$current   = $uri->toString(array('path', 'query', 'fragment'));

				if ($current !== $canonical)
				{
					Factory::getDocument()->addCustomTag('<link href="' . $root . $canonical . '" rel="canonical"/>');

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
						Factory::getApplication()->redirect($redirect, 301);
					}
				}
			}
		}

		// Cache
		if ($view !== 'jupdate' && $view !== 'download' && $this->input->get('task') !== 'download')
		{
			$cachable  = true;
			$urlparams = array(
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
				'Itemid'        => 'INT');
		}

		return parent::display($cachable, $urlparams);
	}

	/**
	 * Redirect to download view.
	 *
	 * @throws  Exception
	 *
	 * @since  1.2.0
	 */
	public function download()
	{
		Factory::getApplication()->redirect(SWJProjectsHelperRoute::getDownloadRoute(
			$this->input->get('version_id', null, 'int'),
			$this->input->get('project_id', null, 'int'),
			$this->input->get('element', null, 'raw')));
	}
}
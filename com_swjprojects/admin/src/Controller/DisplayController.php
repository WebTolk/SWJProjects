<?php
/*
 * @package    SW JProjects
 * @version    2.0.1
 * @author     Sergey Tolkachyov
 * @сopyright  Copyright (c) 2018 - 2024 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
 */

namespace Joomla\Component\SWJProjects\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\SWJProjects\Site\Helper\RouteHelper;
use Joomla\Registry\Registry;

class DisplayController extends BaseController
{
	/**
	 * The default view.
	 *
	 * @var  string
	 *
	 * @since  1.0.0
	 */
	protected $default_view = 'versions';

	/**
	 * Typical view method for MVC based architecture
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link       https://web-tolk.ru
	 *
	 * @throws  \Exception
	 *
	 * @return  BaseController  A BaseController object to support chaining.
	 *
	 * @since   1.5.2
	 */
	public function display($cachable = false, $urlparams = array())
	{
		return parent::display($cachable, $urlparams);
	}

	/**
	 * Redirect to site.
	 *
	 * @since  1.0.0
	 */
	public function siteRedirect()
	{

		$page         = $this->input->get('page', false);
		$id           = $this->input->getInt('id');
		$catid        = $this->input->getInt('catid');
		$project_id   = $this->input->getInt('project_id');
		$version_id   = $this->input->getInt('version_id');
		$element      = $this->input->get('element');
		$download_key = $this->input->get('download_key');

		$redirects = array(
			'projects' => RouteHelper::getProjectsRoute($id),
			'project'  => RouteHelper::getProjectRoute($id, $catid),
			'versions' => RouteHelper::getVersionsRoute($id, $catid),
			'version'  => RouteHelper::getVersionRoute($id, $project_id, $catid),
			'download' => RouteHelper::getDownloadRoute($version_id, $project_id, $element, $download_key),
			'jupdate'  => RouteHelper::getJUpdateRoute($project_id, $element, $download_key),
			'jchangelog'  => RouteHelper::getJChangelogRoute($project_id, $element),
		);

		$redirect = (!empty($page) && !empty($redirects[$page])) ? $redirects[$page] : false;

		if (!$redirect)
		{
			$this->setMessage(Text::_('COM_SWJPROJECTS_ERROR_PAGE_NOT_FOUND'), 'error');
			$this->setRedirect(Route::_('index.php?option=com_swjprojects&view=' . $this->input->get('view')));
			$this->redirect();
		}

		// Set Redirect
		$debug = ($this->input->get('debug', false)) ? '&debug=1' : '';
		$this->setRedirect(Uri::root() . $redirect . $debug);
		$this->redirect();
	}
}
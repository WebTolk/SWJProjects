<?php
/*
 * @package    SW JProjects Component
 * @version    1.9.0
 * @author Septdir Workshop, <https://septdir.com>, Sergey Tolkachyov <https://web-tolk.ru>
 * @сopyright (c) 2018 - October 2023 Septdir Workshop, Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link https://septdir.com, https://web-tolk.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

class SWJProjectsController extends BaseController
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
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link https://septdir.com, https://web-tolk.ru
	 *
	 * @throws  Exception
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
		JLoader::register('SWJProjectsHelperRoute', JPATH_SITE . '/components/com_swjprojects/helpers/route.php');

		$page         = $this->input->get('page', false);
		$id           = $this->input->getInt('id');
		$catid        = $this->input->getInt('catid');
		$project_id   = $this->input->getInt('project_id');
		$version_id   = $this->input->getInt('version_id');
		$element      = $this->input->get('element');
		$download_key = $this->input->get('download_key');

		$redirects = array(
			'projects' => SWJProjectsHelperRoute::getProjectsRoute($id),
			'project'  => SWJProjectsHelperRoute::getProjectRoute($id, $catid),
			'versions' => SWJProjectsHelperRoute::getVersionsRoute($id, $catid),
			'version'  => SWJProjectsHelperRoute::getVersionRoute($id, $project_id, $catid),
			'download' => SWJProjectsHelperRoute::getDownloadRoute($version_id, $project_id, $element, $download_key),
			'jupdate'  => SWJProjectsHelperRoute::getJUpdateRoute($project_id, $element, $download_key),
			'jchangelog'  => SWJProjectsHelperRoute::getJChangelogRoute($project_id, $element),
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
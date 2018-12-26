<?php
/**
 * @package    SW JProjects Component
 * @version    1.0.1
 * @author     Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2018 - 2018 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

defined('_JEXEC') or die;

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
	protected $default_view = 'versions';

	/**
	 * Redirect to site.
	 *
	 * @since  1.0.0
	 */
	public function siteRedirect()
	{
		JLoader::register('SWJProjectsHelperRoute', JPATH_SITE . '/components/com_swjprojects/helpers/route.php');

		$page       = $this->input->get('page', false);
		$id         = $this->input->getInt('id');
		$catid      = $this->input->getInt('catid');
		$project_id = $this->input->getInt('project_id');
		$version_id = $this->input->getInt('version_id');
		$element    = $this->input->get('element');

		$redirects = array(
			'projects' => SWJProjectsHelperRoute::getProjectsRoute($id),
			'project'  => SWJProjectsHelperRoute::getProjectRoute($id, $catid),
			'versions' => SWJProjectsHelperRoute::getVersionsRoute($id, $catid),
			'version'  => SWJProjectsHelperRoute::getVersionRoute($id, $project_id, $catid),
			'download' => SWJProjectsHelperRoute::getDownloadRoute($version_id, $project_id, $element),
			'jupdate'  => SWJProjectsHelperRoute::getJUpdateRoute($project_id, $element)
		);

		$redirect = (!empty($page) && !empty($redirects[$page])) ? $redirects[$page] : false;

		if (!$redirect)
		{
			$this->setMessage(Text::_('COM_SWJPROJECTS_ERROR_PAGE_NOT_FOUND'), 'error');
			$this->setRedirect(Route::_('index.php?option=com_centers&view=' . $this->input->get('view')));
			$this->redirect();
		}

		// Set Redirect
		$debug = ($this->input->get('debug', false)) ? '&debug=1' : '';
		$this->setRedirect(Uri::root() . $redirect . $debug);
		$this->redirect();
	}
}
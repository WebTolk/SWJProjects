<?php
/**
 * @package    SW JProjects Component
 * @version    1.3.2
 * @author     Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2018 - 2019 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class SWJProjectsControllerVersions extends AdminController
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var  string
	 *
	 * @since  1.0.0
	 */
	protected $text_prefix = 'COM_SWJPROJECTS_VERSIONS';

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name.
	 * @param   string  $prefix  The class prefix.
	 * @param   array   $config  The array of possible config values.
	 *
	 * @return  BaseDatabaseModel|SWJProjectsModelVersion  A model object.
	 *
	 * @since  1.0.0
	 */
	public function getModel($name = 'Version', $prefix = 'SWJProjectsModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}
}
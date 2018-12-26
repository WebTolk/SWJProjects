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

use Joomla\CMS\MVC\Controller\AdminController;

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
	 * @param  string $name   The model name.
	 * @param  string $prefix The class prefix.
	 * @param  array  $config The array of possible config values.
	 *
	 * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel  A model object.
	 *
	 * @since  1.0.0
	 */
	public function getModel($name = 'Version', $prefix = 'SWJProjectsModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}
}
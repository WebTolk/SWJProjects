<?php
/**
 * @package       SW JProjects
 * @version       2.6.2
 * @Author        Sergey Tolkachyov
 * @copyright  Copyright (c) 2018 - 2026 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link          https://web-tolk.ru
 * @since         2.6.2
 */

namespace Joomla\Component\SWJProjects\Administrator\Controller;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\Component\SWJProjects\Administrator\Model\MaintainerModel;

defined('_JEXEC') or die;

class MaintainersController extends AdminController
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var  string
	 *
	 * @since  2.6.2
	 */
	protected $text_prefix = 'COM_SWJPROJECTS_MAINTAINERS';

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name.
	 * @param   string  $prefix  The class prefix.
	 * @param   array   $config  The array of possible config values.
	 *
	 * @return  MaintainerModel
	 *
	 * @since  2.6.2
	 */
	public function getModel($name = 'Maintainer', $prefix = 'Administrator', $config = ['ignore_request' => true])
	{
		return parent::getModel($name, $prefix, $config);
	}
}

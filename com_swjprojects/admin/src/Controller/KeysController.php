<?php
/**
 * @package       SW JProjects
 * @version       2.5.0-alhpa1
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
 */

namespace Joomla\Component\SWJProjects\Administrator\Controller;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\Component\SWJProjects\Administrator\Model\KeyModel;
use function defined;

defined('_JEXEC') or die;

class KeysController extends AdminController
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var  string
	 *
	 * @since  1.3.0
	 */
	protected $text_prefix = 'COM_SWJPROJECTS_KEYS';

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name.
	 * @param   string  $prefix  The class prefix.
	 * @param   array   $config  The array of possible config values.
	 *
	 * @return  KeyModel  A model object.
	 *
	 * @since  1.3.0
	 */
	public function getModel($name = 'Key', $prefix = 'Administrator', $config = ['ignore_request' => true])
	{
		return parent::getModel($name, $prefix, $config);
	}
}
<?php
/*
 * @package    SW JProjects
 * @version    2.4.0
 * @author     Sergey Tolkachyov
 * @сopyright  Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
 */

namespace Joomla\Component\SWJProjects\Administrator\Controller;

use Joomla\CMS\MVC\Controller\FormController;
use function defined;

defined('_JEXEC') or die;

class DocumentController extends FormController
{
	/**
	 * The URL view list variable.
	 *
	 * @var  string
	 *
	 * @since  1.4.0
	 */
	protected $view_list = 'documentation';

	/**
	 * The prefix to use with controller messages.
	 *
	 * @var  string
	 *
	 * @since  1.4.0
	 */
	protected $text_prefix = 'COM_SWJPROJECTS_DOCUMENT';
}
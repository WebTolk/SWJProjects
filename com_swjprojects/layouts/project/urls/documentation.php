<?php
/**
 * @package       SW JProjects
 * @version       2.5.0
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

extract($displayData);

/**
 * Layout variables
 * -----------------
 *
 * @var  string $item SW JProject project
 *
 */

if ($item->documentation || $item->urls->get('documentation'))
{
	$link         = $item->documentation ? $item->documentation : $item->urls->get('documentation');
	$title        = '<i class="fas fa-file-alt"></i> '.Text::_('COM_SWJPROJECTS_DOCUMENTATION');
	$link_attribs = [
		'class' => 'btn btn-outline-info me-2 mb-2'
	];

	echo HTMLHelper::link($link, $title, $link_attribs);
}

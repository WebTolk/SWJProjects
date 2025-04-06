<?php
/**
 * @package       SW JProjects
 * @version       2.4.0.1
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
if ($item->versions)
{
	$link         = $item->versions;
	$title        = '<i class="fas fa-tag"></i> ' . Text::_('COM_SWJPROJECTS_VERSIONS');
	$link_attribs = [
		'class' => 'btn btn-outline-secondary me-2 mb-2'
	];

	echo HTMLHelper::link($link, $title, $link_attribs);
}
<?php
/*
 * @package    SW JProjects
 * @version    2.3.0
 * @author     Sergey Tolkachyov
 * @сopyright  Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
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

$link         = $item->link;
$title        = '<i class="fas fa-info-circle"></i> ' . Text::_('COM_SWJPROJECTS_PROJECT');
$link_attribs = [
	'class'             => 'btn btn-outline-primary me-2 mb-2',
];

echo HTMLHelper::link($link, $title, $link_attribs);

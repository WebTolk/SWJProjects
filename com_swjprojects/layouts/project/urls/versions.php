<?php
/*
 * @package    SW JProjects
 * @version    2.0.0
 * @author     Sergey Tolkachyov
 * @сopyright  Copyright (c) 2018 - 2024 Sergey Tolkachyov. All rights reserved.
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
if ($item->versions)
{
	$link         = $item->versions;
	$title        = '<i class="fas fa-tag"></i> ' . Text::_('COM_SWJPROJECTS_VERSIONS');
	$link_attribs = [
		'class' => 'btn btn-outline-secondary me-2'
	];

	echo HTMLHelper::link($link, $title, $link_attribs);
}
<?php
/*
 * @package    SW JProjects
 * @version    2.0.0-alpha3
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

if ($item->documentation)
{
	$link         = $item->documentation;
	$title        = '<i class="fas fa-file-alt"></i> '.Text::_('COM_SWJPROJECTS_DOCUMENTATION');
	$link_attribs = [
		'class' => 'btn btn-outline-info me-2'
	];

	echo HTMLHelper::link($link, $title, $link_attribs);
}
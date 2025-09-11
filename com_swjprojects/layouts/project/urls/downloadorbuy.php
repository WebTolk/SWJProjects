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
$link         = '';
$title        = '';
$link_attribs = [];

if (($item->download_type === 'paid' && $item->payment->get('link')))
{
	$link         = $item->payment->get('link');
	$title        = '<i class="fas fa-shopping-basket"></i>' . Text::_('COM_SWJPROJECTS_BUY');
	$link_attribs = [
		'class'             => 'btn btn-success me-2 mb-2',
		'data-btn-download' => true,
		'target'            => '_blank'
	];

}
elseif ($item->download_type === 'free')
{
	$link         = $item->download;
	$title        = '<i class="fas fa-download"></i> ' . Text::_('COM_SWJPROJECTS_DOWNLOAD');
	$link_attribs = [
		'class'             => 'btn btn-success me-2 mb-2',
		'data-btn-download' => true,
		'target'            => '_blank'
	];
}

echo HTMLHelper::link($link, $title, $link_attribs);

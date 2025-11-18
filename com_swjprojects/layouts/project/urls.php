<?php
/**
 * @package       SW JProjects
 * @version       2.6.0
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
 * @var  string $item            SW JProject project
 * @var  array  $include_buttons Show only specified buttons. Higher priority.
 * @var  array  $exclude_buttons Show ALL EXCEPT specified buttons
 *
 */

$buttons = [
	'projectlink'   => true,
	'downloadorbuy' => true,
	'versions'      => true,
	'documentation' => true,
	'demo'          => true,
	'support'       => true,
	'jed'           => true,
	'github'        => true,
	'donate'        => true
];

if (!empty($exclude_buttons) && !empty($include_buttons))
{
	$exclude_buttons = []; // Show only include buttons
}


foreach ($buttons as $button_name => $flag)
{

	if (in_array($button_name, $exclude_buttons))
	{
		$buttons[$button_name] = false;
	}
	// $include_buttons has higher priority
	if (!empty($include_buttons))
	{
		if (in_array($button_name, $include_buttons))
		{
			$buttons[$button_name] = true;
		}
		else
		{

			$buttons[$button_name] = false;
		}
	}

}

if ($buttons['projectlink'])
{
	echo $this->sublayout('projectlink', $displayData);
}

if ($buttons['downloadorbuy'])
{
	echo $this->sublayout('downloadorbuy', $displayData);
}

if ($buttons['versions'])
{
	echo $this->sublayout('versions', $displayData);
}

if ($buttons['documentation'])
{
	echo $this->sublayout('documentation', $displayData);
}

$url_texts = [

	'demo'    => '<i class="fas fa-external-link-alt"></i> ' . Text::_('COM_SWJPROJECTS_URLS_DEMO'),
	'support' => '<i class="fas fa-info-circle"></i> ' . Text::_('COM_SWJPROJECTS_URLS_SUPPORT'),
	'jed'     => '<i class="fab fa-joomla"></i> ' . Text::_('COM_SWJPROJECTS_URLS_JED'),
	'github'  => '<i class="fab fa-github-square"></i> ' . Text::_('COM_SWJPROJECTS_URLS_GITHUB'),
	'donate'  => '<i class="fas fa-donate"></i> ' . Text::_('COM_SWJPROJECTS_URLS_DONATE'),
];

$link_attribs = [
	'class' => 'btn btn-outline-secondary me-2 mb-2'
];

if ($urls = $item->urls->toArray())
{
	foreach ($urls as $txt => $url)
	{
		if (empty($url) || $txt == 'documentation' || !$url_texts[$txt])
		{
			continue;
		}
		echo HTMLHelper::link($url, $url_texts[$txt], $link_attribs);
	}
}

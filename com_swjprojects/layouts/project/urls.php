<?php

/**
 * @package       SW JProjects
 * @version       2.6.2
 * @Author        Sergey Tolkachyov
 * @copyright  Copyright (c) 2018 - 2026 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link          https://web-tolk.ru
 * @since         1.0.0
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\SWJProjects\Administrator\Helper\ProjectLinksHelper;

extract($displayData);

/**
 * Layout variables
 * -----------------
 *
 * @var  object $item            SW JProject project
 * @var  array  $include_buttons Show only specified buttons. Higher priority.
 * @var  array  $exclude_buttons Show ALL EXCEPT specified buttons
 *
 */

$buttons = [
    'projectlink'   => true,
    'downloadorbuy' => true,
    'versions'      => true,
    'documentation' => true,
];

$linkTypes = ProjectLinksHelper::getTypes();

foreach ($linkTypes as $type) {
    $code = (string) ($type['code'] ?? '');

    if ($code !== '' && !isset($buttons[$code])) {
        $buttons[$code] = true;
    }
}

if (!empty($exclude_buttons) && !empty($include_buttons)) {
    $exclude_buttons = []; // Show only include buttons
}

foreach ($buttons as $button_name => $flag) {

    if (in_array($button_name, $exclude_buttons)) {
        $buttons[$button_name] = false;
    }
    // $include_buttons has higher priority
    if (!empty($include_buttons)) {
        if (in_array($button_name, $include_buttons)) {
            $buttons[$button_name] = true;
        } else {

            $buttons[$button_name] = false;
        }
    }

}

if ($buttons['projectlink']) {
    echo $this->sublayout('projectlink', $displayData);
}

if ($buttons['downloadorbuy']) {
    echo $this->sublayout('downloadorbuy', $displayData);
}

if ($buttons['versions']) {
    echo $this->sublayout('versions', $displayData);
}

if ($buttons['documentation']) {
    echo $this->sublayout('documentation', $displayData);
}

$linkTypesByCode = [];

foreach ($linkTypes as $type) {
    $code = (string) ($type['code'] ?? '');

    if ($code !== '') {
        $linkTypesByCode[$code] = $type;
    }
}

$link_attribs = [
    'class' => 'btn btn-outline-secondary me-2 mb-2'
];

if ($urls = ProjectLinksHelper::normalizeLinks($item->urls)) {
    foreach ($urls as $link) {
        $txt = $link['type'];
        $url = $link['value'];

        if (empty($url) || $txt == 'documentation' || empty($buttons[$txt])) {
            continue;
        }

        $type      = $linkTypesByCode[$txt] ?? [];
        $title     = trim((string) ($link['title'] ?? ''));
        $title     = $title !== '' ? $title : (string) ($type['title'] ?? $txt);
        $iconClass = trim((string) ($type['icon_class'] ?? ''));
        $text      = ($iconClass !== '' ? '<i class="' . $iconClass . '"></i> ' : '') . Text::_($title);

        if (($type['value_type'] ?? 'url') === 'email' && stripos($url, 'mailto:') !== 0) {
            $url = 'mailto:' . $url;
        }

        echo HTMLHelper::link($url, $text, $link_attribs);
    }
}

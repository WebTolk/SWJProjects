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
 * @var  string $item SW JProject project
 *
 */

$documentationUrl = '';

foreach (ProjectLinksHelper::normalizeLinks($item->urls) as $link) {
    if ($link['type'] === 'documentation') {
        $documentationUrl = $link['value'];
        break;
    }
}

if ($item->documentation || $documentationUrl !== '') {
    $link         = $item->documentation ? $item->documentation : $documentationUrl;
    $title        = '<i class="fas fa-file-alt"></i> ' . Text::_('COM_SWJPROJECTS_DOCUMENTATION');
    $link_attribs = [
        'class' => 'btn btn-outline-info me-2 mb-2'
    ];

    echo HTMLHelper::link($link, $title, $link_attribs);
}

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
use Joomla\Component\SWJProjects\Site\Helper\RouteHelper;

?>
<ul class="nav my-2 py-1 flex-nowrap overflow-auto flex-lg-wrap categoriesList">
    <?php foreach ($items as $item) : ?>
        <li>
            <?php
                echo HTMLHelper::link(
	                RouteHelper::getProjectsRoute($item->id), // URL
                    $item->title, // Link text
                    [
					'class' => 'btn btn-sm btn-light text-nowrap text-dark'
					] // attribs, like 'class' => 'btn btn-danger' or 'data-category-id' => $item->id
                );
            ?>
        </li>
    <?php endforeach; ?>
</ul>
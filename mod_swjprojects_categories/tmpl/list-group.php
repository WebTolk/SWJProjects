<?php
/**
 * @package    SW JProjects
 * @version    2.0.1
 * @author     Sergey Tolkachyov
 * @сopyright  Copyright (c) 2018 - 2024 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
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
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

use Joomla\CMS\Language\Text;

extract($displayData);

/**
 * Layout variables
 * -----------------
 *
 * @var  string $item SW JProject project
 *
 */
?>
<?php if ($item->hits && $item->downloads): ?>
    <span class="badge bg-light text-dark pe-0 pe-md-2">
        <i class="far fa-chart-bar" title="CTR"></i> CTR <?php echo round((((int) $item->downloads / (int) $item->hits)) * 100); ?>%
    </span>
<?php endif; ?>

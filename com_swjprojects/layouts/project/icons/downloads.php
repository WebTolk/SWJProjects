<?php
/*
 * @package    SW JProjects
 * @version    2.1.1
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
<?php if ($item->downloads): ?>
    <span class="badge bg-light text-dark pe-0 pe-md-2">
        <i class="fas fa-download"
           title="<?php echo Text::_('COM_SWJPROJECTS_STATISTICS_DOWNLOADS'); ?>"></i>
        <span class="visually-hidden"><?php echo Text::_('COM_SWJPROJECTS_STATISTICS_DOWNLOADS'); ?></span>
        <?php echo $item->downloads; ?>
    </span>
<?php endif; ?>
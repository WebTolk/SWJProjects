<?php
/**
 * @package       SW JProjects
 * @version       2.4.0
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
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
<?php if ($item->hits): ?>
    <span class="badge bg-light text-dark pe-0 pe-md-2">
        <i class="far fa-eye" title="<?php echo Text::_('COM_SWJPROJECTS_STATISTICS_HITS'); ?>"></i>
            <span class="visually-hidden"><?php echo Text::_('COM_SWJPROJECTS_STATISTICS_HITS'); ?></span>
			    <?php echo $item->hits; ?>
    </span>
<?php endif; ?>

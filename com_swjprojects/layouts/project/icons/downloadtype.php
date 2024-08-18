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

use Joomla\CMS\Language\Text;

extract($displayData);

/**
 * Layout variables
 * -----------------
 *
 * @var  string $item SW JProject project
 *
 */

if (!empty($item->download_type)): ?>
    <span class="badge bg-dark text-white"
          title="<?php echo Text::_('COM_SWJPROJECTS_DOWNLOAD_TYPE_' . $item->download_type); ?>"> <?php echo Text::_('COM_SWJPROJECTS_DOWNLOAD_TYPE_' . $item->download_type); ?></span>
<?php endif; ?>

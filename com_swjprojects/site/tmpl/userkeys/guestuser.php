<?php
/**
 * @package    SW JProjects
 * @version    2.4.0
 * @author     Sergey Tolkachyov
 * @сopyright  Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
 */
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useStyle('fontawesome');
?>
<section id="SWJProjects" class="userkeys guestuser">
    <h1><?php echo Text::_('COM_SWJPROJECTS_USER_KEYS');?></h1>
    <div class="d-flex flex-column justify-content-center align-items-center py-5">
            <i class="fa-solid fa-lock fa-8x mb-3"></i>
            <h2><?php echo Text::_('COM_SWJPROJECTS_USER_KEYS_USER_NOT_LOGGED_IN');?></h2>
            <p><?php echo Text::_('COM_SWJPROJECTS_USER_KEYS_USER_NOT_LOGGED_IN_DESC');?></p>
    </div>
</section>

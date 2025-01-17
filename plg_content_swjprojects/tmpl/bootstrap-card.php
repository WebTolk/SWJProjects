<?php
/**
 * @package    SW JProjects
 * @version       2.2.1
 * @Author        Sergey Tolkachyov, https://web-tolk.ru
 * @copyright     Copyright (C) 2024 Sergey Tolkachyov
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @since         1.0.0
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * @var object $insert_project Project from short code
 * @var object $project Original project object
 */

// For full project object info uncomment this
//  echo "<pre>";
//  print_r($insert_project);
//  echo "</pre>";

?>
<div class="card">
    <div class="card-body">
        <h3 class="card-title"><?php echo $insert_project->title; ?></h3>
        <?php echo !empty($insert_project->introtext) ? '<p>'. $insert_project->introtext.'</p>' : ''; ?>
        <?php
            $link_attribs = [
                    'class' => 'btn btn-primary',
                    'target' => '_blank',
            ];
            // $url, $text, $attributes
            echo HTMLHelper::link($insert_project->link, Text::_('JDETAILS'), $link_attribs);
        ?>
    </div>
</div>
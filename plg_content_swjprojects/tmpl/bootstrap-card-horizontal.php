<?php
/**
 * @package    SW JProjects
 * @version       2.1.1
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
 * @var object $project        Original project object
 */

// For full project object info uncomment this
//  echo "<pre>";
//  print_r($insert_project);
//  echo "</pre>";
$icon  = $insert_project->images->get('icon');
$cover = $insert_project->images->get('cover');

?>

<div class="card mb-3">
    <div class="row g-0">
        <?php if($icon): ?>
        <div class="col-4 col-md-2">
			<?php

				$img_attribs = [
					'class' => 'img-fluid rounded-start'
				];
				echo HTMLHelper::image($icon, $insert_project->title, $img_attribs);
			?>
        </div>
        <?php endif; ?>
        <div class="<?php echo $icon ? 'col-8 col-md-10' : 'col-12' ?>">
            <div class="card-body">
                <h3 class="card-title"><?php echo $insert_project->title; ?></h3>
				<?php echo !empty($insert_project->introtext) ? '<p class="card-text">' . $insert_project->introtext . '</p>' : ''; ?>
				<?php
				$link_attribs = [
					'class'  => 'btn btn-primary',
					'target' => '_blank',
				];
				// $url, $text, $attributes
				echo HTMLHelper::link($insert_project->link, Text::_('JDETAILS'), $link_attribs);
				?>
            </div>
        </div>
    </div>
</div>
<?php
/*
 * @package    SW JProjects
 * @version    2.3.0
 * @author     Sergey Tolkachyov
 * @сopyright  Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

?>
<div id="SWJProjects" class="documentation">
    <div class="project info mb-3">
        <h1><?php echo $this->project->title . ' - ' . Text::_('COM_SWJPROJECTS_DOCUMENTATION'); ?></h1>
        <div>
			<?php if (!empty($this->project->categories)): ?>
                <strong><?php echo Text::_('COM_SWJPROJECTS_CATEGORIES'); ?>: </strong>
				<?php $i = 0;
				foreach ($this->project->categories as $category)
				{
					if ($i > 0) echo ', ';
					$i++;
					echo '<a href="' . $category->link . '">' . $category->title . '</a>';
				}
				?>
			<?php else: ?>
                <strong><?php echo Text::_('COM_SWJPROJECTS_CATEGORY'); ?>: </strong>
                <a href="<?php echo $this->category->link; ?>">
					<?php echo $this->category->title; ?>
                </a>
			<?php endif; ?>
        </div>
    </div>

    <div class="d-flex flex-wrap mb-3 project-buttons">
		<?php
		// Get content from plugins
		echo $this->project->event->beforeProjectButtons;

		// see button names in layouts/components/swjprojects/project/urls.php
		echo LayoutHelper::render('components.swjprojects.project.urls',
			[
				'item'            => $this->project,
				'include_buttons' => [], // Show only specified buttons. Higher priority.
				'exclude_buttons' => ['documentation'], // Show ALL EXCEPT specified buttons
			]);

		// Get content from plugins
		echo $this->project->event->afterProjectButtons;
		?>
    </div>

	<?php if (empty($this->items)) : ?>
        <div class="alert alert-info">
            <span class="icon-info-circle" aria-hidden="true"></span><span
                    class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
			<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
        </div>
	<?php else : ?>
        <div class="documentationList">
            <div class="items">
				<?php foreach ($this->items as $item) : ?>
                    <div class="item-<?php echo $item->id; ?> card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="<?php echo $item->link; ?>"><?php echo $item->title; ?></a>
                            </h5>
							<?php if (!empty($item->introtext)): ?>
                                <p><?php echo nl2br($item->introtext); ?></p>
							<?php endif; ?>
                            <div class="text-end">
                                <a href="<?php echo $item->link; ?>"
                                   class="btn btn-outline-primary btn-sm ms-1 mb-1">
									<?php echo Text::_('COM_SWJPROJECTS_MORE'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
				<?php endforeach ?>
            </div>
            <div class="pagination">
				<?php echo $this->pagination->getPagesLinks(); ?>
            </div>
        </div>
	<?php endif; ?>
</div>

<?php
/**
 * @package       SW JProjects
 * @version       2.6.1-dev
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
HTMLHelper::stylesheet('com_swjprojects/site.css', array('version' => 'auto', 'relative' => true));
?>
<div id="SWJProjects" class="versions">
    <div class="project info mb-3">
        <h1><?php echo $this->project->title . ' - ' . Text::_('COM_SWJPROJECTS_VERSIONS'); ?></h1>
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
				'item' => $this->project,
				'include_buttons' => [], // Show only specified buttons. Higher priority.
				'exclude_buttons'=> ['versions'], // Show ALL EXCEPT specified buttons
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
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 versionsList">
			<?php foreach ($this->items as $i => $item): ?>
                <div class="col mb-3">
                    <div class="card h-100 shadow-hover">
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="<?php echo $item->link; ?>"><?php echo $item->version->version; ?></a>
                            </h5>
                            <ul class="list-unstyled">
                                <li>
                                    <strong><?php echo Text::_('JDATE'); ?>: </strong>
									<?php echo HTMLHelper::_('date', $item->date, Text::_('DATE_FORMAT_LC3')); ?>
                                </li>
                                <li>
                                    <strong><?php echo Text::_('COM_SWJPROJECTS_VERSION_TAG'); ?>
                                        : </strong>
                                    <span class="text-<?php echo ($item->tag->key == 'stable') ? 'success' : 'error'; ?>">
															<?php echo $item->tag->title; ?>
														</span>
                                </li>
								<?php if (!empty($item->joomla_version)): ?>
                                    <li>
                                        <strong><?php echo Text::_('COM_SWJPROJECTS_JOOMLA_VERSION'); ?>
                                            : </strong>
										<?php echo $item->joomla_version; ?>
                                    </li>
								<?php endif; ?>
								<?php if ($item->downloads): ?>
                                    <li>
                                        <strong><?php echo Text::_('COM_SWJPROJECTS_STATISTICS_DOWNLOADS'); ?>
                                            : </strong>
										<?php echo $item->downloads; ?>
                                    </li>
								<?php endif; ?>
                            </ul>
                        </div>
	                    <?php if ($item->download_type === 'free'): ?>
                            <div class="card-footer bg-transparent border-0">
                                <a href="<?php echo $item->download; ?>" target="_blank"
                                   class="btn col-12 btn-<?php echo ($item->tag->key == 'stable') ? 'success' : 'secondary'; ?> float-end">
				                    <?php echo Text::_('COM_SWJPROJECTS_DOWNLOAD'); ?>
                                </a>
                            </div>
	                    <?php endif; ?>
                    </div>
                </div>
			<?php endforeach; ?>
        </div>
        <div class="pagination">
			<?php echo $this->pagination->getPagesLinks(); ?>
        </div>
	<?php endif; ?>

</div>

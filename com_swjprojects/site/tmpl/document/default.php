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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

// Check if we have only 1 document for extension
$isSingleDocument = (\count($this->documentation_items) == 1) ? true : false;
?>
<div id="SWJProjects" class="document">
    <div class="project info mb-3">
        <div class="h1"><?php echo $this->project->title . ' - ' . Text::_('COM_SWJPROJECTS_DOCUMENTATION'); ?></div>
        <div class="mb-3">
			<?php if (!empty($this->project->categories)): ?>
                <strong><?php echo Text::_('COM_SWJPROJECTS_CATEGORIES'); ?>: </strong>
				<?php
				foreach ($this->project->categories as $category)
				{
					$links = [];
					foreach ($this->project->categories as $category)
					{
						$links[] = HTMLHelper::link($category->link, $category->title);
					}
					echo !empty($links) ? implode(', ',$links) : '';
				}
				?>
			<?php else: ?>
                <strong><?php echo Text::_('COM_SWJPROJECTS_CATEGORY'); ?>: </strong>
                <a href="<?php echo $this->category->link; ?>">
					<?php echo $this->category->title; ?>
                </a>
			<?php endif; ?>
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
					'exclude_buttons' => ['downloadorbuy'], // Show ALL EXCEPT specified buttons
				]);

			// Get content from plugins
			echo $this->project->event->afterProjectButtons;
			?>

        </div>
    </div>
    <div class="row">
		<?php if (!$isSingleDocument): ?>
            <div class="col-lg-3 mb-3">
                <div class="card">
                    <nav class="navbar navbar-expand-lg">
                        <div class="container-fluid d-flex flex-lg-column align-items-start">
                            <h2 class="h5 ms-3"><?php echo Text::_('COM_SWJPROJECTS_DOCUMENTATION_TOC'); ?></h2>
                            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#navbaDocumentationTableOfContents"
                                    aria-controls="navbarDocumentationTableOfContents"
                                    aria-expanded="false" aria-label="Toggle navigation">
                                <span class="navbar-toggler-icon"></span>
                            </button>
                            <div class="collapse navbar-collapse" id="navbaDocumentationTableOfContents">
                                <div class="list-group list-group-flush">
									<?php foreach ($this->documentation_items as $documentationItem)
									{
										$link_attribs = [
											'class' => 'list-group-item list-group-item-action'
										];
										echo HTMLHelper::link($documentationItem['link'], $documentationItem['title'], $link_attribs);
									}
									?>
                                </div>
                            </div>
                        </div>
                    </nav>
                </div>
            </div>
		<?php endif; ?>
        <div class="<?php echo $isSingleDocument ? 'col-12' : 'col-lg-9'; ?>">
            <div class="card">
                <div class="card-body">
                    <h1 class="h2">
						<?php echo $this->item->title; ?>
                    </h1>
					<?php if (!empty($this->item->introtext)): ?>
                        <p><?php echo nl2br($this->item->introtext); ?></p>
					<?php endif; ?>
					<?php if (!empty($this->item->fulltext)): ?>
                        <div><?php echo $this->item->fulltext; ?></div>
					<?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>


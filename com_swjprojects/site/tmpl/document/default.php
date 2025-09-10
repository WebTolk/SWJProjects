<?php
/**
 * @package       SW JProjects
 * @version       2.5.0-alhpa1
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

defined('_JEXEC') or die;

// Check if we have only 1 document for extension
$isSingleDocument = (count($this->documentation_items) == 1) ? true : false;
?>
<div id="SWJProjects" class="document">
    <div class="project info mb-3">
        <hgroup class="mb-5">
            <h1><?php echo $this->item->title; ?></h1>
            <div class="h3"><?php echo $this->project->title . ' - ' . Text::_('COM_SWJPROJECTS_DOCUMENTATION'); ?></div>
        </hgroup>
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
					echo !empty($links) ? implode(', ', $links) : '';
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
                            <div class="w-100 d-flex justify-content-between align-items-center">
                                <h2 class="h5"><?php echo Text::_('COM_SWJPROJECTS_DOCUMENTATION_TOC'); ?></h2>
                                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#navbaDocumentationTableOfContents"
                                        aria-controls="navbarDocumentationTableOfContents"
                                        aria-expanded="false" aria-label="Toggle navigation">
                                    <span class="navbar-toggler-icon fas fa-bars"></span>
                                </button>
                            </div>
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

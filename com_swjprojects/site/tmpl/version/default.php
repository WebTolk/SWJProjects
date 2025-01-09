<?php
/*
 * @package    SW JProjects
 * @version    2.2.0
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
<div id="SWJProjects" class="version">
    <div class="version info mb-3">
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
	<div class="row mb-3">
		<div class="col-md-3">
			<div class="card">
				<?php if ($icon = $this->project->images->get('icon')): ?>
					<a href="<?php echo $this->project->link; ?>">
						<?php echo HTMLHelper::image($icon, $this->project->title, array('class' => 'card-img-top')); ?>
					</a>
				<?php endif; ?>
				<div class="card-body">
					<ul class="list-unstyled small">
						<li>
							<strong><?php echo Text::_('COM_SWJPROJECTS_DOWNLOAD_TYPE'); ?>: </strong>
							<?php echo Text::_('COM_SWJPROJECTS_DOWNLOAD_TYPE_' . $this->version->download_type); ?>
						</li>
						<?php if ($this->version->download_type === 'paid' && $this->version->payment->get('price')): ?>
							<li>
								<strong><?php echo Text::_('COM_SWJPROJECTS_PRICE'); ?>: </strong>
								<span class="text-success"><?php echo $this->version->payment->get('price'); ?></span>
							</li>
						<?php endif; ?>
						<?php if ($this->version->version->version): ?>
							<li>
								<strong><?php echo Text::_('COM_SWJPROJECTS_VERSION'); ?>: </strong>
								<span><?php echo $this->version->version->version; ?></span>
							</li>
                        <li>
                            <strong><?php echo Text::_('JDATE');?>:</strong> <time><?php echo HTMLHelper::_('date', $this->version->date, Text::_('DATE_FORMAT_LC3')); ?></time>
                        </li>
						<?php endif; ?>
						<?php if ($this->version->downloads): ?>
							<li>
								<strong><?php echo Text::_('COM_SWJPROJECTS_STATISTICS_DOWNLOADS'); ?>: </strong>
								<?php echo $this->version->downloads; ?>
							</li>
						<?php endif; ?>
					</ul>
					<div class="text-center">
						<?php if (($this->version->download_type === 'paid' && $this->version->payment->get('link'))): ?>
							<a href="<?php echo $this->version->payment->get('link'); ?>"
							   class="btn btn-success col-12">
								<?php echo Text::_('COM_SWJPROJECTS_BUY'); ?>
							</a>
						<?php elseif ($this->version->download_type === 'free'): ?>
							<a href="<?php echo $this->version->download; ?>" class="btn btn-primary col-12"
							   target="_blank">
								<?php echo Text::_('COM_SWJPROJECTS_DOWNLOAD'); ?>
							</a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-9">
            <div class="d-flex flex-wrap mb-3 project-buttons">
				<?php
				// Get content from plugins
				echo $this->project->event->beforeProjectButtons;

				// see button names in layouts/components/swjprojects/project/urls.php
				echo LayoutHelper::render('components.swjprojects.project.urls',
					[
						'item' => $this->project,
						'include_buttons' => [], // Show only specified buttons. Higher priority.
						'exclude_buttons'=> ['downloadorbuy'], // Show ALL EXCEPT specified buttons
					]);

				// Get content from plugins
				echo $this->project->event->afterProjectButtons;
				?>

            </div>
            <div class="changelog card-body">
			<?php
			/**
			 * @var bool $isSingleChangelog Flag if we have a single item in our changelog list
			 */
			$isSingleChangelog = (\count($this->version->changelog) == 1) ? true : false;

			foreach ($this->version->changelog as $item):
				if (empty($item['title']) && empty($item['description'])) continue;
				?>
                <section class="row <?php echo (!$isSingleChangelog ? 'border-bottom':'');?> py-3">
                    <div class="col-12 col-md-2 col-xl-1">
						<?php if (!empty($item['type']))
						{
							/**
							 * params
							 * - type - changelog item type - security, fix etc.
							 * - class - badge css class, For ex. 'badge bg-danger'. If empty - default Bootstrap 5 classes used
							 * - css_classes_array - associative array of css classes for badge For ex. 'fix' => 'badge bg-warning'. If empty - default Bootstrap 5 classes used
							 */


							echo LayoutHelper::render('components.swjprojects.changelog.badge', [
								'type'              => $item['type'],
								'class'             => '',
								'css_classes_array' => [],
							]);
						}
						?>
                    </div>
                    <div class="col-12 col-md-10 col-xl-11 ps-3">
						<?php if (!empty($item['title'])): ?>
                            <h3><?php echo $item['title']; ?></h3>
						<?php endif; ?>
						<?php if (!empty($item['description'])): ?>
                            <div class="description"><?php echo $item['description']; ?></div>
						<?php endif; ?>
                    </div>
                </section>
			<?php endforeach; ?>
            </div>
<!--			<div class="card">-->
<!--				<div class="changelog card-body">-->
<!--					<h2 class="h3">--><?php //echo Text::_('COM_SWJPROJECTS_VERSION_CHANGELOG') . ': '; ?><!--</h2>-->
<!---->
<!---->
<!---->
<!--                    <div class="items">-->
<!--						--><?php
//						$i = 0;
//						foreach ($this->version->changelog as $item):
//							if (empty($item['title']) && empty($item['description'])) continue;
//							if ($i > 0) echo '<hr>';
//							$i++;
//							?>
<!--							<div class="item">-->
<!--								<div class="d-flex justify-content-between align-items-center">-->
<!--									--><?php //if (!empty($item['title'])): ?>
<!--										<h3 class="h5">--><?php //echo $item['title']; ?><!--</h3>-->
<!--									--><?php //endif; ?>
<!---->
<!--									--><?php //if (!empty($item['type'])) {
//										/**
//										 * params
//										 * - type - changelog item type - security, fix etc.
//										 * - class - badge css class, For ex. 'badge bg-danger'. If empty - default Bootstrap 5 classes used
//										 * - css_classes_array - associative array of css classes for badge For ex. 'fix' => 'badge bg-warning'. If empty - default Bootstrap 5 classes used
//										 */
//
//	//								$css_classes_array = [
//	//									'security' => 'label label-important',
//	//									'fix' => 'label label-inverse',
//	//									'language' => 'label label-info',
//	//									'addition' => 'label label-success',
//	//									'change' => 'label label-warning',
//	//									'remove' => 'label',
//	//									'note' => 'label label-info',
//	//								];
//										echo LayoutHelper::render('components.swjprojects.changelog.badge', [
//											'type' => $item['type'],
//	//										'class' => 'h1',
//	//										'css_classes_array' => $css_classes_array,
//										]);
//									}
//									?>
<!--								</div>-->
<!--								--><?php //if (!empty($item['description'])): ?>
<!--									<div class="description">--><?php //echo $item['description']; ?><!--</div>-->
<!--								--><?php //endif; ?>
<!--							</div>-->
<!--						--><?php //endforeach; ?>
<!--					</div>-->
<!--				</div>-->
<!--			</div>-->
		</div>
	</div>
</div>
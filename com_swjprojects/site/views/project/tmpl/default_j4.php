<?php
/*
 * @package    SW JProjects Component
 * @version    1.6.4
 * @author Septdir Workshop, <https://septdir.com>, Sergey Tolkachyov <https://web-tolk.ru>
 * @сopyright (c) 2018 - April 2023 Septdir Workshop, Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link https://septdir.com, https://web-tolk.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

HTMLHelper::script('com_swjprojects/popup.min.js', array('version' => 'auto', 'relative' => true));
?>
<div id="SWJProjects" class="project">
	<div class="project info mb-3">
		<h1><?php echo $this->project->title; ?></h1>
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
				<?php if ($icon = $this->project->images->get('icon'))
				{
					echo HTMLHelper::image($icon, $this->project->title, array('class' => 'card-img-top'));
				} ?>
				<div class="card-body">
					<ul class="list-unstyled small">
						<li>
							<strong><?php echo Text::_('COM_SWJPROJECTS_DOWNLOAD_TYPE'); ?>: </strong>
							<?php echo Text::_('COM_SWJPROJECTS_DOWNLOAD_TYPE_' . $this->project->download_type); ?>
						</li>
						<?php if ($this->project->download_type === 'paid' && $this->project->payment->get('price')): ?>
							<li>
								<strong><?php echo Text::_('COM_SWJPROJECTS_PRICE'); ?>: </strong>
								<span class="text-success"><?php echo $this->project->payment->get('price'); ?></span>
							</li>
						<?php endif; ?>
						<?php if ($this->version): ?>
							<li>
								<strong><?php echo Text::_('COM_SWJPROJECTS_VERSION'); ?>: </strong>
								<a href="<?php echo $this->version->link; ?>"
								   class="<?php echo ($this->version->tag_key !== 'stable') ? 'text-error' : ''; ?>">
									<?php echo $this->version->version; ?>
								</a>
							</li>
						<?php endif; ?>
						<?php if ($this->project->downloads): ?>
							<li>
								<strong><?php echo Text::_('COM_SWJPROJECTS_STATISTICS_DOWNLOADS'); ?>: </strong>
								<?php echo $this->project->downloads; ?>
							</li>
						<?php endif; ?>

						<?php if ($this->project->hits): ?>
							<li>
								<strong><?php echo Text::_('COM_SWJPROJECTS_STATISTICS_HITS'); ?>: </strong>
								<?php echo $this->project->hits; ?>
							</li>
						<?php endif; ?>
					</ul>
					<div class="text-center">
						<?php if ($this->project->download_type === 'paid' && $this->project->payment->get('link') && !empty($this->project->version)): ?>
							<a href="<?php echo $this->project->payment->get('link'); ?>"
							   class="btn btn-success col-12">
								<?php echo Text::_('COM_SWJPROJECTS_BUY'); ?>
							</a>
						<?php elseif ($this->project->download_type === 'free' && !empty($this->project->version)): ?>
							<a href="<?php echo $this->project->download; ?>" class="btn btn-primary col-12"
							   target="_blank">
								<?php echo Text::_('COM_SWJPROJECTS_DOWNLOAD'); ?>
							</a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-9">
			<div class="mb-3">
				<a href="<?php echo $this->project->link; ?>"
				   class="btn btn-outline-primary btn-sm ms-1 mb-1">
					<?php echo Text::_('COM_SWJPROJECTS_PROJECT'); ?>
				</a>
				<a href="<?php echo $this->project->versions; ?>"
				   class="btn btn-outline-primary btn-sm ms-1 mb-1">
					<?php echo Text::_('COM_SWJPROJECTS_VERSIONS'); ?>
				</a>
				<?php if ($this->project->documentation): ?>
					<a href="<?php echo $this->project->documentation; ?>"
					   class="btn btn-outline-primary btn-sm ms-1 mb-1">
						<?php echo Text::_('COM_SWJPROJECTS_DOCUMENTATION'); ?>
					</a>
				<?php endif; ?>
				<?php if ($urls = $this->project->urls->toArray()): ?>
					<?php foreach ($urls as $txt => $url):
						if (empty($url)) continue; ?>
						<a href="<?php echo $url; ?>" target="_blank"
						   class="btn btn-outline-primary btn-sm ms-1 mb-1">
							<?php echo Text::_('COM_SWJPROJECTS_URLS_' . $txt); ?>
						</a>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<div class="card mb-3">
				<div class="card-body">
					<?php if (!empty($this->project->introtext)): ?>
						<p class="description">
							<?php echo $this->project->introtext; ?>
						</p>
					<?php endif; ?>
					<?php if (!empty($this->project->fulltext)): ?>
						<div class="fulltext">
							<?php echo $this->project->fulltext; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<?php echo HTMLHelper::_('uitab.startTabSet', 'projectTab', array('active' => 'whats_new', 'class')); ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'projectTab', 'whats_new', Text::_('COM_SWJPROJECTS_WHATS_NEW')); ?>
			<?php if ($this->version && !empty($this->version->changelog)): ?>
				<?php foreach ($this->version->changelog as $item):
					if (empty($item['title']) && empty($item['description'])) continue;
					?>
					<div class="item">
						<div class="d-flex justify-content-between align-items-center">
							<?php if (!empty($item['title'])): ?>
								<h3><?php echo $item['title']; ?></h3>
							<?php endif; ?>
							<?php if (!empty($item['type'])) {
								/**
								 * params
								 * - type - changelog item type - security, fix etc.
								 * - class - badge css class, For ex. 'badge bg-danger'. If empty - default Bootstrap 5 classes used
								 * - css_classes_array - associative array of css classes for badge For ex. 'fix' => 'badge bg-warning'. If empty - default Bootstrap 5 classes used
								 */

//								$css_classes_array = [
//									'security' => 'label label-important',
//									'fix' => 'label label-inverse',
//									'language' => 'label label-info',
//									'addition' => 'label label-success',
//									'change' => 'label label-warning',
//									'remove' => 'label',
//									'note' => 'label label-info',
//								];
									echo LayoutHelper::render('components.swjprojects.changelog.badge', [
										'type' => $item['type'],
//										'class' => 'h1',
//										'css_classes_array' => $css_classes_array,
									]);
								}
								?>
						</div>
						<?php if (!empty($item['description'])): ?>
							<div class="description"><?php echo $item['description']; ?></div>
						<?php endif; ?>
					</div>
					<hr>
				<?php endforeach; ?>
				<div class="text-right small muted">
					<?php echo HTMLHelper::_('date', $this->version->date, Text::_('DATE_FORMAT_LC6')); ?>
				</div>
			<?php endif; ?>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
			<?php if ($this->project->joomla):
				$type = $this->project->joomla->get('type'); ?>
				<?php echo HTMLHelper::_('uitab.addTab', 'projectTab', 'joomla', Text::_('COM_SWJPROJECTS_JOOMLA')); ?>
				<ul class="list-unstyled">
					<li>
						<strong><?php echo Text::_('COM_SWJPROJECTS_JOOMLA_TYPE'); ?>: </strong>
						<?php echo Text::_('COM_SWJPROJECTS_JOOMLA_TYPE_' . $type); ?>
					</li>
					<?php if ($type === 'plugin'): ?>
						<li>
							<strong><?php echo Text::_('COM_SWJPROJECTS_JOOMLA_FOLDER'); ?>: </strong>
							<?php echo utf8_ucfirst($this->project->joomla->get('folder')); ?>
						</li>
					<?php endif; ?>
					<?php if ($type === 'template' || $type === 'module'): ?>
						<li>
							<strong><?php echo Text::_('COM_SWJPROJECTS_JOOMLA_LOCATION'); ?>: </strong>
							<?php echo ($this->project->joomla->get('client_id')) ?
								Text::_('COM_SWJPROJECTS_JOOMLA_LOCATION_ADMINISTRATOR')
								: Text::_('COM_SWJPROJECTS_JOOMLA_LOCATION_SITE') ?>
						</li>
					<?php endif; ?>
					<?php if ($type === 'package' && !empty($this->project->joomla->get('package_composition'))): ?>
						<li>
							<strong><?php echo Text::_('COM_SWJPROJECTS_JOOMLA_PACKAGE_COMPOSITION'); ?>: </strong>
							<?php
							$compositions = array();
							foreach ($this->project->joomla->get('package_composition') as $composition)
							{
								$compositions[] = Text::_('COM_SWJPROJECTS_JOOMLA_TYPE_' . $composition);
							}
							echo implode(', ', $compositions); ?>
						</li>
					<?php endif; ?>
					<?php if ($this->project->joomla->get('version')): ?>
						<li>
							<strong><?php echo Text::_('COM_SWJPROJECTS_JOOMLA_VERSION'); ?>: </strong>
							<?php echo implode(', ', $this->project->joomla->get('version')); ?>
						</li>
					<?php endif; ?>
				</ul>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>
			<?php endif; ?>
			<?php if ($this->project->gallery): ?>
				<?php echo HTMLHelper::_('uitab.addTab', 'projectTab', 'gallery', Text::_('COM_SWJPROJECTS_IMAGES_GALLERY')); ?>
				<?php foreach (array_chunk($this->project->gallery, 2) as $r => $row): ?>
					<div class="row">
						<?php foreach ($row as $image): ?>
							<div class="col-md-6 mb-3">
								<a href="<?php echo $image->src; ?>" popup target="_blank">
									<?php echo HTMLHelper::image($image->src, htmlspecialchars($image->text)); ?>
									<?php if ($image->text): ?>
										<div class="lead"><?php echo $image->text; ?></div>
									<?php endif; ?>
								</a>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endforeach; ?>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>
			<?php endif; ?>

			<?php if (!empty($this->relations)): ?>
				<?php echo HTMLHelper::_('uitab.addTab', 'projectTab', 'relations', Text::_('COM_SWJPROJECTS_RELATIONS')); ?>
				<?php foreach (array_chunk($this->relations, 2) as $r => $row): ?>
					<div class="row">
						<?php foreach ($row as $relation): ?>
							<div class="col-md-6 mb-3">
								<a href="<?php echo $relation['link']; ?>" target="_blank">
									<div class="h5"><?php echo $relation['title']; ?></div>
									<?php echo HTMLHelper::image($relation['icon'], htmlspecialchars($relation['title'])); ?>
								</a>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endforeach; ?>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>
			<?php endif; ?>
			<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
		</div>
	</div>
</div>

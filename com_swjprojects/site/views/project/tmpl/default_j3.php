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

HTMLHelper::stylesheet('com_swjprojects/site.min.css', array('version' => 'auto', 'relative' => true));
HTMLHelper::script('com_swjprojects/popup.min.js', array('version' => 'auto', 'relative' => true));
?>
<div id="SWJProjects" class="project">
	<?php if ($cover = $this->project->images->get('cover')): ?>
		<p class="cover"><?php echo HTMLHelper::image($cover, $this->project->title); ?></p>
		<hr>
	<?php endif; ?>
	<div class="project info well">
		<div class="row-fluid">
			<?php if ($icon = $this->project->images->get('icon')): ?>
				<div class="span3"><?php echo HTMLHelper::image($icon, $this->project->title); ?></div>
			<?php endif; ?>
			<div class="<?php echo ($icon) ? 'span9' : ''; ?>">
				<h1><?php echo $this->project->title; ?></h1>
				<?php if (!empty($this->project->introtext)): ?>
					<p class="description">
						<?php echo $this->project->introtext; ?>
					</p>
				<?php endif; ?>
				<div class="meta">
					<ul class="inline">
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
						<?php if (!empty($this->project->categories)): ?>
							<li>
								<strong><?php echo Text::_('COM_SWJPROJECTS_CATEGORIES'); ?>: </strong>
								<?php $i = 0;
								foreach ($this->project->categories as $category)
								{
									if ($i > 0) echo ', ';
									$i++;
									echo '<a href="' . $category->link . '">' . $category->title . '</a>';
								}
								?>
							</li>
						<?php else: ?>
							<li>
								<strong><?php echo Text::_('COM_SWJPROJECTS_CATEGORY'); ?>: </strong>
								<a href="<?php echo $this->category->link; ?>">
									<?php echo $this->category->title; ?>
								</a>
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
					<div class="buttons">
						<?php if ($this->project->download_type === 'paid' && $this->project->payment->get('link') && !empty($this->project->version)): ?>
							<a href="<?php echo $this->project->payment->get('link'); ?>" class="btn btn-success">
								<?php echo Text::_('COM_SWJPROJECTS_BUY'); ?>
							</a>
						<?php elseif ($this->project->download_type === 'free' && !empty($this->project->version)): ?>
							<a href="<?php echo $this->project->download; ?>" class="btn btn-primary"
							   target="_blank">
								<?php echo Text::_('COM_SWJPROJECTS_DOWNLOAD'); ?>
							</a>
						<?php endif; ?>
						<a href="<?php echo $this->project->versions; ?>" class="btn">
							<?php echo Text::_('COM_SWJPROJECTS_VERSIONS'); ?>
						</a>
						<?php if ($this->project->documentation): ?>
							<a href="<?php echo $this->project->documentation; ?>" class="btn">
								<?php echo Text::_('COM_SWJPROJECTS_DOCUMENTATION'); ?>
							</a>
						<?php endif; ?>
						<?php if ($urls = $this->project->urls->toArray()): ?>
							<?php foreach ($urls as $txt => $url):
								if (empty($url)) continue; ?>
								<a href="<?php echo $url; ?>" target="_blank" class="btn">
									<?php echo Text::_('COM_SWJPROJECTS_URLS_' . $txt); ?>
								</a>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php echo HTMLHelper::_('bootstrap.startTabSet', 'projectTab', array('active' => 'description', 'class')); ?>
	<?php echo HTMLHelper::_('bootstrap.addTab', 'projectTab', 'description', Text::_('JGLOBAL_DESCRIPTION')); ?>
	<?php if (!empty($this->project->fulltext)) echo $this->project->fulltext; ?>
	<?php echo HTMLHelper::_('bootstrap.endTab'); ?>

	<?php if ($this->project->joomla):
		$type = $this->project->joomla->get('type'); ?>
		<?php echo HTMLHelper::_('bootstrap.addTab', 'projectTab', 'joomla', Text::_('COM_SWJPROJECTS_JOOMLA')); ?>
		<ul class="unstyled">
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
		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
	<?php endif; ?>
	<?php if ($this->project->gallery): ?>
		<?php echo HTMLHelper::_('bootstrap.addTab', 'projectTab', 'gallery', Text::_('COM_SWJPROJECTS_IMAGES_GALLERY')); ?>
		<?php foreach (array_chunk($this->project->gallery, 2) as $r => $row):
			echo ($r > 0) ? '<hr>' : ''; ?>
			<div class="row-fluid">
				<?php foreach ($row as $image): ?>
					<div class="span6">
						<p>
							<a href="<?php echo $image->src; ?>" popup target="_blank">
								<?php echo HTMLHelper::image($image->src, htmlspecialchars($image->text)); ?>
								<?php if ($image->text): ?>
									<div class="lead"><?php echo $image->text; ?></div>
								<?php endif; ?>
							</a>
						</p>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endforeach; ?>
		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
	<?php endif; ?>

	<?php if (!empty($this->relations)): ?>
		<?php echo HTMLHelper::_('bootstrap.addTab', 'projectTab', 'relations', Text::_('COM_SWJPROJECTS_RELATIONS')); ?>
		<?php foreach (array_chunk($this->relations, 2) as $r => $row):
			echo ($r > 0) ? '<hr>' : ''; ?>
			<div class="row-fluid">
				<?php foreach ($row as $relation): ?>
					<div class="span6">
						<p>
							<a href="<?php echo $relation['link']; ?>" target="_blank">
								<div class="lead"><?php echo $relation['title']; ?></div>
								<?php echo HTMLHelper::image($relation['icon'], htmlspecialchars($relation['title'])); ?>
							</a>
						</p>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endforeach; ?>
		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
	<?php endif; ?>

	<?php if ($this->version && !empty($this->version->changelog)): ?>
		<?php echo HTMLHelper::_('bootstrap.addTab', 'projectTab', 'whats_new', Text::_('COM_SWJPROJECTS_WHATS_NEW')); ?>
		<?php foreach ($this->version->changelog as $item):
			if (empty($item['title']) && empty($item['description'])) continue;
			?>
			<div class="item">
				<div style="display: flex; justify-content: space-between; align-items: center;">
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

						$css_classes_array = [
							'security' => 'label label-important',
							'fix' => 'label label-inverse',
							'language' => 'label label-info',
							'addition' => 'label label-success',
							'change' => 'label label-warning',
							'remove' => 'label',
							'note' => 'label label-info',
						];
						echo LayoutHelper::render('components.swjprojects.changelog.badge', [
							'type' => $item['type'],
							'class' => 'your_custom_css_class_for_fix_or_addition_or_any_other',
							'css_classes_array' => $css_classes_array,
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
		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
	<?php endif; ?>
	<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
</div>

<?php
/**
 * @package    SW JProjects Component
 * @version    __DEPLOY_VERSION__
 * @author     Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2018 - 2019 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

HTMLHelper::stylesheet('media/com_swjprojects/css/site.min.css', array('version' => 'auto'));
?>
<div id="SWJProjects" class="project">
	<div class="project info well">
		<?php if ($cover = $this->project->images->get('cover')): ?>
			<p><?php echo HTMLHelper::image($cover, $this->project->title); ?></p>
		<?php endif; ?>
		<div class="clearfix">
			<?php if ($icon = $this->project->images->get('icon')): ?>
				<div class="pull-right"><?php echo HTMLHelper::image($icon, $this->project->title); ?></div>
			<?php endif; ?>
			<div>
				<h1><?php echo $this->project->title; ?></h1>
				<?php if (!empty($this->project->introtext)): ?>
					<p class="description">
						<?php echo $this->project->introtext; ?>
					</p>
				<?php endif; ?>
				<div class="meta">
					<ul class="inline">
						<li>
							<strong><?php echo Text::_('COM_SWJPROJECTS_CATEGORY'); ?>: </strong>
							<a href="<?php echo $this->category->link; ?>">
								<?php echo $this->category->title; ?>
							</a>
						</li>
						<?php if ($this->project->version): ?>
							<li>
								<strong><?php echo Text::_('COM_SWJPROJECTS_VERSION'); ?>: </strong>
								<a href="<?php echo $this->project->version->link; ?>">
									<?php echo $this->project->version->version; ?>
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
						<a href="<?php echo $this->project->download; ?>" class="btn btn-success" target="_blank">
							<?php echo Text::_('COM_SWJPROJECTS_DOWNLOAD'); ?>
						</a>
						<a href="<?php echo $this->project->versions; ?>" class="btn">
							<?php echo Text::_('COM_SWJPROJECTS_VERSIONS'); ?>
						</a>
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
	<?php if ($gallery = $this->project->images->get('gallery')): ?>
		<?php echo HTMLHelper::_('bootstrap.addTab', 'projectTab', 'gallery', Text::_('COM_SWJPROJECTS_IMAGES_GALLERY')); ?>
		<?php foreach (ArrayHelper::fromObject($gallery) as $value):
			if (empty($value['image'])) continue; ?>
			<p>
				<a href="<?php echo $value['image']; ?>" target="_blank">
					<?php echo HTMLHelper::image($value['image'], $value['text']); ?>
					<br>
					<?php echo (!empty($value['text'])) ? $value['text'] : ''; ?>
				</a>
			</p>
		<?php endforeach; ?>
		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
	<?php endif; ?>

	<?php if (!empty($this->relations)): ?>
		<?php echo HTMLHelper::_('bootstrap.addTab', 'projectTab', 'relations', Text::_('COM_SWJPROJECTS_RELATIONS')); ?>
		<?php foreach ($this->relations as $relation):
			if (empty($relation['link']) && empty($relation['title'])) continue; ?>
			<p>
				<a href="<?php echo $relation['link']; ?>" target="_blank">
					<?php if (!empty($relation['icon'])): ?>
						<?php echo HTMLHelper::image($relation['icon'], $relation['title']); ?>
						<br>
					<?php endif; ?>
					<?php echo $relation['title']; ?>
				</a>
			</p>
		<?php endforeach; ?>
		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
	<?php endif; ?>


	<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
</div>
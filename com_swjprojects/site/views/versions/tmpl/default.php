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

HTMLHelper::stylesheet('media/com_swjprojects/css/site.min.css', array('version' => 'auto'));
?>
<div id="SWJProjects" class="versions">
	<div class="project info well">
		<h1>
			<a href="<?php echo $this->project->link; ?>"><?php echo $this->project->title; ?></a>
		</h1>
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
	<?php if (empty($this->items)) : ?>
		<div class="alert alert-no-items">
			<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
		</div>
	<?php else : ?>
		<div class="versionsList">
			<div class="items">
				<?php foreach ($this->items as $item) : ?>
					<div class="item-<?php echo $item->id; ?>">
						<h2 class="title">
							<a href="<?php echo $item->link; ?>"><?php echo $item->title; ?></a>
							<a href="<?php echo $item->download; ?>" target="_blank"
							   class="btn btn-<?php echo ($item->tag->key == 'stable') ? 'success' : 'inverse'; ?> pull-right">
								<?php echo Text::_('COM_SWJPROJECTS_DOWNLOAD'); ?>
							</a>
						</h2>
						<ul class="unstyled">
							<li>
								<strong><?php echo Text::_('JDATE'); ?>: </strong>
								<?php echo HTMLHelper::_('date', $item->date, Text::_('DATE_FORMAT_LC3')); ?>
							</li>
							<li>
								<strong><?php echo Text::_('COM_SWJPROJECTS_VERSION_TAG'); ?>: </strong>
								<span class="text-<?php echo ($item->tag->key == 'stable') ? 'success' : 'error'; ?>">
									<?php echo $item->tag->title; ?>
								</span>
							</li>
							<?php if (!empty($item->joomla_version)): ?>
								<li>
									<strong><?php echo Text::_('COM_SWJPROJECTS_JOOMLA_VERSION'); ?>: </strong>
									<?php echo $item->joomla_version; ?>
								</li>
							<?php endif; ?>
							<?php if ($item->downloads): ?>
								<li>
									<strong><?php echo Text::_('COM_SWJPROJECTS_STATISTICS_DOWNLOADS'); ?>: </strong>
									<?php echo $item->downloads; ?>
								</li>
							<?php endif; ?>
						</ul>
					</div>
					<hr>
				<?php endforeach; ?>
			</div>
			<div class="pagination">
				<?php echo $this->pagination->getPagesLinks(); ?>
			</div>
		</div>
	<?php endif; ?>
</div>
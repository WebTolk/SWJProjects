<?php
/**
 * @package    SW JProjects Component
 * @version    1.5.5
 * @author     Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2018 - 2020 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::stylesheet('com_swjprojects/site.min.css', array('version' => 'auto', 'relative' => true));
?>
<div id="SWJProjects" class="version">
	<?php if ($cover = $this->project->images->get('cover')): ?>
		<p class="cover"><?php echo HTMLHelper::image($cover, $this->project->title); ?></p>
		<hr>
	<?php endif; ?>
	<div class="version info well">
		<div class="row-fluid">
			<?php if ($icon = $this->project->images->get('icon')): ?>
				<div class="span3"><?php echo HTMLHelper::image($icon, $this->project->title); ?></div>
			<?php endif; ?>
			<div class="<?php echo ($icon) ? 'span9' : ''; ?>">
				<h1>
					<?php echo $this->version->title; ?>
				</h1>
				<div class="meta">
					<ul class="inline">
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
						<li>
							<strong><?php echo Text::_('COM_SWJPROJECTS_PROJECT'); ?>: </strong>
							<a href="<?php echo $this->project->link; ?>">
								<?php echo $this->project->title; ?>
							</a>
						</li>
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
						<?php if ($this->version->downloads): ?>
							<li>
								<strong><?php echo Text::_('COM_SWJPROJECTS_STATISTICS_DOWNLOADS'); ?>: </strong>
								<?php echo $this->version->downloads; ?>
							</li>
						<?php endif; ?>
					</ul>
					<div class="buttons">
						<?php if (($this->version->download_type === 'paid' && $this->version->payment->get('link'))): ?>
							<a href="<?php echo $this->version->payment->get('link'); ?>" class="btn btn-success">
								<?php echo Text::_('COM_SWJPROJECTS_BUY'); ?>
							</a>
						<?php elseif ($this->version->download_type === 'free'): ?>
							<a href="<?php echo $this->version->download; ?>" class="btn btn-primary"
							   target="_blank">
								<?php echo Text::_('COM_SWJPROJECTS_DOWNLOAD'); ?>
							</a>
						<?php endif; ?>
						<a href="<?php echo $this->project->link; ?>" class="btn">
							<?php echo Text::_('COM_SWJPROJECTS_PROJECT'); ?>
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
	<div class="changelog">
		<?php foreach ($this->version->changelog as $item):
			if (empty($item['title']) && empty($item['description'])) continue;
			?>
			<div class="item">
				<?php if (!empty($item['title'])): ?>
					<h2><?php echo $item['title']; ?></h2>
				<?php endif; ?>
				<?php if (!empty($item['description'])): ?>
					<div class="description"><?php echo $item['description']; ?></div>
				<?php endif; ?>
			</div>
			<hr>
		<?php endforeach; ?>
	</div>
</div>
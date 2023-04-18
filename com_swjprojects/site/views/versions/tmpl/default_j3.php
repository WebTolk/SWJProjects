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

HTMLHelper::stylesheet('com_swjprojects/site.min.css', array('version' => 'auto', 'relative' => true));
?>
<div id="SWJProjects" class="versions">
	<div class="project info well">
		<div class="row-fluid">
			<?php if ($icon = $this->project->images->get('icon')): ?>
				<div class="span3"><?php echo HTMLHelper::image($icon, $this->project->title); ?></div>
			<?php endif; ?>
			<div class="<?php echo ($icon) ? 'span9' : ''; ?>">
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
						<a href="<?php echo $this->project->link; ?>" class="btn">
							<?php echo Text::_('COM_SWJPROJECTS_PROJECT'); ?>
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
							<?php if ($item->download_type === 'free'): ?>
								<a href="<?php echo $item->download; ?>" target="_blank"
								   class="btn btn-<?php echo ($item->tag->key == 'stable') ? 'success' : 'inverse'; ?> pull-right">
									<?php echo Text::_('COM_SWJPROJECTS_DOWNLOAD'); ?>
								</a>
							<?php endif; ?>
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

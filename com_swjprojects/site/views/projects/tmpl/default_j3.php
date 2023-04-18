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
<div id="SWJProjects" class="projects">
	<?php if ($this->category->id > 1): ?>
		<div class="category info well">
			<h1><?php echo $this->category->title; ?></h1>
			<?php if (!empty($this->category->description)): ?>
				<div class="description">
					<?php echo $this->category->description; ?>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>
	<?php if (empty($this->items)) : ?>
		<div class="alert alert-no-items">
			<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
		</div>
	<?php else : ?>
		<div class="projectsList">
			<div class="items">
				<?php foreach ($this->items as $i => $item) :
					echo ($i > 0) ? '<hr>' : ''; ?>
					<div class="row-fluid item-<?php echo $item->id; ?>">
						<?php if ($icon = $item->images->get('icon')): ?>
							<div class="span3"><?php echo HTMLHelper::image($icon, $item->title); ?></div>
						<?php endif; ?>
						<div class="<?php echo ($icon) ? 'span9' : ''; ?>">
							<h2 class="title">
								<a href="<?php echo $item->link; ?>"><?php echo $item->title; ?></a>
							</h2>
							<ul class="meta inline">
								<li>
									<strong><?php echo Text::_('COM_SWJPROJECTS_DOWNLOAD_TYPE'); ?>: </strong>
									<?php echo Text::_('COM_SWJPROJECTS_DOWNLOAD_TYPE_' . $item->download_type); ?>
								</li>
								<?php if ($item->download_type === 'paid' && $item->payment->get('price')): ?>
									<li>
										<strong><?php echo Text::_('COM_SWJPROJECTS_PRICE'); ?>: </strong>
										<span class="text-success"><?php echo $item->payment->get('price'); ?></span>
									</li>
								<?php endif; ?>
								<?php if (!empty($item->categories)): ?>
									<li>
										<strong><?php echo Text::_('COM_SWJPROJECTS_CATEGORIES'); ?>: </strong>
										<?php $i = 0;
										foreach ($item->categories as $category)
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
										<a href="<?php echo $item->category->link; ?>">
											<?php echo $item->category->title; ?>
										</a>
									</li>
								<?php endif; ?>
								<?php if ($item->version): ?>
									<li>
										<strong><?php echo Text::_('COM_SWJPROJECTS_VERSION'); ?>: </strong>
										<a href="<?php echo $item->version->link; ?>">
											<?php echo $item->version->version; ?>
										</a>
									</li>
								<?php endif; ?>
								<?php if ($item->downloads): ?>
									<li>
										<strong><?php echo Text::_('COM_SWJPROJECTS_STATISTICS_DOWNLOADS'); ?>
											: </strong>
										<?php echo $item->downloads; ?>
									</li>
								<?php endif; ?>
								<?php if ($item->hits): ?>
									<li>
										<strong><?php echo Text::_('COM_SWJPROJECTS_STATISTICS_HITS'); ?>: </strong>
										<?php echo $item->hits; ?>
									</li>
								<?php endif; ?>
							</ul>
							<?php if (!empty($item->introtext)): ?>
								<div class="intro">
									<?php echo $item->introtext; ?>
								</div>
							<?php endif; ?>
							<div class="clearfix">
								<div class="btn-group pull-right">
									<?php if ($item->download_type === 'paid' && $item->payment->get('link') && !empty($item->version)): ?>
										<a href="<?php echo $item->payment->get('link'); ?>" class="btn btn-success">
											<?php echo Text::_('COM_SWJPROJECTS_BUY'); ?>
										</a>
									<?php elseif ($item->download_type === 'free' && !empty($item->version)): ?>
										<a href="<?php echo $item->download; ?>" class="btn btn-primary"
										   target="_blank">
											<?php echo Text::_('COM_SWJPROJECTS_DOWNLOAD'); ?>
										</a>
									<?php endif; ?>
									<a href="<?php echo $item->link; ?>" class="btn">
										<?php echo Text::_('COM_SWJPROJECTS_MORE'); ?>
									</a>
								</div>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="pagination">
				<?php echo $this->pagination->getPagesLinks(); ?>
			</div>
		</div>
	<?php endif; ?>
</div>

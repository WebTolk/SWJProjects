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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::stylesheet('com_swjprojects/site.min.css', array('version' => 'auto', 'relative' => true));

$title = ($this->category->id > 1) ? $this->category->title
	: Factory::getApplication()->getMenu()->getActive()->title;
?>
<div id="SWJProjects" class="projects">
	<div class="category info mb-3">
		<h1><?php echo $title; ?></h1>
		<?php if (!empty($this->category->description)): ?>
			<div class="description">
				<?php echo $this->category->description; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php if (empty($this->items)) : ?>
		<div class="alert alert-info">
			<span class="icon-info-circle" aria-hidden="true"></span><span
					class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
			<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
		</div>
	<?php else : ?>
		<div class="projectsList">
			<div class="items">
				<?php foreach (array_chunk($this->items, 3) as $r => $row): ?>
					<div class="row mb-3">
						<?php foreach ($row as $i => $item): ?>
							<div class="col-md-4">
								<div class="card">
									<?php if ($icon = $item->images->get('icon')): ?>
										<a href="<?php echo $item->link; ?>">
											<?php echo HTMLHelper::image($icon, $item->title, array('class' => 'card-img-top')); ?>
										</a>
									<?php endif; ?>
									<div class="card-body">
										<h5 class="card-title">
											<a href="<?php echo $item->link; ?>"><?php echo $item->title; ?></a>
										</h5>
										<ul class="list-unstyled">
											<li>
												<strong><?php echo Text::_('COM_SWJPROJECTS_DOWNLOAD_TYPE'); ?>
													: </strong>
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
													<strong><?php echo Text::_('COM_SWJPROJECTS_CATEGORIES'); ?>
														: </strong>
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
													<strong><?php echo Text::_('COM_SWJPROJECTS_CATEGORY'); ?>
														: </strong>
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
													<strong><?php echo Text::_('COM_SWJPROJECTS_STATISTICS_HITS'); ?>
														: </strong>
													<?php echo $item->hits; ?>
												</li>
											<?php endif; ?>
										</ul>
										<?php if ($item->download_type === 'paid' && $item->payment->get('link') && !empty($item->version)): ?>
											<a href="<?php echo $item->payment->get('link'); ?>"
											   class="btn btn-success">
												<?php echo Text::_('COM_SWJPROJECTS_BUY'); ?>
											</a>
										<?php elseif ($item->download_type === 'free' && !empty($item->version)): ?>
											<a href="<?php echo $item->download; ?>" class="btn btn-primary"
											   target="_blank">
												<?php echo Text::_('COM_SWJPROJECTS_DOWNLOAD'); ?>
											</a>
										<?php endif; ?>
										<a href="<?php echo $item->link; ?>" class="btn btn-secondary">
											<?php echo Text::_('COM_SWJPROJECTS_MORE'); ?>
										</a>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="pagination">
				<?php echo $this->pagination->getPagesLinks(); ?>
			</div>
		</div>
	<?php endif; ?>
</div>

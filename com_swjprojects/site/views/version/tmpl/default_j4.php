<?php
/*
 * @package    SW JProjects Component
 * @version    1.6.2
 * @author     Septdir Workshop - www.septdir.com
 * @сopyright (c) 2018 - March 2023 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<div id="SWJProjects" class="version">
	<div class="version info mb-3">
		<h1><?php echo $this->version->title; ?></h1>
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
							<?php echo Text::_('COM_SWJPROJECTS_DOWNLOAD_TYPE_' . $this->project->download_type); ?>
						</li>
						<?php if ($this->project->download_type === 'paid' && $this->project->payment->get('price')): ?>
							<li>
								<strong><?php echo Text::_('COM_SWJPROJECTS_PRICE'); ?>: </strong>
								<span class="text-success"><?php echo $this->project->payment->get('price'); ?></span>
							</li>
						<?php endif; ?>
						<?php if ($this->version->version->version): ?>
							<li>
								<strong><?php echo Text::_('COM_SWJPROJECTS_VERSION'); ?>: </strong>
								<span><?php echo $this->version->version->version; ?></span>
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
						<?php if (($this->project->download_type === 'paid' && $this->project->payment->get('link'))): ?>
							<a href="<?php echo $this->project->payment->get('link'); ?>"
							   class="btn btn-success col-12">
								<?php echo Text::_('COM_SWJPROJECTS_BUY'); ?>
							</a>
						<?php elseif ($this->project->download_type === 'free'): ?>
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
			<div class="card">
				<div class="changelog card-body">
					<h2 class="h3"><?php echo Text::_('COM_SWJPROJECTS_VERSION_CHANGELOG') . ': '; ?></h2>
					<div class="items">
						<?php
						$i = 0;
						foreach ($this->version->changelog as $item):
							if (empty($item['title']) && empty($item['description'])) continue;
							if ($i > 0) echo '<hr>';
							$i++;
							?>
							<div class="item">
								<?php if (!empty($item['title'])): ?>
									<div class="h5"><?php echo $item['title']; ?></div>
								<?php endif; ?>
								<?php if (!empty($item['description'])): ?>
									<div class="description"><?php echo $item['description']; ?></div>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
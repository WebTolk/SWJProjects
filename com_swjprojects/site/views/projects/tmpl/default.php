<?php
/**
 * @package    SW JProjects Component
 * @version    1.0.2
 * @author     Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2018 - 2018 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::stylesheet('media/com_swjprojects/css/site.min.css', array('version' => 'auto'));
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
				<?php foreach ($this->items as $item) : ?>
					<div class="item-<?php echo $item->id; ?>">
						<h2 class="title">
							<a href="<?php echo $item->link; ?>"><?php echo $item->title; ?></a>
							<a href="<?php echo $item->download; ?>" class="btn btn-success pull-right" target="_blank">
								<?php echo Text::_('COM_SWJPROJECTS_DOWNLOAD'); ?>
							</a>
						</h2>
						<ul class="meta inline">
							<li>
								<strong><?php echo Text::_('COM_SWJPROJECTS_CATEGORY'); ?>: </strong>
								<a href="<?php echo $item->category->link; ?>">
									<?php echo $item->category->title; ?>
								</a>
							</li>
							<?php if ($item->version): ?>
								<li>
									<strong><?php echo Text::_('COM_SWJPROJECTS_VERSION'); ?>: </strong>
									<a href="<?php echo $item->version->link; ?>">
										<?php echo $item->version->version; ?>
									</a>
								</li>
							<?php endif; ?>
						</ul>
						<?php if (!empty($item->introtext)): ?>
							<div class="intro">
								<?php echo $item->introtext; ?>
							</div>
						<?php endif; ?>
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
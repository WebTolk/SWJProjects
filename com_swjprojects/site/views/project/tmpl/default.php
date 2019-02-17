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
<div id="SWJProjects" class="project">
	<div class="project info well">
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
	<?php echo HTMLHelper::_('bootstrap.startTabSet', 'projectTab', array('active' => 'description', 'class')); ?>
	<?php echo HTMLHelper::_('bootstrap.addTab', 'projectTab', 'description', Text::_('JGLOBAL_DESCRIPTION')); ?>
	<?php if (!empty($this->project->fulltext)) echo $this->project->fulltext; ?>
	<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
	<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
</div>
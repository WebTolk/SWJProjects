<?php
/**
 * @package    SW JProjects - Versions Module
 * @version    1.6.4
 * @author Septdir Workshop, <https://septdir.com>, Sergey Tolkachyov <https://web-tolk.ru>
 * @сopyright (c) 2018 - April 2023 Septdir Workshop, Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link https://septdir.com, https://web-tolk.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<div class="versionsList">
	<?php foreach ($items as $item) : ?>
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
<?php
/**
 * @package       SW JProjects
 * @version       2.4.0.1
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * You can use these variables here
 *
 * @var stdClass                               $module   The current module object
 * @var \Joomla\CMS\Application\CMSApplication $app      The application like instead Factory::getApplication()
 * @var \Joomla\Input\Input                    $input    The Joomla Input object
 * @var \Joomla\Registry\Registry              $params   The current module params
 * @var stdClass|string                        $template The current template params
 *
 * @var array                                  $items    The versions list
 */

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
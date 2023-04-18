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

use Joomla\CMS\Button\PublishedButton;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;

HTMLHelper::stylesheet('com_swjprojects/admin-j4.min.css', array('version' => 'auto', 'relative' => true));

$user      = Factory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$columns = 9;
?>
<form action="<?php echo Route::_('index.php?option=com_swjprojects&view=versions'); ?>" method="post"
	  name="adminForm" id="adminForm" class="clearfix">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
				<?php if (empty($this->items)) : ?>
					<div class="alert alert-info">
						<span class="icon-info-circle" aria-hidden="true"></span><span
								class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
						<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else : ?>
					<table id="versionsList" class="table itemList">
						<thead>
						<tr>
							<td class="w-1 text-center">
								<?php echo HTMLHelper::_('grid.checkall'); ?>
							</td>
							<th scope="col" class="w-1 text-center">
								<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS',
									'v.state', $listDirn, $listOrder); ?>
							</th>
							<th scope="col">
								<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE',
									'project_title', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" class="w-10 d-none d-md-table-cell">
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_SWJPROJECTS_PROJECT',
									'project_title', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" class="w-10 d-none d-md-table-cell">
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_SWJPROJECTS_VERSION',
									'version', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" class="w-10 d-none d-md-table-cell">
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_SWJPROJECTS_VERSION_TAG',
									'v.tag', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" class="w-10 d-none d-md-table-cell">
								<?php echo HTMLHelper::_('searchtools.sort', 'JDATE',
									'v.date', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" class="w-10 d-none d-md-table-cell text-center">
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_SWJPROJECTS_STATISTICS_DOWNLOADS',
									'v.downloads', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" class="w-5 d-none d-md-table-cell">
								<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID',
									'v.id', $listDirn, $listOrder); ?>
							</th>
						</tr>
						</thead>
						<tfoot>
						<tr>
							<td colspan="<?php echo $columns; ?>" class="text-end">
								<?php echo $this->pagination->getResultsCounter(); ?>
							</td>
						</tr>
						</tfoot>
						<tbody>
						<?php foreach ($this->items as $i => $item) :
							$canEdit = $user->authorise('core.edit', 'com_swjprojects.version.' . $item->id);
							$canChange = $user->authorise('core.edit.state', 'com_swjprojects.version.' . $item->id);
							$link = ($canEdit) ? Route::_('index.php?option=com_swjprojects&task=version.edit&id='
								. $item->id) : '';
							?>
							<tr class="row<?php echo $i % 2; ?>" data-draggable-group="1">
								<td class="text-center">
									<?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->title); ?>
								</td>
								<td class="text-center">
									<?php echo (new PublishedButton)->render((int) $item->state, $i, [
										'task_prefix' => 'versions.',
										'disabled'    => !$canChange,
										'id'          => 'state-' . $item->id
									]); ?>
								</td>
								<td>
									<a <?php echo ($link) ? 'href="' . $link . '"' : 'disable'; ?>>
										<strong><?php echo $item->title; ?></strong>
									</a>
								</td>
								<td class="d-none d-md-table-cell">
									<?php echo $this->escape($item->project_title); ?>
								</td>
								<td class="d-none d-md-table-cell">
									<?php echo $this->escape($item->version); ?>
								</td>
								<td class="d-none d-md-table-cell">
									<?php echo $this->escape(Text::_('COM_SWJPROJECTS_VERSION_TAG_' . $item->tag)); ?>
								</td>
								<td class="d-none d-md-table-cell">
									<?php echo HTMLHelper::_('date', $item->date, Text::_('DATE_FORMAT_LC5')); ?>
								</td>
								<td class="d-none d-md-table-cell text-center">
									<span class="badge bg-info">
										<?php echo (int) $item->downloads; ?>
									</span>
								</td>
								<td class="d-none d-md-table-cell"><?php echo $item->id; ?></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
					<?php echo $this->pagination->getListFooter(); ?>
				<?php endif; ?>
				<input type="hidden" name="task" value=""/>
				<input type="hidden" name="boxchecked" value="0"/>
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
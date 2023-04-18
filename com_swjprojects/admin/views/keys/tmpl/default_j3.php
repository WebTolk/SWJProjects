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
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Utilities\ArrayHelper;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('formbehavior.chosen', 'select');
HTMLHelper::stylesheet('com_swjprojects/admin-j3.min.css', array('version' => 'auto', 'relative' => true));

$user      = Factory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$columns = 9;
?>
<form action="<?php echo Route::_('index.php?option=com_swjprojects&view=keys'); ?>" method="post"
	  name="adminForm" id="adminForm">
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table id="keysList" class="table table-striped">
				<thead>
				<tr>
					<th width="1%" class="center">
						<?php echo HTMLHelper::_('grid.checkall'); ?>
					</th>
					<th width="2%" style="min-width:100px" class="center">
						<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'k.state', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap">
						<?php echo Text::_('COM_SWJPROJECTS_KEY'); ?>
					</th>
					<th class="nowrap">
						<?php echo HTMLHelper::_('searchtools.sort', 'COM_SWJPROJECTS_ORDER', 'k.order',
							$listDirn, $listOrder); ?>
					</th>
					<th class="nowrap">
						<?php echo Text::_('COM_SWJPROJECTS_USER'); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo Text::_('COM_SWJPROJECTS_PROJECTS'); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo HTMLHelper::_('searchtools.sort', 'COM_SWJPROJECTS_DATE_START', 'k.date_start',
							$listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo HTMLHelper::_('searchtools.sort', 'COM_SWJPROJECTS_DATE_END', 'k.date_end',
							$listDirn, $listOrder); ?>
					</th>
					<th width="1%" class="nowrap hidden-phone center">
						<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'k.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
				</thead>
				<tfoot>
				<tr>
					<td colspan="<?php echo $columns; ?>">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
				</tfoot>
				<tbody>
				<?php foreach ($this->items as $i => $item) :
					$canEdit = $user->authorise('core.edit', 'com_swjprojects.version.' . $item->id);
					$canChange = $user->authorise('core.edit.state', 'com_swjprojects.version.' . $item->id);
					?>
					<tr class="row<?php echo $i % 2; ?>" item-id="<?php echo $item->id ?>">
						<td class="center">
							<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
						</td>
						<td class="center nowrap">
							<div class="btn-group">
								<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'keys.', $canChange); ?>
							</div>
						</td>
						<td class="nowrap">
							<div>
								<?php if ($canEdit) : ?>
									<a class="hasTooltip" title="<?php echo Text::_('JACTION_EDIT'); ?>"
									   href="<?php echo Route::_('index.php?option=com_swjprojects&task=key.edit&id='
										   . $item->id); ?>">
										<?php echo $this->escape($item->key); ?>
									</a>
								<?php else : ?>
									<?php echo $this->escape($item->key); ?>
								<?php endif; ?>
							</div>
						</td>
						<td class="nowrap">
							<?php echo $item->order; ?>
						</td>
						<td class="nowrap">
							<?php if (!empty($item->user)) echo $item->user->name . ' (' . $item->user->email . ')'; ?>
						</td>
						<td class="hidden-phone nowrap">
							<?php echo implode(', ', ArrayHelper::getColumn($item->projects, 'title')); ?>
						</td>
						<td class="hidden-phone">
							<?php echo HTMLHelper::_('date', $item->date_start, Text::_('DATE_FORMAT_LC6')); ?>
						</td>
						<td class="hidden-phone">
							<?php echo ($item->date_end > 0) ?
								HTMLHelper::_('date', $item->date_end, Text::_('DATE_FORMAT_LC6'))
								: Text::_('JNEVER'); ?>
						</td>
						<td class="hidden-phone center">
							<?php echo $item->id; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="boxchecked" value="0"/>
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
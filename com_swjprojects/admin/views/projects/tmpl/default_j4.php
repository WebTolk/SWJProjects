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

HTMLHelper::stylesheet('com_swjprojects/admin_j4.min.css', array('version' => 'auto', 'relative' => true));

$user      = Factory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = ($listOrder == 'p.ordering' && strtolower($listDirn) == 'asc');

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_swjprojects&task=projects.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
	HTMLHelper::_('draggablelist.draggable');
}

$columns = 9;
?>

<form action="<?php echo Route::_('index.php?option=com_swjprojects&view=projects'); ?>" method="post"
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
					<table id="projectsList" class="table itemList">
						<thead>
						<tr>
							<td class="w-1 text-center">
								<?php echo HTMLHelper::_('grid.checkall'); ?>
							</td>
							<th scope="col" class="w-1 text-center d-none d-md-table-cell">
								<?php echo HTMLHelper::_('searchtools.sort', '', 'p.ordering',
									$listDirn, $listOrder, null, 'asc',
									'JGRID_HEADING_ORDERING', 'icon-sort'); ?>
							</th>
							<th scope="col" class="w-1 text-center">
								<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'p.state',
									$listDirn, $listOrder); ?>
							</th>
							<th scope="col">
								<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'p.title',
									$listDirn, $listOrder); ?>
							</th>
							<th scope="col">
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_SWJPROJECTS_CATEGORY',
									'category_title', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" class="w-10 d-none d-md-table-cell">
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_SWJPROJECTS_DOWNLOAD_TYPE',
									'p.download_type', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" class="w-10 d-none d-md-table-cell text-center">
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_SWJPROJECTS_STATISTICS_DOWNLOADS',
									'downloads', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" class="w-10 d-none d-md-table-cell text-center">
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_SWJPROJECTS_STATISTICS_HITS',
									'p.hits', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" class="w-5 d-none d-md-table-cell">
								<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'p.id',
									$listDirn, $listOrder); ?>
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
						<tbody <?php if ($saveOrder) : ?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>"
							data-direction="<?php echo strtolower($listDirn); ?>" data-nested="true"<?php endif; ?>>
						<?php foreach ($this->items as $i => $item) :
							$canEdit = $user->authorise('core.edit', 'com_swjprojects.project.' . $item->id);
							$canChange = $user->authorise('core.edit.state', 'com_swjprojects.project.' . $item->id);
							$link = ($canEdit) ? Route::_('index.php?option=com_swjprojects&task=project.edit&id='
								. $item->id) : '';
							?>
							<tr class="row<?php echo $i % 2; ?>" data-draggable-group="1">
								<td class="text-center">
									<?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->title); ?>
								</td>
								<td class="text-center d-none d-md-table-cell">
									<?php
									$iconClass = '';
									if (!$canChange) $iconClass = ' inactive';
									elseif (!$saveOrder) $iconClass = ' inactive" title="' . Text::_('JORDERINGDISABLED');
									?>
									<span class="sortable-handler<?php echo $iconClass ?>">
										<span class="icon-ellipsis-v"></span>
									</span>
									<?php if ($canChange && $saveOrder) : ?>
										<input type="text" name="order[]" size="5"
											   value="<?php echo $item->ordering; ?>"
											   class="width-20 text-area-order hidden">
									<?php endif; ?>
								</td>
								<td class="text-center">
									<?php echo (new PublishedButton)->render((int) $item->state, $i, [
										'task_prefix' => 'projects.',
										'disabled'    => !$canChange,
										'id'          => 'state-' . $item->id
									]); ?>
								</td>
								<td>
									<a <?php echo ($link) ? 'href="' . $link . '"' : 'disable'; ?>>
										<div class="text-nowrap">
											<strong><?php echo $item->title; ?></strong>
											<?php if (!empty($item->code)): ?>
												<span class="muted"> (<?php echo $item->code; ?>)</span>
											<?php endif; ?>
										</div>
										<?php if (!empty($item->categories) && $item->state != 2): ?>
											<div class="hidden-phone muted">
												<?php echo implode(', ', ArrayHelper::getColumn($item->categories, 'title')); ?>
											</div>
										<?php endif; ?>
									</a>
								</td>
								<td class="d-none d-md-table-cell">
									<?php echo Text::_($this->escape($item->category_title)); ?>
								</td>
								<td class="d-none d-md-table-cell">
									<?php echo Text::_('COM_SWJPROJECTS_DOWNLOAD_TYPE_' . $item->download_type); ?>
								</td>
								<td class="d-none d-md-table-cell text-center">
									<span class="badge bg-info">
										<?php echo (int) $item->downloads; ?>
									</span>
								</td>
								<td class="d-none d-md-table-cell text-center">
									<span class="badge bg-info">
										<?php echo (int) $item->hits; ?>
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
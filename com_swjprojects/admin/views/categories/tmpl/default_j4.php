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
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

HTMLHelper::stylesheet('com_swjprojects/admin-j4.min.css', array('version' => 'auto', 'relative' => true));

$user      = Factory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = ($listOrder == 'c.lft' && strtolower($listDirn) == 'asc');

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_swjprojects&task=categories.saveOrderAjax&tmpl=component';
	HTMLHelper::_('draggablelist.draggable');
}

$columns = 6;
?>
<form action="<?php echo Route::_('index.php?option=com_swjprojects&view=categories'); ?>" method="post"
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
					<table id="categoriesList" class="table itemList">
						<thead>
						<tr>
							<td class="w-1 text-center">
								<?php echo HTMLHelper::_('grid.checkall'); ?>
							</td>
							<th scope="col" class="w-1 text-center d-none d-md-table-cell">
								<?php echo HTMLHelper::_('searchtools.sort', '', 'c.lft', $listDirn, $listOrder, null, 'asc',
									'JGRID_HEADING_ORDERING', 'icon-sort'); ?>
							</th>
							<th scope="col" class="w-1 text-center">
								<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'c.state', $listDirn, $listOrder); ?>
							</th>
							<th scope="col">
								<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'title', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" class="w-5 d-none d-md-table-cell">
								<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'c.id', $listDirn, $listOrder); ?>
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
							data-direction="<?php echo strtolower($listDirn); ?>" data-nested="false"<?php endif; ?>>
						<?php foreach ($this->items as $i => $item) :
							$orderKey = array_search($item->id, $this->ordering[$item->parent_id]);
							$canEdit = $user->authorise('core.edit', 'com_swjprojects.category.' . $item->id);
							$canChange = $user->authorise('core.edit.state', 'com_swjprojects.category.' . $item->id);
							$link = ($canEdit) ? Route::_('index.php?option=com_swjprojects&task=category.edit&id='
								. $item->id) : '';
							// Get the parents of item for sorting
							if ($item->level > 0)
							{
								$parentsStr       = '';
								$_currentParentId = $item->parent_id;
								$parentsStr       = ' ' . $_currentParentId;
								for ($i2 = 0; $i2 < $item->level; $i2++)
								{
									foreach ($this->ordering as $k => $v)
									{
										$v = implode('-', $v);
										$v = '-' . $v . '-';
										if (strpos($v, '-' . $_currentParentId . '-') !== false)
										{
											$parentsStr       .= ' ' . $k;
											$_currentParentId = $k;
											break;
										}
									}
								}
							}
							else $parentsStr = '';
							?>
							<tr class="row<?php echo $i % 2; ?>" data-draggable-group="<?php echo $item->parent_id; ?>"
								data-item-id="<?php echo $item->id ?>" data-parents="<?php echo $parentsStr ?>"
								data-level="<?php echo $item->level ?>">
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
										<input type="text" class="hidden" name="order[]" size="5"
											   value="<?php echo $item->lft; ?>">
									<?php endif; ?>
								</td>
								<td class="text-center">
									<?php echo (new PublishedButton)->render((int) $item->state, $i, [
										'task_prefix' => 'categories.',
										'disabled'    => !$canChange,
										'id'          => 'state-' . $item->id
									]); ?>
								</td>
								<td class="text-nowrap">
									<a <?php echo ($link) ? 'href="' . $link . '"' : 'disable'; ?>>
										<?php echo LayoutHelper::render('joomla.html.treeprefix', array('level' => $item->level)); ?>
										<?php echo $item->title; ?>
									</a>
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
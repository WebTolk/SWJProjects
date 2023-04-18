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

$this->document->getWebAssetManager()
	->useScript('keepalive')
	->useScript('form.validate');
HTMLHelper::stylesheet('com_swjprojects/admin-j4.min.css', array('version' => 'auto', 'relative' => true));

?>
<form action="<?php echo Route::_('index.php?option=com_swjprojects&view=version&id=' . $this->item->id); ?>"
	  method="post" name="adminForm" id="item-form" class="form-validate translate-tabs" enctype="multipart/form-data">
	<fieldset class="row title-alias form-vertical mb-3">
		<div class="col-12 col-md-3">
			<?php echo $this->form->renderField('project_id'); ?>
		</div>
		<div class="col-12 col-md-9">
			<div class="control-group">
				<div class="control-label">
					<?php echo Text::_('COM_SWJPROJECTS_VERSION'); ?>
					<span class="star">&nbsp;*</span>
				</div>
				<div class="controls row g-0">
					<?php
					$this->form->setFieldAttribute('stage', 'hiddenLabel', 'true');
					?>
					<div class="col-1">
						<?php echo $this->form->getInput('major'); ?>
					</div>
					<div class="col-1">
						<?php echo $this->form->getInput('minor'); ?>
					</div>
					<div class="col-1">
						<?php echo $this->form->getInput('micro'); ?>
					</div>
					<div class="col-3">
						<?php echo $this->form->getInput('tag'); ?>
					</div>
					<div class="col-1">
						<?php echo $this->form->renderField('stage'); ?>
					</div>
				</div>
			</div>
		</div>
	</fieldset>
	<div class="main-card">
		<div class="row п-0">
			<div class="col-lg-8">
				<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general', 'class')); ?>
				<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('JGLOBAL_FIELDSET_CONTENT')); ?>
				<fieldset class="form-horizontal">
					<?php echo LayoutHelper::render('components.swjprojects.translate.field', array(
						'forms' => $this->translateForms, 'name' => 'changelog')); ?>
				</fieldset>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>
				<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'metadata', Text::_('JGLOBAL_FIELDSET_METADATA_OPTIONS')); ?>
				<fieldset>
					<?php echo LayoutHelper::render('components.swjprojects.translate.fieldset', array(
						'forms' => $this->translateForms, 'name' => 'metadata'), '', ['class' => 'asdasd']); ?>
				</fieldset>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>
				<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
			</div>
			<div class="col-lg-4">
				<div class="p-3">
					<div class="options-form">
						<div class="control-group">
							<div class="control-label">
								<label class="h5"><?php echo Text::_('COM_SWJPROJECTS_FILE_STATE'); ?></label>
							</div>
							<div class="controls">
								<?php if ($this->item->file): ?>
									<span class="badge bg-success">
										<?php echo Text::_('COM_SWJPROJECTS_FILE_EXIST'); ?>
									</span>
								<?php else: ?>
									<span class="badge bg-danger">
										<?php echo Text::_('COM_SWJPROJECTS_FILE_NOT_EXIST'); ?>
									</span>
								<?php endif; ?>
							</div>
						</div>
						<?php echo $this->form->renderFieldset('file'); ?>
					</div>
					<div class="options-form form-vertical">
						<?php echo $this->form->renderFieldset('global'); ?>
					</div>
					<div class="options-form form-vertical">
						<legend><?php echo Text::_('COM_SWJPROJECTS_STATISTICS'); ?></legend>
						<?php echo $this->form->renderFieldset('statistics'); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="return" value="<?php echo Factory::getApplication()->input->getCmd('return'); ?>"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
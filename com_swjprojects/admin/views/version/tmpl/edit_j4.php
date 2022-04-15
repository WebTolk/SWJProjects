<?php
/**
 * @package    SW JProjects Component
 * @version    __DEPLOY_VERSION__
 * @author     Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2018 - 2020 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

HTMLHelper::stylesheet('com_swjprojects/admin-j4.min.css', array('version' => 'auto', 'relative' => true));

Factory::getDocument()->getWebAssetManager()
	->usePreset('choicesjs')
	->useScript('webcomponent.field-fancy-select');

Factory::getDocument()->addScriptDeclaration('
	document.addEventListener("DOMContentLoaded", function () {
		document.querySelectorAll("select").forEach(function (element) {
			new Choices(element);
		});
	});
	Joomla.submitbutton = function(task)
	{
		if (task == "category.cancel" || document.formvalidator.isValid(document.getElementById("item-form")))
		{
			Joomla.submitform(task, document.getElementById("item-form"));
		}
	};
');
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
						<?php echo $this->form->getInput('stage'); ?>
					</div>
				</div>
			</div>
		</div>
	</fieldset>
	<div class="main-card">
		<div class="row">
			<div class="col-lg-9">
				<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general', 'class')); ?>
				<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('JGLOBAL_FIELDSET_CONTENT')); ?>
				<fieldset class="form-horizontal">
					<div class="control-group">
						<p class="lead me-2">
							<?php echo LayoutHelper::render('components.swjprojects.translate.text',
								''); ?>
						</p>
						<?php echo LayoutHelper::render('components.swjprojects.translate.input', array(
							'forms' => $this->translateForms, 'name' => 'changelog'),'',['class'=>'w-100']); ?>
					</div>
				</fieldset>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>
				<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'metadata', Text::_('JGLOBAL_FIELDSET_METADATA_OPTIONS')); ?>
				<fieldset>
					<?php echo LayoutHelper::render('components.swjprojects.translate.fieldset', array(
						'forms' => $this->translateForms, 'name' => 'metadata'),'',['class'=>'asdasd']); ?>
				</fieldset>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>
				<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
			</div>
			<div class="col-lg-3 bg-light border border-left">
				<fieldset>
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
				</fieldset>
				<fieldset>
					<?php echo $this->form->renderFieldset('global'); ?>
				</fieldset>
				<fieldset class="well form-horizontal form-horizontal-desktop">
					<p class="lead">
						<?php echo Text::_('COM_SWJPROJECTS_STATISTICS'); ?>
					</p>
					<?php echo $this->form->renderFieldset('statistics'); ?>
				</fieldset>
			</div>
		</div>
	</div>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="return" value="<?php echo Factory::getApplication()->input->getCmd('return'); ?>"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
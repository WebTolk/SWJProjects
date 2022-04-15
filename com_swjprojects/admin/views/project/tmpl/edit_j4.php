<?php
/**
 * @package    SW JProjects Component
 * @version    1.5.5
 * @author     Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2018 - 2020 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

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
<form action="<?php echo Route::_('index.php?option=com_swjprojects&view=project&id=' . $this->item->id); ?>"
	  method="post" name="adminForm" id="item-form" class="form-validate translate-tabs" enctype="multipart/form-data">
	<fieldset class="row title-alias form-vertical mb-3">
		<div class="col-12 col-md-6">
			<?php echo LayoutHelper::render('components.swjprojects.translate.field', array(
				'forms' => $this->translateForms, 'name' => 'title')); ?>
		</div>
		<div class="col-12 col-md-3">
			<?php echo $this->form->renderField('element'); ?>
		</div>
		<div class="col-12 col-md-3">
			<?php echo $this->form->renderField('alias'); ?>
		</div>
	</fieldset>
	<div class="main-card">
		<div class="row">
			<div class="col-lg-9">
				<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general', 'class')); ?>
				<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('JGLOBAL_FIELDSET_CONTENT')); ?>
				<fieldset>
					<div class="w-100">
						<h3 class="mb-3">
							<?php echo LayoutHelper::render('components.swjprojects.translate.text',
								'COM_SWJPROJECTS_PROJECT_INTROTEXT'); ?>
						</h3>
						<?php echo LayoutHelper::render('components.swjprojects.translate.input', array(
							'forms' => $this->translateForms, 'name' => 'introtext')); ?>
					</div>
					<hr>
					<div class="w-100">
						<h3 class="mb-3">
							<?php echo LayoutHelper::render('components.swjprojects.translate.text',
								'COM_SWJPROJECTS_PROJECT_FULLTEXT'); ?>
						</h3>
						<?php echo LayoutHelper::render('components.swjprojects.translate.input', array(
							'forms' => $this->translateForms, 'name' => 'fulltext')); ?>
					</div>
				</fieldset>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>
				<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'payment', Text::_('COM_SWJPROJECTS_PAYMENT')); ?>
				<fieldset>
					<?php echo $this->form->renderFieldset('payment'); ?>
					<?php echo LayoutHelper::render('components.swjprojects.translate.fieldset', array(
						'forms' => $this->translateForms, 'name' => 'payment')); ?>
				</fieldset>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>

				<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'joomla', Text::_('COM_SWJPROJECTS_JOOMLA')); ?>
				<fieldset>
					<?php echo $this->form->renderFieldset('joomla'); ?>
				</fieldset>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>

				<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'urls', Text::_('COM_SWJPROJECTS_URLS')); ?>
				<fieldset>
					<?php echo $this->form->renderFieldset('urls'); ?>
				</fieldset>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>

				<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'relations', Text::_('COM_SWJPROJECTS_RELATIONS')); ?>
				<fieldset>
					<?php echo $this->form->getInput('relations'); ?>
				</fieldset>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>

				<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'images', Text::_('COM_SWJPROJECTS_IMAGES')); ?>
				<fieldset>
					<?php if (empty($this->item->id)): ?>
						<div class="alert alert-warning">
							<?php echo Text::_('COM_SWJPROJECTS_IMAGES_AVAILABLE'); ?>
						</div>
					<?php else: ?>
						<?php echo LayoutHelper::render('components.swjprojects.translate.fieldset', array(
							'forms' => $this->translateForms, 'name' => 'images')); ?>
					<?php endif; ?>
				</fieldset>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>

				<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'metadata', Text::_('JGLOBAL_FIELDSET_METADATA_OPTIONS')); ?>
				<fieldset>
					<div class="row">
						<div class="col-6">
							<h4>
								<?php echo Text::_('COM_SWJPROJECTS_PROJECT'); ?>
							</h4>
							<?php echo LayoutHelper::render('components.swjprojects.translate.fieldset', array(
								'forms' => $this->translateForms, 'name' => 'metadata_project')); ?>
						</div>
						<div class="col-6">
							<h4>
								<?php echo Text::_('COM_SWJPROJECTS_VERSIONS'); ?>
							</h4>
							<?php echo LayoutHelper::render('components.swjprojects.translate.fieldset', array(
								'forms' => $this->translateForms, 'name' => 'metadata_versions')); ?>
						</div>
					</div>
					<div class="row">
						<div class="col-6">
							<h4>
								<?php echo Text::_('COM_SWJPROJECTS_DOCUMENTATION'); ?>
							</h4>
							<?php echo LayoutHelper::render('components.swjprojects.translate.fieldset', array(
								'forms' => $this->translateForms, 'name' => 'metadata_documentation')); ?>
						</div>
					</div>
				</fieldset>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>
				<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
			</div>
			<div class="col-lg-3 bg-light">
				<fieldset class="p-3">
					<?php echo $this->form->renderFieldset('global'); ?>
				</fieldset>
				<fieldset class="border-1 p-3">
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
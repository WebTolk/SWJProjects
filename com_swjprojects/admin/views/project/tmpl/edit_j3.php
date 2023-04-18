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
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

HTMLHelper::_('jquery.framework');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.tabstate');
HTMLHelper::_('formbehavior.chosen', 'select');
HTMLHelper::stylesheet('com_swjprojects/admin-j3.min.css', array('version' => 'auto', 'relative' => true));

Factory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function (task) {
		if (task == "project.cancel" || document.formvalidator.isValid(document.getElementById("item-form"))) {
		let form = document.querySelector("#item-form"),
				mSelects = form.querySelectorAll("select[multiple]");
			for (let i = 0; i < mSelects.length; i++) {
				let item = mSelects[i];
				if (item.value === "") {
					let newInput = document.createElement("input");
					newInput.setAttribute("name", item.getAttribute("name").replace("[]", ""));
					newInput.setAttribute("type", "hidden");
					form.append(newInput);
				}
			}
			Joomla.submitform(task, document.getElementById("item-form"));
		}
	};
');
?>
<form action="<?php echo Route::_('index.php?option=com_swjprojects&view=project&id=' . $this->item->id); ?>"
	  method="post" name="adminForm" id="item-form" class="form-validate translate-tabs" enctype="multipart/form-data">
	<fieldset class="form-inline form-inline-header">
		<?php echo LayoutHelper::render('components.swjprojects.translate.field', array(
			'forms' => $this->translateForms, 'name' => 'title')); ?>
		<?php echo $this->form->renderField('element'); ?>
		<?php echo $this->form->renderField('alias'); ?>
	</fieldset>
	<div class="row-fluid">
		<div class="span9">
			<?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'general', 'class')); ?>
			<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'general', Text::_('JGLOBAL_FIELDSET_CONTENT')); ?>
			<fieldset class="form-horizontal">
				<div class="control-group">
					<p class="lead">
						<?php echo LayoutHelper::render('components.swjprojects.translate.text',
							'COM_SWJPROJECTS_PROJECT_INTROTEXT'); ?>
					</p>
					<?php echo LayoutHelper::render('components.swjprojects.translate.input', array(
						'forms' => $this->translateForms, 'name' => 'introtext')); ?>
				</div>
				<hr>
				<div class="control-group">
					<p class="lead">
						<?php echo LayoutHelper::render('components.swjprojects.translate.text',
							'COM_SWJPROJECTS_PROJECT_FULLTEXT'); ?>
					</p>
					<?php echo LayoutHelper::render('components.swjprojects.translate.input', array(
						'forms' => $this->translateForms, 'name' => 'fulltext')); ?>
				</div>
			</fieldset>
			<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
			<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'payment', Text::_('COM_SWJPROJECTS_PAYMENT')); ?>
			<fieldset class="form-horizontal">
				<?php echo $this->form->renderFieldset('payment'); ?>
				<?php echo LayoutHelper::render('components.swjprojects.translate.fieldset', array(
					'forms' => $this->translateForms, 'name' => 'payment')); ?>
			</fieldset>
			<?php echo HTMLHelper::_('bootstrap.endTab'); ?>

			<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'joomla', Text::_('COM_SWJPROJECTS_JOOMLA')); ?>
			<fieldset class="form-horizontal">
				<?php echo $this->form->renderFieldset('joomla'); ?>
			</fieldset>
			<?php echo HTMLHelper::_('bootstrap.endTab'); ?>

			<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'urls', Text::_('COM_SWJPROJECTS_URLS')); ?>
			<fieldset class="form-horizontal">
				<?php echo $this->form->renderFieldset('urls'); ?>
			</fieldset>
			<?php echo HTMLHelper::_('bootstrap.endTab'); ?>

			<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'relations', Text::_('COM_SWJPROJECTS_RELATIONS')); ?>
			<fieldset class="form-horizontal">
				<?php echo $this->form->getInput('relations'); ?>
			</fieldset>
			<?php echo HTMLHelper::_('bootstrap.endTab'); ?>

			<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'images', Text::_('COM_SWJPROJECTS_IMAGES')); ?>
			<fieldset class="form-horizontal">
				<?php if (empty($this->item->id)): ?>
					<div class="alert alert-warning">
						<?php echo Text::_('COM_SWJPROJECTS_IMAGES_AVAILABLE'); ?>
					</div>
				<?php else: ?>
					<?php echo LayoutHelper::render('components.swjprojects.translate.fieldset', array(
						'forms' => $this->translateForms, 'name' => 'images')); ?>
				<?php endif; ?>
			</fieldset>
			<?php echo HTMLHelper::_('bootstrap.endTab'); ?>

			<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'metadata', Text::_('JGLOBAL_FIELDSET_METADATA_OPTIONS')); ?>
			<fieldset class="form-horizontal">
				<div class="row-fluid">
					<div class="span6">
						<p class="lead">
							<?php echo Text::_('COM_SWJPROJECTS_PROJECT'); ?>
						</p>
						<?php echo LayoutHelper::render('components.swjprojects.translate.fieldset', array(
							'forms' => $this->translateForms, 'name' => 'metadata_project')); ?>
					</div>
					<div class="span6">
						<p class="lead">
							<?php echo Text::_('COM_SWJPROJECTS_VERSIONS'); ?>
						</p>
						<?php echo LayoutHelper::render('components.swjprojects.translate.fieldset', array(
							'forms' => $this->translateForms, 'name' => 'metadata_versions')); ?>
					</div>
				</div>
				<div class="row-fluid">
					<div class="span6">
						<p class="lead">
							<?php echo Text::_('COM_SWJPROJECTS_DOCUMENTATION'); ?>
						</p>
						<?php echo LayoutHelper::render('components.swjprojects.translate.fieldset', array(
							'forms' => $this->translateForms, 'name' => 'metadata_documentation')); ?>
					</div>
				</div>
			</fieldset>
			<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
			<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
		</div>
		<div class="span3">
			<fieldset class="well form-horizontal form-horizontal-desktop">
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
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="return" value="<?php echo Factory::getApplication()->input->getCmd('return'); ?>"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
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
		<div class="row п-0">
			<div class="col-lg-8">
				<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general', 'class')); ?>
				<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('JGLOBAL_FIELDSET_CONTENT')); ?>
				<fieldset class="w-100">
					<h3 class="mb-3">
						<?php echo LayoutHelper::render('components.swjprojects.translate.text',
							'COM_SWJPROJECTS_PROJECT_INTROTEXT'); ?>
					</h3>
					<?php echo LayoutHelper::render('components.swjprojects.translate.input', array(
						'forms' => $this->translateForms, 'name' => 'introtext')); ?>
					<hr>
					<h3 class="mb-3">
						<?php echo LayoutHelper::render('components.swjprojects.translate.text',
							'COM_SWJPROJECTS_PROJECT_FULLTEXT'); ?>
					</h3>
					<?php echo LayoutHelper::render('components.swjprojects.translate.input', array(
						'forms' => $this->translateForms, 'name' => 'fulltext')); ?>
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
			<div class="col-lg-4">
				<div class="form-vertical p-3">
					<div class="options-form">
						<?php echo $this->form->renderFieldset('global'); ?>
					</div>
					<div class="options-form">
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
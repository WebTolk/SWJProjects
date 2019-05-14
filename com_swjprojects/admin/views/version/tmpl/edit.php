<?php
/**
 * @package    SW JProjects Component
 * @version    1.2.0
 * @author     Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2018 - 2019 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('jquery.framework');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.combobox');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.tabstate');
HTMLHelper::_('formbehavior.chosen', '#jform_joomla_version', null, array('disable_search_threshold' => 0));
HTMLHelper::_('formbehavior.chosen', 'select');
HTMLHelper::stylesheet('media/com_swjprojects/css/admin.min.css', array('version' => 'auto'));

Factory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function(task)
	{
		if (task == "version.cancel" || document.formvalidator.isValid(document.getElementById("item-form")))
		{
			Joomla.submitform(task, document.getElementById("item-form"));
		}
	};
');
?>
<form action="<?php echo Route::_('index.php?option=com_swjprojects&view=version&id=' . $this->item->id); ?>"
	  method="post" name="adminForm" id="item-form" class="form-validate translate-tabs" enctype="multipart/form-data">
	<div class="row-fluid">
		<div class="span9">
			<fieldset class="form-inline form-inline-header">
				<?php echo $this->form->renderField('project_id'); ?>
				<div class="control-group">
					<div class="control-label">
						<?php echo Text::_('COM_SWJPROJECTS_VERSION'); ?>
						<span class="star">&nbsp;*</span>
					</div>
					<div class="controls">
						<?php
						$this->form->setFieldAttribute('stage', 'hiddenLabel', 'true');
						echo $this->form->getInput('major')
							. $this->form->getInput('minor')
							. $this->form->getInput('micro')
							. $this->form->getInput('tag')
							. $this->form->renderField('stage');
						?>
					</div>
				</div>
			</fieldset>
			<hr>
			<fieldset class="form-horizontal">
				<div class="control-group">
					<p class="lead">
						<?php echo LayoutHelper::render('components.swjprojects.translate.text',
							'COM_SWJPROJECTS_VERSION_CHANGELOG'); ?>
					</p>
					<?php echo LayoutHelper::render('components.swjprojects.translate.input', array(
						'forms' => $this->translateForms, 'name' => 'changelog')); ?>
				</div>
			</fieldset>
		</div>
		<div class="span3">
			<fieldset class="well form-horizontal form-horizontal-desktop">
				<div class="control-group">
					<div class="control-label">
						<label><?php echo Text::_('COM_SWJPROJECTS_FILE_STATE'); ?></label>
					</div>
					<div class="controls">
						<?php if ($this->item->file): ?>
							<span class="text-success">
								<?php echo Text::_('COM_SWJPROJECTS_FILE_EXIST'); ?>
							</span>
						<?php else: ?>
							<span class="text-error">
								<?php echo Text::_('COM_SWJPROJECTS_FILE_NOT_EXIST'); ?>
							</span>
						<?php endif; ?>
					</div>
				</div>
				<?php echo $this->form->renderFieldset('file'); ?>
			</fieldset>
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
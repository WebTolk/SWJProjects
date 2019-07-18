<?php
/**
 * @package    SW JProjects Component
 * @version    __DEPLOY_VERSION__
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
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.tabstate');
HTMLHelper::_('formbehavior.chosen', 'select');
HTMLHelper::stylesheet('com_swjprojects/admin.min.css', array('version' => 'auto', 'relative' => true));

Factory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function(task)
	{
		if (task == "category.cancel" || document.formvalidator.isValid(document.getElementById("item-form")))
		{
			Joomla.submitform(task, document.getElementById("item-form"));
		}
	};
');
?>
<form action="<?php echo Route::_('index.php?option=com_swjprojects&view=category&id=' . $this->item->id); ?>"
	  method="post" name="adminForm" id="item-form" class="form-validate translate-tabs" enctype="multipart/form-data">
	<fieldset class="form-inline form-inline-header">
		<?php echo LayoutHelper::render('components.swjprojects.translate.field', array(
			'forms' => $this->translateForms, 'name' => 'title')); ?>
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
							'JGLOBAL_DESCRIPTION'); ?>
					</p>
					<?php echo LayoutHelper::render('components.swjprojects.translate.input', array(
						'forms' => $this->translateForms, 'name' => 'description')); ?>
				</div>
			</fieldset>
			<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
			<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
		</div>
		<div class="span3">
			<fieldset class="well form-horizontal form-horizontal-desktop">
				<?php echo $this->form->renderFieldset('global'); ?>
			</fieldset>
		</div>
	</div>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="return" value="<?php echo Factory::getApplication()->input->getCmd('return'); ?>"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
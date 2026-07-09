<?php
/**
 * @package       SW JProjects
 * @version       2.6.2
 * @Author        Sergey Tolkachyov
 * @copyright  Copyright (c) 2018 - 2026 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link          https://web-tolk.ru
 * @since         2.6.2
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

$this->document->getWebAssetManager()
	->useScript('keepalive')
	->useScript('form.validate')
	->registerAndUseStyle('com_swjprojects.admin.css', 'com_swjprojects/admin-j4.min.css', ['key' => 'auto', 'relative' => true]);
?>
<form action="<?php echo Route::_('index.php?option=com_swjprojects&view=maintainer&id=' . $this->item->id); ?>"
	  method="post" name="adminForm" id="item-form" class="form-validate translate-tabs" enctype="multipart/form-data">
	<fieldset class="row title-alias form-vertical mb-3">
		<div class="col-12 col-md-6">
			<?php echo LayoutHelper::render('components.swjprojects.translate.field', [
				'forms' => $this->translateForms,
				'name' => 'title',
			]); ?>
		</div>
		<div class="col-12 col-md-3">
			<?php echo $this->form->renderField('alias'); ?>
		</div>
		<div class="col-12 col-md-3">
			<?php echo $this->form->renderField('state'); ?>
		</div>
	</fieldset>
	<div class="main-card">
		<div class="row p-0">
			<div class="col-lg-8">
				<div class="row g-4 mt-1">
					<div class="col-lg-6 col-xxl-4">
						<div class="form-vertical p-3 h-100">
							<?php echo $this->form->renderField('image'); ?>
						</div>
					</div>
					<div class="col-lg-6 col-xxl-8">
						<div class="form-vertical p-3 h-100">
							<?php echo $this->form->renderField('website'); ?>
						</div>
					</div>
					<div class="col-12">
						<fieldset class="w-100">
							<?php echo LayoutHelper::render('components.swjprojects.translate.input', [
								'forms' => $this->translateForms,
								'name' => 'description',
							]); ?>
						</fieldset>
					</div>
				</div>
			</div>
			<div class="col-lg-4">
				<div class="form-vertical p-3">
					<div class="options-form">
						<?php echo $this->form->renderField('id'); ?>
						<?php echo $this->form->renderField('links'); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="return" value="<?php echo Factory::getApplication()->input->getCmd('return'); ?>"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>

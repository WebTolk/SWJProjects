<?php
/**
 * @package       SW JProjects
 * @version       2.6.1
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

$user      = Factory::getApplication()->getIdentity();
$this->document->getWebAssetManager()
	->useScript('keepalive')
	->useScript('form.validate')
    ->registerAndUseStyle('com_swjprojects.admin.css','com_swjprojects/admin-j4.min.css', ['key' => 'auto', 'relative' => true]);
?>
<form action="<?php echo Route::_('index.php?option=com_swjprojects&view=key&id=' . $this->item->id); ?>"
	  method="post" name="adminForm" id="item-form" class="form-validate translate-tabs" enctype="multipart/form-data">
	<div class="main-card">
		<div class="row п-0">
			<div class="col-lg-8">
				<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general', 'recall' => true, 'breakpoint' => 768]); ?>
				<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('JGLOBAL_FIELDSET_CONTENT')); ?>
				<fieldset class="form-horizontal p-3">
					<?php echo $this->form->renderFieldset('key'); ?>
					<?php echo $this->form->renderFieldset('plugins'); ?>
				</fieldset>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>
				<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
			</div>
			<div class="col-lg-4">
				<div class="form-vertical p-3">
					<div class="options-form">
						<?php echo $this->form->renderFieldset('global'); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="return" value="<?php echo Factory::getApplication()->input->getCmd('return'); ?>"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
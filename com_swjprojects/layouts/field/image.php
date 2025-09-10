<?php
/**
 * @package       SW JProjects
 * @version       2.5.0-alhpa1
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

extract($displayData);

/**
 * Layout variables
 * -----------------
 *
 * @var  string $class    Classes for the input.
 * @var  string $hint     Placeholder for the field.
 * @var  string $id       DOM id of the field.
 * @var  string $name     Name of the input field.
 * @var  string $value    Value attribute of the field.
 * @var  string $section  Component section selector (etc. projects).
 * @var  string $pk       The id field selector.
 * @var  string $filename The name of the image file.
 * @var  string $language The language of the image.
 *
 */

HTMLHelper::stylesheet('com_swjprojects/field-image.min.css', array('version' => 'auto', 'relative' => true));
HTMLHelper::script('com_swjprojects/popup.min.js', array('version' => 'auto', 'relative' => true));
HTMLHelper::script('com_swjprojects/field-image.min.js', array('version' => 'auto', 'relative' => true));

$loading = str_replace('//', '/', Uri::root(true) . '/media/com_swjprojects/images/ajax-loader.gif');
?>
<div id="<?php echo $id; ?>" class="<?php echo $class; ?>" input-image="container"
	 data-controller="<?php echo Route::_('index.php?option=com_swjprojects'); ?>"
	 data-section="<?php echo $section; ?>" data-pk="<?php echo $pk; ?>" data-filename="<?php echo $filename; ?>"
	 data-language="<?php echo $language; ?>">
	<div input-image="available" class="alert alert-warning" style="display: none">
		<?php echo Text::_('COM_SWJPROJECTS_IMAGES_AVAILABLE'); ?>
	</div>
	<div input-image="error" class="alert alert-error" style="display: none"></div>
	<div input-image="upload" style="display: none">
		<div class="preview-block">
			<?php echo HTMLHelper::image('com_swjprojects/no-image.svg', '',
				array('input-image' => 'preview', 'data-loading' => $loading), true); ?>
		</div>
		<div input-image="actions" class="btn-group">
			<a class="btn btn-sm btn-dark btn-dark hasTooltip" input-image="view"
			   title="<?php echo Text::_('COM_SWJPROJECTS_IMAGES_VIEW'); ?>"><i class="icon icon-eye"></i></a>
			<label class="btn btn-sm btn-success hasTooltip" for="<?php echo $id; ?>_field"
				   title="<?php echo Text::_('COM_SWJPROJECTS_IMAGES_UPLOAD'); ?>"><i class="icon icon-upload"></i></label>
			<a class="btn btn-sm btn-danger hasTooltip" input-image="delete"
			   title="<?php echo Text::_('COM_SWJPROJECTS_IMAGES_DELETE'); ?>"><i class="icon icon-remove"></i></a>
		</div>
		<input id="<?php echo $id; ?>_field" type="file" accept="image/*" input-image="field"/>
		<label for="<?php echo $id; ?>_field" input-image="drag"></label>
	</div>
</div>
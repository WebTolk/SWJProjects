<?php
/*
 * @package    SW JProjects
 * @version    2.0.0-alpha3
 * @author     Sergey Tolkachyov
 * @сopyright  Copyright (c) 2018 - 2024 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

extract($displayData);

/**
 * Layout variables
 * -----------------
 *
 * @var  string $name   Name of the input field.
 * @var  string $id     DOM id of the field.
 * @var  array  $images Field images array.
 *
 */

$loading = str_replace('//', '/', Uri::root(true) . '/media/com_swjprojects/images/ajax-loader.gif');
?>
<div class="images">
	<?php foreach ($images as $key => $image) : ?>
		<div input-images="image" data-filename="<?php echo $image->name; ?>">
			<div class="previewBlock">
				<img src="<?php echo $image->src; ?>" input-images="preview"
					 style="<?php echo (empty($image->src)) ? 'display:none' : ''; ?>"/>
				<?php echo HTMLHelper::image('media/jui/images/ajax-loader.gif', '',
					array('input-images' => 'image_loading', 'style' => 'display: none;')); ?>
				<?php echo HTMLHelper::image('com_swjprojects/no-image.svg', '',
					array('input-images' => 'noimage', 'style' => (empty($image->src)) ? '' : 'display: none;'), true); ?>
				<div input-images="actions" class="btn-group">
					<a class="btn btn-sm btn-inverse btn-dark  hasTooltip" input-images="view"
					   title="<?php echo Text::_('COM_SWJPROJECTS_IMAGES_VIEW'); ?>"><i class="icon icon-eye"></i></a>
					<label class="btn btn-sm btn-success  hasTooltip"
						   for="<?php echo $id . '_' . $image->name . '_field'; ?>"
						   title="<?php echo Text::_('COM_SWJPROJECTS_IMAGES_UPLOAD'); ?>"><i class="icon icon-upload"></i></label>
					<a class="btn btn-sm btn-primary  hasTooltip" input-images="move"
					   title="<?php echo Text::_('COM_SWJPROJECTS_IMAGES_MOVE'); ?>"><i class="icon icon-move"></i></a>
					<a class="btn btn-sm btn-danger  hasTooltip" input-images="delete"
					   data-key="<?php echo $image->name; ?>"
					   title="<?php echo Text::_('COM_SWJPROJECTS_IMAGES_DELETE'); ?>"><i class="icon icon-remove"></i></a>
				</div>
				<label for="<?php echo $id . '_' . $image->name . '_field'; ?>"></label>
			</div>
			<textarea name="<?php echo $name . '[' . $image->name . '][text]'; ?>" input-images="text" rows="3"
					  class="col-12 form-control" data-key="<?php echo $image->name; ?>"
					  data-type="text"><?php echo $image->text; ?></textarea>
			<input type="hidden" name="<?php echo $name . '[' . $image->name . '][ordering]'; ?>"
				   input-images="ordering" value="<?php echo $image->ordering; ?>"
				   data-key="<?php echo $image->name; ?>" data-type="ordering">
			<input id="<?php echo $id . '_' . $image->name . '_field'; ?>" type="file" accept="image/*"
				   input-images="image_field" data-key="<?php echo $image->name; ?>">
		</div>
	<?php endforeach; ?>
</div>


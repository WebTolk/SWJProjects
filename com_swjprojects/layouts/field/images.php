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
 * @var  array  $value    Filed value array.
 * @var  string $section  Component section selector (etc. projects).
 * @var  string $pk       The id field selector.
 * @var  string $folder   The name of the images folder.
 * @var  string $language The language of the image.
 *
 */

HTMLHelper::stylesheet('com_swjprojects/field-images.min.css', array('version' => 'auto', 'relative' => true));
HTMLHelper::stylesheet('com_swjprojects/dragula.min.css', array('version' => 'auto', 'relative' => true));
HTMLHelper::script('com_swjprojects/popup.min.js', array('version' => 'auto', 'relative' => true));
HTMLHelper::script('com_swjprojects/dragula.min.js', array('version' => 'auto', 'relative' => true));
HTMLHelper::script('com_swjprojects/field-images.min.js', array('version' => 'auto', 'relative' => true));

$loading = str_replace('//', '/', Uri::root(true) . '/media/com_swjprojects/images/ajax-loader.gif');
?>
<div id="<?php echo $id; ?>" class="<?php echo $class; ?>" input-images="container"
	 data-controller="<?php echo Route::_('index.php?option=com_swjprojects'); ?>"
	 data-section="<?php echo $section; ?>" data-pk="<?php echo $pk; ?>" data-folder="<?php echo $folder; ?>"
	 data-language="<?php echo $language; ?>" data-name="<?php echo $name; ?>">
	<div input-images="available" class="alert alert-warning" style="display: none">
		<?php echo Text::_('COM_SWJPROJECTS_IMAGES_AVAILABLE'); ?>
	</div>
	<div input-images="error" class="alert alert-error" style="display: none"></div>
	<div input-images="upload" style="display: none">
		<input id="<?php echo $id; ?>_field" type="file" accept="image/*" multiple input-images="field"/>
		<label for="<?php echo $id; ?>_field" input-images="drag"></label>
		<div input-images="loading" style="display: none">
			<?php echo HTMLHelper::image('media/jui/images/ajax-loader.gif', ''); ?>
		</div>
		<div class="text">
			<div><i class="icon-upload lead"></i></div>
			<div class="button">
				<span class="btn btn-success"><?php echo Text::_('COM_SWJPROJECTS_IMAGES_CHOOSE'); ?></span>
			</div>
		</div>
	</div>
	<div input-images="result" style="display: none">
		<?php if (!empty($value)): ?>
			<?php
			$order = count($value) + 1;
			foreach ($value as $key => $image)
			{
				$text     = (isset($image['text'])) ? $image['text'] : '';
				$ordering = (!empty($image['ordering'])) ? $image['ordering'] : 0;
				if (empty($ordering))
				{
					$ordering = $order;
					$order++;
				}
				echo '<input type"hidden" name="' . $name . '[' . $key . '][text]" value="' . $text . '"'
					. 'data-key="' . $key . '"  data-type="text">';
				echo '<input type"hidden" name="' . $name . '[' . $key . '][ordering]" value="' . $ordering . '"'
					. 'data-key="' . $key . '" data-type="ordering">';
			} ?>
		<?php endif; ?>
	</div>
</div>

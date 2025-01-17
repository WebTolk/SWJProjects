<?php
/*
 * @package    SW JProjects
 * @version    2.2.1
 * @author     Sergey Tolkachyov
 * @сopyright  Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
 */

use Joomla\Component\SWJProjects\Administrator\Helper\TranslationHelper;

defined('_JEXEC') or die;

extract($displayData);

/**
 * Layout variables
 * -----------------
 *
 * @var  array  $forms Translates forms array.
 * @var  string $name  Name of the field for which to get the value.
 * @var  string $group Optional dot-separated form group path on which to get the value.
 *
 */

$group        = (isset($group)) ? $group : '';
$languages    = TranslationHelper::getTranslations();
?>
<?php foreach ($forms as $code => $form):
	$field = (!empty($form->getField($name, $group))) ? $form->getField($name, $group) : false;
	$language = (!empty($languages[$code])) ? $languages[$code] : false;
	?>
	<?php if ($field && $language): ?>
	<div data-translate-input style="display: none"
		 data-translate="<?php echo $code; ?>"
		 data-id="<?php echo $field->id; ?>"
		 data-name="<?php echo $field->name; ?>">
		<?php echo $field->input; ?>
	</div>
<?php endif; ?>
<?php endforeach; ?>
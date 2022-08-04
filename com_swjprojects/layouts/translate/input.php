<?php
/*
 * @package    SW JProjects Component
 * @version    __DEPLOY_VERSION__
 * @author     Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2018 - 2022 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

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
$languages    = SWJProjectsHelperTranslation::getTranslations();
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
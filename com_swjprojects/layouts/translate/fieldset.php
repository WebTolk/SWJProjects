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

use Joomla\CMS\Layout\LayoutHelper;

extract($displayData);

/**
 * Layout variables
 * -----------------
 *
 * @var  array  $forms Translates forms array.
 * @var  string $name  Name of the fieldset for which to get the values.
 *
 */

$form = current($forms);
foreach ($form->getFieldSet($name) as $field)
{
	echo LayoutHelper::render('components.swjprojects.translate.field', array(
		'forms' => $forms,
		'name'  => $field->getAttribute('name'),
		'group' => $field->group));
}
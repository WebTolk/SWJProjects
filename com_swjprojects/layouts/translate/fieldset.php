<?php
/*
 * @package    SW JProjects
 * @version    2.2.0
 * @author     Sergey Tolkachyov
 * @сopyright  Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
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
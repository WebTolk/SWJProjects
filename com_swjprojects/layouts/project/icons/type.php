<?php
/*
 * @package    SW JProjects
 * @version    2.2.1
 * @author     Sergey Tolkachyov
 * @сopyright  Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

extract($displayData);

/**
 * Layout variables
 * -----------------
 *
 * @var  string $item SW JProject project
 *
 */

if (!empty($item->joomla->get('type'))): ?>
	<?php if ($item->joomla->get('type') == "component"): ?>
        <span class="badge bg-light text-dark"
              title="Component">Comp</span>
	<?php endif; ?>
	<?php if ($item->joomla->get('type') == "file"): ?>
        <span class="badge bg-light  text-dark " title="Joomla File">File</span>
	<?php endif; ?>
	<?php if ($item->joomla->get('type') == "language"): ?>
        <span class="badge bg-light  text-dark "
              title="Joomla language">Lang</span>
	<?php endif; ?>
	<?php if ($item->joomla->get('type') == "plugin"): ?>
        <span class="badge bg-light text-dark " title="Joomla Plugin">Plg</span>
	<?php endif; ?>

	<?php if ($item->joomla->get('type') == "module"): ?>
        <span class="badge bg-light text-dark " title="Joomla Module">Mod</span>
	<?php endif; ?>
	<?php if ($item->joomla->get('type') == "package"): ?>
        <span class="badge bg-light text-dark "
              title="Joomla Package">Pack</span>
	<?php endif; ?>
	<?php if ($item->joomla->get('type') == "template"): ?>
        <span class="badge bg-light text-dark "
              title="Joomla Template">Tpl</span>
	<?php endif; ?>
	<?php if ($item->joomla->get('type') == "library"): ?>
        <span class="badge bg-light text-dark "
              title="Joomla Library">Lib</span>
	<?php endif; ?>
<?php endif; ?>
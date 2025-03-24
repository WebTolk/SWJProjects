<?php
/**
 * @package       SW JProjects
 * @version       2.4.0
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
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

if ($item->joomla && !empty($item->joomla->get('type'))): ?>
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
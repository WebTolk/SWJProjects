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
 * @var  string $type Changelog item type
 * @var  string $class CSS class for badge
 * @var  array $css_classes_array CSS class for badge
 *
 */

$css_classes = [
    'security' => 'badge bg-danger',
    'fix' => 'badge bg-dark',
    'language' => 'badge bg-primary',
    'addition' => 'badge bg-success',
    'change' => 'badge bg-warning',
    'remove' => 'badge bg-secondary',
    'note' => 'badge bg-info',
];

if (isset($css_classes_array) && !empty($css_classes_array)) {

    $css_classes = array_merge($css_classes, $css_classes_array);

}


/**
 * $class string has a higher priority
 */
if (isset($class) && !empty($class)) {

    $css_class = $class;

} else {
    $css_class = $css_classes[$type];
}
?>
<span class="<?php echo $css_class; ?>" title="<?php echo Text::_('COM_SWJPROJECTS_VERSION_CHANGELOG_ITEM_TYPE'); ?>"><?php echo Text::_('COM_SWJPROJECTS_VERSION_CHANGELOG_ITEM_TYPE_' . strtoupper($type)); ?></span>
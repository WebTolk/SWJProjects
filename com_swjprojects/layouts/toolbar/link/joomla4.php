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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

extract($displayData);

/**
 * Layout variables
 * -----------------
 *
 * @var  string  $link  Button link.
 * @var  string  $text  Button text.
 * @var  string  $icon  Button icon.
 * @var  boolean $new   Button target.
 * @var  string  $id    Button id.
 * @var  int     $order Button order.
 *
 */

$new = (isset($new)) ? $new : true;
$id  = (isset($id)) ? ' id="' . $id . '"' : '';
if (!empty($order))
{
	$class = (isset($class)) ? $class . ' ' . 'swjprojects_toolbar_order' : 'swjprojects_toolbar_order';
}
$class = (!empty($class)) ? ' class="' . $class . '"' : '';
$text  = Text::_($text);
$link  = Route::_($link, false);
$order = (!empty($order)) ? 'style="order: ' . $order . ';   margin-inline-start: 0.75rem;"' : '';

Factory::getDocument()->addStyleDeclaration('
	joomla-toolbar-button > a[href="' . $link . '"]:before{display:none;};
');
?>
<joomla-toolbar-button <?php echo $id . $class . $order; ?>>
	<a href="<?php echo $link; ?>" class="btn btn-small"<?php echo ($new) ? ' target="_blank"' : ''; ?>
	   title="<?php echo htmlspecialchars($text); ?>">
		<span aria-hidden="true" class="icon-<?php echo $icon; ?>"></span>
		<?php echo Text::_($text); ?>
	</a>
	<?php if ($order): ?>
		<script>
			document.addEventListener('DOMContentLoaded', function () {
				let button = document.querySelector('#toolbar a[href="<?php echo $link;?>"]');
				if (button) {
					let toolbar = button.closest("#toolbar"),
						first = toolbar.querySelector('joomla-toolbar-button:not(.swjprojects_toolbar_order');
					first.style.marginInlineStart = '0';
				}
			});
		</script>
	<?php endif; ?>
</joomla-toolbar-button>
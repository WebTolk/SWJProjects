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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

extract($displayData);

/**
 * Layout variables
 * -----------------
 *
 * @var  string  $link Button link.
 * @var  string  $text Button text.
 * @var  string  $icon Button icon.
 * @var  boolean $new  Button target.
 * @var  string  $id   Button id.
 *
 */

$new = (isset($new)) ? $new : true;
?>
<a href="<?php echo Route::_($link); ?>" class="btn btn-small"<?php echo ($new) ? ' target="_blank"' : ''; ?>>
	<span aria-hidden="true" class="icon-<?php echo $icon; ?>"></span>
	<?php echo Text::_($text); ?>
</a>
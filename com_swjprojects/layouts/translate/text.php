<?php
/*
 * @package    SW JProjects
 * @version    2.1.0.1
 * @author     Sergey Tolkachyov
 * @сopyright  Copyright (c) 2018 - 2024 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\SWJProjects\Administrator\Helper\TranslationHelper;

?>
<?php foreach (TranslationHelper::getTranslations() as $code => $language): ?>
	<span data-translate-text data-translate="<?php echo $code; ?>" style="display: none">
		<?php echo Text::_($displayData); ?>
		<sup>
			<?php echo HTMLHelper::_('image', 'mod_languages/' . $language->image . '.gif', '', null, true); ?>
		</sup>
	</span>
<?php endforeach; ?>
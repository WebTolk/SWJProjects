<?php
/**
 * @package       SW JProjects
 * @version       2.4.0.1
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Component\SWJProjects\Administrator\Helper\TranslationHelper;

HTMLHelper::stylesheet('com_swjprojects/translate-switcher.min.css', array('version' => 'auto', 'relative' => true));
HTMLHelper::script('com_swjprojects/translate-switcher.min.js', array('version' => 'auto', 'relative' => true));
?>
<div data-translate-switcher class="btn-group ms-auto" data-default="<?php echo TranslationHelper::getDefault(); ?>">
	<?php foreach (TranslationHelper::getTranslations() as $translation): ?>
		<a href="javascript:void(0);" title="<?php echo $translation->name; ?>"
		   data-translate="<?php echo $translation->code; ?>"
		   class="btn hasTooltip">
			<?php echo HTMLHelper::_('image', 'mod_languages/' . $translation->image . '.gif', '', null, true); ?>
		</a>
	<?php endforeach; ?>
</div>
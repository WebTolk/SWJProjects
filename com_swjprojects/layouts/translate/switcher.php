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

HTMLHelper::stylesheet('com_swjprojects/translate-switcher.min.css', array('version' => 'auto', 'relative' => true));
HTMLHelper::script('com_swjprojects/translate-switcher.min.js', array('version' => 'auto', 'relative' => true));
?>
<div data-translate-switcher class="btn-group ms-auto" data-default="<?php echo SWJProjectsHelperTranslation::getDefault(); ?>">
	<?php foreach (SWJProjectsHelperTranslation::getTranslations() as $translation): ?>
		<a href="javascript:void(0);" title="<?php echo $translation->name; ?>"
		   data-translate="<?php echo $translation->code; ?>"
		   class="btn hasTooltip">
			<?php echo HTMLHelper::_('image', 'mod_languages/' . $translation->image . '.gif', '', null, true); ?>
		</a>
	<?php endforeach; ?>
</div>
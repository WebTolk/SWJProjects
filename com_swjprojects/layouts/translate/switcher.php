<?php
/**
 * @package    SW JProjects Component
 * @version    __DEPLOY_VERSION__
 * @author     Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2018 - 2020 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::stylesheet('com_swjprojects/translate-switcher.min.css', array('version' => 'auto', 'relative' => true));
HTMLHelper::script('com_swjprojects/translate-switcher.min.js', array('version' => 'auto', 'relative' => true));
?>
<div data-translate-switcher class="btn-group" data-default="<?php echo SWJProjectsHelperTranslation::getDefault(); ?>">
	<?php foreach (SWJProjectsHelperTranslation::getTranslations() as $translation): ?>
		<a href="javascript:void(0);" title="<?php echo $translation->name; ?>"
		   data-translate="<?php echo $translation->code; ?>"
		   class="btn hasTooltip">
			<?php echo HTMLHelper::_('image', 'mod_languages/' . $translation->image . '.gif', '', null, true); ?>
		</a>
	<?php endforeach; ?>
</div>
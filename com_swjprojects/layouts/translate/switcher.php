<?php
/**
 * @package    SW JProjects Component
 * @version    1.0.1
 * @author     Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2018 - 2018 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\LanguageHelper;

HTMLHelper::stylesheet('media/com_swjprojects/css/translate-switcher.min.css', array('version' => 'auto'));
HTMLHelper::script('media/com_swjprojects/js/translate-switcher.min.js', array('version' => 'auto'));

$default = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
?>
<div data-translate-switcher class="btn-group" data-default="<?php echo $default; ?>">
	<?php foreach (LanguageHelper::getLanguages('lang_code') as $code => $language) : ?>
		<a href="javascript:void(0);" title="<?php echo $language->title; ?>" data-translate="<?php echo $code; ?>"
		   class="btn hasTooltip">
			<?php echo HTMLHelper::_('image', 'mod_languages/' . $language->image . '.gif', '', null, true); ?>
		</a>
	<?php endforeach; ?>
</div>
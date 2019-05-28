<?php
/**
 * @package    SW JProjects Component
 * @version    1.2.1
 * @author     Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2018 - 2019 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;

?>
<?php foreach (LanguageHelper::getLanguages('lang_code') as $code => $language): ?>
	<span data-translate-text data-translate="<?php echo $code; ?>" style="display: none">
		<?php echo Text::_($displayData); ?>
		<sup>
			<?php echo HTMLHelper::_('image', 'mod_languages/' . $language->image . '.gif', '', null, true); ?>
		</sup>
	</span>
<?php endforeach; ?>
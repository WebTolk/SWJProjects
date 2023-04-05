<?php
/*
 * @package    SW JProjects Component
 * @version    1.6.3
 * @author     Septdir Workshop - www.septdir.com
 * @сopyright (c) 2018 - April 2023 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<?php foreach (SWJProjectsHelperTranslation::getTranslations() as $code => $language): ?>
	<span data-translate-text data-translate="<?php echo $code; ?>" style="display: none">
		<?php echo Text::_($displayData); ?>
		<sup>
			<?php echo HTMLHelper::_('image', 'mod_languages/' . $language->image . '.gif', '', null, true); ?>
		</sup>
	</span>
<?php endforeach; ?>
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

HTMLHelper::stylesheet('com_swjprojects/site.min.css', array('version' => 'auto', 'relative' => true));
?>
<div id="SWJProjects" class="document">
	<?php if ($cover = $this->project->images->get('cover')): ?>
		<p class="cover"><?php echo HTMLHelper::image($cover, $this->project->title); ?></p>
		<hr>
	<?php endif; ?>
	<h1><?php echo $this->item->title; ?></h1>
	<div>
		<?php if (!empty($this->item->fulltext)): ?>
			<?php echo $this->item->fulltext; ?>
		<?php elseif (!empty($this->item->introtext)): ?>
			<p><?php echo nl2br($this->item->introtext); ?></p>
		<?php endif; ?>
	</div>
</div>
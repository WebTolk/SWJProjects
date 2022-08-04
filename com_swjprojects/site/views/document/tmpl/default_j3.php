<?php
/*
 * @package    SW JProjects Component
 * @version    1.6.0
 * @author     Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2018 - 2022 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
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
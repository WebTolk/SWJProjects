<?php
/**
 * @package       SW JProjects
 * @version       2.6.1-dev
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

/**
 * @var object $insert_project Project from short code
 * @var object $project Original project object
 */

// For full project object info uncomment this echos
// dump($insert_project);

?>
<h4><?php echo $insert_project->title; ?></h4>
<br/>
<?php echo $insert_project->introtext; ?>
<br/>
<a href="<?php echo $insert_project->link;?>"><?php echo Text::_('JDETAILS'); ?></a>

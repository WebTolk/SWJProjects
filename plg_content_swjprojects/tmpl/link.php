<?php
/**
 * @package    SW JProjects
 * @version       2.2.0
 * @Author        Sergey Tolkachyov, https://web-tolk.ru
 * @copyright     Copyright (C) 2024 Sergey Tolkachyov
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @since         1.0.0
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

/**
 * @var object $insert_project Project from short code
 * @var object $project Original project object
 */
// For full project object info uncomment this echos
//  echo "<pre>";
//  print_r($insert_project);
//  echo "</pre>";

?>
<a href="<?php echo $insert_project->link;?>"><?php echo $insert_project->title; ?></a>

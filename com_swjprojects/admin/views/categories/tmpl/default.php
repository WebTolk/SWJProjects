<?php
/*
 * @package    SW JProjects Component
 * @version    1.6.2
 * @author     Septdir Workshop - www.septdir.com
 * @сopyright (c) 2018 - March 2023 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Version;

echo $this->loadTemplate(((new Version())->isCompatible('4.0')) ? 'j4' : 'j3');
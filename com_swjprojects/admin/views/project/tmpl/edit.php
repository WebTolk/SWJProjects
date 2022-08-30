<?php
/*
 * @package    SW JProjects Component
 * @version    1.6.1
 * @author     Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2018 - 2022 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Version;

echo $this->loadTemplate(((new Version())->isCompatible('4.0')) ? 'j4' : 'j3');
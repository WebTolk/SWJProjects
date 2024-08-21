<?php
/*
 * @package    SW JProjects
 * @version    2.0.1
 * @author     Sergey Tolkachyov
 * @сopyright  Copyright (c) 2018 - 2024 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

extract($displayData);

/**
 * Layout variables
 * -----------------
 *
 * @var  string $item SW JProject project
 *
 */

echo $this->sublayout('downloads', $displayData);
echo $this->sublayout('hits', $displayData);
echo $this->sublayout('ctr', $displayData);
echo $this->sublayout('type', $displayData);
echo $this->sublayout('downloadtype', $displayData);

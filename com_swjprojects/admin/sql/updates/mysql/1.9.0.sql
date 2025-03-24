/*
 * @package       SW JProjects
 * @version    2.4.0
 * @author     Sergey Tolkachyov
 * @copyright  Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
 */

ALTER TABLE `#__swjprojects_versions` CHANGE `micro` `patch` int(10) NOT NULL DEFAULT '0' AFTER `minor`;
ALTER TABLE `#__swjprojects_versions` ADD `hotfix` int(10) NOT NULL DEFAULT '0' AFTER `patch`;
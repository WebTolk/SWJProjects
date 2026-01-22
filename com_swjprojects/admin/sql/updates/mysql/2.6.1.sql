/*
 * @package       SW JProjects
 * @version    2.6.1-dev
 * @author     Sergey Tolkachyov
 * @copyright  Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
 */

ALTER TABLE `#__swjprojects_projects` ADD `update_server` INT NULL DEFAULT NULL COMMENT 'Enable update server flag' AFTER `visible`;
UPDATE `#__swjprojects_projects` SET `update_server` = '1' WHERE `joomla` LIKE '%"update_server":"1"%';
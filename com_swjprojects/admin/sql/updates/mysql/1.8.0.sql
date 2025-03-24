/*
 * @package       SW JProjects
 * @version    2.4.0
 * @author     Sergey Tolkachyov
 * @copyright  Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
 */

ALTER TABLE `#__swjprojects_projects` ADD `visible` INT(1) NOT NULL DEFAULT 1 COMMENT 'Is project visible or not in frontend' AFTER `hits`, ADD INDEX `idx_visible` (`visible`);
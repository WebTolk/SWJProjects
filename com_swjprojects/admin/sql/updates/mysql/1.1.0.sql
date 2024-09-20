/*
 * @package    SW JProjects
 * @version    2.1.0.1
 * @author     Sergey Tolkachyov
 * @сopyright  Copyright (c) 2018 - 2024 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
 */

ALTER TABLE `#__swjprojects_versions` DROP INDEX `date`;
ALTER TABLE `#__swjprojects_versions` ADD INDEX `idx_date`(`date`);
ALTER TABLE `#__swjprojects_projects` ADD `hits` INT(10) NOT NULL DEFAULT 0 AFTER `ordering`;
ALTER TABLE `#__swjprojects_projects` ADD `relations` TEXT NOT NULL AFTER `urls`;
ALTER TABLE `#__swjprojects_projects` ADD INDEX `idx_hits`(`hits`);
ALTER TABLE `#__swjprojects_translate_projects` ADD `images` MEDIUMTEXT NOT NULL AFTER `fulltext`;
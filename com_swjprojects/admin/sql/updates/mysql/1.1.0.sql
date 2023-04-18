/*
 * @package    SW JProjects Component
 * @version    1.6.4
 * @author Septdir Workshop, <https://septdir.com>, Sergey Tolkachyov <https://web-tolk.ru>
 * @сopyright (c) 2018 - April 2023 Septdir Workshop, Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link https://septdir.com, https://web-tolk.ru
 */

ALTER TABLE `#__swjprojects_versions` DROP INDEX `date`;
ALTER TABLE `#__swjprojects_versions` ADD INDEX `idx_date`(`date`);
ALTER TABLE `#__swjprojects_projects` ADD `hits` INT(10) NOT NULL DEFAULT 0 AFTER `ordering`;
ALTER TABLE `#__swjprojects_projects` ADD `relations` TEXT NOT NULL AFTER `urls`;
ALTER TABLE `#__swjprojects_projects` ADD INDEX `idx_hits`(`hits`);
ALTER TABLE `#__swjprojects_translate_projects` ADD `images` MEDIUMTEXT NOT NULL AFTER `fulltext`;
/*
 * @package    SW JProjects Component
 * @version    1.6.4
 * @author Septdir Workshop, <https://septdir.com>, Sergey Tolkachyov <https://web-tolk.ru>
 * @сopyright (c) 2018 - April 2023 Septdir Workshop, Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link https://septdir.com, https://web-tolk.ru
 */

ALTER TABLE `#__swjprojects_projects` ADD `download_type` VARCHAR(100) NOT NULL DEFAULT 'free' AFTER `catid`;
ALTER TABLE `#__swjprojects_projects` ADD INDEX `idx_download`(`download_type`(100));
ALTER TABLE `#__swjprojects_translate_projects` ADD `payment` MEDIUMTEXT NOT NULL AFTER `fulltext`;
ALTER TABLE `#__swjprojects_translate_versions` ADD `metadata` TEXT NOT NULL AFTER `changelog`;
ALTER TABLE `#__swjprojects_translate_projects` ADD `metadata` TEXT NOT NULL AFTER `payment`;
ALTER TABLE `#__swjprojects_translate_categories` ADD `metadata` TEXT NOT NULL AFTER `description`;
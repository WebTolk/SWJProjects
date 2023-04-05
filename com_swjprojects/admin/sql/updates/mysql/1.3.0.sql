/*
 * @package    SW JProjects Component
 * @version    1.6.3
 * @author     Septdir Workshop - www.septdir.com
 * @сopyright (c) 2018 - April 2023 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

ALTER TABLE `#__swjprojects_projects` ADD `download_type` VARCHAR(100) NOT NULL DEFAULT 'free' AFTER `catid`;
ALTER TABLE `#__swjprojects_projects` ADD INDEX `idx_download`(`download_type`(100));
ALTER TABLE `#__swjprojects_translate_projects` ADD `payment` MEDIUMTEXT NOT NULL AFTER `fulltext`;
ALTER TABLE `#__swjprojects_translate_versions` ADD `metadata` TEXT NOT NULL AFTER `changelog`;
ALTER TABLE `#__swjprojects_translate_projects` ADD `metadata` TEXT NOT NULL AFTER `payment`;
ALTER TABLE `#__swjprojects_translate_categories` ADD `metadata` TEXT NOT NULL AFTER `description`;
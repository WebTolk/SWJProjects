ALTER TABLE `#__swjprojects_projects` ADD `download_type` VARCHAR(100) NOT NULL DEFAULT 'free' AFTER `catid`;
ALTER TABLE `#__swjprojects_projects` ADD INDEX `idx_download`(`download_type`(100));
ALTER TABLE `#__swjprojects_translate_projects` ADD `payment` MEDIUMTEXT NOT NULL AFTER `fulltext`;
ALTER TABLE `#__swjprojects_versions` DROP INDEX `date`;
ALTER TABLE `#__swjprojects_versions` ADD INDEX `idx_date`(`date`);
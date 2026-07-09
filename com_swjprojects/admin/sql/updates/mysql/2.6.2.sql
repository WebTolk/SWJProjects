/*
 * @package       SW JProjects
 * @version    2.6.2
 * @author     Sergey Tolkachyov
 * @copyright  Copyright (c) 2018 - 2026 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link          https://web-tolk.ru
 */

CREATE TABLE IF NOT EXISTS `#__swjprojects_maintainers`
(
    `id`               int(11)                                                NOT NULL AUTO_INCREMENT,
    `asset_id`         int(10) unsigned                                       NOT NULL DEFAULT 0,
    `alias`            varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `state`            tinyint(3)                                             NOT NULL DEFAULT 0,
    `image`            varchar(255)                                           NOT NULL DEFAULT '',
    `website`          varchar(255)                                           NOT NULL DEFAULT '',
    `links`            text                                                   NULL,
    `params`           text                                                   NULL,
    `ordering`         int(11)                                                NOT NULL DEFAULT 0,
    `created`          datetime                                               NULL,
    `created_by`       int(10) unsigned                                       NOT NULL DEFAULT 0,
    `modified`         datetime                                               NULL,
    `modified_by`      int(10) unsigned                                       NOT NULL DEFAULT 0,
    `checked_out`      int(10) unsigned                                       NULL,
    `checked_out_time` datetime                                               NULL,
    PRIMARY KEY `id` (`id`),
    KEY `idx_alias` (`alias`(191)),
    KEY `idx_state` (`state`),
    KEY `idx_ordering` (`ordering`),
    KEY `idx_created_by` (`created_by`),
    KEY `idx_checked_out` (`checked_out`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__swjprojects_translate_maintainers`
(
    `id`          int(11)      NOT NULL DEFAULT 0,
    `language`    char(7)      NOT NULL DEFAULT '',
    `title`       varchar(255) NOT NULL DEFAULT '',
    `description` mediumtext   NULL,
    `metadata`    text         NULL,
    PRIMARY KEY (`id`, `language`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;

ALTER TABLE `#__swjprojects_maintainers`
    DROP COLUMN IF EXISTS `github`;

ALTER TABLE `#__swjprojects_projects`
    ADD `maintainer_id` int(11) NOT NULL DEFAULT 0 AFTER `additional_categories`;

ALTER TABLE `#__swjprojects_projects`
    ADD KEY `idx_maintainer_id` (`maintainer_id`);

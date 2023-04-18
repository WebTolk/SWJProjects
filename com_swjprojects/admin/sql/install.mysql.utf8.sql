/*
 * @package    SW JProjects Component
 * @version    1.6.4
 * @author Septdir Workshop, <https://septdir.com>, Sergey Tolkachyov <https://web-tolk.ru>
 * @сopyright (c) 2018 - April 2023 Septdir Workshop, Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link https://septdir.com, https://web-tolk.ru
 */

CREATE TABLE IF NOT EXISTS `#__swjprojects_versions`
(
    `id`             int(11)                                                NOT NULL AUTO_INCREMENT,
    `major`          int(10)                                                NOT NULL DEFAULT 0,
    `minor`          int(10)                                                NOT NULL DEFAULT 0,
    `micro`          int(10)                                                NOT NULL DEFAULT 0,
    `tag`            varchar(100)                                           NOT NULL DEFAULT '',
    `stability`      int(10)                                                NOT NULL DEFAULT 0,
    `alias`          varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `stage`          int(10)                                                NOT NULL DEFAULT 0,
    `state`          tinyint(3)                                             NOT NULL DEFAULT 0,
    `project_id`     int(11)                                                NOT NULL DEFAULT 0,
    `date`           datetime                                               NULL,
    `joomla_version` varchar(100)                                           NOT NULL DEFAULT '',
    `params`         text                                                   NULL,
    `downloads`      int(10)                                                NOT NULL DEFAULT 0,
    PRIMARY KEY `id` (`id`),
    KEY `idx_version` (`major`, `minor`, `micro`, `stability`, `stage`),
    KEY `idx_alias` (`alias`(191)),
    KEY `idx_state` (`state`),
    KEY `idx_project_id` (`project_id`),
    KEY `idx_date` (`date`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci
    AUTO_INCREMENT = 0;

CREATE TABLE IF NOT EXISTS `#__swjprojects_projects`
(
    `id`                    int(11)                                                NOT NULL AUTO_INCREMENT,
    `element`               varchar(100)                                           NOT NULL DEFAULT '',
    `alias`                 varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `state`                 tinyint(3)                                             NOT NULL DEFAULT 0,
    `catid`                 int(11)                                                NOT NULL DEFAULT 0,
    `additional_categories` text                                                   NULL,
    `download_type`         varchar(100)                                           NOT NULL DEFAULT 'free',
    `joomla`                text                                                   NULL,
    `urls`                  text                                                   NULL,
    `relations`             text                                                   NULL,
    `params`                text                                                   NULL,
    `ordering`              int(11)                                                NOT NULL DEFAULT 0,
    `hits`                  int(10)                                                NOT NULL DEFAULT 0,
    PRIMARY KEY `id` (`id`),
    KEY `idx_element` (`element`(100)),
    KEY `idx_download` (`download_type`(100)),
    KEY `idx_alias` (`alias`(191)),
    KEY `idx_state` (`state`),
    KEY `idx_catid` (`catid`),
    KEY `idx_ordering` (`ordering`),
    KEY `idx_hits` (`hits`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci
    AUTO_INCREMENT = 0;

CREATE TABLE IF NOT EXISTS `#__swjprojects_keys`
(
    `id`          int(11)             NOT NULL AUTO_INCREMENT,
    `key`         varchar(100) BINARY NOT NULL DEFAULT '',
    `note`        varchar(255)        NOT NULL DEFAULT '',
    `email`       varchar(100)        NOT NULL DEFAULT '',
    `order`       varchar(100)        NOT NULL DEFAULT '',
    `user`        int(10) unsigned    NOT NULL DEFAULT 0,
    `projects`    varchar(100)        NOT NULL DEFAULT '',
    `date_start`  datetime            NULL,
    `date_end`    datetime            NULL,
    `limit`       tinyint(3)          NOT NULL DEFAULT 0,
    `limit_count` int(11)             NOT NULL DEFAULT 0,
    `state`       tinyint(3)          NOT NULL DEFAULT 0,
    `plugins`     mediumtext,
    PRIMARY KEY `id` (`id`),
    KEY `idx_key` (`key`(100)),
    KEY `idx_email` (`email`(100)),
    KEY `idx_order` (`order`(100)),
    KEY `idx_user` (`user`),
    KEY `idx_projects` (`projects`),
    KEY `idx_state` (`state`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci
    AUTO_INCREMENT = 0;

CREATE TABLE IF NOT EXISTS `#__swjprojects_documentation`
(
    `id`         int(11)                                                NOT NULL AUTO_INCREMENT,
    `alias`      varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `state`      tinyint(3)                                             NOT NULL DEFAULT 0,
    `project_id` int(11)                                                NOT NULL DEFAULT 0,
    `params`     text                                                   NULL,
    `ordering`   int(11)                                                NOT NULL DEFAULT 0,
    PRIMARY KEY `id` (`id`),
    KEY `idx_alias` (`alias`(191)),
    KEY `idx_state` (`state`),
    KEY `idx_project` (`project_id`),
    KEY `idx_ordering` (`ordering`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci
    AUTO_INCREMENT = 0;

CREATE TABLE IF NOT EXISTS `#__swjprojects_categories`
(
    `id`        int(11)                                                NOT NULL AUTO_INCREMENT,
    `parent_id` int(11)                                                NOT NULL DEFAULT 0,
    `lft`       int(11)                                                NOT NULL DEFAULT 0,
    `rgt`       int(11)                                                NOT NULL DEFAULT 0,
    `level`     int(10)                                                NOT NULL DEFAULT 0,
    `path`      varchar(400)                                           NOT NULL DEFAULT '',
    `alias`     varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `state`     tinyint(3)                                             NOT NULL DEFAULT 0,
    `params`    text                                                   NOT NULL,
    PRIMARY KEY `id` (`id`),
    KEY `idx_left_right` (`lft`, `rgt`),
    KEY `idx_path` (`path`(100)),
    KEY `idx_alias` (`alias`(191)),
    KEY `idx_state` (`state`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci
    AUTO_INCREMENT = 0;

CREATE TABLE IF NOT EXISTS `#__swjprojects_translate_versions`
(
    `id`        int(11)    NOT NULL DEFAULT 0,
    `language`  char(7)    NOT NULL DEFAULT '',
    `changelog` mediumtext NULL,
    `metadata`  text       NULL,
    PRIMARY KEY (`id`, `language`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__swjprojects_translate_projects`
(
    `id`        int(11)      NOT NULL DEFAULT 0,
    `language`  char(7)      NOT NULL DEFAULT '',
    `title`     varchar(255) NOT NULL DEFAULT '',
    `introtext` text         NULL,
    `fulltext`  mediumtext   NULL,
    `gallery`   mediumtext   NULL,
    `payment`   mediumtext   NULL,
    `metadata`  TEXT         NULL,
    PRIMARY KEY (`id`, `language`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__swjprojects_translate_documentation`
(
    `id`        int(11)      NOT NULL DEFAULT 0,
    `language`  char(7)      NOT NULL DEFAULT '',
    `title`     varchar(255) NOT NULL DEFAULT '',
    `introtext` text         NULL,
    `fulltext`  mediumtext   NULL,
    `metadata`  text         NULL,
    PRIMARY KEY (`id`, `language`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__swjprojects_translate_categories`
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

CREATE TABLE IF NOT EXISTS `#__swjprojects_projects_categories`
(
    `project_id`  int(11) NOT NULL DEFAULT 0,
    `category_id` int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (`project_id`, `category_id`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;
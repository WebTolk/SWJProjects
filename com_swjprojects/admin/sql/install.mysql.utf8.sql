CREATE TABLE IF NOT EXISTS `#__swjprojects_versions`
(
    `id`             INT(11)                                                NOT NULL AUTO_INCREMENT,
    `major`          INT(10)                                                NOT NULL DEFAULT 0,
    `minor`          INT(10)                                                NOT NULL DEFAULT 0,
    `micro`          INT(10)                                                NOT NULL DEFAULT 0,
    `tag`            VARCHAR(100)                                           NOT NULL DEFAULT '',
    `stability`      INT(10)                                                NOT NULL DEFAULT 0,
    `alias`          VARCHAR(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `stage`          INT(10)                                                NOT NULL DEFAULT 0,
    `state`          TINYINT(3)                                             NOT NULL DEFAULT 0,
    `project_id`     INT(11)                                                NOT NULL DEFAULT 0,
    `date`           DATETIME                                               NOT NULL DEFAULT '0000-00-00 00:00:00',
    `joomla_version` VARCHAR(100)                                           NOT NULL DEFAULT '',
    `params`         TEXT                                                   NOT NULL,
    `downloads`      INT(10)                                                NOT NULL DEFAULT 0,
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
    `id`                    INT(11)                                                NOT NULL AUTO_INCREMENT,
    `element`               VARCHAR(100)                                           NOT NULL DEFAULT '',
    `alias`                 VARCHAR(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `state`                 TINYINT(3)                                             NOT NULL DEFAULT 0,
    `catid`                 INT(11)                                                NOT NULL DEFAULT 0,
    `additional_categories` TEXT                                                   NOT NULL,
    `download_type`         VARCHAR(100)                                           NOT NULL DEFAULT 'free',
    `joomla`                TEXT                                                   NOT NULL,
    `urls`                  TEXT                                                   NOT NULL,
    `relations`             TEXT                                                   NOT NULL,
    `params`                TEXT                                                   NOT NULL,
    `ordering`              INT(11)                                                NOT NULL DEFAULT 0,
    `hits`                  INT(10)                                                NOT NULL DEFAULT 0,
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
    `id`          INT(11)             NOT NULL AUTO_INCREMENT,
    `key`         VARCHAR(100) BINARY NOT NULL DEFAULT '',
    `note`        VARCHAR(255)        NOT NULL DEFAULT '',
    `email`       VARCHAR(100)        NOT NULL DEFAULT '',
    `order`       VARCHAR(100)        NOT NULL DEFAULT '',
    `user`        int(10) unsigned    NOT NULL DEFAULT 0,
    `projects`    VARCHAR(100)        NOT NULL DEFAULT '',
    `date_start`  DATETIME            NOT NULL DEFAULT '0000-00-00 00:00:00',
    `date_end`    DATETIME            NOT NULL DEFAULT '0000-00-00 00:00:00',
    `limit`       TINYINT(3)          NOT NULL DEFAULT 0,
    `limit_count` INT(11)             NOT NULL DEFAULT 0,
    `state`       TINYINT(3)          NOT NULL DEFAULT 0,
    `plugins`     MEDIUMTEXT          NOT NULL,
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
    `id`         INT(11)                                                NOT NULL AUTO_INCREMENT,
    `alias`      VARCHAR(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `state`      TINYINT(3)                                             NOT NULL DEFAULT 0,
    `project_id` INT(11)                                                NOT NULL DEFAULT 0,
    `params`     TEXT                                                   NOT NULL,
    `ordering`   INT(11)                                                NOT NULL DEFAULT 0,
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
    `id`        INT(11)                                                NOT NULL AUTO_INCREMENT,
    `parent_id` INT(11)                                                NOT NULL DEFAULT 0,
    `lft`       INT(11)                                                NOT NULL DEFAULT 0,
    `rgt`       INT(11)                                                NOT NULL DEFAULT 0,
    `level`     INT(10)                                                NOT NULL DEFAULT 0,
    `path`      VARCHAR(400)                                           NOT NULL DEFAULT '',
    `alias`     VARCHAR(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `state`     TINYINT(3)                                             NOT NULL DEFAULT 0,
    `params`    TEXT                                                   NOT NULL,
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
    `id`        INT(11)    NOT NULL DEFAULT 0,
    `language`  CHAR(7)    NOT NULL DEFAULT '',
    `changelog` MEDIUMTEXT NOT NULL,
    `metadata`  TEXT       NOT NULL,
    PRIMARY KEY (`id`, `language`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__swjprojects_translate_projects`
(
    `id`        INT(11)      NOT NULL DEFAULT 0,
    `language`  CHAR(7)      NOT NULL DEFAULT '',
    `title`     VARCHAR(255) NOT NULL DEFAULT '',
    `introtext` TEXT         NOT NULL,
    `fulltext`  MEDIUMTEXT   NOT NULL,
    `gallery`   MEDIUMTEXT,
    `payment`   MEDIUMTEXT   NOT NULL,
    `metadata`  TEXT         NOT NULL,
    PRIMARY KEY (`id`, `language`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__swjprojects_translate_documentation`
(
    `id`        INT(11)      NOT NULL DEFAULT 0,
    `language`  CHAR(7)      NOT NULL DEFAULT '',
    `title`     VARCHAR(255) NOT NULL DEFAULT '',
    `introtext` TEXT         NOT NULL,
    `fulltext`  MEDIUMTEXT   NOT NULL,
    `metadata`  TEXT         NOT NULL,
    PRIMARY KEY (`id`, `language`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__swjprojects_translate_categories`
(
    `id`          INT(11)      NOT NULL DEFAULT 0,
    `language`    CHAR(7)      NOT NULL DEFAULT '',
    `title`       VARCHAR(255) NOT NULL DEFAULT '',
    `description` MEDIUMTEXT   NOT NULL,
    `metadata`    TEXT         NOT NULL,
    PRIMARY KEY (`id`, `language`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__swjprojects_projects_categories`
(
    `project_id`  INT(11) NOT NULL DEFAULT 0,
    `category_id` INT(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (`project_id`, `category_id`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;
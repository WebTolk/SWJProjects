/*
 * @package    SW JProjects
 * @version    2.1.0.1
 * @author     Sergey Tolkachyov
 * @сopyright  Copyright (c) 2018 - 2024 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
 */

ALTER TABLE `#__swjprojects_keys` ADD `limit` TINYINT(3) NOT NULL DEFAULT 0 AFTER `date_end`;
ALTER TABLE `#__swjprojects_keys` ADD `limit_count` INT(11) NOT NULL DEFAULT 0 AFTER `limit`;
/*
 * @package    SW JProjects Component
 * @version    1.6.1
 * @author     Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2018 - 2022 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

ALTER TABLE `#__swjprojects_keys` ADD `limit` TINYINT(3) NOT NULL DEFAULT 0 AFTER `date_end`;
ALTER TABLE `#__swjprojects_keys` ADD `limit_count` INT(11) NOT NULL DEFAULT 0 AFTER `limit`;
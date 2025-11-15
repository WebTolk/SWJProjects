/*
 * @package       SW JProjects
 * @version    2.6.0-alpha
 * @author     Sergey Tolkachyov
 * @copyright  Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
 */

DROP TABLE `#__swjprojects_versions`;
DROP TABLE `#__swjprojects_projects`;
DROP TABLE `#__swjprojects_keys`;
DROP TABLE `#__swjprojects_documentation`;
DROP TABLE `#__swjprojects_categories`;
DROP TABLE `#__swjprojects_translate_versions`;
DROP TABLE `#__swjprojects_translate_projects`;
DROP TABLE `#__swjprojects_translate_categories`;
DROP TABLE `#__swjprojects_projects_categories`;
DELETE FROM `#__action_logs_extensions` WHERE `extension` = 'com_swjprojects';
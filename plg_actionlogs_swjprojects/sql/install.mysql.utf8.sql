/*
 * @package    SW JProjects
 * @version    2.3.0
 * @author     Sergey Tolkachyov
 * @сopyright  Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
 */

INSERT INTO `#__action_logs_extensions` (`extension`) VALUES ('com_swjprojects');
INSERT INTO `#__action_log_config` (`type_title`, `type_alias`, `id_holder`, `title_holder`, `table_name`, `text_prefix`) VALUES ('version', 'com_swjprojects.version', 'id', 'title', '#__swjprojects_versions', 'PLG_ACTIONLOG_SWJPROJECTS'),('project', 'com_swjprojects.project', 'id', 'title', '#__swjprojects_projects', 'PLG_ACTIONLOG_SWJPROJECTS'),('document', 'com_swjprojects.document', 'id', 'title', '#__swjprojects_documentation', 'PLG_ACTIONLOG_SWJPROJECTS'),('category', 'com_swjprojects.category', 'id', 'title', '#__swjprojects_categories', 'PLG_ACTIONLOG_SWJPROJECTS'),('key', 'com_swjprojects.key', 'id', 'title', '#__swjprojects_keys', 'PLG_ACTIONLOG_SWJPROJECTS');
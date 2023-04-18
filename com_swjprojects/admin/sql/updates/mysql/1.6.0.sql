/*
 * @package    SW JProjects Component
 * @version    1.6.4
 * @author Septdir Workshop, <https://septdir.com>, Sergey Tolkachyov <https://web-tolk.ru>
 * @сopyright (c) 2018 - April 2023 Septdir Workshop, Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link https://septdir.com, https://web-tolk.ru
 */

alter table `#__swjprojects_versions` modify `params` text NULL;
alter table `#__swjprojects_versions` modify `date` datetime NULL;

alter table `#__swjprojects_projects` modify `additional_categories` text NULL;
alter table `#__swjprojects_projects` modify `joomla` text NULL;
alter table `#__swjprojects_projects` modify `urls` text NULL;
alter table `#__swjprojects_projects` modify `relations` text NULL;
alter table `#__swjprojects_projects` modify `params` text NULL;

alter table `#__swjprojects_keys` modify `date_start` datetime NULL;
alter table `#__swjprojects_keys` modify `date_end` datetime NULL;
alter table `#__swjprojects_keys` modify `plugins` mediumtext NULL;

alter table `#__swjprojects_documentation` modify `params` text NULL;

alter table `#__swjprojects_categories` modify `params` text NULL;

alter table `#__swjprojects_translate_versions` modify `changelog` mediumtext NULL;
alter table `#__swjprojects_translate_versions` modify `metadata` text NULL;

alter table `#__swjprojects_translate_projects` modify `introtext` text NULL;
alter table `#__swjprojects_translate_projects` modify `fulltext` mediumtext NULL;
alter table `#__swjprojects_translate_projects` modify `gallery` mediumtext NULL;
alter table `#__swjprojects_translate_projects` modify `payment` mediumtext NULL;
alter table `#__swjprojects_translate_projects` modify `metadata` text NULL;

alter table `#__swjprojects_translate_documentation` modify `introtext` text NULL;
alter table `#__swjprojects_translate_documentation` modify `fulltext` mediumtext NULL;
alter table `#__swjprojects_translate_documentation` modify `metadata` text NULL;

alter table `#__swjprojects_translate_categories` modify `description` mediumtext NULL;
alter table `#__swjprojects_translate_categories` modify `metadata` text NULL;
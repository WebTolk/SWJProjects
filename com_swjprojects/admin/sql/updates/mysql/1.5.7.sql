/*
 * @package    SW JProjects Component
 * @version    1.6.4
 * @author Septdir Workshop, <https://septdir.com>, Sergey Tolkachyov <https://web-tolk.ru>
 * @сopyright (c) 2018 - April 2023 Septdir Workshop, Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link https://septdir.com, https://web-tolk.ru
 */

alter table `#__swjprojects_keys` drop index `idx_project_id`;
alter table `#__swjprojects_keys` change `project_id` `projects` varchar (100) not null default '';
alter table `#__swjprojects_keys` add index `idx_projects`(`projects`);
alter table `#__swjprojects_keys` add `user` int(10) unsigned not null default 0 after `order`;
alter table `#__swjprojects_keys` add index `idx_user`(`user`);


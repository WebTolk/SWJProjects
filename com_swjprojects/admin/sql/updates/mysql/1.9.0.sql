/*
 * @package    SW JProjects Component
 * @version    1.9.0-alpha
 * @author Septdir Workshop, <https://septdir.com>, Sergey Tolkachyov <https://web-tolk.ru>
 * @сopyright (c) 2018 - October 2023 Septdir Workshop, Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link https://septdir.com, https://web-tolk.ru
 */

ALTER TABLE `#__swjprojects_versions` CHANGE `micro` `patch` int(10) NOT NULL DEFAULT '0' AFTER `minor`;
ALTER TABLE `#__swjprojects_versions` ADD `hotfix` int(10) NOT NULL DEFAULT '0' AFTER `patch`;
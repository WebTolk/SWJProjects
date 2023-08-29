/*
 * @package    SW JProjects Component
 * @version    1.8.0
 * @author Septdir Workshop, <https://septdir.com>, Sergey Tolkachyov <https://web-tolk.ru>
 * @сopyright (c) 2018 - August 2023 Septdir Workshop, Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link https://septdir.com, https://web-tolk.ru
 */

ALTER TABLE `#__swjprojects_projects` ADD `visible` INT(1) NOT NULL DEFAULT 1 COMMENT 'Is project visible or not in frontend' AFTER `hits`;
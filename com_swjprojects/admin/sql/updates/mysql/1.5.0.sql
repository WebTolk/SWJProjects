/*
 * @package    SW JProjects
 * @version    2.0.1
 * @author     Sergey Tolkachyov
 * @сopyright  Copyright (c) 2018 - 2024 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
 */

ALTER TABLE `#__swjprojects_projects` ADD `additional_categories` TEXT NOT NULL AFTER `catid`;
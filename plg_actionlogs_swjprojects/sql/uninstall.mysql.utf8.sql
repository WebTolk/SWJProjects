/*
 * @package    SW JProjects
 * @version    2.3.0
 * @author     Sergey Tolkachyov
 * @сopyright  Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
 */

DELETE FROM `#__action_logs_extensions` WHERE `extension` = 'com_swjprojects';
DELETE FROM `#__action_logs_extensions` WHERE `type_alias` LIKE '%com_swjprojects%';
/*
 * @package    SW JProjects
 * @version    2.3.0
 * @author     Sergey Tolkachyov
 * @сopyright  Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
 */
document.addEventListener('DOMContentLoaded', () => {
    let elements = document.querySelectorAll('[data-key]');
    elements.forEach(el => {
        el.addEventListener('click', async () => {
            await navigator.clipboard.writeText(el.getAttribute('data-key')).then(() => {
                Joomla.renderMessages({
                    message: [Joomla.Text._('COM_SWJPROJECTS_USER_KEYS_KEY_SUCCESSFULLY_COPYED')]
                });
            }, () => {
                Joomla.renderMessages({
                    error: [Joomla.Text._('COM_SWJPROJECTS_USER_KEYS_KEY_NOT_COPYED')]
                });
            });
        });
    });
});
/**
 * @package       SW JProjects
 * @version       2.5.0-alhpa1
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
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
/**
 * @package    SW JProjects
 * @version       2.0.0
 * @Author        Sergey Tolkachyov, https://web-tolk.ru
 * @copyright     Copyright (C) 2024 Sergey Tolkachyov
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @since         1.0.0
 */

(() => {
    document.addEventListener('DOMContentLoaded', () => {
        // Get the elements
        const elements = document.querySelectorAll('[data-project-id]');

        for (let i = 0, l = elements.length; l > i; i += 1) {
            // Listen for click event
            elements[i].addEventListener('click', event => {
                event.preventDefault();
                const {
                    target
                } = event;

                const project_id = target.getAttribute('data-project-id');
                const project_title = target.getAttribute('data-project-title');
                const tmpl = document.getElementById('swjprojectseditorxtd_layout').value;

                if (!Joomla.getOptions('xtd-swjprojectseditorxtd')) {
                    // Something went wrong!
                    // @TODO Close the modal
                    return false;
                }

                const {
                    editor
                } = Joomla.getOptions('xtd-swjprojectseditorxtd');

                let linkString = '';
                if (tmpl === '--none--') {
                    linkString = '<a href="index.php?option=com_swjprojects&view=project&id=' + project_id + '">' + project_title + '</a>';
                } else {
                    linkString = "{swjprojects project_id=" + project_id + " tmpl=" + tmpl + "}";
                }

                window.parent.Joomla.editors.instances[editor].replaceSelection(linkString);

                if (window.parent.Joomla.Modal) {
                    window.parent.Joomla.Modal.getCurrent().close();
                }
            });
        }
    });
})();

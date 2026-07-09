<?php

/**
 * @package       SW JProjects
 * @version       2.7.0
 * @Author        Sergey Tolkachyov
 * @copyright  Copyright (c) 2018 - 2026 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link          https://web-tolk.ru
 * @since         2.7.0
 */

namespace Joomla\Component\SWJProjects\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\SWJProjects\Administrator\Helper\ProjectLinksHelper;

class ProjectlinktypesField extends ListField
{
    /**
     * The form field type.
     *
     * @var  string
     *
     * @since  2.7.0
     */
    protected $type = 'projectlinktypes';

    /**
     * Method to get the field options.
     *
     * @return  array
     *
     * @since  2.7.0
     */
    protected function getOptions(): array
    {
        $options = parent::getOptions();

        foreach (ProjectLinksHelper::getTypes() as $type) {
            $options[] = HTMLHelper::_('select.option', $type['code'], Text::_($type['title']));
        }

        return $options;
    }
}

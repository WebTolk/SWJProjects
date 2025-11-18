<?php
/**
 * @package       SW JProjects
 * @version       2.6.0
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
 */

namespace Joomla\Component\SWJProjects\Administrator\Field;

use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\SWJProjects\Administrator\Helper\ServerschemeHelper;
use Joomla\Registry\Registry;

use function defined;

defined('_JEXEC') or die();

class ServerschemelistField extends ListField
{
    /**
     * The form field type.
     *
     * @var  string
     *
     * @since  1.0.0
     */
    protected $type = 'Serverschemelist';

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     *
     * @throws  Exception
     *
     * @since  1.0.0
     */
    protected function getOptions(): array
    {
        $app = Factory::getApplication();
	    $lang = $app->getLanguage();
	    $lang->load('com_swjprojects', JPATH_ADMINISTRATOR);
        $usegolobal  = $this->element['useglobal'] ? boolval((string)$this->element['useglobal']) : false;
        $usecategory = $this->element['usecategory'] ? boolval((string)$this->element['usecategory']) : false;

        $options         = [];
        $componentParams = ComponentHelper::getParams('com_swjprojects');

        if ($usegolobal) {
            $global_value = $componentParams->get($this->fieldname, 'joomla');

            // Try with global configuration
            if (empty($global_value)) {
                $global_value = $app->get($this->fieldname);
            }

            if (!empty($global_value)) {
                $global_value = (string)$global_value;
            }

            $options[] = HTMLHelper::_(
                'select.option',
                'component_default',
                Text::sprintf('JGLOBAL_USE_GLOBAL_VALUE', $global_value)
            );
        }

        /**
         * If `usecategory` = true we can use project_id from $app->getInput()
         * and then get a category id from project
         * or use a $this->element['catid'] where category id specified
         */

        if ($usecategory) {
            $db    = $this->getDatabase();
            $catid = (int)$this->element['catid'];
            if (
                empty($catid)
                && $app->getInput()->get('option') == 'com_swjprojects'
                && $app->getInput()->get('view') == 'project'
            ) {
                // we get catid from project

                $query = $db->createQuery();
                $query->select($db->quoteName('catid'))
                      ->from($db->quoteName('#__swjprojects_projects'))
                      ->where($db->quoteName('id') . ' = ' . $db->quote($app->getInput()->getInt('id')));
                $catid = $db->setQuery($query)->loadResult();
            }

            $categoryParams = new Registry();
            if (!empty($catid)) {
                $query = $db->createQuery();
                $query->select($db->quoteName('params'))
                      ->from($db->quoteName('#__swjprojects_categories'))
                      ->where($db->quoteName('id') . ' = ' . $db->quote($catid));
                $params = $db->setQuery($query)->loadResult();
                if ($params) {
                    $categoryParams->loadString($params);
                }
            }
            $category_value = (string)$categoryParams->get($this->fieldname);
            if ($category_value == 'component_default' || empty($category_value)) {
                $category_value = $componentParams->get($this->fieldname, 'joomla');
            }

            $options[] = HTMLHelper::_(
                'select.option',
                'category_default',
	            Text::sprintf('COM_SWJPROJECTS_FIELD_SERVERSCHEMELIST_CATEGORY_DEFAULT', $category_value)
            );
        }
        $ServerSchemesList = ServerschemeHelper::getServerSchemesList();

        if (empty($ServerSchemesList)) {
            return [];
        }

        if ($usegolobal || $usecategory) {
            foreach ($options as &$option) {
                foreach ($ServerSchemesList as $sceme) {
                    $option->text = str_replace($sceme->getType(), $sceme->getName(), $option->text);
                }
            }

            if ($usegolobal) {
                $groups[Text::_('JOPTION_USE_DEFAULT')][] = HTMLHelper::_(
                    'select.option',
                    'component_default',
                    Text::sprintf('JGLOBAL_USE_GLOBAL_VALUE', $global_value)
                );
            }
            if ($usecategory) {
                $groups[Text::_('JOPTION_USE_DEFAULT')][] = HTMLHelper::_(
                    'select.option',
                    'category_default',
                    Text::sprintf('COM_SWJPROJECTS_FIELD_SERVERSCHEMELIST_CATEGORY_DEFAULT', $category_value)
                );
            }
            $options[] = HTMLHelper::_(
                'select.option',
                '',
                '-----'
            );
        }

        foreach ($ServerSchemesList as $key => $scheme_class) {
            $name = $scheme_class->getName();

            $options[] = HTMLHelper::_('select.option', $key, $name);
        }

        return $options;
    }
}

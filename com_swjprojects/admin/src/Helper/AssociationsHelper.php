<?php
/**
 * @package       SW JProjects
 * @version       2.6.2
 * @Author        Sergey Tolkachyov
 * @copyright  Copyright (c) 2018 - 2026 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link          https://web-tolk.ru
 * @since         2.6.1
 */

namespace Joomla\Component\SWJProjects\Administrator\Helper;

use Joomla\CMS\Association\AssociationExtensionHelper;
use Joomla\Component\SWJProjects\Site\Helper\AssociationHelper as SiteAssociationHelper;

use function defined;

\defined('_JEXEC') or die;

/**
 * Frontend association bridge for languagefilter and mod_languages.
 *
 * This component stores translations in custom translate tables, so we only
 * expose frontend associations as language-specific routes.
 *
 * @since  2.6.1
 */
class AssociationsHelper extends AssociationExtensionHelper
{
    /**
     * The extension name.
     *
     * @var string
     *
     * @since  2.6.1
     */
    protected $extension = 'com_swjprojects';

    /**
     * Whether the extension supports frontend associations.
     *
     * @var bool
     *
     * @since  2.6.1
     */
    protected $associationsSupport = true;

    /**
     * Returns language-specific frontend routes for the current item.
     *
     * @param   integer  $id    Item id.
     * @param   string   $view  Current view name.
     *
     * @return  array
     *
     * @since   2.6.1
     */
    public function getAssociationsForItem($id = 0, $view = null)
    {
        return SiteAssociationHelper::getAssociations((int) $id, $view);
    }
}

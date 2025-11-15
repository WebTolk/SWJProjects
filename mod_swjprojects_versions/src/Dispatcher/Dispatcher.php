<?php
/**
 * @package       SW JProjects
 * @version       2.6.0-alpha
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
 */

namespace Joomla\Module\Swjprojectsversions\Site\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\Module\Swjprojectsversions\Site\Helper\SwjprojectsversionsHelper;
use function defined;

defined('JPATH_PLATFORM') or die;
/**
 * Dispatcher class for mod_swjprojects_versions
 *
 * @since  1.0.0
 */
class Dispatcher extends AbstractModuleDispatcher
{

	/**
	 * Returns the layout data.
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	protected function getLayoutData()
	{
		$data = parent::getLayoutData();
		$helper = new SwjprojectsversionsHelper();
		$data['items'] = $helper->getVersions($data['params'], $this->getApplication());

		return $data;
	}
}
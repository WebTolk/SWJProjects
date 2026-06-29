<?php

/**
 * @package       SW JProjects
 * @version       2.6.2
 * @Author        Sergey Tolkachyov
 * @copyright  Copyright (c) 2018 - 2026 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link          https://web-tolk.ru
 * @since         2.6.2
 */

namespace Joomla\Component\SWJProjects\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Component\SWJProjects\Administrator\Helper\MaintainerLinksHelper;

class MaintainerlinktypesField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 *
	 * @since  2.6.2
	 */
	protected $type = 'maintainerlinktypes';

	/**
	 * Method to get the field options.
	 *
	 * @return  array
	 *
	 * @since  2.6.2
	 */
	protected function getOptions(): array
	{
		$options = parent::getOptions();

		foreach (MaintainerLinksHelper::getTypes() as $type) {
			$options[] = HTMLHelper::_('select.option', $type['code'], $type['title']);
		}

		return $options;
	}
}

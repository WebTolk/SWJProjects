<?php
/**
 * @package       SW JProjects
 * @subpackage    API
 * @copyright  Copyright (c) 2018 - 2026 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Joomla\Component\SWJProjects\Api\Model;

\defined('_JEXEC') or die;

class UserkeyModel extends UserkeysModel
{
	public function getItem($pk = null)
	{
		return parent::getItem($pk ?? (int) $this->getState($this->getName() . '.id'));
	}
}

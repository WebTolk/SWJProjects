<?php
/**
 * @package       SW JProjects
 * @version       2.6.1-dev
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
 */

namespace Joomla\Component\SWJProjects\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Table\Nested;
use Joomla\Database\DatabaseDriver;
use function defined;

class CategoriesTable extends Nested
{
	/**
	 * Cache for the root id.
	 *
	 * @var  integer
	 *
	 * @since  1.0.0
	 */
	protected static $root_id = 1;

	/**
	 * Constructor.
	 *
	 * @param   DatabaseDriver &$db  Database connector object
	 *
	 * @since  1.0.0
	 */
	function __construct(&$db)
	{
		parent::__construct('#__swjprojects_categories', 'id', $db);

		// Set the alias since the column is called state
		$this->setColumnAlias('published', 'state');
	}
}
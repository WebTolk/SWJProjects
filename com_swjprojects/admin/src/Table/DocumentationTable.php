<?php
/*
 * @package    SW JProjects
 * @version    2.2.0
 * @author     Sergey Tolkachyov
 * @сopyright  Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
 */

namespace Joomla\Component\SWJProjects\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use function defined;

class DocumentationTable extends Table
{
	/**
	 * Constructor.
	 *
	 * @param   DatabaseDriver &$db  Database connector object
	 *
	 * @since  1.0.0
	 */
	function __construct(&$db)
	{
		parent::__construct('#__swjprojects_documentation', 'id', $db);

		// Set the alias since the column is called state
		$this->setColumnAlias('published', 'state');
	}
}
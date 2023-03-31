<?php
/*
 * @package    SW JProjects Component
 * @version    1.6.2
 * @author     Septdir Workshop - www.septdir.com
 * @сopyright (c) 2018 - March 2023 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

class SWJProjectsTableProjects extends Table
{
	/**
	 * Constructor.
	 *
	 * @param   JDatabaseDriver &$db  Database connector object
	 *
	 * @since  1.0.0
	 */
	function __construct(&$db)
	{
		parent::__construct('#__swjprojects_projects', 'id', $db);

		// Set the alias since the column is called state
		$this->setColumnAlias('published', 'state');
	}
}
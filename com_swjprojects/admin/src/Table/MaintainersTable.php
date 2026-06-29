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

namespace Joomla\Component\SWJProjects\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

class MaintainersTable extends Table
{
	/**
	 * Track assets for future per-item ACL expansion.
	 *
	 * @var  boolean
	 *
	 * @since  2.6.2
	 */
	protected $_trackAssets = true;

	/**
	 * Constructor.
	 *
	 * @param   DatabaseDriver  $db  Database connector object.
	 *
	 * @since  2.6.2
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__swjprojects_maintainers', 'id', $db);

		$this->setColumnAlias('published', 'state');
	}

	/**
	 * Returns the asset title.
	 *
	 * @return  string
	 *
	 * @since  2.6.2
	 */
	protected function _getAssetTitle()
	{
		return 'com_swjprojects.maintainer.' . (int) $this->id;
	}

	/**
	 * Returns the asset name.
	 *
	 * @return  string
	 *
	 * @since  2.6.2
	 */
	protected function _getAssetName()
	{
		return 'com_swjprojects.maintainer.' . (int) $this->id;
	}

	/**
	 * Returns the asset parent id.
	 *
	 * @param   Table|null  $table  The table.
	 * @param   integer     $id     The id.
	 *
	 * @return  integer
	 *
	 * @since  2.6.2
	 */
	protected function _getAssetParentId($table = null, $id = null)
	{
		$asset = \Joomla\CMS\Table\Table::getInstance('Asset');
		$asset->loadByName('com_swjprojects');

		return (int) $asset->id ?: $asset->getRootId();
	}
}

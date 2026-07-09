<?php
/**
 * @package       SW JProjects
 * @subpackage    API
 * @copyright  Copyright (c) 2018 - 2026 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Joomla\Component\SWJProjects\Api\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Component\SWJProjects\Administrator\Helper\TranslationHelper;
use Joomla\Database\QueryInterface;
use Joomla\Utilities\ArrayHelper;
use function is_numeric;

class CategoriesModel extends ListModel
{
	protected function populateState($ordering = 'c.lft', $direction = 'ASC')
	{
		parent::populateState($ordering, $direction);
		$this->setState('filter.published', 1);
	}

	protected function getListQuery(): QueryInterface
	{
		$db = $this->getDatabase();
		$query = $this->getBaseQuery();
		$search = trim((string) $this->getState('filter.search'));

		if ($search !== '')
		{
			$escaped = '%' . str_replace(' ', '%', $db->escape($search, true)) . '%';
			$query->where(
				'('
				. $db->quoteName('t_c.title') . ' LIKE ' . $db->quote($escaped, false)
				. ' OR ' . $db->quoteName('td_c.title') . ' LIKE ' . $db->quote($escaped, false)
				. ' OR ' . $db->quoteName('c.alias') . ' LIKE ' . $db->quote($escaped, false)
				. ')'
			);
		}

		$parentId = $this->getState('filter.parent_id');

		if (is_numeric($parentId))
		{
			$query->where($db->quoteName('c.parent_id') . ' = ' . (int) $parentId);
		}

		$query->order($db->escape((string) $this->getState('list.ordering', 'c.lft')) . ' ' . $db->escape((string) $this->getState('list.direction', 'ASC')));

		return $query;
	}

	public function getItem($pk = null)
	{
		$db = $this->getDatabase();
		$query = $this->getBaseQuery()
			->where($db->quoteName('c.id') . ' = ' . (int) $pk);

		$item = $db->setQuery($query)->loadObject();

		if (!$item)
		{
			return null;
		}

		return $this->prepareItem($item);
	}

	public function getItems()
	{
		$items = parent::getItems();

		foreach ($items as &$item)
		{
			$item = $this->prepareItem($item);
		}

		return ArrayHelper::sortObjects($items, 'lft');
	}

	private function getBaseQuery(): QueryInterface
	{
		$db = $this->getDatabase();
		$current = (string) $this->getState('filter.language', TranslationHelper::getDefault());
		$default = TranslationHelper::getDefault();

		return $db->getQuery(true)
			->select([
				$db->quoteName('c.id'),
				$db->quoteName('c.parent_id'),
				$db->quoteName('c.level'),
				$db->quoteName('c.lft'),
				$db->quoteName('c.rgt'),
				$db->quoteName('c.path'),
				$db->quoteName('c.alias'),
				$db->quoteName('c.state'),
				$db->quoteName('t_c.title'),
				$db->quoteName('t_c.description'),
				$db->quoteName('t_c.language'),
				$db->quoteName('td_c.title', 'default_title'),
				$db->quoteName('td_c.description', 'default_description'),
			])
			->from($db->quoteName('#__swjprojects_categories', 'c'))
			->leftJoin(
				$db->quoteName('#__swjprojects_translate_categories', 't_c')
				. ' ON ' . $db->quoteName('t_c.id') . ' = ' . $db->quoteName('c.id')
				. ' AND ' . $db->quoteName('t_c.language') . ' = ' . $db->quote($current)
			)
			->leftJoin(
				$db->quoteName('#__swjprojects_translate_categories', 'td_c')
				. ' ON ' . $db->quoteName('td_c.id') . ' = ' . $db->quoteName('c.id')
				. ' AND ' . $db->quoteName('td_c.language') . ' = ' . $db->quote($default)
			)
			->where($db->quoteName('c.state') . ' = 1');
	}

	private function prepareItem(object $item): object
	{
		$item->title = $item->title ?: $item->default_title ?: $item->alias;
		$item->description = $item->description ?: $item->default_description ?: '';

		unset($item->default_title, $item->default_description);

		return $item;
	}
}

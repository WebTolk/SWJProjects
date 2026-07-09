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
use Joomla\Component\SWJProjects\Administrator\Helper\ProjectLinksHelper;
use Joomla\Component\SWJProjects\Administrator\Helper\TranslationHelper;
use Joomla\Database\QueryInterface;
use Joomla\Registry\Registry;
use function explode;
use function is_numeric;

class ProjectsModel extends ListModel
{
	protected function populateState($ordering = 'p.ordering', $direction = 'ASC')
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
				. $db->quoteName('p.element') . ' LIKE ' . $db->quote($escaped, false)
				. ' OR ' . $db->quoteName('t_p.title') . ' LIKE ' . $db->quote($escaped, false)
				. ' OR ' . $db->quoteName('td_p.title') . ' LIKE ' . $db->quote($escaped, false)
				. ')'
			);
		}

		$categoryId = $this->getState('filter.category_id');

		if (is_numeric($categoryId) && (int) $categoryId > 0)
		{
			$query->where(
				'('
				. $db->quoteName('p.catid') . ' = ' . (int) $categoryId
				. ' OR FIND_IN_SET(' . (int) $categoryId . ', ' . $db->quoteName('p.additional_categories') . ')'
				. ')'
			);
		}

		$downloadType = (string) $this->getState('filter.download_type');

		if ($downloadType !== '')
		{
			$query->where($db->quoteName('p.download_type') . ' = ' . $db->quote($downloadType));
		}

		$element = (string) $this->getState('filter.element');

		if ($element !== '')
		{
			$query->where($db->quoteName('p.element') . ' = ' . $db->quote($element));
		}

		$query->order($db->escape((string) $this->getState('list.ordering', 'p.ordering')) . ' ' . $db->escape((string) $this->getState('list.direction', 'ASC')));

		return $query;
	}

	public function getItem($pk = null)
	{
		$db = $this->getDatabase();
		$query = $this->getBaseQuery()
			->where($db->quoteName('p.id') . ' = ' . (int) $pk);

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

		return $items;
	}

	private function getBaseQuery(): QueryInterface
	{
		$db = $this->getDatabase();
		$current = (string) $this->getState('filter.language', TranslationHelper::getDefault());
		$default = TranslationHelper::getDefault();

		$lastVersionQuery = $db->getQuery(true)
			->select(
				'CONCAT('
				. $db->quoteName('lv.id') . ', ' . $db->quote(',') . ', '
				. $db->quoteName('lv.alias') . ', ' . $db->quote(',') . ', '
				. 'CASE WHEN ' . $db->quoteName('lv.hotfix') . ' != 0'
				. ' THEN CONCAT(' . $db->quoteName('lv.major') . ', ".", ' . $db->quoteName('lv.minor') . ', ".", ' . $db->quoteName('lv.patch') . ', ".", ' . $db->quoteName('lv.hotfix') . ')'
				. ' ELSE CONCAT(' . $db->quoteName('lv.major') . ', ".", ' . $db->quoteName('lv.minor') . ', ".", ' . $db->quoteName('lv.patch') . ') END'
				. ')'
			)
			->from($db->quoteName('#__swjprojects_versions', 'lv'))
			->where($db->quoteName('lv.project_id') . ' = ' . $db->quoteName('p.id'))
			->where($db->quoteName('lv.state') . ' = 1')
			->where($db->quoteName('lv.tag') . ' = ' . $db->quote('stable'))
			->order($db->quoteName('lv.major') . ' DESC')
			->order($db->quoteName('lv.minor') . ' DESC')
			->order($db->quoteName('lv.patch') . ' DESC')
			->order($db->quoteName('lv.hotfix') . ' DESC')
			->setLimit(1);

		return $db->getQuery(true)
			->select([
				$db->quoteName('p.id'),
				$db->quoteName('p.element'),
				$db->quoteName('p.alias'),
				$db->quoteName('p.catid'),
				$db->quoteName('p.additional_categories'),
				$db->quoteName('p.download_type'),
				$db->quoteName('p.ordering'),
				$db->quoteName('p.visible'),
				$db->quoteName('p.update_server'),
				$db->quoteName('p.joomla'),
				$db->quoteName('p.urls'),
				$db->quoteName('t_p.title'),
				$db->quoteName('t_p.introtext'),
				$db->quoteName('t_p.language'),
				$db->quoteName('td_p.title', 'default_title'),
				$db->quoteName('td_p.introtext', 'default_introtext'),
				$db->quoteName('c.alias', 'category_alias'),
				$db->quoteName('tc.title', 'category_title'),
				'(' . (string) $lastVersionQuery . ') AS ' . $db->quoteName('last_version_raw'),
			])
			->from($db->quoteName('#__swjprojects_projects', 'p'))
			->leftJoin($db->quoteName('#__swjprojects_categories', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('p.catid'))
			->leftJoin(
				$db->quoteName('#__swjprojects_translate_projects', 't_p')
				. ' ON ' . $db->quoteName('t_p.id') . ' = ' . $db->quoteName('p.id')
				. ' AND ' . $db->quoteName('t_p.language') . ' = ' . $db->quote($current)
			)
			->leftJoin(
				$db->quoteName('#__swjprojects_translate_projects', 'td_p')
				. ' ON ' . $db->quoteName('td_p.id') . ' = ' . $db->quoteName('p.id')
				. ' AND ' . $db->quoteName('td_p.language') . ' = ' . $db->quote($default)
			)
			->leftJoin(
				$db->quoteName('#__swjprojects_translate_categories', 'tc')
				. ' ON ' . $db->quoteName('tc.id') . ' = ' . $db->quoteName('c.id')
				. ' AND ' . $db->quoteName('tc.language') . ' = ' . $db->quote($current)
			)
			->where($db->quoteName('p.state') . ' = 1')
			->where($db->quoteName('c.state') . ' = 1')
			->where($db->quoteName('p.visible') . ' = 1')
			->group($db->quoteName('p.id'));
	}

	private function prepareItem(object $item): object
	{
		$item->title = $item->title ?: $item->default_title ?: $item->element;
		$item->introtext = $item->introtext ?: $item->default_introtext ?: '';
		$item->category = [
			'id' => (int) $item->catid,
			'alias' => (string) $item->category_alias,
			'title' => (string) $item->category_title,
		];
		$item->additional_categories = $item->additional_categories !== '' ? explode(',', (string) $item->additional_categories) : [];
		$item->joomla = (new Registry($item->joomla))->toArray();
		$item->urls = ProjectLinksHelper::normalizeLinks($item->urls);
		$item->last_version = null;

		if (!empty($item->last_version_raw))
		{
			[$id, $alias, $version] = explode(',', (string) $item->last_version_raw, 3);
			$item->last_version = [
				'id' => (int) $id,
				'alias' => (string) $alias,
				'number' => (string) $version,
			];
		}

		unset(
			$item->default_title,
			$item->default_introtext,
			$item->category_alias,
			$item->category_title,
			$item->last_version_raw
		);

		return $item;
	}
}

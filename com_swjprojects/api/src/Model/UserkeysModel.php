<?php
/**
 * @package       SW JProjects
 * @subpackage    API
 * @copyright  Copyright (c) 2018 - 2026 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Joomla\Component\SWJProjects\Api\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Component\SWJProjects\Administrator\Helper\TranslationHelper;
use Joomla\Component\SWJProjects\Administrator\Service\AccessService;
use Joomla\Database\QueryInterface;
use Joomla\Utilities\ArrayHelper;
use function array_merge;
use function array_unique;
use function boolval;
use function explode;
use function implode;
use function intval;
use function is_array;

class UserkeysModel extends ListModel
{
	private ?array $projects = null;

	protected function populateState($ordering = 'k.date_start', $direction = 'DESC')
	{
		parent::populateState($ordering, $direction);
	}

	protected function getListQuery(): QueryInterface
	{
		$db = $this->getDatabase();
		$user = Factory::getApplication()->getIdentity();
		$accessService = new AccessService($db);
		$requestedUserId = (int) $this->getState('filter.user_id');
		$permittedUserId = $accessService->getPermittedUserId($user, $requestedUserId ?: null);

		if ($permittedUserId === -1)
		{
			throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$query = $db->getQuery(true)
			->select([
				$db->quoteName('k.id'),
				$db->quoteName('k.key'),
				$db->quoteName('k.user'),
				$db->quoteName('k.projects'),
				$db->quoteName('k.date_start'),
				$db->quoteName('k.date_end'),
				$db->quoteName('k.limit'),
				$db->quoteName('k.limit_count'),
				$db->quoteName('k.state'),
				$db->quoteName('k.domain'),
				$db->quoteName('k.note'),
			])
			->from($db->quoteName('#__swjprojects_keys', 'k'));

		if ($permittedUserId !== null)
		{
			$query->where($db->quoteName('k.user') . ' = ' . $permittedUserId);
		}

		if (!$accessService->canViewAllUserKeys($user))
		{
			$query->where($db->quoteName('k.state') . ' = 1');
		}
		elseif ($this->getState('filter.state') !== null && $this->getState('filter.state') !== '')
		{
			$query->where($db->quoteName('k.state') . ' = ' . (int) $this->getState('filter.state'));
		}

		$search = trim((string) $this->getState('filter.search'));

		if ($search !== '')
		{
			$escaped = '%' . str_replace(' ', '%', $db->escape($search, true)) . '%';
			$query->where(
				'('
				. $db->quoteName('k.key') . ' LIKE ' . $db->quote($escaped, false)
				. ' OR ' . $db->quoteName('k.domain') . ' LIKE ' . $db->quote($escaped, false)
				. ' OR ' . $db->quoteName('k.note') . ' LIKE ' . $db->quote($escaped, false)
				. ')'
			);
		}

		$query->group($db->quoteName('k.id'));
		$query->order($db->escape((string) $this->getState('list.ordering', 'k.date_start')) . ' ' . $db->escape((string) $this->getState('list.direction', 'DESC')));

		return $query;
	}

	public function getItem($pk = null)
	{
		$db = $this->getDatabase();
		$user = Factory::getApplication()->getIdentity();
		$accessService = new AccessService($db);
		$requestedUserId = (int) $this->getState('filter.user_id');
		$permittedUserId = $accessService->getPermittedUserId($user, $requestedUserId ?: null);

		if ($permittedUserId === -1)
		{
			throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$query = $db->getQuery(true)
			->select([
				$db->quoteName('k.id'),
				$db->quoteName('k.key'),
				$db->quoteName('k.user'),
				$db->quoteName('k.projects'),
				$db->quoteName('k.date_start'),
				$db->quoteName('k.date_end'),
				$db->quoteName('k.limit'),
				$db->quoteName('k.limit_count'),
				$db->quoteName('k.state'),
				$db->quoteName('k.domain'),
				$db->quoteName('k.note'),
			])
			->from($db->quoteName('#__swjprojects_keys', 'k'))
			->where($db->quoteName('k.id') . ' = ' . (int) $pk);

		if ($permittedUserId !== null)
		{
			$query->where($db->quoteName('k.user') . ' = ' . $permittedUserId);
		}

		if (!$accessService->canViewAllUserKeys($user))
		{
			$query->where($db->quoteName('k.state') . ' = 1');
		}

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

		if (!$items)
		{
			return $items;
		}

		$projects = $this->getProjects(
			implode(',', array_merge(ArrayHelper::getColumn($items, 'projects')))
		);

		foreach ($items as &$item)
		{
			$item = $this->prepareItem($item, $projects);
		}

		return $items;
	}

	private function prepareItem(object $item, ?array $loadedProjects = null): object
	{
		$projects = $loadedProjects ?? $this->getProjects((string) $item->projects);
		$nullDate = $this->getDatabase()->getNullDate();
		$now = Factory::getDate()->toUnix();

		$item->limit = boolval($item->limit);
		$item->limit_count = intval($item->limit_count);
		$item->domain = $item->domain ?: $item->note;

		if (!empty($item->projects))
		{
			$ids = explode(',', (string) $item->projects);
			$item->projects = [];

			foreach ($ids as $id)
			{
				$id = (int) $id;

				if (!empty($projects[$id]))
				{
					$item->projects[$id] = $projects[$id];
				}
			}

			$item->projects = ArrayHelper::sortObjects($item->projects, 'ordering');
		}
		else
		{
			$item->projects = [];
		}

		if ($item->date_end === $nullDate)
		{
			$item->date_end = null;
		}

		if ($item->date_start === $nullDate)
		{
			$item->date_start = null;
		}

		$item->is_expired = $item->date_end ? Factory::getDate($item->date_end)->toUnix() < $now : false;
		$item->is_depleted = $item->limit && $item->limit_count <= 0;
		$item->can_download = (int) $item->state === 1 && !$item->is_expired && !$item->is_depleted;

		unset($item->note);

		return $item;
	}

	private function getProjects(string|array $pks): array
	{
		if ($this->projects === null)
		{
			$this->projects = [];
		}

		if (!is_array($pks))
		{
			$pks = array_unique(ArrayHelper::toInteger(explode(',', $pks)));
		}

		if (empty($pks))
		{
			return [];
		}

		$projects = [];
		$get = [];

		foreach ($pks as $pk)
		{
			if (isset($this->projects[$pk]))
			{
				$projects[$pk] = $this->projects[$pk];
			}
			else
			{
				$get[] = $pk;
			}
		}

		if (!empty($get))
		{
			$db = $this->getDatabase();
			$current = (string) $this->getState('filter.language', TranslationHelper::getDefault());
			$default = TranslationHelper::getDefault();
			$query = $db->getQuery(true)
				->select([
					$db->quoteName('p.id'),
					$db->quoteName('p.element'),
					$db->quoteName('p.catid'),
					$db->quoteName('p.ordering'),
					$db->quoteName('t_p.title'),
					$db->quoteName('td_p.title', 'default_title'),
				])
				->from($db->quoteName('#__swjprojects_projects', 'p'))
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
				->where($db->quoteName('p.id') . ' IN (' . implode(',', $get) . ')')
				->where($db->quoteName('p.state') . ' = 1');

			foreach ($db->setQuery($query)->loadObjectList() as $project)
			{
				$project->title = $project->title ?: $project->default_title ?: $project->element;
				unset($project->default_title);
				$this->projects[$project->id] = $project;
				$projects[$project->id] = $project;
			}
		}

		return $projects;
	}
}

<?php
/**
 * @package       SW JProjects
 * @version       2.6.2-dev
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         2.6.1
 */

namespace Joomla\Component\SWJProjects\Administrator\Helper;

use Joomla\CMS\Association\AssociationExtensionHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Component\SWJProjects\Site\Helper\AssociationHelper as SiteAssociationHelper;

use function defined;
use function array_search;
use function array_slice;
use function count;
use function explode;
use function in_array;
use function is_array;
use function trim;

defined('_JEXEC') or die;

/**
 * Frontend association bridge for languagefilter and mod_languages.
 *
 * This component stores translations in custom translate tables, so we only
 * expose frontend associations as language-specific routes.
 */
class AssociationsHelper extends AssociationExtensionHelper
{
	/**
	 * The extension name.
	 *
	 * @var string
	 *
	 * @since  2.6.1
	 */
	protected $extension = 'com_swjprojects';

	/**
	 * Whether the extension supports frontend associations.
	 *
	 * @var bool
	 *
	 * @since  2.6.1
	 */
	protected $associationsSupport = true;

	/**
	 * Returns language-specific frontend routes for the current item.
	 *
	 * @param   int  $id    Item id.
	 * @param   string   $view  Current view name.
	 *
	 * @return  array
	 *
	 * @since   2.6.1
	 */
	public function getAssociationsForItem($id = 0, $view = null)
	{
		$context = $this->resolveContext((int) $id, $view);

		return SiteAssociationHelper::getAssociations(
			$context['id'],
			$context['view'],
			$context['catid'],
			$context['project_id']
		);
	}

	/**
	 * Builds the current frontend context for associations.
	 *
	 * mod_languages can be rendered before the component view has fully populated
	 * the request state, so we resolve missing identifiers from the menu item and
	 * current SEF path when needed.
	 *
	 * @param   int          $id    Current item id from the caller.
	 * @param   string|null  $view  Current view from the caller.
	 *
	 * @return  array<string, int|string>
	 *
	 * @since   2.6.1
	 */
	private function resolveContext(int $id, ?string $view): array
	{
		$app   = Factory::getApplication();
		$input = $app->getInput();
		$db    = Factory::getContainer()->get(DatabaseInterface::class);
		$menu  = $app->getMenu();
		$active = $menu->getActive();

		$context = [
			'view'       => $view ?: $input->getCmd('view', ''),
			'id'         => $id ?: $input->getInt('id', 0),
			'catid'      => $input->getInt('catid', 0),
			'project_id' => $input->getInt('project_id', 0),
		];

		$context = $this->hydrateFromDatabase($db, $context);

		if ($this->hasEnoughContext($context))
		{
			return $context;
		}

		if (!$active || ($active->query['option'] ?? '') !== 'com_swjprojects')
		{
			return $context;
		}

		$resolved = $this->resolveFromMenuPath($db, $menu, $active);

		if (!$resolved)
		{
			return $context;
		}

		foreach ($resolved as $key => $value)
		{
			if (empty($context[$key]) && !empty($value))
			{
				$context[$key] = $value;
			}
		}

		return $this->hydrateFromDatabase($db, $context);
	}

	/**
	 * Completes missing foreign keys from the database.
	 *
	 * @param   DatabaseInterface  $db       Database connection.
	 * @param   array              $context  Current route context.
	 *
	 * @return  array
	 *
	 * @since   2.6.1
	 */
	private function hydrateFromDatabase(DatabaseInterface $db, array $context): array
	{
		if ($context['view'] === 'version' && !empty($context['id']))
		{
			if (empty($context['project_id']))
			{
				$context['project_id'] = $this->getScalar(
					$db,
					'#__swjprojects_versions',
					'project_id',
					'id',
					(int) $context['id']
				);
			}

			if (empty($context['catid']) && !empty($context['project_id']))
			{
				$context['catid'] = $this->getScalar(
					$db,
					'#__swjprojects_projects',
					'catid',
					'id',
					(int) $context['project_id']
				);
			}
		}

		if ($context['view'] === 'document' && !empty($context['id']))
		{
			if (empty($context['project_id']))
			{
				$context['project_id'] = $this->getScalar(
					$db,
					'#__swjprojects_documentation',
					'project_id',
					'id',
					(int) $context['id']
				);
			}

			if (empty($context['catid']) && !empty($context['project_id']))
			{
				$context['catid'] = $this->getScalar(
					$db,
					'#__swjprojects_projects',
					'catid',
					'id',
					(int) $context['project_id']
				);
			}
		}

		if (in_array($context['view'], ['project', 'versions', 'documentation'], true)
			&& empty($context['catid'])
			&& !empty($context['id']))
		{
			$context['catid'] = $this->getScalar(
				$db,
				'#__swjprojects_projects',
				'catid',
				'id',
				(int) $context['id']
			);
		}

		return $context;
	}

	/**
	 * Resolves route context from the active menu item and current SEF path.
	 *
	 * @param   DatabaseInterface  $db      Database connection.
	 * @param   AbstractMenu       $menu    Site menu.
	 * @param   object             $active  Active menu item.
	 *
	 * @return  array<string, int|string>|null
	 *
	 * @since   2.6.1
	 */
	private function resolveFromMenuPath(DatabaseInterface $db, AbstractMenu $menu, object $active): ?array
	{
		$query = is_array($active->query ?? null) ? $active->query : [];
		$view  = (string) ($query['view'] ?? '');
		$id    = (int) ($query['id'] ?? 0);
		$catid = (int) ($query['catid'] ?? 0);

		$pathSegments  = $this->splitPath(Uri::getInstance()->getPath());
		$routeSegments = $this->splitPath($active->route ?? '');
		$tailSegments  = $this->getTailSegments($pathSegments, $routeSegments);

		if ($view === 'projects')
		{
			return $this->resolveProjectsTail($db, $id, $tailSegments);
		}

		if ($view === 'project')
		{
			return $this->resolveProjectTail($db, $id, $catid, $tailSegments);
		}

		return [
			'view'       => $view,
			'id'         => $id,
			'catid'      => $catid,
			'project_id' => (int) ($query['project_id'] ?? 0),
		];
	}

	/**
	 * Resolves category/project/version/documentation paths under a category menu.
	 *
	 * @param   DatabaseInterface  $db       Database connection.
	 * @param   int                $catid    Category id from menu.
	 * @param   array              $segments Current path tail after the menu route.
	 *
	 * @return  array<string, int|string>
	 *
	 * @since   2.6.1
	 */
	private function resolveProjectsTail(DatabaseInterface $db, int $catid, array $segments): array
	{
		$context = [
			'view'       => 'projects',
			'id'         => $catid,
			'catid'      => 0,
			'project_id' => 0,
		];

		if (count($segments) === 0)
		{
			return $context;
		}

		$projectId = $this->getProjectIdByAlias($db, $segments[0], $catid);

		if (empty($projectId))
		{
			return $context;
		}

		$context = [
			'view'       => 'project',
			'id'         => $projectId,
			'catid'      => $catid,
			'project_id' => 0,
		];

		if (count($segments) === 1)
		{
			return $context;
		}

		if ($segments[1] === 'versions')
		{
			$context = [
				'view'       => 'versions',
				'id'         => $projectId,
				'catid'      => $catid,
				'project_id' => 0,
			];

			if (!empty($segments[2]))
			{
				$versionId = $this->getVersionIdByAlias($db, $segments[2], $projectId);

				if (!empty($versionId))
				{
					$context = [
						'view'       => 'version',
						'id'         => $versionId,
						'catid'      => $catid,
						'project_id' => $projectId,
					];
				}
			}

			return $context;
		}

		if ($segments[1] === 'documentation')
		{
			$context = [
				'view'       => 'documentation',
				'id'         => $projectId,
				'catid'      => $catid,
				'project_id' => 0,
			];

			if (!empty($segments[2]))
			{
				$documentId = $this->getDocumentIdByAlias($db, $segments[2], $projectId);

				if (!empty($documentId))
				{
					$context = [
						'view'       => 'document',
						'id'         => $documentId,
						'catid'      => $catid,
						'project_id' => $projectId,
					];
				}
			}
		}

		return $context;
	}

	/**
	 * Resolves nested paths when the active menu item points to a project.
	 *
	 * @param   DatabaseInterface  $db       Database connection.
	 * @param   int                $projectId  Project id from menu.
	 * @param   int                $catid    Category id from menu.
	 * @param   array              $segments Current path tail after the menu route.
	 *
	 * @return  array<string, int|string>
	 *
	 * @since   2.6.1
	 */
	private function resolveProjectTail(DatabaseInterface $db, int $projectId, int $catid, array $segments): array
	{
		if (count($segments) === 0)
		{
			return [
				'view'       => 'project',
				'id'         => $projectId,
				'catid'      => $catid,
				'project_id' => 0,
			];
		}

		if ($segments[0] === 'versions')
		{
			$context = [
				'view'       => 'versions',
				'id'         => $projectId,
				'catid'      => $catid,
				'project_id' => 0,
			];

			if (!empty($segments[1]))
			{
				$versionId = $this->getVersionIdByAlias($db, $segments[1], $projectId);

				if (!empty($versionId))
				{
					$context = [
						'view'       => 'version',
						'id'         => $versionId,
						'catid'      => $catid,
						'project_id' => $projectId,
					];
				}
			}

			return $context;
		}

		if ($segments[0] === 'documentation')
		{
			$context = [
				'view'       => 'documentation',
				'id'         => $projectId,
				'catid'      => $catid,
				'project_id' => 0,
			];

			if (!empty($segments[1]))
			{
				$documentId = $this->getDocumentIdByAlias($db, $segments[1], $projectId);

				if (!empty($documentId))
				{
					$context = [
						'view'       => 'document',
						'id'         => $documentId,
						'catid'      => $catid,
						'project_id' => $projectId,
					];
				}
			}

			return $context;
		}

		return [
			'view'       => 'project',
			'id'         => $projectId,
			'catid'      => $catid,
			'project_id' => 0,
		];
	}

	/**
	 * Checks whether we already have enough route data for associations.
	 *
	 * @param   array  $context  Current route context.
	 *
	 * @return  bool
	 *
	 * @since   2.6.1
	 */
	private function hasEnoughContext(array $context): bool
	{
		if (empty($context['view']) || empty($context['id']))
		{
			return false;
		}

		if (in_array($context['view'], ['version', 'document'], true))
		{
			return !empty($context['project_id']) && !empty($context['catid']);
		}

		if (in_array($context['view'], ['project', 'versions', 'documentation'], true))
		{
			return !empty($context['catid']);
		}

		return true;
	}

	/**
	 * Returns the path segments after the active menu route.
	 *
	 * @param   array  $pathSegments   Current URI path segments.
	 * @param   array  $routeSegments  Active menu route segments.
	 *
	 * @return  array
	 *
	 * @since   2.6.1
	 */
	private function getTailSegments(array $pathSegments, array $routeSegments): array
	{
		if (empty($routeSegments))
		{
			return $pathSegments;
		}

		$offset = array_search($routeSegments[0], $pathSegments, true);

		if ($offset === false)
		{
			return $pathSegments;
		}

		return array_slice($pathSegments, $offset + count($routeSegments));
	}

	/**
	 * Splits a URL path into normalized segments.
	 *
	 * @param   string  $path  Raw path.
	 *
	 * @return  array
	 *
	 * @since   2.6.1
	 */
	private function splitPath(string $path): array
	{
		$path = trim($path, '/');

		if ($path === '')
		{
			return [];
		}

		return array_values(array_filter(explode('/', $path), static fn ($segment) => $segment !== ''));
	}

	/**
	 * Returns a scalar value from a table by key.
	 *
	 * @param   DatabaseInterface  $db        Database connection.
	 * @param   string             $table     Table name.
	 * @param   string             $field     Field to select.
	 * @param   string             $keyField  Key field.
	 * @param   int                $keyValue  Key value.
	 *
	 * @return  int
	 *
	 * @since   2.6.1
	 */
	private function getScalar(DatabaseInterface $db, string $table, string $field, string $keyField, int $keyValue): int
	{
		$query = $db->getQuery(true)
			->select($db->quoteName($field))
			->from($db->quoteName($table))
			->where($db->quoteName($keyField) . ' = ' . (int) $keyValue);

		$db->setQuery($query);

		return (int) $db->loadResult();
	}

	/**
	 * Resolves a project id by alias inside the category.
	 *
	 * @param   DatabaseInterface  $db      Database connection.
	 * @param   string             $alias   Project alias.
	 * @param   int                $catid   Category id.
	 *
	 * @return  int
	 *
	 * @since   2.6.1
	 */
	private function getProjectIdByAlias(DatabaseInterface $db, string $alias, int $catid): int
	{
		$query = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from($db->quoteName('#__swjprojects_projects'))
			->where($db->quoteName('alias') . ' = ' . $db->quote($alias))
			->where($db->quoteName('catid') . ' = ' . (int) $catid);

		$db->setQuery($query);

		return (int) $db->loadResult();
	}

	/**
	 * Resolves a version id by alias inside the project.
	 *
	 * @param   DatabaseInterface  $db        Database connection.
	 * @param   string             $alias     Version alias.
	 * @param   int                $projectId Project id.
	 *
	 * @return  int
	 *
	 * @since   2.6.1
	 */
	private function getVersionIdByAlias(DatabaseInterface $db, string $alias, int $projectId): int
	{
		$query = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from($db->quoteName('#__swjprojects_versions'))
			->where($db->quoteName('alias') . ' = ' . $db->quote($alias))
			->where($db->quoteName('project_id') . ' = ' . (int) $projectId);

		$db->setQuery($query);

		return (int) $db->loadResult();
	}

	/**
	 * Resolves a documentation item id by alias inside the project.
	 *
	 * @param   DatabaseInterface  $db        Database connection.
	 * @param   string             $alias     Document alias.
	 * @param   int                $projectId Project id.
	 *
	 * @return  int
	 *
	 * @since   2.6.1
	 */
	private function getDocumentIdByAlias(DatabaseInterface $db, string $alias, int $projectId): int
	{
		$query = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from($db->quoteName('#__swjprojects_documentation'))
			->where($db->quoteName('alias') . ' = ' . $db->quote($alias))
			->where($db->quoteName('project_id') . ' = ' . (int) $projectId);

		$db->setQuery($query);

		return (int) $db->loadResult();
	}
}

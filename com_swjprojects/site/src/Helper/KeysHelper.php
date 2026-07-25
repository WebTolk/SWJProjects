<?php
/**
 * @package       SW JProjects
 * @version       2.6.2
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link          https://web-tolk.ru
 * @since         1.0.0
 */

namespace Joomla\Component\SWJProjects\Site\Helper;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use function defined;
use function implode;
use function md5;
use function strlen;

defined('_JEXEC') or die;

class KeysHelper
{
	/**
	 * Key checks results.
	 *
	 * @var  array
	 *
	 * @since  1.3.0
	 */
	protected static $checkResults = [];

	/**
	 * User project key lookup results.
	 *
	 * @var  array
	 *
	 * @since  2.7.1
	 */
	protected static $userProjectKeys = [];

	/**
	 * Method to check key.
	 *
	 * @param   int     $project_id  The id of the project.
	 * @param   string  $key         The key value.
	 *
	 * @return boolean|integer Key id on on success, false on failure.
	 *
	 * @since  1.3.0
	 */
	public static function checkKey(?int $project_id = null, ?string $key = null)
	{
		if (empty($key)) return false;

		$hash = (!empty($project_id)) ? $project_id : -1;
		$hash .= $key;
		$hash = md5($hash);
		if (!isset(self::$checkResults[$hash]))
		{
			$length = strlen($key);
			if ($length < 8)
			{
				// Check min length
				$result = false;
			}
			elseif ($length === 128)
			{    // Check master key
				$masterKey = ComponentHelper::getParams('com_swjprojects')->get('key_master');

				$result = ($key === $masterKey);
			}
			else
			{
				// Check database
				$db = Factory::getContainer()->get(DatabaseInterface::class);

				// Define null, empty and now dates
				$nullDate  = $db->quote($db->getNullDate());
				$emptyDate = $db->quote('');
				$nowDate   = $db->quote(Factory::getDate()->toSql());

				// Define  projects
				$projects = ['FIND_IN_SET(-1, projects)'];
				if (!empty($project_id)) $projects[] = 'FIND_IN_SET(' . $project_id . ', projects)';

				// Build query
				$query  = $db->getQuery(true)
					->select(['id'])
					->from($db->quoteName('#__swjprojects_keys'))
					->where('(' . implode(' OR ', $projects) . ')')
					->where($db->quoteName('key') . ' = ' . $db->quote($key))
					->where('state = 1')
					->where('(date_start IS NULL OR date_start = ' . $nullDate . ' OR date_start = ' . $emptyDate . ' OR date_start <= ' . $nowDate . ')')
					->where('(date_end IS NULL OR date_end = ' . $nullDate . ' OR date_end = ' . $emptyDate . ' OR date_end >= ' . $nowDate . ')')
					->where('(' . $db->quoteName('limit') . ' = 0 OR limit_count > 0)');
				$result = (int) $db->setQuery($query)->loadResult();
			}

			self::$checkResults[$hash] = $result;
		}

		return self::$checkResults[$hash];
	}

	/**
	 * Get the current user's valid download key for a paid project.
	 *
	 * @param   int|null  $project_id  The id of the project.
	 *
	 * @return  string|null  Key on success, null on failure.
	 *
	 * @since  2.7.1
	 */
	public static function getUserProjectKey(?int $project_id = null): ?string
	{
		$user = Factory::getApplication()->getIdentity();

		if (empty($project_id) || empty($user->id))
		{
			return null;
		}

		$projectId = (int) $project_id;
		$userId    = (int) $user->id;
		$cacheKey  = $userId . ':' . $projectId;

		if (!isset(self::$userProjectKeys[$cacheKey]))
		{
			$db        = Factory::getContainer()->get(DatabaseInterface::class);
			$nullDate  = $db->quote($db->getNullDate());
			$emptyDate = $db->quote('');
			$nowDate   = $db->quote(Factory::getDate()->toSql());

			$query = $db->getQuery(true)
				->select($db->quoteName('key'))
				->from($db->quoteName('#__swjprojects_keys'))
				->where('(' . implode(' OR ', [
					'FIND_IN_SET(' . $projectId . ', ' . $db->quoteName('projects') . ')',
					'FIND_IN_SET(-1, ' . $db->quoteName('projects') . ')',
				]) . ')')
				->where($db->quoteName('user') . ' = ' . $userId)
				->where($db->quoteName('state') . ' = 1')
				->where('CHAR_LENGTH(' . $db->quoteName('key') . ') >= 8')
				->where('(date_start IS NULL OR date_start = ' . $nullDate . ' OR date_start = ' . $emptyDate . ' OR date_start <= ' . $nowDate . ')')
				->where('(date_end IS NULL OR date_end = ' . $nullDate . ' OR date_end = ' . $emptyDate . ' OR date_end >= ' . $nowDate . ')')
				->where('(' . $db->quoteName('limit') . ' = 0 OR limit_count > 0)')
				->order('CASE WHEN FIND_IN_SET(' . $projectId . ', ' . $db->quoteName('projects') . ') THEN 0 ELSE 1 END ASC')
				->order($db->quoteName('limit') . ' ASC')
				->order($db->quoteName('id') . ' ASC');

			$key = (string) $db->setQuery($query, 0, 1)->loadResult();

			self::$userProjectKeys[$cacheKey] = $key !== '' ? $key : null;
		}

		return self::$userProjectKeys[$cacheKey];
	}
}
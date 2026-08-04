<?php
/**
 * @package       SW JProjects
 * @version       2.7.1
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2026 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link          https://web-tolk.ru
 * @since         2.7.1
 */

namespace Joomla\Component\SWJProjects\Site\Service;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;

/**
 * Resolves the public latest version policy for project cards and direct project downloads.
 *
 * @since  2.7.1
 */
final class VersionResolver
{
	public const PARAM_ALLOW_UNSTABLE_LATEST_DOWNLOADS = 'allow_unstable_latest_downloads';

	/**
	 * Check whether public project latest version links may target unstable versions.
	 *
	 * @return  boolean
	 *
	 * @since  2.7.1
	 */
	public static function allowUnstableLatestDownloads(): bool
	{
		return (bool) ComponentHelper::getParams('com_swjprojects')->get(
			self::PARAM_ALLOW_UNSTABLE_LATEST_DOWNLOADS,
			0
		);
	}

	/**
	 * Apply the public latest-version filter.
	 *
	 * @param   object        $query            Database query.
	 * @param   object        $db               Database driver.
	 * @param   string        $versionAlias     Version table alias.
	 * @param   boolean|null  $includeUnstable  Include unstable versions; null reads component params.
	 *
	 * @return  object
	 *
	 * @since  2.7.1
	 */
	public static function applyLatestVersionFilter($query, $db, string $versionAlias = 'v', ?bool $includeUnstable = null)
	{
		$includeUnstable = $includeUnstable ?? self::allowUnstableLatestDownloads();

		if (!$includeUnstable)
		{
			$query->where($db->quoteName($versionAlias . '.tag') . ' = ' . $db->quote('stable'));
		}

		return $query;
	}

	/**
	 * Apply the public latest-version ordering.
	 *
	 * @param   object        $query            Database query.
	 * @param   object        $db               Database driver.
	 * @param   string        $versionAlias     Version table alias.
	 * @param   boolean|null  $includeUnstable  Include unstable versions; null reads component params.
	 *
	 * @return  object
	 *
	 * @since  2.7.1
	 */
	public static function applyLatestVersionOrdering($query, $db, string $versionAlias = 'v', ?bool $includeUnstable = null)
	{
		$includeUnstable = $includeUnstable ?? self::allowUnstableLatestDownloads();

		$query->order($db->escape($versionAlias . '.major') . ' ' . $db->escape('desc'))
			->order($db->escape($versionAlias . '.minor') . ' ' . $db->escape('desc'))
			->order($db->escape($versionAlias . '.patch') . ' ' . $db->escape('desc'))
			->order($db->escape($versionAlias . '.hotfix') . ' ' . $db->escape('desc'));

		if ($includeUnstable)
		{
			$query->order($db->escape($versionAlias . '.stability') . ' ' . $db->escape('desc'))
				->order($db->escape($versionAlias . '.stage') . ' ' . $db->escape('desc'));
		}

		return $query;
	}

	/**
	 * Apply the full public latest-version policy to a query.
	 *
	 * @param   object        $query            Database query.
	 * @param   object        $db               Database driver.
	 * @param   string        $versionAlias     Version table alias.
	 * @param   boolean|null  $includeUnstable  Include unstable versions; null reads component params.
	 *
	 * @return  object
	 *
	 * @since  2.7.1
	 */
	public static function applyLatestVersionSelection($query, $db, string $versionAlias = 'v', ?bool $includeUnstable = null)
	{
		self::applyLatestVersionFilter($query, $db, $versionAlias, $includeUnstable);

		return self::applyLatestVersionOrdering($query, $db, $versionAlias, $includeUnstable);
	}
}

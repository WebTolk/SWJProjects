<?php

/**
 * @package       SW JProjects
 * @version       2.7.0
 * @Author        Sergey Tolkachyov
 * @copyright  Copyright (c) 2018 - 2026 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link          https://web-tolk.ru
 * @since         2.7.0
 */

namespace Joomla\Component\SWJProjects\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

use function array_key_exists;
use function array_values;
use function is_array;
use function is_object;
use function is_string;
use function json_decode;
use function json_encode;
use function json_last_error;
use function preg_replace;
use function strtolower;
use function trim;

use const JSON_ERROR_NONE;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

class ProjectLinksHelper
{
	/**
	 * Component parameter name for shared link type descriptors.
	 *
	 * @since  2.7.0
	 */
	public const PARAM_LINK_TYPES = MaintainerLinksHelper::PARAM_LINK_TYPES;

	/**
	 * Default shared link types.
	 *
	 * @return  array
	 *
	 * @since  2.7.0
	 */
	public static function getDefaultTypes(): array
	{
		return MaintainerLinksHelper::getDefaultTypes();
	}

	/**
	 * Get normalized component-level shared link types.
	 *
	 * @param   Registry|null  $params  Component params.
	 *
	 * @return  array
	 *
	 * @since  2.7.0
	 */
	public static function getTypes(?Registry $params = null): array
	{
		return MaintainerLinksHelper::getTypes($params);
	}

	/**
	 * Normalize raw subform data into code-keyed link types.
	 *
	 * @param   mixed  $types  Raw types.
	 *
	 * @return  array
	 *
	 * @since  2.7.0
	 */
	public static function normalizeTypes($types): array
	{
		return MaintainerLinksHelper::normalizeTypes($types);
	}

	/**
	 * Normalize legacy fixed-key URL maps and typed project link rows into one list shape.
	 *
	 * @param   mixed  $links  Raw project links.
	 *
	 * @return  array
	 *
	 * @since  2.7.0
	 */
	public static function normalizeLinks($links): array
	{
		$links = self::toArray($links);

		if (!is_array($links)) {
			return [];
		}

		if (self::containsTypedRows($links)) {
			return self::normalizeTypedRows($links);
		}

		return self::normalizeLegacyMap($links);
	}

	/**
	 * Convert links to JSON using the typed list shape.
	 *
	 * @param   mixed  $links  Raw project links.
	 *
	 * @return  string
	 *
	 * @since  2.7.0
	 */
	public static function toJson($links): string
	{
		$json = json_encode(
			array_values(self::normalizeLinks($links)),
			JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
		);

		return is_string($json) ? $json : '[]';
	}

	/**
	 * Detect typed rows from stored JSON lists or Joomla subform POST arrays.
	 *
	 * @param   array  $links  Raw project links.
	 *
	 * @return  bool
	 *
	 * @since  2.7.0
	 */
	protected static function containsTypedRows(array $links): bool
	{
		foreach ($links as $link) {
			if (is_object($link)) {
				return true;
			}

			if (!is_array($link)) {
				continue;
			}

			if (array_key_exists('type', $link) || array_key_exists('title', $link) || array_key_exists('value', $link)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Normalize typed project link rows.
	 *
	 * @param   array  $links  Raw typed rows.
	 *
	 * @return  array
	 *
	 * @since  2.7.0
	 */
	protected static function normalizeTypedRows(array $links): array
	{
		$normalized = [];

		foreach ($links as $link) {
			$link = self::normalizeLink($link);

			if ($link !== null) {
				$normalized[] = $link;
			}
		}

		return $normalized;
	}

	/**
	 * Normalize the public 2.6.2 fixed-key URL map into typed project link rows.
	 *
	 * @param   array  $links  Legacy fixed-key URL map.
	 *
	 * @return  array
	 *
	 * @since  2.7.0
	 */
	protected static function normalizeLegacyMap(array $links): array
	{
		$normalized = [];

		foreach ($links as $type => $value) {
			if (is_array($value) || is_object($value)) {
				continue;
			}

			$link = self::normalizeLink(['type' => $type, 'value' => $value]);

			if ($link !== null) {
				$normalized[] = $link;
			}
		}

		return $normalized;
	}

	/**
	 * Normalize one link row.
	 *
	 * @param   mixed  $link  Raw link.
	 *
	 * @return  array|null
	 *
	 * @since  2.7.0
	 */
	protected static function normalizeLink($link): ?array
	{
		if (is_object($link)) {
			$link = (array) $link;
		}

		if (!is_array($link)) {
			return null;
		}

		$type = self::normalizeCode($link['type'] ?? '');
		$title = trim((string) ($link['title'] ?? ''));
		$value = trim((string) ($link['value'] ?? ''));

		if ($type === '' || $value === '') {
			return null;
		}

		return [
			'type'  => $type,
			'title' => $title,
			'value' => $value,
		];
	}

	/**
	 * Convert raw input to an array when possible.
	 *
	 * @param   mixed  $value  Raw value.
	 *
	 * @return  mixed
	 *
	 * @since  2.7.0
	 */
	protected static function toArray($value)
	{
		if ($value instanceof Registry) {
			return $value->toArray();
		}

		if (is_string($value)) {
			$value = trim($value);

			if ($value === '') {
				return [];
			}

			$decoded = json_decode($value, true);

			if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
				return $decoded;
			}

			return (new Registry($value))->toArray();
		}

		if (is_object($value)) {
			return (array) $value;
		}

		return $value;
	}

	/**
	 * Normalize link type codes.
	 *
	 * @param   mixed  $code  Raw code.
	 *
	 * @return  string
	 *
	 * @since  2.7.0
	 */
	protected static function normalizeCode($code): string
	{
		return preg_replace('/[^a-z0-9_-]/', '', strtolower(trim((string) $code)));
	}
}

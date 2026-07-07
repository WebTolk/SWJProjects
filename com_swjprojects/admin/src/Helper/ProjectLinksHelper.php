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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\Registry\Registry;

use function array_is_list;
use function array_values;
use function in_array;
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
	public const PARAM_LINK_TYPES = 'project_link_types';

	/**
	 * Default project link types matching the legacy fixed URL fields.
	 *
	 * @return  array
	 *
	 * @since  2.7.0
	 */
	public static function getDefaultTypes(): array
	{
		return [
			[
				'code'       => 'demo',
				'title'      => 'Demo',
				'value_type' => 'url',
				'icon_class' => 'fas fa-external-link-alt',
			],
			[
				'code'       => 'support',
				'title'      => 'Support',
				'value_type' => 'url',
				'icon_class' => 'fas fa-info-circle',
			],
			[
				'code'       => 'github',
				'title'      => 'GitHub',
				'value_type' => 'url',
				'icon_class' => 'fab fa-github-square',
			],
			[
				'code'       => 'jed',
				'title'      => 'Joomla Extensions Directory',
				'value_type' => 'url',
				'icon_class' => 'fab fa-joomla',
			],
			[
				'code'       => 'donate',
				'title'      => 'Donate',
				'value_type' => 'url',
				'icon_class' => 'fas fa-donate',
			],
			[
				'code'       => 'documentation',
				'title'      => 'Documentation',
				'value_type' => 'url',
				'icon_class' => 'fas fa-file-alt',
			],
		];
	}

	/**
	 * Get normalized component-level project link types.
	 *
	 * @param   Registry|null  $params  Component params.
	 *
	 * @return  array
	 *
	 * @since  2.7.0
	 */
	public static function getTypes(?Registry $params = null): array
	{
		$params = $params ?: ComponentHelper::getParams('com_swjprojects');
		$types  = self::normalizeTypes($params->get(self::PARAM_LINK_TYPES, []));

		return $types ?: self::normalizeTypes(self::getDefaultTypes());
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
		$types = self::toArray($types);

		if (!is_array($types)) {
			return [];
		}

		$normalized = [];

		foreach ($types as $type) {
			if (is_object($type)) {
				$type = (array) $type;
			}

			if (!is_array($type)) {
				continue;
			}

			$code = self::normalizeCode($type['code'] ?? '');

			if ($code === '') {
				continue;
			}

			$valueType = trim((string) ($type['value_type'] ?? 'url'));

			if (!in_array($valueType, ['url', 'email'], true)) {
				$valueType = 'url';
			}

			$normalized[$code] = [
				'code'       => $code,
				'title'      => trim((string) ($type['title'] ?? $code)),
				'value_type' => $valueType,
				'icon_class' => trim((string) ($type['icon_class'] ?? '')),
			];
		}

		return $normalized;
	}

	/**
	 * Normalize legacy fixed-key URL maps and typed-list project links into one list shape.
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

		$normalized = [];

		if (array_is_list($links)) {
			foreach ($links as $link) {
				$link = self::normalizeLink($link);

				if ($link !== null) {
					$normalized[] = $link;
				}
			}

			return $normalized;
		}

		foreach ($links as $link) {
			$link = self::normalizeLink($link);

			if ($link === null) {
				$normalized = [];
				break;
			}

			$normalized[] = $link;
		}

		if ($normalized !== []) {
			return $normalized;
		}

		foreach ($links as $type => $value) {
			$link = self::normalizeLink(['type' => $type, 'value' => $value]);

			if ($link !== null) {
				$normalized[] = $link;
			}
		}

		return $normalized;
	}

	/**
	 * Convert links to the legacy code => value map expected by older rendering code.
	 *
	 * @param   mixed  $links  Raw project links.
	 *
	 * @return  array
	 *
	 * @since  2.7.0
	 */
	public static function toMap($links): array
	{
		$map = [];

		foreach (self::normalizeLinks($links) as $link) {
			if (!isset($map[$link['type']])) {
				$map[$link['type']] = $link['value'];
			}
		}

		return $map;
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
		$json = json_encode(array_values(self::normalizeLinks($links)), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

		return is_string($json) ? $json : '[]';
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

		$type  = self::normalizeCode($link['type'] ?? '');
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


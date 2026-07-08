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

namespace Joomla\Component\SWJProjects\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\Registry\Registry;

use function in_array;
use function is_array;
use function is_object;
use function is_string;
use function json_decode;
use function json_last_error;
use function preg_replace;
use function strtolower;
use function trim;

use const JSON_ERROR_NONE;

class MaintainerLinksHelper
{
	public const PARAM_LINK_TYPES = 'link_types';
	private const LEGACY_PARAM_LINK_TYPES = ['maintainer_link_types', 'project_link_types'];

	/**
	 * Default shared link types.
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
				'title'      => 'COM_SWJPROJECTS_URLS_DEMO',
				'value_type' => 'url',
				'icon_class' => 'fas fa-external-link-alt',
			],
			[
				'code'       => 'support',
				'title'      => 'COM_SWJPROJECTS_URLS_SUPPORT',
				'value_type' => 'url',
				'icon_class' => 'fas fa-info-circle',
			],
			[
				'code'       => 'github',
				'title'      => 'COM_SWJPROJECTS_URLS_GITHUB',
				'value_type' => 'url',
				'icon_class' => 'fab fa-github-square',
			],
			[
				'code'       => 'jed',
				'title'      => 'COM_SWJPROJECTS_URLS_JED',
				'value_type' => 'url',
				'icon_class' => 'fab fa-joomla',
			],
			[
				'code'       => 'donate',
				'title'      => 'COM_SWJPROJECTS_URLS_DONATE',
				'value_type' => 'url',
				'icon_class' => 'fas fa-donate',
			],
			[
				'code'       => 'documentation',
				'title'      => 'COM_SWJPROJECTS_URLS_DOCUMENTATION',
				'value_type' => 'url',
				'icon_class' => 'fas fa-file-alt',
			],
		];
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
		$params = $params ?: ComponentHelper::getParams('com_swjprojects');
		$types  = self::normalizeTypes($params->get(self::PARAM_LINK_TYPES, []));

		if ($types !== []) {
			return $types;
		}

		foreach (self::LEGACY_PARAM_LINK_TYPES as $legacyParam) {
			$types = self::mergeTypes($types, self::normalizeTypes($params->get($legacyParam, [])));
		}

		return $types ?: self::normalizeTypes(self::getDefaultTypes());
	}

	/**
	 * Normalize raw subform data into code-keyed link types.
	 *
	 * @param   mixed  $types  Raw types.
	 *
	 * @return  array
	 *
	 * @since  2.6.2
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

			$code = trim((string) ($type['code'] ?? ''));

			if ($code === '') {
				continue;
			}

			$code = preg_replace('/[^a-z0-9_-]/', '', strtolower($code));
			$valueType = trim((string) ($type['value_type'] ?? 'url'));

			if (!in_array($valueType, ['url', 'email'], true)) {
				$valueType = 'url';
			}

			$normalized[$code] = [
				'code'       => $code,
				'title'      => self::normalizeTitle($code, $type['title'] ?? ''),
				'value_type' => $valueType,
				'icon_class' => trim((string) ($type['icon_class'] ?? '')),
			];
		}

		return $normalized;
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
	private static function toArray($value)
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
	 * Normalize built-in titles to language constants while keeping custom titles intact.
	 *
	 * @param   string  $code   Link type code.
	 * @param   mixed   $title  Raw title.
	 *
	 * @return  string
	 *
	 * @since  2.7.0
	 */
	private static function normalizeTitle(string $code, $title): string
	{
		$title = trim((string) $title);
		$defaultTitle = self::getDefaultTitleConstant($code);

		if ($defaultTitle === '') {
			return $title !== '' ? $title : $code;
		}

		if ($title === '' || self::isLegacyDefaultTitle($code, $title)) {
			return $defaultTitle;
		}

		return $title;
	}

	/**
	 * Get the default language key for a built-in link type.
	 *
	 * @param   string  $code  Link type code.
	 *
	 * @return  string
	 *
	 * @since  2.7.0
	 */
	private static function getDefaultTitleConstant(string $code): string
	{
		return match ($code) {
			'demo' => 'COM_SWJPROJECTS_URLS_DEMO',
			'support' => 'COM_SWJPROJECTS_URLS_SUPPORT',
			'github' => 'COM_SWJPROJECTS_URLS_GITHUB',
			'jed' => 'COM_SWJPROJECTS_URLS_JED',
			'donate' => 'COM_SWJPROJECTS_URLS_DONATE',
			'documentation' => 'COM_SWJPROJECTS_URLS_DOCUMENTATION',
			default => '',
		};
	}

	/**
	 * Detect legacy built-in titles that should be migrated to language constants.
	 *
	 * @param   string  $code   Link type code.
	 * @param   string  $title  Raw title.
	 *
	 * @return  bool
	 *
	 * @since  2.7.0
	 */
	private static function isLegacyDefaultTitle(string $code, string $title): bool
	{
		$legacy = match ($code) {
			'demo' => ['Demo', 'Демо'],
			'support' => ['Support', 'Поддержка'],
			'github' => ['GitHub'],
			'jed' => ['JED', 'Joomla Extensions Directory'],
			'donate' => ['Donate', 'Поддержать'],
			'documentation' => ['Documentation', 'Документация'],
			default => [],
		};

		return in_array($title, $legacy, true) || $title === self::getDefaultTitleConstant($code);
	}

	/**
	 * Merge code-keyed link type arrays, letting later groups override earlier ones.
	 *
	 * @param   array  ...$groups  Link type groups.
	 *
	 * @return  array
	 *
	 * @since  2.7.0
	 */
	private static function mergeTypes(array ...$groups): array
	{
		$merged = [];

		foreach ($groups as $group) {
			foreach ($group as $code => $type) {
				$merged[$code] = $type;
			}
		}

		return $merged;
	}
}
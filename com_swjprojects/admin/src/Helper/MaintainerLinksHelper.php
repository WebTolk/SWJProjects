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
use function preg_replace;
use function strtolower;
use function trim;

class MaintainerLinksHelper
{
	public const PARAM_LINK_TYPES = 'maintainer_link_types';

	/**
	 * Default maintainer link types.
	 *
	 * @return  array
	 *
	 * @since  2.6.2
	 */
	public static function getDefaultTypes(): array
	{
		return [
			[
				'code'       => 'jed',
				'title'      => 'Joomla Extensions Directory',
				'value_type' => 'url',
				'icon_class' => 'fab fa-joomla',
			],
			[
				'code'       => 'github',
				'title'      => 'GitHub',
				'value_type' => 'url',
				'icon_class' => 'fab fa-github',
			],
		];
	}

	/**
	 * Get normalized component-level maintainer link types.
	 *
	 * @param   Registry|null  $params  Component params.
	 *
	 * @return  array
	 *
	 * @since  2.6.2
	 */
	public static function getTypes(?Registry $params = null): array
	{
		$params = $params ?: ComponentHelper::getParams('com_swjprojects');
		$types = $params->get(self::PARAM_LINK_TYPES, []);
		$types = self::normalizeTypes($types);

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
		if ($types instanceof Registry) {
			$types = $types->toArray();
		}

		if (is_object($types)) {
			$types = (array) $types;
		}

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
				'title'      => trim((string) ($type['title'] ?? $code)),
				'value_type' => $valueType,
				'icon_class' => trim((string) ($type['icon_class'] ?? '')),
			];
		}

		return $normalized;
	}
}

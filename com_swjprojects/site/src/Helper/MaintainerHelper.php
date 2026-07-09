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

namespace Joomla\Component\SWJProjects\Site\Helper;

use function defined;
use function is_array;
use function is_string;
use function json_decode;
use function trim;

defined('_JEXEC') or die;

class MaintainerHelper
{
	public static function buildProjection(
		int $id = 0,
		string $alias = '',
		string $title = '',
		string $website = '',
		string $image = '',
		$links = null,
		string $fallbackTitle = ''
	): ?object {
		if ($id <= 0)
		{
			return null;
		}

		$title = trim($title);

		if ($title === '')
		{
			$title = trim($fallbackTitle);
		}

		if ($title === '')
		{
			$title = trim($alias);
		}

		$decodedLinks = [];

		if (is_string($links) && $links !== '')
		{
			$decodedLinks = json_decode($links, true) ?: [];
		}
		elseif (is_array($links))
		{
			$decodedLinks = $links;
		}

		return (object) [
			'id'      => $id,
			'alias'   => $alias,
			'title'   => $title,
			'website' => $website,
			'image'   => $image,
			'links'   => $decodedLinks,
		];
	}
}

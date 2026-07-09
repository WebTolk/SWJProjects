<?php
/**
 * @package       SW JProjects
 * @subpackage    API
 * @copyright  Copyright (c) 2018 - 2026 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Joomla\Component\SWJProjects\Api\View\Userkeys;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;

class JsonapiView extends BaseApiView
{
	protected $fieldsToRenderItem = [
		'id',
		'user',
		'key',
		'domain',
		'projects',
		'date_start',
		'date_end',
		'limit',
		'limit_count',
		'state',
		'is_expired',
		'is_depleted',
		'can_download',
	];

	protected $fieldsToRenderList = [
		'id',
		'user',
		'key',
		'domain',
		'projects',
		'date_start',
		'date_end',
		'limit',
		'limit_count',
		'state',
		'is_expired',
		'is_depleted',
		'can_download',
	];
}

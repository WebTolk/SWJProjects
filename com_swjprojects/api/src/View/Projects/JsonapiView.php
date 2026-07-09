<?php
/**
 * @package       SW JProjects
 * @subpackage    API
 * @copyright  Copyright (c) 2018 - 2026 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Joomla\Component\SWJProjects\Api\View\Projects;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;

class JsonapiView extends BaseApiView
{
	protected $fieldsToRenderItem = [
		'id',
		'element',
		'alias',
		'catid',
		'category',
		'additional_categories',
		'title',
		'introtext',
		'download_type',
		'update_server',
		'joomla',
		'urls',
		'last_version',
		'language',
	];

	protected $fieldsToRenderList = [
		'id',
		'element',
		'alias',
		'catid',
		'category',
		'title',
		'download_type',
		'update_server',
		'last_version',
		'language',
	];
}

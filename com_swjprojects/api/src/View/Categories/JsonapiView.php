<?php
/**
 * @package       SW JProjects
 * @subpackage    API
 * @copyright  Copyright (c) 2018 - 2026 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Joomla\Component\SWJProjects\Api\View\Categories;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;

class JsonapiView extends BaseApiView
{
	protected $fieldsToRenderItem = [
		'id',
		'parent_id',
		'level',
		'lft',
		'rgt',
		'path',
		'alias',
		'title',
		'description',
		'language',
	];

	protected $fieldsToRenderList = [
		'id',
		'parent_id',
		'level',
		'path',
		'alias',
		'title',
		'language',
	];
}

<?php
/**
 * @package       SW JProjects
 * @subpackage    API
 * @copyright  Copyright (c) 2018 - 2026 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Joomla\Component\SWJProjects\Api\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\MVC\Controller\ApiController;

class ProjectsController extends ApiController
{
	use LanguageAwareControllerTrait;

	protected $contentType = 'projects';

	protected $default_view = 'projects';

	public function displayList()
	{
		$apiFilterInfo = $this->input->get('filter', [], 'array');
		$filter = InputFilter::getInstance();
		$this->applyLanguageFilter();

		if (\array_key_exists('search', $apiFilterInfo))
		{
			$this->modelState->set('filter.search', $filter->clean($apiFilterInfo['search'], 'STRING'));
		}

		if (\array_key_exists('category_id', $apiFilterInfo))
		{
			$this->modelState->set('filter.category_id', $filter->clean($apiFilterInfo['category_id'], 'INT'));
		}

		if (\array_key_exists('download_type', $apiFilterInfo))
		{
			$this->modelState->set('filter.download_type', $filter->clean($apiFilterInfo['download_type'], 'STRING'));
		}

		if (\array_key_exists('element', $apiFilterInfo))
		{
			$this->modelState->set('filter.element', $filter->clean($apiFilterInfo['element'], 'CMD'));
		}

		return parent::displayList();
	}

	public function displayItem($id = null)
	{
		$this->applyLanguageFilter();

		return parent::displayItem($id);
	}
}

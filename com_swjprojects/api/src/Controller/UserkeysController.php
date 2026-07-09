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

class UserkeysController extends ApiController
{
	use LanguageAwareControllerTrait;

	protected $contentType = 'userkeys';

	protected $default_view = 'userkeys';

	public function displayList()
	{
		$apiFilterInfo = $this->input->get('filter', [], 'array');
		$filter = InputFilter::getInstance();
		$this->applyLanguageFilter();

		if (\array_key_exists('search', $apiFilterInfo))
		{
			$this->modelState->set('filter.search', $filter->clean($apiFilterInfo['search'], 'STRING'));
		}

		if (\array_key_exists('state', $apiFilterInfo))
		{
			$this->modelState->set('filter.state', $filter->clean($apiFilterInfo['state'], 'INT'));
		}

		if (\array_key_exists('user_id', $apiFilterInfo))
		{
			$this->modelState->set('filter.user_id', $filter->clean($apiFilterInfo['user_id'], 'INT'));
		}

		return parent::displayList();
	}

	public function displayItem($id = null)
	{
		$this->applyLanguageFilter();

		return parent::displayItem($id);
	}
}

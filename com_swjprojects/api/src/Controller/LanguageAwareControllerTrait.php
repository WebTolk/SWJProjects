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
use Joomla\Component\SWJProjects\Administrator\Helper\TranslationHelper;
use function in_array;

trait LanguageAwareControllerTrait
{
	protected function applyLanguageFilter(): void
	{
		$filter = InputFilter::getInstance();
		$apiFilterInfo = $this->input->get('filter', [], 'array');
		$language = $filter->clean($this->input->get('lang', '', 'CMD'), 'CMD');

		if ($language === '' && \array_key_exists('language', $apiFilterInfo))
		{
			$language = $filter->clean($apiFilterInfo['language'], 'CMD');
		}

		if ($language === '' || !in_array($language, TranslationHelper::getCodes(), true))
		{
			$language = TranslationHelper::getDefault();
		}

		$this->modelState->set('filter.language', $language);
	}
}

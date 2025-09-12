<?php
/**
 * @package       SW JProjects
 * @version       2.5.0
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
 */

namespace Joomla\Component\SWJProjects\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\Component\SWJProjects\Site\Helper\RouteHelper;
use function defined;

class ProjectchangelogurlField extends FormField
{

	protected $type = 'projectchangelogurl';

	/**
	 * Method to get the field input markup for a spacer.
	 * The spacer does not have accept input.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.7.0
	 */
	protected function getInput()
	{
		$app = Factory::getApplication();

		if ($app->getInput()->get('view') == 'project' && !empty($project_id = $app->getInput()->get('id')))
		{
			$model   = $app->bootComponent('com_swjprojects')->getMVCFactory()->createModel('Project', 'Administrator', ['ignore_request' => false]);
			$project = $model->getItem();
			$url     = Uri::getInstance(Route::link('site', RouteHelper::getJChangelogRoute(null, $project->element), false, '', true));

			if (!empty($url->getVar('Itemid')))
			{
				$url->delVar('Itemid');
			}

			return '</div>
			<div class="col-12 alert alert-info mt-4">
			<h4>' . Text::_('COM_SWJPROJECTS_SERVER_PARAMS_CHANGELOGURL') . '</h4>
			 <p>' . Text::sprintf('COM_SWJPROJECTS_JOOMLA_CHANGELOGURL_URL_FIELD_DESC', $url->toString()) . '</p>
			</div><div>
			';

		}

		return '';
	}

	/**
	 * Method to get the field title.
	 *
	 * @return  string  The field title.
	 *
	 * @since   1.7.0
	 */
	protected function getTitle()
	{
		return $this->getLabel();
	}

	/**
	 * @return  string  The field label markup.
	 *
	 * @since   1.7.0
	 */
	protected function getLabel()
	{
		return ' ';
	}

}

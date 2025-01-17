<?php
/*
 * @package    SW JProjects
 * @version    2.2.1
 * @author     Sergey Tolkachyov
 * @сopyright  Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
 */

namespace Joomla\Component\SWJProjects\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Component\SWJProjects\Site\Helper\RouteHelper;
use function defined;


class ProjectupdateserverurlField extends FormField
{

	protected $type = 'projectupdateserverurl';

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
			$url     = Uri::getInstance(Route::link('Site', RouteHelper::getJUpdateRoute('', $project->element), false, '', true));

			$component_params = ComponentHelper::getParams('com_swjprojects');
			// Join over current translates
			$lang = $component_params->get('changelogurl_language');
			if (empty($lang))
			{
				$lang = $app->getLanguage()->getTag();
			}
			$project_name = $project->translates[$lang]->title;
			if (empty($project_name))
			{
				$project_name = 'Your extension name';
			}

			if (!empty($url->getVar('Itemid')))
			{
				$url->delVar('Itemid');
			}

			return '</div>
				<div class="col-12 alert alert-info mt-4">
				<h4>' . Text::_('COM_SWJPROJECTS_JOOMLA_UPDATE_SERVER') . '</h4>
				 <p><code>' . $url->toString() . '</code></p>
				 <p>' . Text::sprintf('COM_SWJPROJECTS_JOOMLA_UPDATE_SERVER_URL_FIELD_DESC', $project_name, $url->toString()) . '</p>
				</div><div>
			';
		}
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

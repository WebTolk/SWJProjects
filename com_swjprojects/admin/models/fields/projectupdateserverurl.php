<?php
/**
 * @package       WebTolk plugin info field
 * @version       1.8.0
 * @Author        Sergey Tolkachyov, https://web-tolk.ru
 * @copyright     Copyright (C) 2020 Sergey Tolkachyov
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
 * @since         1.0.0
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\NoteField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Language\Text;
use \Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Component\ComponentHelper;

class JFormFieldProjectupdateserverurl extends JFormField
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
		return '';
	}

	/**
	 * @return  string  The field label markup.
	 *
	 * @since   1.7.0
	 */
	protected function getLabel()
	{
		$app = Factory::getApplication();

		if ($app->input->get('view') == 'project' && !empty($project_id = $app->input->get('id')))
		{
			JLoader::register('SWJProjectsHelperRoute', JPATH_SITE . '/components/com_swjprojects/helpers/route.php');

			BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_swjprojects/models');
			$model   = BaseDatabaseModel::getInstance('Project', 'SWJProjectsModel', array('ignore_request' => false));
			$project = $model->getItem();
			$url     = Uri::getInstance(Uri::root());

			$component_params = ComponentHelper::getParams('com_swjprojects');
			// Join over current translates
			$lang         = $component_params->get('changelogurl_language', 'en-GB');
			$project_name = $project->translates[$lang]->title;
			if (empty($project_name))
			{
				$project_name = 'Your extension name';
			}
			$url->setPath(Route::link('site', SWJProjectsHelperRoute::getJUpdateRoute('', $project->element)));


			return $html = '</div>
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

}

?>
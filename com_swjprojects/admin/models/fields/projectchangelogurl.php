<?php
/**
 * @package     WebTolk plugin info field
 * @version     1.8.0
 * @Author 		Sergey Tolkachyov, https://web-tolk.ru
 * @copyright   Copyright (C) 2020 Sergey Tolkachyov
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
 * @since 		1.0.0
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

class JFormFieldProjectchangelogurl extends JFormField
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
			$model = BaseDatabaseModel::getInstance('Project', 'SWJProjectsModel', array('ignore_request' => false));
			$project = $model->getItem();
			$url = Uri::getInstance(Uri::root());
			$url->setPath(Route::link('site', SWJProjectsHelperRoute::getJChangelogRoute('', $project->element)));
			
						
			return $html = '</div>
			<div class="col-12 alert alert-info mt-4">
			<h4>'.Text::_('COM_SWJPROJECTS_PARAMS_CHANGELOGURL').'</h4>
			 <p>'.Text::sprintf('COM_SWJPROJECTS_JOOMLA_CHANGELOGURL_URL_FIELD_DESC', $url->toString()).'</p>
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

}

?>
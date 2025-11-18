<?php
/**
 * @package       SW JProjects
 * @version       2.6.0
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
 */

namespace Joomla\Component\SWJProjects\Administrator\View\Project;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\SWJProjects\Administrator\Helper\SWJProjectsHelper;
use function count;
use function defined;
use function implode;

class HtmlView extends BaseHtmlView
{
	/**
	 * Model state variables.
	 *
	 * @var  Joomla\CMS\Object\CMSObject
	 *
	 * @since  1.0.0
	 */
	protected $state;

	/**
	 * Form object.
	 *
	 * @var  Form
	 *
	 * @since  1.0.0
	 */
	protected $form;

	/**
	 * Translates forms array.
	 *
	 * @var  array
	 *
	 * @since  1.0.0
	 */
	protected $translateForms;

	/**
	 * Project object.
	 *
	 * @var  object
	 *
	 * @since  1.0.0
	 */
	protected $item;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse.
	 *
	 * @throws  \Exception
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since  1.0.0
	 */
	public function display($tpl = null)
	{
		$this->state          = $this->get('State');
		$this->form           = $this->get('Form');
		$this->translateForms = $this->get('TranslateForms');
		$this->item           = $this->get('Item');

		// Check for errors
		if (count($errors = $this->get('Errors')))
		{
			throw new \Exception(implode('\n', $errors), 500);
		}

		// Add title and toolbar
		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add title and toolbar.
	 *
	 * @throws  \Exception
	 *
	 * @since  1.0.0
	 */
	protected function addToolbar()
	{
		$isNew     = ($this->item->id == 0);
		$canDo     = SWJProjectsHelper::getActions('com_swjprojects', 'project', $this->item->id);
		$toolbar   = Toolbar::getInstance();

		// Disable menu
		Factory::getApplication()->input->set('hidemainmenu', true);

		// Set page title
		$title = ($isNew) ? Text::_('COM_SWJPROJECTS_PROJECT_ADD') : Text::_('COM_SWJPROJECTS_PROJECT_EDIT');
		ToolbarHelper::title(Text::_('COM_SWJPROJECTS') . ': ' . $title, 'cube');

		// Add apply & save buttons
		if ($canDo->get('core.edit'))
		{
			ToolbarHelper::apply('project.apply');
			ToolbarHelper::save('project.save');
		}

		// Add save new button
		if ($canDo->get('core.create'))
		{
			ToolbarHelper::save2new('project.save2new');
		}

		// Add cancel button
		ToolbarHelper::cancel('project.cancel', 'JTOOLBAR_CLOSE');

		// Add joomla update server buttons
		if ($this->item->id)
		{
			// Joomla update server button
			$link = 'index.php?option=com_swjprojects&task=siteRedirect&page=jupdate&debug=1&element=' . $this->item->element;
			if ($this->item->download_type === 'paid')
			{
				$link .= '&download_key=' . ComponentHelper::getParams('com_swjprojects')->get('key_master');
			}
			$jupdate = LayoutHelper::render('components.swjprojects.toolbar.link',
				array('link' => $link, 'text' => 'COM_SWJPROJECTS_PROJECT_UPDATE_SERVER', 'icon' => 'joomla'));
			$toolbar->appendButton('Custom', $jupdate, 'joomla');

			// Joomla changelog url button
			$link = 'index.php?option=com_swjprojects&task=siteRedirect&page=jchangelog&debug=1&element=' . $this->item->element;

			$jchangelog = LayoutHelper::render('components.swjprojects.toolbar.link',
				array('link' => $link, 'text' => 'COM_SWJPROJECTS_SERVER_PARAMS_CHANGELOGURL', 'icon' => 'joomla'));
			$toolbar->appendButton('Custom', $jchangelog, 'joomla');
		}

		// Add translate switcher
		$switcher = LayoutHelper::render('components.swjprojects.translate.switcher');
		$toolbar->appendButton('Custom', $switcher, 'translate-switcher');

		// Add GitHub button
		$link = 'https://github.com/WebTolk/SWJProjects';
		$github = LayoutHelper::render('components.swjprojects.toolbar.link',
			array('link' => $link, 'text' => 'GitHub', 'icon' => ' fab fa-github', 'new' => true));
		$toolbar->appendButton('Custom', $github, 'github');

		// Add preview button
		if ($this->item->id)
		{
			$link    = 'index.php?option=com_swjprojects&task=siteRedirect&page=project&debug=1&id=' . $this->item->id
				. '&catid=' . $this->item->catid;
			$preview = LayoutHelper::render('components.swjprojects.toolbar.link',
				array('link' => $link, 'text' => 'JGLOBAL_PREVIEW', 'icon' => 'eye'));
			$toolbar->appendButton('Custom', $preview, 'preview');
		}
	}
}
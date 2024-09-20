<?php
/*
 * @package    SW JProjects
 * @version    2.1.0.1
 * @author     Sergey Tolkachyov
 * @сopyright  Copyright (c) 2018 - 2024 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
 */

namespace Joomla\Component\SWJProjects\Administrator\View\Document;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\SWJProjects\Administrator\Helper\SWJProjectsHelper;

class HtmlView extends BaseHtmlView
{
	/**
	 * Model state variables.
	 *
	 * @var  Joomla\CMS\Object\CMSObject
	 *
	 * @since  1.4.0
	 */
	protected $state;

	/**
	 * Form object.
	 *
	 * @var  Form
	 *
	 * @since  1.4.0
	 */
	protected $form;

	/**
	 * Translates forms array.
	 *
	 * @var  array
	 *
	 * @since  1.4.0
	 */
	protected $translateForms;

	/**
	 * Document object.
	 *
	 * @var  object
	 *
	 * @since  1.4.0
	 */
	protected $item;

	/**
	 * Project object.
	 *
	 * @var  object
	 *
	 * @since  1.4.0
	 */
	protected $project;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @throws  \Exception
	 *
	 * @since  1.4.0
	 */
	public function display($tpl = null)
	{
		$this->state          = $this->get('State');
		$this->form           = $this->get('Form');
		$this->translateForms = $this->get('TranslateForms');
		$this->item           = $this->get('Item');
		$this->project        = $this->getModel()->getProject($this->form->getValue('project_id', '', 0));

		Factory::getApplication()->getDocument()->getWebAssetManager()->addInlineScript("function projectHasChanged(element) {
			document.body.appendChild(document.createElement('joomla-core-loader'));
			document.querySelector('input[name=task]').value = 'document.reload';
			element.form.submit();
		}");

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
	 * @since  1.4.0
	 */
	protected function addToolbar()
	{
		$isNew   = ($this->item->id == 0);
		$canDo   = SWJProjectsHelper::getActions('com_swjprojects', 'document', $this->item->id);
		$toolbar = Toolbar::getInstance();

		// Disable menu
		Factory::getApplication()->input->set('hidemainmenu', true);

		// Set page title
		$title = ($isNew) ? Text::_('COM_SWJPROJECTS_DOCUMENT_ADD') : Text::_('COM_SWJPROJECTS_DOCUMENT_EDIT');
		ToolbarHelper::title(Text::_('COM_SWJPROJECTS') . ': ' . $title, 'cube');

		// Add apply & save buttons
		if ($canDo->get('core.edit'))
		{
			ToolbarHelper::apply('document.apply');
			ToolbarHelper::save('document.save');
		}

		// Add save new button
		if ($canDo->get('core.create'))
		{
			ToolbarHelper::save2new('document.save2new');
		}

		// Add cancel button
		ToolbarHelper::cancel('document.cancel', 'JTOOLBAR_CLOSE');

		// Add translate switcher
		$switcher = LayoutHelper::render('components.swjprojects.translate.switcher');
		$toolbar->appendButton('Custom', $switcher, 'translate-switcher');


		// Add GitHub button
		$link   = 'https://github.com/WebTolk/SWJProjects';
		$github = LayoutHelper::render('components.swjprojects.toolbar.link',
			array('link' => $link, 'text' => 'GitHub', 'icon' => ' fab fa-github', 'new' => true));
		$toolbar->appendButton('Custom', $github, 'github');

		// Add preview button
		if ($this->item->id)
		{
			// Preview button
			$link    = 'index.php?option=com_swjprojects&task=siteRedirect&page=document&debug=1&id=' . $this->item->id
				. '&project_id=' . $this->project->id . '&catid=' . $this->project->catid;
			$preview = LayoutHelper::render('components.swjprojects.toolbar.link',
				array('link' => $link, 'text' => 'JGLOBAL_PREVIEW', 'icon' => 'eye'));
			$toolbar->appendButton('Custom', $preview, 'preview');
		}
	}
}
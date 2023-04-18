<?php
/*
 * @package    SW JProjects Component
 * @version    1.6.4
 * @author Septdir Workshop, <https://septdir.com>, Sergey Tolkachyov <https://web-tolk.ru>
 * @сopyright (c) 2018 - April 2023 Septdir Workshop, Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link https://septdir.com, https://web-tolk.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Version;

class SWJProjectsViewVersion extends HtmlView
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
	 * Version object.
	 *
	 * @var  object
	 *
	 * @since  1.0.0
	 */
	protected $item;

	/**
	 * Project object.
	 *
	 * @var  object
	 *
	 * @since  1.0.0
	 */
	protected $project;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse.
	 *
	 * @throws  Exception
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
		$this->project        = $this->getModel()->getProject($this->form->getValue('project_id', '', 0));

		// Check for errors
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode('\n', $errors), 500);
		}

		// Prepare form
		if (!$this->project || empty($this->project->joomla['type']))
		{
			$this->form->removeField('joomla_version', '');
		}

		if ((new Version())->isCompatible('4.0'))
		{
			Factory::getDocument()->addScriptDeclaration("function projectHasChanged(element) {
				document.body.appendChild(document.createElement('joomla-core-loader'));
				document.querySelector('input[name=task]').value = 'version.reload';
				element.form.submit();
			}");
		}
		else
		{
			Factory::getDocument()->addScriptDeclaration("function projectHasChanged(element) {
				var cat = jQuery(element);
				Joomla.loadingLayer('show');
				jQuery('input[name=task]').val('version.reload');
				element.form.submit();
			}");
		}


		// Add title and toolbar
		$this->addToolbar();

		return parent::display($tpl);
	}

	/**
	 * Add title and toolbar.
	 *
	 * @throws  Exception
	 *
	 * @since  1.0.0
	 */
	protected function addToolbar()
	{
		$isNew     = ($this->item->id == 0);
		$canDo     = SWJProjectsHelper::getActions('com_swjprojects', 'version', $this->item->id);
		$toolbar   = Toolbar::getInstance();

		// Disable menu
		Factory::getApplication()->input->set('hidemainmenu', true);

		// Set page title
		$title = ($isNew) ? Text::_('COM_SWJPROJECTS_VERSION_ADD') : Text::_('COM_SWJPROJECTS_VERSION_EDIT');
		ToolbarHelper::title(Text::_('COM_SWJPROJECTS') . ': ' . $title, 'cube');

		// Add apply & save buttons
		if ($canDo->get('core.edit'))
		{
			ToolbarHelper::apply('version.apply');
			ToolbarHelper::save('version.save');
		}

		// Add save new button
		if ($canDo->get('core.create'))
		{
			ToolbarHelper::save2new('version.save2new');
		}

		// Add cancel button
		ToolbarHelper::cancel('version.cancel', 'JTOOLBAR_CLOSE');

		// Add preview & download buttons
		if ($this->item->id)
		{
			// Download button
			if ($this->item->file)
			{
				$link = 'index.php?option=com_swjprojects&task=siteRedirect&page=download&debug=1&version_id='
					. $this->item->id;
				if ($this->project->download_type === 'paid')
				{
					$link .= '&download_key=' . ComponentHelper::getParams('com_swjprojects')->get('key_master');
				}
				$download = LayoutHelper::render('components.swjprojects.toolbar.link',
					array('link' => $link, 'text' => 'COM_SWJPROJECTS_FILE_DOWNLOAD', 'icon' => 'download', 'new' => false));
				$toolbar->appendButton('Custom', $download, 'download');
			}
		}

		// Add translate switcher
		$switcher = LayoutHelper::render('components.swjprojects.translate.switcher');
		$toolbar->appendButton('Custom', $switcher, 'translate-switcher');

		// Add support button
		$link    = 'https://www.septdir.com/support#solution=SWJProjects';
		$support = LayoutHelper::render('components.swjprojects.toolbar.link',
			array('link' => $link, 'text' => 'COM_SWJPROJECTS_SUPPORT', 'icon' => 'support', 'new' => true));
		$toolbar->appendButton('Custom', $support, 'support');

		// Add donate button
		$link   = 'https://www.septdir.com/donate#solution=swjprojects';
		$donate = LayoutHelper::render('components.swjprojects.toolbar.link',
			array('link' => $link, 'text' => 'COM_SWJPROJECTS_DONATE', 'icon' => 'heart', 'new' => true));
		$toolbar->appendButton('Custom', $donate, 'donate');

		// Add preview button
		if ($this->item->id)
		{
			// Preview button
			$link    = 'index.php?option=com_swjprojects&task=siteRedirect&page=version&debug=1&id=' . $this->item->id
				. '&project_id=' . $this->project->id . '&catid=' . $this->project->catid;
			$preview = LayoutHelper::render('components.swjprojects.toolbar.link',
				array('link' => $link, 'text' => 'JGLOBAL_PREVIEW', 'icon' => 'eye'));
			$toolbar->appendButton('Custom', $preview, 'preview');
		}
	}
}
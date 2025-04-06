<?php
/**
 * @package       SW JProjects
 * @version       2.4.0.1
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
 */

namespace Joomla\Component\SWJProjects\Administrator\View\Key;

defined('_JEXEC') or die;

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
	 * @since  1.3.0
	 */
	protected $state;

	/**
	 * Form object.
	 *
	 * @var  Form
	 *
	 * @since  1.3.0
	 */
	protected $form;

	/**
	 * Key object.
	 *
	 * @var  object
	 *
	 * @since  1.3.0
	 */
	protected $item;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @throws  \Exception
	 *
	 * @since  1.3.0
	 */
	public function display($tpl = null)
	{
		$this->state = $this->get('State');
		$this->form  = $this->get('Form');
		$this->item  = $this->get('Item');

		// Check for errors
		if (count($errors = $this->get('Errors')))
		{
			throw new \Exception(implode('\n', $errors), 500);
		}

		// Add title and toolbar
		$this->addToolbar();

		// Re-generate field
		if (empty($this->item->id))
		{
			$this->form->removeField('key_regenerate');
		}

		parent::display($tpl);
	}

	/**
	 * Add title and toolbar.
	 *
	 * @throws  \Exception
	 *
	 * @since  1.3.0
	 */
	protected function addToolbar()
	{
		$isNew   = ($this->item->id == 0);
		$canDo   = SWJProjectsHelper::getActions('com_swjprojects', 'key', $this->item->id);
		$toolbar = Toolbar::getInstance();

		// Disable menu
		Factory::getApplication()->input->set('hidemainmenu', true);

		// Set page title
		$title = ($isNew) ? Text::_('COM_SWJPROJECTS_KEY_ADD') : Text::_('COM_SWJPROJECTS_KEY_EDIT');
		ToolbarHelper::title(Text::_('COM_SWJPROJECTS') . ': ' . $title, 'cube');

		// Add apply & save buttons
		if ($canDo->get('core.edit'))
		{
			ToolbarHelper::apply('key.apply');
			ToolbarHelper::save('key.save');
		}

		// Add save new button
		if ($canDo->get('core.create'))
		{
			ToolbarHelper::save2new('key.save2new');
		}

		// Add cancel button
		ToolbarHelper::cancel('key.cancel', 'JTOOLBAR_CLOSE');

		// Add GitHub button
		$link   = 'https://github.com/WebTolk/SWJProjects';
		$github = LayoutHelper::render('components.swjprojects.toolbar.link',
			array('link' => $link, 'text' => 'GitHub', 'icon' => ' fab fa-github', 'new' => true));
		$toolbar->appendButton('Custom', $github, 'github');
	}
}

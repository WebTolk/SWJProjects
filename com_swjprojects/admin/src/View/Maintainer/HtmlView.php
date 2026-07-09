<?php
/**
 * @package       SW JProjects
 * @version       2.6.2
 * @Author        Sergey Tolkachyov
 * @copyright  Copyright (c) 2018 - 2026 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link          https://web-tolk.ru
 * @since         2.6.2
 */

namespace Joomla\Component\SWJProjects\Administrator\View\Maintainer;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\SWJProjects\Administrator\Helper\SWJProjectsHelper;
use function count;
use function implode;

class HtmlView extends BaseHtmlView
{
	/**
	 * Model state variables.
	 *
	 * @var  \Joomla\CMS\Object\CMSObject
	 *
	 * @since  2.6.2
	 */
	protected $state;

	/**
	 * Form object.
	 *
	 * @var  Form
	 *
	 * @since  2.6.2
	 */
	protected $form;

	/**
	 * Translates forms array.
	 *
	 * @var  array
	 *
	 * @since  2.6.2
	 */
	protected $translateForms;

	/**
	 * Maintainer object.
	 *
	 * @var  object
	 *
	 * @since  2.6.2
	 */
	protected $item;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse.
	 *
	 * @return  mixed
	 *
	 * @throws  \Exception
	 *
	 * @since  2.6.2
	 */
	public function display($tpl = null)
	{
		$model = $this->getModel();
		$this->state          = $model->getState();
		$this->form           = $model->getForm();
		$this->translateForms = $model->getTranslateForms();
		$this->item           = $model->getItem();

		if (count($errors = $model->getErrors()))
		{
			throw new \Exception(implode('\n', $errors), 500);
		}

		$this->addToolbar();
		$this->getDocument()->getWebAssetManager()->useScript('form.validate');

		parent::display($tpl);
	}

	/**
	 * Add title and toolbar.
	 *
	 * @throws  \Exception
	 *
	 * @since  2.6.2
	 */
	protected function addToolbar()
	{
		$isNew   = ($this->item->id == 0);
		$canDo   = SWJProjectsHelper::getActions('com_swjprojects', 'maintainer', $this->item->id);
		$toolbar = $this->getDocument()->getToolbar();

		Factory::getApplication()->getInput()->set('hidemainmenu', true);

		$title = $isNew ? Text::_('COM_SWJPROJECTS_MAINTAINER_ADD') : Text::_('COM_SWJPROJECTS_MAINTAINER_EDIT');
		ToolbarHelper::title(Text::_('COM_SWJPROJECTS') . ': ' . $title, 'cube');

		if ($canDo->get('core.edit'))
		{
			ToolbarHelper::apply('maintainer.apply');
			ToolbarHelper::save('maintainer.save');
		}

		if ($canDo->get('core.create'))
		{
			ToolbarHelper::save2new('maintainer.save2new');
		}

		ToolbarHelper::cancel('maintainer.cancel', 'JTOOLBAR_CLOSE');

		$switcher = LayoutHelper::render('components.swjprojects.translate.switcher');
		$toolbar->appendButton('Custom', $switcher, 'translate-switcher');

		$link   = 'https://github.com/WebTolk/SWJProjects';
		$github = LayoutHelper::render(
			'components.swjprojects.toolbar.link',
			['link' => $link, 'text' => 'GitHub', 'icon' => ' fab fa-github', 'new' => true]
		);
		$toolbar->appendButton('Custom', $github, 'github');
	}
}

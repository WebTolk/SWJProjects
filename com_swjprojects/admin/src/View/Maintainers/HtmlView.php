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

namespace Joomla\Component\SWJProjects\Administrator\View\Maintainers;

defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\SWJProjects\Administrator\Helper\SWJProjectsHelper;
use function count;
use function implode;

class HtmlView extends BaseHtmlView
{
	/**
	 * Form object for search filters.
	 *
	 * @var  Form
	 *
	 * @since  2.6.2
	 */
	public $filterForm;

	/**
	 * The active search filters.
	 *
	 * @var  array
	 *
	 * @since  2.6.2
	 */
	public $activeFilters;

	/**
	 * View sidebar.
	 *
	 * @var  string
	 *
	 * @since  2.6.2
	 */
	public $sidebar;

	/**
	 * Model state variables.
	 *
	 * @var  \Joomla\CMS\Object\CMSObject
	 *
	 * @since  2.6.2
	 */
	protected $state;

	/**
	 * Maintainers array.
	 *
	 * @var  array
	 *
	 * @since  2.6.2
	 */
	protected $items;

	/**
	 * Pagination object.
	 *
	 * @var  Pagination
	 *
	 * @since  2.6.2
	 */
	protected $pagination;

	/**
	 * Display the view.
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
		$this->state         = $model->getState();
		$this->items         = $model->getItems();
		$this->pagination    = $model->getPagination();
		$this->filterForm    = $model->getFilterForm();
		$this->activeFilters = $model->getActiveFilters();

		$this->addToolbar();

		if (count($errors = $model->getErrors()))
		{
			throw new \Exception(implode('\n', $errors), 500);
		}

		parent::display($tpl);
	}

	/**
	 * Add title and toolbar.
	 *
	 * @since  2.6.2
	 */
	protected function addToolbar()
	{
		$canDo   = SWJProjectsHelper::getActions('com_swjprojects', 'maintainers');
		$toolbar = $this->getDocument()->getToolbar();

		ToolbarHelper::title(Text::_('COM_SWJPROJECTS') . ': ' . Text::_('COM_SWJPROJECTS_MAINTAINERS'), 'cube');

		if ($canDo->get('core.create'))
		{
			ToolbarHelper::addNew('maintainer.add');
		}

		if ($canDo->get('core.edit.state'))
		{
			ToolbarHelper::publish('maintainers.publish', 'JTOOLBAR_PUBLISH', true);
			ToolbarHelper::unpublish('maintainers.unpublish', 'JTOOLBAR_UNPUBLISH', true);
		}

		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
		{
			ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'maintainers.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			ToolbarHelper::trash('maintainers.trash');
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			ToolbarHelper::preferences('com_swjprojects');
		}

		$link   = 'https://github.com/WebTolk/SWJProjects';
		$github = LayoutHelper::render(
			'components.swjprojects.toolbar.link',
			['link' => $link, 'text' => 'GitHub', 'icon' => ' fab fa-github', 'new' => true]
		);
		$toolbar->appendButton('Custom', $github, 'github');
	}

	/**
	 * Returns an array of fields the table can be sorted by.
	 *
	 * @return  array
	 *
	 * @since  2.6.2
	 */
	protected function getSortFields()
	{
		return [
			'm.state'       => Text::_('JSTATUS'),
			'm.id'          => Text::_('JGRID_HEADING_ID'),
			'title'         => Text::_('JGLOBAL_TITLE'),
			'projects_count' => Text::_('COM_SWJPROJECTS_MAINTAINER_PROJECTS_COUNT'),
			'm.ordering'    => Text::_('JGRID_HEADING_ORDERING'),
		];
	}
}

<?php
/*
 * @package    SW JProjects
 * @version    2.0.0-alpha3
 * @author     Sergey Tolkachyov
 * @сopyright  Copyright (c) 2018 - 2024 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
 */

namespace Joomla\Component\SWJProjects\Administrator\View\Keys;

defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Pagination\Pagination;
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
	 * @since  1.3.0
	 */
	protected $state;

	/**
	 * Keys array.
	 *
	 * @var  array
	 *
	 * @since  1.3.0
	 */
	protected $items;

	/**
	 * Pagination object.
	 *
	 * @var  Pagination
	 *
	 * @since  1.3.0
	 */
	protected $pagination;

	/**
	 * Form object for search filters.
	 *
	 * @var  Form
	 *
	 * @since  1.3.0
	 */
	public $filterForm;

	/**
	 * The active search filters.
	 *
	 * @var  array
	 *
	 * @since  1.3.0
	 */
	public $activeFilters;

	/**
	 * View sidebar.
	 *
	 * @var  string
	 *
	 * @since  1.3.0
	 */
	public $sidebar;

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse.
	 *
	 * @throws  \Exception
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since  1.3.0
	 */
	public function display($tpl = null)
	{
		$this->state         = $this->get('State');
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Add title and toolbar
		$this->addToolbar();

		// Check for errors
		if (count($errors = $this->get('Errors')))
		{
			throw new \Exception(implode('\n', $errors), 500);
		}

		parent::display($tpl);
	}

	/**
	 * Add title and toolbar.
	 *
	 * @since  1.3.0
	 */
	protected function addToolbar()
	{
		$canDo   = SWJProjectsHelper::getActions('com_swjprojects', 'keys');
		$toolbar = Toolbar::getInstance();

		// Set page title
		ToolbarHelper::title(Text::_('COM_SWJPROJECTS') . ': ' . Text::_('COM_SWJPROJECTS_KEYS'), 'cube');

		// Add create button
		if ($canDo->get('core.create'))
		{
			ToolbarHelper::addNew('key.add');
		}

		// Add publish & unpublish buttons
		if ($canDo->get('core.edit.state'))
		{
			ToolbarHelper::publish('keys.publish', 'JTOOLBAR_PUBLISH', true);
			ToolbarHelper::unpublish('keys.unpublish', 'JTOOLBAR_UNPUBLISH', true);
		}

		// Add delete/trash buttons
		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
		{
			ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'keys.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			ToolbarHelper::trash('keys.trash');
		}

		// Add preferences button
		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			ToolbarHelper::preferences('com_swjprojects');
		}

		// Add GitHub button
		$link = 'https://github.com/WebTolk/SWJProjects';
		$github = LayoutHelper::render('components.swjprojects.toolbar.link',
			array('link' => $link, 'text' => 'GitHub', 'icon' => ' fab fa-github', 'new' => true));
		$toolbar->appendButton('Custom', $github, 'github');
	}

	/**
	 * Returns an array of fields the table can be sorted by.
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value.
	 *
	 * @since  1.3.0
	 */
	protected function getSortFields()
	{
		return [
			'k.state'      => Text::_('JSTATUS'),
			'k.id'         => Text::_('JGRID_HEADING_ID'),
			'k.email'      => Text::_('JGLOBAL_EMAIL'),
			'k.date_start' => Text::_('COM_SWJPROJECTS_DATE_START'),
			'k.date_end'   => Text::_('COM_SWJPROJECTS_DATE_END'),
		];
	}
}

<?php
/**
 * @package    SW JProjects Component
 * @version    1.1.0
 * @author     Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2018 - 2019 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class SWJProjectsViewVersions extends HtmlView
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
	 * Versions array.
	 *
	 * @var  array
	 *
	 * @since  1.0.0
	 */
	protected $items;

	/**
	 * Pagination object.
	 *
	 * @var  \Joomla\CMS\Pagination\Pagination
	 *
	 * @since  1.0.0
	 */
	protected $pagination;

	/**
	 * Form object for search filters.
	 *
	 * @var  \Joomla\CMS\Form\Form
	 *
	 * @since  1.0.0
	 */
	public $filterForm;

	/**
	 * The active search filters.
	 *
	 * @var  array
	 *
	 * @since  1.0.0
	 */
	public $activeFilters;

	/**
	 * View sidebar.
	 *
	 * @var  string
	 *
	 * @since  1.0.0
	 */
	public $sidebar;

	/**
	 * Display the view.
	 *
	 * @param  string $tpl The name of the template file to parse.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @throws  Exception
	 *
	 * @since  1.0.0
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

		// Prepare sidebar
		SWJProjectsHelper::addSubmenu('versions');
		$this->sidebar = JHtmlSidebar::render();

		// Check for errors
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode('\n', $errors), 500);
		}

		return parent::display($tpl);
	}

	/**
	 * Add title and toolbar.
	 *
	 * @since  1.0.0
	 */
	protected function addToolbar()
	{
		$canDo = SWJProjectsHelper::getActions('com_swjprojects', 'versions');

		// Set page title
		ToolbarHelper::title(Text::_('COM_SWJPROJECTS') . ': ' . Text::_('COM_SWJPROJECTS_VERSIONS'), 'cube');

		// Add create button
		if ($canDo->get('core.create'))
		{
			ToolbarHelper::addNew('version.add');
		}

		// Add publish & unpublish buttons
		if ($canDo->get('core.edit.state'))
		{
			ToolbarHelper::publish('versions.publish', 'JTOOLBAR_PUBLISH', true);
			ToolbarHelper::unpublish('versions.unpublish', 'JTOOLBAR_UNPUBLISH', true);
		}

		// Add delete/trash buttons
		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
		{
			ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'versions.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			ToolbarHelper::trash('versions.trash');
		}

		// Add preferences button
		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			ToolbarHelper::preferences('com_swjprojects');
		}
	}

	/**
	 * Returns an array of fields the table can be sorted by.
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value.
	 *
	 * @since  1.0.0
	 */
	protected function getSortFields()
	{
		return [
			'v.state'     => Text::_('JSTATUS'),
			'v.id'        => Text::_('JGRID_HEADING_ID'),
			'title'       => Text::_('JGLOBAL_TITLE'),
			'p.element'   => Text::_('COM_SWJPROJECTS_ELEMENT'),
			'v.version'   => Text::_('COM_SWJPROJECTS_VERSION'),
			'v.tag'       => Text::_('COM_SWJPROJECTS_VERSION_TAG'),
			'v.downloads' => Text::_('COM_SWJPROJECTS_STATISTICS_DOWNLOADS')
		];
	}
}
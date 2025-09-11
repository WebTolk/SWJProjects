<?php
/**
 * @package       SW JProjects
 * @version       2.5.0
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
 */

namespace Joomla\Component\SWJProjects\Site\View\Userkeys;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Menu\MenuItem;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use function count;
use function defined;
use function htmlspecialchars;
use function implode;

defined('_JEXEC') or die;

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
	 * Application params.
	 *
	 * @var  Registry;
	 *
	 * @since  1.4.0
	 */
	public $params;

	/**
	 * Documents array.
	 *
	 * @var  array
	 *
	 * @since  1.4.0
	 */
	protected $items;

	/**
	 * Pagination object.
	 *
	 * @var  Pagination
	 *
	 * @since  1.4.0
	 */
	protected $pagination;

	/**
	 * Active menu item.
	 *
	 * @var  MenuItem
	 *
	 * @since  1.4.0
	 */
	protected $menu;

	/**
	 * Page class suffix from params.
	 *
	 * @var  string
	 *
	 * @since  1.4.0
	 */
	public $pageclass_sfx;

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse.
	 *
	 * @throws  \Exception
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since  1.4.0
	 */
	public function display($tpl = null)
	{
		$app           = Factory::getApplication();
		$user = $app->getIdentity();

		if($user->guest)
		{
			$this->setLayout('guestuser');
			parent::display($tpl);
			return $this;
		}

		$this->state      = $this->get('State');
		$this->params     = $this->state->get('params');
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->menu       = $app->getMenu()->getActive();

		// Check for errors
		if (count($errors = $this->get('Errors')))
		{
			throw new \Exception(implode('\n', $errors), 500);
		}

		// Check to see which parameters should take priority
		$menu = $this->menu;

		if ($menu
			&& $menu->query['option'] == 'com_swjprojects'
			&& $menu->query['view'] == 'userkeys')
		{
			if (isset($menu->query['layout']))
			{
				$this->setLayout($menu->query['layout']);
			}
			elseif ($layout = $this->params->get('userkeys_layout'))
			{
				$this->setLayout($layout);
			}

		}

		// Escape strings for html output
		$this->pageclass_sfx = (!empty($this->params->get('pageclass_sfx')) ? htmlspecialchars($this->params->get('pageclass_sfx')) : '');

		// Prepare the document
		$this->_prepareDocument();

		parent::display($tpl);
		return $this;
	}

	/**
	 * Prepare the document.
	 *
	 * @throws  \Exception
	 *
	 * @since  1.4.0
	 */
	protected function _prepareDocument()
	{
		$app     = Factory::getApplication();
		$menu    = $this->menu;
		$doc     = $this->getDocument();
		$current = ($menu && $menu->query['option'] === 'com_swjprojects'
			&& $menu->query['view'] === 'userkeys');

		// Set meta title
		$title = Text::_('COM_SWJPROJECTS_USER_KEYS');

		$sitename = $app->get('sitename');
		if ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = Text::sprintf('JPAGETITLE', $sitename, $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = Text::sprintf('JPAGETITLE', $title, $sitename);
		}
		$doc->setTitle($title);

		// Set meta description
		if ($current && $this->params->get('menu-meta_description'))
		{
			$doc->setDescription($this->params->get('menu-meta_description'));
		}

		// Set meta image
		if ($current && $this->params->get('menu-meta_image'))
		{
			$doc->setMetadata('image', Uri::root() . $this->params->get('menu-meta_image'));
		}

		// Set meta robots
		$doc->setMetadata('robots', 'noindex');
	}

}

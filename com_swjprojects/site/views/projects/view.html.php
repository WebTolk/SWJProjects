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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Menu\MenuItem;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

class SWJProjectsViewProjects extends HtmlView
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
	 * Application params.
	 *
	 * @var  Registry;
	 *
	 * @since  1.0.0
	 */
	public $params;

	/**
	 * Projects array.
	 *
	 * @var  array
	 *
	 * @since  1.0.0
	 */
	protected $items;

	/**
	 * Pagination object.
	 *
	 * @var  Pagination
	 *
	 * @since  1.0.0
	 */
	protected $pagination;

	/**
	 * Category object.
	 *
	 * @var  object|false
	 *
	 * @since  1.0.0
	 */
	protected $category;

	/**
	 * Active menu item.
	 *
	 * @var  MenuItem
	 *
	 * @since  1.0.0
	 */
	protected $menu;

	/**
	 * Page class suffix from params.
	 *
	 * @var  string
	 *
	 * @since  1.0.0
	 */
	public $pageclass_sfx;

	/**
	 * Display the view.
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
		$this->state      = $this->get('State');
		$this->params     = $this->state->get('params');
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->category   = $this->get('Item');
		$this->menu       = Factory::getApplication()->getMenu()->getActive();

		// Check for errors
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode('\n', $errors), 500);
		}

		// Create a shortcut for item
		$category = $this->category;

		// Check to see which parameters should take priority
		$temp = clone $this->params;
		$menu = $this->menu;

		if ($menu
			&& $menu->query['option'] == 'com_swjprojects'
			&& $menu->query['view'] == 'projects'
			&& @$menu->query['id'] == $category->id)
		{
			if (isset($menu->query['layout']))
			{
				$this->setLayout($menu->query['layout']);
			}
			elseif ($layout = $category->params->get('projects_layout'))
			{
				$this->setLayout($layout);
			}

			$category->params->merge($temp);
		}
		else
		{
			$temp->merge($category->params);
			$category->params = $temp;

			if ($layout = $category->params->get('projects_layout'))
			{
				$this->setLayout($layout);
			}
		}
		$this->params = $category->params;

		// Escape strings for html output
		$this->pageclass_sfx = (!empty($this->params->get('pageclass_sfx')) ? htmlspecialchars($this->params->get('pageclass_sfx')) : '');

		// Prepare the document
		$this->_prepareDocument();

		return parent::display($tpl);
	}

	/**
	 * Prepare the document.
	 *
	 * @throws  Exception
	 *
	 * @since  1.0.0
	 */
	protected function _prepareDocument()
	{
		$app      = Factory::getApplication();
		$category = $this->category;
		$menu     = $this->menu;
		$current  = ($menu && $menu->query['option'] === 'com_swjprojects'
			&& $menu->query['view'] === 'projects'
			&& (int) @$menu->query['id'] === (int) $category->id);

		// Add category pathway item if no current menu
		if ($menu && !$current)
		{
			$paths = array(array('title' => $category->title, 'link' => ''));

			// Add parent categories pathway item if no current menu
			$parent = $this->getModel()->getCategoryParent($category->id);
			while ($parent && $parent->id > 1
				&& ($menu->query['option'] !== 'com_swjprojects'
					|| $menu->query['view'] !== 'projects'
					|| (int) @$menu->query['id'] !== (int) $parent->id))
			{
				$paths[] = array('title' => $parent->title, 'link' => $parent->link);
				$parent  = $this->getModel()->getCategoryParent($parent->id);
			}

			// Add pathway items
			$pathway = $app->getPathway();
			foreach (array_reverse($paths) as $path)
			{
				$pathway->addItem($path['title'], $path['link']);
			}
		}

		// Set meta title
		$title = ($category->id > 0) ? $category->title : $this->params->get('page_title');
		if ($current && $this->params->get('page_title'))
		{
			$title = $this->params->get('page_title');
		}
		elseif ($category->metadata->get('title'))
		{
			$title = $category->metadata->get('title');
		};
		$sitename = $app->get('sitename');
		if ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = Text::sprintf('JPAGETITLE', $sitename, $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = Text::sprintf('JPAGETITLE', $title, $sitename);
		}
		$this->document->setTitle($title);

		// Set meta description
		if ($current && $this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}
		elseif ($category->metadata->get('description'))
		{
			$this->document->setDescription($category->metadata->get('description'));
		}
		elseif (!empty($category->description))
		{
			$this->document->setDescription(JHtmlString::truncate($category->description, 150, false, false));
		}

		// Set meta keywords
		if ($current && $this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}
		elseif ($category->metadata->get('keywords'))
		{
			$this->document->setMetadata('keywords', $category->metadata->get('keywords'));
		}

		// Set meta image
		if ($current && $this->params->get('menu-meta_image'))
		{
			$this->document->setMetadata('image', Uri::root() . $this->params->get('menu-meta_image'));
		}
		elseif ($category->metadata->get('image'))
		{
			$this->document->setMetadata('image', Uri::root() . $category->metadata->get('image'));
		}

		// Set meta robots
		if ($this->state->get('debug', 0))
		{
			$this->document->setMetadata('robots', 'noindex');
		}
		elseif ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
		elseif ($category->metadata->get('robots'))
		{
			$this->document->setMetadata('robots', $category->metadata->get('robots'));
		}

		// Set meta url
		$url = Uri::getInstance()->toString(array('scheme', 'host', 'port')) . $category->link;
		$this->document->setMetaData('url', $url);

		// Set meta twitter
		$this->document->setMetaData('twitter:card', 'summary_large_image');
		$this->document->setMetaData('twitter:site', $sitename);
		$this->document->setMetaData('twitter:creator', $sitename);
		$this->document->setMetaData('twitter:title', $title);
		$this->document->setMetaData('twitter:url', $url);
		if ($description = $this->document->getMetaData('description'))
		{
			$this->document->setMetaData('twitter:description', $description);
		}
		if ($image = $this->document->getMetaData('image'))
		{
			$this->document->setMetaData('twitter:image', $image);
		}

		// Set meta open graph
		$this->document->setMetadata('og:type', 'website', 'property');
		$this->document->setMetaData('og:site_name', $sitename, 'property');
		$this->document->setMetaData('og:title', $title, 'property');
		$this->document->setMetaData('og:url', $url, 'property');
		if ($description)
		{
			$this->document->setMetaData('og:description', $description, 'property');
		}
		if ($image)
		{
			$this->document->setMetaData('og:image', $image, 'property');
		}
	}
}

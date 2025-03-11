<?php
/*
 * @package    SW JProjects
 * @version    2.4.0
 * @author     Sergey Tolkachyov
 * @сopyright  Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
 */

namespace Joomla\Component\SWJProjects\Site\View\Projects;

defined('_JEXEC') or die;

use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\Helpers\StringHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Menu\MenuItem;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use function array_reverse;
use function count;
use function defined;
use function htmlspecialchars;
use function implode;
use function trim;

class HtmlView extends BaseHtmlView
{
	/**
	 * Application params.
	 *
	 * @var  Registry;
	 *
	 * @since  1.0.0
	 */
	public $params;
	/**
	 * Page class suffix from params.
	 *
	 * @var  string
	 *
	 * @since  1.0.0
	 */
	public $pageclass_sfx;
	/**
	 * Model state variables.
	 *
	 * @var  Joomla\CMS\Object\CMSObject
	 *
	 * @since  1.0.0
	 */
	protected $state;
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
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @throws  \Exception
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
			throw new \Exception(implode('\n', $errors), 500);
		}

		// Create a shortcut for item
		$category = &$this->category;

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

		$app = Factory::getApplication();
		$dispatcher = $this->getDispatcher();

		$offset     = $app->getInput()->getUInt('limitstart');

		foreach ($this->items as &$item)
		{

			// Extra content from events for each project
			$item->event   = new \stdClass();
			$item->params = $this->params;
			$contentEventArguments = [
				'context' => 'com_swjprojects.projects.project',
				'subject' => $item,
				'params'  => $item->params,
				'page'    => $offset,
			];

			$contentEvents = [
				'afterDisplayTitle'    => AbstractEvent::create('onContentAfterTitle', $contentEventArguments),
				'beforeDisplayContent' => AbstractEvent::create('onContentBeforeDisplay', $contentEventArguments),
				'afterDisplayContent'  => AbstractEvent::create('onContentAfterDisplay', $contentEventArguments),
				'beforeProjectButtons'  => AbstractEvent::create('beforeProjectButtons', $contentEventArguments),
				'afterProjectButtons'  => AbstractEvent::create('afterProjectButtons', $contentEventArguments),
			];

			foreach ($contentEvents as $resultKey => $event) {
				$results = $dispatcher->dispatch($event->getName(), $event)->getArgument('result', []);

				$item->event->{$resultKey} = $results ? trim(implode("\n", $results)) : '';
			}
		}

		// Extra content from events FOR CATEGORY
		$category->event   = new \stdClass();

		$contentEventArguments = [
			'context' => 'com_swjprojects.projects',
			'subject' => $category,
			'params'  => $category->params,
			'page'    => $offset,
		];

		$contentEvents = [
			'afterDisplayProjectsTitle'    => AbstractEvent::create('afterDisplayProjectsTitle', $contentEventArguments),
			'beforeDisplayProjectsContent' => AbstractEvent::create('beforeDisplayProjectsContent', $contentEventArguments),
			'afterDisplayProjectsContent'  => AbstractEvent::create('afterDisplayProjectsContent', $contentEventArguments),
		];

		foreach ($contentEvents as $resultKey => $event) {
			$results = $dispatcher->dispatch($event->getName(), $event)->getArgument('result', []);

			$category->event->{$resultKey} = $results ? trim(implode("\n", $results)) : '';
		}

		// Escape strings for html output
		$this->pageclass_sfx = (!empty($this->params->get('pageclass_sfx')) ? htmlspecialchars($this->params->get('pageclass_sfx')) : '');

		// Prepare the document
		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepare the document.
	 *
	 * @throws  \Exception
	 *
	 * @since  1.0.0
	 */
	protected function _prepareDocument()
	{
		$app      = Factory::getApplication();
		$category = $this->category;
		$menu     = $this->menu;
		$doc      = $this->getDocument();
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
		$doc->setTitle($title);

		// Set meta description
		if ($current && $this->params->get('menu-meta_description'))
		{
			$doc->setDescription($this->params->get('menu-meta_description'));
		}
		elseif ($category->metadata->get('description'))
		{
			$doc->setDescription($category->metadata->get('description'));
		}
		elseif (!empty($category->description))
		{
			$doc->setDescription(StringHelper::truncate($category->description, 150, false, false));
		}

		// Set meta keywords
		if ($current && $this->params->get('menu-meta_keywords'))
		{
			$doc->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}
		elseif ($category->metadata->get('keywords'))
		{
			$doc->setMetadata('keywords', $category->metadata->get('keywords'));
		}

		// Set meta image
		if ($current && $this->params->get('menu-meta_image'))
		{
			$doc->setMetadata('image', Uri::root() . $this->params->get('menu-meta_image'));
		}
		elseif ($category->metadata->get('image'))
		{
			$doc->setMetadata('image', Uri::root() . $category->metadata->get('image'));
		}

		// Set meta robots
		if ($this->state->get('debug', 0))
		{
			$doc->setMetadata('robots', 'noindex');
		}
		elseif ($this->params->get('robots'))
		{
			$doc->setMetadata('robots', $this->params->get('robots'));
		}
		elseif ($category->metadata->get('robots'))
		{
			$doc->setMetadata('robots', $category->metadata->get('robots'));
		}

		// Set meta url
		$url = Uri::getInstance()->toString(array('scheme', 'host', 'port')) . $category->link;
		$doc->setMetaData('url', $url);

		// Set meta twitter
		$doc->setMetaData('twitter:card', 'summary_large_image');
		$doc->setMetaData('twitter:site', $sitename);
		$doc->setMetaData('twitter:creator', $sitename);
		$doc->setMetaData('twitter:title', $title);
		$doc->setMetaData('twitter:url', $url);
		if ($description = $doc->getMetaData('description'))
		{
			$doc->setMetaData('twitter:description', $description);
		}
		if ($image = $doc->getMetaData('image'))
		{
			$doc->setMetaData('twitter:image', $image);
		}

		// Set meta open graph
		$doc->setMetadata('og:type', 'website', 'property');
		$doc->setMetaData('og:site_name', $sitename, 'property');
		$doc->setMetaData('og:title', $title, 'property');
		$doc->setMetaData('og:url', $url, 'property');
		if ($description)
		{
			$doc->setMetaData('og:description', $description, 'property');
		}
		if ($image)
		{
			$doc->setMetaData('og:image', $image, 'property');
		}
	}
}

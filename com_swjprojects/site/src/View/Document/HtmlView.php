<?php
/*
 * @package    SW JProjects
 * @version    2.1.0.1
 * @author     Sergey Tolkachyov
 * @сopyright  Copyright (c) 2018 - 2024 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
 */

namespace Joomla\Component\SWJProjects\Site\View\Document;

defined('_JEXEC') or die;

use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\Helpers\StringHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Menu\MenuItem;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

class HtmlView extends BaseHtmlView
{
	/**
	 * Application params.
	 *
	 * @var  Registry;
	 *
	 * @since  1.4.0
	 */
	public $params;
	/**
	 * Page class suffix from params.
	 *
	 * @var  string
	 *
	 * @since  1.4.0
	 */
	public $pageclass_sfx;
	/**
	 * Model state variables.
	 *
	 * @var  Joomla\CMS\Object\CMSObject
	 *
	 * @since  1.4.0
	 */
	protected $state;
	/**
	 * Document object.
	 *
	 * @var  object|false
	 *
	 * @since  1.4.0
	 */
	protected $item;
	/**
	 * Project object.
	 *
	 * @var  object|false
	 *
	 * @since  1.4.0
	 */
	protected $project;
	/**
	 * Category object.
	 *
	 * @var  object|false
	 *
	 * @since  1.4.0
	 */
	protected $category;
	/**
	 * Active menu item.
	 *
	 * @var  MenuItem
	 *
	 * @since  1.4.0
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
	 * @since  1.4.0
	 */
	public function display($tpl = null)
	{
		$this->state    = $this->get('State');
		$this->params   = $this->state->get('params');
		$this->item     = $this->get('Item');
		$this->documentation_items     = $this->item->documentation_items;
		$this->project  = $this->item->project;
		$this->category = $this->item->category;
		$this->menu     = Factory::getApplication()->getMenu()->getActive();

		// Check for errors
		if (count($errors = $this->get('Errors')))
		{
			throw new \Exception(implode('\n', $errors), 500);
		}

		// Create a shortcut for item
		$item = &$this->item;

		// Check to see which parameters should take priority
		$temp = clone $this->params;
		$menu = $this->menu;

		if ($menu
			&& $menu->query['option'] == 'com_swjprojects'
			&& $menu->query['view'] == 'document'
			&& @$menu->query['id'] == $item->id)
		{
			if (isset($menu->query['layout']))
			{
				$this->setLayout($menu->query['layout']);
			}
			elseif ($layout = $item->params->get('document_layout'))
			{
				$this->setLayout($layout);
			}

			$item->params->merge($temp);
		}
		else
		{
			$temp->merge($item->params);
			$item->params = $temp;

			if ($layout = $item->params->get('document_layout'))
			{
				$this->setLayout($layout);
			}
		}
		$this->params = $item->params;

		$dispatcher = $this->getDispatcher();
		// Process the content plugins
		PluginHelper::importPlugin('content', null, true, $dispatcher);
		$item->text = &$item->fulltext;

		// Extra content from events for document
		$item->event   = new \stdClass();

		$contentEventArguments = [
			'context' => 'com_swjprojects.document',
			'subject' => $item,
			'params'  => $item->params,
			'page'    => 0,
		];

		$contentEvents = [
			'afterDisplayTitle'    => AbstractEvent::create('onContentAfterTitle', $contentEventArguments),
			'beforeDisplayContent' => AbstractEvent::create('onContentBeforeDisplay', $contentEventArguments),
			'afterDisplayContent'  => AbstractEvent::create('onContentAfterDisplay', $contentEventArguments),
			'beforeProjectButtons'  => AbstractEvent::create('beforeProjectButtons', $contentEventArguments),
			'afterProjectButtons'  => AbstractEvent::create('afterProjectButtons', $contentEventArguments),
			'onContentPrepare'  => AbstractEvent::create('onContentPrepare', $contentEventArguments),
		];

		foreach ($contentEvents as $resultKey => $event) {
			$results = $dispatcher->dispatch($event->getName(), $event)->getArgument('result', []);

			$item->event->{$resultKey} = $results ? trim(implode("\n", $results)) : '';
		}


		// Extra content from events for project
		$project = &$this->project;
		$project->event   = new \stdClass();

		$contentEventArguments = [
			'context' => 'com_swjprojects.document',
			'subject' => $project,
			'params'  => $this->params,
			'page'    => 0,
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

			$project->event->{$resultKey} = $results ? trim(implode("\n", $results)) : '';
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
	 * @since  1.4.0
	 */
	protected function _prepareDocument()
	{
		$app     = Factory::getApplication();
		$item    = $this->item;
		$menu    = $this->menu;
		$doc     = $this->getDocument();
		$current = ($menu && $menu->query['option'] === 'com_swjprojects'
			&& $menu->query['view'] === 'document' &&
			(int) @$menu->query['id'] === (int) $item->id);

		// Add document pathway item if no current menu
		if ($menu && !$current)
		{
			$paths = array(array('title' => $item->title, 'link' => ''));

			// Add documentation pathway item if no current menu
			$project = $this->project;
			if ($menu->query['option'] !== 'com_swjprojects'
				|| $menu->query['view'] !== 'documentation'
				|| (int) @$menu->query['project_id'] !== (int) $project->id)
			{
				$paths[] = array('title' => Text::_('COM_SWJPROJECTS_DOCUMENTATION'), 'link' => $project->documentation);

				// Add project pathway item if no current menu
				if ($menu->query['option'] !== 'com_swjprojects'
					|| $menu->query['view'] !== 'project'
					|| (int) @$menu->query['id'] !== (int) $project->id)
				{
					$paths[] = ['title' => $project->title, 'link' => $project->link];

					// Add categories pathway item if no current menu
					$category = $this->category;
					while ($category && $category->id > 1
						&& ($menu->query['option'] !== 'com_swjprojects'
							|| $menu->query['view'] !== 'projects'
							|| (int) @$menu->query['id'] !== (int) $category->id))
					{
						$paths[]  = ['title' => $category->title, 'link' => $category->link];
						$category = $this->getModel()->getCategoryParent($category->id);
					}
				}
			}

			// Add pathway items
			$pathway = $app->getPathway();
			foreach (array_reverse($paths) as $path)
			{
				$pathway->addItem($path['title'], $path['link']);
			}
		}

		// Set meta title
		$title = $item->title;
		if ($current && $this->params->get('page_title'))
		{
			$title = $this->params->get('page_title');
		}
		elseif ($item->metadata->get('title'))
		{
			$title = $item->metadata->get('title');
		}
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
		elseif ($item->metadata->get('description'))
		{
			$doc->setDescription($item->metadata->get('description'));
		}
		elseif (!empty($item->introtext))
		{
			$doc->setDescription(StringHelper::truncate($item->introtext, 150, false, false));
		}

		// Set meta keywords
		if ($current && $this->params->get('menu-meta_keywords'))
		{
			$doc->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}
		elseif ($item->metadata->get('keywords'))
		{
			$doc->setMetadata('keywords', $item->metadata->get('keywords'));
		}

		// Set meta image
		if ($current && $this->params->get('menu-meta_image'))
		{
			$doc->setMetadata('image', Uri::root() . $this->params->get('menu-meta_image'));
		}
		elseif ($item->metadata->get('image'))
		{
			$doc->setMetadata('image', Uri::root() . $item->metadata->get('image'));
		}
		elseif (!empty($this->project->images->get('cover')))
		{
			$doc->setMetadata('image', Uri::root() . $this->project->images->get('cover'));
		}
		elseif (!empty($this->project->images->get('icon')))
		{
			$doc->setMetadata('image', Uri::root() . $this->project->images->get('icon'));
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
		elseif ($item->metadata->get('robots'))
		{
			$doc->setMetadata('robots', $item->metadata->get('robots'));
		}

		// Set meta url
		$url = Uri::getInstance()->toString(array('scheme', 'host', 'port')) . $item->link;
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

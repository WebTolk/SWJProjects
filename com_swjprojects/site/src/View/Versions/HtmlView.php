<?php
/*
 * @package    SW JProjects
 * @version    2.1.2
 * @author     Sergey Tolkachyov
 * @сopyright  Copyright (c) 2018 - 2024 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
 */

namespace Joomla\Component\SWJProjects\Site\View\Versions;

defined('_JEXEC') or die;

use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\Helpers\StringHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Menu\MenuItem;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

class HtmlView extends BaseHtmlView
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
	 * @var  Pagination
	 *
	 * @since  1.0.0
	 */
	protected $pagination;

	/**
	 * Project object.
	 *
	 * @var  object|false
	 *
	 * @since  1.0.0
	 */
	protected $project;

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
	 * @throws  \Exception
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since  1.0.0
	 */
	public function display($tpl = null)
	{
		$app = Factory::getApplication();
		$this->state      = $this->get('State');
		$this->params     = $this->state->get('params');
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		// Flag indicates to not add limitstart=0 to URL
		$this->pagination->hideEmptyLimitstart = true;
		// Add additional parameters
		$queryParameterList = [
			'catid'      => 'int',
			'project_id' => 'int',
			'language'   => 'string',
		];

		foreach ($queryParameterList as $parameter => $filter)
		{
			$value = $app->getInput()->get($parameter, null, $filter);

			if (is_null($value))
			{
				continue;
			}

			$this->pagination->setAdditionalUrlParam($parameter, $value);
		}

		$this->project    = $this->get('Item');
		$this->category   = $this->project->category;
		$this->menu       = $app->getMenu()->getActive();

		// Check for errors
		if (count($errors = $this->get('Errors')))
		{
			throw new \Exception(implode('\n', $errors), 500);
		}

		// Create a shortcut for item
		$project = &$this->project;

		// Check to see which parameters should take priority
		$temp = clone $this->params;
		$menu = $this->menu;

		if ($menu
			&& $menu->query['option'] == 'com_swjprojects'
			&& $menu->query['view'] == 'versions'
			&& @$menu->query['id'] == $project->id)
		{
			if (isset($menu->query['layout']))
			{
				$this->setLayout($menu->query['layout']);
			}
			elseif ($layout = $project->params->get('versions_layout'))
			{
				$this->setLayout($layout);
			}

			$project->params->merge($temp);
		}
		else
		{
			$temp->merge($project->params);
			$project->params = $temp;

			if ($layout = $project->params->get('versions_layout'))
			{
				$this->setLayout($layout);
			}
		}
		$this->params = $project->params;
		$app           = Factory::getApplication();
		$offset        = $app->getInput()->getUInt('limitstart');
		$dispatcher = $this->getDispatcher();
		// Extra content from events
		$project->event   = new \stdClass();

		$contentEventArguments = [
			'context' => 'com_swjprojects.versions.project',
			'subject' => $project,
			'params'  => $project->params,
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
	 * @since  1.0.0
	 */
	protected function _prepareDocument()
	{
		$app     = Factory::getApplication();
		$project = $this->project;
		$menu    = $this->menu;
		$doc = $this->getDocument();
		$current = ($menu
			&& $menu->query['option'] === 'com_swjprojects'
			&& $menu->query['view'] === 'versions'
			&& (int) @$menu->query['id'] === (int) $project->id);

		// Add versions pathway item if no current menu
		if ($menu && !$current)
		{
			$paths = array(array('title' => Text::_('COM_SWJPROJECTS_VERSIONS'), 'link' => ''));

			// Add project pathway item if no current menu
			if ($menu->query['option'] !== 'com_swjprojects'
				|| $menu->query['view'] !== 'project'
				|| (int) @$menu->query['id'] !== (int) $project->id)
			{
				$paths[] = array('title' => $project->title, 'link' => $project->link);

				// Add categories pathway item if no current menu
				$category = $this->category;
				while ($category && $category->id > 1
					&& ($menu->query['option'] !== 'com_swjprojects'
						|| $menu->query['view'] !== 'projects'
						|| (int) @$menu->query['id'] !== (int) $category->id))
				{
					$paths[]  = array('title' => $category->title, 'link' => $category->link);
					$category = $this->getModel()->getCategoryParent($category->id);
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
		$title = Text::sprintf('COM_SWJPROJECTS_VERSIONS_TITLE', $project->title);
		if ($current && $this->params->get('page_title'))
		{
			$title = $this->params->get('page_title');
		}
		elseif ($project->metadata->get('versions_title'))
		{
			$title = $project->metadata->get('versions_title');
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
		elseif ($project->metadata->get('versions_description'))
		{
			$doc->setDescription($project->metadata->get('versions_description'));
		}
		elseif (!empty($project->introtext))
		{
			$doc->setDescription(StringHelper::truncate($project->introtext, 150, false, false));
		}

		// Set meta keywords
		if ($current && $this->params->get('menu-meta_keywords'))
		{
			$doc->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}
		elseif ($project->metadata->get('versions_keywords'))
		{
			$doc->setMetadata('keywords', $project->metadata->get('versions_keywords'));
		}

		// Set meta image
		if ($current && $this->params->get('menu-meta_image'))
		{
			$doc->setMetadata('image', Uri::root() . $this->params->get('menu-meta_image'));
		}
		elseif ($project->metadata->get('versions_image'))
		{
			$doc->setMetadata('image', Uri::root() . $project->metadata->get('versions_image'));
		}
		elseif (!empty($project->images->get('cover')))
		{
			$doc->setMetadata('image', Uri::root() . $project->images->get('cover'));
		}
		elseif (!empty($project->images->get('icon')))
		{
			$doc->setMetadata('image', Uri::root() . $project->images->get('icon'));
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
		elseif ($project->metadata->get('versions_robots'))
		{
			$doc->setMetadata('robots', $project->metadata->get('versions_robots'));
		}

		// Set meta url
		$url = Uri::getInstance()->toString(array('scheme', 'host', 'port')) . $project->versions;
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

<?php
/**
 * @package       SW JProjects
 * @version       2.6.1-dev
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
 */

namespace Joomla\Component\SWJProjects\Site\View\Project;

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
use function array_reverse;
use function count;
use function defined;
use function htmlspecialchars;
use function implode;
use function trim;

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
	 * Relations array.
	 *
	 * @var  array|false
	 *
	 * @since  1.1.0
	 */
	protected $relations;

	/**
	 * Last version object.
	 *
	 * @var  array|false
	 *
	 * @since  1.3.0
	 */
	protected $version;

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
        $app           = Factory::getApplication();
        $model = $this->getModel();
		$this->state     = $model->getState();
		$this->params    = $this->state->get('params');
		$this->project   = $model->getItem();
		$this->category  = $this->project->category;
		$this->relations = $model->getRelations();
		$this->version   = $model->getVersion();
		$this->menu      = $app->getMenu()->getActive();

		// Check for errors
		if (count($errors = $model->getErrors()))
		{
			throw new \Exception(implode('\n', $errors), 500);
		}

		// Create a shortcut for item
		$project = $this->project;

		// Check to see which parameters should take priority
		$temp = clone $this->params;
		$menu = $this->menu;

		if ($menu
			&& $menu->query['option'] == 'com_swjprojects'
			&& $menu->query['view'] == 'project'
			&& @$menu->query['id'] == $project->id)
		{
			if (isset($menu->query['layout']))
			{
				$this->setLayout($menu->query['layout']);
			}
			elseif ($layout = $project->params->get('project_layout'))
			{
				$this->setLayout($layout);
			}

			$project->params->merge($temp);
		}
		else
		{
			$temp->merge($project->params);
			$project->params = $temp;

			if ($layout = $project->params->get('project_layout'))
			{
				$this->setLayout($layout);
			}
		}
		$this->params = $project->params;

		// Process the content plugins
		PluginHelper::importPlugin('content');

		$offset        = $app->getInput()->getUInt('limitstart');
		$project->text = &$project->fulltext;

		$dispatcher = $this->getDispatcher();
		// Extra content from events
		$project->event   = new \stdClass();

		$contentEventArguments = [
			'context' => 'com_swjprojects.project',
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
			'onContentPrepare'  => AbstractEvent::create('onContentPrepare', $contentEventArguments),
		];

		foreach ($contentEvents as $resultKey => $event) {
			$results = $dispatcher->dispatch($event->getName(), $event)->getArgument('result', []);

			$project->event->{$resultKey} = $results ? trim(implode("\n", $results)) : '';

		}

		// Escape strings for html output
		$this->pageclass_sfx = (!empty($this->params->get('pageclass_sfx')) ? htmlspecialchars($this->params->get('pageclass_sfx')) : '');

		// Prepare the document
		$this->_prepareDocument();

		// Update hist counter
		$this->getModel()->hit();

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
			&& $menu->query['view'] === 'project'
			&& (int) @$menu->query['id'] === (int) $project->id);

		// Add project pathway item if no current menu
		if ($menu && !$current)
		{
			$paths = [['title' => $project->title, 'link' => '']];

			// Add categories pathway item if no current menu
			$category = $this->category;
			while ($category
				&& $category->id > 1
				&& ($menu->query['option'] !== 'com_swjprojects'
					|| $menu->query['view'] !== 'projects'
					|| (int) @$menu->query['id'] !== (int) $category->id))
			{
				$paths[]  = ['title' => $category->title, 'link' => $category->link];
				$category = $this->getModel()->getCategoryParent($category->id);
			}

			// Add pathway items
			$pathway = $app->getPathway();
			foreach (array_reverse($paths) as $path)
			{
				$pathway->addItem($path['title'], $path['link']);
			}
		}

		// Set meta title
		$title    = $project->title;
		if ($current && $this->params->get('page_title'))
		{
			$title = $this->params->get('page_title');
		}
		elseif ($project->metadata->get('title'))
		{
			$title = $project->metadata->get('title');
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
		elseif ($project->metadata->get('description'))
		{
			$doc->setDescription($project->metadata->get('description'));
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
		elseif ($project->metadata->get('keywords'))
		{
			$doc->setMetadata('keywords', $project->metadata->get('keywords'));
		}

		// Set meta image
		if ($current && $this->params->get('menu-meta_image'))
		{
			$doc->setMetadata('image',Uri::root() .$this->params->get('menu-meta_image'));
		}
		elseif ($project->metadata->get('image'))
		{
			$doc->setMetadata('image', Uri::root() . $project->metadata->get('image'));
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
		elseif ($project->metadata->get('robots'))
		{
			$doc->setMetadata('robots', $project->metadata->get('robots'));
		}

		// Set meta url
		$url = Uri::getInstance()->toString(['scheme', 'host', 'port']) . $project->link;
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

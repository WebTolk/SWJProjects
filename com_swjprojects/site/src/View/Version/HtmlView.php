<?php
/**
 * @package       SW JProjects
 * @version       2.6.0
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
 */

namespace Joomla\Component\SWJProjects\Site\View\Version;

defined('_JEXEC') or die;

use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
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
	 * Version object.
	 *
	 * @var  object|false
	 *
	 * @since  1.0.0
	 */
	protected $version;

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
        $model = $this->getModel();
		$this->state    = $model->getState();
		$this->params   = $this->state->get('params');
		$this->version  = $model->getItem();
		$this->project  = $this->version->project;
		$this->category = $this->version->category;
		$this->menu     = Factory::getApplication()->getMenu()->getActive();

		// Check for errors
		if (count($errors = $model->getErrors()))
		{
			throw new \Exception(implode('\n', $errors), 500);
		}

		// Create a shortcut for item
		$version = $this->version;
		$project = &$this->project;

		// Check to see which parameters should take priority
		$temp = clone $this->params;
		$menu = $this->menu;

		if ($menu
			&& $menu->query['option'] == 'com_swjprojects'
			&& $menu->query['view'] == 'version'
			&& @$menu->query['id'] == $version->id)
		{
			if (isset($menu->query['layout']))
			{
				$this->setLayout($menu->query['layout']);
			}
			elseif ($layout = $version->params->get('version_layout'))
			{
				$this->setLayout($layout);
			}

			$version->params->merge($temp);
		}
		else
		{
			$temp->merge($version->params);
			$version->params = $temp;

			if ($layout = $version->params->get('version_layout'))
			{
				$this->setLayout($layout);
			}
		}
		$this->params = $version->params;

		// Process the content plugins
		PluginHelper::importPlugin('content');
		$project->text = &$project->fulltext;

		$dispatcher = $this->getDispatcher();
		// Extra content from events
		$project->event   = new \stdClass();

		$contentEventArguments = [
			'context' => 'com_swjprojects.version.project',
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
	 * @since  1.0.0
	 */
	protected function _prepareDocument()
	{
		$app     = Factory::getApplication();
		$version = $this->version;
		$menu    = $this->menu;
		$doc = $this->getDocument();
		$current = ($menu && $menu->query['option'] === 'com_swjprojects'
			&& $menu->query['view'] === 'version' &&
			(int) @$menu->query['id'] === (int) $version->id);

		// Add version pathway item if no current menu
		if ($menu && !$current)
		{
			$paths = [['title' => $version->title, 'link' => '']];

			// Add versions pathway item if no current menu
			$project = $this->project;
			if ($menu->query['option'] !== 'com_swjprojects'
				|| $menu->query['view'] !== 'versions'
				|| (int) @$menu->query['project_id'] !== (int) $project->id)
			{
				$paths[] = ['title' => Text::_('COM_SWJPROJECTS_VERSIONS'), 'link' => $project->versions];

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
		$title = $version->title;
		if ($current && $this->params->get('page_title'))
		{
			$title = $this->params->get('page_title');
		}
		elseif ($version->metadata->get('title'))
		{
			$title = $version->metadata->get('title');
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
		elseif ($version->metadata->get('description'))
		{
			$doc->setDescription($version->metadata->get('description'));
		}

		// Set meta keywords
		if ($current && $this->params->get('menu-meta_keywords'))
		{
			$doc->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}
		elseif ($version->metadata->get('keywords'))
		{
			$doc->setMetadata('keywords', $version->metadata->get('keywords'));
		}

		// Set meta image
		if ($current && $this->params->get('menu-meta_image'))
		{
			$doc->setMetadata('image',Uri::root() .$this->params->get('menu-meta_image'));
		}
		elseif ($version->metadata->get('image'))
		{
			$doc->setMetadata('image', Uri::root() . $version->metadata->get('image'));
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
		elseif ($version->metadata->get('robots'))
		{
			$doc->setMetadata('robots', $version->metadata->get('robots'));
		}

		// Set meta url
		$url = Uri::getInstance()->toString(['scheme', 'host', 'port']) . $version->link;
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

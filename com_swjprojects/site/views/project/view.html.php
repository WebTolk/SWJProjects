<?php
/**
 * @package    SW JProjects Component
 * @version    1.0.0
 * @author     Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2018 - 2018 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;

class SWJProjectsViewProject extends HtmlView
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
	 * @var  \Joomla\Registry\Registry;
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
	 * Active menu item.
	 *
	 * @var  \Joomla\CMS\Menu\MenuItem
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
	 * @param  string $tpl The name of the template file to parse.
	 *
	 * @throws  Exception
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since  1.0.0
	 */
	public function display($tpl = null)
	{
		$this->state    = $this->get('State');
		$this->params   = $this->state->get('params');
		$this->project  = $this->get('Item');
		$this->category = $this->project->category;
		$this->menu     = Factory::getApplication()->getMenu()->getActive();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode('\n', $errors), 500);
		}

		// Create a shortcut for item.
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

		// Process the content plugins.
		PluginHelper::importPlugin('content');
		$app           = Factory::getApplication();
		$offset        = $app->input->getUInt('limitstart');
		$project->text = &$project->fulltext;
		$app->triggerEvent('onContentPrepare', array('com_swjprojects.project', &$project, &$project->params, $offset));

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

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
		$app     = Factory::getApplication();
		$project = $this->project;
		$menu    = $this->menu;
		$current = ($menu
			&& $menu->query['option'] === 'com_swjprojects'
			&& $menu->query['view'] === 'project'
			&& (int) @$menu->query['id'] === (int) $project->id);

		// Add project pathway item if no current menu
		if ($menu && !$current)
		{
			$paths = array(array('title' => $project->title, 'link' => ''));

			// Add categories pathway item if no current menu
			$category = $this->category;
			while ($category
				&& $category->id > 1
				&& ($menu->query['option'] !== 'com_swjprojects'
					|| $menu->query['view'] !== 'projects'
					|| (int) @$menu->query['id'] !== (int) $category->id))
			{
				$paths[]  = array('title' => $category->title, 'link' => $category->link);
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
		$title    = (!$current) ? $project->title : $this->params->get('page_title');
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
		elseif (!empty($project->introtext))
		{
			$this->document->setDescription(JHtmlString::truncate($project->introtext, 150, false, false));
		}

		// Set Meta Keywords
		if ($current && $this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		// Set meta robots
		$inWork = $this->state->get('debug', 0);
		if ($inWork)
		{
			$this->document->setMetadata('robots', 'noindex');
		}
		elseif ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}

		// Set meta url
		$url = rtrim(URI::root(), '/') . $project->link;
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
<?php
/**
 * @package    JLSitemap - SW JProjects Plugin
 * @version    __DEPLOY_VERSION__
 * @author     Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2018 - 2019 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Registry\Registry;

class plgJLSitemapSWJProjects extends CMSPlugin
{
	/**
	 * Affects constructor behavior.
	 *
	 * @var boolean
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Translates languages.
	 *
	 * @var  array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $translates = null;

	/**
	 * Constructor.
	 *
	 * @param  object  &$subject The object to observe
	 * @param  array    $config  An optional associative array of configuration settings.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(object $subject, array $config = array())
	{
		// Set translates
		$this->translates = array(
			'current' => Factory::getLanguage()->getTag(),
			'default' => ComponentHelper::getParams('com_languages')->get('site', 'en-GB'),
			'all'     => array_keys(LanguageHelper::getLanguages('lang_code'))
		);

		parent::__construct($subject, $config);
	}

	/**
	 * Method to get urls array.
	 *
	 * @param  array    $urls   Urls array.
	 * @param  Registry $config Component config.
	 *
	 * @return  array  Urls array with attributes.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function onGetUrls(&$urls, $config)
	{
		if (!$this->params->get('projects_enable')
			|| !$this->params->get('project_enable')
			|| !$this->params->get('versions_enable')
			|| !$this->params->get('version_enable'))
		{
			return $urls;
		}

		$db            = Factory::getDbo();
		$multilanguage = $config->get('multilanguage');
		$current       = $this->translates['current'];
		$default       = $this->translates['default'];
		$all           = $this->translates['all'];
		foreach ($all as $key => $code)
		{
			$all[$key] = $db->quote($code);
		}

		// Load route helper
		JLoader::register('SWJProjectsHelperRoute', JPATH_SITE . '/components/com_swjprojects/helpers/route.php');

		// Add projects categories to sitemap
		if ($this->params->get('projects_enable'))
		{
			$query = $db->getQuery(true)
				->select(array('c.id', 'c.alias', 'c.state'))
				->from($db->quoteName('#__swjprojects_categories', 'c'))
				->where($db->quoteName('c.alias') . '!=' . $db->quote('root'))
				->group(array('c.id', 't_c.language'));

			// Join over translates
			$query->select(array('t_c.title', 't_c.language'));
			if ($multilanguage)
			{
				$query->leftJoin($db->quoteName('#__swjprojects_translate_categories', 't_c')
					. '  ON t_c.id = c.id AND ' . $db->quoteName('t_c.language') . 'IN (' . implode(',', $all) . ')');
			}
			else
			{
				$query->leftJoin($db->quoteName('#__swjprojects_translate_categories', 't_c')
					. '  ON t_c.id = c.id AND ' . $db->quoteName('t_c.language') . ' = ' . $db->quote($current));
			}

			// Join over default translates
			$query->select(array('td_c.title as default_title'))
				->leftJoin($db->quoteName('#__swjprojects_translate_categories', 'td_c')
					. ' ON td_c.id = c.id AND ' . $db->quoteName('td_c.language') . ' = ' . $db->quote($default));

			$rows       = $db->setQuery($query)->loadObjectList();
			$changefreq = $this->params->get('projects_changefreq', $config->get('changefreq', 'weekly'));
			$priority   = $this->params->get('projects_priority', $config->get('priority', '0.5'));

			foreach ($rows as $row)
			{
				// Prepare title attribute
				$title = (!empty($row->title)) ? $row->title : $row->default_title;
				if (empty($title))
				{
					$title = $row->alias;
				}

				// Prepare loc attribute
				$slug = $row->id . ':' . $row->alias;
				$loc  = SWJProjectsHelperRoute::getProjectsRoute($slug);
				if ($multilanguage)
				{
					$loc .= '&lang=' . $row->language;
				}

				// Prepare exclude attribute
				$exclude = array();
				if ($row->state != 1)
				{
					$exclude[] = array(
						'type' => Text::_('PLG_JLSITEMAP_SWJPROJECTS_EXCLUDE_CATEGORY'),
						'msg'  => ($row->state == -1)
							? Text::_('PLG_JLSITEMAP_SWJPROJECTS_EXCLUDE_CATEGORY_TRASH')
							: Text::_('PLG_JLSITEMAP_SWJPROJECTS_EXCLUDE_CATEGORY_UNPUBLISH')
					);
				}

				// Prepare category object
				$category             = new stdClass();
				$category->type       = Text::_('PLG_JLSITEMAP_SWJPROJECTS_TYPES_PROJECTS');
				$category->title      = $title;
				$category->loc        = $loc;
				$category->changefreq = $changefreq;
				$category->priority   = $priority;
				$category->exclude    = (!empty($exclude)) ? $exclude : false;
				$category->alternates = ($multilanguage) ? array() : false;

				if ($category->alternates !== false)
				{
					foreach ($this->translates['all'] as $code)
					{
						$category->alternates[$code] = SWJProjectsHelperRoute::getProjectsRoute($slug) . '&lang=' . $code;
					}
				}

				// Add category to array
				$urls[] = $category;
			}
		}

		// Add projects to sitemap
		if ($this->params->get('project_enable') || $this->params->get('versions_enable'))
		{
			$query = $db->getQuery(true)
				->select(array('p.id', 'p.alias', 'p.state'))
				->from($db->quoteName('#__swjprojects_projects', 'p'))
				->group(array('p.id', 't_p.language'));

			// Join over categories
			$query->select(array('c.id as category_id', 'c.alias as category_alias', 'c.state as category_state'))
				->leftJoin($db->quoteName('#__swjprojects_categories', 'c') . ' ON c.id = p.catid');

			// Join over translates
			$query->select(array('t_p.title', 't_p.language'));
			if ($multilanguage)
			{
				$query->leftJoin($db->quoteName('#__swjprojects_translate_projects', 't_p')
					. '  ON t_p.id = p.id AND ' . $db->quoteName('t_p.language') . 'IN (' . implode(',', $all) . ')');
			}
			else
			{
				$query->leftJoin($db->quoteName('#__swjprojects_translate_projects', 't_p')
					. '  ON t_p.id = p.id AND ' . $db->quoteName('t_p.language') . ' = ' . $db->quote($current));
			}

			// Join over default translates
			$query->select(array('td_p.title as default_title'))
				->leftJoin($db->quoteName('#__swjprojects_translate_projects', 'td_p')
					. ' ON td_p.id = p.id AND ' . $db->quoteName('td_p.language') . ' = ' . $db->quote($default));

			$rows               = $db->setQuery($query)->loadObjectList();
			$changefreq         = $this->params->get('project_changefreq', $config->get('changefreq', 'weekly'));
			$priority           = $this->params->get('project_priority', $config->get('priority', '0.5'));
			$versionsChangefreq = $this->params->get('versions_changefreq', $config->get('changefreq', 'weekly'));
			$versionsPriority   = $this->params->get('versions_priority', $config->get('priority', '0.5'));

			foreach ($rows as $row)
			{
				// Prepare title attribute
				$title = (!empty($row->title)) ? $row->title : $row->default_title;
				if (empty($title))
				{
					$title = $row->alias;
				}

				// Prepare loc attribute
				$slug        = $row->id . ':' . $row->alias;
				$catslug     = $row->category_id . ':' . $row->category_alias;
				$loc         = SWJProjectsHelperRoute::getProjectRoute($slug, $catslug);
				$versionsLoc = SWJProjectsHelperRoute::getVersionsRoute($slug, $catslug);
				if ($multilanguage)
				{
					$loc         .= '&lang=' . $row->language;
					$versionsLoc .= '&lang=' . $row->language;
				}

				// Prepare exclude attribute
				$exclude = array();
				if ($row->state != 1)
				{
					$exclude[] = array(
						'type' => Text::_('PLG_JLSITEMAP_SWJPROJECTS_EXCLUDE_PROJECT'),
						'msg'  => ($row->state == -1)
							? Text::_('PLG_JLSITEMAP_SWJPROJECTS_EXCLUDE_PROJECT_TRASH')
							: Text::_('PLG_JLSITEMAP_SWJPROJECTS_EXCLUDE_PROJECT_UNPUBLISH')
					);
				}
				if ($row->category_state != 1)
				{
					$exclude[] = array(
						'type' => Text::_('PLG_JLSITEMAP_SWJPROJECTS_EXCLUDE_CATEGORY'),
						'msg'  => ($row->state == -1)
							? Text::_('PLG_JLSITEMAP_SWJPROJECTS_EXCLUDE_CATEGORY_TRASH')
							: Text::_('PLG_JLSITEMAP_SWJPROJECTS_EXCLUDE_CATEGORY_UNPUBLISH')
					);
				}

				// Prepare project object
				$project             = new stdClass();
				$project->type       = Text::_('PLG_JLSITEMAP_SWJPROJECTS_TYPES_PROJECT');
				$project->title      = $title;
				$project->loc        = $loc;
				$project->changefreq = $changefreq;
				$project->priority   = $priority;
				$project->exclude    = (!empty($exclude)) ? $exclude : false;
				$project->alternates = ($multilanguage) ? array() : false;

				// Prepare versions object
				$versions             = new stdClass();
				$versions->type       = Text::_('PLG_JLSITEMAP_SWJPROJECTS_TYPES_VERSIONS');
				$versions->title      = Text::sprintf('PLG_JLSITEMAP_SWJPROJECTS_TYPES_VERSIONS_TITLE', $title);
				$versions->loc        = $versionsLoc;
				$versions->changefreq = $versionsChangefreq;
				$versions->priority   = $versionsPriority;
				$versions->exclude    = (!empty($exclude)) ? $exclude : false;
				$versions->alternates = ($multilanguage) ? array() : false;

				if ($project->alternates !== false)
				{
					foreach ($this->translates['all'] as $code)
					{
						$project->alternates[$code] = SWJProjectsHelperRoute::getProjectRoute($slug, $catslug)
							. '&lang=' . $code;

						$versions->alternates[$code] = SWJProjectsHelperRoute::getVersionsRoute($slug, $catslug)
							. '&lang=' . $code;
					}
				}

				// Add project to array
				if ($this->params->get('project_enable'))
				{
					$urls[] = $project;
				}

				// Add versions to array
				if ($this->params->get('versions_enable'))
				{
					$urls[] = $versions;
				}
			}
		}

		// Add versions to sitemap
		if ($this->params->get('version_enable'))
		{
			$query = $db->getQuery(true)
				->select(array('v.id', 'v.major', 'v.minor', 'v.micro', 'v.tag', 'v.stage', 'v.state'))
				->from($db->quoteName('#__swjprojects_versions', 'v'))
				->group(array('v.id', 't_v.language'));

			// Join over projects
			$query->select(array('p.id as project_id', 'p.alias as project_alias', 'p.state as project_state'))
				->leftJoin($db->quoteName('#__swjprojects_projects', 'p') . ' ON p.id = v.project_id');

			// Join over categories
			$query->select(array('c.id as category_id', 'c.alias as category_alias', 'c.state as category_state'))
				->leftJoin($db->quoteName('#__swjprojects_categories', 'c') . ' ON c.id = p.catid');

			// Join over translates
			$query->select(array('t_p.title as project_title', 't_v.language'));
			if ($multilanguage)
			{
				$query->leftJoin($db->quoteName('#__swjprojects_translate_versions', 't_v')
					. '  ON t_v.id = v.id AND ' . $db->quoteName('t_v.language') . 'IN (' . implode(',', $all) . ')');
			}
			else
			{
				$query->leftJoin($db->quoteName('#__swjprojects_translate_versions', 't_v')
					. '  ON t_v.id = v.id AND ' . $db->quoteName('t_v.language') . ' = ' . $db->quote($current));
			}
			$query->leftJoin($db->quoteName('#__swjprojects_translate_projects', 't_p')
				. '  ON t_p.id = p.id AND t_p.language = t_v.language');

			// Join over default translates
			$query->select(array('td_p.title as project_default_title'))
				->leftJoin($db->quoteName('#__swjprojects_translate_projects', 'td_p')
					. ' ON td_p.id = p.id AND ' . $db->quoteName('td_p.language') . ' = ' . $db->quote($default));

			$rows       = $db->setQuery($query)->loadObjectList();
			$changefreq = $this->params->get('version_changefreq', $config->get('changefreq', 'weekly'));
			$priority   = $this->params->get('version_priority', $config->get('priority', '0.5'));

			foreach ($rows as $row)
			{
				// Prepare title attribute
				$projectTitle = (!empty($row->project_title)) ? $row->project_title : $row->project_default_title;
				if (empty($projectTitle))
				{
					$projectTitle = $row->project_alias;
				}
				$title = $projectTitle . ' ' . $row->major . '.' . $row->minor . '.' . $row->micro;
				if ($row->tag !== 'stable')
				{
					$title .= ' ' . Text::_('PLG_JLSITEMAP_SWJPROJECTS_TYPES_VERSION_TAG_' . $row->tag);
					if ($row->tag !== 'dev' && !empty($row->stage))
					{
						$title .= ' ' . $row->stage;
					}
				}

				// Prepare loc attribute
				$pojectslug = $row->project_id . ':' . $row->project_alias;
				$catslug    = $row->category_id . ':' . $row->category_alias;
				$loc        = SWJProjectsHelperRoute::getVersionRoute($row->id, $pojectslug, $catslug);
				if ($multilanguage)
				{
					$loc .= '&lang=' . $row->language;
				}

				// Prepare exclude attribute
				$exclude = array();
				if ($row->state != 1)
				{
					$exclude[] = array(
						'type' => Text::_('PLG_JLSITEMAP_SWJPROJECTS_EXCLUDE_VERSION'),
						'msg'  => ($row->state == -1)
							? Text::_('PLG_JLSITEMAP_SWJPROJECTS_EXCLUDE_VERSION_TRASH')
							: Text::_('PLG_JLSITEMAP_SWJPROJECTS_EXCLUDE_VERSION_UNPUBLISH')
					);
				}
				if ($row->project_state != 1)
				{
					$exclude[] = array(
						'type' => Text::_('PLG_JLSITEMAP_SWJPROJECTS_EXCLUDE_PROJECT'),
						'msg'  => ($row->project_state == -1)
							? Text::_('PLG_JLSITEMAP_SWJPROJECTS_EXCLUDE_PROJECT_TRASH')
							: Text::_('PLG_JLSITEMAP_SWJPROJECTS_EXCLUDE_PROJECT_UNPUBLISH')
					);
				}
				if ($row->category_state != 1)
				{
					$exclude[] = array(
						'type' => Text::_('PLG_JLSITEMAP_SWJPROJECTS_EXCLUDE_CATEGORY'),
						'msg'  => ($row->state == -1)
							? Text::_('PLG_JLSITEMAP_SWJPROJECTS_EXCLUDE_CATEGORY_TRASH')
							: Text::_('PLG_JLSITEMAP_SWJPROJECTS_EXCLUDE_CATEGORY_UNPUBLISH')
					);
				}

				// Prepare project object
				$version             = new stdClass();
				$version->type       = Text::_('PLG_JLSITEMAP_SWJPROJECTS_TYPES_VERSION');
				$version->title      = $title;
				$version->loc        = $loc;
				$version->changefreq = $changefreq;
				$version->priority   = $priority;
				$version->exclude    = (!empty($exclude)) ? $exclude : false;
				$version->alternates = ($multilanguage) ? array() : false;

				if ($version->alternates !== false)
				{
					foreach ($this->translates['all'] as $code)
					{
						$version->alternates[$code] = SWJProjectsHelperRoute::getVersionRoute($row->id, $pojectslug, $catslug)
							. '&lang=' . $code;
					}
				}

				// Add version to array
				$urls[] = $version;
			}
		}

		return $urls;
	}
}
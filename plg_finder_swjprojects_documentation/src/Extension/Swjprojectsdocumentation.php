<?php
/**
 * @package    SW JProjects
 * @version    2.2.0
 * @author     Sergey Tolkachyov
 * @сopyright  Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
 */

namespace Joomla\Plugin\Finder\Swjprojectsdocumentation\Extension;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\Finder as FinderEvent;

use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\Component\Finder\Administrator\Indexer\Adapter;
use Joomla\Component\Finder\Administrator\Indexer\Helper;
use Joomla\Component\Finder\Administrator\Indexer\Indexer;
use Joomla\Component\Finder\Administrator\Indexer\Result;
use Joomla\Component\SWJProjects\Site\Helper\RouteHelper;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseQuery;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Smart Search adapter for com_swjprojects.
 *
 * @since  2.5
 */
final class Swjprojectsdocumentation extends Adapter implements SubscriberInterface
{
	use DatabaseAwareTrait;

	/**
	 * The plugin identifier.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $context = 'Swjprojectsdocumentation';

	/**
	 * The extension name.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $extension = 'com_swjprojects';

	/**
	 * The sublayout to use when rendering the results.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $layout = 'document';

	/**
	 * The type of content that the adapter indexes.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $type_title = 'Document';

	/**
	 * The table name.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $table = '#__swjprojects_documentation';

	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 *
	 * @since   5.0.0
	 */
	public static function getSubscribedEvents(): array
	{
		return array_merge([
			'onFinderCategoryChangeState' => 'onFinderCategoryChangeState',
			'onFinderChangeState'         => 'onFinderChangeState',
			'onFinderAfterDelete'         => 'onFinderAfterDelete',
			'onFinderBeforeSave'          => 'onFinderBeforeSave',
			'onFinderAfterSave'           => 'onFinderAfterSave',
		], parent::getSubscribedEvents());
	}

	/**
	 * Method to update the item link information when the item category is
	 * changed. This is fired when the item category is published or unpublished
	 * from the list view.
	 *
	 * @param   FinderEvent\AfterCategoryChangeStateEvent  $event  The event instance.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function onFinderCategoryChangeState(FinderEvent\AfterCategoryChangeStateEvent $event)
	{
//		// Make sure we're handling com_swjprojects categories.
//		if ($event->getExtension() === 'com_swjprojects')
//		{
//			$this->categoryStateChange($event->getPks(), $event->getValue());
//		}
	}

	/**
	 * Method to remove the link information for items that have been deleted.
	 *
	 * @param   FinderEvent\AfterDeleteEvent  $event  The event instance.
	 *
	 * @return  void
	 *
	 * @throws  \Exception on database error.
	 * @since   2.5
	 */
	public function onFinderAfterDelete(FinderEvent\AfterDeleteEvent $event): void
	{
//		$context = $event->getContext();
//		$table   = $event->getItem();
//
//		if ($context === 'com_swjprojects.project')
//		{
//			$id = $table->id;
//		}
//		elseif ($context === 'com_finder.index')
//		{
//			$id = $table->link_id;
//		}
//		else
//		{
//			return;
//		}
//
//		// Remove item from the index.
//		$this->remove($id);
	}

	/**
	 * Smart Search after save content method.
	 * Reindexes the link information for an project that has been saved.
	 * It also makes adjustments if the access level of an item or the
	 * category to which it belongs has changed.
	 *
	 * @param   FinderEvent\AfterSaveEvent  $event  The event instance.
	 *
	 * @return  void
	 *
	 * @throws  \Exception on database error.
	 * @since   2.5
	 */
	public function onFinderAfterSave(FinderEvent\AfterSaveEvent $event): void
	{
//		$context = $event->getContext();
//		$row     = $event->getItem();
//		$isNew   = $event->getIsNew();
//
//		// We only want to handle projects here.
//		if ($context === 'com_swjprojects.project' || $context === 'com_swjprojects.form')
//		{
//			// Check if the access levels are different.
//			if (!$isNew && $this->old_access != $row->access)
//			{
//				// Process the change.
//				$this->itemAccessChange($row);
//			}
//
//			// Reindex the item.
//			$this->reindex($row->id);
//		}
//
//		// Check for access changes in the category.
//		if ($context === 'com_swjprojects.category')
//		{
//			// Check if the access levels are different.
//			if (!$isNew && $this->old_cataccess != $row->access)
//			{
//				$this->categoryAccessChange($row);
//			}
//		}
	}

	/**
	 * Smart Search before content save method.
	 * This event is fired before the data is actually saved.
	 *
	 * @param   FinderEvent\BeforeSaveEvent  $event  The event instance.
	 *
	 * @return  boolean  True on success.
	 *
	 * @throws  \Exception on database error.
	 * @since   2.5
	 */
	public function onFinderBeforeSave(FinderEvent\BeforeSaveEvent $event)
	{
//		$context = $event->getContext();
//		$row     = $event->getItem();
//		$isNew   = $event->getIsNew();
//
//		// We only want to handle projects here.
//		if ($context === 'com_swjprojects.project' || $context === 'com_swjprojects.form')
//		{
//			// Query the database for the old access level if the item isn't new.
//			if (!$isNew)
//			{
//				$this->checkItemAccess($row);
//			}
//		}
//
//		// Check for access levels from the category.
//		if ($context === 'com_categories.category')
//		{
//			// Query the database for the old access level if the item isn't new.
//			if (!$isNew)
//			{
//				$this->checkCategoryAccess($row);
//			}
//		}
//
//		return true;
	}

	/**
	 * Method to update the link information for items that have been changed
	 * from outside the edit screen. This is fired when the item is published,
	 * unpublished, archived, or unarchived from the list view.
	 *
	 * @param   FinderEvent\AfterChangeStateEvent  $event  The event instance.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function onFinderChangeState(FinderEvent\AfterChangeStateEvent $event)
	{
//		$context = $event->getContext();
//		$pks     = $event->getPks();
//		$value   = $event->getValue();
//
//		// We only want to handle projects here.
//		if ($context === 'com_swjprojects.project' || $context === 'com_swjprojects.form')
//		{
//			$this->itemStateChange($pks, $value);
//		}
//
//		// Handle when the plugin is disabled.
//		if ($context === 'com_plugins.plugin' && $value === 0)
//		{
//			$this->pluginDisable($pks);
//		}
	}

	/**
	 * Method to setup the indexer to be run.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 */
	protected function setup()
	{
		return true;
	}

	/**
	 * Method to index an item. The item must be a Result object.
	 *
	 * @param   Result  $item  The item to index as a Result object.
	 *
	 * @return  void
	 *
	 * @throws  \Exception on database error.
	 * @since   2.5
	 */
	protected function index(Result $item)
	{
		/**
		 * Таксономии: разобраться с категориями. Добавить extension_type - плагин, модуль, компонент и т.д.
		 */
		// Check if the extension is enabled.
		if (ComponentHelper::isEnabled($this->extension) === false)
		{
			return;
		}

		// Initialise the item parameters.
		$registry     = new Registry($item->params);
		$item->params = clone ComponentHelper::getParams('com_swjprojects', true);
		$item->params->merge($registry);
		$item->context = 'com_swjprojects.document';
		$lang_codes    = LanguageHelper::getLanguages('lang_code');

		$translates = $this->getTranslateDocumentation($item->id);

		// Translate the state. projects should only be published if the category is published.
		$item->state = $this->translateState($item->document_state, $item->project_state);

		// Get taxonomies to display
		$taxonomies = $this->params->get('taxonomies', ['type', 'category', 'language']);

		// Add the type taxonomy data.
		if (\in_array('type', $taxonomies))
		{
			$item->addTaxonomy('Type', 'Document');
		}

		$item->access = 1;
		foreach ($translates as $translate)
		{
			$item->language = $translate->language;
			$item->title    = $translate->title;
			// Trigger the onContentPrepare event.
			$item->summary = Helper::prepareContent($translate->introtext, $item->params, $item);
			$item->body    = Helper::prepareContent($translate->fulltext, $item->params, $item);

			$metadata       = new Registry($translate->metadata);
			$item->metakey  = $metadata->get('keywords', '');
			$item->metadesc = $metadata->get('description', $translate->introtext);
			// Add the metadata processing instructions.
			$item->addInstruction(Indexer::META_CONTEXT, 'metakey');
			$item->addInstruction(Indexer::META_CONTEXT, 'metadesc');

			$lang = '';

			if (Multilanguage::isEnabled())
			{
				foreach ($lang_codes as $lang_code)
				{
					if ($translate->language == $lang_code->lang_code)
					{
						$lang = $lang_code->sef;
					}
				}
			}
			// Create a URL as identifier to recognise items again.
			$item->url = $this->getUrl($item->id, $this->extension, $this->layout, $lang);

			// Build the necessary route and path information.
			$item->route = RouteHelper::getDocumentRoute($item->id, $item->project_id, $item->catid);

			// Get the menu title if it exists.
			$title = $this->getItemMenuTitle($item->route);

			// Adjust the title if necessary.
			if (!empty($title) && $this->params->get('use_menu_title', true))
			{
				$item->title = $title;
			}

			// Add the language taxonomy data.
			if (\in_array('language', $taxonomies))
			{
				$item->addTaxonomy('Language', $item->language, 1, 1, $item->language);
			}


			$item->metadata = new Registry($item->metadata);

			// Add the meta author.
//        $item->metaauthor = $item->metadata->get('author');


			// Get content extras.
//        Helper::getContentExtras($item);
//        Helper::addCustomFields($item, 'com_swjprojects.project');

			// Index the item.
			$this->indexer->index($item);

		}

	}

	/**
	 * Get the project translates by project id
	 *
	 * @param   int  $document_id
	 *
	 * @return mixed
	 *
	 * @since 2.1.0
	 */
	private function getTranslateDocumentation(int $document_id)
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
			->select($db->quoteName([
				't_d.title',
				't_d.introtext',
				't_d.fulltext',
				't_d.language',
				't_d.metadata',
			]))
			->from($db->quoteName('#__swjprojects_translate_documentation', 't_d'))
			->where($db->quoteName('t_d.id') . ' = ' . $db->quote($document_id));


		return $db->setQuery($query)->loadObjectList();
	}

	/**
	 * @param   int     $id
	 * @param   string  $extension
	 * @param   string  $view
	 * @param   string  $lang  Language SEF code like `ru`, `en` etc
	 *
	 * @return string
	 *
	 * @since 2.1.0
	 */
	public function getUrl($id, $extension, $view, $lang = '')
	{

		$url = 'index.php?option=' . $extension . '&view=' . $view . '&id=' . $id;

		if (!empty($lang))
		{
			$url .= '&lang=' . $lang;
		}

		return $url;
	}

	/**
	 * Method to get the SQL query used to retrieve the list of content items.
	 *
	 * @param   mixed  $query  A DatabaseQuery object or null.
	 *
	 * @return  DatabaseQuery  A database object.
	 *
	 * @since   2.5
	 */
	protected function getListQuery($query = null)
	{
		$db = $this->getDatabase();

		// Check if we can use the supplied SQL query.
		$query = $query instanceof DatabaseQuery ? $query : $db->getQuery(true)
			->select($db->quoteName([
				'a.id',
				'a.alias',
				'a.project_id',
				'a.params',
				'p.catid',
				'p.visible',
			]))
			->select(
				$db->quoteName(
					[
						'a.state',
						'p.state',
						'c.state',
					], [
					'document_state',
					'project_state',
					'category_state',
				])
			)
			->from($db->quoteName('#__swjprojects_documentation', 'a'))
			->innerJoin($db->quoteName('#__swjprojects_projects', 'p'), 'p.id = a.project_id')
			->innerJoin($db->quoteName('#__swjprojects_categories', 'c'), 'c.id = p.catid');

		$query->where(
			[
				$db->quoteName('a.state') . ' = ' . $db->quote(1),
				$db->quoteName('p.state') . ' = ' . $db->quote(1),
				$db->quoteName('p.visible') . ' = ' . $db->quote(1),
				$db->quoteName('c.state') . ' = ' . $db->quote(1)
			]
		);

		return $query;
	}
}

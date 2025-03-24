<?php
/**
 * @package       SW JProjects
 * @version       2.4.0
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
 */

namespace Joomla\Plugin\Actionlog\Swjprojects\Extension;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\User\UserFactoryAwareTrait;
use Joomla\Component\Actionlogs\Administrator\Helper\ActionlogsHelper;
use Joomla\Component\Actionlogs\Administrator\Plugin\ActionLogPlugin;
use Joomla\Component\SWJProjects\Administrator\Helper\TranslationHelper;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\DispatcherInterface;
// use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use RuntimeException;
use stdClass;
use function defined;
use function explode;
use function in_array;
use function strtoupper;

defined('_JEXEC') or die;

/**
 * Joomla! Users Actions Logging Plugin.
 *
 * @since  3.9.0
 * @todo   implements SubscriberInterface when Joomla 6 will be released.
 */
final class Swjprojects extends ActionLogPlugin
{
	use DatabaseAwareTrait;
	use UserFactoryAwareTrait;

	/**
	 * Array of loggable extensions.
	 *
	 * @var    array
	 * @since  2.4.0
	 */
	protected array $loggableExtensions = [];

	/**
	 * Flag for loggable Api.
	 *
	 * @var    boolean
	 * @since  2.4.0
	 */
	protected bool $loggableApi = false;

	/**
	 * Array of loggable verbs.
	 *
	 * @var    array
	 * @since  2.4.0
	 */
	protected array $loggableVerbs = [];

	/**
	 * Context aliases
	 *
	 * @var    array
	 * @since  2.4.0
	 */
	protected array $contextList = [
		'com_swjprojects.document',
		'com_swjprojects.documentation',
		'com_swjprojects.project',
		'com_swjprojects.projects',
		'com_swjprojects.key',
		'com_swjprojects.keys',
		'com_swjprojects.version',
		'com_swjprojects.versions',
		'com_swjprojects.category',
		'com_swjprojects.categories',
	];

	/**
	 * Map context to database tables with translations
	 *
	 * @var array
	 * @since 2.4.0
	 */
	protected array $translateTables = [
		'com_swjprojects.document'      => '#__swjprojects_translate_documentation',
		'com_swjprojects.documentation' => '#__swjprojects_translate_documentation',
		'com_swjprojects.project'       => '#__swjprojects_translate_projects',
		'com_swjprojects.projects'      => '#__swjprojects_translate_projects',
		'com_swjprojects.key'           => null,
		'com_swjprojects.keys'          => null,
		'com_swjprojects.version'       => '#__swjprojects_versions',
		'com_swjprojects.versions'      => '#__swjprojects_versions',
		'com_swjprojects.category'      => '#__swjprojects_translate_categories',
		'com_swjprojects.categories'    => '#__swjprojects_translate_categories',
	];

	/**
	 * Constructor.
	 *
	 * @param   DispatcherInterface  $dispatcher  The dispatcher
	 * @param   array                $config      An optional associative array of configuration settings
	 *
	 * @since   2.4.0
	 */
	public function __construct(DispatcherInterface $dispatcher, array $config)
	{
		parent::__construct($dispatcher, $config);

		$params = ComponentHelper::getComponent('com_actionlogs')->getParams();

		$this->loggableExtensions = $params->get('loggable_extensions', []);

		$this->loggableApi = $params->get('loggable_api', 0);

		$this->loggableVerbs = $params->get('loggable_verbs', []);

	}

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return array
	 *
	 * @since   2.4.0
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onContentAfterSave'   => 'onContentAfterSave',
			'onContentAfterDelete' => 'onContentAfterDelete',
			'onContentChangeState' => 'onContentChangeState',
		];
	}


	/**
	 * After save content logging method.
	 * This method adds a record to `#__action_logs` contains (message, date, context, user)
	 * Method is called right after the content is saved
	 *
	 * @param   string  $context
	 * @param   object  $item
	 * @param   bool    $isNew
	 * @param   array   $data
	 *
	 * @return  void
	 *
	 * @since   2.4.0 // $context, $item, $isNew, $data
	 * @todo    use Model\AfterSaveEvent $event when Joomla 6 will be released
	 */
	public function onContentAfterSave($context, $item, $isNew, $data): void
	{
//		$context = $event->getContext();
//		$item = $event->getItem();
//		$isNew = $event->getIsNew();
//		$data = $event->getData();

		if (!in_array($context, $this->contextList))
		{
			return;
		}

		list($option, $contentType) = explode('.', $context);

		if (!$this->checkLoggable($option))
		{
			return;
		}

		if ($isNew)
		{
			$messageLanguageKey = 'PLG_ACTIONLOG_SWJPROJECTS_' . strtoupper($contentType) . '_ADDED';
			$data['id']         = $item->id;
		}
		else if ($context == 'com_swjprojects.key' && $data['key_regenerate'] == 1)
		{
			$messageLanguageKey = 'PLG_ACTIONLOG_SWJPROJECTS_KEY_REGENERATED';
		}
		else
		{
			$messageLanguageKey = 'PLG_ACTIONLOG_SWJPROJECTS_' . strtoupper($contentType) . '_UPDATED';
		}


		$message = [
			'action'   => $isNew ? 'add' : 'update',
			'type'     => 'PLG_ACTIONLOG_SWJPROJECTS_TYPE_' . strtoupper($contentType),
			'id'       => $item->id,
			'title'    => $this->getItemTitle($context, $data),
			'itemlink' => 'index.php?option=com_swjprojects&task=' . $contentType . '.edit&id=' . $item->id,
		];

		if (!in_array($contentType, ['project', 'category', 'key']))
		{
			$message['projectTitle'] = $this->getProjectTitle($item->project_id);
			$message['projectLink']  = 'index.php?option=com_swjprojects&task=project.edit&id=' . $item->project_id;
		}

		$this->addLog([$message], $messageLanguageKey, $context);
	}


	/**
	 * Function to check if a component is loggable or not
	 *
	 * @param   string  $extension  The extension that triggered the event
	 *
	 * @return  boolean
	 *
	 * @since   2.4.0
	 */
	protected function checkLoggable(string $extension): bool
	{
		return in_array($extension, $this->loggableExtensions);
	}

	/**
	 * @param   string  $context
	 * @param   array   $data  Item data
	 *
	 * @return string
	 *
	 * @since 2.4.0
	 */
	protected function getItemTitle(string $context, array $data): string
	{
		$langTag = TranslationHelper::getCurrent() ?? TranslationHelper::getDefault();

		switch ($context)
		{
			case 'com_swjprojects.version' :

				$title = $data['major'] . '.' . $data['minor'] . '.' . $data['patch'];
				if (array_key_exists('hotfix', $data) && !empty($data['hotfix']))
				{
					$title .= '.' . $data['hotfix'];
				}
				break;
			case 'com_swjprojects.key':
				$title = $data['id'];
				break;
			case 'com_swjprojects.document' :
			case 'com_swjprojects.category' :
			case 'com_swjprojects.project' :
			default:
				$title = $data['translates'][$langTag]['title'];

				break;

		}

		return $title;
	}

	/**
	 * Get the project name for versions, documents etc.
	 *
	 * @param   int  $project_id
	 *
	 * @return string project name
	 *
	 * @since 2.4.0
	 */
	private function getProjectTitle(int $project_id): string
	{
		$project = $this->getApplication()->bootComponent('com_swjprojects')
			->getMVCFactory()
			->createModel('Project', 'Administrator', ['ignore_requets' => true])
			->getItem($project_id);

		$langTag = TranslationHelper::getCurrent() ?? TranslationHelper::getDefault();

		return $project->translates[$langTag]->title;
	}

	/**
	 * After delete content logging method
	 * This method adds a record to #__action_logs contains (message, date, context, user)
	 * Method is called right after the content is deleted
	 *
	 * @param string $context
	 * @param object $item
	 *
	 * @return  void
	 *
	 * @since   2.4.0
	 * @todo    use Model\AfterDeleteEvent $event when Joomla 6 will be released
	 */
	public function onContentAfterDelete($context, $item): void
	{
//		$context = $event->getContext();
//		item = $event->getItem();
		$option = $this->getApplication()->getInput()->get('option');

		if (!$this->checkLoggable($option))
		{
			return;
		}

		list(, $contentType) = explode('.', $context);

		$messageLanguageKey = 'PLG_ACTIONLOG_SWJPROJECTS_' . strtoupper($contentType) . '_DELETED';
		$data               = (new Registry($item))->toArray();

		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		$langTag = TranslationHelper::getCurrent() ?? TranslationHelper::getDefault();

		if (in_array($context, [
			'com_swjprojects.document',
			'com_swjprojects.documentation',
			'com_swjprojects.project',
			'com_swjprojects.projects',
			'com_swjprojects.category',
			'com_swjprojects.categories']))
		{
			$query->select($db->quoteName('title'))
				->from($db->quoteName($this->translateTables[$context]))
				->where($db->quoteName('id') . ' = ' . $db->quote($item->id))
				->where($db->quoteName('language') . ' = ' . $db->quote($langTag));

			$data['translates'][$langTag]['title'] = $db->setQuery($query)->loadResult();
		}

		$message = [
			'action' => 'delete',
			'type'   => 'PLG_ACTIONLOG_SWJPROJECTS_TYPE_' . $contentType,
			'id'     => $item->id,
			'title'  => $this->getItemTitle($context, $data),
		];

		if (!in_array($contentType, ['project', 'category', 'key']))
		{
			$message['projectTitle'] = $this->getProjectTitle($item->project_id);
			$message['projectLink']  = 'index.php?option=com_swjprojects&task=project.edit&id=' . $item->project_id;
		}

		$this->addLog([$message], $messageLanguageKey, $context);
	}

	/**
	 * On content change status logging method
	 * This method adds a record to #__action_logs contains (message, date, context, user)
	 * Method is called when the status of the article is changed
	 *
	 * @param string $context
	 * @param array $pks
	 * @param int $value
	 *
	 * @return  void
	 *
	 * @since   2.4.0
	 * @todo    use Model\AfterChangeStateEvent $event when Joomla 6 will be released
	 */
	public function onContentChangeState($context, $pks, $value): void
	{
//		$context = $event->getContext();
//		$pks     = $event->getPks();
//		$value   = $event->getValue();
		$option = $this->getApplication()->getInput()->getCmd('option');

		if (!$this->checkLoggable($option))
		{
			return;
		}

		list(, $contentType) = explode('.', $context);

		$langTag = TranslationHelper::getCurrent() ?? TranslationHelper::getDefault();

		switch ($value)
		{
			case 0:
				$messageLanguageKey = 'PLG_ACTIONLOG_SWJPROJECTS_' . strtoupper($contentType) . '_UNPUBLISHED';
				$action             = 'unpublish';
				break;
			case 1:
				$messageLanguageKey = 'PLG_ACTIONLOG_SWJPROJECTS_' . strtoupper($contentType) . '_PUBLISHED';
				$action             = 'publish';
				break;
			case -2:
				$messageLanguageKey = 'PLG_ACTIONLOG_SWJPROJECTS_' . strtoupper($contentType) . '_TRASHED';
				$action             = 'trash';
				break;
			default:
				$messageLanguageKey = '';
				$action             = '';
				break;
		}

		$items = [];
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);
		if (in_array($context, ['com_swjprojects.key', 'com_swjprojects.keys']))
		{
			foreach ($pks as $pk)
			{
				$keyData        = new stdClass();
				$keyData->id    = $pk;
				$keyData->title = $pk;
				$items[$pk]     = $keyData;
			}
		}
		else
		{
			if (in_array($context, ['com_swjprojects.version', 'com_swjprojects.versions']))
			{
				$query->select(['CONCAT(CASE WHEN a.hotfix != 0 THEN CONCAT(a.major, ".", a.minor, ".", a.patch,".", a.hotfix) ELSE CONCAT(a.major, ".", a.minor, ".", a.patch) END) as title', 'a.id', 'a.project_id']);
			}
			else
			{
				$query->select($db->quoteName(['a.title', 'a.id']))
					->where($db->quoteName('a.language') . ' = ' . $db->quote($langTag));
			}

			if (in_array($context, ['com_swjprojects.document', 'com_swjprojects.documentation']))
			{
				$query->select($db->quoteName('doc.project_id', 'project_id'));
				$query->leftJoin($db->quoteName('#__swjprojects_documentation', 'doc'), $db->quoteName('a.id') . ' = ' . $db->quoteName('doc.id'));
			}
			$query->from($db->quoteName($this->translateTables[$context], 'a'));

			$query->whereIn($db->quoteName('a.id'), ArrayHelper::toInteger($pks));

			$db->setQuery($query);

			try
			{
				$items = $db->loadObjectList('id');
			}
			catch (RuntimeException $e)
			{
				$items = [];
			}
		}
		$messages = [];

		foreach ($pks as $pk)
		{
			$message = [
				'action'   => $action,
				'type'     => 'PLG_ACTIONLOG_SWJPROJECTS_TYPE_' . $contentType,
				'id'       => $pk,
				'title'    => $items[$pk]->title,
				'itemlink' => ActionlogsHelper::getContentTypeLink($option, $contentType, $pk, 'id', null),
			];

			if (!in_array($contentType, ['project', 'category', 'key']))
			{
				$message['projectTitle'] = $this->getProjectTitle($items[$pk]->project_id);
				$message['projectLink']  = 'index.php?option=com_swjprojects&task=project.edit&id=' . $items[$pk]->project_id;
			}

			$messages[] = $message;
		}

		$this->addLog($messages, $messageLanguageKey, $context);
	}

}

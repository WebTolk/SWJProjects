<?php
/**
 * @package    SW JProjects
 * @version       2.4.0
 * @Author        Sergey Tolkachyov, https://web-tolk.ru
 * @сopyright  Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @since         1.0.0
 */

namespace Joomla\Plugin\Actionlog\Swjprojects\Extension;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\Model;
use Joomla\CMS\MVC\Factory\MVCFactoryServiceInterface;
use Joomla\CMS\User\UserFactoryAwareTrait;
use Joomla\Component\Actionlogs\Administrator\Helper\ActionlogsHelper;
use Joomla\Component\Actionlogs\Administrator\Plugin\ActionLogPlugin;
use Joomla\Component\SWJProjects\Administrator\Helper\TranslationHelper;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\Utilities\ArrayHelper;
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
	 * Context aliases
	 *
	 * @var    array
	 * @since  2.4.0
	 */
	protected $contextList = [
		'com_swjprojects.document',
		'com_swjprojects.project',
		'com_swjprojects.key',
		'com_swjprojects.version',
		'com_swjprojects.category',
	];

	/**
	 * Current lang tag for translates
	 *
	 * @var string|null
	 * @since 2.4.0
	 */
	protected $langTag;

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

		$this->langTag = TranslationHelper::getCurrent() ?? TranslationHelper::getDefault();
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
	 * After save content logging method
	 * This method adds a record to #__action_logs contains (message, date, context, user)
	 * Method is called right after the content is saved
	 *
	 * @param   Model\AfterSaveEvent  $event  The event instance.
	 *
	 * @return  void
	 *
	 * @since   3.9.0 // $context, $item, $isNew, $data
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

		$params = $this->getActionLogParams($context);
		$params->type_title = strtoupper($params->type_title);
		// Not found a valid content type, don't process further
		if ($params === null)
		{
			return;
		}

		list($option, $contentType) = explode('.', $params->type_alias);

		if (!$this->checkLoggable($option))
		{
			return;
		}

		if ($isNew)
		{
			$messageLanguageKey = $params->text_prefix . '_' . $params->type_title . '_ADDED';
		}
		else
		{
			$messageLanguageKey = $params->text_prefix . '_' . $params->type_title . '_UPDATED';
		}


		$id = empty($params->id_holder) ? 0 : $item->get($params->id_holder);

		$item->title = $this->getItemTitle($context, $data);

		$message = [
			'action'      => $isNew ? 'add' : 'update',
			'type'        => $params->text_prefix . '_TYPE_' . $params->type_title,
			'id'          => $id,
			'title'       => $item->title,
			'itemlink'    => 'index.php?option=com_swjprojects&task=' . $contentType . '.edit&id=' . $id,
		];

		if (!in_array($contentType, ['project', 'category', 'key']))
		{
			$message['projectTitle'] = $this->getProjectTitle($item->project_id);
			$message['projectLink'] = 'index.php?option=com_swjprojects&task=project.edit&id=' . $item->project_id;
		}

		if($context == 'com_swjprojects.key')
		{
			$project_ids = explode(',', $data['projects']);
			$projectTitles = [];
			foreach ($project_ids as $projectId)
			{
				$projectTitles[] = $this->getProjectTitle((int)$projectId);
			}

			$message['projects'] = implode(', ', $projectTitles);
		}

		$this->addLog([$message], $messageLanguageKey, $context);
	}

	/**
	 * Returns the action log params for the given context.
	 *
	 * @param   string  $context  The context of the action log
	 *
	 * @return  ?stdClass  The params
	 *
	 * @since   4.2.0
	 */
	private function getActionLogParams($context): ?stdClass
	{
		$component = $this->getApplication()->bootComponent('actionlogs');

		if (!$component instanceof MVCFactoryServiceInterface)
		{
			return null;
		}

		return $component->getMVCFactory()->createModel('ActionlogConfig', 'Administrator')->getLogContentTypeParams($context);
	}

	/**
	 * Function to check if a component is loggable or not
	 *
	 * @param   string  $extension  The extension that triggered the event
	 *
	 * @return  boolean
	 *
	 * @since   3.9.0
	 */
	protected function checkLoggable(string $extension): bool
	{
		return in_array($extension, $this->loggableExtensions);
	}

	/**
	 * @param string $context
	 * @param array $data Item data
	 *
	 * @return string
	 *
	 * @since version
	 */
	protected function getItemTitle($context, $data): string
	{
		$title = '';
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
				$title = $data['translates'][$this->langTag]['title'];

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

		return $project->translates[$this->langTag]->title;
	}

	/**
	 * After delete content logging method
	 * This method adds a record to #__action_logs contains (message, date, context, user)
	 * Method is called right after the content is deleted
	 *
	 * @param   Model\AfterDeleteEvent  $event  The event instance.
	 *
	 * @return  void
	 *
	 * @since   3.9.0
	 */
	public function onContentAfterDelete(Model\AfterDeleteEvent $event): void
	{
		$context = $event->getContext();
		$article = $event->getItem();
		$option  = $this->getApplication()->getInput()->get('option');

		if (!$this->checkLoggable($option))
		{
			return;
		}

		$params = $this->getActionLogParams($context);

		// Not found a valid content type, don't process further
		if ($params === null)
		{
			return;
		}

		// If the content type has its own language key, use it, otherwise, use default language key
		if ($this->getApplication()->getLanguage()->hasKey(strtoupper($params->text_prefix . '_' . $params->type_title . '_DELETED')))
		{
			$messageLanguageKey = $params->text_prefix . '_' . $params->type_title . '_DELETED';
		}
		else
		{
			$messageLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_DELETED';
		}

		$id = empty($params->id_holder) ? 0 : $article->get($params->id_holder);

		$message = [
			'action' => 'delete',
			'type'   => $params->text_prefix . '_TYPE_' . $params->type_title,
			'id'     => $id,
			'title'  => $article->get($params->title_holder),
		];

		$this->addLog([$message], $messageLanguageKey, $context);
	}

	/**
	 * On content change status logging method
	 * This method adds a record to #__action_logs contains (message, date, context, user)
	 * Method is called when the status of the article is changed
	 *
	 * @param   Model\AfterChangeStateEvent  $event  The event instance.
	 *
	 * @return  void
	 *
	 * @since   3.9.0
	 */
	public function onContentChangeState(Model\AfterChangeStateEvent $event): void
	{
		$context = $event->getContext();
		$pks     = $event->getPks();
		$value   = $event->getValue();
		$option  = $this->getApplication()->getInput()->getCmd('option');

		if (!$this->checkLoggable($option))
		{
			return;
		}

		$params = $this->getActionLogParams($context);

		// Not found a valid content type, don't process further
		if ($params === null)
		{
			return;
		}

		list(, $contentType) = explode('.', $params->type_alias);

		switch ($value)
		{
			case 0:
				$messageLanguageKey = $params->text_prefix . '_' . $params->type_title . '_UNPUBLISHED';
				$defaultLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_UNPUBLISHED';
				$action             = 'unpublish';
				break;
			case 1:
				$messageLanguageKey = $params->text_prefix . '_' . $params->type_title . '_PUBLISHED';
				$defaultLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_PUBLISHED';
				$action             = 'publish';
				break;
			case -2:
				$messageLanguageKey = $params->text_prefix . '_' . $params->type_title . '_TRASHED';
				$defaultLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_TRASHED';
				$action             = 'trash';
				break;
			default:
				$messageLanguageKey = '';
				$defaultLanguageKey = '';
				$action             = '';
				break;
		}

		// If the content type doesn't have its own language key, use default language key
		if (!$this->getApplication()->getLanguage()->hasKey($messageLanguageKey))
		{
			$messageLanguageKey = $defaultLanguageKey;
		}

		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
			->select($db->quoteName([$params->title_holder, $params->id_holder]))
			->from($db->quoteName($params->table_name))
			->whereIn($db->quoteName($params->id_holder), ArrayHelper::toInteger($pks));
		$db->setQuery($query);

		try
		{
			$items = $db->loadObjectList($params->id_holder);
		}
		catch (RuntimeException $e)
		{
			$items = [];
		}

		$messages = [];

		foreach ($pks as $pk)
		{
			$message = [
				'action'   => $action,
				'type'     => $params->text_prefix . '_TYPE_' . $params->type_title,
				'id'       => $pk,
				'title'    => $items[$pk]->{$params->title_holder},
				'itemlink' => ActionlogsHelper::getContentTypeLink($option, $contentType, $pk, $params->id_holder, null),
			];

			$messages[] = $message;
		}

		$this->addLog($messages, $messageLanguageKey, $context);
	}

}

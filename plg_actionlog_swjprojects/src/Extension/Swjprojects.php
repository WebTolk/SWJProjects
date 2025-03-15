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

use Exception;
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
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use stdClass;
use function defined;
use function explode;
use function in_array;
use function strtoupper;

defined('_JEXEC') or die;

/**
 * SW JProjects User Actions Logging Plugin.
 *
 * @since  3.9.0
 * @todo   implements SubscriberInterface when Joomla 6 will be released.
 */
final class Swjprojects extends ActionLogPlugin
{
	use DatabaseAwareTrait;
	use UserFactoryAwareTrait;

	/**
	 * Available contexts
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
	 * Map context to database tables with translations
	 *
	 * @var array
	 * @since 2.4.0
	 */
	protected $translateTables = [
		'com_swjprojects.document' => '#__swjprojects_translate_documentation',
		'com_swjprojects.documentation' => '#__swjprojects_translate_documentation',
		'com_swjprojects.project' => '#__swjprojects_translate_projects',
		'com_swjprojects.projects' => '#__swjprojects_translate_projects',
		'com_swjprojects.key' => null,
		'com_swjprojects.keys' => null,
		'com_swjprojects.version' => '#__swjprojects_versions',
		'com_swjprojects.versions' => '#__swjprojects_versions',
		'com_swjprojects.category' => '#__swjprojects_translate_categories',
		'com_swjprojects.categories' => '#__swjprojects_translate_categories',
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
				$title = $data['translates'][$this->langTag]['title'] ?? '';

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
				->where($db->quoteName('language') . ' = ' . $db->quote($this->langTag));

			$data['translates'][$this->langTag]['title'] = $db->setQuery($query)->loadResult();
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
	 * @param   Model\AfterChangeStateEvent  $event  The event instance.
	 *
	 * @return  void
	 *
	 * @since   3.9.0
	 * @todo use Model\AfterChangeStateEvent $event when Joomla 6 will be released
	 */
	public function onContentChangeState($context, $pks, $value): void
	{

//		$context = $event->getContext();
//		$pks     = $event->getPks();
//		$value   = $event->getValue();
		$option  = $this->getApplication()->getInput()->getCmd('option');

		if (!$this->checkLoggable($option))
		{
			return;
		}

		list(, $contentType) = explode('.', $context);

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
		if(in_array($context, ['com_swjprojects.key', 'com_swjprojects.keys']))
		{
			foreach ($pks as $pk)
			{
				$keyData = new \stdClass();
				$keyData->id = $pk;
				$keyData->title = $pk;
				$items[$pk] = $keyData;
			}
		} else {
			if(in_array($context, ['com_swjprojects.version', 'com_swjprojects.versions']))
			{
				$query->select(['CONCAT(CASE WHEN a.hotfix != 0 THEN CONCAT(a.major, ".", a.minor, ".", a.patch,".", a.hotfix) ELSE CONCAT(a.major, ".", a.minor, ".", a.patch) END) as title', 'a.id', 'a.project_id']);
			} else {
				$query->select($db->quoteName(['a.title', 'a.id']))
					->where($db->quoteName('a.language').' = '.$db->quote($this->langTag));
			}

			if(in_array($context, ['com_swjprojects.document', 'com_swjprojects.documentation'])) {
				$query->select($db->quoteName('doc.project_id','project_id'));
				$query->leftJoin($db->quoteName('#__swjprojects_documentation', 'doc'),$db->quoteName('a.id').' = '.$db->quoteName('doc.id'));
			}
			$query->from($db->quoteName($this->translateTables[$context],'a'));

			$query->whereIn($db->quoteName('a.id'), ArrayHelper::toInteger($pks));

			$db->setQuery($query);

			try
			{
				$items = $db->loadObjectList('id');
			}
			catch (Exception $e)
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
				$message['projectLink'] = 'index.php?option=com_swjprojects&task=project.edit&id=' . $items[$pk]->project_id;
			}

			$messages[] = $message;
		}

		$this->addLog($messages, $messageLanguageKey, $context);
	}

}

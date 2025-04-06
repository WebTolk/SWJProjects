<?php
/**
 * @package       SW JProjects
 * @version       2.4.0.1
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
 */

namespace Joomla\Plugin\Content\Swjprojects\Extension;

use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\Content\ContentPrepareEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Component\SWJProjects\Site\Helper\RouteHelper;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use function defined;
use function explode;
use function file_exists;
use function ob_get_clean;
use function ob_start;
use function preg_match_all;
use function property_exists;
use function str_replace;
use function strpos;

defined('_JEXEC') or die('Restricted access');

final class Swjprojects extends CMSPlugin implements SubscriberInterface
{
	use DatabaseAwareTrait;

	/**
	 * If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  3.9.0
	 */
	protected $autoloadLanguage = true;

	protected $allowLegacyListeners = false;

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onContentPrepare' => 'onContentPrepare',
		];
	}

	/**
	 * Plugin that change short code to project data with specified layout
	 *
	 * @param   string   $context     The context of the content being passed to the plugin.
	 * @param   object   $project     The project object.  Note $project->text is also available
	 * @param   mixed    $params      The project params
	 * @param   integer  $limitstart  The 'page' number
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */

	public function onContentPrepare(ContentPrepareEvent $event)
	{

		// Don't run if in the API Application
		// Don't run this plugin when the content is being indexed
		if (!$this->getApplication()->isClient('site') || $event->getContext() === 'com_finder.indexer')
		{
			return;
		}

		// Get content item
		$project = $event->getItem();

		// If the item does not have a text property there is nothing to do
		if (!property_exists($project, 'text'))
		{
			return;
		}

		//Проверка есть ли строка замены в контенте
		if (strpos($project->text, 'swjproject') === false)
		{
			return;
		}


		$regex = '/{swjprojects\s(.*?)}/i';
		preg_match_all($regex, $project->text, $short_codes);

		$i                 = 0;
		$short_code_params = [];

		foreach ($short_codes[1] as $short_code)
		{

			$settings = explode(" ", $short_code);

			foreach ($settings as $param)
			{
				$param                        = explode("=", $param);
				$short_code_params[$param[0]] = $param[1];

			}

			if (!empty($short_code_params["project_id"]))
			{

				$html = '';

				$tmpl = (!empty($short_code_params["tmpl"]) ? $short_code_params["tmpl"] : 'default');

				try
				{
					$insert_project = $this->getProject((int) $short_code_params["project_id"]);


					if ($insert_project)
					{

						ob_start();
						if (file_exists(JPATH_SITE . '/plugins/content/swjprojects/tmpl/' . $tmpl . '.php'))
						{

							require JPATH_SITE . '/plugins/content/swjprojects/tmpl/' . $tmpl . '.php';
						}
						else
						{
							require JPATH_SITE . '/plugins/content/swjprojects/tmpl/default.php';
						}

						$html = ob_get_clean();

					}
				}
				catch (Exception $e)
				{

				}

				$project->text = str_replace($short_codes[0][$i], $html, $project->text);

			}
			else
			{
				return;
			}
			$i++;
		}

		$project = $this->doSefLink($project);

	}

	/**
	 * @param   int  $pk  project id
	 *
	 * @return bool|object
	 *
	 * @throws Exception
	 * @since 2.0.1
	 * @see   \Joomla\Component\SWJProjects\Site\Model\ProjectModel
	 */
	private function getProject(int $pk)
	{
		$model  = $this->getApplication()
			->bootComponent('com_swjprojects')
			->getMVCFactory()
			->createModel('Project', 'Site', ['ignore_request' => true]);
		$params = ComponentHelper::getParams('com_swjprojects');
		$model->setState('params', $params);
		$project = $model->getItem($pk);

		return $project;
	}

	/**
	 * Do SEF links from index.php?option=com_swjprojects.....
	 *
	 * @param $project
	 *
	 * @return mixed
	 *
	 * @since 2.0.0
	 */
	private function doSefLink($project)
	{
		$prefix = $this->getApplication()->getDocument()->getType() === 'feed' ? Uri::root() : '';

		// Replace index.php URI by SEF URI.
		if (strpos($project->text, 'href="' . $prefix . 'index.php?option=com_swjprojects') !== false)
		{
			preg_match_all('#href="' . $prefix . 'index.php\?option=com_swjprojects([^"]+)"#m', $project->text, $matches);
			$i = 0;
			foreach ($matches[1] as $urlQueryString)
			{
				$uri  = new Uri(Uri::root() . '/index.php?option=com_swjprojects' . $urlQueryString);
				$view = $uri->getVar('view');

				if ($view == 'project')
				{

					$project_id = $uri->getVar('id');
					if ($project_id && strpos($project_id, ':') !== false)
					{
						$id         = explode(':', $project_id);
						$project_id = $id[0];
					}

					$cat_id = $uri->getVar('catid');
					if ($cat_id && strpos($cat_id, ':') !== false)
					{
						$id         = explode(':', $cat_id);
						$cat_id = $id[0];
					}

					$project->text = str_replace(
						$matches[0][$i],
						'href="' . $prefix . Route::_(RouteHelper::getProjectRoute($project_id,$cat_id)) . '"',
						$project->text
					);
				}
			}

		}

		return $project;
	}
}

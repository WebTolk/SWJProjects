<?php
/**
 * @package    SW JProjects
 * @version       2.3.0
 * @Author        Sergey Tolkachyov, https://web-tolk.ru
 * @сopyright  Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @since         1.0.0
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Cache\Cache;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Helper\LibraryHelper;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Component\SWJProjects\Administrator\Helper\KeysHelper;
use Joomla\Database\DatabaseDriver;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\CMS\Version;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\Path;
use Joomla\Registry\Registry;

return new class () implements ServiceProviderInterface {
	public function register(Container $container)
	{
		$container->set(InstallerScriptInterface::class, new class ($container->get(AdministratorApplication::class)) implements InstallerScriptInterface {

			/**
			 * The application object
			 *
			 * @var  AdministratorApplication
			 *
			 * @since  1.0.0
			 */
			protected AdministratorApplication $app;

			/**
			 * The Database object.
			 *
			 * @var   DatabaseDriver
			 *
			 * @since  1.0.0
			 */
			protected DatabaseDriver $db;

			/**
			 * Minimum Joomla version required to install the extension.
			 *
			 * @var  string
			 *
			 * @since  1.0.0
			 */
			protected string $minimumJoomla = '4.2.7';

			/**
			 * Minimum PHP version required to install the extension.
			 *
			 * @var  string
			 *
			 * @since  1.0.0
			 */
			protected string $minimumPhp = '7.4';

			/**
			 * Constructor.
			 *
			 * @param   AdministratorApplication  $app  The application object.
			 *
			 * @since 1.0.0
			 */
			public function __construct(AdministratorApplication $app)
			{
				$this->app = $app;
				$this->db  = Factory::getContainer()->get('DatabaseDriver');
			}

			/**
			 * This method is called after a component is installed.
			 *
			 * @param   \stdClass  $installer  - Parent object calling this method.
			 *
			 * @return void
			 */
			public function install(InstallerAdapter $adapter): bool
			{

				return true;

			}

			/**
			 * Function called after the extension is uninstalled.
			 *
			 * @param   InstallerAdapter  $adapter  The adapter calling this method
			 *
			 * @return  boolean  True on success
			 *
			 * @since   1.0.0
			 */
			public function uninstall(InstallerAdapter $adapter): bool
			{
				return true;
			}

			/**
			 * Function called after the extension is updated.
			 *
			 * @param   InstallerAdapter  $adapter  The adapter calling this method
			 *
			 * @return  boolean  True on success
			 *
			 * @since   1.0.0
			 */
			public function update(InstallerAdapter $adapter): bool
			{

				return true;

			}

			/**
			 * Function called before extension installation/update/removal procedure commences.
			 *
			 * @param   string            $type     The type of change (install or discover_install, update, uninstall)
			 * @param   InstallerAdapter  $adapter  The adapter calling this method
			 *
			 * @return  boolean  True on success
			 *
			 * @since   1.0.0
			 */
			public function preflight(string $type, InstallerAdapter $adapter): bool
			{

				return true;

			}


			/**
			 * Function called after extension installation/update/removal procedure commences.
			 *
			 * @param   string            $type     The type of change (install or discover_install, update, uninstall)
			 * @param   InstallerAdapter  $adapter  The adapter calling this method
			 *
			 * @return  boolean  True on success
			 *
			 * @since   1.0.0
			 */
			public function postflight(string $type, InstallerAdapter $adapter): bool
			{
				$actionLogsParams = $this->getActionlogsComponentParams();
				$loggableExtensions = $actionLogsParams->get('loggable_extensions', []);
				$loggableExtensions[] = 'com_swjprojects';
				$actionLogsParams->set('loggable_extensions', $loggableExtensions);
				$this->setActionlogsComponentParams($actionLogsParams);
				return true;
			}


			/**
			 * Method to get Action logs component params.
			 *
			 * @return  Registry  Component params registry.
			 *
			 * @since  2.4.0
			 */
			protected function getActionlogsComponentParams(): Registry
			{
				$db    = $this->db;
				$query = $db->getQuery(true)
					->select('params')
					->from('#__extensions')
					->where($db->quoteName('element') . ' = ' . $db->quote('com_actionlogs'));

				return new Registry($db->setQuery($query)->loadResult());
			}

			/**
			 * Method to get Action logs component params.
			 *
			 * @return  void
			 *
			 * @since  2.4.0
			 */
			protected function setActionlogsComponentParams(Registry $params): void
			{
				$db    = $this->db;
				$actionLogsParams = new \stdClass();
				$actionLogsParams->element = 'com_actionlogs';
				$actionLogsParams->params = $params->toString();

				$db->updateObject('#__extensions', $actionLogsParams, ['element']);
			}
		});
	}
};

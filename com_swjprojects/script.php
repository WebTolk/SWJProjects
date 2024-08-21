<?php
/**
 * @package    SW JProjects
 * @version       2.0.1
 * @Author        Sergey Tolkachyov, https://web-tolk.ru
 * @сopyright  Copyright (c) 2018 - 2024 Sergey Tolkachyov. All rights reserved.
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
				// Remove layouts
				$this->removeLayouts($adapter->getParent()->getManifest()->layouts);
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
				if ($type != 'uninstall')
				{
					// Parse layouts
					$this->parseLayouts($adapter->getParent()->getManifest()->layouts, $adapter->getParent());

					// Check databases
					$this->checkTables($adapter);

					// Check root category
					$this->checkRootCategory('#__swjprojects_categories');

					// Check files folder
					$this->checkFilesFolder();

					// Check images folder
					$this->checkImagesFolder();
				}

				return true;
			}


			/**
			 * Method to parse through a layout element of the installation manifest and take appropriate action.
			 *
			 * @param   SimpleXMLElement  $element    The XML node to process.
			 * @param   InstallerAdapter  $installer  Installer calling object.
			 *
			 * @return  boolean  True on success.
			 *
			 * @since  1.3.0
			 */
			public function parseLayouts(SimpleXMLElement $element, $installer)
			{
				if (!$element || !count($element->children()))
				{
					return false;
				}

				// Get destination
				$folder      = ((string) $element->attributes()->destination) ? '/' . $element->attributes()->destination : null;
				$destination = Path::clean(JPATH_ROOT . '/layouts' . $folder);

				// Get source
				$folder = (string) $element->attributes()->folder;
				$source = ($folder && file_exists($installer->getPath('source') . '/' . $folder)) ?
					$installer->getPath('source') . '/' . $folder : $installer->getPath('source');

				// Prepare files
				$copyFiles = array();
				foreach ($element->children() as $file)
				{
					$path['src']  = Path::clean($source . '/' . $file);
					$path['dest'] = Path::clean($destination . '/' . $file);

					// Is this path a file or folder?
					$path['type'] = $file->getName() === 'folder' ? 'folder' : 'file';
					if (basename($path['dest']) !== $path['dest'])
					{
						$newdir = dirname($path['dest']);
						if (!Folder::create($newdir))
						{
							Log::add(Text::sprintf('JLIB_INSTALLER_ERROR_CREATE_DIRECTORY', $newdir), Log::WARNING, 'jerror');

							return false;
						}
					}

					$copyFiles[] = $path;
				}

				return $installer->copyFiles($copyFiles);
			}

			/**
			 * Method to create root category if don't exist.
			 *
			 * @param   string  $table  Table name.
			 *
			 * @since  1.0.0
			 */
			protected function checkRootCategory($table = null)
			{
				$db = $this->db;

				// Get base categories
				$query = $db->getQuery(true)
					->select('id')
					->from($table)
					->where('id = 1');
				$db->setQuery($query);

				// Add root in not found
				if (empty($db->loadResult()))
				{
					$root            = new \stdClass();
					$root->id        = 1;
					$root->parent_id = 0;
					$root->lft       = 0;
					$root->rgt       = 1;
					$root->level     = 0;
					$root->path      = '';
					$root->alias     = 'root';
					$root->state     = 1;
					$root->params    = '';

					$db->insertObject($table, $root);
				}
			}

			/**
			 * Method to create database tables in not exist.
			 *
			 * @param   InstallerAdapter  $adapter  Parent object calling object.
			 *
			 * @since  1.0.0
			 */
			protected function checkTables($adapter)
			{
				if ($sql = file_get_contents($adapter->getParent()->getPath('extension_administrator')
					. '/sql/install.mysql.utf8.sql'))
				{
					$db = $this->db;

					foreach ($db->splitSql($sql) as $query)
					{
						$db->setQuery($db->convertUtf8mb4QueryToUtf8($query));
						try
						{
							$db->execute();
						}
						catch (\Exception $e)
						{
							Log::add(Text::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $e->getMessage()), Log::WARNING, 'jerror');
						}
					}
				}
			}

			/**
			 * Method to create files folder if don't exist.
			 *
			 * @throws  Exception
			 *
			 * @since  1.0.0
			 */
			protected function checkFilesFolder()
			{
				$params         = $this->getComponentParams();
				$standardFolder = Path::clean(JPATH_ROOT . '/' . 'swjprojects');
				$paramsFolder   = $params->get('files_folder');
				$folder         = ($paramsFolder) ? Path::clean(rtrim($paramsFolder, '/')) : $standardFolder;
				$setParams      = (empty($paramsFolder) || $folder !== $paramsFolder);

				// Check folder exist
				if (!Folder::exists($folder))
				{
					// Set standard folder
					if (!Folder::create($folder) && $folder !== $standardFolder)
					{
						$folder    = $standardFolder;
						$setParams = true;

						Factory::getApplication()->enqueueMessage(
							Text::sprintf('COM_SWJPROJECTS_SET_STANDARD_FILES_FOLDER', $folder), 'warning'
						);

						if (!Folder::exists($folder))
						{
							Folder::create($folder);
						}
					}
				}

				// Set files_folder param
				if ($setParams)
				{
					$params->set('files_folder', $folder);

					$component          = new \stdClass();
					$component->element = 'com_swjprojects';
					$component->params  = $params->toString();

					$this->db->updateObject('#__extensions', $component, array('element'));
				}
			}

			/**
			 * Method to create images folder if don't exist.
			 *
			 * @throws  Exception
			 *
			 * @since  1.3.0
			 */
			protected function checkImagesFolder()
			{
				$params         = $this->getComponentParams();
				$standardFolder = 'images/swjprojects';
				$paramsFolder   = $params->get('images_folder');
				$folder         = ($paramsFolder) ? trim($paramsFolder, '/') : $standardFolder;
				$setParams      = (empty($paramsFolder) || $folder !== $paramsFolder);
				$path           = Path::clean(JPATH_ROOT . '/' . $folder);

				// Check folder exist
				if (!Folder::exists($path))
				{
					// Set standard folder
					if (!Folder::create($path) && $folder !== $standardFolder)
					{
						$folder    = $standardFolder;
						$path      = Path::clean(JPATH_ROOT . '/' . $folder);
						$setParams = true;

						Factory::getApplication()->enqueueMessage(
							Text::sprintf('COM_SWJPROJECTS_SET_STANDARD_IMAGES_FOLDER', $folder), 'warning'
						);

						if (!Folder::exists($path))
						{
							Folder::create($path);
						}
					}
				}

				// Set images_folder param
				if ($setParams)
				{
					$params->set('images_folder', $folder);

					$component          = new \stdClass();
					$component->element = 'com_swjprojects';
					$component->params  = $params->toString();

					$this->db->updateObject('#__extensions', $component, array('element'));
				}
			}




			/**
			 * Method to parse through a layouts element of the installation manifest and remove the files that were installed.
			 *
			 * @param   SimpleXMLElement  $element  The XML node to process.
			 *
			 * @return  boolean  True on success.
			 *
			 * @since  1.3.0
			 */
			protected function removeLayouts(SimpleXMLElement $element)
			{
				if (!$element || !count($element->children()))
				{
					return false;
				}

				// Get the array of file nodes to process
				$files = $element->children();

				// Get source
				$folder = ((string) $element->attributes()->destination) ? '/' . $element->attributes()->destination : null;
				$source = Path::clean(JPATH_ROOT . '/layouts' . $folder);

				// Process each file in the $files array (children of $tagName).
				foreach ($files as $file)
				{
					$path = Path::clean($source . '/' . $file);

					// Actually delete the files/folders
					if (is_dir($path))
					{
						$val = Folder::delete($path);
					}
					else
					{
						$val = File::delete($path);
					}

					if ($val === false)
					{
						Log::add('Failed to delete ' . $path, Log::WARNING, 'jerror');

						return false;
					}
				}

				if (!empty($folder))
				{
					Folder::delete($source);
				}

				return true;
			}

			/**
			 * Method to get component params.
			 *
			 * @return  Registry  Component params registry.
			 *
			 * @since  1.3.1
			 */
			protected function getComponentParams()
			{
				$db    = $this->db;
				$query = $db->getQuery(true)
					->select('params')
					->from('#__extensions')
					->where($db->quoteName('element') . ' = ' . $db->quote('com_swjprojects'));

				return new Registry($db->setQuery($query)->loadResult());
			}


		});
	}
};
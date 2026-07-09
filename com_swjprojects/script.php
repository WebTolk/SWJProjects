<?php

/**
 * @package       SW JProjects
 * @version       2.6.2
 * @Author        Sergey Tolkachyov
 * @copyright  Copyright (c) 2018 - 2026 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link          https://web-tolk.ru
 * @since         1.0.0
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Cache\Cache;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Version;
use Joomla\Database\DatabaseDriver;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;
use Joomla\Registry\Registry;

return new class () implements ServiceProviderInterface {
	public function register(Container $container)
	{
		$container->set(InstallerScriptInterface::class, new class ($container->get(AdministratorApplication::class)) extends InstallerScript implements InstallerScriptInterface {
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
			 * Installed component version captured before Joomla overwrites manifest metadata on update.
			 *
			 * @var  string|null
			 *
			 * @since  2.7.0
			 */
			protected ?string $installedComponentVersionBeforeUpdate = null;

			/**
			 * Component param name used to store shared link type descriptors.
			 *
			 * @var  string
			 *
			 * @since  2.7.0
			 */
			protected const LINK_TYPES_PARAM = 'link_types';


			/**
			 * Minimum Joomla version required to install the extension.
			 *
			 * @var  string
			 *
			 * @since  1.0.0
			 */
			protected $minimumJoomla = '4.2.7';

			/**
			 * Minimum PHP version required to install the extension.
			 *
			 * @var  string
			 *
			 * @since  1.0.0
			 */
			protected $minimumPhp = '7.4';

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
				$this->db = Factory::getContainer()->get('DatabaseDriver');
				$this->extension = 'swjprojects';
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
				$installedVersion = $this->getInstalledComponentVersion();

				if (!$installedVersion || version_compare($installedVersion, '2.7.0', '<')) {
					// Fill defaults and migrate legacy project links on 2.6.2 -> 2.7.0 upgrade path.
					$this->checkLinkTypes();
					$this->migrateProjectLinks();
				}
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
			public function preflight($type, $adapter): bool
			{
				if ($type === 'update') {
					$this->installedComponentVersionBeforeUpdate = $this->readInstalledComponentVersionFromFilesystem();
				}

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
				if ($type != 'uninstall') {
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
					// Check shared link types
					$this->checkLinkTypes();
					// Check dashboard menu module
					$this->checkDashboardMenu();

					// Check dashboard link metadata on the component administrator menu item
					$this->checkDashboardMenuLink();
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
				if (!$element || !count($element->children())) {
					return false;
				}

				// Get destination
				$folder = ((string) $element->attributes()->destination) ? '/' . $element->attributes()->destination : null;
				$destination = Path::clean(JPATH_ROOT . '/layouts' . $folder);

				// Get source
				$folder = (string) $element->attributes()->folder;
				$source = ($folder && file_exists($installer->getPath('source') . '/' . $folder)) ?
					$installer->getPath('source') . '/' . $folder : $installer->getPath('source');

				// Prepare files
				$copyFiles = [];
				foreach ($element->children() as $file) {
					$path['src'] = Path::clean($source . '/' . $file);
					$path['dest'] = Path::clean($destination . '/' . $file);

					// Is this path a file or folder?
					$path['type'] = $file->getName() === 'folder' ? 'folder' : 'file';
					if (basename($path['dest']) !== $path['dest']) {
						$newdir = dirname($path['dest']);
						if (!Folder::create($newdir)) {
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
				if (empty($db->loadResult())) {
					$root = new \stdClass();
					$root->id = 1;
					$root->parent_id = 0;
					$root->lft = 0;
					$root->rgt = 1;
					$root->level = 0;
					$root->path = '';
					$root->alias = 'root';
					$root->state = 1;
					$root->params = '';

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
					. '/sql/install.mysql.utf8.sql')) {
					$db = $this->db;

					foreach ($db->splitSql($sql) as $query) {
						$db->setQuery($db->convertUtf8mb4QueryToUtf8($query));
						try {
							$db->execute();
						} catch (\Exception $e) {
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
				$params = $this->getComponentParams();
				$standardFolder = Path::clean(JPATH_ROOT . '/' . 'swjprojects');
				$paramsFolder = $params->get('files_folder');
				$folder = ($paramsFolder) ? Path::clean(rtrim($paramsFolder, '/')) : $standardFolder;
				$setParams = (empty($paramsFolder) || $folder !== $paramsFolder);

				// Check folder exist
				if (!\is_dir($folder)) {
					// Set standard folder
					if (!Folder::create($folder) && $folder !== $standardFolder) {
						$folder = $standardFolder;
						$setParams = true;

						Factory::getApplication()->enqueueMessage(
							Text::sprintf('COM_SWJPROJECTS_SET_STANDARD_FILES_FOLDER', $folder),
							'warning'
						);

						if (!\is_dir($folder)) {
							Folder::create($folder);
						}
					}
				}

				// Set files_folder param
				if ($setParams) {
					$params->set('files_folder', $folder);

					$component = new \stdClass();
					$component->element = 'com_swjprojects';
					$component->params = $params->toString();

					$this->db->updateObject('#__extensions', $component, ['element']);
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
				$params = $this->getComponentParams();
				$standardFolder = 'images/swjprojects';
				$paramsFolder = $params->get('images_folder');
				$folder = ($paramsFolder) ? trim($paramsFolder, '/') : $standardFolder;
				$setParams = (empty($paramsFolder) || $folder !== $paramsFolder);
				$path = Path::clean(JPATH_ROOT . '/' . $folder);

				// Check folder exist
				if (!\is_dir($path)) {
					// Set standard folder
					if (!Folder::create($path) && $folder !== $standardFolder) {
						$folder = $standardFolder;
						$path = Path::clean(JPATH_ROOT . '/' . $folder);
						$setParams = true;

						Factory::getApplication()->enqueueMessage(
							Text::sprintf('COM_SWJPROJECTS_SET_STANDARD_IMAGES_FOLDER', $folder),
							'warning'
						);

						if (!\is_dir($path)) {
							Folder::create($path);
						}
					}
				}

				// Set images_folder param
				if ($setParams) {
					$params->set('images_folder', $folder);

					$component = new \stdClass();
					$component->element = 'com_swjprojects';
					$component->params = $params->toString();

					$this->db->updateObject('#__extensions', $component, ['element']);
				}
			}

			/**
			 * Method to seed and unify shared link types in component params.
			 *
			 * @since  2.7.0
			 */
			protected function checkLinkTypes(): void
			{
				$params = $this->getComponentParams();
				$data = $params->toArray();
				$linkTypes = $this->normalizeLinkTypes($data[self::LINK_TYPES_PARAM] ?? []);
				$needsSave = false;

				if ($linkTypes === []) {
					$linkTypes = $this->normalizeLinkTypes($this->getDefaultLinkTypes());
					$data[self::LINK_TYPES_PARAM] = array_values($linkTypes);
					$needsSave = true;
				} elseif (($this->projectLinksToArray($data[self::LINK_TYPES_PARAM] ?? []) ?: []) !== array_values($linkTypes)) {
					$data[self::LINK_TYPES_PARAM] = array_values($linkTypes);
					$needsSave = true;
				}

				if (!$needsSave) {
					return;
				}

				$component = new \stdClass();
				$component->element = 'com_swjprojects';
				$component->params = (new Registry($data))->toString();

				$this->db->updateObject('#__extensions', $component, ['element']);
			}
			/**
			 * Method to migrate legacy project URL maps to typed project-link lists.
			 *
			 * @since  2.7.0
			 */
			protected function migrateProjectLinks(): void
			{
				$db = $this->db;
				$query = $db->getQuery(true)
					->select($db->quoteName(['id', 'urls']))
					->from($db->quoteName('#__swjprojects_projects'))
					->where($db->quoteName('urls') . ' IS NOT NULL')
					->where($db->quoteName('urls') . ' != ' . $db->quote(''));

				foreach ($db->setQuery($query)->loadObjectList() as $project) {
					$normalized = $this->normalizeProjectLinksToJson((string) $project->urls);

					if ($normalized === (string) $project->urls) {
						continue;
					}

					$row = new \stdClass();
					$row->id = (int) $project->id;
					$row->urls = $normalized;

					$db->updateObject('#__swjprojects_projects', $row, 'id');
				}
			}


			/**
			 * Get default shared link types matching the legacy fixed URL fields.
			 *
			 * @return  array
			 *
			 * @since  2.7.0
			 */
			protected function getDefaultLinkTypes(): array
			{
				return [
					[
						'code'       => 'demo',
						'title'      => 'COM_SWJPROJECTS_URLS_DEMO',
						'value_type' => 'url',
						'icon_class' => 'fas fa-external-link-alt',
					],
					[
						'code'       => 'support',
						'title'      => 'COM_SWJPROJECTS_URLS_SUPPORT',
						'value_type' => 'url',
						'icon_class' => 'fas fa-info-circle',
					],
					[
						'code'       => 'github',
						'title'      => 'COM_SWJPROJECTS_URLS_GITHUB',
						'value_type' => 'url',
						'icon_class' => 'fab fa-github-square',
					],
					[
						'code'       => 'jed',
						'title'      => 'COM_SWJPROJECTS_URLS_JED',
						'value_type' => 'url',
						'icon_class' => 'fab fa-joomla',
					],
					[
						'code'       => 'donate',
						'title'      => 'COM_SWJPROJECTS_URLS_DONATE',
						'value_type' => 'url',
						'icon_class' => 'fas fa-donate',
					],
					[
						'code'       => 'documentation',
						'title'      => 'COM_SWJPROJECTS_URLS_DOCUMENTATION',
						'value_type' => 'url',
						'icon_class' => 'fas fa-file-alt',
					],
				];
			}

			/**
			 * Normalize raw link type subform data into code-keyed descriptors.
			 *
			 * @param   mixed  $types  Raw link types.
			 *
			 * @return  array
			 *
			 * @since  2.7.0
			 */
			protected function normalizeLinkTypes($types): array
			{
				$types = $this->projectLinksToArray($types);

				if (!is_array($types)) {
					return [];
				}

				$normalized = [];

				foreach ($types as $type) {
					if (is_object($type)) {
						$type = (array) $type;
					}

					if (!is_array($type)) {
						continue;
					}

					$code = $this->normalizeProjectLinkCode($type['code'] ?? '');

					if ($code === '') {
						continue;
					}

					$valueType = trim((string) ($type['value_type'] ?? 'url'));

					if (!in_array($valueType, ['url', 'email'], true)) {
						$valueType = 'url';
					}

					$normalized[$code] = [
						'code'       => $code,
						'title'      => $this->normalizeLinkTypeTitle($code, $type['title'] ?? ''),
						'value_type' => $valueType,
						'icon_class' => trim((string) ($type['icon_class'] ?? '')),
					];
				}

				return $normalized;
			}

			/**
			 * Normalize built-in link-type titles to language constants while keeping custom titles intact.
			 *
			 * @param   string  $code   Link type code.
			 * @param   mixed   $title  Raw title.
			 *
			 * @return  string
			 *
			 * @since  2.7.0
			 */
			protected function normalizeLinkTypeTitle(string $code, $title): string
			{
				$title = trim((string) $title);
				$defaultTitle = $this->getDefaultLinkTypeTitleConstant($code);

				if ($defaultTitle === '') {
					return $title !== '' ? $title : $code;
				}

				if ($title === '') {
					return $defaultTitle;
				}

				return $title;
			}

			/**
			 * Get the default language key for a built-in link type.
			 *
			 * @param   string  $code  Link type code.
			 *
			 * @return  string
			 *
			 * @since  2.7.0
			 */
			protected function getDefaultLinkTypeTitleConstant(string $code): string
			{
				return match ($code) {
					'demo'          => 'COM_SWJPROJECTS_URLS_DEMO',
					'support'       => 'COM_SWJPROJECTS_URLS_SUPPORT',
					'github'        => 'COM_SWJPROJECTS_URLS_GITHUB',
					'jed'           => 'COM_SWJPROJECTS_URLS_JED',
					'donate'        => 'COM_SWJPROJECTS_URLS_DONATE',
					'documentation' => 'COM_SWJPROJECTS_URLS_DOCUMENTATION',
					default         => '',
				};
			}

			/**
			 * Convert legacy project URLs and typed project-link rows into typed-list JSON.
			 *
			 * @param   mixed  $links  Raw project links.
			 *
			 * @return  string
			 *
			 * @since  2.7.0
			 */
			protected function normalizeProjectLinksToJson($links): string
			{
				$json = json_encode($this->normalizeProjectLinks($links), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

				return is_string($json) ? $json : '[]';
			}

			/**
			 * Normalize legacy fixed-key URL maps and typed project-link rows into one list shape.
			 *
			 * @param   mixed  $links  Raw project links.
			 *
			 * @return  array
			 *
			 * @since  2.7.0
			 */
			protected function normalizeProjectLinks($links): array
			{
				$links = $this->projectLinksToArray($links);

				if (!is_array($links)) {
					return [];
				}

				if ($this->containsTypedProjectLinkRows($links)) {
					return $this->normalizeProjectLinkRows($links);
				}

				$normalized = [];

				foreach ($links as $type => $value) {
					if (is_array($value) || is_object($value)) {
						continue;
					}

					$link = $this->normalizeProjectLink([
						'type'  => $type,
						'value' => $value,
					]);

					if ($link !== null) {
						$normalized[] = $link;
					}
				}

				return $normalized;
			}

			/**
			 * Check whether the URL payload already contains typed project-link rows.
			 *
			 * @param   array  $links  Raw project links.
			 *
			 * @return  bool
			 *
			 * @since  2.7.0
			 */
			protected function containsTypedProjectLinkRows(array $links): bool
			{
				foreach ($links as $link) {
					if (is_object($link)) {
						return true;
					}

					if (is_array($link)
						&& (array_key_exists('type', $link)
							|| array_key_exists('title', $link)
							|| array_key_exists('value', $link))) {
						return true;
					}
				}

				return false;
			}

			/**
			 * Normalize typed project-link rows.
			 *
			 * @param   array  $links  Raw typed project links.
			 *
			 * @return  array
			 *
			 * @since  2.7.0
			 */
			protected function normalizeProjectLinkRows(array $links): array
			{
				$normalized = [];

				foreach ($links as $link) {
					$link = $this->normalizeProjectLink($link);

					if ($link !== null) {
						$normalized[] = $link;
					}
				}

				return $normalized;
			}
			/**
			 * Normalize one project link row.
			 *
			 * @param   mixed  $link  Raw project link row.
			 *
			 * @return  array|null
			 *
			 * @since  2.7.0
			 */
			protected function normalizeProjectLink($link): ?array
			{
				if (is_object($link)) {
					$link = (array) $link;
				}

				if (!is_array($link)) {
					return null;
				}

				$type = $this->normalizeProjectLinkCode($link['type'] ?? '');
				$title = trim((string) ($link['title'] ?? ''));
				$value = trim((string) ($link['value'] ?? ''));

				if ($type === '' || $value === '') {
					return null;
				}

				return [
					'type'  => $type,
					'title' => $title,
					'value' => $value,
				];
			}

			/**
			 * Convert raw project links to an array when possible.
			 *
			 * @param   mixed  $links  Raw project links.
			 *
			 * @return  mixed
			 *
			 * @since  2.7.0
			 */
			protected function projectLinksToArray($links)
			{
				if ($links instanceof Registry) {
					return $links->toArray();
				}

				if (is_string($links)) {
					$links = trim($links);

					if ($links === '') {
						return [];
					}

					$decoded = json_decode($links, true);

					if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
						return $decoded;
					}

					return (new Registry($links))->toArray();
				}

				if (is_object($links)) {
					return (array) $links;
				}

				return $links;
			}

			/**
			 * Normalize project link type codes.
			 *
			 * @param   mixed  $code  Raw project link code.
			 *
			 * @return  string
			 *
			 * @since  2.7.0
			 */
			protected function normalizeProjectLinkCode($code): string
			{
				return preg_replace('/[^a-z0-9_-]/', '', strtolower(trim((string) $code)));
			}
			/**
			 * Method to create the SW JProjects dashboard submenu module if it does not exist.
			 *
			 * @since  2.7.0
			 */
			protected function checkDashboardMenu(): void
			{
				$dashboard = 'swjprojects';
				$position = 'cpanel-' . $dashboard;

				$db = $this->db;
				$query = $db->getQuery(true)
					->select('COUNT(*)')
					->from($db->quoteName('#__modules'))
					->where([
						$db->quoteName('module') . ' = ' . $db->quote('mod_submenu'),
						$db->quoteName('client_id') . ' = 1',
						$db->quoteName('position') . ' = :position',
					])
					->bind(':position', $position);

				if ((int) $db->setQuery($query)->loadResult() > 0) {
					return;
				}

				$this->addDashboardMenu($dashboard, 'swjprojects');
			}

			/**
			 * Method to add dashboard metadata to the component administrator menu item.
			 *
			 * @since  2.7.0
			 */
			protected function checkDashboardMenuLink(): void
			{
				$db = $this->db;
				$query = $db->getQuery(true)
					->select($db->quoteName(['id', 'params']))
					->from($db->quoteName('#__menu'))
					->where([
						$db->quoteName('client_id') . ' = 1',
						$db->quoteName('link') . ' = ' . $db->quote('index.php?option=com_swjprojects'),
					]);

				foreach ($db->setQuery($query)->loadObjectList() as $item) {
					$params = new Registry((string) $item->params);

					if ($params->get('dashboard') === 'swjprojects') {
						continue;
					}

					$params->set('dashboard', 'swjprojects');

					$menu = new \stdClass();
					$menu->id = (int) $item->id;
					$menu->params = $params->toString();

					$db->updateObject('#__menu', $menu, 'id');
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
				if (!$element || !count($element->children())) {
					return false;
				}

				// Get the array of file nodes to process
				$files = $element->children();

				// Get source
				$folder = ((string) $element->attributes()->destination) ? '/' . $element->attributes()->destination : null;
				$source = Path::clean(JPATH_ROOT . '/layouts' . $folder);

				// Process each file in the $files array (children of $tagName).
				foreach ($files as $file) {
					$path = Path::clean($source . '/' . $file);

					// Actually delete the files/folders
					if (is_dir($path)) {
						$val = Folder::delete($path);
					} else {
						$val = File::delete($path);
					}

					if ($val === false) {
						Log::add('Failed to delete ' . $path, Log::WARNING, 'jerror');

						return false;
					}
				}

				if (!empty($folder)) {
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
				$db = $this->db;
				$query = $db->getQuery(true)
					->select('params')
					->from('#__extensions')
					->where($db->quoteName('element') . ' = ' . $db->quote('com_swjprojects'));

				return new Registry($db->setQuery($query)->loadResult());
			}

			/**
			 * Get installed component version from manifest cache.
			 *
			 * @return  string|null  Installed version or null if unavailable.
			 *
			 * @since  2.7.0
			 */
			protected function getInstalledComponentVersion(): ?string
			{
				if ($this->installedComponentVersionBeforeUpdate !== null) {
					return $this->installedComponentVersionBeforeUpdate;
				}

				$db = $this->db;
				$query = $db->getQuery(true)
					->select('manifest_cache')
					->from($db->quoteName('#__extensions'))
					->where($db->quoteName('type') . ' = ' . $db->quote('component'))
					->where($db->quoteName('element') . ' = ' . $db->quote('com_swjprojects'));

				$manifestCache = (string) $db->setQuery($query)->loadResult();

				if ($manifestCache === '') {
					return null;
				}

				$manifest = json_decode($manifestCache, true);

				if (json_last_error() !== JSON_ERROR_NONE || !is_array($manifest) || empty($manifest['version'])) {
					return null;
				}

				return trim((string) $manifest['version']) ?: null;
			}

			/**
			 * Read the currently installed component version from the live administrator manifest file.
			 *
			 * On Joomla update routes this is the reliable old-version source during preflight, before
			 * storeExtension() rewrites `#__extensions.manifest_cache` with the incoming package version.
			 *
			 * @return  string|null  Installed version or null if unavailable.
			 *
			 * @since  2.7.0
			 */
			protected function readInstalledComponentVersionFromFilesystem(): ?string
			{
				$manifestPath = JPATH_ADMINISTRATOR . '/components/com_swjprojects/swjprojects.xml';

				if (!is_file($manifestPath)) {
					return null;
				}

				$manifest = simplexml_load_file($manifestPath);

				if ($manifest === false || empty($manifest->version)) {
					return null;
				}

				return trim((string) $manifest->version) ?: null;
			}
		});
	}
};

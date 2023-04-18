<?php
/*
 * @package    SW JProjects Component
 * @version    1.6.4
 * @author Septdir Workshop, <https://septdir.com>, Sergey Tolkachyov <https://web-tolk.ru>
 * @сopyright (c) 2018 - April 2023 Septdir Workshop, Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link https://septdir.com, https://web-tolk.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Log\Log;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

class com_swjprojectsInstallerScript
{
	/**
	 * Runs right after any installation action.
	 *
	 * @param   string            $type    Type of PostFlight action.
	 * @param   InstallerAdapter  $parent  Parent object calling object.
	 *
	 * @throws  Exception
	 *
	 * @since  1.0.0
	 */
	function postflight($type, $parent)
	{
		// Parse layouts
		$this->parseLayouts($parent->getParent()->getManifest()->layouts, $parent->getParent());

		// Check databases
		$this->checkTables($parent);

		// Check root category
		$this->checkRootCategory('#__swjprojects_categories');

		// Check files folder
		$this->checkFilesFolder();

		// Check images folder
		$this->checkImagesFolder();

		// Check key params
		$this->checkKeysParams();

		if ($type == 'update')
		{
			// Check hits column
			$this->checkHitsColumn();

			// Prepare projects images
			$this->prepareImagesColumn();

			// Remove router rudiments
			$this->removeRouterRudiments();
		}

		// Donate message
		Factory::getApplication()->enqueueMessage(LayoutHelper::render('components.swjprojects.message.donate'), '');
	}

	/**
	 * Method to parse through a layout element of the installation manifest and take appropriate action.
	 *
	 * @param   SimpleXMLElement  $element    The XML node to process.
	 * @param   Installer         $installer  Installer calling object.
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
		$db = Factory::getDbo();

		// Get base categories
		$query = $db->getQuery(true)
			->select('id')
			->from($table)
			->where('id = 1');
		$db->setQuery($query);

		// Add root in not found
		if (empty($db->loadResult()))
		{
			$root            = new stdClass();
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
	 * @param   InstallerAdapter  $parent  Parent object calling object.
	 *
	 * @since  1.0.0
	 */
	protected function checkTables($parent)
	{
		if ($sql = file_get_contents($parent->getParent()->getPath('extension_administrator')
			. '/sql/install.mysql.utf8.sql'))
		{
			$db = Factory::getDbo();

			foreach ($db->splitSql($sql) as $query)
			{
				$db->setQuery($db->convertUtf8mb4QueryToUtf8($query));
				try
				{
					$db->execute();
				}
				catch (JDataBaseExceptionExecuting $e)
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

			$component          = new stdClass();
			$component->element = 'com_swjprojects';
			$component->params  = $params->toString();

			Factory::getDbo()->updateObject('#__extensions', $component, array('element'));
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

			$component          = new stdClass();
			$component->element = 'com_swjprojects';
			$component->params  = $params->toString();

			Factory::getDbo()->updateObject('#__extensions', $component, array('element'));
		}
	}

	/**
	 * Method to check keys params and set defaults if don't exist.
	 *
	 * @throws  Exception
	 *
	 * @since  1.3.0
	 */
	protected function checkKeysParams()
	{
		$params = $this->getComponentParams();
		$update = false;
		JLoader::register('SWJProjectsHelperKeys',
			JPATH_ADMINISTRATOR . '/components/com_swjprojects/helpers/keys.php');

		// Length
		if (empty($params->get('key_length')))
		{
			$params->set('key_length', 16);
			$update = true;
		}

		// Characters
		if (empty($params->get('key_characters')))
		{
			$params->set('key_characters', implode(',', SWJProjectsHelperKeys::getCharacters()));
			$update = true;
		}

		// Master
		if (empty($params->get('key_master')))
		{
			$params->set('key_master', SWJProjectsHelperKeys::generateKey(128));
			$update = true;
		}

		// Update
		if ($update)
		{
			$component          = new stdClass();
			$component->element = 'com_swjprojects';
			$component->params  = $params->toString();

			Factory::getDbo()->updateObject('#__extensions', $component, array('element'));
		}
	}

	/**
	 * Method to create hits column if don't exist.
	 *
	 * @since  1.2.1
	 */
	protected function checkHitsColumn()
	{
		$db      = Factory::getDbo();
		$columns = $db->getTableColumns('#__swjprojects_projects');
		if (!isset($columns['hits']))
		{
			// Create hits column
			$db->setQuery('ALTER TABLE `#__swjprojects_projects` ADD `hits` INT(10) NOT NULL DEFAULT 0 AFTER `ordering`')
				->execute();

			// Create hits index
			$db->setQuery('ALTER TABLE `#__swjprojects_projects` ADD INDEX `idx_hits`(`hits`)')
				->execute();
		}
	}

	/**
	 * Method to move projects images and save gallery.
	 *
	 * @since  1.3.0
	 */
	protected function prepareImagesColumn()
	{
		$db      = Factory::getDbo();
		$table   = '#__swjprojects_translate_projects';
		$columns = $db->getTableColumns($table);

		// Check gallery column
		if (!isset($columns['gallery']))
		{
			$db->setQuery('ALTER TABLE `' . $table . '` ADD `gallery` MEDIUMTEXT NOT NULL AFTER `fulltext`;')->execute();
		}

		if (isset($columns['images']))
		{
			$query = $db->getQuery(true)
				->select(array('id', 'language', 'images'))
				->from($db->quoteName($table));
			$rows  = $db->setQuery($query)->loadObjectList();
			$root  = ComponentHelper::getParams('com_swjprojects')->get('images_folder');

			// Update projects
			foreach ($rows as $row)
			{
				// Check project folder
				$folder = Path::clean(JPATH_ROOT . '/' . $root . '/projects/' . $row->id . '/' . $row->language);
				if (!Folder::exists($folder))
				{
					Folder::create($folder);
				}

				if (!empty($row->images))
				{
					$registry    = new Registry($row->images);
					$row->images = '';

					// Copy icon
					if ($icon = $registry->get('icon'))
					{
						$src  = Path::clean(JPATH_ROOT . '/' . $icon);
						$dest = Path::clean($folder . '/icon.' . File::getExt($src));

						if (!File::exists($dest))
						{
							File::copy($src, $dest);
						}
					}

					// Copy cover
					if ($icon = $registry->get('cover'))
					{
						$src  = Path::clean(JPATH_ROOT . '/' . $icon);
						$dest = Path::clean($folder . '/cover.' . File::getExt($src));

						if (!File::exists($dest))
						{
							File::copy($src, $dest);
						}
					}

					// Copy and prepare gallery
					$gallery = array();
					if (!empty($registry->get('gallery')))
					{
						// Check folder
						JLoader::register('SWJProjectsHelperImages',
							JPATH_ADMINISTRATOR . '/components/com_swjprojects/helpers/images.php');
						$folder = Path::clean($folder . '/gallery');
						if (!Folder::exists($folder))
						{
							Folder::create($folder);
						}

						$names = array();
						foreach (ArrayHelper::fromObject($registry->get('gallery')) as $key => $image)
						{
							$src      = Path::clean(JPATH_ROOT . '/' . $image['image']);
							$text     = (isset($image['text'])) ? $image['text'] : '';
							$ordering = (int) str_replace('gallery', '', $key) + 1;

							// Prepare file name
							$name = SWJProjectsHelperImages::generateName();
							while (in_array($name, $names))
							{
								$name = SWJProjectsHelperImages::generateName();
							}
							$filename = $name . '.' . File::getExt($src);
							$dest     = Path::clean($folder . '/' . $filename);

							// Set to gallery
							$gallery[$name] = array(
								'text'     => $text,
								'ordering' => $ordering
							);

							// Copy image
							if (!File::exists($dest))
							{
								File::copy($src, $dest);
							}
						}
					}
					$gallery      = new Registry($gallery);
					$row->gallery = $gallery->toString('json', array('bitmask' => JSON_UNESCAPED_UNICODE));

					// Update row
					$db->updateObject($table, $row, array('id', 'language'));
				}
			}

			// Remove images column
			$db->setQuery('ALTER TABLE `' . $table . '` drop column `images`;')->execute();
		}
	}


	/**
	 * Method to remove route rudiments from menu items.
	 *
	 * @since  1.3.1
	 */
	protected function removeRouterRudiments()
	{
		// Remove key
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->update($db->quoteName('#__menu'))
			->where($db->quoteName('link') . ' LIKE ' . $db->quote('%com_swjprojects%'))
			->where($db->quoteName('link') . ' LIKE ' . $db->quote('%&key=1%'))
			->set($db->quoteName('link') . ' = REPLACE (' . $db->quoteName('link') . ','
				. $db->quote('&key=1') . ',' . $db->quote('') . ')');
		$db->setQuery($query)->execute();
	}

	/**
	 * This method is called after extension is uninstalled.
	 *
	 * @param   InstallerAdapter  $parent  Parent object calling object.
	 *
	 * @since  1.0.0
	 */
	public function uninstall($parent)
	{
		// Remove layouts
		$this->removeLayouts($parent->getParent()->getManifest()->layouts);
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
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('params')
			->from('#__extensions')
			->where($db->quoteName('element') . ' = ' . $db->quote('com_swjprojects'));

		return new Registry($db->setQuery($query)->loadResult());
	}
}
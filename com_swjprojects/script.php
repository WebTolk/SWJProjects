<?php
/**
 * @package    SW JProjects Component
 * @version    1.2.1
 * @author     Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2018 - 2019 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
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
use Joomla\CMS\Log\Log;

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

		// Check files folder
		$this->checkHitsColumn();
	}

	/**
	 * Method to parse through a layout element of the installation manifest and take appropriate action.
	 *
	 * @param   SimpleXMLElement  $element    The XML node to process.
	 * @param   Installer         $installer  Installer calling object.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since  __DEPLOY_VERSION__
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
		$params         = ComponentHelper::getParams('com_swjprojects');
		$standardFolder = Path::clean(JPATH_ROOT . '/' . 'swjprojects');
		$paramsFolder   = $params->get('files_folder');
		$folder         = ($paramsFolder) ? Path::clean($paramsFolder) : $standardFolder;
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
	 * @since  __DEPLOY_VERSION__
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
	 * This method is called when extension is updated.
	 *
	 * @param   InstallerAdapter  $parent  Parent object calling object.
	 *
	 * @since  1.0.1
	 */
	public function update($parent)
	{
		// Remove forgot js file
		$file = JPATH_ROOT . '/media/com_swjprojects/js/translate-switcher.min.min.js';
		if (File::exists($file))
		{
			File::delete($file);
		}
	}
}
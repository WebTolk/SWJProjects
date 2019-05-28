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
	 * @since  1.0.0
	 */
	function postflight($type, $parent)
	{
		// Install layouts
		$this->installLayouts($parent);

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
	 * Method to install/update extension layouts.
	 *
	 * @param   InstallerAdapter  $parent  Parent object calling object.
	 *
	 * @since  1.0.0
	 */
	protected function installLayouts($parent)
	{
		$root   = JPATH_ROOT . '/layouts';
		$source = $parent->getParent()->getPath('source');

		// Get attributes
		$attributes = $parent->getParent()->manifest->xpath('layouts');
		if (!is_array($attributes) || empty($attributes[0])) return;

		// Get destination
		$destination = (!empty($attributes[0]->attributes()->destination)) ?
			(string) $attributes[0]->attributes()->destination : false;
		if (!$destination) return;

		// Remove old layouts
		if (Folder::exists($root . '/' . trim($destination, '/')))
		{
			Folder::delete($root . '/' . trim($destination, '/'));
		}

		// Get folder
		$folder = (!empty($attributes[0]->attributes()->folder)) ? (string) $attributes[0]->attributes()->folder
			: 'layouts';
		if (!Folder::exists($source . '/' . trim($folder, '/'))) return;

		// Prepare src and dest
		$src  = $source . '/' . trim($folder, '/');
		$dest = $root . '/' . trim($destination, '/');

		// Check destination
		$path = $root;
		$dirs = explode('/', $destination);
		array_pop($dirs);

		if (!empty($dirs))
		{
			foreach ($dirs as $i => $dir)
			{
				$path .= '/' . $dir;
				if (!Folder::exists($path))
				{
					Folder::create($path);
				}
			}
		}

		// Move layouts
		Folder::move($src, $dest);
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
	 * @since  1.0.0
	 */
	protected function checkFilesFolder()
	{
		$params = ComponentHelper::getParams('com_swjprojects');

		// Set files_folder param
		if (!$folder = $params->get('files_folder'))
		{
			$folder = JPATH_ROOT . '/' . 'swjprojects';
			$params->set('files_folder', $folder);

			$component          = new stdClass();
			$component->element = 'com_swjprojects';
			$component->params  = $params->toString();

			Factory::getDbo()->updateObject('#__extensions', $component, array('element'));
		}

		// Create folder
		if (!Folder::exists($folder))
		{
			Folder::create($folder);
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
		// Uninstall layouts
		$this->uninstallLayouts($parent);
	}

	/**
	 * Method to uninstall extension layouts.
	 *
	 * @param   InstallerAdapter  $parent  Parent object calling object.
	 *
	 * @since  1.0.0
	 */
	protected function uninstallLayouts($parent)
	{
		$attributes = $parent->getParent()->manifest->xpath('layouts');
		if (!is_array($attributes) || empty($attributes[0])) return;

		$destination = (!empty($attributes[0]->attributes()->destination)) ?
			(string) $attributes[0]->attributes()->destination : false;
		if (!$destination) return;

		$dest = JPATH_ROOT . '/layouts/' . trim($destination, '/');

		if (Folder::exists($dest))
		{
			Folder::delete($dest);
		}
	}

	/**
	 * This method is called when extension is updated.
	 *
	 * @param   InstallerAdapter  $parent  Parent object calling object.
	 *
	 * @since  1.0.1
	 */
	function update($parent)
	{
		// Remove forgot js file
		$file = JPATH_ROOT . '/media/com_swjprojects/js/translate-switcher.min.min.js';
		if (File::exists($file))
		{
			File::delete($file);
		}
	}
}
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
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

class SWJProjectsHelperImages
{
	/**
	 * Single image.
	 *
	 * @var  array
	 *
	 * @since  1.3.0
	 */
	protected static $_image = array();

	/**
	 * Multiple images.
	 *
	 * @var  array
	 *
	 * @since  1.3.0
	 */
	protected static $_images = array();

	/**
	 * Images mime types.
	 *
	 * @var  array
	 *
	 * @since 1.3.0
	 */
	public static $mime_types = array('image/png', 'image/jpeg', 'image/gif', 'image/bmp', 'image/svg', 'image/svg+xml');

	/**
	 * Method to get the simple image.
	 *
	 * @param   string   $section   Component section selector (etc. projects).
	 * @param   integer  $pk        The id of the item.
	 * @param   string   $name      The name of the image file.
	 * @param   string   $language  The language of the image.
	 *
	 * @return  false|string  Simple image path string on success, false on failure.
	 *
	 * @since  1.3.0
	 */
	public static function getImage($section = null, $pk = null, $name = null, $language = null)
	{
		$language = (!empty($language)) ? $language : Factory::getLanguage()->getTag();
		if (empty($section) || empty($pk) || empty($name) || empty($language)) return false;

		// Check hash
		$hash = md5($section . '_' . $pk . '_' . $name . '_' . $language);
		if (!isset(self::$_image[$hash]))
		{
			$root   = ComponentHelper::getParams('com_swjprojects')->get('images_folder', 'images/swjprojects');
			$folder = $root . '/' . $section . '/' . $pk . '/' . $language;
			$path   = Path::clean(JPATH_ROOT . '/' . $folder);

			// Get file
			$file   = false;
			$filter = '^' . $name . '\.[a-zA-Z]*$';
			$files  = (Folder::exists($path)) ? Folder::files($path, $filter, false) : false;
			if ($files && !empty($files[0]))
			{
				$filename = $files[0];
				$file     = (self::checkImage($path . '/' . $filename)) ? $folder . '/' . $filename : false;
			}

			self::$_image[$hash] = $file;
		}

		return self::$_image[$hash];
	}

	/**
	 * Method to get the multiple images.
	 *
	 * @param   string    $section   Component section selector (etc. projects).
	 * @param   integer   $pk        The id of the item.
	 * @param   string    $folder    The name of the images folder.
	 * @param   Registry  $values    The images values array.
	 * @param   string    $language  The language of the image.
	 *
	 * @return false|object[] Multiple images array on success, false on failure.
	 *
	 * @since  1.3.0
	 */
	public static function getImages($section = null, $pk = null, $folder = null, $values = null, $language = null)
	{
		$language = (!empty($language)) ? $language : Factory::getLanguage()->getTag();
		if (empty($section) || empty($pk) || empty($folder) || empty($language)) return false;

		// Check hash
		$hash = md5($section . '_' . $pk . '_' . $folder . '_' . $language);
		if (!isset(self::$_images[$hash]))
		{
			$images = false;
			$values = ($values instanceof Registry) ? $values : new Registry($values);
			$values = $values->toArray();
			$root   = ComponentHelper::getParams('com_swjprojects')->get('images_folder', 'images/swjprojects');
			$folder = $root . '/' . $section . '/' . $pk . '/' . $language . '/' . $folder;
			$path   = Path::clean(JPATH_ROOT . '/' . $folder);

			// Get images
			$files = (Folder::exists($path)) ? Folder::files($path, null, false, true) : false;
			if ($files)
			{
				$ordering = count($values);
				$images   = array();
				foreach ($files as $file)
				{
					if (!self::checkImage($file)) continue;
					$filename = basename($file);
					$name     = File::stripExt($filename);
					$value    = (isset($values[$name])) ? $values[$name] : false;

					// Prepare image
					$image       = new stdClass();
					$image->file = $filename;
					$image->name = $name;
					$image->src  = $folder . '/' . $filename;
					$image->text = (!empty($value) && !empty($value['text'])) ? $value['text'] : '';

					// Set ordering
					$image->ordering = (!empty($value) && !empty($value['ordering'])) ? $value['ordering'] : 0;
					if (empty($image->ordering))
					{
						$ordering        = $ordering + 1;
						$image->ordering = $ordering;
					}
					$image->ordering = (int) $image->ordering;

					// Add to images
					$images[] = $image;
				}

				// Sort images array if don't empty
				$images = (!empty($images)) ? ArrayHelper::sortObjects($images, 'ordering', 1) : false;
			}

			self::$_images[$hash] = $images;
		};

		return self::$_images[$hash];
	}

	/**
	 * Check if file is image by mme type.
	 *
	 * @param   string  $image  Full path to image.
	 *
	 * @return  true|false  True on success, false on failure.
	 *
	 * @since  1.3.0
	 */
	public static function checkImage($image = '')
	{
		$image = Path::clean($image);
		if (function_exists('finfo_open'))
		{
			$finfo    = finfo_open(FILEINFO_MIME_TYPE);
			$mimetype = finfo_file($finfo, $image);
			finfo_close($finfo);
		}
		else
		{
			$mimetype = mime_content_type($image);
		}

		return in_array($mimetype, self::$mime_types);
	}
}
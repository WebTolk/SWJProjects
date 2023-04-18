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
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Uri\Uri;
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
	 * @param   bool     $absolute  Return absolute path.
	 * @param   bool     $reload    Reload cache.
	 *
	 * @return  false|string  Simple image path string on success, false on failure.
	 *
	 * @since  1.3.0
	 */
	public static function getImage($section = null, $pk = null, $name = null, $language = null, $absolute = false, $reload = false)
	{
		if (empty($section) || empty($pk) || empty($name) || empty($language)) return false;

		// Check hash
		$hash = md5($section . '_' . $pk . '_' . $name . '_' . $language . '_' . (int) $absolute);
		if (!isset(self::$_image[$hash]) || $reload)
		{
			$root    = ComponentHelper::getParams('com_swjprojects')->get('images_folder', 'images/swjprojects');
			$folder  = $root . '/' . $section . '/' . $pk . '/' . $language;
			$path    = Path::clean(JPATH_ROOT . '/' . $folder);
			$site    = rtrim(Uri::root(true) . '/', '/');
			$version = 'v=' . time();

			// Get file
			$file  = false;
			$filter = '^' . $name . '\.[a-zA-Z]*$';
			$files = (Folder::exists($path)) ? Folder::files($path, $filter, false) : false;
			if ($files && !empty($files[0]))
			{
				$filename = $files[0];
				if (self::checkImage($path . '/' . $filename))
				{
					$file = ($absolute) ? Path::clean($path . '/' . $filename)
						: $site . '/' . $folder . '/' . $filename . '?' . $version;
				}
			}

			self::$_image[$hash] = $file;
		}

		return self::$_image[$hash];
	}

	/**
	 * Method to delete the simple image.
	 *
	 * @param   string   $section   Component section selector (etc. projects).
	 * @param   integer  $pk        The id of the item.
	 * @param   string   $name      The name of the image file.
	 * @param   string   $language  The language of the image.
	 *
	 * @return  bool  True on success, false on failure.
	 *
	 * @since  1.3.0
	 */
	public static function deleteImage($section = null, $pk = null, $name = null, $language = null)
	{
		$image = self::getImage($section, $pk, $name, $language, true);

		return (!$image || File::delete($image));
	}

	/**
	 * Method to upload the simple image.
	 *
	 * @param   string   $section   Component section selector (etc. projects).
	 * @param   integer  $pk        The id of the item.
	 * @param   string   $name      The name of the image file.
	 * @param   string   $language  The language of the image.
	 * @param   array    $image     The upload image data.
	 *
	 * @return  bool|string  Simple image path string on success, false on failure.
	 *
	 * @since  1.3.0
	 */
	public static function uploadImage($section = null, $pk = null, $name = null, $language = null, $image = array())
	{
		if (empty($section) || empty($pk) || empty($name) || empty($language)) return false;

		// Check upload image
		if (empty($image) || empty($image['tmp_name']) || empty($image['name']) || !self::checkImage($image['tmp_name']))
		{
			return false;
		}

		// Delete current
		$current = self::getImage($section, $pk, $name, $language, true);
		if ($current && !File::delete($current))
		{
			return false;
		}

		$filename = $name . '.' . File::getExt($image['name']);
		$root     = ComponentHelper::getParams('com_swjprojects')->get('images_folder', 'images/swjprojects');
		$folder   = $root . '/' . $section . '/' . $pk . '/' . $language;
		$path     = Path::clean(JPATH_ROOT . '/' . $folder);

		// Check folder
		if (!Folder::exists($path) && !Folder::create($path))
		{
			return false;
		}

		// Upload image
		$src  = $image['tmp_name'];
		$dest = Path::clean($path . '/' . $filename);

		return (File::upload($src, $dest, false, true)) ?
			self::getImage($section, $pk, $name, $language, false, true) : false;
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
		if (empty($section) || empty($pk) || empty($folder) || empty($language)) return false;

		// Check hash
		$hash = md5($section . '_' . $pk . '_' . $folder . '_' . $language);
		if (!isset(self::$_images[$hash]))
		{
			$images  = false;
			$values  = ($values instanceof Registry) ? $values : new Registry($values);
			$values  = $values->toArray();
			$root    = ComponentHelper::getParams('com_swjprojects')->get('images_folder', 'images/swjprojects');
			$folder  = $root . '/' . $section . '/' . $pk . '/' . $language . '/' . $folder;
			$path    = Path::clean(JPATH_ROOT . '/' . $folder);
			$site    = rtrim(Uri::root(true) . '/', '/');
			$version = 'v=' . time();

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
					$image->src  = $site . '/' . $folder . '/' . $filename . '?' . $version;
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
					$images[$name] = $image;
				}

				// Add empty images
				foreach ($values as $name => $value)
				{
					if (!isset($images[$name]) && !empty($value['text']))
					{
						// Prepare image
						$image       = new stdClass();
						$image->file = false;
						$image->name = $name;
						$image->src  = '';
						$image->text = $value['text'];

						// Set ordering
						$image->ordering = (!empty($value) && !empty($value['ordering'])) ? $value['ordering'] : 0;
						if (empty($image->ordering))
						{
							$ordering        = $ordering + 1;
							$image->ordering = $ordering;
						}
						$image->ordering = (int) $image->ordering;

						// Add to images
						$images[$name] = $image;
					}
				}

				// Sort images array if don't empty
				$images = (!empty($images)) ? ArrayHelper::sortObjects($images, 'ordering', 1) : false;
			}

			self::$_images[$hash] = $images;
		};

		return self::$_images[$hash];
	}

	/**
	 * Method to upload the multiple images.
	 *
	 * @param   string    $section   Component section selector (etc. projects).
	 * @param   integer   $pk        The id of the item.
	 * @param   string    $folder    The name of the images folder.
	 * @param   Registry  $values    The images values array.
	 * @param   string    $language  The language of the image.
	 * @param   array     $upload    The upload images data.
	 *
	 * @return   false|array  New images names  array on success, false on failure.
	 *
	 * @since  1.3.0
	 */
	public static function uploadImages($section = null, $pk = null, $folder = null, $values = null, $language = null, $upload = array())
	{
		if (empty($section) || empty($pk) || empty($folder) || empty($language) || empty($upload)) return false;

		// Get exist images
		$images = self::getImages($section, $pk, $folder, $values, $language);
		$names  = ($images)? ArrayHelper::getColumn($images, 'name') : array();

		// Check folder
		$root   = ComponentHelper::getParams('com_swjprojects')->get('images_folder', 'images/swjprojects');
		$folder = $root . '/' . $section . '/' . $pk . '/' . $language . '/' . $folder;
		$path   = Path::clean(JPATH_ROOT . '/' . $folder);
		if (!Folder::exists($path) && !Folder::create($path))
		{
			return false;
		}

		// Upload images
		$result = array();
		foreach ($upload as $file)
		{
			// Check image before upload
			if (empty($file) || empty($file['tmp_name']) || empty($file['name']) || !self::checkImage($file['tmp_name']))
			{
				continue;
			}

			// Prepare name
			$name = self::generateName();
			while (in_array($name, $names))
			{
				$name = self::generateName();
			}
			$filename = $name . '.' . File::getExt($file['name']);

			// Upload
			$src  = $file['tmp_name'];
			$dest = Path::clean($path . '/' . $filename);
			if (!File::upload($src, $dest, false, true))
			{
				continue;
			}

			// Add to names
			$names[] = $name;

			// Add to result
			$result[] = $name;
		}

		return (!empty($result)) ? $result : false;
	}

	/**
	 * Method to change the multiple images.
	 *
	 * @param   string   $section   Component section selector (etc. projects).
	 * @param   integer  $pk        The id of the item.
	 * @param   string   $folder    The name of the images folder.
	 * @param   string   $language  The language of the image.
	 * @param   string   $name      The name of the image file.
	 * @param   array    $image     The upload image data.
	 *
	 * @return   false|string  New image src on success, false on failure.
	 *
	 * @since  1.3.0
	 */
	public static function changeImages($section = null, $pk = null, $folder = null, $language = null, $name = null, $image = array())
	{
		if (empty($section) || empty($pk) || empty($folder) || empty($language) || empty($name) || empty($image)) return false;

		// Check upload image
		if (empty($image) || empty($image['tmp_name']) || empty($image['name']) || !self::checkImage($image['tmp_name']))
		{
			return false;
		}

		// Check folder
		$root   = ComponentHelper::getParams('com_swjprojects')->get('images_folder', 'images/swjprojects');
		$folder = $root . '/' . $section . '/' . $pk . '/' . $language . '/' . $folder;
		$path   = Path::clean(JPATH_ROOT . '/' . $folder);
		if (!Folder::exists($path) && !Folder::create($path))
		{
			return false;
		}

		// Delete current
		$current = false;
		$filter = '^' . $name . '\.[a-zA-Z]*$';
		$files   = (Folder::exists($path)) ? Folder::files($path, $filter, false) : false;
		if ($files && !empty($files[0]))
		{
			$filename = $files[0];
			if (self::checkImage($path . '/' . $filename))
			{
				$current = Path::clean($path . '/' . $filename);
			}
		}
		if ($current && !File::delete($current))
		{
			return false;
		}

		// Upload image
		$filename = $name . '.' . File::getExt($image['name']);
		$src      = $image['tmp_name'];
		$dest     = Path::clean($path . '/' . $filename);
		if (!File::upload($src, $dest, false, true))
		{
			return false;
		}

		// Get new src
		$site    = rtrim(Uri::root(true) . '/', '/');
		$version = 'v=' . time();

		return $site . '/' . $folder . '/' . $filename . '?' . $version;
	}

	/**
	 * Method to delete the multiple images.
	 *
	 * @param   string   $section   Component section selector (etc. projects).
	 * @param   integer  $pk        The id of the item.
	 * @param   string   $folder    The name of the images folder.
	 * @param   string   $language  The language of the image.
	 * @param   string   $name      The name of the image file.
	 *
	 * @return  bool  True on success, false on failure.
	 *
	 * @since  1.3.0
	 */
	public static function deleteImages($section = null, $pk = null, $folder = null, $language = null, $name = null)
	{
		if (empty($section) || empty($pk) || empty($folder) || empty($language) || empty($name)) return false;

		// Check folder
		$root   = ComponentHelper::getParams('com_swjprojects')->get('images_folder', 'images/swjprojects');
		$folder = $root . '/' . $section . '/' . $pk . '/' . $language . '/' . $folder;
		$path   = Path::clean(JPATH_ROOT . '/' . $folder);
		if (!Folder::exists($path) && !Folder::create($path))
		{
			return false;
		}

		$current = false;
		$filter = '^' . $name . '\.[a-zA-Z]*$';
		$files   = (Folder::exists($path)) ? Folder::files($path, $filter, false) : false;
		if ($files && !empty($files[0]))
		{
			$filename = $files[0];
			if (self::checkImage($path . '/' . $filename))
			{
				$current = Path::clean($path . '/' . $filename);
			}
		}

		return (!$current || File::delete($current));
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

	/**
	 * Method to generate random image name.
	 *
	 * @param   int  $length  Name length.
	 *
	 * @return  string  Image name.
	 *
	 * @since  1.3.0
	 */
	public static function generateName($length = 11)
	{
		$secret = '';
		$chars  = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's',
			't', 'u', 'v', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O',
			'P', 'R', 'S', 'T', 'U', 'V', 'X', 'Y', 'Z', 0, 1, 2, 3, 4, 5, 6, 7, 8, 9);
		for ($i = 0; $i < $length; $i++)
		{
			$key    = rand(0, count($chars) - 1);
			$secret .= $chars[$key];
		}

		return $secret;
	}
}
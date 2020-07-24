<?php
/**
 * @package    SW JProjects Component
 * @version    1.5.5
 * @author     Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2018 - 2020 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;

class SWJProjectsHelperKeys
{
	/**
	 * Key characters.
	 *
	 * @var  array
	 *
	 * @since  1.3.0
	 */
	protected static $characters = null;

	/**
	 * Method to generate key.
	 *
	 * @param   int  $length  Key length.
	 *
	 * @return  string  The key.
	 *
	 * @since  1.3.0
	 */
	public static function generateKey($length = null)
	{
		$secret     = '';
		$characters = self::getCharacters();
		$length     = (!empty($length)) ? $length
			: ComponentHelper::getParams('com_swjprojects')->get('key_length', 16);
		for ($i = 0; $i < $length; $i++)
		{
			$key    = rand(0, count($characters) - 1);
			$secret .= $characters[$key];
		}

		return $secret;
	}

	/**
	 * Method to key characters.
	 *
	 * @return  array  The key characters.
	 *
	 * @since  1.3.0
	 */
	public static function getCharacters()
	{
		if (self::$characters === null)
		{
			// Get from params
			if ($characters = ComponentHelper::getParams('com_swjprojects')->get('key_characters'))
			{
				$characters = array_filter(array_map('trim', explode(',', $characters)), function ($element) {
					return (!empty($element));
				});
			}

			// Get default
			if (empty($characters))
			{
				$characters = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's',
					't', 'u', 'v', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O',
					'P', 'R', 'S', 'T', 'U', 'V', 'X', 'Y', 'Z', 0, 1, 2, 3, 4, 5, 6, 7, 8, 9);
			}

			self::$characters = array_unique($characters);
		}

		return self::$characters;
	}

	/**
	 * Method to mask key.
	 *
	 * @param   string  $key  The key.
	 *
	 * @return  string  The key with mask.
	 *
	 * @since  1.3.0
	 */
	public static function maskKey($key = null)
	{
		$length  = iconv_strlen($key);
		$maskKey = '';
		$stars   = '';
		foreach (str_split($key) as $key => $symbol)
		{
			$mask    = ($key > 1 && $key < ($length - 4));
			$maskKey .= ($mask) ? '*' : $symbol;
			$stars   .= ($mask) ? '*' : '';
		}

		$maskKey = str_replace($stars, '*****', $maskKey);

		return $maskKey;
	}
}
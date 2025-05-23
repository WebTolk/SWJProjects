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

namespace Joomla\Component\SWJProjects\Administrator\Helper;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Multilanguage;
use function array_keys;
use function defined;
use function str_replace;
use function strtolower;

defined('_JEXEC') or die;

class TranslationHelper
{
	/**
	 * Translations data.
	 *
	 * @var  array
	 *
	 * @since  1.5.3
	 */
	protected static $_translations = null;

	/**
	 * Translations codes.
	 *
	 * @var  array
	 *
	 * @since  1.5.3
	 */
	protected static $_codes = null;

	/**
	 * Default translation code.
	 *
	 * @var  string
	 *
	 * @since  1.5.3
	 */
	protected static $_default = null;

	/**
	 * Current translation code.
	 *
	 * @var  string
	 *
	 * @since  1.5.3
	 */
	protected static $_current = null;

	/**
	 * Method for getting translations.
	 *
	 * @return  object[]  Translations data array.
	 *
	 * @since  1.5.3
	 */
	public static function getTranslations()
	{
		if (self::$_translations === null)
		{
			$languages = LanguageHelper::getInstalledLanguages(0, true);
			$default   = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
			$current   = Factory::getApplication()->getLanguage()->getTag();
			$multilang = Multilanguage::isEnabled();

			$translations = array();
			$first        = array();
			foreach ($languages as $code => $language)
			{
				if (!$multilang && $code !== $default) continue;

				$translation          = new \stdClass();
				$translation->name    = $language->metadata['name'];
				$translation->code    = $code;
				$translation->default = ($code === $default) ? 1 : 0;
				$translation->current = ($code === $current) ? 1 : 0;
				$translation->image   = strtolower(str_replace('-', '_', $code));

				if ($translation->current)
				{
					self::$_current = $code;
				}
				if ($translation->default)
				{
					$first[$code]   = $translation;
					self::$_default = $code;
				}
				else
				{
					$translations[$code] = $translation;
				}
			}

			$translations = $first + $translations;

			self::$_translations = $translations;
			self::$_codes        = array_keys($translations);
		}

		return self::$_translations;
	}

	/**
	 * Method for getting translations codes.
	 *
	 * @return  array  Translations codes.
	 *
	 * @since  1.5.3
	 */
	public static function getCodes()
	{
		if (self::$_codes === null)
		{
			self::getTranslations();
		}

		return self::$_codes;
	}

	/**
	 * Method for getting default translation code.
	 *
	 * @return  string Default translation code.
	 *
	 * @since  1.5.3
	 */
	public static function getDefault()
	{
		if (self::$_default === null)
		{
			self::getTranslations();
		}

		return self::$_default;
	}

	/**
	 * Method for getting current translation code.
	 *
	 * @return  string Default translation code.
	 *
	 * @since  1.5.3
	 */
	public static function getCurrent()
	{
		if (self::$_current === null)
		{
			self::getTranslations();
		}

		return self::$_current;
	}

	/**
	 * Method for getting is current translation default.
	 *
	 * @return  bool Default translation code.
	 *
	 * @since  1.5.3
	 */
	public static function isDefault()
	{
		if (self::$_translations === null)
		{
			self::getTranslations();
		}

		return self::$_default == self::$_current;
	}
}

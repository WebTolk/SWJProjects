<?php
/**
 * @package    SW JProjects Package
 * @version    1.6.4
 * @author Septdir Workshop, <https://septdir.com>, Sergey Tolkachyov <https://web-tolk.ru>
 * @сopyright (c) 2018 - April 2023 Septdir Workshop, Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link https://septdir.com, https://web-tolk.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Version;

class pkg_swjprojectsInstallerScript
{
	/**
	 * Minimum PHP version required to install the extension.
	 *
	 * @var  string
	 *
	 * @since  1.0.0
	 */
	protected $minimumPhp = '7.0';

	/**
	 * Minimum Joomla version required to install the extension.
	 *
	 * @var  string
	 *
	 * @since  1.0.0
	 */
	protected $minimumJoomla = '3.9.0';

	/**
	 * Runs right before any installation action.
	 *
	 * @param   string            $type    Type of PostFlight action.
	 * @param   InstallerAdapter  $parent  Parent object calling object.
	 *
	 * @throws  Exception
	 *
	 * @return  boolean True on success, false on failure.
	 *
	 * @since  1.0.0
	 */
	function preflight($type, $parent)
	{
		// Check compatible
		if (!$this->checkCompatible()) return false;

		// Check update server
		if ($type == 'update')
		{
			$this->checkUpdateServer();
		}

		return true;
	}

	/**
	 * Method to check compatible.
	 *
	 * @throws  Exception
	 *
	 * @return  boolean True on success, false on failure.
	 *
	 * @since  1.2.0
	 */
	protected function checkCompatible()
	{
		// Check old joomla
		if (!class_exists('Joomla\CMS\Version'))
		{
			JFactory::getApplication()->enqueueMessage(JText::sprintf('PKG_SWJPROJECTS_ERROR_COMPATIBLE_JOOMLA',
				$this->minimumJoomla), 'error');

			return false;
		}

		$app      = Factory::getApplication();
		$jversion = new Version();

		// Check php
		if (!(version_compare(PHP_VERSION, $this->minimumPhp) >= 0))
		{
			$app->enqueueMessage(Text::sprintf('PKG_SWJPROJECTS_ERROR_COMPATIBLE_PHP', $this->minimumPhp),
				'error');

			return false;
		}

		// Check joomla version
		if (!$jversion->isCompatible($this->minimumJoomla))
		{
			$app->enqueueMessage(Text::sprintf('PKG_SWJPROJECTS_ERROR_COMPATIBLE_JOOMLA', $this->minimumJoomla),
				'error');

			return false;
		}

		return true;
	}

	/**
	 * Method to check update server and change if need.
	 *
	 * @since  1.2.0
	 */
	protected function checkUpdateServer()
	{
		$old = array(
			'https://www.septdir.com/jupdate?element=pkg_swjprojects',
			'https://www.septdir.com/marketplace/joomla/update?element=pkg_swjprojects'
		);
		$new = 'https://www.septdir.com/solutions/joomla/update?element=pkg_swjprojects';

		$db      = Factory::getDbo();
		$query   = $db->getQuery(true)
			->select(array('update_site_id', 'location'))
			->from($db->quoteName('#__update_sites'))
			->where($db->quoteName('name') . ' = ' . $db->quote('SW JProjects'));
		$current = $db->setQuery($query)->loadObject();

		if (in_array($current->location, $old))
		{
			$current->location = $new;
			$db->updateObject('#__update_sites', $current, array('update_site_id'));
		}
	}

	/**
	 * This method is called when extension is updated.
	 *
	 * @param   InstallerAdapter  $parent  Parent object calling object.
	 *
	 * @since  1.3.0
	 */
	public function update($parent)
	{
		// Unset package id for JLSitemap plugin
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('extension_id')
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
			->where($db->quoteName('folder') . ' = ' . $db->quote('jlsitemap'))
			->where('package_id <>  0');
		if ($plugin = $db->setQuery($query)->loadResult())
		{
			$db->setQuery('UPDATE #__extensions SET package_id = 0 WHERE extension_id = ' . $plugin)->execute();
		}
	}
}
<?php
/**
 * @package       SW JProjects
 * @version       2.6.1
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Cache\Cache;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\LibraryHelper;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\CMS\Version;
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
			protected string $minimumJoomla = '5.0.0';

			/**
			 * Minimum PHP version required to install the extension.
			 *
			 * @var  string
			 *
			 * @since  1.0.0
			 */
			protected string $minimumPhp = '8.1';

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

				// Check compatible
				if (!$this->checkCompatible($adapter->getElement()))
				{
					return false;
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
				// Check key params

				$smile = '';
				if ($type != 'uninstall')
				{
					$smiles    = ['&#9786;', '&#128512;', '&#128521;', '&#128525;', '&#128526;', '&#128522;', '&#128591;'];
					$smile_key = array_rand($smiles, 1);
					$smile     = $smiles[$smile_key];
					// Check keys only on install or update
					$this->checkKeysParams();
				}

				$element = strtoupper($adapter->getElement());
				$type    = strtoupper($type);
				$html    = '
				<div class="row m-0">
				<div class="col-12 col-md-8 p-0 pe-2">
				<h2>' . $smile . ' ' . Text::_($element . '_AFTER_' . $type) . ' <br/>' . Text::_($element) . '</h2>
				' . Text::_($element . '_DESC');

				$html .= Text::_($element . '_WHATS_NEW');

				$html .= '</div>
				<div class="col-12 col-md-4 p-0 d-flex flex-column justify-content-start">
				<img width="180" src="https://web-tolk.ru/web_tolk_logo_wide.png">
				<p>Joomla Extensions</p>
				<p class="btn-group">
					<a class="btn btn-sm btn-outline-primary" href="https://web-tolk.ru" target="_blank"> https://web-tolk.ru</a>
					<a class="btn btn-sm btn-outline-primary" href="mailto:info@web-tolk.ru"><i class="icon-envelope"></i> info@web-tolk.ru</a>
				</p>
				<div class="btn-group-vertical mb-3 web-tolk-btn-links" role="group" aria-label="Joomla community links">
					<a class="btn btn-danger text-white w-100" href="https://t.me/joomlaru" target="_blank">' . Text::_($element . '_JOOMLARU_TELEGRAM_CHAT') . '</a>
					<a class="btn btn-primary text-white w-100" href="https://t.me/webtolkru" target="_blank">' . Text::_($element . '_WEBTOLK_TELEGRAM_CHANNEL') . '</a>
				</div>
				' . Text::_($element . "_MAYBE_INTERESTING") . '
				</div>

				';
				$this->app->enqueueMessage($html, 'info');

				return true;
			}

			/**
			 * Enable plugin after installation.
			 *
			 * @param   InstallerAdapter  $adapter  Parent object calling object.
			 *
			 * @since  1.0.0
			 */
			protected function enablePlugin(InstallerAdapter $adapter)
			{
				// Prepare plugin object
				$plugin          = new \stdClass();
				$plugin->type    = 'plugin';
				$plugin->element = $adapter->getElement();
				$plugin->folder  = (string) $adapter->getParent()->manifest->attributes()['group'];
				$plugin->enabled = 1;

				// Update record
				$this->db->updateObject('#__extensions', $plugin, ['type', 'element', 'folder']);
			}

			/**
			 * Method to check compatible.
			 *
			 * @throws  Exception
			 *
			 * @return  boolean True on success, False on failure.
			 *
			 * @since  1.0.0
			 */
			protected function checkCompatible(string $element): bool
			{
				$element = strtoupper($element);
				// Check joomla version
				if (!(new Version)->isCompatible($this->minimumJoomla))
				{
					$this->app->enqueueMessage(
						Text::sprintf($element.'_ERROR_COMPATIBLE_JOOMLA', $this->minimumJoomla),
						'error'
					);

					return false;
				}

				// Check PHP
				if (!(version_compare(PHP_VERSION, $this->minimumPhp) >= 0))
				{
					$this->app->enqueueMessage(
						Text::sprintf($element.'_ERROR_COMPATIBLE_PHP', $this->minimumPhp),
						'error'
					);

					return false;
				}

				return true;
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
				\JLoader::registerNamespace('Joomla\Component\SWJProjects\Administrator\Helper', JPATH_ADMINISTRATOR.'/components/com_swjprojects/src/Helper');
				// Length
				if (empty($params->get('key_length')))
				{
					$params->set('key_length', 16);
					$update = true;
				}

				// Characters
				if (empty($params->get('key_characters')))
				{
					$params->set('key_characters', implode(',', Joomla\Component\SWJProjects\Administrator\Helper\KeysHelper::getCharacters()));
					$update = true;
				}

				// Master
				if (empty($params->get('key_master')))
				{
					$params->set('key_master', Joomla\Component\SWJProjects\Administrator\Helper\KeysHelper::generateKey(128));
					$update = true;
				}

				// Update
				if ($update)
				{
					$component          = new \stdClass();
					$component->element = 'com_swjprojects';
					$component->params  = $params->toString();

					$this->db->updateObject('#__extensions', $component, ['element']);
				}
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
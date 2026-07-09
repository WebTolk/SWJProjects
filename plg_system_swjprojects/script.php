<?php
/**
 * @package       SW JProjects
 * @version       2.7.0
 * @Author        Sergey Tolkachyov
 * @copyright  Copyright (c) 2018 - 2026 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link          https://web-tolk.ru
 * @since         2.7.0
 */

declare(strict_types=1);

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\Database\DatabaseDriver;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

\defined('_JEXEC') or die;

return new class () implements ServiceProviderInterface {
	public function register(Container $container)
	{
		$container->set(InstallerScriptInterface::class, new class () implements InstallerScriptInterface {
			protected DatabaseDriver $db;

			public function __construct()
			{
				$this->db  = Factory::getContainer()->get('DatabaseDriver');
			}

			public function install(InstallerAdapter $adapter): bool
			{
				$this->enablePlugin($adapter);

				return true;
			}

			public function update(InstallerAdapter $adapter): bool
			{
				$this->enablePlugin($adapter);

				return true;
			}

			public function uninstall(InstallerAdapter $adapter): bool
			{
				return true;
			}

			public function preflight(string $type, InstallerAdapter $adapter): bool
			{
				return true;
			}

			public function postflight(string $type, InstallerAdapter $adapter): bool
			{
				return true;
			}

			protected function enablePlugin(InstallerAdapter $adapter): void
			{
				$plugin          = new stdClass();
				$plugin->type    = 'plugin';
				$plugin->element = $adapter->getElement();
				$plugin->folder  = (string) $adapter->getParent()->manifest->attributes()['group'];
				$plugin->enabled = 1;

				$this->db->updateObject('#__extensions', $plugin, ['type', 'element', 'folder']);
			}
		});
	}
};

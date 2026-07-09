<?php
/**
 * @package       SW JProjects
 * @subpackage    Webservices.swjprojects
 * @copyright  Copyright (c) 2018 - 2026 Sergey Tolkachyov. All rights reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Plugin\WebServices\Swjprojects\Extension\Swjprojects;

return new class () implements ServiceProviderInterface {
	public function register(Container $container): void
	{
		$container->set(
			PluginInterface::class,
			static function (Container $container): PluginInterface {
				$plugin = new Swjprojects((array) PluginHelper::getPlugin('webservices', 'swjprojects'));
				$plugin->setApplication(Factory::getApplication());

				return $plugin;
			}
		);
	}
};

<?php
/**
 * @package    SW JProjects
 * @subpackage  mod_footer
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Extension\Service\Provider\HelperFactory;
use Joomla\CMS\Extension\Service\Provider\Module;
use Joomla\CMS\Extension\Service\Provider\ModuleDispatcherFactory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

defined('_JEXEC') or die;
/**
 * The footer module service provider.
 *
 * @since  4.4.0
 */
return new class () implements ServiceProviderInterface {
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since   4.4.0
	 */
	public function register(Container $container): void
	{
		$container->registerServiceProvider(new ModuleDispatcherFactory('\\Joomla\\Module\\Swjprojectscategories'));
		// Namespace модуля для хелпера
		$container->registerServiceProvider(new HelperFactory('\\Joomla\\Module\\Swjprojectscategories\\Site\\Helper'));
		$container->registerServiceProvider(new Module());
	}
};

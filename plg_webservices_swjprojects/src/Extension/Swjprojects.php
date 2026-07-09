<?php
/**
 * @package       SW JProjects
 * @subpackage    Webservices.swjprojects
 * @copyright  Copyright (c) 2018 - 2026 Sergey Tolkachyov. All rights reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\WebServices\Swjprojects\Extension;

\defined('_JEXEC') or die;

use Joomla\CMS\Event\Application\BeforeApiRouteEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use Joomla\Router\Route;

final class Swjprojects extends CMSPlugin implements SubscriberInterface
{
	public static function getSubscribedEvents(): array
	{
		return [
			'onBeforeApiRoute' => 'onBeforeApiRoute',
		];
	}

	public function onBeforeApiRoute(BeforeApiRouteEvent $event): void
	{
		$router = $event->getRouter();
		$publicDefaults = ['component' => 'com_swjprojects', 'public' => true];
		$privateDefaults = ['component' => 'com_swjprojects'];

		$router->addRoutes([
			new Route(['GET'], 'v1/swjprojects/categories', 'categories.displayList', [], $publicDefaults),
			new Route(['GET'], 'v1/swjprojects/categories/:id', 'categories.displayItem', ['id' => '(\d+)'], $publicDefaults),
			new Route(['GET'], 'v1/swjprojects/projects', 'projects.displayList', [], $publicDefaults),
			new Route(['GET'], 'v1/swjprojects/projects/:id', 'projects.displayItem', ['id' => '(\d+)'], $publicDefaults),
			new Route(['GET'], 'v1/swjprojects/userkeys', 'userkeys.displayList', [], $privateDefaults),
			new Route(['GET'], 'v1/swjprojects/userkeys/:id', 'userkeys.displayItem', ['id' => '(\d+)'], $privateDefaults),
		]);
	}
}

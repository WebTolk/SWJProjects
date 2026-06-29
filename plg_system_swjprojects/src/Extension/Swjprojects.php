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

namespace Joomla\Plugin\System\Swjprojects\Extension;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Menu\AdministratorMenuItem;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

use function array_shift;
use function defined;

defined('_JEXEC') or die;

final class Swjprojects extends CMSPlugin implements SubscriberInterface
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    bool
	 * @since  2.7.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Is the original component menu item already removed from the current tree.
	 *
	 * @var   bool
	 * @since 2.7.0
	 */
	protected bool $removeAdministratorMenu = false;

	/**
	 * Is the plugin-driven menu already injected into the current tree.
	 *
	 * @var   bool
	 * @since 2.7.0
	 */
	protected bool $loadAdministratorMenu = false;

	/**
	 * Cached administrator menu root item.
	 *
	 * @var   AdministratorMenuItem|null
	 * @since 2.7.0
	 */
	protected ?AdministratorMenuItem $administratorMenu = null;

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 *
	 * @since   2.7.0
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onPreprocessMenuItems' => 'onPreprocessMenuItems',
		];
	}

	/**
	 * Inject the SW JProjects administrator menu through the backend menu event.
	 *
	 * @param   Event  $event  The event.
	 *
	 * @return  void
	 *
	 * @since   2.7.0
	 */
	public function onPreprocessMenuItems(Event $event): void
	{
		$context  = $event->getArgument(0);
		$children = $event->getArgument(1);

		$this->getApplication()->getLanguage()->load('com_swjprojects', JPATH_ADMINISTRATOR);
		$this->getApplication()->getLanguage()->load('com_swjprojects.sys', JPATH_ADMINISTRATOR);

		$this->removeSwjprojectsAdministratorComponentsMenuItem($context, $children);
		$this->loadSwjprojectsAdministratorMenu($context, $children);

		$event->setArgument(1, $children);
	}

	/**
	 * Remove the legacy component menu item if Joomla still builds it from old data.
	 *
	 * @param   string|null  $context   Event context selector.
	 * @param   array        $children  Current tree level children.
	 *
	 * @return  void
	 *
	 * @since   2.7.0
	 */
	protected function removeSwjprojectsAdministratorComponentsMenuItem(?string $context = null, array $children = []): void
	{
		if (
			!$this->getApplication()->isClient('administrator')
			|| $this->removeAdministratorMenu === true
			|| $context !== 'com_menus.administrator.module'
		) {
			return;
		}

		$componentId = (int) ComponentHelper::getComponent('com_swjprojects')->id;

		foreach ($children as $child)
		{
			if ($child->type === 'component' && (int) $child->component_id === $componentId)
			{
				$child->getParent()->removeChild($child);
				$this->removeAdministratorMenu = true;
			}
		}
	}

	/**
	 * Inject the SW JProjects menu tree into the administrator navigation.
	 *
	 * @param   string|null  $context   Event context selector.
	 * @param   array        $children  Current tree level children.
	 *
	 * @return  void
	 *
	 * @since   2.7.0
	 */
	protected function loadSwjprojectsAdministratorMenu(?string $context = null, array $children = []): void
	{
		if (
			!$this->getApplication()->isClient('administrator')
			|| $this->loadAdministratorMenu === true
			|| $context !== 'com_menus.administrator.module'
			|| !$this->getApplication()->getIdentity()->authorise('core.manage', 'com_swjprojects')
		) {
			return;
		}

		$first = array_shift($children);

		if ($first === null)
		{
			return;
		}

		$parent   = $first->getParent();
		$children = $parent->getChildren();
		$menu     = $this->getSwjprojectsAdministratorMenu();
		$rebuild  = false;

		foreach ($children as $child)
		{
			if ($child->title === 'MOD_MENU_SYSTEM')
			{
				$rebuild = 'standard';
			}
			elseif ($child->title === 'MOD_MENU_COMPONENTS')
			{
				$rebuild = 'alternative';
			}
		}

		if ($rebuild === false)
		{
			$parent->addChild($menu);
		}
		else
		{
			foreach ($children as $child)
			{
				$parent->removeChild($child);

				if ($rebuild === 'standard' && $child->title === 'MOD_MENU_SYSTEM')
				{
					$parent->addChild($menu);
				}
				elseif ($rebuild === 'alternative' && $child->title === 'MOD_MENU_COMPONENTS')
				{
					$parent->addChild($menu);
				}

				$parent->addChild($child);
			}
		}

		$this->loadAdministratorMenu = true;
	}

	/**
	 * Build the SW JProjects administrator menu tree.
	 *
	 * @return  AdministratorMenuItem
	 *
	 * @since   2.7.0
	 */
	protected function getSwjprojectsAdministratorMenu(): AdministratorMenuItem
	{
		if ($this->administratorMenu === null)
		{
			$parent = new AdministratorMenuItem([
				'title'   => 'COM_SWJPROJECTS',
				'type'    => 'heading',
				'element' => 'com_swjprojects',
				'class'   => 'class:folder-open swjprojects-menu-root',
			]);

			$items = [
				[
					'title'   => 'COM_SWJPROJECTS_VERSIONS',
					'link'    => 'index.php?option=com_swjprojects&view=versions',
					'element' => 'com_swjprojects',
					'quicktask' => 'index.php?option=com_swjprojects&task=version.add',
					'quicktask_title' => 'COM_SWJPROJECTS_MENUS_NEW_VERSION',
				],
				[
					'title'   => 'COM_SWJPROJECTS_PROJECTS',
					'link'    => 'index.php?option=com_swjprojects&view=projects',
					'element' => 'com_swjprojects',
					'quicktask' => 'index.php?option=com_swjprojects&task=project.add',
					'quicktask_title' => 'COM_SWJPROJECTS_MENUS_NEW_PROJECT',
				],
				[
					'title'   => 'COM_SWJPROJECTS_KEYS',
					'link'    => 'index.php?option=com_swjprojects&view=keys',
					'element' => 'com_swjprojects',
					'quicktask' => 'index.php?option=com_swjprojects&task=key.add',
					'quicktask_title' => 'COM_SWJPROJECTS_MENUS_NEW_KEY',
				],
				[
					'title'   => 'COM_SWJPROJECTS_DOCUMENTATION',
					'link'    => 'index.php?option=com_swjprojects&view=documentation',
					'element' => 'com_swjprojects',
					'quicktask' => 'index.php?option=com_swjprojects&task=document.add',
					'quicktask_title' => 'COM_SWJPROJECTS_MENUS_NEW_DOCUMENT',
				],
				[
					'title'   => 'COM_SWJPROJECTS_CATEGORIES',
					'link'    => 'index.php?option=com_swjprojects&view=categories',
					'element' => 'com_swjprojects',
					'quicktask' => 'index.php?option=com_swjprojects&task=category.add',
					'quicktask_title' => 'COM_SWJPROJECTS_MENUS_NEW_CATEGORY',
				],
				[
					'title'   => 'COM_SWJPROJECTS_MAINTAINERS',
					'link'    => 'index.php?option=com_swjprojects&view=maintainers',
					'element' => 'com_swjprojects',
					'quicktask' => 'index.php?option=com_swjprojects&task=maintainer.add',
					'quicktask_title' => 'COM_SWJPROJECTS_MENUS_NEW_MAINTAINER',
				],
			];

			foreach ($items as $item)
			{
				$parent->addChild(new AdministratorMenuItem([
					'title'   => $item['title'],
					'type'    => 'component',
					'link'    => $item['link'],
					'element' => $item['element'],
					'scope'   => 'com_swjprojects',
					'params'  => new Registry([
						'menu-quicktask' => $item['quicktask'],
						'menu-quicktask-title' => $item['quicktask_title'],
						'menu-quicktask-icon' => 'plus',
						'menu-quicktask-permission' => 'core.create',
					]),
				]));
			}

			if ($this->getApplication()->getIdentity()->authorise('core.admin', 'com_swjprojects'))
			{
				$parent->addChild(new AdministratorMenuItem([
					'title'   => 'COM_SWJPROJECTS_CONFIG',
					'type'    => 'component',
					'link'    => 'index.php?option=com_config&view=component&component=com_swjprojects',
					'element' => 'com_config',
					'scope'   => 'default',
				]));
			}

			$this->administratorMenu = $parent;
		}

		return $this->administratorMenu;
	}
}

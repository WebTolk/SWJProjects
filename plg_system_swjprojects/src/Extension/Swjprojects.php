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
use Joomla\CMS\Event\Menu\PreprocessMenuItemsEvent;
use Joomla\CMS\Menu\AdministratorMenuItem;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Menus\Administrator\Helper\MenusHelper;
use Joomla\Event\SubscriberInterface;

use function array_shift;
use function defined;

defined('_JEXEC') or die;

final class Swjprojects extends CMSPlugin implements SubscriberInterface
{
	private const MENU_PLACEMENT_COMPONENTS = 'components';
	private const MENU_PLACEMENT_TOP = 'top';

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
	 * @param   PreprocessMenuItemsEvent  $event  The menu preprocessing event.
	 *
	 * @return  void
	 *
	 * @since   2.7.0
	 */
	public function onPreprocessMenuItems(PreprocessMenuItemsEvent $event): void
	{
		$context  = $event->getContext();
		$children = $event->getItems();

		$this->getApplication()->getLanguage()->load('com_swjprojects', JPATH_ADMINISTRATOR);
		$this->getApplication()->getLanguage()->load('com_swjprojects.sys', JPATH_ADMINISTRATOR);

		if ($this->getAdministratorMenuPlacement() === self::MENU_PLACEMENT_TOP)
		{
			$this->removeSwjprojectsAdministratorComponentsMenuItem($context, $children);
			$this->loadSwjprojectsTopLevelAdministratorMenu($context, $children);
		}
		else
		{
			$this->loadSwjprojectsComponentsAdministratorMenu($context, $children);
		}

		$event->updateItems($this->getCurrentAdministratorMenuItems($children));
	}

	/**
	 * Get the current sibling list after direct menu tree mutations.
	 *
	 * @param   AdministratorMenuItem[]  $children  Event menu items.
	 *
	 * @return  AdministratorMenuItem[]
	 *
	 * @since   2.7.0
	 */
	protected function getCurrentAdministratorMenuItems(array $children): array
	{
		$first = $children[0] ?? null;

		if (!$first instanceof AdministratorMenuItem)
		{
			return $children;
		}

		$parent = $first->getParent();

		return $parent instanceof AdministratorMenuItem ? $parent->getChildren() : $children;
	}

	/**
	 * Resolve where the plugin-owned administrator menu should be placed.
	 *
	 * @return  string
	 *
	 * @since   2.7.0
	 */
	protected function getAdministratorMenuPlacement(): string
	{
		$placement = (string) $this->params->get('administrator_menu_placement', self::MENU_PLACEMENT_COMPONENTS);

		return $placement === self::MENU_PLACEMENT_TOP ? self::MENU_PLACEMENT_TOP : self::MENU_PLACEMENT_COMPONENTS;
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
			if (
				$child->type === 'component'
				&& (
					(int) ($child->component_id ?? 0) === $componentId
					|| (string) $child->element === 'com_swjprojects'
					|| str_contains((string) $child->link, 'option=com_swjprojects')
				)
			)
			{
				$child->getParent()->removeChild($child);
				$this->removeAdministratorMenu = true;

				return;
			}

			$this->removeSwjprojectsAdministratorComponentsMenuItem($context, $child->getChildren());

			if ($this->removeAdministratorMenu === true)
			{
				return;
			}
		}
	}

	/**
	 * Add preset children to the standard Components menu item.
	 *
	 * @param   string|null  $context   Event context selector.
	 * @param   array        $children  Current tree level children.
	 *
	 * @return  void
	 *
	 * @since   2.7.0
	 */
	protected function loadSwjprojectsComponentsAdministratorMenu(?string $context = null, array $children = []): void
	{
		if (
			!$this->getApplication()->isClient('administrator')
			|| $this->loadAdministratorMenu === true
			|| $context !== 'com_menus.administrator.module'
			|| !$this->getApplication()->getIdentity()->authorise('core.manage', 'com_swjprojects')
		) {
			return;
		}

		$componentMenu = $this->findSwjprojectsAdministratorComponentsMenuItem($children);
		$presetMenu    = $this->getSwjprojectsPresetMenu();

		if ($componentMenu === null || $presetMenu === null)
		{
			return;
		}

		foreach ($componentMenu->getChildren() as $child)
		{
			$componentMenu->removeChild($child);
		}

		$componentMenu->dashboard = 'swjprojects';

		foreach ($presetMenu->getChildren() as $child)
		{
			$componentMenu->addChild($child);
		}

		$this->loadAdministratorMenu = true;
	}

	/**
	 * Inject the SW JProjects menu tree into the top administrator navigation level.
	 *
	 * @param   string|null  $context   Event context selector.
	 * @param   array        $children  Current tree level children.
	 *
	 * @return  void
	 *
	 * @since   2.7.0
	 */
	protected function loadSwjprojectsTopLevelAdministratorMenu(?string $context = null, array $children = []): void
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
		$menu  = $this->getSwjprojectsPresetMenu();

		if ($first === null || $menu === null)
		{
			return;
		}

		$parent   = $first->getParent();
		$children = $parent->getChildren();
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
	 * Find the standard component menu item generated by Joomla.
	 *
	 * @param   array  $children  Current tree level children.
	 *
	 * @return  AdministratorMenuItem|null
	 *
	 * @since   2.7.0
	 */
	protected function findSwjprojectsAdministratorComponentsMenuItem(array $children): ?AdministratorMenuItem
	{
		$componentId = (int) ComponentHelper::getComponent('com_swjprojects')->id;

		foreach ($children as $child)
		{
			if (
				$child->type === 'component'
				&& (
					(int) ($child->component_id ?? 0) === $componentId
					|| (string) $child->element === 'com_swjprojects'
					|| str_contains((string) $child->link, 'option=com_swjprojects')
				)
			) {
				return $child;
			}

			$match = $this->findSwjprojectsAdministratorComponentsMenuItem($child->getChildren());

			if ($match !== null)
			{
				return $match;
			}
		}

		return null;
	}

	/**
	 * Load the SW JProjects administrator menu tree from the component preset.
	 *
	 * @return  AdministratorMenuItem|null
	 *
	 * @since   2.7.0
	 */
	protected function getSwjprojectsPresetMenu(): ?AdministratorMenuItem
	{
		if ($this->administratorMenu === null)
		{
			$root     = MenusHelper::loadPreset('swjprojects', false);
			$children = $root->getChildren();

			$this->administratorMenu = $children[0] ?? null;
		}

		return $this->administratorMenu;
	}
}

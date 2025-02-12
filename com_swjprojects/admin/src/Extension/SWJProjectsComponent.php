<?php

/**
 * @package    SW JProjects
 * @subpackage  com_swjprojects
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\SWJProjects\Administrator\Extension;

use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Component\Router\RouterServiceTrait;
use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\SWJProjects\Administrator\Helper\SWJProjectsHelper;
use Joomla\Component\SWJProjects\Administrator\Service\HTML\Icon;
use Psr\Container\ContainerInterface;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Component class for com_swjprojects
 *
 * @since  4.0.0
 */
class SWJProjectsComponent extends MVCComponent implements
	BootableExtensionInterface,
	RouterServiceInterface
{
	use RouterServiceTrait;
	use HTMLRegistryAwareTrait;

	/** @var array Supported functionality */
	protected $supportedFunctionality = [
		'core.featured' => true,
		'core.state'    => true,
	];

	/**
	 * The trashed condition
	 *
	 * @since   4.0.0
	 */
	public const CONDITION_NAMES = [
		self::CONDITION_PUBLISHED   => 'JPUBLISHED',
		self::CONDITION_UNPUBLISHED => 'JUNPUBLISHED',
		self::CONDITION_ARCHIVED    => 'JARCHIVED',
		self::CONDITION_TRASHED     => 'JTRASHED',
	];

	/**
	 * The archived condition
	 *
	 * @since   4.0.0
	 */
	public const CONDITION_ARCHIVED = 2;

	/**
	 * The published condition
	 *
	 * @since   4.0.0
	 */
	public const CONDITION_PUBLISHED = 1;

	/**
	 * The unpublished condition
	 *
	 * @since   4.0.0
	 */
	public const CONDITION_UNPUBLISHED = 0;

	/**
	 * The trashed condition
	 *
	 * @since   4.0.0
	 */
	public const CONDITION_TRASHED = -2;

	/**
	 * Booting the extension. This is the function to set up the environment of the extension like
	 * registering new class loaders, etc.
	 *
	 * If required, some initial set up can be done from services of the container, eg.
	 * registering HTML services.
	 *
	 * @param   ContainerInterface  $container  The container
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function boot(ContainerInterface $container)
	{
//        $this->getRegistry()->register('SWJProjectsicon', new Icon());

		// The layout joomla.SWJProjects.icons does need a general icon service
//        $this->getRegistry()->register('icon', $this->getRegistry()->getService('SWJProjectsicon'));
	}


	/**
	 * Returns valid contexts
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	public function getContexts(): array
	{
		Factory::getApplication()->getLanguage()->load('com_swjprojects', JPATH_ADMINISTRATOR);

		$contexts = [
			'com_swjprojects.project'    => Text::_('com_swjprojects'),
			'com_swjprojects.categories' => Text::_('JCATEGORY'),
		];

		return $contexts;
	}


	/**
	 * Prepares the category form
	 *
	 * @param   Form          $form  The form to prepare
	 * @param   array|object  $data  The form data
	 *
	 * @return void
	 */
	public function prepareForm(Form $form, $data)
	{
		SWJProjectsHelper::onPrepareForm($form, $data);
	}
}

<?php
/**
 * @package       SW JProjects
 * @version       2.5.0-alhpa1
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
 */

namespace Joomla\Component\SWJProjects\Administrator\Traits;

use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Cache\Controller\OutputController;
use Joomla\CMS\Factory;
use function defined;

defined('_JEXEC') or die;

trait CacheAwareTrait
{
    /**
     * Return the pre-configured cache object
     *
     * @param   array  $cache_options
     *
     * @return OutputController
     *
     * @since 2.5.0
     */
    public function getCache(array $cache_options = []): OutputController
    {
        $config  = Factory::getContainer()->get('config');
        $options = [
            'defaultgroup' => 'com_swjprojects',
            'caching'      => true,
            'cachebase'    => $config->get('cache_path'),
            'storage'      => $config->get('cache_handler'),
        ];

		$options = array_merge($options, $cache_options);

		if (array_key_exists('cacheTimeout', $options) && is_scalar($options['cacheTimeout']) && $options['cacheTimeout'] > 0)
        {
            $options['lifetime'] = (int)$options['cacheTimeout'] * 60;
        }

        return Factory::getContainer()->get(CacheControllerFactoryInterface::class)->createCacheController(
            'output',
            $options
        );
    }

}
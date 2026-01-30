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

namespace Joomla\Component\SWJProjects\Administrator\Helper;

use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\SWJProjects\Administrator\Event\ServerschemeEvent;
use Joomla\Component\SWJProjects\Administrator\Serverscheme\ServerschemePlugin;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

class ServerschemeHelper
{
    /**
     * Cached schemes list by scheme type (system scheme name).
     *
     * Keys: scheme type (e.g. "joomla"), Values: ServerschemePlugin instance.
     *
     * @var array<string, ServerschemePlugin>
     *
     * @since 2.5.0
     */
    public static array $schemes = [];

    /**
     * Return a concrete serverscheme plugin instance by its type.
     *
     * The plugin instance is obtained from plugins via event `onGetServerschemeList`.
     * The given $config is injected into the plugin via `setConfig()`.
     *
     * @param  string $type   Scheme type (system scheme name), e.g. "joomla".
     * @param  array  $config Arbitrary configuration passed into scheme plugin (filesPath, cacheTimeout, etc.).
     *
     * @return ServerschemePlugin The scheme plugin instance.
     *
     * @throws Exception If scheme not found in registered plugins list.
     *
     * @since  2.5.0
     */
    public static function getServerScheme(string $type, array $config): ServerschemePlugin
    {
        $schemes = self::getServerSchemesList($config);

        if (!array_key_exists($type, $schemes)) {
            throw new Exception("Server schema {$type} not found", 500);
        }

        return $schemes[$type];
    }

    /**
     * Build and return list of all available server schemes from plugins.
     *
     * Notes about caching:
     * - This method fills static cache `self::$schemes`.
     * - To preserve BC, we keep a static cache, but still call `setConfig($config)` on each discovered scheme.
     *
     * @param  array $config Arbitrary configuration passed into scheme plugin instances.
     *
     * @return array<string, ServerschemePlugin> Array keyed by scheme type.
     *
     * @since  2.5.0
     */
    public static function getServerSchemesList(array $config = []): array
    {
        PluginHelper::importPlugin('swjprojects');

        $event = ServerschemeEvent::create('onGetServerschemeList', [
            'eventClass' => ServerschemeEvent::class,
            'subject'    => new Registry(),
        ]);

        Factory::getApplication()->getDispatcher()->dispatch($event->getName(), $event);

        /** @var array<int, array{type:string,name:string,class:ServerschemePlugin}> $results */
        $results = $event->getArgument('result', []);

        if (!empty($results)) {
            foreach ($results as $schema) {
                /** @var ServerschemePlugin $plugin */
                $plugin = $schema['class'];
                $plugin->setConfig($config);

                self::$schemes[$schema['type']] = $plugin;
            }
        }

        return self::$schemes;
    }

    /**
     * Resolve effective scheme name from 3 levels of configuration:
     * - global/component level
     * - category level
     * - project level
     *
     * Rules (as requested):
     * - Component scheme: base fallback. If empty => "joomla".
     * - Category scheme:
     *   - empty or "component_default" => use component scheme
     *   - otherwise => use category scheme (applies to all projects in category)
     * - Project scheme:
     *   - empty => use category effective scheme (or component if category also empty)
     *   - "component_default" => use component scheme
     *   - "category_default" => use category effective scheme
     *   - otherwise => use project custom scheme
     *
     * @param  string      $componentScheme Raw component scheme value.
     * @param  string|null $categoryScheme  Raw category scheme value (may be null/empty/"component_default").
     * @param  string|null $projectScheme   Raw project scheme value (may be null/empty/"component_default"/"category_default"/custom).
     *
     * @return string Effective scheme type to be used (e.g. "joomla", "custom_type").
     *
     * @since  2.6.1-dev
     */
    public static function resolveServerScheme(
        string $componentScheme,
        ?string $categoryScheme = null,
        ?string $projectScheme = null
    ): string {
        $componentScheme = self::normalizeSchemeValue($componentScheme);
        $componentScheme = $componentScheme !== '' ? $componentScheme : 'joomla';

        $categoryScheme = self::normalizeSchemeValue($categoryScheme);
        $projectScheme  = self::normalizeSchemeValue($projectScheme);

        // Category effective scheme
        $categoryEffective = ($categoryScheme !== '' && $categoryScheme !== 'component_default')
            ? $categoryScheme
            : $componentScheme;

        // Project effective scheme
        if ($projectScheme === '' || $projectScheme === 'category_default') {
            return $categoryEffective;
        }

        if ($projectScheme === 'component_default') {
            return $componentScheme;
        }

        return $projectScheme; // custom
    }

    /**
     * Normalize scheme string value: trim and convert null to empty string.
     *
     * @param  string|null $value Raw value.
     *
     * @return string Normalized value (trimmed), or empty string.
     *
     * @since  2.6.1-dev
     */
    private static function normalizeSchemeValue(?string $value): string
    {
        $value = $value ?? '';
        return trim($value);
    }

    /**
     * Compute a right server scheme name for the project.
     *
     * This is a BC method which previously tried to apply overrides.
     * Now it delegates the actual override logic to {@see resolveServerScheme()}.
     *
     * @param  int $project_id Project id.
     *
     * @return string Effective scheme type for the project.
     *
     * @since  2.5.0
     */
    public static function getServerSchemaNameForProject(int $project_id): string
    {
        $allParams       = self::getParams($project_id);
        $componentParams = $allParams->get('component_params');
        $categoryParams  = $allParams->get('category_params');
        $projectParams   = $allParams->get('project_params');

        // component scheme
        $componentScheme = $componentParams instanceof Registry
            ? (string) $componentParams->get('server_scheme', 'joomla')
            : 'joomla';

        // category scheme (string params or Registry)
        $categoryScheme = null;
        if ($categoryParams instanceof Registry) {
            $categoryScheme = (string) $categoryParams->get('server_scheme', '');
        } elseif (is_string($categoryParams)) {
            $tmp = new Registry($categoryParams);
            $categoryScheme = (string) $tmp->get('server_scheme', '');
        }

        // project scheme (string params or Registry)
        $projectScheme = null;
        if ($projectParams instanceof Registry) {
            $projectScheme = (string) $projectParams->get('server_scheme', '');
        } elseif (is_string($projectParams)) {
            $tmp = new Registry($projectParams);
            $projectScheme = (string) $tmp->get('server_scheme', '');
        }

        return self::resolveServerScheme($componentScheme, $categoryScheme, $projectScheme);
    }

    /**
     * Get all params together: component, category and project.
     *
     * Returned registry contains:
     * - component_params: Registry (ComponentHelper::getParams())
     * - category_params: string|null (raw JSON/INI params from #__swjprojects_categories.params)
     * - project_params:  string|null (raw JSON/INI params from #__swjprojects_projects.params)
     *
     * NOTE: We keep category_params/project_params as raw strings to avoid BC break for existing code.
     *
     * @param  int|null $project_id Project id; if null - returns only component params.
     *
     * @return Registry Registry container with params.
     *
     * @since  2.5.0
     */
    public static function getParams(?int $project_id = null): Registry
    {
        $params = new Registry();
        $params->set('component_params', ComponentHelper::getParams('com_swjprojects'));

        if (!empty($project_id)) {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->createQuery();

            $query->select([
                $db->quoteName('p.params', 'project_params'),
                $db->quoteName('c.params', 'category_params'),
            ])
                ->from($db->quoteName('#__swjprojects_projects', 'p'))
                ->where($db->quoteName('p.id') . ' = ' . $db->quote($project_id))
                ->leftJoin(
                    $db->quoteName('#__swjprojects_categories', 'c'),
                    $db->quoteName('c.id') . ' = ' . $db->quoteName('p.catid')
                );

            $additionalParams = $db->setQuery($query)->loadAssoc();

            if (!empty($additionalParams)) {
                if (!empty($additionalParams['category_params'])) {
                    $params->set('category_params', (string) $additionalParams['category_params']);
                }
                if (!empty($additionalParams['project_params'])) {
                    $params->set('project_params', (string) $additionalParams['project_params']);
                }
            }
        }

        return $params;
    }
}

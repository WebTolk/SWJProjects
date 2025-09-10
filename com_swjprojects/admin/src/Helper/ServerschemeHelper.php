<?php
/**
 * @package       SW JProjects
 * @subpackage
 *
 * @copyright     A copyright
 * @license       A "Slug" license name e.g. GPL2
 */

namespace Joomla\Component\SWJProjects\Administrator\Helper;

use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\SWJProjects\Administrator\Event\ServerschemeEvent;
use Joomla\Component\SWJProjects\Administrator\Serverscheme\ServerschemePlugin;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

use function defined;

defined('_JEXEC') or die;

class ServerschemeHelper
{
    use DatabaseAwareTrait;

    /**
     * @var array
     * @since 2.5.0
     */
    public static array $schemes = [];

    /**
     * @var string
     * @since 2.5.0
     */
    private static string $component_server_scheme = 'joomla';

    /**
     * @var string
     * @since 2.5.0
     */
    private static string $category_server_scheme = 'component_default';

    /**
     * @var string
     * @since 2.5.0
     */
    private static string $project_server_scheme = 'category_default';

    /**
     * @param   string  $type  Scheme type - system scheme name
     *
     * @return ServerschemePlugin
     * @since 2.5.0
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
     * Schemes cache
     * @return array
     *
     * @since 2.5.0
     */
    public static function getServerSchemesList(array $config = []): array
    {
        PluginHelper::importPlugin('swjprojects');
        $event = ServerschemeEvent::create('onGetServerschemeList', [
            'eventClass' => ServerschemeEvent::class,
            'subject'    => (new Registry()),
        ]);
        // Trigger the change state event.
        Factory::getApplication()->getDispatcher()->dispatch(
            $event->getName(),
            $event
        );

        $results = $event->getArgument('result', []);
        if (!empty($results)) {
            foreach ($results as $schema) {
                $plugin = $schema['class'];
                $plugin->setConfig($config);

                self::$schemes[$schema['type']] = $plugin;
            }
        }

        return self::$schemes;
    }

    /**
     * Compute a right server scheme name for the project.
     *
     * @param   int  $project_id
     *
     * @return string
     *
     * @since 2.5.0
     */
    public static function getServerSchemaNameForProject(int $project_id): string
    {
        $allParams       = ServerschemeHelper::getParams($project_id);
        $componentParams = $allParams->get('component_params');
        $categoryParams  = $allParams->get('category_params');
        $projectParams   = $allParams->get('project_params');

        // Set default server scheme from component params
        self::$component_server_scheme = $componentParams->get('server_scheme', 'joomla');
        self::$project_server_scheme   = self::$component_server_scheme;
        self::$category_server_scheme  = self::$component_server_scheme;

        if (!empty($categoryParams)) {
            $categoryParams = new Registry($categoryParams);
            $categoryScheme = $categoryParams->get('server_scheme', 'component_default');

            if ($categoryScheme != 'component_default') {
                self::$category_server_scheme = $categoryScheme;
                self::$project_server_scheme  = $categoryScheme;
            }
        }

        if (!empty($projectParams)) {
            $projectScheme = $projectParams->get('server_scheme', '');
            if (!empty($projectScheme)) {
                if ($projectScheme == 'component_default') {
                    self::$project_server_scheme = self::$component_server_scheme;
                } elseif ($projectScheme == 'category_default') {
                    self::$project_server_scheme = self::$category_server_scheme;
                } else {
                    self::$project_server_scheme = $projectScheme;
                }
            } else {
                // Use category scheme
                self::$project_server_scheme = self::$category_server_scheme;
            }
        }

        return self::$project_server_scheme;
    }

    /**
     * Get all params together: component, category and project
     *
     * @param   int|null  $project_id
     *
     * @return Registry
     *
     * @since 2.5.0
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
                $db->quoteName('c.params', 'category_params')
            ])
                ->from($db->quoteName('#__swjprojects_projects', 'p'))
                ->where($db->quoteName('p.id') . ' = ' . $db->quote($project_id))
                ->leftJoin(
                    $db->quoteName('#__swjprojects_categories', 'c'),
                    $db->quoteName('c.id') . ' = ' . $db->quoteName('p.catid')
                );
            $adiitionalParams = $db->setQuery($query)->loadAssoc();
            if(!empty($adiitionalParams)) {
                $adiitionalParams = new Registry($adiitionalParams);
                if($adiitionalParams->exists('category_params')) {
                    $params->set('category_params', $adiitionalParams->extract('category_params'));
                }
                if($adiitionalParams->exists('project_params')) {
                    $params->set('project_params', $adiitionalParams->extract('project_params'));
                }
            }
//            $params->set('category_params', (new Registry($adiitionalParams['category_params'])));
//            $params->set('project_params', (new Registry($adiitionalParams['project_params'])));
        }

        return $params;
    }
}
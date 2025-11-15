<?php

/**
 * @package       SW JProjects
 * @subpackage      Schemaorg.article
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Schemaorg\Softwareapplicationswjprojects\Extension;

use Joomla\CMS\Event\Plugin\System\Schemaorg\BeforeCompileHeadEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Schemaorg\SchemaorgPluginTrait;
use Joomla\CMS\Schemaorg\SchemaorgPrepareDateTrait;
use Joomla\CMS\Schemaorg\SchemaorgPrepareImageTrait;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\Priority;
use Joomla\Event\SubscriberInterface;
use Joomla\Filter\OutputFilter;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

use function count;
use function defined;

defined('_JEXEC') or die;

/**
 * Schemaorg Plugin
 *
 * @since  5.1.0
 */
final class Softwareapplicationswjprojects extends CMSPlugin implements SubscriberInterface
{
    use SchemaorgPluginTrait;
    use SchemaorgPrepareDateTrait;
    use SchemaorgPrepareImageTrait;

    /**
     * Load the language file on instantiation.
     *
     * @var    bool
     * @since  5.1.0
     */
    protected $autoloadLanguage = true;

    /**
     * The name of the schema form
     *
     * @var   string
     * @since 5.1.0
     */
    protected $pluginName = 'Softwareapplicationswjprojects';
    /**
     * @var array $allowedContext
     * @since 2.6.0
     */
    private array $allowedContext = [
        'com_swjprojects.projects',
        'com_swjprojects.project',
        'com_swjprojects.versions',
        'com_swjprojects.version',
        'com_swjprojects.documentation',
        'com_swjprojects.document',
    ];

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   5.1.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onSchemaPrepareForm'       => 'onSchemaPrepareForm',
            'onSchemaBeforeCompileHead' => ['onSchemaBeforeCompileHead', Priority::BELOW_NORMAL],
        ];
    }

    /**
     * Cleanup all Article types
     *
     * @param   BeforeCompileHeadEvent  $event  The given event
     *
     * @return  void
     *
     * @since   5.1.0
     */
    public function onSchemaBeforeCompileHead(BeforeCompileHeadEvent $event): void
    {
        $schema  = $event->getSchema();
        $context = $event->getContext();
        $context = explode('.', $context);
        $context = $context[0] . '.' . $context[1];

        if (!in_array($context, $this->allowedContext)) {
            return;
        }

        switch ($context) {
            case 'com_swjprojects.projects':
                $this->getProjectsSchema($schema);
                break;
            case 'com_swjprojects.project':
                $this->getProjectSchema($schema);
                break;
            case 'com_swjprojects.versions':
                $this->getVersionsSchema($schema);
                break;
            case 'com_swjprojects.version':
                $this->getVersionSchema($schema);
                break;
            case 'com_swjprojects.documentation':
                $this->getDocumentationSchema($schema);
                break;
            case 'com_swjprojects.document':
                $this->getDocumentSchema($schema);
                break;
            default:
                return;
                break;
        }
    }

    /**
     * Schema.org microdata for projects list
     *
     * @param $schema
     *
     *
     * @since 2.6.0
     */
    private function getProjectsSchema(&$schema): void
    {
        $model    = $this->getApplication()->bootComponent('com_swjprojects')->getMVCFactory()->createModel(
            'Projects',
            'Site',
            ['ignore_request' => false]
        );
        $projects = $model->getItems();
        $category = $model->getItem();

        $title       = ($category->title == 'root') ? $this->getApplication()->getDocument()->getTitle(
        ) : $category->title;
        $description = (empty($category->description)) ? $this->getApplication()->getDocument()->getDescription(
        ) : $category->description;

        $projects_microdata = [
            '@context'        => 'https://schema.org',
            '@type'           => 'ItemList',
            'name'            => $title,
            'description'     => $description,
            'numberOfItems'   => count($projects),
            'url'             => (new Uri(Uri::getInstance()))->toString(),
            'itemListElement' => [],
        ];

        if (!empty($projects)) {
            foreach ($projects as $project) {
                $image = $project->images->get('icon', '');
                if (empty($image)) {
                    $image = $project->images->get('cover', '');
                }

                if (!empty($image)) {
                    $image = (new Uri(Uri::root()))->setPath('/' . $this->prepareImage($image))->toString();
                }

                $list_item = [
                    '@type'                => 'SoftwareApplication',
                    'name'                 => $project->title,
                    'url'                  => (new Uri(Uri::root()))->setPath($project->link)->toString(),
                    'description'          => (!empty($project->introtext) ? OutputFilter::cleanText($project->introtext) : ''),                    'applicationCategory'  => $project->category->title,
                    'softwareVersion'      => (!empty($project->version) ? $project->version->version : ''),
                    'downloadUrl'          => (new Uri(Uri::root()))->setPath($project->download)->toString(),
                    'image'                => (!empty($image) ? $this->prepareImage($image) : ''),
                    'operatingSystem'      => 'ANY',
                    'interactionStatistic' => [
                        [
                            '@type'                => 'InteractionCounter',
                            'interactionType'      => 'https://schema.org/DownloadAction',
                            'userInteractionCount' => (int)$project->downloads ?? 0,
                        ],
                        [
                            '@type'                => 'InteractionCounter',
                            'interactionType'      => 'https://schema.org/ViewAction',
                            'userInteractionCount' => (int)$project->hits ?? 0,
                        ],
                    ],
                ];

                if ($project->joomla) {
                    $list_item['softwareRequirements'] = 'Joomla CMS';
                }
                if ($project->version && !empty($project->version->version)) {
                    $list_item['applicationSubCategory'] = $project->version->version;
                }
                if (!empty($project->categories)) {
                    $list_item['applicationSubCategory'] = implode(
                        ', ',
                        ArrayHelper::getColumn($project->categories, 'title')
                    );
                }

                if ($project->download_type == 'paid' && !empty($price = $project->payment->get('price',''))) {
                    $price = explode(' ',$price);
                    $list_item['offers'] = [
                        [
                            '@type'         => 'Offer',
                            'price'         => $price[0],
                            'priceCurrency' => $price[1],
                            'availability'  => 'https://schema.org/InStock',
                        ],
                    ];
                } elseif ($project->download_type == 'free') {
                    $list_item['isAccessibleForFree'] = true;
                }

                $metadata = new Registry($project->metadata);
                if (!empty($keywords = $metadata->get('keywords', ''))) {
                    $list_item['keywords'] = explode(',', $keywords);
                }

                $projects_microdata['itemListElement'][] = $list_item;
            }
        }
        $graph   = $schema->get('@graph');
        $graph[] = $projects_microdata;
        $schema->set('@graph', $graph);
    }

    private function getProjectSchema(&$schema): void
    {
    }

    private function getVersionsSchema(&$schema): void
    {
    }

    private function getVersionSchema(&$schema): void
    {
    }

    private function getDocumentationSchema(&$schema): void
    {
    }

    private function getDocumentSchema(&$schema): void
    {
    }

}

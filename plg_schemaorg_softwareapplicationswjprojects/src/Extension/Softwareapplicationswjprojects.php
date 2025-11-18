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
use Joomla\CMS\Language\Text;
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
 * @since  2.6.0
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

        $title              = ($category->title == 'root') ? $this->getApplication()->getDocument()->getTitle(
        ) : $category->title;
        $description        = (empty($category->description)) ? $this->getApplication()->getDocument()->getDescription(
        ) : $category->description;
        $current_url        = (new Uri(Uri::getInstance()))->toString();
        $projects_microdata = [
            '@type'            => 'ItemList',
            'name'             => $title,
            'description'      => $description,
            'numberOfItems'    => count($projects),
            'url'              => $current_url,
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                'url'   => $current_url,
            ],
            'itemListElement'  => [],
        ];

        if (!empty($projects)) {
            foreach ($projects as $project) {
                $projects_microdata['itemListElement'][] = $this->buildProjectMicrodata($project);
            }
        }
        $graph   = $schema->get('@graph');
        $graph[] = $projects_microdata;

        $about_data = [
            '@id'       => Uri::root().'#/schema/ItemList/base'
        ];
        $this->fixAboutInWebPage($about_data,$graph);

        $schema->set('@graph', $graph);
    }

    /**
     * Build microdata array for project
     *
     * @param $project
     *
     * @return array
     *
     * @since 2.6.0
     */
    private function buildProjectMicrodata($project): array
    {
        $image = $project->images->get('icon', '');
        if (empty($image)) {
            $image = $project->images->get('cover', '');
        }

        if (!empty($image)) {
            $image = (new Uri(Uri::root()))->setPath('/' . $this->prepareImage($image))->toString();
        }
        $current_url       = (new Uri(Uri::root()))->setPath($project->link)->toString();
        $project_microdata = [
            '@type'                => 'SoftwareApplication',
            'name'                 => $project->title,
            'url'                  => $current_url,
            'description'          => (!empty($project->introtext) ? OutputFilter::cleanText($project->introtext) : ''),
            'applicationCategory'  => $project->category->title,
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
            'mainEntityOfPage'     => [
                '@type' => 'WebPage',
                'url'   => $current_url,
            ],
        ];

        if ($project->joomla) {
            $project_microdata['softwareRequirements'] = 'Joomla';
        }
        if ($project->version && !empty($project->version->version)) {
            $project_microdata['applicationSubCategory'] = $project->version->version;
        }
        if (!empty($project->categories)) {
            $project_microdata['applicationSubCategory'] = implode(
                ', ',
                ArrayHelper::getColumn($project->categories, 'title')
            );
        }
        $project_microdata['isAccessibleForFree'] = ($project->download_type == 'free');
        $metadata                                 = new Registry($project->metadata);
        if (!empty($keywords = $metadata->get('keywords', ''))) {
            $project_microdata['keywords'] = explode(',', $keywords);
        }

        return $project_microdata;
    }

    /**
     * Schema.org microdata for project
     *
     * @param $schema
     *
     *
     * @since 2.6.0
     */
    private function getProjectSchema(&$schema): void
    {
        $model   = $this->getApplication()
            ->bootComponent('com_swjprojects')
            ->getMVCFactory()
            ->createModel(
                'Project',
                'Site',
                ['ignore_request' => false]
            );
        $project = $model->getItem();

        $graph   = $schema->get('@graph');
        $graph[] = $this->buildProjectMicrodata($project);

        $about_data = [
            '@id'       => Uri::root().'#/schema/SoftwareApplication/base'
        ];
        $this->fixAboutInWebPage($about_data,$graph);

        $schema->set('@graph', $graph);
    }

    /**
     * @param $schema
     *
     *
     * @since 2.6.0
     */
    private function getVersionsSchema(&$schema): void
    {
        $model    = $this->getApplication()
            ->bootComponent('com_swjprojects')
            ->getMVCFactory()
            ->createModel(
                'Versions',
                'Site',
                ['ignore_request' => false]
            );
        $versions = $model->getItems();
        if (empty($versions)) {
            return;
        }

        $title              = Text::_('COM_SWJPROJECTS_VERSIONS') . ' ' . $versions[0]->project_title;
        $description        = !empty($versions[0]->project_introtext) ? OutputFilter::cleanText(
            $versions[0]->project_introtext
        ) : $this->getApplication()->getDocument()->getDescription();
        $current_url        = (new Uri(Uri::getInstance()))->toString();
        $versions_microdata = [
            '@type'            => 'ItemList',
            'name'             => $title,
            'description'      => $description,
            'numberOfItems'    => count($versions),
            'url'              => $current_url,
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                'url'   => $current_url,
            ],
            'itemListElement'  => [],
        ];

        foreach ($versions as $version) {
            $versions_microdata['itemListElement'][] = $this->buildVersionMicrodata($version);
        }

        $graph   = $schema->get('@graph');
        $graph[] = $versions_microdata;
        $about_data = [
            '@id'       => Uri::root().'#/schema/ItemList/base'
        ];
        $this->fixAboutInWebPage($about_data,$graph);
        $schema->set('@graph', $graph);
    }

    /**
     * @param $version
     *
     * @return array
     *
     * @since 2.6.0
     */
    private function buildVersionMicrodata($version): array
    {
        $image = $version->project->images->get('icon', '');
        if (empty($image)) {
            $image = $version->project->images->get('cover', '');
        }

        if (!empty($image)) {
            $image = (new Uri(Uri::root()))->setPath('/' . $this->prepareImage($image))->toString();
        }
        $current_url       = (new Uri(Uri::root()))->setPath($version->link)->toString();
        $version_microdata = [
            '@type'                => 'SoftwareApplication',
            'name'                 => $version->title,
            'url'                  => $current_url,
            'description'          => (!empty($version->project_introtext) ? OutputFilter::cleanText(
                $version->project_introtext
            ) : ''),
            'applicationCategory'  => $version->category_title,
            'softwareVersion'      => (!empty($version->version) ? $version->version->version : ''),
            'downloadUrl'          => (new Uri(Uri::root()))->setPath($version->download)->toString(),
            'datePublished'        => $version->date,
            'image'                => (!empty($image) ? $this->prepareImage($image) : ''),
            'mainEntityOfPage'     => [
                '@type' => 'WebPage',
                'url'   => $current_url,
            ],
            'operatingSystem'      => 'ANY',
            'interactionStatistic' => [
                [
                    '@type'                => 'InteractionCounter',
                    'interactionType'      => 'https://schema.org/DownloadAction',
                    'userInteractionCount' => (int)$version->downloads ?? 0,
                ],
            ]
        ];

        if ($version->joomla) {
            $version_microdata['softwareRequirements'] = 'Joomla';
            if ($joomla_version = $version->joomla->get('version', '')) {
                $version_microdata['softwareRequirements'] .= ' ' . $joomla_version;
            }
        }

        if ($version->version && !empty($version->version->version)) {
            $version_microdata['applicationSubCategory'] = $version->version->version;
        }

        $version_microdata['isAccessibleForFree'] = ($version->download_type == 'free');

        $metadata = new Registry($version->metadata);
        if (!empty($keywords = $metadata->get('keywords', ''))) {
            $version_microdata['keywords'] = explode(',', $keywords);
        }

        // Release note only for single version page
        if (!empty($version->changelog) && $this->getApplication()->getInput()->get('view') == 'version') {
            $changelog = '';
            foreach ($version->changelog as $changelog_item) {
                $changelog .= Text::_(
                        'COM_SWJPROJECTS_VERSION_CHANGELOG_ITEM_TYPE_' . strtoupper($changelog_item['type'])
                    ) . ': ' . $changelog_item['title'] . '. ' . OutputFilter::cleanText(
                        $changelog_item['description']
                    ) . ' ';
            }
            if (!empty($changelog)) {
                $version_microdata['releaseNotes'] = $changelog;
            }
        }


        return $version_microdata;
    }

    private function getVersionSchema(&$schema): void
    {
        $model   = $this->getApplication()
            ->bootComponent('com_swjprojects')
            ->getMVCFactory()
            ->createModel(
                'Version',
                'Site',
                ['ignore_request' => false]
            );
        $version = $model->getItem();
        $graph   = $schema->get('@graph');
        $graph[] = $this->buildVersionMicrodata($version);

        $about_data = [
            '@id'       => Uri::root().'#/schema/SoftwareApplication/base'
        ];
        $this->fixAboutInWebPage($about_data,$graph);
        $schema->set('@graph', $graph);
    }

    /**
     * @param $schema
     *
     *
     * @since 2.6.0
     */
    private function getDocumentationSchema(&$schema): void
    {
        $model         = $this->getApplication()
            ->bootComponent('com_swjprojects')
            ->getMVCFactory()
            ->createModel(
                'Documentation',
                'Site',
                ['ignore_request' => false]
            );
        $documentation = $model->getItems();
        if (empty($documentation)) {
            return;
        }
        $title                   = Text::_('COM_SWJPROJECTS_DOCUMENTATION') . ' ' . $documentation[0]->project_title;
        $description             = !empty($documentation[0]->project_introtext) ? OutputFilter::cleanText(
            $documentation[0]->project_introtext
        ) : $this->getApplication()->getDocument()->getDescription();
        $current_url             = (new Uri(Uri::getInstance()))->toString();
        $documentation_microdata = [
            '@type'            => 'ItemList',
            'name'             => $title,
            'description'      => $description,
            'numberOfItems'    => count($documentation),
            'url'              => $current_url,
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                'url'   => $current_url,
            ],
            'itemListElement'  => [],
        ];

        foreach ($documentation as $document) {
            $documentation_microdata['itemListElement'][] = $this->buildDocumentMicrodata($document);
        }

        $graph   = $schema->get('@graph');
        $graph[] = $documentation_microdata;

        $about_data = [
            '@id'       => Uri::root().'#/schema/ListItem/base'
        ];
        $this->fixAboutInWebPage($about_data,$graph);
        $schema->set('@graph', $graph);
    }

    /**
     * @param $schema
     *
     *
     * @since 2.6.0
     * @return void
     */
    private function getDocumentSchema(&$schema): void
    {
        $model   = $this->getApplication()
            ->bootComponent('com_swjprojects')
            ->getMVCFactory()
            ->createModel(
                'Document',
                'Site',
                ['ignore_request' => false]
            );
        $document = $model->getItem();
        $graph   = $schema->get('@graph');
        $graph[] = $this->buildDocumentMicrodata($document);

        $about_data = [
            '@id'       => Uri::root().'#/schema/TechArticle/base'
        ];
        $this->fixAboutInWebPage($about_data,$graph);
        $schema->set('@graph', $graph);
    }

    /**
     * @param $document
     *
     * @return array
     *
     * @since 2.6.0
     */
    private function buildDocumentMicrodata($document): array
    {
        $current_url        = (new Uri(Uri::root()))->setPath($document->link)->toString();
        $document_microdata = [
            '@type'               => 'TechArticle',
            'headline'            => $document->title,
            'url'                 => $current_url,
            'description'         => (!empty($document->introtext) ? OutputFilter::cleanText(
                $document->introtext
            ) : ''),
            'mainEntityOfPage'    => [
                '@type' => 'WebPage',
                'url'   => $current_url,
            ]
        ];

        $metadata = new Registry($document->metadata);
        if (!empty($keywords = $metadata->get('keywords', ''))) {
            $document_microdata['keywords'] = explode(',', $keywords);
        }

        return $document_microdata;
    }

    /**
     * Fix `about` section in `WebPage` microdata. Change it direct in microdata graph
     *
     * @param array $about_data
     * @param array $graph
     *
     *
     * @since 2.6.0
     */
    private function fixAboutInWebPage(array $about_data, array &$graph):void
    {
        foreach ($graph as &$graph_item) {
            if($graph_item['@type'] == 'WebPage') {
                $graph_item['about'] = $about_data;
            }
        }
    }
}

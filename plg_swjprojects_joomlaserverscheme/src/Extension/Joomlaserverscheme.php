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

namespace Joomla\Plugin\Swjprojects\Joomlaserverscheme\Extension;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\SWJProjects\Administrator\Event\ServerschemeEvent;
use Joomla\Component\SWJProjects\Administrator\Serverscheme\ServerschemePlugin;
use Joomla\Component\SWJProjects\Site\Helper\RouteHelper;
use Joomla\Event\SubscriberInterface;
use Joomla\Filesystem\File;
use SimpleXMLElement;

use function defined;

defined('_JEXEC') or die;

/**
 * Joomla data schema for the SW JProjects component
 * update server: projects list, project and changelog of the project
 *
 * @since  4.0.0
 */
final class Joomlaserverscheme extends ServerschemePlugin implements SubscriberInterface
{
    /**
     * Load the language file on instantiation.
     *
     * @var    bool
     * @since  4.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * @var string
     * @since 2.5.0
     */
    protected string $mimeType = 'application/xml';

    /**
     * @var string
     * @since 2.5.0
     */
    protected string $charset = 'utf-8';

    /**
     * @var string
     * @since 2.5.0
     */
    protected string $name = 'Joomla CMS';

    /**
     * @var string
     * @since 2.5.0
     */
    protected string $type = 'joomla';

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   4.0.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onGetServerschemeList' => 'onGetServerschemeList',
        ];
    }


    /**
     * @param ServerschemeEvent $event
     *
     *
     * @since 1.0.0
     */
    public function onGetServerschemeList(ServerschemeEvent $event): void
    {
        $event->addResult([
            'type'  => $this->getType(),
            'name'  => $this->getName(),
            'class' => $this,
        ]);
    }

    public function renderOutput(array $data):string
    {
        switch ($this->getScheme()) {
            case 'updates':
                $output = $this->buildProjectUpdatesXml($data);
                break;
            case 'changelogs':
                $output = $this->buildChangelogsXml($data);
                break;
            case 'collection':
            default:
                $output = $this->buildCollectionXml($data);
                break;
        }

        return $output;
    }

    /**
     * Generate a XML for Joomla extensions update server
     *
     * @param   array  $data
     *
     * @return string
     *
     * @since 2.5.0
     */
    protected function buildProjectUpdatesXml(array $data):string
    {
        $updates    = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><updates/>');
        $site_root  = Uri::getInstance()->toString(['scheme', 'host', 'port']);
        $files_root = $this->config['filesPath']['versions'];
        foreach ($data as $item) {
            // Add to updates
            $update = $updates->addChild('update');
            $update->addChild('name', $item->name);
            $update->addChild('description', $item->description);
            $update->addChild('element', $item->element);
            $update->addChild('type', $item->type);
            if ($item->type == 'plugin') {
                $update->addChild('folder', $item->folder);
            }
            $update->addChild('client', $item->client);
            $update->addChild('version', $item->version);

            $infourl = $update->addChild('infourl', $site_root . $item->link);
            $infourl->addAttribute('title', $item->name);

            if ($item->file) {
                $downloads = $update->addChild('downloads');

                $downloadurl = $downloads->addChild('downloadurl', $site_root . $item->download);
                $downloadurl->addAttribute('type', 'full');
                $downloadurl->addAttribute('format', File::getExt($item->file));

                $file_path_from_root = $files_root . '/' . $item->id . '/' . $item->file;
                if (is_file($file_path_from_root)) {
                    $update->addChild('sha256', hash_file('sha256', $file_path_from_root));
                    $update->addChild('sha384', hash_file('sha384', $file_path_from_root));
                    $update->addChild('sha512', hash_file('sha512', $file_path_from_root));
                }
                $update->addChild(
                    'changelogurl',
                    $site_root . Route::_(RouteHelper::getJChangelogRoute(null, $item->element))
                );
            }

            $tags = $update->addChild('tags');
            $tags->addChild('tag', $item->tag);
            $targetPlatform = $update->addChild('targetPlatform');
            $targetPlatform->addAttribute('name', 'joomla');
            $targetPlatform->addAttribute('version', '');

        }

        return $updates->asXML();
    }

    /**
     * @param   array  $data
     *
     *
     * @return string
     * @since 2.5.0
     */
    protected function buildChangelogsXml(array $data):string
    {
        $changelogs    = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><changelogs/>');

        foreach ($data as $item) {
            // Add to changelogs
            $changelog = $changelogs->addChild('changelog');

            $changelog->addChild('element', $item->element);
            $changelog->addChild('type', $item->type);
            $changelog->addChild('version', $item->version);
            $changelog_data = [];

            foreach ($item->changelog->toObject() as $value)
            {
                $value_type = (empty($value->type)) ? 'info' : $value->type;
                $changelog_data[$value_type][] = $value->title.' '.$value->description;
            }
            if(!empty($changelog_data)){
                foreach ($changelog_data as $key => $value){
                    $changelog_child =	$changelog->addChild($key);
                    foreach ($value as $v){
                        $changelog_child->addChild('item',$v);
                    }
                }
            }
        }

        return $changelogs->asXML();
    }

    /**
     * @param   array  $data
     *
     * @return string
     *
     * @since 2.5.0
     */
    protected function buildCollectionXml(array $data): string
    {
        $extensionset = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><extensionset/>');
        $site_root    = Uri::getInstance()->toString(['scheme', 'host', 'port']);
        foreach ($data as $item) {
            $extension = $extensionset->addChild('extension');
            $extension->addAttribute('name', $item->title);
            $extension->addAttribute('element', $item->element);
            $extension->addAttribute('type', $item->type);
            if ($item->type == 'plugin') {
                $extension->addAttribute('folder', $item->folder);
            }
            $extension->addAttribute('client', $item->client);
            $extension->addAttribute('detailsurl', $site_root . $item->link);
            $extension->addAttribute('version', $item->version);
        }

        return $extensionset->asXML();
    }
}

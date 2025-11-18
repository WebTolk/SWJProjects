<?php
/**
 * @package       SW JProjects
 * @version       2.6.0
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
 */

namespace Joomla\Component\SWJProjects\Administrator\Serverscheme;
use Joomla\CMS\Plugin\CMSPlugin;

use function defined;

defined('_JEXEC') or die;

abstract class ServerschemePlugin extends CMSPlugin
{

    /**
     * @var string
     * @since 2.5.0
     */
    protected string $scheme = 'collection';
    /**
     * @var
     * @since 2.5.0
     */
    protected string $mimeType = '';
    /**
     * @var
     * @since 2.5.0
     */
    protected string $charset = '';

    /**
     * Scheme's name for user interface. Language constant.
     *
     * @var string
     * @since 2.5.0
     */
    protected string $name = '';

    /**
     * Scheme's unique system name
     *
     * @var string
     * @since 2.5.0
     */
    protected string $type = '';

    /**
     * SW JProjects model params
     *
     * @var array
     * @since 2.5.0
     */
    protected array $config = [];

    public function setConfig($config = [])
    {
        $this->config = $config;
    }

    /**
     * Set the scheme for rendering:
     * - `collection`
     * - `updates`
     * - `changelogs`
     *
     * @param   string  $scheme
     *
     * @return $this
     *
     * @since 2.5.0
     */
    public function setScheme(string $scheme = 'collection'): self
    {
        $this->scheme = $scheme;
        return $this;
    }

    /**
     * Return the scheme for rendering:
     * - `collection`
     * - `updates`
     * - `changelogs`
     *
     * @return string
     *
     * @since 2.5.0
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getMimeType():string
    {
        return $this->mimeType;
    }
    public function getCharset():string
    {
        return $this->charset;
    }

    /**
     * Outputs the updates or changelogs server data
     *
     * @param   array  $data
     *
     * @return mixed
     *
     * @since 2.5.0
     */
    abstract public function renderOutput(array $data): mixed;

    /**
     * Get the server schema name for user interface.
     * It would be language constant.
     *
     * @return string
     *
     * @since 2.5.0
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the server schema system name
     *
     * @return string
     *
     * @since 2.5.0
     */
    public function getType(): string
    {
        return $this->type;
    }
}
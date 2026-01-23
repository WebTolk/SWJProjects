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

namespace Joomla\Component\SWJProjects\Site\View\JChangelog;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use function defined;

defined('_JEXEC') or die;

class HtmlView extends BaseHtmlView
{
	/**
	 * Update server data.
	 *
	 * @var  string
	 *
	 * @since  1.0.0
	 */
	protected $data;

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse.
	 *
	 * @throws  \Exception
	 *
	 * @since  1.0.0
	 */
	public function display($tpl = null)
	{
        $model = $this->getModel();
        $this->data = $model->getData();

        // Set response
        $app = Factory::getApplication();
        $app->setHeader('Content-Type', $this->data['mimetype'].'; charset='.$this->data['charset'], true);

        $app->sendHeaders();

        echo $this->data['data'] ?? '';

        $app->close();
	}
}

<?php
/**
 * @package       SW JProjects
 * @version       2.4.0.1
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
 */

namespace Joomla\Component\SWJProjects\Site\View\JChangelog;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use function defined;
use function implode;

class HtmlView extends BaseHtmlView
{
	/**
	 * Update server xml string.
	 *
	 * @var  string
	 *
	 * @since  1.0.0
	 */
	protected $xml;

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
		$this->xml = $this->get('XML');

		// Check for errors
		if ($errors = $this->get('Errors'))
		{
			throw new \Exception(implode('\n', $errors), 500);
		}

		// Set xml response
		$app = Factory::getApplication();
		$app->setHeader('Content-Type', 'application/xml; charset=utf-8', true);

		$app->sendHeaders();

		echo $this->xml;

		$app->close();
	}
}

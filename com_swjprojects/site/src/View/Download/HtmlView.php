<?php
/**
 * @package       SW JProjects
 * @version       2.4.0
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
 */

namespace Joomla\Component\SWJProjects\Site\View\Download;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use function defined;
use function file_get_contents;
use function implode;
use function ob_end_clean;

class HtmlView extends BaseHtmlView
{
	/**
	 * File path.
	 *
	 * @var  string
	 *
	 * @since  1.2.0
	 */
	protected $file;

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse.
	 *
	 * @throws  \Exception
	 *
	 * @since  1.2.0
	 */
	public function display($tpl = null)
	{
		$this->file = $this->get('File');

		// Check for errors
		if ($errors = $this->get('Errors'))
		{
			throw new \Exception(implode('\n', $errors), 500);
		}

		// Set headers
		$app = Factory::getApplication();

		ob_end_clean();
		$app->clearHeaders();
		$app->setHeader('Content-Type', $this->file->type, true);
		$app->setHeader('Content-Disposition', 'attachment; filename=' . $this->file->name . ';', true);
		$app->sendHeaders();

		// Read file
		if ($context = @file_get_contents($this->file->path))
		{
			echo $context;
			$this->getModel()->setDownload();
		}

		// Close application
		$app->close();
	}
}

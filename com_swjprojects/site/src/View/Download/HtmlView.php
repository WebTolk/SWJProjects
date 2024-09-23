<?php
/*
 * @package    SW JProjects
 * @version    2.1.1
 * @author     Sergey Tolkachyov
 * @сopyright  Copyright (c) 2018 - 2024 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
 */

namespace Joomla\Component\SWJProjects\Site\View\Download;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

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

<?php
/**
 * @package    SW JProjects Component
 * @version    1.5.1
 * @author     Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2018 - 2019 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;

class SWJProjectsViewDownload extends HtmlView
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
	 * @throws  Exception
	 *
	 * @since  1.2.0
	 */
	public function display($tpl = null)
	{
		$this->file = $this->get('File');

		// Check for errors
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode('\n', $errors), 500);
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
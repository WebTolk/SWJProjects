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

class SWJProjectsViewJUpdate extends HtmlView
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
	 * @throws  Exception
	 *
	 * @since  1.0.0
	 */
	public function display($tpl = null)
	{
		$this->xml = $this->get('XML');

		// Check for errors
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode('\n', $errors), 500);
		}

		// Set xml response
		$app = Factory::getApplication();
		$app->setHeader('Content-Type', 'application/xml; charset=utf-8', true);

		$app->sendHeaders();

		echo $this->xml;

		$app->close();
	}
}
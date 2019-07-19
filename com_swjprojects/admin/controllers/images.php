<?php
/**
 * @package    SW JProjects Component
 * @version    __DEPLOY_VERSION__
 * @author     Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2018 - 2019 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Session\Session;

class SWJProjectsControllerImages extends BaseController
{
	/**
	 * Method to load single image.
	 *
	 * @throws  Exception
	 *
	 * @return  bool  Send json response with image src on success, empty string on failure.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function loadImage()
	{
		// Check token
		$this->checkToken();

		// Get data
		$section  = $this->input->get('section');
		$pk       = $this->input->getInt('pk');
		$filename = $this->input->getCmd('filename');
		$language = $this->input->getCmd('language');

		// Get image
		return ($image = SWJProjectsHelperImages::getImage($section, $pk, $filename, $language)) ? $this->setResponse($image)
			: $this->setResponse($image, Text::_('COM_SWJPROJECTS_ERROR_IMAGE_NOT_FOUND'));
	}

	/**
	 * Method to delete single image.
	 *
	 * @throws  Exception
	 *
	 * @return  bool  Send json response with true on success, false on failure.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function deleteImage()
	{
		// Check token
		$this->checkToken();

		// Get data
		$section  = $this->input->get('section');
		$pk       = $this->input->getInt('pk');
		$filename = $this->input->getCmd('filename');
		$language = $this->input->getCmd('language');

		// Delete image
		return ($result = SWJProjectsHelperImages::deleteImage($section, $pk, $filename, $language)) ? $this->setResponse($result)
			: $this->setResponse($result, Text::_('COM_SWJPROJECTS_ERROR_IMAGE_NOT_DELETED'), true);
	}

	/**
	 * Method to delete single image.
	 *
	 * @throws  Exception
	 *
	 * @return  bool  Send json response with image src on success, false on failure.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function uploadImage()
	{
		$this->checkToken();

		// Get data
		$section  = $this->input->get('section');
		$pk       = $this->input->getInt('pk');
		$filename = $this->input->getCmd('filename');
		$language = $this->input->getCmd('language');
		$images   = $this->input->files->get('images', array(), 'array');
		$image    = (!empty($images[0])) ? $images[0] : false;

		// Check image
		if (!$image)
		{
			return $this->setResponse($image, Text::_('COM_SWJPROJECTS_ERROR_IMAGE_NOT_FOUND'));
		}

		return ($result = SWJProjectsHelperImages::uploadImage($section, $pk, $filename, $language, $image)) ? $this->setResponse($result)
			: $this->setResponse($result, Text::_('COM_SWJPROJECTS_ERROR_IMAGE_NOT_UPLOADED'), true);
	}

	/**
	 * Method to set json response.
	 *
	 * @param   mixed   $response  Response data.
	 * @param   string  $message   Response message text.
	 * @param   bool    $error     Response error, send true if need set error response.
	 *
	 * @throws  Exception
	 *
	 * @return   True on success, false on failure.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function setResponse($response = null, $message = null, $error = false)
	{
		$app = Factory::getApplication();
		$app->setHeader('Content-Type', 'application/json; charset=utf-8', true);
		$app->sendHeaders();
		echo new JsonResponse($response, $message, $error);
		$app->close(200);

		return (!$error);
	}

	/**
	 * Checks for a form token in the request.
	 *
	 * @param   string  $method  The request method in which to look for the token key.
	 * @param   bool    $json    Set json response or throw.
	 *
	 * @throws  Exception
	 *
	 * @return  boolean  True if found and valid, false otherwise.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function checkToken($method = 'post', $json = true)
	{
		if (!$valid = Session::checkToken($method))
		{
			if ($json)
			{
				$this->setResponse(null, Text::_('JINVALID_TOKEN_NOTICE'), true);
			}
			else
			{
				throw new Exception(Text::_('JINVALID_TOKEN_NOTICE'), 403);
			}
		}

		return $valid;
	}
}
<?php
/*
 * @package    SW JProjects Component
 * @version    1.6.4
 * @author Septdir Workshop, <https://septdir.com>, Sergey Tolkachyov <https://web-tolk.ru>
 * @сopyright (c) 2018 - April 2023 Septdir Workshop, Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link https://septdir.com, https://web-tolk.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Session\Session;
use Joomla\Registry\Registry;

class SWJProjectsControllerImages extends BaseController
{
	/**
	 * Method to load single image.
	 *
	 * @throws  Exception
	 *
	 * @return  bool  Send json response with image src on success, empty string on failure.
	 *
	 * @since  1.3.0
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
	 * @since  1.3.0
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
	 * Method to upload single image.
	 *
	 * @throws  Exception
	 *
	 * @return  bool  Send json response with image src on success, false on failure.
	 *
	 * @since  1.3.0
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
			return $this->setResponse($image, Text::_('COM_SWJPROJECTS_ERROR_IMAGE_NOT_FOUND'), true);
		}

		return ($result = SWJProjectsHelperImages::uploadImage($section, $pk, $filename, $language, $image)) ? $this->setResponse($result)
			: $this->setResponse($result, Text::_('COM_SWJPROJECTS_ERROR_IMAGE_NOT_UPLOADED'), true);
	}

	/**
	 * Method to load multiple images result.
	 *
	 * @throws  Exception
	 *
	 * @return  bool  Send json response with images src and field html on success, empty string on failure.
	 *
	 * @since  1.3.0
	 */
	public function loadImages()
	{
		// Check token
		$this->checkToken();

		// Get data
		$id       = $this->input->getCmd('id');
		$section  = $this->input->getCmd('section');
		$pk       = $this->input->getInt('pk');
		$folder   = $this->input->getCmd('folder');
		$language = $this->input->getCmd('language');
		$name     = $this->input->get('name', '', 'raw');
		$values   = new Registry($this->input->get('values', '', 'raw'));

		// Get images
		if (!$images = SWJProjectsHelperImages::getImages($section, $pk, $folder, $values, $language))
		{
			return $this->setResponse($images, Text::_('COM_SWJPROJECTS_ERROR_IMAGES_NOT_FOUND'));
		}

		// Prepare response
		$response = array(
			'images' => $images,
			'html'   => LayoutHelper::render('components.swjprojects.field.images.result',
				array('id' => $id, 'name' => $name, 'images' => $images)),
		);

		return $this->setResponse($response);
	}

	/**
	 * Method to upload multiple images.
	 *
	 * @throws  Exception
	 *
	 * @return  bool  Send json response with new images names on success, empty string on failure.
	 *
	 * @since  1.3.0
	 */
	public function uploadImages()
	{
		// Check token
		$this->checkToken();

		// Get data
		$section  = $this->input->getCmd('section');
		$pk       = $this->input->getInt('pk');
		$folder   = $this->input->getCmd('folder');
		$language = $this->input->getCmd('language');
		$values   = new Registry($this->input->get('values', '', 'raw'));
		$images   = $this->input->files->get('images', false, 'array');

		// Check images
		if (!$images)
		{
			return $this->setResponse($images, Text::_('COM_SWJPROJECTS_ERROR_IMAGES_NOT_FOUND'), true);
		}

		return ($uploads = SWJProjectsHelperImages::uploadImages($section, $pk, $folder, $values, $language, $images)) ?
			$this->setResponse($uploads)
			: $this->setResponse($uploads, Text::_('COM_SWJPROJECTS_ERROR_IMAGES_NOT_UPLOADED'),true);
	}

	/**
	 * Method to change multiple images.
	 *
	 * @throws  Exception
	 *
	 * @return  bool  Send json response with image src on success, empty string on failure.
	 *
	 * @since  1.3.0
	 */
	public function changeImages()
	{
		// Check token
		$this->checkToken();

		// Get data
		$section  = $this->input->getCmd('section');
		$pk       = $this->input->getInt('pk');
		$folder   = $this->input->getCmd('folder');
		$language = $this->input->getCmd('language');
		$filename = $this->input->getCmd('filename');
		$images   = $this->input->files->get('images', array(), 'array');
		$image    = (!empty($images[0])) ? $images[0] : false;

		// Check image
		if (!$image)
		{
			return $this->setResponse($image, Text::_('COM_SWJPROJECTS_ERROR_IMAGE_NOT_FOUND'), true);
		}

		return ($result = SWJProjectsHelperImages::changeImages($section, $pk, $folder, $language, $filename, $image)) ?
			$this->setResponse($result)
			: $this->setResponse($result, Text::_('COM_SWJPROJECTS_ERROR_IMAGE_NOT_UPLOADED'), true);
	}

	/**
	 * Method to delete multiple images.
	 *
	 * @throws  Exception
	 *
	 * @return  bool  Send json response with true on success, false on failure.
	 *
	 * @since  1.3.0
	 */
	public function deleteImages()
	{
		// Check token
		$this->checkToken();

		// Get data
		$section  = $this->input->get('section');
		$pk       = $this->input->getInt('pk');
		$folder   = $this->input->getCmd('folder');
		$language = $this->input->getCmd('language');
		$filename = $this->input->getCmd('filename');

		// Delete images
		return ($result = SWJProjectsHelperImages::deleteImages($section, $pk, $folder, $language, $filename)) ? $this->setResponse($result)
			: $this->setResponse($result, Text::_('COM_SWJPROJECTS_ERROR_IMAGE_NOT_DELETED'), true);
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
	 * @since  1.3.0
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
	 * @since  1.3.0
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
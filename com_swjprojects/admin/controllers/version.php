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
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;

class SWJProjectsControllerVersion extends FormController
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var  string
	 *
	 * @since  1.0.0
	 */
	protected $text_prefix = 'COM_SWJPROJECTS_VERSION';

	/**
	 * Method to reload a record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key.
	 *
	 * @throws  Exception
	 *
	 * @since  1.0.0
	 */
	public function reload($key = null, $urlVar = null)
	{
		// Check for request forgeries
		$this->checkToken();

		$app   = Factory::getApplication();
		$model = $this->getModel();
		$data  = $this->input->post->get('jform', array(), 'array');

		// Determine the name of the primary key for the data
		if (empty($key))
		{
			$key = $model->getTable()->getKeyName();
		}

		// To avoid data collisions the urlVar may be different from the primary key
		if (empty($urlVar))
		{
			$urlVar = $key;
		}

		$recordId = $this->input->getInt($urlVar);

		// Populate the row id from the session
		$data[$key] = $recordId;

		// Check if it is allowed to edit or create the data
		if (($recordId && !$this->allowEdit($data, $key)) || (!$recordId && !$this->allowAdd($data)))
		{
			$this->setRedirect(
				Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list
					. $this->getRedirectToListAppend(), false
				)
			);
			$this->redirect();
		}

		// The redirect url
		$redirectUrl = Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item
			. $this->getRedirectToItemAppend($recordId, $urlVar),
			false
		);

		// Validate the posted data
		$form           = $model->getForm($data, false);
		$translateForms = $model->getTranslateForms(false);

		if (!$form || !$translateForms)
		{
			$app->enqueueMessage($model->getError(), 'error');

			$this->setRedirect($redirectUrl);
			$this->redirect();
		}

		// Filter base data
		$value = $form->filter($data);

		// Filter translates data
		$value['translates'] = array();

		foreach (SWJProjectsHelperTranslation::getCodes() as $code)
		{
			$translateForm = (!empty($translateForms[$code])) ? $translateForms[$code] : false;
			if ($translateForm)
			{
				$translate                  = $data['translates'][$code];
				$value['translates'][$code] = $translateForm->filter($translate);
			}
		}

		// Save the value in the session
		$app->setUserState($this->option . '.edit.' . $this->context . '.data', $value);

		// Redirect
		$this->setRedirect($redirectUrl);
		$this->redirect();
	}

	/**
	 * Method to save a record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key.
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since  1.0.0
	 */
	public function save($key = null, $urlVar = null)
	{
		// Check for request forgeries
		$this->checkToken();

		// Set file to data
		$data  = $this->input->post->get('jform', array(), 'array');
		$files = $this->input->files->get('jform', '', 'raw');

		$data['file_upload'] = (!empty($files['file_upload'])) ? $files['file_upload'] : '';

		$this->input->post->set('jform', $data);

		return parent::save($key, $urlVar);
	}
}
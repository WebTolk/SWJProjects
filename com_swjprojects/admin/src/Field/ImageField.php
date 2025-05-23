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

namespace Joomla\Component\SWJProjects\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use function defined;

class ImageField extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 *
	 * @since  1.3.0
	 */
	protected $type = 'image';

	/**
	 * Name of the layout being used to render the field.
	 *
	 * @var  string
	 *
	 * @since  1.3.0
	 */
	protected $layout = 'components.swjprojects.field.image';

	/**
	 * Component section selector (etc. projects).
	 *
	 * @var  string
	 *
	 * @since  1.3.0
	 */
	protected $section = null;

	/**
	 * The id field selector.
	 *
	 * @var  string
	 *
	 * @since  1.3.0
	 */
	protected $pk = null;

	/**
	 * The name of the image file.
	 *
	 * @var  string
	 *
	 * @since  1.3.0
	 */
	protected $filename = null;

	/**
	 * The language of the image.
	 *
	 * @var  string
	 *
	 * @since  1.3.0
	 */
	protected $language = null;

	/**
	 * Method to attach a Form object to the field.
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since  1.3.0
	 */
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		if ($return = parent::setup($element, $value, $group))
		{
			$this->section  = (!empty($this->element['section'])) ? (string) $this->element['section'] : $this->section;
			$this->pk       = (!empty($this->element['pk'])) ? (string) $this->element['pk'] : $this->pk;
			$this->filename = (!empty($this->element['filename'])) ? (string) $this->element['filename'] : $this->filename;
			$this->language = (!empty($this->element['language'])) ? (string) $this->element['language'] : $this->language;
		}

		return $return;
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since  1.3.0
	 */
	protected function getInput()
	{
		return ($this->section && $this->pk && $this->filename && $this->language) ? parent::getInput() : false;
	}

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array Layout data array.
	 *
	 * @since  1.3.0
	 */
	protected function getLayoutData()
	{
		$data             = parent::getLayoutData();
		$data['section']  = $this->section;
		$data['pk']       = $this->pk;
		$data['filename'] = $this->filename;
		$data['language'] = $this->language;

		return $data;
	}
}
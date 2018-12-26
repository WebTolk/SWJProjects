<?php
/**
 * @package    SW JProjects Component
 * @version    1.0.0
 * @author     Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2018 - 2018 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('list');

class JFormFieldJVersion extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 *
	 * @since  1.0.0
	 */
	public $type = 'jversion';

	/**
	 * Field options array.
	 *
	 * @var  array
	 *
	 * @since  1.0.0
	 */
	protected $_options = null;

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since  1.0.0
	 */
	protected function getOptions()
	{
		if ($this->_options === null)
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select(array('joomla_version'))
				->from($db->quoteName('#__swjprojects_versions'));
			$db->setQuery($query);
			$jversions = $db->loadColumn();

			$options = parent::getOptions();

			foreach (array_unique($jversions) as $version)
			{
				$option        = new stdClass();
				$option->value = $version;
				$option->text  = $version;

				$options[] = $option;
			}

			$this->_options = $options;
		}

		return $this->_options;
	}
}
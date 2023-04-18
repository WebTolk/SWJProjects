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

JLoader::register('SWJProjectsHelperKeys', JPATH_SITE . '/components/com_swjprojects/helpers/keys.php');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Utilities\ArrayHelper;

class SWJProjectsModelDownload extends BaseDatabaseModel
{
	/**
	 * Version object.
	 *
	 * @var  object
	 *
	 * @since  1.0.0
	 */
	protected $_version = null;

	/**
	 * Version id by project id.
	 *
	 * @var  int
	 *
	 * @since  1.0.0
	 */
	protected $_versionID = null;

	/**
	 * Project id by element.
	 *
	 * @var  int
	 *
	 * @since  1.0.0
	 */
	protected $_projectID = null;

	/**
	 * Path to files.
	 *
	 * @var  array
	 *
	 * @since  1.0.0
	 */
	protected $filesPath = null;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @throws  Exception
	 *
	 * @since  1.0.0
	 */
	public function __construct($config = array())
	{
		$params = ComponentHelper::getParams('com_swjprojects');

		// Set files paths
		$root            = $params->get('files_folder');
		$this->filesPath = array(
			'root'     => $root,
			'versions' => $root . '/versions',
		);

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @throws  Exception
	 *
	 * @since  1.0.0
	 */
	protected function populateState()
	{
		$app = Factory::getApplication('site');

		// Set request states
		$this->setState('version.id', $app->input->getInt('version_id', 0));
		$this->setState('project.id', $app->input->getInt('project_id', 0));
		$this->setState('project.element', $app->input->get('element', ''));
		$this->setState('download.key', $app->input->getCmd('download_key', ''));

		// Set published && debug state
		if ($app->input->getInt('debug', 0))
		{
			$this->setState('filter.published', array(0, 1));
			$this->setState('debug', 1);
		}
		else
		{
			$this->setState('filter.published', 1);
		}
	}

	/**
	 * Method to get version data.
	 *
	 * @param   integer  $pk  The id of the version.
	 *
	 * @throws  Exception
	 *
	 * @return  object|Exception  Version object on success, false or exception on failure.
	 *
	 * @since  1.0.0
	 */
	public function getVersion($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('version.id');

		if (empty($pk))
		{
			$pk = $this->getVersionID();
		}

		if ($this->_version === null)
		{
			$this->_version = array();
		}

		if (!isset($this->_version[$pk]))
		{
			try
			{
				$db    = $this->getDbo();
				$query = $db->getQuery(true)
					->select(array('v.*', 'p.id as project_id', 'p.element', 'p.download_type'))
					->from($db->quoteName('#__swjprojects_versions', 'v'))
					->leftJoin($db->quoteName('#__swjprojects_projects', 'p') . ' ON p.id = v.project_id')
					->leftJoin($db->quoteName('#__swjprojects_categories', 'c') . ' ON c.id = p.catid')
					->where('v.id =' . (int) $pk);

				// Filter by published state
				$published = $this->getState('filter.published');
				if (is_numeric($published))
				{
					$query->where('v.state = ' . (int) $published)
						->where('p.state = ' . (int) $published)
						->where('c.state = ' . (int) $published);
				}
				elseif (is_array($published))
				{
					$published = ArrayHelper::toInteger($published);
					$published = implode(',', $published);

					$query->where('v.state IN (' . $published . ')')
						->where('p.state IN (' . $published . ')')
						->where('c.state IN (' . $published . ')');
				}

				$data = $db->setQuery($query)->loadObject();

				if (empty($data))
				{
					throw new Exception(Text::_('COM_SWJPROJECTS_ERROR_VERSION_NOT_FOUND'), 404);
				}

				$data->filename = $data->element . '_' . $data->major . '.' . $data->minor . '.' . $data->micro;
				if ($data->tag !== 'stable')
				{
					$data->filename .= '-' . $data->tag;

					if ($data->tag !== 'dev' && !empty($data->stage))
					{
						$data->filename .= $data->stage;
					}
				}

				$this->_version[$pk] = $data;
			}
			catch (Exception $e)
			{
				throw new Exception(Text::_($e->getMessage()), $e->getCode());
			}
		}

		return $this->_version[$pk];
	}

	/**
	 * Method to get version id from project.
	 *
	 * @param   integer  $pk  The id of the project.
	 *
	 * @throws  Exception
	 *
	 * @return  integer|Exception  Version id on success, exception on failure.
	 *
	 * @since  1.0.0
	 */
	public function getVersionID($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('project.id');

		if (empty($pk))
		{
			$pk = $this->getProjectID();
		}

		if ($this->_versionID === null)
		{
			$this->_versionID = array();
		}

		if (!isset($this->_versionID[$pk]))
		{
			try
			{
				$db   = $this->getDbo();
				$data = false;
				foreach (array('stable', 'rc', 'beta', 'alpha', 'dev') as $tag)
				{
					$query = $db->getQuery(true)
						->select('v.id')
						->from($db->quoteName('#__swjprojects_versions', 'v'))
						->leftJoin($db->quoteName('#__swjprojects_projects', 'p') . ' ON p.id = v.project_id')
						->leftJoin($db->quoteName('#__swjprojects_categories', 'c') . ' ON c.id = p.catid')
						->where('p.id =' . (int) $pk)
						->where($db->quoteName('v.tag') . ' = ' . $db->quote($tag))
						->order($db->escape('v.major') . ' ' . $db->escape('desc'))
						->order($db->escape('v.minor') . ' ' . $db->escape('desc'))
						->order($db->escape('v.micro') . ' ' . $db->escape('desc'));

					// Set stage ordering
					if (in_array($tag, array('rc', 'beta', 'alpha')))
					{
						$query->order($db->escape('v.stage') . ' ' . $db->escape('desc'));
					}

					// Filter by published state
					$published = $this->getState('filter.published');
					if (is_numeric($published))
					{
						$query->where('v.state = ' . (int) $published)
							->where('p.state = ' . (int) $published)
							->where('c.state = ' . (int) $published);
					}
					elseif (is_array($published))
					{
						$published = ArrayHelper::toInteger($published);
						$published = implode(',', $published);

						$query->where('v.state IN (' . $published . ')')
							->where('p.state IN (' . $published . ')')
							->where('c.state IN (' . $published . ')');
					}

					if ($data = $db->setQuery($query)->loadResult())
					{
						break;
					}
				}

				if (!$data)
				{
					throw new Exception(Text::_('COM_SWJPROJECTS_ERROR_VERSION_NOT_FOUND'), 404);
				}

				$this->_versionID[$pk] = $data;
			}
			catch (Exception $e)
			{
				throw new Exception(Text::_($e->getMessage()), $e->getCode());
			}
		}

		return $this->_versionID[$pk];
	}

	/**
	 * Method to get project id from element.
	 *
	 * @param   string  $pk  The id of the project.
	 *
	 * @throws  Exception
	 *
	 * @return  integer|Exception  Project id on success, exception on failure.
	 *
	 * @since  1.0.0
	 */
	public function getProjectID($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : $this->getState('project.element');

		if (!empty($this->getState('project.id')))
		{
			return $this->getState('project.id');
		}

		if ($this->_projectID === null)
		{
			$this->_projectID = array();
		}

		if (empty($pk))
		{
			throw new Exception(Text::_('COM_SWJPROJECTS_ERROR_PROJECT_NOT_FOUND'), 404);
		}

		if (!isset($this->_projectID[$pk]))
		{
			try
			{
				$db    = $this->getDbo();
				$query = $db->getQuery(true)
					->select('p.id')
					->from($db->quoteName('#__swjprojects_projects', 'p'))
					->leftJoin($db->quoteName('#__swjprojects_categories', 'c') . ' ON c.id = p.catid')
					->where($db->quoteName('p.element') . ' = ' . $db->quote($pk));

				// Filter by published state
				$published = $this->getState('filter.published');
				if (is_numeric($published))
				{
					$query->where('p.state = ' . (int) $published)
						->where('c.state = ' . (int) $published);
				}
				elseif (is_array($published))
				{
					$published = ArrayHelper::toInteger($published);
					$published = implode(',', $published);

					$query->where('p.state IN (' . $published . ')')
						->where('c.state IN (' . $published . ')');
				}

				$data = $db->setQuery($query)->loadResult();

				if (empty($data))
				{
					throw new Exception(Text::_('COM_SWJPROJECTS_ERROR_PROJECT_NOT_FOUND'), 404);
				}

				$this->_projectID[$pk] = $data;
			}
			catch (Exception $e)
			{
				throw new Exception(Text::_($e->getMessage()), $e->getCode());
			}
		}

		return $this->_projectID[$pk];
	}

	/**
	 * Method to get file.
	 *
	 * @throws  Exception
	 *
	 * @return  object|Exception  Download file object on success, false or exception on failure.
	 *
	 * @since  1.0.0
	 */
	public function getFile()
	{
		// Get version
		if (!$version = $this->getVersion())
		{
			throw new Exception(Text::_('COM_SWJPROJECTS_ERROR_VERSION_NOT_FOUND'), 404);
		}

		// Check key
		if ($version->download_type !== 'free' && !SWJProjectsHelperKeys::checkKey($version->project_id, $this->getState('download.key')))
		{
			throw new Exception(Text::_('COM_SWJPROJECTS_ERROR_INVALID_KEY'), 403);
		}

		// Get file
		$path  = $this->filesPath['versions'] . '/' . $version->id;
		$files = Folder::files($path, 'download', false, true);
		if (empty($files))
		{
			throw new Exception(Text::_('COM_SWJPROJECTS_ERROR_FILE_NOT_FOUND'), 404);
		}

		// Prepare return object
		$file       = new stdClass();
		$file->name = $version->filename . '.' . File::getExt($files[0]);
		$file->path = $files[0];
		$file->type = mime_content_type($file->path);

		return $file;
	}

	/**
	 * Method to update download counter.
	 *
	 * @throws  Exception
	 *
	 * @since  1.0.0
	 */
	public function setDownload()
	{
		if (!$version = $this->getVersion())
		{
			throw new Exception(Text::_('COM_SWJPROJECTS_ERROR_VERSION_NOT_FOUND'), 404);
		}

		$db = Factory::getDbo();

		// Set statistic
		$query = $db->getQuery(true)
			->update('#__swjprojects_versions')
			->set('downloads = downloads + 1')
			->where('id = ' . (int) $version->id);
		$db->setQuery($query)->execute();

		// Set limit
		$download_key = $this->getState('download.key');
		if (!empty($download_key) && strlen($download_key) !== 128)
		{
			$query = $db->getQuery(true)
				->update('#__swjprojects_keys')
				->set('limit_count = limit_count - 1')
				->where($db->quoteName('key') . ' = ' . $db->quote($download_key))
				->where($db->quoteName('limit') . ' = 1');
			$db->setQuery($query)->execute();
		}
	}
}
<?php
/**
 * @package    SW JProjects
 * @version       2.1.2
 * @Author        Sergey Tolkachyov, https://web-tolk.ru
 * @copyright     Copyright (C) 2024 Sergey Tolkachyov
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @since         1.0.0
 */

namespace Joomla\Plugin\EditorsXtd\Swjprojectseditorxtd\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

/**
 * Editor Article button
 *
 * @since  1.5
 */
final class Swjprojectseditorxtd extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Display the button
	 *
	 * @param   string  $name  The name of the button to add
	 *
	 * @return  CMSObject  The button options as JObject
	 *
	 * @since   1.5
	 */
	public function onDisplay($name)
	{


		if (!empty(PluginHelper::getPlugin('content', 'swjprojects')))
		{
			$app = $this->getApplication();
			if (!$app->isClient('administrator'))
			{
				return;
			}

			$user = $app->getIdentity();

			// Can create in any category (component permission) or at least in one category
			$canCreateRecords = $user->authorise('core.create', 'com_swjprojects')
				|| count($user->getAuthorisedCategories('com_swjprojects', 'core.create')) > 0;

			// Instead of checking edit on all records, we can use **same** check as the form editing view
			$values           = (array) Factory::getApplication()->getUserState('com_swjprojects.edit.project.id');
			$isEditingRecords = count($values);

			// This ACL check is probably a double-check (form view already performed checks)
			$hasAccess = $canCreateRecords || $isEditingRecords;
			if (!$hasAccess)
			{
				return;
			}

			$link = 'index.php?option=com_ajax&amp;plugin=swjprojectseditorxtd&amp;group=editors-xtd&amp;format=html&amp;tmpl=component&amp;' . Session::getFormToken() . '=1&amp;editor=' . $name;

			$button          = new CMSObject;
			$button->modal   = true;
			$button->class   = 'btn';
			$button->link    = $link;
			$button->text    = '{SW JProjects}';
			$button->icon    = 'file-add';
			$button->name    = $this->_type . '_' . $this->_name;
			$button->options = [
				'height'     => '400px',
				'width'      => '800px',
				'modalWidth' => '90',
			];

			return $button;
		}

		return;
	}

	/**
	 * Method working with Joomla com_ajax. Return a HTML form for product selection
	 * @return string product selection HTML form
	 * @throws \Exception
	 */
	public function onAjaxSwjprojectseditorxtd(Event $event)
	{
		$app = $this->getApplication();

		if ($app->isClient('site'))
		{
			Session::checkToken('get') or die(Text::_('JINVALID_TOKEN'));
		}

		$doc = $app->getDocument();
		$doc->getWebAssetManager()
			->useScript('core')
			->registerAndUseScript(
				'swjprojectseditorxtd', 'plg_editors-xtd_swjprojectseditorxtd/swjprojectseditorxtd.js'
			);

		$editor               = $app->getInput()->get('editor', '');
		$swjprojectseditorxtd = Folder::files(JPATH_SITE . "/plugins/content/swjprojects/tmpl");
		$layout_options       = [
                0 => HTMLHelper::_('select.option', '--none--', Text::_('JNONE'))
        ];
		foreach ($swjprojectseditorxtd as $file)
		{
			if (File::getExt($file) == "php")
			{
				$wt_layout        = File::stripExt($file);
				$layout_options[] = HTMLHelper::_('select.option', $wt_layout, $wt_layout);
			}
		}

		if (!empty($editor))
		{

			$doc->addScriptOptions('xtd-swjprojectseditorxtd', array('editor' => $editor));
		}

		$context = 'com_swjprojects.projects';

		$limit = $app->getInput()->get('limit', $app->get('list_limit'), 'int');


		$limitstart = $app->getInput()->get('limitstart', 0, 'int');

		$projects_model = $app->bootComponent('com_swjprojects')
			->getMVCFactory()
			->createModel('Projects', 'Administrator', ['ignore_request' => true]);

		$projects_model->setState('context', $context);
		$projects_model->setState('list.start', $limitstart);
		$projects_model->setState('list.limit', $limit);
		$projects_model->setState('list.direction', 'asc');

		$filter        = $app->getInput()->get('filter', [], 'array');
		$filter_search = (!empty($filter['search'])) ? $filter['search'] : '';
		$projects_model->setState('filter.search', $filter_search);

		$projects = $projects_model->getItems();

		?>
        <form
                action="index.php?option=com_ajax&plugin=swjprojectseditorxtd&group=editors-xtd&format=html&tmpl=component&<?php echo Session::getFormToken(); ?>=1&editor=<?php echo $editor; ?>"
                method="post"
                name="adminForm"
                id="adminForm"
                class="container">
            <input type="hidden" name="option" value="com_ajax"/>
            <input type="hidden" name="plugin" value="swjprojectseditorxtd"/>
            <input type="hidden" name="group" value="editors-xtd"/>
            <input type="hidden" name="format" value="html"/>
            <input type="hidden" name="tmpl" value="component"/>
            <input type="hidden" name="<?php echo Session::getFormToken(); ?>" value="1"/>
            <input type="hidden" name="editor" value="<?php echo $editor; ?>"/>

            <div class="row mb-3 border-bottom">
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="input-group mb-3">
                        <label for="swjprojectseditorxtd_layout" class="input-group-text">
                            <strong>tmpl</strong>
                        </label>
						<?php
						$attribs = [
							'class'      => 'form-select',
							'aria-label' => 'Choose layout'
						];

						echo HTMLHelper::_("select.genericlist", $layout_options, $name = "swjprojectseditorxtd_layout", $attribs, $key = 'value', $text = 'text', $selected = 0);

						?>
                    </div>

                </div>

                <div class="col-3">

                </div>
                <div class="col-2">
		            <?php echo $projects_model->getPagination()->getLimitBox(); ?>
                </div>
                <div class="col-6 col-md-4">
                    <div class="input-group mb-3">
                        <input class="form-control" id="filter_search" type="text" name="filter[search]" placeholder="<?php echo Text::_('JSEARCH_FILTER');?>"
							<?php
							if (!empty($filter_search))
							{
								echo 'value="' . $filter_search . '"';
							}
							?>
                        />
                        <button class="btn btn-primary" type="submit"><i class="icon-search"></i></button>
                        <button class="btn btn-danger filter-search-actions__button js-stools-btn-clear" type="button"
                                onclick="document.getElementById('filter_search').value='';Joomla.submitform();return false;">
                            <i class="icon-remove"></i></button>
                    </div>
                </div>
            </div>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4">
                <?php foreach ($projects as $project) :?>
                    <div class="col mb-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h3 class="h5"><?php echo $project->title; ?></h3>
                        </div>
                        <div class="card-footer bg-transparent d-flex justify-content-between">
                            <a href="#" data-project-id="<?php echo $project->id; ?>" data-project-cat-id="<?php echo $project->catid; ?>" class="stretched-link" data-project-title="<?php echo \htmlspecialchars($project->title); ?>"><?php echo Text::_('JSELECT'); ?></a>
                            <span class="text-muted">#<?php echo $project->id; ?></span>
                        </div>
                    </div>
                    </div>
                <?php endforeach;?>
            </div>
            <div class="border-top mt-3">
		        <?php echo $projects_model->getPagination()->getListFooter(); ?>
            </div>
        </form>
        <div class="fixed-bottom bg-white shadow-sm border-top">
            <div class="container d-flex justify-content-between align-items-end py-2">
                <span class="">
                        <a href="https://web-tolk.ru" target="_blank"
                           style="display: inline-flex; align-items: center;">
                                <svg width="85" height="18" xmlns="http://www.w3.org/2000/svg">
                                     <g>
                                      <title>Go to https://web-tolk.ru</title>
                                      <text font-weight="bold" xml:space="preserve" text-anchor="start"
                                            font-family="Helvetica, Arial, sans-serif" font-size="18" id="svg_3" y="18"
                                            x="8.152073" stroke-opacity="null" stroke-width="0" stroke="#000"
                                            fill="#0fa2e6">Web</text>
                                      <text font-weight="bold" xml:space="preserve" text-anchor="start"
                                            font-family="Helvetica, Arial, sans-serif" font-size="18" id="svg_4" y="18"
                                            x="45" stroke-opacity="null" stroke-width="0" stroke="#000"
                                            fill="#384148">Tolk</text>
                                     </g>
                                </svg>
                        </a>
                    </span>
            </div>
        </div>
        </div>
		<?php
	}
}

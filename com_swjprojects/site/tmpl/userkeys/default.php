<?php
/**
 * @package       SW JProjects
 * @version       2.6.0-alpha
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
 */

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\SWJProjects\Administrator\Helper\KeysHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\SWJProjects\Site\Helper\RouteHelper;

defined('_JEXEC') or die;

Text::script('ERROR');
Text::script('MESSAGE');
Text::script('COM_SWJPROJECTS_USER_KEYS_KEY_SUCCESSFULLY_COPYED');
Text::script('COM_SWJPROJECTS_USER_KEYS_KEY_NOT_COPYED');

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->registerAndUseScript('com_swjprojects.userkeys.copykey','com_swjprojects/copy-userkey.js', ['version' => 'auto', 'relative' => true]);

?>
<div id="SWJProjects" class="userkeys">
    <h1><?php echo Text::_('COM_SWJPROJECTS_USER_KEYS'); ?></h1>
	<?php if (empty($this->items)) : ?>
        <div class="alert alert-info">
            <span class="icon-info-circle" aria-hidden="true"></span><span
                    class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
			<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
        </div>
	<?php else : ?>
        <div class="userkeyslist">
            <div class="d-flex flex-column">
				<?php foreach ($this->items as $item) :
					// All is fine
					$downloadAble = true;
					// By downloads count
					$limit = ($item->limit && $item->limit_count == 0) ? false : true;

					// By datetime

					$date_expired = (
						((new Date())->toUnix()) > (new Date($item->date_end))->toUnix()
					) ? false : true;

					if ($limit == false || $date_expired == false)
					{
						$downloadAble = false;
					}

					?>
                    <div class="card mb-3 <?php echo(!$downloadAble ? 'border-danger' : ''); ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <div class="fs-5 fw-bolder bg-light p-2">

                                    <?php if (!$downloadAble) : ?>
                                    <i class="fa-solid fa-lock"></i> <s>
                                        <?php else : ?>
                                            <i class="fa-solid fa-key"></i>
                                        <?php endif; ?>
                                    <span>
                                        <?php echo KeysHelper::maskKey($item->key); ?>
                                    </span>
                                    <?php if (!$downloadAble): ?>
                                    </s>
                                    <?php else : ?>
                                        <button type="button" class="btn" data-key="<?php echo $item->key; ?>">
                                        <i class="fa-solid fa-copy"></i>
                                            </button>
                                    <?php endif; ?>
                                </div>

								<?php if ($item->limit): ?>
                                    <div class="text-muted">
										<?php echo Text::_('COM_SWJPROJECTS_USER_KEYS_KEY_LIMIT_COUNT'); ?> <span
                                                class="badge <?php echo(!$limit ? 'bg-danger' : 'bg-success'); ?>"><?php echo $item->limit_count; ?></span>
                                    </div>
								<?php endif; ?>
                            </div>
                            <div class="row mb-3">
                                <?php if(!empty($item->domain)):?>
                                <div class="col-12 col-md-6 col-lg-3">
                                    <span class="fw-bold"><?php echo Text::_('COM_SWJPROJECTS_USER_KEYS_DOMAIN'); ?></span>:
                                    <span class="badge bg-primary"><?php echo $item->domain; ?></span></div>
                                <?php endif;?>
	                            <?php if(!empty($item->date_start)):?>
                                <div class="col-12 col-md-6 col-lg-3"><span
                                            class="fw-bold"><?php echo Text::_('COM_SWJPROJECTS_USER_KEYS_DATE_START'); ?></span>:
                                    <span class="badge bg-primary"><?php echo HTMLHelper::date($item->date_start, 'DATE_FORMAT_LC4'); ?></span>
                                </div>
	                            <?php endif;?>
	                            <?php if(!empty($item->date_end)):?>
                                <div class="col-12 col-md-6 col-lg-3"><span
                                            class="fw-bold"><?php echo Text::_('COM_SWJPROJECTS_USER_KEYS_DATE_END'); ?></span>:
                                    <span class="badge <?php echo(!$date_expired ? 'bg-danger' : 'bg-success'); ?>"><?php echo HTMLHelper::date($item->date_end, 'DATE_FORMAT_LC4'); ?></span>
                                </div>
	                            <?php endif;?>
                            </div>

							<?php if ($item->projects): ?>
                                <div class="d-flex flex-column">

									<?php foreach ($item->projects as $project) : ?>
                                        <div class="border-bottom p-2 d-flex">
                                            <div class="flex-grow-1">
												<?php
												$link_attribs = [
													'target' => '_blank'
												];
												$project_link = RouteHelper::getProjectRoute($project->id, $project->catid);
												echo HTMLHelper::link(Route::_($project_link), $project->title, $link_attribs);
												?>
                                            </div>
                                            <div class="p-2">
												<?php


												if ($downloadAble)
												{
													$link_attribs  = [
														'class' => 'btn btn-sm btn-primary'
													];
													$download_link = RouteHelper::getDownloadRoute(null, null, $project->element, $item->key);
													echo HTMLHelper::link(Route::_($download_link), Text::_('COM_SWJPROJECTS_DOWNLOAD'), $link_attribs);
												}

												?>
                                            </div>
                                        </div>
									<?php endforeach; ?>
                                </div>
							<?php endif; ?>
                        </div>
                    </div>
				<?php endforeach ?>
            </div>
            <div class="pagination">
				<?php echo $this->pagination->getPagesLinks(); ?>
            </div>
        </div>
	<?php endif; ?>
</div>

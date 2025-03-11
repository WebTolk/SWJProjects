<?php
/**
 * @package    SW JProjects
 * @version    2.4.0
 * @author     Sergey Tolkachyov
 * @copyright  Copyright (c) 2018 - 2019 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

HTMLHelper::_('bootstrap.carousel');
$wa = Factory::getApplication()
	->getDocument()
	->getWebAssetManager()
	->useScript('bootstrap.tab');

HTMLHelper::stylesheet('com_swjprojects/site.css', array('version' => 'auto', 'relative' => true));

?>
    <section id="SWJProjects" class="sw-jprojects-project">
		<?php if ($cover = $this->project->images->get('cover')): ?>
            <div class="d-none d-lg-block">
                <div class="cover">
					<?php
					$size        = \getimagesize($cover);
					$img_attribs = [
						'class'  => 'w-100 h-auto',
						'width'  => $size[0],
						'height' => $size[1],
					];
					echo HTMLHelper::image($cover, $this->project->title, $img_attribs); ?>
                </div>
                <hr>
            </div>
		<?php endif; ?>
		<?php
		// Get content from plugins
		echo $this->project->event->beforeDisplayContent;

		$icon = $this->project->images->get('icon');
		?>
        <section class="project info card mb-3">
            <div class="card-body">
                <div class="row g-0 mb-2">

                    <div class="<?php echo($icon ? 'col-9 col-xl-9' : 'col-12'); ?> order-md-1">
                        <header>
                            <h1 class="mb-3 mb-md-5 project-title"><?php echo $this->project->title; ?></h1>
							<?php
							// Get content from plugins
							echo $this->project->event->afterDisplayTitle;
							?>
                            <ul class="list-unstyled">
								<?php if (!empty($this->project->categories)): ?>
                                    <li>
                                        <strong><?php echo Text::_('COM_SWJPROJECTS_CATEGORIES'); ?>: </strong>
										<?php
										$links = [];
										foreach ($this->project->categories as $category)
										{
											$links[] = HTMLHelper::link($category->link, $category->title);
										}
										echo implode(', ', $links);
										?>
                                    </li>
								<?php else: ?>
                                    <li>
                                        <strong><?php echo Text::_('COM_SWJPROJECTS_CATEGORY'); ?>: </strong>
                                        <a href="<?php echo $this->category->link; ?>">
											<?php echo $this->category->title; ?>
                                        </a>
                                    </li>
								<?php endif; ?>
								<?php if ($this->version): ?>
                                    <li>
                                        <strong><?php echo Text::_('COM_SWJPROJECTS_VERSION'); ?>: </strong>
                                        <span class="<?php echo ($this->version->tag_key !== 'stable') ? 'badge badge-danger' : ''; ?>">
													<?php echo $this->version->version; ?></span>
                                    </li>
                                    <li>
                                        <strong><?php echo Text::_('JDATE'); ?>:</strong>
                                        <time><?php echo HTMLHelper::_('date', $this->version->date, Text::_('DATE_FORMAT_LC3')); ?></time>
                                    </li>
								<?php endif; ?>
                            </ul>
                        </header>
                        <div class="d-flex flex-wrap mb-3 project-icons">
							<?php echo LayoutHelper::render('components.swjprojects.project.icons', ['item' => $this->project]); ?>
                        </div>
                        <div class="d-flex flex-wrap mb-3 project-buttons">
							<?php
							// Get content from plugins
							echo $this->project->event->beforeProjectButtons;

                            // see button names in layouts/components/swjprojects/project/urls.php
							echo LayoutHelper::render('components.swjprojects.project.urls',
                                [
                                        'item' => $this->project,
                                        'include_buttons' => [], // Show only specified buttons. Higher priority.
                                        'exclude_buttons'=> ['projectlink'], // Show ALL EXCEPT specified buttons
                                ]);

							// Get content from plugins
							echo $this->project->event->afterProjectButtons;
							?>
                        </div>
						<?php if (!empty($this->project->introtext)): ?>
                            <p class="description">
								<?php echo $this->project->introtext; ?>
                            </p>
						<?php endif; ?>

                    </div>
					<?php if ($icon): ?>
                        <div class="col-3 col-xl-3 order-md-2">
							<?php
							$size        = \getimagesize($icon);
							$img_attribs = [
								'class'  => 'w-100 h-auto',
								'width'  => $size[0],
								'height' => $size[1],
							];
							echo HTMLHelper::image($icon, $this->project->title, $img_attribs); ?>
                        </div>
					<?php endif; ?>
                </div>

            </div>
        </section>


        <ul class="nav nav-tabs" id="projectTabs" role="tablist">
			<?php if (!empty($this->project->fulltext)): ?>
                <li class="nav-item">
                    <a class="nav-link active" id="home-tab" data-bs-toggle="tab" href="#projectTab" role="tab"
                       aria-controls="projectTab"
                       aria-selected="true"><?php echo Text::_('JGLOBAL_DESCRIPTION'); ?></a>
                </li>
			<?php endif; ?>
			<?php if ($this->project->joomla): ?>
                <li class="nav-item">
                    <a class="nav-link" id="profile-tab" data-bs-toggle="tab" href="#project_joomla" role="tab"
                       aria-controls="project_joomla"
                       aria-selected="false"><?php echo Text::_('COM_SWJPROJECTS_JOOMLA'); ?></a>
                </li>
			<?php endif; ?>
			<?php if ($this->project->gallery): ?>
                <li class="nav-item">
                    <a class="nav-link" id="contact-tab" data-bs-toggle="tab" href="#project_gallery" role="tab"
                       aria-controls="project_gallery"
                       aria-selected="false"><?php echo Text::_('COM_SWJPROJECTS_IMAGES_GALLERY'); ?></a>
                </li>
			<?php endif; ?>
			<?php if ($this->version && !empty($this->version->changelog)): ?>
                <li class="nav-item">
                    <a class="nav-link" id="contact-tab" data-bs-toggle="tab" href="#project_whats_new" role="tab"
                       aria-controls="project_whats_new"
                       aria-selected="false"><?php echo Text::_('COM_SWJPROJECTS_WHATS_NEW'); ?></a>
                </li>
			<?php endif; ?>
        </ul>
        <div class="tab-content" id="projectTabContent">

			<?php if (!empty($this->project->fulltext)): ?>
                <section class="tab-pane fade show active pt-3" id="projectTab" role="tabpanel"
                         aria-labelledby="project-tab">
                    <h2><?php echo Text::_('JGLOBAL_DESCRIPTION'); ?></h2>
					<?php echo $this->project->fulltext; ?>
                </section>
			<?php endif; ?>

			<?php if ($this->project->joomla):
				$type = $this->project->joomla->get('type'); ?>
                <section class="tab-pane fade pt-3" id="project_joomla" role="tabpanel" aria-labelledby="profile-tab">
                    <h2><?php echo Text::_('COM_SWJPROJECTS_JOOMLA'); ?></h2>
                    <dl>
                        <dt>
                            <strong><?php echo Text::_('COM_SWJPROJECTS_JOOMLA_TYPE'); ?>: </strong>
                        </dt>
                        <dd>
							<?php echo Text::_('COM_SWJPROJECTS_JOOMLA_TYPE_' . $type); ?>
                        </dd>

						<?php if ($type === 'plugin'): ?>
                            <dt>
                                <strong><?php echo Text::_('COM_SWJPROJECTS_JOOMLA_FOLDER'); ?>: </strong>
                            </dt>
                            <dd><?php echo utf8_ucfirst($this->project->joomla->get('folder')); ?>
                            </dd>

						<?php endif; ?>
						<?php if ($type === 'template' || $type === 'module'): ?>
                            <dt>
                                <strong><?php echo Text::_('COM_SWJPROJECTS_JOOMLA_LOCATION'); ?>: </strong>
                            </dt>
                            <dd>
								<?php echo ($this->project->joomla->get('client_id')) ?
									Text::_('COM_SWJPROJECTS_JOOMLA_LOCATION_ADMINISTRATOR')
									: Text::_('COM_SWJPROJECTS_JOOMLA_LOCATION_SITE') ?>
                            </dd>
						<?php endif; ?>
						<?php if ($type === 'package' && !empty($this->project->joomla->get('package_composition'))): ?>
                            <dt>
                                <strong><?php echo Text::_('COM_SWJPROJECTS_JOOMLA_PACKAGE_COMPOSITION'); ?>: </strong>
                            </dt>
                            <dd>
								<?php
								$compositions = array();
								foreach ($this->project->joomla->get('package_composition') as $composition)
								{
									$compositions[] = Text::_('COM_SWJPROJECTS_JOOMLA_TYPE_' . $composition);
								}
								echo implode(', ', $compositions); ?>
                            </dd>
						<?php endif; ?>
						<?php if ($this->project->joomla->get('version')): ?>
                            <dt>
                                <strong><?php echo Text::_('COM_SWJPROJECTS_JOOMLA_VERSION'); ?>: </strong>
                            </dt>

                            <dd><?php echo implode(', ', $this->project->joomla->get('version')); ?>
                            </dd>

						<?php endif; ?>
                    </dl>


                </section>
			<?php endif; ?>

			<?php if ($this->project->gallery): ?>
                <section class="tab-pane fade pt-3" id="project_gallery" role="tabpanel"
                         aria-labelledby="project_gallery-tab">
                    <h2><?php echo Text::_('COM_SWJPROJECTS_IMAGES_GALLERY'); ?></h2>
                    <div id="project-images" class="carousel carousel-dark slide carousel-fade" data-bs-ride="carousel">


                        <div class="carousel-indicators">
							<?php $i = 0;
							foreach ($this->project->gallery as $image):
								?>
                                <button type="button" data-bs-target="#project-images"
                                        data-bs-slide-to="<?php echo $i; ?>"
									<?php if ($i + 1 == 1): ?>
                                        class="active" aria-current="true"
									<?php endif; ?>

                                        aria-label="<?php echo $this->project->title; ?> image slide <?php echo $i; ?>">

                                </button>


								<?php $i++; endforeach; ?>
                        </div>

                        <div class="carousel-inner">

							<?php
							$i = 0;

							foreach ($this->project->gallery as $image):?>
                                <div class="carousel-item <?php if ($i + 1 == 1)
								{
									echo "active";
								}; ?>">
                                    <img src="<?php echo $image->src; ?>" class="d-block w-100" alt="..."/>

									<?php if ($image->text): ?>
                                        <div class="carousel-caption d-none d-md-block"><?php echo $image->text; ?></div>
									<?php endif; ?>
                                </div>
								<?php $i++;

							endforeach; ?>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#project-images"
                                data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#project-images"
                                data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>


                </section>
			<?php endif; ?>

			<?php if ($this->version && !empty($this->version->changelog)): ?>
                <section class="tab-pane fade pt-3" id="project_whats_new" role="tabpanel"
                         aria-labelledby="project_whats_new-tab">
                    <h2><?php echo Text::_('COM_SWJPROJECTS_WHATS_NEW'); ?></h2>
                    <time class="fs-5"><?php echo HTMLHelper::_('date', $this->version->date, Text::_('DATE_FORMAT_LC6')); ?></time>


					<?php
					/**
					 * @var bool $isSingleChangelog Flag if we have a single item in our changelog list
					 */
                    $isSingleChangelog = (\count($this->version->changelog) == 1) ? true : false;

                    foreach ($this->version->changelog as $item):
						if (empty($item['title']) && empty($item['description'])) continue;
						?>
                        <section class="row <?php echo (!$isSingleChangelog ? 'border-bottom':'');?> py-3">
                            <div class="col-12 col-md-2 col-xl-1">
								<?php if (!empty($item['type']))
								{
									/**
									 * params
									 * - type - changelog item type - security, fix etc.
									 * - class - badge css class, For ex. 'badge bg-danger'. If empty - default Bootstrap 5 classes used
									 * - css_classes_array - associative array of css classes for badge For ex. 'fix' => 'badge bg-warning'. If empty - default Bootstrap 5 classes used
									 */


									echo LayoutHelper::render('components.swjprojects.changelog.badge', [
										'type'              => $item['type'],
										'class'             => '',
										'css_classes_array' => [],
									]);
								}
								?>
                            </div>
                            <div class="col-12 col-md-10 col-xl-11">
								<?php if (!empty($item['title'])): ?>
                                    <h3><?php echo $item['title']; ?></h3>
								<?php endif; ?>
								<?php if (!empty($item['description'])): ?>
                                    <div class="description"><?php echo $item['description']; ?></div>
								<?php endif; ?>
                            </div>
                        </section>
					<?php endforeach; ?>

                </section>
			<?php endif; ?>

			<?php if (!empty($this->relations)): ?>
            <hr class="hr my-5"/>
                <section>
                    <h2 class="mb-3"><?php echo Text::_('COM_SWJPROJECTS_RELATED_EXTENSIONS'); ?></h2>
                    <div class="row row-cols-2 row-cols-lg-4 row-cols-xl-5 relations">

						<?php foreach ($this->relations as $relation): ?>
                            <div class="col">
                                <div class="card mb-3 h-100">
                                    <a href="<?php echo $relation['link']; ?>" target="_blank"
                                       class="stretched-link d-flex flex-column justify-content-center align-items-center">

										<?php
										if (!empty($relation['icon']))
										{
											$image = HTMLHelper::cleanImageURL($relation['icon']);
											if (!isset($image->attributes['width']) || empty($image->attributes['width']))
											{
												$size                        = getimagesize(JPATH_SITE . '/' . $relation['icon']);
												$image->attributes['width']  = $size[0];
												$image->attributes['height'] = $size[1];
											}

											$image->attributes['class'] = 'w-100 h-auto';

											echo HTMLHelper::image($relation['icon'], htmlspecialchars($relation['title']), $image->attributes);
										}
										?>
                                        <span class="card-body"><?php echo $relation['title']; ?></span>
                                    </a>
                                </div>
                            </div>
						<?php endforeach; ?>
                    </div>
                </section>
			<?php endif; ?>

        </div>
    </section>
<?php
// Get content from plugins
echo $this->project->event->afterDisplayContent;
?>
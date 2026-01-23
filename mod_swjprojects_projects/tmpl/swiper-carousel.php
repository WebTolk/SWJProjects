<?php
/**
 * @package       SW JProjects
 * @version       2.6.1
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

defined('_JEXEC') or die;

/**
 * You can use these variables here
 *
 * @var stdClass                               $module   The current module object
 * @var \Joomla\CMS\Application\CMSApplication $app      The application like instead Factory::getApplication()
 * @var \Joomla\Input\Input                    $input    The Joomla Input object
 * @var \Joomla\Registry\Registry              $params   The current module params
 * @var stdClass|string                        $template The current template params
 *
 * @var array                                  $items The projects list
 */

$css='
.badge-comp {background-color:#28A745;}
.badge-file {background-color:#c4c4c4;}
.badge-lang {background-color:#FD7E14;}
.badge-lib {background-color:#6610F2;}
.badge-plg {background-color:#FF3366;}
.badge-mod {background-color:#DC3545;}
.badge-pack {background-color:#FFC107;}
.badge-tpl {background-color:#0D6EFD;}
';
/**
 * @var $wa \Joomla\CMS\WebAsset\WebAssetManager
 */
$wa = $app->getDocument()->getWebAssetManager();
$wa->addInlineStyle($css);

/**
 * You can download swiper js for Joomla
 * @link       https://web-tolk.ru
 *       or include swiper.js from CDN or other way you want
 */

$wa->useScript('swiper-bundle')->useStyle('swiper-bundle');

$unique = 'mod_swjprojects_projects_'.$module->id;

?>
 <div class="<?php echo $unique; ?> swiper">
    <div class="swiper-wrapper mb-4">

	<?php foreach ($items as $i => $item) :?>
					<article class="swiper-slide h-auto">
						<div class="card rounded-0 shadow-hover h-100">
							<div class="card-body">
								<div class="row g-0 mb-2">
									<?php 
									
									$icon = $item->images->get('icon');

									?>
									<header class="<?php echo ($icon ? 'col-9 col-md-8' : 'col-12');?>">
											<a href="<?php echo $item->link; ?>" class="text-decoration-none text-dark">
												<h2 class="h6 fw-bold">
													<?php echo $item->title; ?>
												</h2>
											</a>

									</header>
									<?php if ($icon): ?>
										<div class="col-3 col-md-4">
											<?php
											$size = \getimagesize($icon);
											$img_attribs = [
												'class' => 'w-100 h-auto',
												'width' => $size[0],
												'height' => $size[1],
											];
											if($i > 0){
												$img_attribs['loading'] = 'lazy';
											}
											echo HTMLHelper::image($icon, $item->title, $img_attribs); ?>
										</div>
									<?php endif; ?>
								</div>
							</div>
							<div class="card-footer bg-white border-0 py-1">
							<?php if ($item->downloads): ?>
										<span class="badge bg-light text-dark pe-0 pe-md-2">
											<i class="fas fa-download" title="<?php echo Text::_('COM_SWJPROJECTS_STATISTICS_DOWNLOADS'); ?>"></i>
											<span class="visually-hidden"><?php echo Text::_('COM_SWJPROJECTS_STATISTICS_DOWNLOADS'); ?></span>
											<?php echo $item->downloads; ?>
										</span>
									<?php endif; ?>
									<?php if ($item->hits): ?>
										<span class="badge bg-light text-dark pe-0 pe-md-2">
											<i class="far fa-eye" title="<?php echo Text::_('COM_SWJPROJECTS_STATISTICS_HITS'); ?>"></i>
											<span class="visually-hidden"><?php echo Text::_('COM_SWJPROJECTS_STATISTICS_HITS'); ?></span>
											<?php echo $item->hits; ?>
										</span>
									<?php endif; ?>

									<?php
									if (!empty($item->joomla->get('type'))): ?>
										<?php if ($item->joomla->get('type') == "component"): ?>
											<span class="badge bg-light  text-dark ms-auto"
												  title="Component">Comp</span>
										<?php endif; ?>
										<?php if ($item->joomla->get('type') == "file"): ?>
											<span class="badge bg-light  text-dark ms-auto" title="Joomla File">File</span>
										<?php endif; ?>
										<?php if ($item->joomla->get('type') == "language"): ?>
											<span class="badge bg-light  text-dark ms-auto" title="Joomla language">Lang</span>
										<?php endif; ?>
										<?php if ($item->joomla->get('type') == "plugin"): ?>
											<span class="badge bg-light text-dark ms-auto" title="Joomla Plugin">Plg</span>
										<?php endif; ?>

										<?php if ($item->joomla->get('type') == "module"): ?>
											<span class="badge bg-light text-dark ms-auto" title="Joomla Module">Mod</span>
										<?php endif; ?>
										<?php if ($item->joomla->get('type') == "package"): ?>
											<span class="badge bg-light text-dark ms-auto" title="Joomla Package">Pack</span>
										<?php endif; ?>
										<?php if ($item->joomla->get('type') == "template"): ?>
											<span class="badge bg-light text-dark  ms-auto" title="Joomla Template">Tpl</span>
										<?php endif; ?>
										<?php if ($item->joomla->get('type') == "library"): ?>
											<span class="badge bg-light text-dark  ms-auto" title="Joomla Library">Lib</span>
										<?php endif; ?>

										<?php if (!empty($item->download_type)): ?>
											<span class="badge bg-dark text-white"
												  title="<?php echo Text::_('COM_SWJPROJECTS_DOWNLOAD_TYPE_' . \strtoupper($item->download_type)); ?>"> <?php echo Text::_('COM_SWJPROJECTS_DOWNLOAD_TYPE_' . \strtoupper($item->download_type)); ?></span>
										<?php endif; ?>
									<?php endif; ?>
							</div>
							<footer class="card-footer btn-group bg-white border-0 d-flex">
									<?php if (($item->download_type === 'paid' && $item->payment->get('link'))): ?>
										<a href="<?php echo $item->payment->get('link'); ?>"
										   class="btn btn-success" data-btn-download>
											<?php echo Text::_('COM_SWJPROJECTS_BUY'); ?>
										</a>
									<?php elseif ($item->download_type === 'free'): ?>
										<a href="<?php echo $item->download; ?>" class="btn btn-dark"
										   target="_blank" data-btn-download>
											<?php echo Text::_('COM_SWJPROJECTS_DOWNLOAD'); ?>
										</a>
									<?php endif; ?>
									<a href="<?php echo $item->link; ?>" class="btn btn-info text-white">
										<?php echo Text::_('COM_SWJPROJECTS_MORE'); ?>
									</a>
							</footer>
						</div>
					</article>
	<?php endforeach; ?>
	</div>
		 <!-- If we need pagination -->
    <div class="swiper-pagination swiper-pagination_<?php echo $unique; ?>"></div>

    <!-- If we need navigation buttons -->
    <div class="swiper-button-prev swiper-button-prev_<?php echo $unique; ?>"></div>
    <div class="swiper-button-next swiper-button-next_<?php echo $unique; ?>"></div>
</div>
<script>
    let swiper_options<?php echo $unique;?> = {
        "speed": 400,
        "spaceBetween": 100,
        "allowTouchMove": 1,
        "autoHeight": 0,
        "direction": "horizontal",
        "loop": true,
        "allowSlideNext": 1,
        "allowSlidePrev": 1,
        "navigation": {
            "nextEl": ".swiper-button-next_<?php echo $unique;?>",
            "prevEl": ".swiper-button-prev_<?php echo $unique;?>"
        },
        "pagination": {
            "el": ".swiper-pagination_<?php echo $unique;?>",
            "dynamicBullets": 1,
            "dynamicMainBullets": 5,
            "type": "bullets"
        },
        "breakpoints": {
            "320": {
                "slidesPerView": 1,
                "spaceBetween": 2
            },
            "768": {
                "slidesPerView": 2,
                "spaceBetween": 2
            },
            "982": {
                "slidesPerView": 4,
                "spaceBetween": 2
            },
            "1200": {
                "slidesPerView": 5,
                "spaceBetween": 2
            }
        },
        "scrollbar": false,
        "autoplay": false
    };

    if (document.readyState != 'loading') {
        loadWTJSwiper<?php echo $unique;?>();
    } else {
        document.addEventListener('DOMContentLoaded', loadWTJSwiper<?php echo $unique;?>);
    }

    function loadWTJSwiper<?php echo $unique;?>() {
        const swiper<?php echo $unique;?> = new Swiper('.<?php echo $unique;?>', swiper_options<?php echo $unique;?>);
    }

</script>
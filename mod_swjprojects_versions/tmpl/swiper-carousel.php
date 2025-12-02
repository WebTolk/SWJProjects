<?php
/**
 * @package       SW JProjects
 * @version       2.6.1-dev
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * You can use these variables here
 *
 * @var stdClass                               $module   The current module object
 * @var \Joomla\CMS\Application\CMSApplication $app      The application like instead Factory::getApplication()
 * @var \Joomla\Input\Input                    $input    The Joomla Input object
 * @var \Joomla\Registry\Registry              $params   The current module params
 * @var stdClass|string                        $template The current template params
 *
 * @var array                                  $items    The versions list
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
 * @var \Joomla\CMS\WebAsset\WebAssetManager $wa
 */
$wa = $app->getDocument()->getWebAssetManager();
$wa->addInlineStyle($css);
/**
 * You can download swiper js for Joomla
 * @link       https://web-tolk.ru
 *       or include swiper.js from CDN or other way you want
 */
$wa->useScript('swiper-bundle')->useStyle('swiper-bundle');

$unique = 'mod_swjprojects_header_'.$module->id;

?>
 <div class="<?php echo $unique; ?> swiper">
    <div class="swiper-wrapper mb-4">

	<?php foreach ($items as $i => $item) :?>
		<article class="swiper-slide h-auto">
			<div class="card rounded-0 shadow-hover h-100 item-<?php echo $item->id; ?>">
				<header class="card-header bg-white border-0">
					<small clas="text-muted"><strong><i class="fas fa-calendar-alt" title="<?php echo Text::_('JDATE'); ?>"></i> </strong>
						<?php echo HTMLHelper::_('date', $item->date, Text::_('DATE_FORMAT_LC3')); ?>
					</small>
				</header>
				<div class="card-body">
					<h2 class="h6">
						<a class="text-dark" href="<?php echo $item->project->link; ?>"><?php echo $item->title; ?></a>
					</h2> 
				</div>
				<div class="card-footer  bg-white border-0">
					<ul class="d-flex list-unstyled mb-1">
						<li class="me-2">
							<span class="fw-bold"><i class="fas fa-tag" title="<?php echo Text::_('COM_SWJPROJECTS_VERSION_TAG'); ?>"></i></span>
							<span class="text-<?php echo ($item->tag->key == 'stable') ? 'success' : 'error'; ?>">
								<?php echo $item->tag->title; ?>
							</span>
						</li>
						<?php if (!empty($item->joomla_version)): ?>
							<li class="me-2">
								<span class="fw-bold"><i class="fab fa-joomla" title="<?php echo Text::_('COM_SWJPROJECTS_JOOMLA_VERSION'); ?>"></i> </span>
								<?php echo $item->joomla_version; ?>
							</li>
						<?php endif; ?>
						<?php if ($item->downloads): ?>
							<li class="me-2">
								<span class="fw-bold"><i class="fas fa-download" title="<?php echo Text::_('COM_SWJPROJECTS_STATISTICS_DOWNLOADS'); ?>"></i></span>
								<?php echo $item->downloads; ?>
							</li>
						<?php endif; ?>
					</ul>
				</div>
				<footer class="card-footer bg-white border-0">
					<?php if ($item->download_type === 'paid'): ?>
						<a href="<?php echo $item->project->link; ?>"
						   class="btn btn-success" data-btn-download>
							<?php echo Text::_('COM_SWJPROJECTS_BUY'); ?>
						</a>
					<?php elseif ($item->download_type === 'free'): ?>
						<a href="<?php echo $item->download; ?>" target="_blank"
						   class="btn btn-dark" data-btn-download>
							<?php echo Text::_('COM_SWJPROJECTS_DOWNLOAD'); ?>
						</a>
					<?php endif; ?>
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
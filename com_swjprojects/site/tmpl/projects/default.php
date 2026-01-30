<?php
/**
 * @package       SW JProjects
 * @version       2.6.1
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link          https://web-tolk.ru
 * @since         1.0.0
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

$title = ($this->category->id > 1) ? $this->category->title
        : Factory::getApplication()->getMenu()->getActive()->title;
?>
<section id="SWJProjects" class="projects">
    <?php
    // Get content from plugins
    echo $this->category->event->beforeDisplayProjectsContent;
    ?>
    <div class="category info my-5">
        <h1><?php
            echo $title; ?></h1>
        <?php
        // Get content from plugins
        echo $this->category->event->afterDisplayProjectsTitle;
        ?>
        <?php
        if (!empty($this->category->description)): ?>
            <div class="description">
                <?php
                echo $this->category->description; ?>
            </div>
        <?php
        endif; ?>
    </div>
    <?php
    if (empty($this->items)) : ?>
        <div class="alert alert-info">
            <span class="icon-info-circle" aria-hidden="true"></span><span
                    class="visually-hidden"><?php
                echo Text::_('INFO'); ?></span>
            <?php
            echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
        </div>
    <?php
    else : ?>
        <div class="projectsList">
            <div class="items row g-0 row-cols-1 row-cols-md-3 row-cols-lg-4 row-cols-xxl-5">
                <?php
                // Итератор для определения 1-го итема и включения loading=lazy для картинки
                $i = 0;

                foreach ($this->items as $item): ?>
                    <article class="project col mb-3 mb-md-0 shadow-hover">
                        <?php
                        // Get content from plugins
                        echo $item->event->beforeDisplayContent;
                        ?>
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="row g-0 mb-2">
                                    <?php
                                    $icon = $item->images->get('icon'); ?>
                                    <header class="<?php echo($icon ? 'col-9 col-md-8' : 'col-12'); ?>">
                                        <a href="<?php echo $item->link; ?>" class="text-decoration-none text-dark">
                                            <h2 class="h6 fw-bold">
                                                <?php echo $item->title; ?>
                                            </h2>
                                            <?php
                                            // Get content from plugins
                                            echo $item->event->afterDisplayTitle;
                                            ?>
                                        </a>
                                        <ul class="list-unstyled">
                                            <?php
                                            if (!empty($item->categories)): ?>
                                                <li>
                                                    <strong><?php
                                                        echo Text::_('COM_SWJPROJECTS_CATEGORIES'); ?>: </strong>
                                                    <?php
                                                    $cat_links  = [];
                                                    foreach ($item->categories as $category) {
                                                        $cat_links[] = HTMLHelper::link($category->link,$category->title);
                                                    }
                                                    echo implode(', ',$cat_links);
                                                    ?>
                                                </li>
                                            <?php else: ?>
                                                <li>
                                                    <strong><?php echo Text::_('COM_SWJPROJECTS_CATEGORY'); ?>: </strong>
                                                    <?php echo HTMLHelper::link($item->category->link,$item->category->title);?>
                                                </li>
                                            <?php
                                            endif; ?>
                                            <?php
                                            if ($item->version): ?>
                                                <li>
                                                    <strong><?php
                                                        echo Text::_('COM_SWJPROJECTS_VERSION'); ?>: </strong>
                                                    <a href="<?php
                                                    echo $item->version->link; ?>">
                                                        <?php
                                                        echo $item->version->version; ?>
                                                    </a>
                                                </li>
                                            <?php
                                            endif; ?>
                                        </ul>
                                    </header>
                                    <?php
                                    if ($icon): ?>
                                        <div class="col-3 col-md-4">
                                            <?php
                                            $size        = getimagesize($icon);
                                            $img_attribs = [
                                                    'class'  => 'w-100 h-auto',
                                                    'width'  => $size[0],
                                                    'height' => $size[1],
                                                    'fetchpriority' => 'high',
                                            ];
                                            if ($i > 1) {
                                                $img_attribs['loading'] = 'lazy';
                                                $img_attribs['fetchpriority'] = 'low';
                                            }
                                            echo HTMLHelper::image($icon, $item->title, $img_attribs); ?>
                                        </div>
                                    <?php
                                    endif; ?>
                                </div>
                                <?php
                                if (!empty($item->introtext)) : ?>
                                    <p class="d-none d-lg-block"><?php
                                        echo $short_text = mb_strimwidth($item->introtext, 0, 250, "..."); ?> </p>
                                <?php
                                endif; ?>
                                <?php
                                if ($item->download_type === 'paid' && $item->payment->get('price')): ?>
                                    <p class="fs-4 mb-0">
                                        <strong><?php
                                            echo Text::_('COM_SWJPROJECTS_PRICE'); ?>:</strong> <span
                                                class="fw-bold text-success"><?php
                                            echo $item->payment->get('price'); ?></span>
                                    </p>
                                <?php
                                endif; ?>
                            </div>
                            <div class="card-footer bg-white border-0 py-1">
                                <?php
                                echo LayoutHelper::render('components.swjprojects.project.icons', ['item' => $item]); ?>
                            </div>
                            <footer class="card-footer btn-group bg-white border-0 d-flex">
                                <?php
                                // Get content from plugins
                                echo $item->event->beforeProjectButtons;
                                ?>
                                <?php
                                if (($item->download_type === 'paid' && $item->payment->get('link'))): ?>
                                    <a href="<?php
                                    echo $item->payment->get('link'); ?>"
                                       class="btn btn-success" data-btn-download>
                                        <?php
                                        echo Text::_('COM_SWJPROJECTS_BUY'); ?>
                                    </a>
                                <?php
                                elseif ($item->download_type === 'free'): ?>
                                    <a href="<?php
                                    echo $item->download; ?>" class="btn btn-dark"
                                       target="_blank" data-btn-download>
                                        <?php
                                        echo Text::_('COM_SWJPROJECTS_DOWNLOAD'); ?>
                                    </a>
                                <?php
                                endif; ?>
                                <a href="<?php
                                echo $item->link; ?>" class="btn btn-primary text-white">
                                    <?php
                                    echo Text::_('COM_SWJPROJECTS_MORE'); ?>
                                </a>

                                <?php
                                // Get content from plugins
                                echo $item->event->afterProjectButtons;
                                ?>
                        </div>
                        </footer>
                        <?php
                        // Get content from plugins
                        echo $item->event->afterDisplayContent;
                        ?>
                    </article>
                <?php
                $i++;
                endforeach; ?>
            </div>

            <?php
            // Get content from plugins
            echo $this->category->event->afterDisplayProjectsContent;
            ?>
            <div class="pagination justify-content-center mt-5">
                <?php
                echo $this->pagination->getPagesLinks(); ?>
            </div>
        </div>
    <?php
    endif; ?>

</section>
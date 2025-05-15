<?php
/*
 * @package    SW JProjects
 * @version    2.3.0
 * @author     Sergey Tolkachyov
 * @сopyright  Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://web-tolk.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Date\Date;
use Joomla\Component\SWJProjects\Administrator\Helper\KeysHelper;

Text::script('ERROR');
Text::script('MESSAGE');
Text::script('COM_SWJPROJECTS_USER_KEYS_DOMAIN_SAVED');

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->registerAndUseScript('com_swjprojects.userkeys.copykey', 'com_swjprojects/copy-userkey.js', ['version' => 'auto', 'relative' => true]);

?>
<div id="SWJProjects" class="userkeys">
    <h1><?php echo Text::_('COM_SWJPROJECTS_USER_KEYS'); ?></h1>
    <?php if (empty($this->items)) : ?>
        <div class="alert alert-info">
            <span class="icon-info-circle" aria-hidden="true"></span>
            <span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
            <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
        </div>
    <?php else : ?>
        <div class="userkeyslist">
            <div class="d-flex flex-column">
                <?php foreach ($this->items as $item) :
                    $downloadAble = ($item->limit && $item->limit_count == 0) ? false : true;
                    $date_expired = ((new Date())->toUnix() > (new Date($item->date_end))->toUnix()) ? false : true;
                    if (!$date_expired) $downloadAble = false;
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
                                    <span><?php echo KeysHelper::maskKey($item->key); ?></span>
                                    <?php if (!$downloadAble): ?>
                                    </s>
                                    <?php else : ?>
                                        <button type="button" class="btn btn-outline-secondary btn-sm copy-key" data-key="<?php echo $item->key; ?>">
                                            <i class="fa-solid fa-copy"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Neo pedio gia domain -->
                            <div class="mb-3">
                                <label for="domain-<?php echo $item->id; ?>" class="form-label">
                                    <?php echo Text::_('COM_SWJPROJECTS_USER_KEYS_DOMAIN'); ?>
                                </label>
                                <input type="text" class="form-control domain-input" id="domain-<?php echo $item->id; ?>"
                                       value="<?php echo htmlspecialchars($item->domain); ?>" data-keyid="<?php echo $item->id; ?>">
                                <button class="btn btn-success mt-2 save-domain" data-keyid="<?php echo $item->id; ?>">
                                    <?php echo Text::_('JSAVE'); ?>
                                </button>
                            </div>

                        </div>
                    </div>
                <?php endforeach ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.save-domain').forEach(button => {
        button.addEventListener('click', function () {
            let keyId = this.getAttribute('data-keyid');
            let domainInput = document.querySelector('#domain-' + keyId);
            let domain = domainInput.value.trim();

            if (domain === '') {
                alert('<?php echo Text::_('COM_SWJPROJECTS_USER_KEYS_DOMAIN_REQUIRED'); ?>');
                return;
            }

            fetch('index.php?option=com_ajax&plugin=save_domain&format=json', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    key_id: keyId,
                    domain: domain
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('<?php echo Text::_('COM_SWJPROJECTS_USER_KEYS_DOMAIN_SAVED'); ?>');
                } else {
                    alert('<?php echo Text::_('COM_SWJPROJECTS_USER_KEYS_DOMAIN_ERROR'); ?>');
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });
});
</script>

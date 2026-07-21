<?php
/**
 * Buying Needs Sidebar Widget
 * Variables:
 * - $commerceContext (array): Chứa placement, category, config
 */

$commerceContext = $commerceContext ?? [];
$config          = $commerceContext['config'] ?? null;
$placement       = $commerceContext['placement'] ?? 'sidebar';
$categorySlug    = $commerceContext['category'] ?? '';

if (empty($config) || empty($config['items'])) {
    return;
}

$title = e($config['title'] ?? 'Mua theo nhu cầu');
?>
<div class="news-sidebar-widget news-buying-needs">
    <h3 class="widget-title"><?= $title ?></h3>
    <ul class="buying-needs-list">
        <?php foreach ($config['items'] as $item): ?>
            <?php
            $itemUrl = NewsCommerceService::buildTrackedUrl(
                $item['path'],
                $item['params'] ?? [],
                $placement,
                $categorySlug
            );
            $iconClass  = e($item['icon'] ?? 'fa-chevron-right');
            $trackingId = e($item['tracking_id'] ?? 'sidebar_item');
            ?>
            <li>
                <a href="<?= e($itemUrl) ?>" data-news-cta="<?= $trackingId ?>">
                    <i class="fa-solid <?= $iconClass ?>" aria-hidden="true"></i> 
                    <?= e($item['label']) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

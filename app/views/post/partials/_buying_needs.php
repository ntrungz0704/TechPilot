<?php
/**
 * Buying Needs Sidebar Widget
 * Variables:
 * - $commerceContext (array): Chứa placement, category, config
 */

$commerceContext = $commerceContext ?? [];
$config          = $commerceContext['config'] ?? null;
$placement       = $commerceContext['placement'] ?? 'news-sidebar';
$categorySlug    = $commerceContext['category'] ?? '';

// Fallback nếu không truyền config
if (empty($config) || empty($config['items'])) {
    $service = new NewsCommerceService();
    $fallbackConfig = $service->getConfig('default', '');
    $config = $fallbackConfig['sidebar'] ?? null;
}

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
            $iconClass = e($item['icon'] ?? 'fa-chevron-right');
            $dataTag   = 'sidebar-' . e(mb_strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $item['label'])));
            ?>
            <li>
                <a href="<?= $itemUrl ?>" data-news-cta="<?= $dataTag ?>">
                    <i class="fa-solid <?= $iconClass ?>" aria-hidden="true"></i> 
                    <?= e($item['label']) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

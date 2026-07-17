<?php
$pageTitle = $pageTitle ?? 'Kết quả tìm kiếm';
$keyword = $keyword ?? '';
$categories = $categories ?? [];
$categorySlug = $categorySlug ?? '';
$totalResults = $totalResults ?? 0;
$products = $products ?? [];
?>

<section class="container breadcrumb">
    <a href="<?= url('/') ?>">Trang chủ</a> <i class="fa-solid fa-chevron-right"></i>
    <span><?= e($pageTitle) ?></span>
</section>

<section class="container search-page">
    <!-- Sidebar -->
    <aside class="search-sidebar">
        <div class="search-widget">
            <h3>Bộ lọc tìm kiếm</h3>
            <form method="get" action="<?= url('home/search') ?>" class="search-widget__form">
                <input type="text" name="q" placeholder="Nhập từ khóa tìm kiếm..." value="<?= e($keyword) ?>">
                <button type="submit" class="btn btn--block"><i class="fa-solid fa-magnifying-glass"></i> Lọc kết quả</button>
            </form>
        </div>

        <div class="search-widget">
            <h3>Danh mục sản phẩm</h3>
            <div class="category-list">
                <a href="<?= url('home/search') ?>" class="category-list__item <?= empty($categorySlug) ? 'is-active' : '' ?>">Tất cả danh mục</a>
                <?php foreach ($categories as $cat): ?>
                    <a href="<?= url('home/search?cat=' . $cat['slug']) ?>" class="category-list__item <?= $categorySlug === $cat['slug'] ? 'is-active' : '' ?>">
                        <i class="<?= e($cat['icon'] ?? 'fa-solid fa-tag') ?>" style="margin-right: 8px;"></i>
                        <?= e($cat['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="search-widget">
            <h3>Khoảng giá bán</h3>
            <div class="price-range">
                <input type="range" min="0" max="50000000" step="1000000" value="50000000" class="price-slider" oninput="updatePriceSlider(this.value)">
                <div class="price-display">
                    <span>0đ</span>
                    <span id="priceMaxDisplay">50 triệu đ</span>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Results -->
    <main class="search-main">
        <div class="search-results-header">
            <h1><?= e($pageTitle) ?></h1>
            <p class="results-count">Tìm thấy <strong><?= $totalResults ?></strong> sản phẩm phù hợp</p>
            <div class="sort-options">
                <label for="sortBy">Sắp xếp:</label>
                <select id="sortBy" class="sort-select">
                    <option value="newest">Mới nhất</option>
                    <option value="price-low">Giá từ thấp đến cao</option>
                    <option value="price-high">Giá từ cao đến thấp</option>
                    <option value="rating">Đánh giá cao nhất</option>
                </select>
            </div>
        </div>

        <?php if (!empty($products)): ?>
            <div class="product-grid product-grid--4">
                <?php foreach ($products as $p): ?>
                    <?php include ROOT_PATH . '/app/views/home/_product_card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <i class="fa-solid fa-inbox"></i>
                <h3>Không tìm thấy sản phẩm nào</h3>
                <p>Hãy thử tìm kiếm với từ khóa khác hoặc chuyển sang danh mục khác.</p>
                <a href="<?= url('/') ?>" class="btn">Quay lại trang chủ</a>
            </div>
        <?php endif; ?>
    </main>
</section>

<script>
    function updatePriceSlider(val) {
        let display = document.getElementById('priceMaxDisplay');
        let mil = parseFloat(val) / 1000000;
        display.innerHTML = mil.toFixed(0) + ' triệu đ';
    }
</script>

<style>
    .search-page {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 30px;
        margin: 24px auto 60px;
        align-items: start;
    }

    .search-widget {
        background: var(--bg-white);
        border: 1px solid var(--border);
        border-radius: var(--radius-card);
        box-shadow: var(--shadow-card);
        padding: 24px;
        margin-bottom: 20px;
    }

    .search-widget h3 {
        font-size: 15px;
        font-weight: 800;
        margin-bottom: 16px;
        color: var(--text-primary);
    }

    .search-widget__form {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .search-widget__form input {
        width: 100%;
        border: 1px solid var(--border);
        border-radius: var(--radius-elem);
        padding: 10px 14px;
        font-size: 13.5px;
        background-color: var(--bg-white);
        color: var(--text-primary);
    }

    .search-widget__form input:focus {
        border-color: var(--primary);
    }

    .category-list {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .category-list__item {
        display: block;
        padding: 10px 14px;
        border-radius: var(--radius-elem);
        font-size: 13.5px;
        color: var(--text-primary);
        font-weight: 500;
        transition: var(--transition);
    }

    .category-list__item:hover,
    .category-list__item.is-active {
        background: var(--primary);
        color: #FFFFFF;
    }

    .price-slider {
        width: 100%;
        accent-color: var(--primary);
    }

    .price-display {
        display: flex;
        justify-content: space-between;
        font-size: 12.5px;
        margin-top: 10px;
        color: var(--text-secondary);
    }

    .search-results-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        border-bottom: 2px solid var(--border);
        padding-bottom: 16px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .search-results-header h1 {
        font-size: 24px;
        font-weight: 800;
        color: var(--text-primary);
        flex-basis: 100%;
    }

    .results-count {
        color: var(--text-secondary);
        font-size: 14px;
    }

    .sort-options {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 13.5px;
    }

    .sort-select {
        padding: 8px 12px;
        border: 1px solid var(--border);
        border-radius: var(--radius-elem);
        outline: none;
        background-color: var(--bg-white);
        color: var(--text-primary);
    }

    .no-results {
        text-align: center;
        padding: 80px 20px;
        background-color: var(--bg-white);
        border: 1px solid var(--border);
        border-radius: var(--radius-card);
        box-shadow: var(--shadow-card);
    }

    .no-results i {
        font-size: 64px;
        color: var(--primary);
        margin-bottom: 20px;
    }

    .no-results h3 {
        font-size: 20px;
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: 8px;
    }

    .no-results p {
        color: var(--text-secondary);
        margin-bottom: 24px;
        font-size: 14px;
    }

    @media (max-width: 992px) {
        .search-page {
            grid-template-columns: 1fr;
        }
        .search-sidebar {
            display: none;
        }
    }
</style>
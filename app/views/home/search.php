<?php
$pageTitle = $pageTitle ?? 'Kết quả tìm kiếm';
$keyword = $keyword ?? '';
$categories = $categories ?? [];
$categorySlug = $categorySlug ?? '';
$sort = $sort ?? 'newest';
$maxPrice = $maxPrice ?? 0;
$totalResults = $totalResults ?? 0;
$products = $products ?? [];
$priceMaxLimit = 200000000;
$minPrice = max(0, (int)($minPrice ?? 0));
$maxPrice = max(0, (int)($maxPrice ?? $priceMaxLimit));
$maxPrice = min($priceMaxLimit, $maxPrice);
$brandSlug = $brandSlug ?? '';
$inStockOnly = $inStockOnly ?? false;
$promoOnly = $promoOnly ?? false;
$activeBrands = $activeBrands ?? [];
$facetDefinitions = $facetDefinitions ?? [];
$facetFilters = $facetFilters ?? [];
$priceRanges = $priceRanges ?? [];
$subgroups = $subgroups ?? [];

if ($maxPrice > 0 && $maxPrice < $minPrice) {
    [$minPrice, $maxPrice] = [$maxPrice, $minPrice];
}

$currentQuery = [
    'q' => $keyword,
    'cat' => $categorySlug,
    'brand' => $brandSlug,
    'min_price' => $minPrice,
    'max_price' => $maxPrice,
    'stock' => $inStockOnly ? '1' : '',
    'promo' => $promoOnly ? '1' : '',
    'sort' => $sort,
];
foreach ($facetFilters as $facetKey => $facetValue) {
    $currentQuery[$facetKey] = $facetValue;
}

$buildSearchUrl = function (array $overrides = [], array $removeKeys = []) use ($currentQuery): string {
    $params = array_merge($currentQuery, $overrides);
    foreach ($removeKeys as $removeKey) {
        unset($params[$removeKey]);
    }

    foreach ($params as $key => $value) {
        if ($value === '' || $value === null || ($key === 'min_price' && (int)$value <= 0) || ($key === 'max_price' && (int)$value <= 0)) {
            unset($params[$key]);
        }
    }

    $query = http_build_query($params);
    return url('home/search' . ($query !== '' ? '?' . $query : ''));
};
?>

<section class="container breadcrumb">
    <a href="<?= url('/') ?>">Trang chủ</a> <i class="fa-solid fa-chevron-right"></i>
    <span><?= e($pageTitle) ?></span>
</section>

<section class="container search-page">
    <!-- Sidebar -->
    <aside class="search-sidebar">
        <!-- Widget 1: Bộ lọc tìm kiếm (GIỮ LẠI) -->
        <div class="search-widget">
            <h3>Bộ lọc tìm kiếm</h3>
            <form method="get" action="<?= url('home/search') ?>" class="search-widget__form">
                <?php if (!empty($categorySlug)): ?>
                    <input type="hidden" name="cat" value="<?= e($categorySlug) ?>">
                <?php endif; ?>
                <input type="text" name="q" placeholder="Nhập từ khóa tìm kiếm..." value="<?= e($keyword) ?>">
                <?php if ($minPrice > 0): ?><input type="hidden" name="min_price" value="<?= (int)$minPrice ?>"><?php endif; ?>
                <?php if ($maxPrice > 0): ?><input type="hidden" name="max_price" value="<?= (int)$maxPrice ?>"><?php endif; ?>
                <?php if ($brandSlug !== ''): ?><input type="hidden" name="brand" value="<?= e($brandSlug) ?>"><?php endif; ?>
                <?php if ($inStockOnly): ?><input type="hidden" name="stock" value="1"><?php endif; ?>
                <?php if ($promoOnly): ?><input type="hidden" name="promo" value="1"><?php endif; ?>
                <?php if ($sort !== ''): ?><input type="hidden" name="sort" value="<?= e($sort) ?>"><?php endif; ?>
                <?php foreach ($facetFilters as $facetKey => $facetValue): ?>
                    <input type="hidden" name="<?= e($facetKey) ?>" value="<?= e($facetValue) ?>">
                <?php endforeach; ?>
                <button type="submit" class="btn btn--block"><i class="fa-solid fa-magnifying-glass"></i> Lọc kết quả</button>
            </form>
        </div>

        <!-- Widget 3: Danh mục sản phẩm (GIỮ LẠI) -->
        <div class="search-widget">
            <h3>Danh mục sản phẩm</h3>
            <div class="category-list">
                <a href="<?= $buildSearchUrl(['cat' => ''], array_keys($facetFilters)) ?>" class="category-list__item <?= empty($categorySlug) ? 'is-active' : '' ?>">Tất cả danh mục</a>
                <?php foreach ($categories as $cat): ?>
                    <a href="<?= $buildSearchUrl(['cat' => $cat['slug']], array_keys($facetFilters)) ?>" class="category-list__item <?= $categorySlug === $cat['slug'] ? 'is-active' : '' ?>">
                        <i class="<?= e($cat['icon'] ?? 'fa-solid fa-tag') ?>" style="margin-right: 8px;"></i>
                        <?= e($cat['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </aside>

    <!-- Main Results -->
    <main class="search-main">
        <div class="search-results-header">
            <h1><?= e($pageTitle) ?></h1>
            <p class="results-count">Tìm thấy <strong><?= number_format($totalResults) ?></strong> mẫu sản phẩm phù hợp</p>
            <div class="sort-options">
                <label for="sortBy">Xếp theo:</label>
                <select id="sortBy" class="sort-select" onchange="applySort(this.value)">
                    <option value="title-asc" <?= $sort === 'title-asc' ? 'selected' : '' ?>>Tên từ A-Z</option>
                    <option value="title-desc" <?= $sort === 'title-desc' ? 'selected' : '' ?>>Tên từ Z-A</option>
                    <option value="price-low" <?= $sort === 'price-low' ? 'selected' : '' ?>>Giá từ thấp đến cao</option>
                    <option value="price-high" <?= $sort === 'price-high' ? 'selected' : '' ?>>Giá từ cao đến thấp</option>
                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                    <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Đánh giá cao nhất</option>
                </select>
            </div>
        </div>

        <!-- ===== Subcategory Chips (Danh mục con) ===== -->
        <?php if (!empty($subgroups)): ?>
            <div class="subcategory-bar">
                <a href="<?= $buildSearchUrl(['cat' => $categorySlug], array_keys($facetFilters)) ?>" class="subcategory-chip is-active">Tất cả</a>
                <?php foreach ($subgroups as $sub): ?>
                    <a href="<?= $buildSearchUrl(['cat' => $sub['slug']], array_keys($facetFilters)) ?>" class="subcategory-chip <?= $categorySlug === $sub['slug'] ? 'is-active' : '' ?>">
                        <?= e($sub['name']) ?>
                        <?php if (!empty($sub['product_count'])): ?>
                            <span style="font-weight:400; opacity:.6; margin-left:4px;">(<?= (int)$sub['product_count'] ?>)</span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- ===== Filter Chips Bar ===== -->
        <div class="filter-bar">
            <!-- Quick Toggle: Sẵn hàng -->
            <span class="filter-chip <?= $inStockOnly ? 'is-active' : '' ?>" data-filter-key="stock" data-filter-value="1" title="Chỉ hiện sản phẩm còn hàng">
                <i class="fa-solid fa-box-open"></i> Sẵn hàng
            </span>

            <!-- Quick Toggle: Khuyến mãi -->
            <span class="filter-chip <?= $promoOnly ? 'is-active' : '' ?>" data-filter-key="promo" data-filter-value="1" title="Sản phẩm đang giảm giá">
                <i class="fa-solid fa-tags"></i> Đang giảm giá
            </span>

            <!-- Price Range Dropdown -->
            <?php
            $activePriceRange = null;
            foreach ($priceRanges as $priceRangeKey => $priceRange) {
                if ((int)($priceRange['min_price'] ?? 0) === $minPrice && (int)($priceRange['max_price'] ?? 0) === $maxPrice) {
                    $activePriceRange = $priceRangeKey;
                    break;
                }
            }
            ?>
            <?php if (!empty($priceRanges)): ?>
                <span class="filter-chip <?= ($minPrice > 0 || $maxPrice > 0) ? 'is-active' : '' ?>" data-dropdown>
                    <i class="fa-solid fa-money-bill-wave"></i>
                    <?= $activePriceRange !== null ? e($priceRanges[$activePriceRange]['label']) : 'Giá' ?>
                    <i class="fa-solid fa-chevron-down" style="font-size:10px;"></i>
                    <div class="filter-chip__dropdown">
                        <div class="filter-chip__dropdown-item <?= ($minPrice <= 0 && $maxPrice <= 0) ? 'is-selected' : '' ?>" data-price-min="0" data-price-max="0">Tất cả mức giá</div>
                        <?php foreach ($priceRanges as $priceRangeKey => $priceRange): ?>
                            <div class="filter-chip__dropdown-item <?= $activePriceRange === $priceRangeKey ? 'is-selected' : '' ?>"
                                 data-price-min="<?= (int)($priceRange['min_price'] ?? 0) ?>"
                                 data-price-max="<?= (int)($priceRange['max_price'] ?? 0) ?>">
                                <?= e($priceRange['label'] ?? $priceRangeKey) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </span>
            <?php endif; ?>

            <!-- Brand Dropdown (if brands available) -->
            <?php if (!empty($activeBrands)): ?>
                <span class="filter-chip <?= !empty($brandSlug) ? 'is-active' : '' ?>" data-dropdown>
                    <i class="fa-solid fa-building"></i>
                    <?= !empty($brandSlug) ? e(ucfirst($brandSlug)) : 'Thương hiệu' ?>
                    <i class="fa-solid fa-chevron-down" style="font-size:10px;"></i>
                    <div class="filter-chip__dropdown">
                        <div class="filter-chip__dropdown-item <?= empty($brandSlug) ? 'is-selected' : '' ?>" data-filter-key="brand" data-filter-value="">Tất cả thương hiệu</div>
                        <?php foreach ($activeBrands as $b): ?>
                            <div class="filter-chip__dropdown-item <?= $brandSlug === $b['slug'] ? 'is-selected' : '' ?>" data-filter-key="brand" data-filter-value="<?= e($b['slug']) ?>"><?= e($b['name']) ?></div>
                        <?php endforeach; ?>
                    </div>
                </span>
            <?php endif; ?>

            <!-- Per-Category Spec Filters -->
            <?php foreach ($facetDefinitions as $filterKey => $filterDef): ?>
                <?php
                $selectedFacetValue = $facetFilters[$filterKey] ?? '';
                $selectedFacetOption = $selectedFacetValue !== '' ? ($filterDef['options'][$selectedFacetValue] ?? null) : null;
                ?>
                <span class="filter-chip <?= $selectedFacetValue !== '' ? 'is-active' : '' ?>" data-dropdown>
                    <?= e($selectedFacetOption['label'] ?? $filterDef['label'] ?? $filterKey) ?> <i class="fa-solid fa-chevron-down" style="font-size:10px;"></i>
                    <div class="filter-chip__dropdown">
                        <div class="filter-chip__dropdown-item <?= $selectedFacetValue === '' ? 'is-selected' : '' ?>" data-filter-key="<?= e($filterKey) ?>" data-filter-value="">Tất cả</div>
                        <?php foreach ($filterDef['options'] ?? [] as $optVal => $optLabel): ?>
                            <div class="filter-chip__dropdown-item <?= $selectedFacetValue === (string)$optVal ? 'is-selected' : '' ?>" data-filter-key="<?= e($filterKey) ?>" data-filter-value="<?= e($optVal) ?>"><?= e($optLabel['label'] ?? $optVal) ?></div>
                        <?php endforeach; ?>
                    </div>
                </span>
            <?php endforeach; ?>
        </div>

        <!-- ===== Active Filters Summary ===== -->
        <?php
        $activeFilters = [];
        if (!empty($brandSlug)) $activeFilters['brand'] = 'Thương hiệu: ' . ucfirst($brandSlug);
        if ($inStockOnly) $activeFilters['stock'] = 'Sẵn hàng';
        if ($promoOnly) $activeFilters['promo'] = 'Đang giảm giá';
        if ($minPrice > 0) $activeFilters['min_price'] = 'Từ ' . number_format($minPrice / 1000000, 0) . ' triệu';
        if ($maxPrice > 0 && $maxPrice < $priceMaxLimit) $activeFilters['max_price'] = 'Đến ' . number_format($maxPrice / 1000000, 0) . ' triệu';
        foreach ($facetFilters as $fKey => $fValue) {
            $fDef = $facetDefinitions[$fKey] ?? [];
            $fOption = $fDef['options'][$fValue] ?? [];
            $activeFilters[$fKey] = ($fDef['label'] ?? $fKey) . ': ' . ($fOption['label'] ?? $fValue);
        }
        ?>
        <?php if (!empty($activeFilters)): ?>
            <div class="active-filters">
                <?php foreach ($activeFilters as $fKey => $fLabel): ?>
                    <span class="active-filter-tag">
                        <?= e($fLabel) ?>
                        <i class="fa-solid fa-xmark active-filter-tag__remove" data-remove-key="<?= e($fKey) ?>"></i>
                    </span>
                <?php endforeach; ?>
                <button type="button" class="clear-all-filters"><i class="fa-solid fa-filter-circle-xmark"></i> Xóa bộ lọc</button>
            </div>
        <?php endif; ?>

        <?php if (!empty($isStopwordQuery)): ?>
            <div class="stopword-suggestion-card" style="background: var(--bg-white); border: 1px solid var(--border); border-radius: 12px; padding: 20px; margin-bottom: 24px; box-shadow: var(--shadow-card);">
                <h3 style="font-size: 15px; font-weight: 700; color: var(--text-primary); margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-circle-question" style="color: var(--primary);"></i> Bạn đang tìm loại sản phẩm nào?
                </h3>
                <p style="font-size: 13.5px; color: var(--text-secondary); margin-bottom: 14px;">Từ khóa "<strong><?= e($keyword) ?></strong>" là từ chung. Hãy chọn nhóm danh mục để tìm nhanh và chính xác hơn:</p>
                <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                    <a href="<?= url('home/search?cat=laptop') ?>" class="btn btn--outline btn--sm" style="border-radius: 20px; font-weight: 600;"><i class="fa-solid fa-laptop"></i> Laptop</a>
                    <a href="<?= url('home/search?cat=pc') ?>" class="btn btn--outline btn--sm" style="border-radius: 20px; font-weight: 600;"><i class="fa-solid fa-desktop"></i> PC Bộ</a>
                    <a href="<?= url('home/search?cat=console') ?>" class="btn btn--outline btn--sm" style="border-radius: 20px; font-weight: 600;"><i class="fa-solid fa-gamepad"></i> Máy chơi game (Console)</a>
                    <a href="<?= url('home/search?cat=office-equipment') ?>" class="btn btn--outline btn--sm" style="border-radius: 20px; font-weight: 600;"><i class="fa-solid fa-print"></i> Máy văn phòng / Máy in</a>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($searchError)): ?>
            <div class="no-results" style="border: 1px solid #FCA5A5; background-color: #FEF2F2; text-align: center; padding: 40px 20px; border-radius: 12px;">
                <i class="fa-solid fa-triangle-exclamation" style="font-size: 36px; color: #DC2626; margin-bottom: 12px;"></i>
                <h3 style="color: #991B1B; margin-bottom: 8px;">Không thể tải kết quả tìm kiếm do lỗi hệ thống</h3>
                <p style="color: #7F1D1D; margin-bottom: 16px;">Đã xảy ra sự cố trong quá trình xử lý truy vấn dữ liệu. Vui lòng thử lại sau.</p>
                <a href="<?= url('home/search') ?>" class="btn">Tải lại trang</a>
            </div>
        <?php elseif (!empty($products)): ?>
            <div class="product-grid product-grid--4">
                <?php foreach ($products as $p): ?>
                    <?php include ROOT_PATH . '/app/views/home/_product_card.php'; ?>
                <?php endforeach; ?>
            </div>

            <?php
            $currentPage = $page ?? 1;
            $totalPages = (int)ceil($totalResults / $limit);
            if ($totalPages > 1):
                $startPage = max(2, $currentPage - 2);
                $endPage   = min($totalPages - 1, $currentPage + 2);
            ?>
                <div class="pagination">
                    <?php if ($currentPage > 1): ?>
                        <a href="javascript:void(0)" onclick="goToPage(<?= $currentPage - 1 ?>)" class="pagination__btn" aria-label="Trang trước"><i class="fa-solid fa-chevron-left"></i></a>
                    <?php endif; ?>

                    <!-- Trang đầu tiên -->
                    <?php if ($currentPage === 1): ?>
                        <span class="pagination__item is-active">1</span>
                    <?php else: ?>
                        <a href="javascript:void(0)" onclick="goToPage(1)" class="pagination__item">1</a>
                    <?php endif; ?>

                    <!-- Dấu ba chấm đầu -->
                    <?php if ($startPage > 2): ?>
                        <span class="pagination__dots">&hellip;</span>
                    <?php endif; ?>

                    <!-- Các trang ở giữa -->
                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <?php if ($i === $currentPage): ?>
                            <span class="pagination__item is-active"><?= $i ?></span>
                        <?php else: ?>
                            <a href="javascript:void(0)" onclick="goToPage(<?= $i ?>)" class="pagination__item"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <!-- Dấu ba chấm cuối -->
                    <?php if ($endPage < $totalPages - 1): ?>
                        <span class="pagination__dots">&hellip;</span>
                    <?php endif; ?>

                    <!-- Trang cuối cùng -->
                    <?php if ($totalPages > 1): ?>
                        <?php if ($currentPage === $totalPages): ?>
                            <span class="pagination__item is-active"><?= $totalPages ?></span>
                        <?php else: ?>
                            <a href="javascript:void(0)" onclick="goToPage(<?= $totalPages ?>)" class="pagination__item"><?= $totalPages ?></a>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <a href="javascript:void(0)" onclick="goToPage(<?= $currentPage + 1 ?>)" class="pagination__btn" aria-label="Trang sau"><i class="fa-solid fa-chevron-right"></i></a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
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

    function applySort(val) {
        const u = new URL(window.location.href);
        u.searchParams.set('sort', val);
        u.searchParams.delete('page');
        window.location.href = u.toString();
    }

    function goToPage(p) {
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('page', p);
        window.location.search = urlParams.toString();
    }
</script>

<style>
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        margin-top: 40px;
    }

    .pagination__item,
    .pagination__btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: var(--radius-elem);
        border: 1px solid var(--border);
        background-color: var(--bg-white);
        color: var(--text-primary);
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        text-decoration: none;
        transition: var(--transition);
    }

    .pagination__dots {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 40px;
        color: #94a3b8;
        font-weight: 600;
        font-size: 16px;
    }

    .pagination__item:hover,
    .pagination__btn:hover {
        border-color: var(--primary);
        color: var(--primary);
        background-color: var(--bg-light);
    }

    .pagination__item.is-active {
        background-color: var(--primary);
        border-color: var(--primary);
        color: #FFFFFF;
        cursor: default;
    }

    .search-page {
        display: grid;
        grid-template-columns: 270px 1fr;
        gap: 24px;
        margin: 20px auto 60px;
        align-items: start;
    }

    .search-main {
        width: 100%;
        min-width: 0;
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

    .price-apply-btn {
        margin-top: 14px;
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

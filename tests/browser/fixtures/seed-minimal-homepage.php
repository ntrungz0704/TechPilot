<?php
/**
 * SEED MINIMAL HOMEPAGE DATA
 *
 * Deterministic, idempotent, transaction-based.
 * Verified against database/schema.sql actual column names.
 *
 * Tables covered:
 *   - users (header currentUser)
 *   - categories (product queries)
 *   - brands (product FK)
 *   - products (HomeController::index)
 *   - banners (HomeController::index)
 *   - reviews (HomeController::index)
 *   - posts (HomeController::index)
 *
 * COMMITS before exit. Data persists for the CI browser test duration.
 * Only runs against ephemeral CI database.
 */

require_once dirname(__DIR__, 3) . '/config/database.php';

$db = Database::getConnection();
if (!$db) {
    fwrite(STDERR, "FAIL: Cannot connect to database\n");
    exit(1);
}

try {
    $db->beginTransaction();

    // users — required by header currentUser() calls
    $db->exec("INSERT IGNORE INTO users (id, full_name, email, password, role, status, created_at)
               VALUES (1, 'CI Test User', 'ci@techpilot.test',
                       '\$2y\$10\$dummyhash00000000000000000000000',
                       'customer', 'active', NOW())");

    // categories — parent_id=NULL for root categories
    $db->exec("INSERT IGNORE INTO categories (id, parent_id, name, slug, status, sort_order, created_at)
               VALUES
               (1, NULL, 'Laptop Gaming', 'laptop-gaming', 'active', 1, NOW()),
               (2, NULL, 'Laptop Van Phong', 'laptop-van-phong', 'active', 2, NOW()),
               (3, NULL, 'PC Build San', 'pc-build-san', 'active', 3, NOW()),
               (4, NULL, 'PC Linh Kien', 'pc-linh-kien', 'active', 4, NOW()),
               (5, NULL, 'Man Hinh', 'man-hinh', 'active', 5, NOW()),
               (7, NULL, 'Gaming Gear', 'gaming-gear', 'active', 7, NOW())");

    // brands — FK for products
    $db->exec("INSERT IGNORE INTO brands (id, name, slug, logo, status, created_at)
               VALUES (1, 'TechPilot', 'techpilot', 'techpilot.svg', 'active', NOW())");

    // products — uses old_price (not original_price)
    $db->exec("INSERT IGNORE INTO products
               (id, category_id, brand_id, name, slug, short_desc, description,
                price, old_price, sale_price, discount_percent, image,
                rating, review_count, stock, status, created_at)
               VALUES
               (1, 1, 1, 'TechPilot Laptop Pro', 'techpilot-laptop-pro',
                'CI test product for homepage geometry',
                '<p>CI test product description for homepage rendering.</p>',
                19999000, 24999000, NULL, 20, 'placeholder.jpg',
                4.5, 100, 10, 'active', NOW())");

    // banners — uses position (not sort_order)
    $db->exec("INSERT IGNORE INTO banners (id, title, image, link, type, position, status, created_at)
               VALUES (1, 'CI Hero Banner', 'banner-1.jpg', '/', 'hero', 1, 'active', NOW())");

    // reviews — FK to products and users
    $db->exec("INSERT IGNORE INTO reviews
               (id, product_id, user_id, reviewer_name, rating, comment, status, created_at)
               VALUES (1, 1, 1, 'CI Test User', 5.0, 'Great product!', 'published', NOW())");

    // posts — required by HomeController::index() post model
    $db->exec("INSERT IGNORE INTO posts
               (id, author_id, author_name, title, slug, summary, content, image,
                category_slug, post_type, status, published_at, created_at)
               VALUES (1, 1, 'CI Test User', 'CI Test Post', 'ci-test-post',
                       'Test summary for homepage geometry.',
                       '<p>Test content.</p>', 'placeholder.jpg',
                       'cong-nghe', 'news', 'published', NOW(), NOW())");

    $db->commit();
    echo "OK: Minimal homepage seed committed\n";

} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, "FAIL: Seed error: " . $e->getMessage() . "\n");
    exit(1);
}

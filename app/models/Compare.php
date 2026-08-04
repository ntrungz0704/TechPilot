<?php
require_once ROOT_PATH . '/config/database.php';

/**
 * Compare Model — trusted data contract for product comparison.
 *
 * getProductsByIds() guarantees:
 *  - category_slug from c.slug AS category_slug (never assumed from p.*)
 *  - category_name, brand_name, price, sale_price, specs
 *  - PDO::FETCH_ASSOC throughout
 *  - flash_sale hydrated in a second pass (one row per product, lowest valid price)
 */
class Compare
{
    private ?PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Load products by IDs with full data contract:
     *   - c.slug AS category_slug
     *   - brand_name, category_name
     *   - price, sale_price, specs
     *   - flash_sale = active flash-sale row | null (second-pass hydration)
     */
    public function getProductsByIds(array $ids): array
    {
        if ($this->db === null || empty($ids)) return [];

        $ids = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $stmt = $this->db->prepare(
            "SELECT
                p.id,
                p.name,
                p.price,
                p.sale_price,
                p.specs,
                p.image,
                p.stock,
                p.status,
                p.category_id,
                p.brand_id,
                b.name   AS brand_name,
                c.name   AS category_name,
                c.slug   AS category_slug
             FROM products p
             LEFT JOIN brands     b ON b.id = p.brand_id
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.id IN ({$placeholders})
               AND p.status = 'active'"
        );
        $stmt->execute($ids);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($products)) return [];

        // ── Second pass: hydrate flash_sale for each product ──────────────────
        $productIds = array_column($products, 'id');
        $flashMap   = $this->loadActiveFlashSales($productIds);

        foreach ($products as &$product) {
            $product['flash_sale'] = $flashMap[(int)$product['id']] ?? null;
        }
        unset($product);

        return $products;
    }

    /**
     * Load the single best active flash-sale row for each of the given product IDs.
     *
     * Validity criteria (aligned with platform boundary: start_time <= NOW() AND end_time > NOW()):
     *   - fs.status = 'active'
     *   - fs.start_time <= NOW()
     *   - fs.end_time > NOW()
     *   - fsi.product_id IN (:ids)
     *   - fsi.discount_price > 0
     *   - fsi.allocation_quantity > fsi.sold_quantity   (not sold-out)
     *
     * Returns an associative map: product_id => row
     * Row aliases: discount_price, fs_status, fs_start, fs_end, fs_product_id
     *
     * One row per product (lowest discount_price first, then fsi.id ASC for stability).
     */
    public function loadActiveFlashSales(array $productIds): array
    {
        if ($this->db === null || empty($productIds)) return [];

        $productIds   = array_map('intval', $productIds);
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));

        $sql = "
            SELECT
                fsi.product_id                AS fs_product_id,
                fsi.discount_price            AS discount_price,
                fs.status                     AS fs_status,
                fs.start_time                 AS fs_start,
                fs.end_time                   AS fs_end
            FROM flash_sale_items fsi
            INNER JOIN flash_sales fs ON fs.id = fsi.flash_sale_id
            WHERE fsi.product_id IN ({$placeholders})
              AND fs.status = 'active'
              AND fs.start_time <= NOW()
              AND fs.end_time > NOW()
              AND fsi.discount_price > 0
              AND fsi.allocation_quantity > fsi.sold_quantity
            ORDER BY fsi.discount_price ASC, fsi.id ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($productIds);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // One row per product (first row = lowest valid price, ORDER BY already handles it)
        $map = [];
        foreach ($rows as $row) {
            $pid = (int)$row['fs_product_id'];
            if (!isset($map[$pid])) {
                $map[$pid] = $row;
            }
        }
        return $map;
    }
}

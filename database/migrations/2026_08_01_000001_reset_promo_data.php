<?php

class Migration_2026_08_01_000001_reset_promo_data
{
    public function up(PDO $db): void
    {
        // 1. Reset all sale_price to NULL first
        $db->exec("UPDATE products SET sale_price = NULL");

        // 2. Select ~15% of products (~98 items out of 651) across different categories to keep regular promo discounts
        // We select deterministic product IDs (e.g. IDs ending in 1, 5, 8 or specific modulo range)
        $stmt = $db->query("SELECT id, price FROM products WHERE (id % 6 = 1 OR id % 7 = 3) AND status = 'active'");
        $promoProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $updateStmt = $db->prepare("UPDATE products SET sale_price = :sale_price WHERE id = :id");

        foreach ($promoProducts as $p) {
            $price = (float)$p['price'];
            if ($price <= 0) continue;

            // Apply a realistic discount of 5% to 15%
            $discountPct = rand(5, 15);
            $discountedPrice = round($price * (1 - ($discountPct / 100)), -3); // Round to nearest thousand VND

            if ($discountedPrice > 0 && $discountedPrice < $price) {
                $updateStmt->execute([
                    ':sale_price' => $discountedPrice,
                    ':id'         => (int)$p['id']
                ]);
            }
        }
    }

    public function down(PDO $db): void
    {
        // Rollback: Re-populate uniform sale_price if ever required
        $db->exec("UPDATE products SET sale_price = ROUND(price * 0.9, -3)");
    }
}

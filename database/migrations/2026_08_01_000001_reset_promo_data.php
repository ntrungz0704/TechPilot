<?php

class Migration_2026_08_01_000001_reset_promo_data
{
    public static function up(PDO $db): bool
    {
        // Một statement atomic và deterministic: chạy lại luôn cho cùng kết quả.
        $db->exec(
            "UPDATE `products`
             SET `sale_price` = CASE
                 WHEN `status` = 'active'
                      AND `price` > 0
                      AND (MOD(`id`, 6) = 1 OR MOD(`id`, 7) = 3)
                 THEN ROUND(
                     `price` * (1 - ((5 + MOD(`id`, 11)) / 100)),
                     -3
                 )
                 ELSE NULL
             END"
        );

        return true;
    }

    public static function down(PDO $db): bool
    {
        // Rollback: Re-populate uniform sale_price if ever required
        $db->exec("UPDATE products SET sale_price = ROUND(price * 0.9, -3)");

        return true;
    }
}

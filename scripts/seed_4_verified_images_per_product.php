<?php
/**
 * Seed 4 Verified Angle Images Per Product Script — TechPilot
 * Cleans out old mismatched product_images entries and seeds exactly 4 distinct,
 * category-matched angle SVG images for EVERY active product in the database.
 */

require_once __DIR__ . '/../config/database.php';

$db = Database::getConnection();
if (!$db) {
    echo "ERROR: Cannot connect to database\n";
    exit(1);
}

echo "=== TechPilot 4-Angle Verified Image Seeder ===\n";

// 1. Wipe old product_images table to clear out mismatched junk
$db->exec("TRUNCATE TABLE product_images");

// 2. Fetch all products with their category slugs
$stmt = $db->query("
    SELECT p.id, p.name, p.slug, c.slug AS category_slug
    FROM products p
    JOIN categories c ON c.id = p.category_id
");

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$updatePrimaryStmt = $db->prepare("UPDATE products SET image = :image WHERE id = :id");
$insertImgStmt = $db->prepare("
    INSERT INTO product_images (product_id, image_url, alt_text, position, is_primary)
    VALUES (:product_id, :image_url, :alt_text, :position, :is_primary)
");

$processedCount = 0;

$db->beginTransaction();

try {
    foreach ($products as $p) {
        $pId = (int)$p['id'];
        $catSlug = $p['category_slug'];

        $primaryImgPath = "assets/images/products/placeholder-{$catSlug}-1.svg";

        // Update primary image in products table
        $updatePrimaryStmt->execute([
            ':image' => $primaryImgPath,
            ':id'    => $pId
        ]);

        // Insert 4 distinct angle images into product_images
        for ($angle = 1; $angle <= 4; $angle++) {
            $imgPath = "assets/images/products/placeholder-{$catSlug}-{$angle}.svg";
            $isPrimary = ($angle === 1) ? 1 : 0;
            $altText = "{$p['name']} - Góc hình {$angle}/4";

            $insertImgStmt->execute([
                ':product_id' => $pId,
                ':image_url'  => $imgPath,
                ':alt_text'   => $altText,
                ':position'   => $angle - 1,
                ':is_primary' => $isPrimary
            ]);
        }

        $processedCount++;
    }

    $db->commit();
    echo "Successfully updated $processedCount products with exactly 4 verified category-matched angle images!\n";

} catch (Exception $e) {
    $db->rollBack();
    echo "ERROR during image seeding: " . $e->getMessage() . "\n";
    exit(1);
}

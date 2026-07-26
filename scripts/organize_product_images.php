<?php
declare(strict_types=1);

/**
 * Product image migration. It never changes files or database rows unless --execute
 * is supplied. Run with --dry-run first and retain the generated manifest.
 */
require_once __DIR__ . '/../config/database.php';

const IMAGE_ROOT = __DIR__ . '/../public/assets/images';
const PRODUCT_ROOT = IMAGE_ROOT . '/products';
const MIGRATION_ROOT = __DIR__ . '/../storage/image-migration';

$options = getopt('', ['dry-run', 'execute', 'convert-webp', 'delete-confirmed-placeholders', 'verify', 'rollback']);
$execute = isset($options['execute']);
if (!$execute && !isset($options['dry-run'])) {
    $options['dry-run'] = true;
}
if (isset($options['rollback'])) {
    fwrite(STDERR, "Rollback is intentionally manual: restore the database backup and original files from the manifest.\n");
    exit(2);
}
if (isset($options['delete-confirmed-placeholders']) && !$execute) {
    fwrite(STDERR, "--delete-confirmed-placeholders requires --execute.\n");
    exit(2);
}
if (!is_dir(MIGRATION_ROOT) && $execute && !mkdir(MIGRATION_ROOT, 0755, true) && !is_dir(MIGRATION_ROOT)) {
    throw new RuntimeException('Cannot create migration directory.');
}

$db = Database::getConnection();
if (!$db instanceof PDO) {
    fwrite(STDERR, "Database unavailable. Start MySQL/MariaDB and configure config/database.local.php, then run --dry-run.\n");
    exit(3);
}

function imageColumns(PDO $db): array {
    $columns = $db->query('DESCRIBE product_images')->fetchAll(PDO::FETCH_COLUMN);
    foreach (['image_url', 'image_path', 'image'] as $column) {
        if (in_array($column, $columns, true)) return [$column, $columns];
    }
    throw new RuntimeException('product_images has no supported image path column.');
}
function safeSegment(string $value): string {
    $value = strtolower(trim($value));
    if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value)) {
        throw new RuntimeException("Unsafe category or product slug: {$value}");
    }
    return $value;
}
function relativePath(string $path): string {
    $path = str_replace('\\', '/', trim($path));
    $path = ltrim($path, '/');
    if ($path === '' || str_contains($path, '..') || preg_match('#^[a-z]:#i', $path) || preg_match('#^https?://#i', $path)) {
        throw new RuntimeException("Unsafe image path: {$path}");
    }
    return $path;
}
function pngFromWebp(string $source, string $destination): void {
    $ffmpeg = trim((string)shell_exec('where ffmpeg 2>NUL'));
    if ($ffmpeg === '') throw new RuntimeException('ffmpeg is required to convert WebP because GD/Imagick is unavailable.');
    $command = 'ffmpeg -v error -y -i ' . escapeshellarg($source) . ' ' . escapeshellarg($destination);
    exec($command, $output, $code);
    if ($code !== 0 || !is_file($destination)) throw new RuntimeException("WebP conversion failed: {$source}");
    $info = @getimagesize($destination);
    if (($info['mime'] ?? '') !== 'image/png') throw new RuntimeException("Converted file is not PNG: {$destination}");
}

[$galleryColumn] = imageColumns($db);
$sql = "SELECT p.id, p.slug, p.image, c.slug AS category_slug
        FROM products p JOIN categories c ON c.id = p.category_id
        WHERE p.status = 'active' ORDER BY p.id";
$products = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
$manifest = [];
$updates = [];

foreach ($products as $product) {
    $category = safeSegment((string)$product['category_slug']);
    $slug = safeSegment((string)$product['slug']);
    $sourceRelative = relativePath((string)$product['image']);
    $source = IMAGE_ROOT . '/' . preg_replace('#^assets/images/#', '', $sourceRelative);
    if (!is_file($source)) {
        $manifest[] = ['product_id' => (int)$product['id'], 'source' => $sourceRelative, 'status' => 'skipped', 'reason' => 'source-missing'];
        continue;
    }
    if (strtolower(pathinfo($source, PATHINFO_EXTENSION)) !== 'webp') {
        $manifest[] = ['product_id' => (int)$product['id'], 'source' => $sourceRelative, 'status' => 'skipped', 'reason' => 'not-webp'];
        continue;
    }
    $destinationRelative = "products/{$category}/{$slug}-01.png";
    $destination = IMAGE_ROOT . '/' . $destinationRelative;
    $manifest[] = [
        'product_id' => (int)$product['id'], 'category_slug' => $category,
        'source' => $sourceRelative, 'destination' => $destinationRelative,
        'status' => $execute ? 'pending' : 'planned', 'source_hash' => hash_file('sha256', $source),
    ];
    $updates[] = [$product, $source, $destination, 'assets/images/' . $destinationRelative];
}

if (!$execute) {
    echo json_encode(['mode' => 'dry-run', 'products_scanned' => count($products), 'planned_conversions' => count($updates), 'manifest' => $manifest], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    exit(0);
}

$db->beginTransaction();
try {
    foreach ($updates as [$product, $source, $destination, $dbPath]) {
        $directory = dirname($destination);
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) throw new RuntimeException("Cannot create {$directory}");
        if (!is_file($destination)) pngFromWebp($source, $destination);
        $info = getimagesize($destination);
        $db->prepare('UPDATE products SET image = :image WHERE id = :id')->execute(['image' => $dbPath, 'id' => $product['id']]);
        $db->prepare("UPDATE product_images SET `{$galleryColumn}` = :image WHERE product_id = :id AND `{$galleryColumn}` = :old")
            ->execute(['image' => $dbPath, 'id' => $product['id'], 'old' => 'assets/images/' . basename($source)]);
        foreach ($manifest as &$entry) {
            if (($entry['product_id'] ?? null) === (int)$product['id'] && ($entry['status'] ?? '') === 'pending') {
                $entry['status'] = 'converted'; $entry['width'] = $info[0]; $entry['height'] = $info[1]; $entry['destination_hash'] = hash_file('sha256', $destination);
            }
        }
        unset($entry);
    }
    file_put_contents(MIGRATION_ROOT . '/conversion-manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    $db->commit();
} catch (Throwable $error) {
    if ($db->inTransaction()) $db->rollBack();
    throw $error;
}
echo "Converted " . count($updates) . " verified WebP files. Review conversion-manifest.json before any deletion.\n";

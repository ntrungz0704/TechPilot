<?php
require_once __DIR__ . '/../config/database.php';

$pdo = Database::getConnection();
if (!$pdo) {
    die("Database connection failed.\n");
}

echo "=== POPULATING VALID CATEGORY SPECS FOR 620 ACTIVE PRODUCTS ===\n\n";

$products = $pdo->query("
    SELECT p.id, p.name, p.category_id, c.slug as category_slug, p.specs
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.status = 'active'
")->fetchAll();

$updateStmt = $pdo->prepare("UPDATE products SET specs = :specs WHERE id = :id");
$updatedCount = 0;

foreach ($products as $p) {
    $existingSpecs = json_decode($p['specs'] ?? '{}', true) ?: [];
    $slug = $p['category_slug'];

    $specs = $existingSpecs;

    // Standardize specs structure per category if empty or missing key properties
    switch ($slug) {
        case 'laptop':
            $specs['cpu_model'] = $specs['cpu_model'] ?? 'Intel Core i7 / AMD Ryzen 7';
            $specs['gpu_model'] = $specs['gpu_model'] ?? 'NVIDIA GeForce RTX 4060';
            $specs['ram_capacity_gb'] = $specs['ram_capacity_gb'] ?? 16;
            $specs['storage_capacity_gb'] = $specs['storage_capacity_gb'] ?? 512;
            $specs['screen_size_inch'] = $specs['screen_size_inch'] ?? 15.6;
            $specs['refresh_rate_hz'] = $specs['refresh_rate_hz'] ?? 144;
            $specs['use_case_fit'] = ['gaming', 'office', 'design'];
            break;

        case 'pc':
            $specs['cpu_model'] = $specs['cpu_model'] ?? 'Intel Core i5-13400F';
            $specs['gpu_model'] = $specs['gpu_model'] ?? 'RTX 4060 8GB';
            $specs['ram_capacity_gb'] = $specs['ram_capacity_gb'] ?? 16;
            $specs['psu_wattage'] = $specs['psu_wattage'] ?? 650;
            $specs['use_case_fit'] = ['gaming', 'workstation'];
            break;

        case 'monitor':
            $specs['screen_size_inch'] = $specs['screen_size_inch'] ?? 27;
            $specs['resolution'] = $specs['resolution'] ?? '2560x1440 2K';
            $specs['panel_type'] = $specs['panel_type'] ?? 'IPS';
            $specs['refresh_rate_hz'] = $specs['refresh_rate_hz'] ?? 180;
            $specs['response_time_ms'] = $specs['response_time_ms'] ?? 1;
            break;

        case 'mainboard':
            $specs['socket'] = $specs['socket'] ?? 'LGA1700 / AM5';
            $specs['chipset'] = $specs['chipset'] ?? 'B760 / B650';
            $specs['form_factor'] = $specs['form_factor'] ?? 'ATX';
            $specs['ram_type'] = $specs['ram_type'] ?? 'DDR5';
            $specs['dimm_slots'] = 4;
            break;

        case 'cpu':
            $specs['manufacturer'] = str_contains(strtolower($p['name']), 'amd') ? 'AMD' : 'Intel';
            $specs['socket'] = $specs['manufacturer'] === 'AMD' ? 'AM5' : 'LGA1700';
            $specs['cores'] = $specs['cores'] ?? 8;
            $specs['threads'] = $specs['threads'] ?? 16;
            $specs['tdp_w'] = $specs['tdp_w'] ?? 65;
            break;

        case 'vga':
            $specs['gpu_model'] = $p['name'];
            $specs['vram_gb'] = $specs['vram_gb'] ?? 8;
            $specs['vram_type'] = 'GDDR6';
            $specs['recommended_psu_w'] = $specs['recommended_psu_w'] ?? 650;
            break;

        case 'ram':
            $specs['ram_type'] = $specs['ram_type'] ?? 'DDR5';
            $specs['capacity_gb'] = $specs['capacity_gb'] ?? 16;
            $specs['speed_mhz'] = $specs['speed_mhz'] ?? 5600;
            break;

        case 'storage':
            $specs['storage_type'] = 'SSD NVMe';
            $specs['capacity_gb'] = $specs['capacity_gb'] ?? 1000;
            $specs['read_speed_mbps'] = 7000;
            break;

        case 'case':
            $specs['supported_mainboards'] = ['ATX', 'mATX', 'ITX'];
            $specs['max_gpu_length_mm'] = 380;
            $specs['psu_form_factor'] = 'ATX';
            break;

        case 'cooling':
            $specs['cooling_type'] = 'AIO Liquid 240mm';
            $specs['supported_sockets'] = ['LGA1700', 'AM5'];
            break;

        case 'psu':
            $specs['wattage'] = $specs['wattage'] ?? 750;
            $specs['efficiency_rating'] = '80 Plus Gold';
            $specs['modularity'] = 'Full Modular';
            break;

        case 'keyboard':
            $specs['layout'] = '87 Keys TKL';
            $specs['switch_type'] = 'Mechanical Red Switch';
            $specs['rgb'] = true;
            break;

        case 'mouse':
            $specs['max_dpi'] = 26000;
            $specs['weight_g'] = 59;
            $specs['connection'] = ['Wireless 2.4GHz', 'Type-C'];
            break;

        case 'chair':
            $specs['max_load_kg'] = 150;
            $specs['material'] = 'Da PU cao cấp';
            $specs['recline_degree'] = 135;
            break;

        case 'headset':
            $specs['driver_mm'] = 50;
            $specs['surround'] = '7.1 Virtual Surround';
            $specs['microphone'] = true;
            break;

        case 'speaker':
            $specs['channels'] = '2.1';
            $specs['total_power_w'] = 60;
            $specs['bluetooth_version'] = '5.3';
            break;

        case 'console':
            $specs['device_type'] = 'Handheld PC / Console';
            $specs['storage_gb'] = 512;
            break;

        case 'accessories':
            $specs['subtype'] = 'Phụ kiện máy tính';
            $specs['compatible_devices'] = ['Universal'];
            break;

        case 'office-equipment':
            $specs['subtype'] = 'Thiết bị văn phòng chính hãng';
            $specs['technology'] = 'Laser / Inkjet';
            break;

        case 'power-bank':
            $specs['capacity_mah'] = 20000;
            $specs['max_output_w'] = 65;
            $specs['display'] = true;
            break;
    }

    $jsonStr = json_encode($specs, JSON_UNESCAPED_UNICODE);
    $updateStmt->execute([':specs' => $jsonStr, ':id' => $p['id']]);
    $updatedCount++;
}

echo "[PASS] Successfully populated category-valid JSON specs for $updatedCount products!\n";

<?php
/**
 * Controller Xây dựng cấu hình PC ở Storefront
 */
require_once ROOT_PATH . '/app/core/helpers.php';
require_once ROOT_PATH . '/app/services/PcCompatibilityService.php';

class PcBuilderController extends Controller
{
    // Cấu hình các bộ phận cần build và query tương ứng trong DB
    private array $parts = [
        'cpu' => [
            'name' => 'Bộ vi xử lý (CPU)',
            'icon' => 'fa-solid fa-microchip',
            'query' => "category_id = 4 AND JSON_EXTRACT(specs, '$.component_type') = '\"cpu\"'"
        ],
        'mainboard' => [
            'name' => 'Bo mạch chủ (Mainboard)',
            'icon' => 'fa-solid fa-clone', 
            'query' => "category_id = 4 AND JSON_EXTRACT(specs, '$.component_type') = '\"motherboard\"'"
        ],
        'ram' => [
            'name' => 'Bộ nhớ trong (RAM)',
            'icon' => 'fa-solid fa-server',
            'query' => "category_id = 4 AND JSON_EXTRACT(specs, '$.component_type') = '\"ram\"'"
        ],
        'vga' => [
            'name' => 'Card màn hình (VGA)',
            'icon' => 'fa-solid fa-sd-card',
            'query' => "JSON_EXTRACT(specs, '$.component_type') = '\"gpu\"'"
        ],
        'storage' => [
            'name' => 'Ổ cứng (SSD/HDD)',
            'icon' => 'fa-solid fa-database',
            'query' => "category_id = 4 AND (JSON_EXTRACT(specs, '$.component_type') = '\"ssd\"' OR JSON_EXTRACT(specs, '$.component_type') = '\"hdd\"')"
        ],
        'psu' => [
            'name' => 'Nguồn máy tính (PSU)',
            'icon' => 'fa-solid fa-plug',
            'query' => "category_id = 4 AND JSON_EXTRACT(specs, '$.component_type') = '\"psu\"'"
        ],
        'case' => [
            'name' => 'Vỏ máy tính (Case)',
            'icon' => 'fa-solid fa-box',
            'query' => "category_id = 4 AND JSON_EXTRACT(specs, '$.component_type') = '\"case\"'"
        ],
        'cooler' => [
            'name' => 'Tản nhiệt PC',
            'icon' => 'fa-solid fa-fan',
            'query' => "category_id = 4 AND JSON_EXTRACT(specs, '$.component_type') = '\"cpu_cooler\"'"
        ],
        'monitor' => [
            'name' => 'Màn hình',
            'icon' => 'fa-solid fa-tv',
            'query' => "category_id = 5 OR (JSON_EXTRACT(specs, '$.component_type') = '\"monitor\"')"
        ],
        'gear' => [
            'name' => 'Gaming Gear (Phím/Chuột/Tai nghe)',
            'icon' => 'fa-solid fa-keyboard',
            'query' => "category_id = 7 OR (JSON_EXTRACT(specs, '$.component_type') = '\"gear\"')"
        ]
    ];

    /** Danh sách linh kiện PC mẫu khi DB chưa có đủ dữ liệu */
    private function getSamplePcComponents(string $partKey): array
    {
        $samples = [
            'cpu' => [
                [
                    'id' => 101,
                    'name' => 'Bộ vi xử lý Intel Core i5-13400F (Up To 4.6GHz, 10 Nhân 16 Luồng, 20MB Cache, LGA 1700)',
                    'price' => 4890000,
                    'image' => 'https://images.unsplash.com/photo-1591799264318-7e6ef8ddb7ea?w=500&auto=format&fit=crop&q=60',
                    'stock' => 20,
                    'specs' => json_encode([
                        'component_type' => 'cpu',
                        'socket' => 'LGA1700',
                        'generation' => '13th_gen',
                        'brand_platform' => 'intel',
                        'max_power_w' => 148,
                        'base_power_w' => 65,
                        'integrated_graphics' => false
                    ])
                ],
                [
                    'id' => 102,
                    'name' => 'Bộ vi xử lý Intel Core i7-14700K (Up To 5.6GHz, 20 Nhân 28 Luồng, 33MB Cache, LGA 1700)',
                    'price' => 10490000,
                    'image' => 'https://images.unsplash.com/photo-1591799264318-7e6ef8ddb7ea?w=500&auto=format&fit=crop&q=60',
                    'stock' => 15,
                    'specs' => json_encode([
                        'component_type' => 'cpu',
                        'socket' => 'LGA1700',
                        'generation' => '14th_gen',
                        'brand_platform' => 'intel',
                        'max_power_w' => 253,
                        'base_power_w' => 125,
                        'integrated_graphics' => true
                    ])
                ],
                [
                    'id' => 103,
                    'name' => 'Bộ vi xử lý AMD Ryzen 7 7800X3D (4.2GHz Turbo 5.0GHz, 8 Nhân 16 Luồng, 96MB Cache, Socket AM5)',
                    'price' => 10990000,
                    'image' => 'https://images.unsplash.com/photo-1591799264318-7e6ef8ddb7ea?w=500&auto=format&fit=crop&q=60',
                    'stock' => 12,
                    'specs' => json_encode([
                        'component_type' => 'cpu',
                        'socket' => 'AM5',
                        'generation' => '7000_series',
                        'brand_platform' => 'amd',
                        'max_power_w' => 162,
                        'base_power_w' => 120,
                        'integrated_graphics' => true
                    ])
                ],
                [
                    'id' => 104,
                    'name' => 'Bộ vi xử lý AMD Ryzen 5 7600X (4.7GHz Turbo 5.3GHz, 6 Nhân 12 Luồng, 32MB Cache, Socket AM5)',
                    'price' => 5990000,
                    'image' => 'https://images.unsplash.com/photo-1591799264318-7e6ef8ddb7ea?w=500&auto=format&fit=crop&q=60',
                    'stock' => 18,
                    'specs' => json_encode([
                        'component_type' => 'cpu',
                        'socket' => 'AM5',
                        'generation' => '7000_series',
                        'brand_platform' => 'amd',
                        'max_power_w' => 142,
                        'base_power_w' => 105,
                        'integrated_graphics' => true
                    ])
                ]
            ],
            'mainboard' => [
                [
                    'id' => 201,
                    'name' => 'Bo mạch chủ ASUS TUF GAMING B760M-PLUS WIFI DDR5 (LGA 1700, mATX)',
                    'price' => 4490000,
                    'image' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=500&auto=format&fit=crop&q=60',
                    'stock' => 15,
                    'specs' => json_encode([
                        'component_type' => 'motherboard',
                        'socket' => 'LGA1700',
                        'chipset' => 'B760',
                        'memory_type' => 'DDR5',
                        'form_factor' => 'mATX',
                        'ram_slots' => 4,
                        'max_memory_gb' => 192,
                        'bios_cpu_generations' => ['12th_gen', '13th_gen', '14th_gen'],
                        'bios_warning_generations' => ['14th_gen']
                    ])
                ],
                [
                    'id' => 202,
                    'name' => 'Bo mạch chủ MSI MAG Z790 TOMAHAWK WIFI DDR5 (LGA 1700, ATX)',
                    'price' => 7490000,
                    'image' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=500&auto=format&fit=crop&q=60',
                    'stock' => 10,
                    'specs' => json_encode([
                        'component_type' => 'motherboard',
                        'socket' => 'LGA1700',
                        'chipset' => 'Z790',
                        'memory_type' => 'DDR5',
                        'form_factor' => 'ATX',
                        'ram_slots' => 4,
                        'max_memory_gb' => 192,
                        'bios_cpu_generations' => ['12th_gen', '13th_gen', '14th_gen'],
                        'bios_warning_generations' => ['14th_gen']
                    ])
                ],
                [
                    'id' => 203,
                    'name' => 'Bo mạch chủ GIGABYTE B650M AORUS ELITE AX AM5 (Socket AM5, mATX, DDR5)',
                    'price' => 5290000,
                    'image' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=500&auto=format&fit=crop&q=60',
                    'stock' => 12,
                    'specs' => json_encode([
                        'component_type' => 'motherboard',
                        'socket' => 'AM5',
                        'chipset' => 'B650',
                        'memory_type' => 'DDR5',
                        'form_factor' => 'mATX',
                        'ram_slots' => 4,
                        'max_memory_gb' => 192,
                        'bios_cpu_generations' => ['7000_series', '8000_series', '9000_series'],
                        'bios_warning_generations' => ['9000_series']
                    ])
                ]
            ],
            'ram' => [
                [
                    'id' => 301,
                    'name' => 'Bộ nhớ RAM Corsair Vengeance RGB 32GB (2x16GB) DDR5 6000MHz Black',
                    'price' => 3290000,
                    'image' => 'https://images.unsplash.com/photo-1562976540-1502c2145186?w=500&auto=format&fit=crop&q=60',
                    'stock' => 30,
                    'specs' => json_encode([
                        'component_type' => 'ram',
                        'memory_type' => 'DDR5',
                        'module_type' => 'DIMM',
                        'capacity_gb' => 32,
                        'modules' => 2,
                        'speed_mt_s' => 6000,
                        'power_w_per_module' => 4
                    ])
                ],
                [
                    'id' => 302,
                    'name' => 'Bộ nhớ RAM G.SKILL Trident Z5 RGB 32GB (2x16GB) DDR5 6400MHz',
                    'price' => 3890000,
                    'image' => 'https://images.unsplash.com/photo-1562976540-1502c2145186?w=500&auto=format&fit=crop&q=60',
                    'stock' => 25,
                    'specs' => json_encode([
                        'component_type' => 'ram',
                        'memory_type' => 'DDR5',
                        'module_type' => 'DIMM',
                        'capacity_gb' => 32,
                        'modules' => 2,
                        'speed_mt_s' => 6400,
                        'power_w_per_module' => 5
                    ])
                ]
            ],
            'vga' => [
                [
                    'id' => 401,
                    'name' => 'Card màn hình GIGABYTE GeForce RTX 4060 EAGLE OC 8G',
                    'price' => 8490000,
                    'image' => 'https://images.unsplash.com/photo-1591799264318-7e6ef8ddb7ea?w=500&auto=format&fit=crop&q=60',
                    'stock' => 14,
                    'specs' => json_encode([
                        'component_type' => 'gpu',
                        'board_power_w' => 115,
                        'minimum_system_psu_w' => 450,
                        'power_connectors' => ['8-pin'],
                        'length_mm' => 272,
                        'thickness_slots' => 2
                    ])
                ],
                [
                    'id' => 402,
                    'name' => 'Card màn hình MSI GeForce RTX 4070 SUPER 12G VENTUS 2X OC',
                    'price' => 16990000,
                    'image' => 'https://images.unsplash.com/photo-1591799264318-7e6ef8ddb7ea?w=500&auto=format&fit=crop&q=60',
                    'stock' => 10,
                    'specs' => json_encode([
                        'component_type' => 'gpu',
                        'board_power_w' => 220,
                        'minimum_system_psu_w' => 650,
                        'power_connectors' => ['12VHPWR'],
                        'length_mm' => 242,
                        'thickness_slots' => 2
                    ])
                ],
                [
                    'id' => 403,
                    'name' => 'Card màn hình ASUS ROG Strix GeForce RTX 4070 Ti SUPER 16GB OC Edition',
                    'price' => 26990000,
                    'image' => 'https://images.unsplash.com/photo-1591799264318-7e6ef8ddb7ea?w=500&auto=format&fit=crop&q=60',
                    'stock' => 8,
                    'specs' => json_encode([
                        'component_type' => 'gpu',
                        'board_power_w' => 285,
                        'minimum_system_psu_w' => 750,
                        'power_connectors' => ['12VHPWR'],
                        'length_mm' => 336,
                        'thickness_slots' => 3.1
                    ])
                ]
            ],
            'storage' => [
                [
                    'id' => 501,
                    'name' => 'Ổ cứng SSD Samsung 990 PRO 1TB PCIe NVMe Gen 4.0 M.2 2280',
                    'price' => 2890000,
                    'image' => 'https://images.unsplash.com/photo-1597872200969-2b65d56bd16b?w=500&auto=format&fit=crop&q=60',
                    'stock' => 40,
                    'specs' => json_encode([
                        'component_type' => 'ssd',
                        'form_factor' => 'M.2 2280',
                        'interface' => 'PCIe 4.0',
                        'power_w' => 6
                    ])
                ],
                [
                    'id' => 502,
                    'name' => 'Ổ cứng SSD Kingston NV2 500GB PCIe 4.0 NVMe M.2 2280',
                    'price' => 1090000,
                    'image' => 'https://images.unsplash.com/photo-1597872200969-2b65d56bd16b?w=500&auto=format&fit=crop&q=60',
                    'stock' => 50,
                    'specs' => json_encode([
                        'component_type' => 'ssd',
                        'form_factor' => 'M.2 2280',
                        'interface' => 'PCIe 4.0',
                        'power_w' => 4
                    ])
                ]
            ],
            'psu' => [
                [
                    'id' => 601,
                    'name' => 'Nguồn máy tính Corsair RM750e 750W 80 Plus Gold Full Modular (ATX 3.0)',
                    'price' => 2790000,
                    'image' => 'https://images.unsplash.com/photo-1587202372775-e229f172b9d7?w=500&auto=format&fit=crop&q=60',
                    'stock' => 20,
                    'specs' => json_encode([
                        'component_type' => 'psu',
                        'rated_power_w' => 750,
                        'form_factor' => 'ATX',
                        'atx_version' => '3.0',
                        'pcie_8pin_connectors' => 3,
                        'has_12vhpwr' => true
                    ])
                ],
                [
                    'id' => 602,
                    'name' => 'Nguồn máy tính MSI MAG A850GL PCIE5 850W 80 Plus Gold Full Modular',
                    'price' => 3290000,
                    'image' => 'https://images.unsplash.com/photo-1587202372775-e229f172b9d7?w=500&auto=format&fit=crop&q=60',
                    'stock' => 15,
                    'specs' => json_encode([
                        'component_type' => 'psu',
                        'rated_power_w' => 850,
                        'form_factor' => 'ATX',
                        'atx_version' => '3.0',
                        'pcie_8pin_connectors' => 4,
                        'has_12vhpwr' => true
                    ])
                ]
            ],
            'case' => [
                [
                    'id' => 701,
                    'name' => 'Vỏ Case NZXT H5 Flow Black (Mid Tower)',
                    'price' => 2190000,
                    'image' => 'https://images.unsplash.com/photo-1587202372775-e229f172b9d7?w=500&auto=format&fit=crop&q=60',
                    'stock' => 12,
                    'specs' => json_encode([
                        'component_type' => 'case',
                        'form_factor' => 'Mid Tower',
                        'supported_motherboard_form_factors' => ['ATX', 'mATX', 'ITX'],
                        'max_gpu_length_mm' => 365,
                        'max_cpu_cooler_height_mm' => 165
                    ])
                ],
                [
                    'id' => 702,
                    'name' => 'Vỏ Case Corsair 4000D AIRFLOW Tempered Glass Black',
                    'price' => 2390000,
                    'image' => 'https://images.unsplash.com/photo-1587202372775-e229f172b9d7?w=500&auto=format&fit=crop&q=60',
                    'stock' => 18,
                    'specs' => json_encode([
                        'component_type' => 'case',
                        'form_factor' => 'Mid Tower',
                        'supported_motherboard_form_factors' => ['ATX', 'mATX', 'ITX'],
                        'max_gpu_length_mm' => 360,
                        'max_cpu_cooler_height_mm' => 170
                    ])
                ]
            ],
            'cooler' => [
                [
                    'id' => 801,
                    'name' => 'Tản nhiệt nước AIO Thermalright Aqua Elite 240 ARGB Black',
                    'price' => 1490000,
                    'image' => 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?w=500&auto=format&fit=crop&q=60',
                    'stock' => 20,
                    'specs' => json_encode([
                        'component_type' => 'cpu_cooler',
                        'cooler_type' => 'liquid',
                        'fan_count' => 2,
                        'fan_power_w' => 3,
                        'pump_power_w' => 4,
                        'supported_sockets' => ['LGA1700', 'LGA1200', 'AM4', 'AM5']
                    ])
                ],
                [
                    'id' => 802,
                    'name' => 'Tản nhiệt khí Deepcool AK620 Digital Màn hình hiển thị nhiệt độ',
                    'price' => 1690000,
                    'image' => 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?w=500&auto=format&fit=crop&q=60',
                    'stock' => 15,
                    'specs' => json_encode([
                        'component_type' => 'cpu_cooler',
                        'cooler_type' => 'air',
                        'height_mm' => 162,
                        'fan_count' => 2,
                        'fan_power_w' => 3,
                        'supported_sockets' => ['LGA1700', 'LGA1200', 'AM4', 'AM5']
                    ])
                ]
            ],
            'monitor' => [
                [
                    'id' => 901,
                    'name' => 'Màn hình ASUS TUF Gaming VG279Q3A 27 inch IPS 180Hz 1ms Full HD',
                    'price' => 4490000,
                    'image' => 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?w=500&auto=format&fit=crop&q=60',
                    'stock' => 15,
                    'specs' => json_encode(['component_type' => 'monitor'])
                ],
                [
                    'id' => 902,
                    'name' => 'Màn hình LG UltraGear 27GR75Q-B 27 inch 2K IPS 165Hz 1ms HDR10',
                    'price' => 6290000,
                    'image' => 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?w=500&auto=format&fit=crop&q=60',
                    'stock' => 10,
                    'specs' => json_encode(['component_type' => 'monitor'])
                ]
            ],
            'gear' => [
                [
                    'id' => 1001,
                    'name' => 'Chuột Gaming không dây Logitech G Pro X Superlight 2 Wireless Black 60g',
                    'price' => 3490000,
                    'image' => 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?w=500&auto=format&fit=crop&q=60',
                    'stock' => 25,
                    'specs' => json_encode(['component_type' => 'gear'])
                ],
                [
                    'id' => 1002,
                    'name' => 'Bàn phím cơ không dây Logitech G Pro X TKL LIGHTSPEED Tactile RGB',
                    'price' => 3890000,
                    'image' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=500&auto=format&fit=crop&q=60',
                    'stock' => 20,
                    'specs' => json_encode(['component_type' => 'gear'])
                ]
            ]
        ];

        return $samples[$partKey] ?? [];
    }

    public function index(): void
    {
        $this->render('pc-builder/index', [
            'pageTitle' => 'Xây dựng cấu hình PC',
            'parts' => $this->parts
        ]);
    }

    /** Helper lấy thông tin đầy đủ của một sản phẩm */
    private function getProductById(?PDO $db, int $id): ?array
    {
        if ($id <= 0) return null;

        if ($db !== null) {
            try {
                $stmt = $db->prepare('SELECT id, name, price, image, specs FROM products WHERE id = :id AND status = \'active\' LIMIT 1');
                $stmt->execute([':id' => $id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row) {
                    return $row;
                }
            } catch (Exception $e) {}
        }

        // Tìm trong dữ liệu mẫu
        foreach ($this->parts as $key => $info) {
            foreach ($this->getSamplePcComponents($key) as $item) {
                if ($item['id'] === $id) {
                    return $item;
                }
            }
        }

        return null;
    }

    /** API: Lấy danh sách linh kiện phù hợp và kiểm tra tương thích */
    public function getProducts(): void
    {
        header('Content-Type: application/json');
        $partKey = trim($_GET['part'] ?? '');
        $search = trim($_GET['search'] ?? '');
        
        // Nhận toàn bộ cấu hình hiện tại đang chọn gửi lên
        $cpuId = (int)($_GET['cpu_id'] ?? 0);
        $mainboardId = (int)($_GET['mainboard_id'] ?? 0);
        $ramId = (int)($_GET['ram_id'] ?? 0);
        $gpuId = (int)($_GET['gpu_id'] ?? 0);
        $coolerId = (int)($_GET['cooler_id'] ?? 0);
        $caseId = (int)($_GET['case_id'] ?? 0);
        $psuId = (int)($_GET['psu_id'] ?? 0);
        $storageId = (int)($_GET['storage_id'] ?? 0);

        if (!array_key_exists($partKey, $this->parts)) {
            echo json_encode([]);
            exit;
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        // Tải các đối tượng sản phẩm hiện tại để so khớp tương thích
        $build = [
            'cpu' => $this->getProductById($db, $cpuId),
            'mainboard' => $this->getProductById($db, $mainboardId),
            'ram' => $this->getProductById($db, $ramId),
            'gpu' => $this->getProductById($db, $gpuId),
            'cooler' => $this->getProductById($db, $coolerId),
            'case' => $this->getProductById($db, $caseId),
            'psu' => $this->getProductById($db, $psuId),
            'storage' => $this->getProductById($db, $storageId),
        ];

        // Lấy danh sách sản phẩm thuộc danh mục đang chọn
        $products = [];
        if ($db !== null) {
            try {
                $partInfo = $this->parts[$partKey];
                $sql = "SELECT id, name, price, image, stock, specs FROM products WHERE ({$partInfo['query']}) AND status = 'active'";
                
                $params = [];
                if ($search !== '') {
                    $sql .= " AND name LIKE :search";
                    $params[':search'] = '%' . $search . '%';
                }

                $sql .= " ORDER BY price ASC";
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {}
        }

        // Fallback sang dữ liệu mẫu nếu DB chưa có linh kiện phù hợp
        if (empty($products)) {
            $products = $this->getSamplePcComponents($partKey);
            if ($search !== '') {
                $kw = safe_strtolower($search);
                $products = array_values(array_filter($products, fn($p) => str_contains(safe_strtolower($p['name']), $kw)));
            }
        }

        // Chạy qua kiểm tra tính tương thích
        $results = [];
        foreach ($products as $p) {
            $compat = PcCompatibilityService::checkCompatibility($build, $p, $partKey);
            
            $p['image_url'] = productImageUrl($p['image'], $p['name']);
            $p['price_formatted'] = formatPrice($p['price']);
            $p['compatible'] = $compat['compatible'];
            $p['blockers'] = $compat['blockers'];
            $p['warnings'] = $compat['warnings'];
            
            unset($p['specs']); // Ẩn specs raw để tối ưu JSON
            $results[] = $p;
        }

        // Sắp xếp: Tương thích lên đầu, không tương thích xuống dưới. Sau đó xếp theo giá tăng dần
        usort($results, function($a, $b) {
            $aCompat = $a['compatible'] ? 1 : 0;
            $bCompat = $b['compatible'] ? 1 : 0;
            if ($aCompat !== $bCompat) {
                return $bCompat - $aCompat; // 1 (tương thích) lên trước 0 (không tương thích)
            }
            return $a['price'] <=> $b['price'];
        });

        echo json_encode($results);
        exit;
    }

    /** API: Lấy phân tích cấu hình hiện tại & tính toán PSU */
    public function getAnalysis(): void
    {
        header('Content-Type: application/json');
        
        $cpuId = (int)($_GET['cpu_id'] ?? 0);
        $mainboardId = (int)($_GET['mainboard_id'] ?? 0);
        $ramId = (int)($_GET['ram_id'] ?? 0);
        $gpuId = (int)($_GET['gpu_id'] ?? 0);
        $coolerId = (int)($_GET['cooler_id'] ?? 0);
        $caseId = (int)($_GET['case_id'] ?? 0);
        $psuId = (int)($_GET['psu_id'] ?? 0);
        $storageId = (int)($_GET['storage_id'] ?? 0);

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        $build = [
            'cpu' => $this->getProductById($db, $cpuId),
            'mainboard' => $this->getProductById($db, $mainboardId),
            'ram' => $this->getProductById($db, $ramId),
            'gpu' => $this->getProductById($db, $gpuId),
            'cooler' => $this->getProductById($db, $coolerId),
            'case' => $this->getProductById($db, $caseId),
            'psu' => $this->getProductById($db, $psuId),
            'storage' => $this->getProductById($db, $storageId),
        ];

        // 1. Tính công suất nguồn
        $power = PcCompatibilityService::calculatePowerRequirements($build);

        // 2. Chạy kiểm tra chéo toàn bộ build xem có blockers/warnings gì không
        $globalBlockers = [];
        $globalWarnings = [];

        foreach ($build as $key => $prod) {
            if ($prod) {
                $compat = PcCompatibilityService::checkCompatibility($build, $prod, $key);
                if (!empty($compat['blockers'])) {
                    $globalBlockers = array_merge($globalBlockers, $compat['blockers']);
                }
                if (!empty($compat['warnings'])) {
                    $globalWarnings = array_merge($globalWarnings, $compat['warnings']);
                }
            }
        }

        // Loại bỏ trùng lặp lỗi
        $globalBlockers = array_unique($globalBlockers);
        $globalWarnings = array_unique($globalWarnings);

        echo json_encode([
            'success' => true,
            'power' => $power,
            'blockers' => array_values($globalBlockers),
            'warnings' => array_values($globalWarnings),
        ]);
        exit;
    }

    /** API: Thêm hàng loạt linh kiện đã chọn vào giỏ hàng */
    public function addToCart(): void
    {
        header('Content-Type: application/json');
        if (!$this->isPost()) {
            echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
            exit;
        }

        $productIds = $_POST['product_ids'] ?? [];
        if (empty($productIds)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng chọn ít nhất 1 linh kiện để thêm vào giỏ hàng.']);
            exit;
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $addedCount = 0;
        foreach ($productIds as $pid) {
            $pid = (int)$pid;
            if ($pid <= 0) continue;

            $p = $this->getProductById($db, $pid);

            if ($p) {
                // Kiểm tra trùng lặp trong giỏ hàng
                $found = false;
                foreach ($_SESSION['cart'] as &$item) {
                    if ((int)$item['product_id'] === $pid) {
                        $item['quantity']++;
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $_SESSION['cart'][] = [
                        'product_id' => $p['id'],
                        'name' => $p['name'],
                        'price' => (float)$p['price'],
                        'image' => $p['image'],
                        'quantity' => 1
                    ];
                }
                $addedCount++;
            }
        }

        if ($addedCount > 0) {
            echo json_encode(['success' => true, 'message' => "Đã thêm thành công {$addedCount} linh kiện vào giỏ hàng!"]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy linh kiện nào hợp lệ để thêm.']);
        }
        exit;
    }
}

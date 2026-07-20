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
            'query' => "category_id = 5"
        ],
        'gear' => [
            'name' => 'Gaming Gear (Phím/Chuột/Tai nghe)',
            'icon' => 'fa-solid fa-keyboard',
            'query' => "category_id = 7"
        ]
    ];

    public function index(): void
    {
        $this->render('pc-builder/index', [
            'pageTitle' => 'Xây dựng cấu hình PC',
            'parts' => $this->parts
        ]);
    }

    /** Helper lấy thông tin đầy đủ của một sản phẩm */
    private function getProductById(PDO $db, int $id): ?array
    {
        if ($id <= 0) return null;
        $stmt = $db->prepare('SELECT id, name, price, image, specs FROM products WHERE id = :id AND status = \'active\' LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
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
        if (!$db) {
            echo json_encode([]);
            exit;
        }

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
        $partInfo = $this->parts[$partKey];
        $sql = "SELECT id, name, price, image, stock, specs FROM products WHERE {$partInfo['query']} AND status = 'active'";
        
        $params = [];
        if ($search !== '') {
            $sql .= " AND name LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY price ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Chạy qua kiểm tra tính tương thích
        $results = [];
        foreach ($products as $p) {
            $compat = PcCompatibilityService::checkCompatibility($build, $p, $partKey);
            
            $p['image_url'] = productImageUrl($p['image']);
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
        if (!$db) {
            echo json_encode(['success' => false, 'message' => 'Lỗi kết nối database']);
            exit;
        }

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
        if (!$db) {
            echo json_encode(['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu.']);
            exit;
        }

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

            $stmt = $db->prepare('SELECT id, name, price, image, stock FROM products WHERE id = :id AND status = \'active\' LIMIT 1');
            $stmt->execute([':id' => $pid]);
            $p = $stmt->fetch(PDO::FETCH_ASSOC);

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

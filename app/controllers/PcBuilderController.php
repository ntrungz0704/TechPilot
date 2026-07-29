<?php
/**
 * Controller Xây dựng cấu hình PC ở Storefront
 */
require_once ROOT_PATH . '/app/core/helpers.php';
require_once ROOT_PATH . '/app/services/PcCompatibilityService.php';

class PcBuilderController extends Controller
{
    // Cấu hình các bộ phận cần build và query tương ứng trong DB (dùng category_id)
    private array $parts = [
        'cpu' => [
            'name' => 'Bộ vi xử lý (CPU)',
            'icon' => 'fa-solid fa-microchip',
            'query' => "category_id = 5"
        ],
        'mainboard' => [
            'name' => 'Bo mạch chủ (Mainboard)',
            'icon' => 'fa-solid fa-clone', 
            'query' => "category_id = 4"
        ],
        'ram' => [
            'name' => 'Bộ nhớ trong (RAM)',
            'icon' => 'fa-solid fa-server',
            'query' => "category_id = 7"
        ],
        'vga' => [
            'name' => 'Card màn hình (VGA)',
            'icon' => 'fa-solid fa-sd-card',
            'query' => "category_id = 6"
        ],
        'storage' => [
            'name' => 'Ổ cứng (SSD/HDD)',
            'icon' => 'fa-solid fa-database',
            'query' => "category_id = 8"
        ],
        'psu' => [
            'name' => 'Nguồn máy tính (PSU)',
            'icon' => 'fa-solid fa-plug',
            'query' => "category_id = 11"
        ],
        'case' => [
            'name' => 'Vỏ máy tính (Case)',
            'icon' => 'fa-solid fa-box',
            'query' => "category_id = 9"
        ],
        'cooler' => [
            'name' => 'Tản nhiệt PC',
            'icon' => 'fa-solid fa-fan',
            'query' => "category_id = 10"
        ],
        'monitor' => [
            'name' => 'Màn hình',
            'icon' => 'fa-solid fa-tv',
            'query' => "category_id = 3"
        ],
        'gear' => [
            'name' => 'Gaming Gear',
            'icon' => 'fa-solid fa-keyboard',
            'query' => "category_id IN (12, 13)"
        ]
    ];

    public function index(): void
    {
        $this->render('pc-builder/index', [
            'pageTitle' => 'Xây dựng cấu hình PC - TechPilot',
            'parts' => $this->parts,
        ]);
    }

    /** API: Trả về danh sách linh kiện */
    public function getProducts(): void
    {
        header('Content-Type: application/json');
        $partKey = $_GET['part'] ?? '';
        
        if (!isset($this->parts[$partKey])) {
            echo json_encode(['success' => false, 'message' => 'Linh kiện không hợp lệ']);
            exit;
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        $whereClause = $this->parts[$partKey]['query'];
        $search = trim($_GET['search'] ?? '');
        if ($search) {
            $whereClause .= " AND name LIKE :search";
        }
        
        $sql = "SELECT id, name, price, stock, image, specs, component_type, power_draw_w, recommended_psu_w FROM products WHERE ($whereClause) AND status = 'active' AND stock > 0 ORDER BY price ASC";
        $stmt = $db->prepare($sql);
        if ($search) {
            $stmt->execute([':search' => '%' . $search . '%']);
        } else {
            $stmt->execute();
        }
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Xây dựng lại mảng $build từ GET
        $build = [];
        foreach ($this->parts as $k => $info) {
            $idParam = $_GET[$k . '_id'] ?? 0;
            if ($idParam > 0) {
                $p = $this->getProductById($db, (int)$idParam);
                if ($p) {
                    $build[$k] = [
                        'id' => $p['id'],
                        'name' => $p['name'],
                        'specs' => json_decode($p['specs'], true)
                    ];
                }
            }
        }

        // Format data to match frontend expectations
        $formattedProducts = [];
        foreach ($products as $p) {
            $p['specs'] = $p['specs'] ?: '{}';
            $parsed = json_decode($p['specs'], true) ?: [];
            if (!empty($p['component_type'])) $parsed['component_type'] = $p['component_type'];
            if (!empty($p['power_draw_w'])) $parsed['power_draw_w'] = $p['power_draw_w'];
            if (!empty($p['recommended_psu_w'])) $parsed['recommended_psu_w'] = $p['recommended_psu_w'];
            $p['specs'] = json_encode($parsed);

            $compat = PcCompatibilityService::checkCompatibility($build, $p, $partKey);

            $formattedProducts[] = [
                'id' => (int)$p['id'],
                'name' => $p['name'],
                'price' => (float)$p['price'],
                'price_formatted' => formatPrice((float)$p['price']),
                'stock' => (int)$p['stock'],
                'image_url' => empty($p['image']) ? '/assets/images/placeholder.jpg' : (str_starts_with($p['image'], 'http') ? $p['image'] : (str_starts_with($p['image'], 'assets/') ? '/' . $p['image'] : '/assets/images/products/' . $p['image'])),
                'specs' => $p['specs'],
                'compatible' => empty($compat['blockers']),
                'blockers' => $compat['blockers'],
                'warnings' => $compat['warnings']
            ];
        }

        echo json_encode(['success' => true, 'data' => $formattedProducts]);
        exit;
    }

    /** Lấy một sản phẩm từ DB */
    private function getProductById(?PDO $db, int $id): ?array
    {
        if (!$db) return null;
        $stmt = $db->prepare("SELECT id, name, price, stock, image, specs, component_type, power_draw_w, recommended_psu_w FROM products WHERE id = :id AND status = 'active'");
        $stmt->execute([':id' => $id]);
        $prod = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($prod) {
            $prod['specs'] = $prod['specs'] ?: '{}';
            // Ensure frontend receives parsed specs
            $parsed = json_decode($prod['specs'], true) ?: [];
            if (!empty($prod['component_type'])) {
                $parsed['component_type'] = $prod['component_type'];
            }
            if (!empty($prod['power_draw_w'])) {
                $parsed['power_draw_w'] = $prod['power_draw_w'];
            }
            if (!empty($prod['recommended_psu_w'])) {
                $parsed['recommended_psu_w'] = $prod['recommended_psu_w'];
            }
            $prod['specs'] = json_encode($parsed);
        }

        return $prod ?: null;
    }

    /** API: Lấy phân tích tương thích và công suất */
    public function getAnalysis(): void
    {
        header('Content-Type: application/json');
        if (!$this->isPost()) {
            echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }
        
        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        $build = [];
        foreach ($this->parts as $key => $info) {
            if (!empty($input[$key])) {
                $prod = $this->getProductById($db, (int)$input[$key]);
                if ($prod) {
                    $build[$key] = [
                        'id' => $prod['id'],
                        'name' => $prod['name'],
                        'price' => $prod['price'],
                        'specs' => json_decode($prod['specs'], true)
                    ];
                }
            }
        }

        $power = PcCompatibilityService::calculatePowerRequirements($build);
        $globalBlockers = [];
        $globalWarnings = [];

        if ($power['is_selected_psu_sufficient'] === false) {
            $globalBlockers[] = "Nguồn máy tính đang chọn ({$power['selected_psu_w']}W) không đủ công suất khuyến nghị ({$power['recommended_psu_w']}W) cho cấu hình này.";
        }

        if ($power['data_quality'] === 'insufficient' && !empty($power['missing_power_fields'])) {
            $globalWarnings[] = "Thiếu dữ liệu công suất của linh kiện: " . implode(', ', $power['missing_power_fields']);
        }

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

    private function getOrCreateCartId(int $userId, PDO $db): int
    {
        $stmt = $db->prepare("SELECT id FROM carts WHERE user_id = :user_id AND status = 'active' LIMIT 1");
        $stmt->execute([':user_id' => $userId]);
        $cart = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($cart) {
            return (int)$cart['id'];
        }

        $stmt = $db->prepare("INSERT INTO carts (user_id, status) VALUES (:user_id, 'active')");
        $stmt->execute([':user_id' => $userId]);
        return (int)$db->lastInsertId();
    }

    /** API: Thêm hàng loạt linh kiện đã chọn vào giỏ hàng */
    public function addToCart(): void
    {
        header('Content-Type: application/json');
        if (!$this->isPost()) {
            echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
            exit;
        }

        $user = currentUser();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để thêm vào giỏ hàng.', 'redirect' => '/auth/login?redirect=/build-pc']);
            exit;
        }

        $productIds = $_POST['product_ids'] ?? [];
        if (empty($productIds)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng chọn ít nhất 1 linh kiện để thêm vào giỏ hàng.']);
            exit;
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        // 1. Kiểm tra 6 món cốt lõi
        $build = [];
        $coreKeys = ['cpu', 'mainboard', 'ram', 'storage', 'psu', 'case'];
        $hasCore = true;
        
        $categoryMap = [
            'cpu' => [5],
            'mainboard' => [4],
            'vga' => [6],
            'ram' => [7],
            'storage' => [8],
            'case' => [9],
            'cooler' => [10],
            'psu' => [11],
            'monitor' => [3],
            'gear' => [12, 13]
        ];

        foreach ($this->parts as $key => $info) {
            $foundId = null;
            foreach ($productIds as $pid) {
                $p = $this->getProductById($db, (int)$pid);
                if ($p) {
                    $specs = json_decode($p['specs'], true) ?: [];
                    $compType = $specs['component_type'] ?? '';
                    $catId = (int)($p['category_id'] ?? 0);
                    $allowedCats = $categoryMap[$key] ?? [];

                    if ($compType === $key || in_array($catId, $allowedCats, true)) {
                        $foundId = $p['id'];
                        $build[$key] = [
                            'id' => $p['id'],
                            'name' => $p['name'],
                            'specs' => $specs
                        ];
                        break;
                    }
                }
            }
            if (in_array($key, $coreKeys, true) && !$foundId) {
                $hasCore = false;
            }
        }

        if (!$hasCore) {
            echo json_encode(['success' => false, 'message' => 'Bạn phải chọn đầy đủ các linh kiện cơ bản (CPU, Mainboard, RAM, Ổ cứng, Nguồn, Case) trước khi thêm vào giỏ hàng.']);
            exit;
        }

        // 2. Validate GPU vs iGPU
        if (empty($build['vga'])) {
            $cpuSpecs = $build['cpu']['specs'] ?? [];
            if (!isset($cpuSpecs['integrated_graphics']) || !$cpuSpecs['integrated_graphics']) {
                echo json_encode(['success' => false, 'message' => 'Cấu hình thiếu Card màn hình, mà CPU bạn chọn lại không có đồ họa tích hợp (iGPU). Vui lòng chọn thêm VGA hoặc đổi CPU.']);
                exit;
            }
        }

        // 3. Re-validate tính tương thích
        $globalBlockers = [];
        foreach ($build as $key => $prod) {
            $compat = PcCompatibilityService::checkCompatibility($build, $prod, $key);
            if (!empty($compat['blockers'])) {
                $globalBlockers = array_merge($globalBlockers, $compat['blockers']);
            }
        }

        if (!empty($globalBlockers)) {
            echo json_encode(['success' => false, 'message' => 'Cấu hình của bạn có lỗi tương thích. Vui lòng khắc phục trước khi thêm vào giỏ hàng: ' . implode(', ', array_unique($globalBlockers))]);
            exit;
        }

        // 4. Insert DB
        try {
            $db->beginTransaction();
            $cartId = $this->getOrCreateCartId((int)$user['id'], $db);

            $addedCount = 0;
            foreach ($productIds as $pid) {
                $pid = (int)$pid;
                if ($pid <= 0) continue;

                // Check if already in cart
                $stmt = $db->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = :cart_id AND product_id = :product_id");
                $stmt->execute([':cart_id' => $cartId, ':product_id' => $pid]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($existing) {
                    $stmtUpdate = $db->prepare("UPDATE cart_items SET quantity = quantity + 1 WHERE id = :id");
                    $stmtUpdate->execute([':id' => $existing['id']]);
                } else {
                    $stmtInsert = $db->prepare("INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (:cart_id, :product_id, 1)");
                    $stmtInsert->execute([':cart_id' => $cartId, ':product_id' => $pid]);
                }
                $addedCount++;
            }
            $db->commit();
            
            echo json_encode(['success' => true, 'message' => "Đã thêm thành công {$addedCount} linh kiện vào giỏ hàng!", 'redirect' => '/cart']);
        } catch (Exception $e) {
            $db->rollBack();
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
        exit;
    }
}

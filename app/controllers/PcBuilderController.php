<?php
/**
 * Controller Xây dựng cấu hình PC ở Storefront
 */
require_once ROOT_PATH . '/app/core/helpers.php';

class PcBuilderController extends Controller
{
    // Cấu hình các bộ phận cần build và query tương ứng trong DB
    private array $parts = [
        'cpu' => [
            'name' => 'Bộ vi xử lý (CPU)',
            'icon' => 'fa-solid fa-microchip',
            'query' => "category_id = 4 AND (name LIKE '%CPU%' OR name LIKE '%Intel Core%' OR name LIKE '%Ryzen%')"
        ],
        'mainboard' => [
            'name' => 'Bo mạch chủ (Mainboard)',
            'icon' => 'fa-solid fa-clone', 
            'query' => "category_id = 4 AND (name LIKE '%Mainboard%' OR name LIKE '%Main board%' OR name LIKE '%ASUS TUF%')"
        ],
        'ram' => [
            'name' => 'Bộ nhớ trong (RAM)',
            'icon' => 'fa-solid fa-server',
            'query' => "category_id = 4 AND (name LIKE '%RAM%' OR name LIKE '%Vengeance%' OR name LIKE '%Fury%')"
        ],
        'vga' => [
            'name' => 'Card màn hình (VGA)',
            'icon' => 'fa-solid fa-sd-card',
            'query' => "(category_id = 4 OR category_id = 7) AND (name LIKE '%RTX%' OR name LIKE '%GTX%' OR name LIKE '%Radeon%' OR name LIKE '%VGA%')"
        ],
        'storage' => [
            'name' => 'Ổ cứng (SSD/HDD)',
            'icon' => 'fa-solid fa-database',
            'query' => "category_id = 4 AND (name LIKE '%SSD%' OR name LIKE '%Ổ cứng%' OR name LIKE '%NVMe%' OR name LIKE '%Kingston NV2%')"
        ],
        'psu' => [
            'name' => 'Nguồn máy tính (PSU)',
            'icon' => 'fa-solid fa-plug',
            'query' => "category_id = 4 AND (name LIKE '%Nguồn%' OR name LIKE '%PSU%' OR name LIKE '%Corsair CV650%')"
        ],
        'case' => [
            'name' => 'Vỏ máy tính (Case)',
            'icon' => 'fa-solid fa-box',
            'query' => "category_id = 4 AND (name LIKE '%Case%' OR name LIKE '%Vỏ%')"
        ],
        'cooler' => [
            'name' => 'Tản nhiệt PC',
            'icon' => 'fa-solid fa-fan',
            'query' => "category_id = 4 AND (name LIKE '%Tản%' OR name LIKE '%Cooler%')"
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

    /** API: Lấy danh sách linh kiện phù hợp theo danh mục */
    public function getProducts(): void
    {
        header('Content-Type: application/json');
        $partKey = trim($_GET['part'] ?? '');
        $search = trim($_GET['search'] ?? '');

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

        $partInfo = $this->parts[$partKey];
        $sql = "SELECT id, name, price, image, stock FROM products WHERE {$partInfo['query']} AND status = 'active'";
        
        $params = [];
        if ($search !== '') {
            $sql .= " AND name LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY price ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Chuẩn hóa đường dẫn hình ảnh và định dạng tiền tệ
        foreach ($products as &$p) {
            $p['image_url'] = productImageUrl($p['image']);
            $p['price_formatted'] = formatPrice($p['price']);
        }

        echo json_encode($products);
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

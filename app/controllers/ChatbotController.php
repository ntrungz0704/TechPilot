<?php
require_once ROOT_PATH . '/app/core/helpers.php';
require_once ROOT_PATH . '/config/database.php';

class ChatbotController extends Controller
{
    private ?PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * API: Trả về danh sách tất cả Laptop hoạt động để hiển thị trong các dropdown chọn so sánh
     */
    public function products(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if ($this->db === null) {
            echo json_encode(['success' => false, 'message' => 'Lỗi kết nối database']);
            exit;
        }

        try {
            // Lấy tất cả sản phẩm thuộc danh mục Laptop Gaming (1) và Laptop Văn Phòng (2)
            $stmt = $this->db->prepare(
                "SELECT id, name, price, image, specs 
                 FROM products 
                 WHERE category_id IN (1, 2) AND status = 'active'
                 ORDER BY name ASC"
            );
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Parse specs
            foreach ($products as &$p) {
                $p['price_formatted'] = number_format($p['price'], 0, ',', '.') . 'đ';
                $p['specs_decoded'] = json_decode($p['specs'], true) ?? [];
            }

            echo json_encode(['success' => true, 'data' => $products]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * API: So sánh hai sản phẩm dựa trên ID
     */
    public function compare(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if ($this->db === null) {
            echo json_encode(['success' => false, 'message' => 'Lỗi kết nối database']);
            exit;
        }

        $leftId = (int)($_GET['left_id'] ?? 0);
        $rightId = (int)($_GET['right_id'] ?? 0);
        $userGroup = trim($_GET['group'] ?? '');

        if ($leftId === 0 || $rightId === 0) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng chọn đầy đủ 2 sản phẩm để so sánh']);
            exit;
        }

        try {
            $stmt = $this->db->prepare(
                "SELECT p.*, b.name as brand_name, c.name as category_name 
                 FROM products p
                 LEFT JOIN brands b ON p.brand_id = b.id
                 LEFT JOIN categories c ON p.category_id = c.id
                 WHERE p.id IN (?, ?) AND p.status = 'active'"
            );
            $stmt->execute([$leftId, $rightId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($rows) < 2) {
                // Thử tìm xem có cái nào không hoạt động hoặc không tồn tại
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin sản phẩm để so sánh']);
                exit;
            }

            // Gán đúng vị trí left/right
            $leftProduct = null;
            $rightProduct = null;
            foreach ($rows as $row) {
                if ((int)$row['id'] === $leftId) {
                    $leftProduct = $row;
                } else {
                    $rightProduct = $row;
                }
            }

            // Trường hợp trùng ID
            if ($leftId === $rightId) {
                $leftProduct = $rows[0];
                $rightProduct = $rows[0];
            }

            // Decode specs JSON
            $leftSpecs = json_decode($leftProduct['specs'], true) ?? [];
            $rightSpecs = json_decode($rightProduct['specs'], true) ?? [];

            // Helper chấm điểm sao ước tính cho game, office, đồ họa dựa trên cấu hình
            $getScores = function(array $specs, float $price) {
                $cpu = strtolower($specs['CPU'] ?? $specs['cpu'] ?? '');
                $ram = (int)filter_var($specs['RAM'] ?? $specs['ram'] ?? '8', FILTER_SANITIZE_NUMBER_INT);
                $vga = strtolower($specs['VGA'] ?? $specs['vga'] ?? '');
                $isGamingVga = (strpos($vga, 'rtx') !== false || strpos($vga, 'gtx') !== false || strpos($vga, 'radeon rx') !== false);

                // Office score (hầu hết đều tốt, RAM cao thì đa nhiệm tốt hơn)
                $office = 3;
                if ($ram >= 8) $office = 4;
                if ($ram >= 16) $office = 5;

                // Gaming score
                $game = 1;
                if ($isGamingVga) {
                    $game = 4;
                    if (strpos($vga, '4060') !== false || strpos($vga, '4070') !== false || strpos($vga, '4080') !== false) {
                        $game = 5;
                    }
                } elseif (strpos($cpu, 'i5') !== false || strpos($cpu, 'ryzen 5') !== false) {
                    $game = 3; // Integrated but decent
                } elseif (strpos($cpu, 'i3') !== false) {
                    $game = 2;
                }

                // Graphic score
                $graphic = 1;
                if ($ram >= 16) {
                    $graphic = 3;
                    if ($isGamingVga) $graphic = 5;
                } elseif ($ram >= 8 && (strpos($cpu, 'i5') !== false || strpos($cpu, 'i7') !== false)) {
                    $graphic = 3;
                }

                return ['game' => $game, 'office' => $office, 'graphic' => $graphic];
            };

            $leftMetrics = $getScores($leftSpecs, (float)$leftProduct['price']);
            $rightMetrics = $getScores($rightSpecs, (float)$rightProduct['price']);

            // Phân tích và đưa ra lời khuyên cá nhân hóa
            $advice = [];
            $groupLower = strtolower($userGroup);

            // CPU & RAM Text
            $lCpu = $leftSpecs['CPU'] ?? 'N/A';
            $rCpu = $rightSpecs['CPU'] ?? 'N/A';
            $lRam = $leftSpecs['RAM'] ?? '8GB';
            $rRam = $rightSpecs['RAM'] ?? '8GB';

            if ($groupLower === 'student' || $groupLower === 'sinh viên') {
                $advice[] = "👉 **Dành cho Sinh viên**: " . ($leftProduct['price'] < $rightProduct['price'] 
                    ? "**" . $leftProduct['name'] . "** là lựa chọn tiết kiệm chi phí hơn (" . number_format($leftProduct['price'], 0, ',', '.') . "đ) giúp bạn tối ưu ngân sách học tập." 
                    : "**" . $rightProduct['name'] . "** kinh tế hơn hẳn, đáp ứng trọn vẹn các tác vụ soạn thảo, lướt web.");
            } elseif ($groupLower === 'designer' || $groupLower === 'đồ họa' || $groupLower === 'game thủ' || $groupLower === 'gamer') {
                $leftGameScore = $leftMetrics['game'] + $leftMetrics['graphic'];
                $rightGameScore = $rightMetrics['game'] + $rightMetrics['graphic'];

                if ($leftGameScore > $rightGameScore) {
                    $advice[] = "👉 **Dành cho Đồ họa & Game**: **" . $leftProduct['name'] . "** vượt trội hơn hẳn nhờ cấu hình CPU " . $lCpu . " + VGA " . ($leftSpecs['VGA'] ?? 'Onboard') . " giúp chiến game nặng và render mượt mà.";
                } elseif ($rightGameScore > $leftGameScore) {
                    $advice[] = "👉 **Dành cho Đồ họa & Game**: **" . $rightProduct['name'] . "** mạnh mẽ hơn với GPU " . ($rightSpecs['VGA'] ?? 'Onboard') . " chuyên dụng, tối ưu khung hình FPS.";
                } else {
                    $advice[] = "👉 **Dành cho Đồ họa & Game**: Cả hai chiếc máy đều có hiệu năng tương đồng. Bạn hãy chọn theo sở thích thương hiệu hoặc thiết kế mỏng nhẹ.";
                }
            } else {
                // Lời khuyên chung
                $advice[] = "👉 **Lời khuyên**: Nếu bạn cần một chiếc máy mỏng nhẹ, pin khỏe để làm việc văn phòng thông thường thì nên ưu tiên dòng máy có giá tốt hơn. Nếu bạn chạy các phần mềm nặng hoặc đa nhiệm cao, chiếc máy có RAM " . ($lRam > $rRam ? $lRam : $rRam) . " sẽ đem lại trải nghiệm mượt mà lâu dài.";
            }

            // Gửi dữ liệu so sánh cấu trúc
            $comparisonData = [
                'left' => [
                    'id' => $leftProduct['id'],
                    'name' => $leftProduct['name'],
                    'price' => number_format($leftProduct['price'], 0, ',', '.') . 'đ',
                    'image' => $leftProduct['image'],
                    'slug' => $leftProduct['slug'],
                    'specs' => [
                        'CPU' => $lCpu,
                        'RAM' => $lRam,
                        'SSD' => $leftSpecs['SSD'] ?? '512GB NVMe',
                        'VGA' => $leftSpecs['VGA'] ?? 'Intel/AMD Graphics',
                        'Screen' => $leftSpecs['Màn hình'] ?? '15.6 inch'
                    ],
                    'ratings' => $leftMetrics
                ],
                'right' => [
                    'id' => $rightProduct['id'],
                    'name' => $rightProduct['name'],
                    'price' => number_format($rightProduct['price'], 0, ',', '.') . 'đ',
                    'image' => $rightProduct['image'],
                    'slug' => $rightProduct['slug'],
                    'specs' => [
                        'CPU' => $rCpu,
                        'RAM' => $rRam,
                        'SSD' => $rightSpecs['SSD'] ?? '512GB NVMe',
                        'VGA' => $rightSpecs['VGA'] ?? 'Intel/AMD Graphics',
                        'Screen' => $rightSpecs['Màn hình'] ?? '15.6 inch'
                    ],
                    'ratings' => $rightMetrics
                ],
                'advice' => $advice
            ];

            echo json_encode(['success' => true, 'data' => $comparisonData]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * API: Nhận câu hỏi tự nhiên hoặc profile khảo sát để tư vấn + giải thích
     */
    public function query(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if ($this->db === null) {
            echo json_encode(['success' => false, 'message' => 'Lỗi kết nối database']);
            exit;
        }

        $queryText = trim($_GET['q'] ?? '');
        $userGroup = trim($_GET['group'] ?? '');
        $userBudgetStr = trim($_GET['budget'] ?? '');
        $userPriority = trim($_GET['priority'] ?? '');

        // Trích xuất ngân sách tối đa
        $maxBudget = 999000000;
        if ($userBudgetStr === 'under_5m') $maxBudget = 5000000;
        elseif ($userBudgetStr === '5_10m') $maxBudget = 10000000;
        elseif ($userBudgetStr === '10_20m') $maxBudget = 20000000;
        elseif ($userBudgetStr === 'over_20m') $maxBudget = 999000000;
        elseif (is_numeric($userBudgetStr)) $maxBudget = (float)$userBudgetStr;

        // Nếu người dùng gửi tin nhắn trò chuyện tự nhiên
        if ($queryText !== '') {
            $response = $this->handleNaturalLanguage($queryText);
            if ($response !== null) {
                echo json_encode($response);
                exit;
            }
        }

        // Chạy bộ máy chấm điểm tư vấn (Recommendation Engine)
        try {
            $stmt = $this->db->prepare(
                "SELECT p.*, b.name as brand_name, c.name as category_name 
                 FROM products p
                 LEFT JOIN brands b ON p.brand_id = b.id
                 LEFT JOIN categories c ON p.category_id = c.id
                 WHERE p.category_id IN (1, 2) AND p.status = 'active'"
            );
            $stmt->execute();
            $laptops = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $scoredLaptops = [];
            foreach ($laptops as $laptop) {
                $specs = json_decode($laptop['specs'], true) ?? [];
                $price = (float)$laptop['price'];
                
                $score = 100.0;
                $reasons = [];

                // 1. Kiểm tra ngân sách
                if ($price > $maxBudget) {
                    $excessPercent = ($price - $maxBudget) / $maxBudget;
                    if ($excessPercent <= 0.1) {
                        $score -= 15;
                        $reasons[] = "Hơi vượt ngân sách đề xuất của bạn một chút (~10%)";
                    } elseif ($excessPercent <= 0.25) {
                        $score -= 35;
                        $reasons[] = "Vượt ngân sách đề xuất của bạn (~20%)";
                    } else {
                        $score -= 75; // Bỏ qua
                        continue; 
                    }
                } else {
                    // Tiết kiệm được bao nhiêu tiền
                    $saved = $maxBudget - $price;
                    if ($saved > 3000000 && $maxBudget < 999000000) {
                        $reasons[] = "Tiết kiệm cho bạn " . number_format($saved, 0, ',', '.') . "đ so với hạn mức tối đa.";
                    }
                }

                // 2. Phân tích đối tượng người dùng (Group)
                $groupLower = strtolower($userGroup);
                $cpu = strtolower($specs['CPU'] ?? $specs['cpu'] ?? '');
                $ram = (int)filter_var($specs['RAM'] ?? $specs['ram'] ?? '8', FILTER_SANITIZE_NUMBER_INT);
                $vga = strtolower($specs['VGA'] ?? $specs['vga'] ?? '');
                $isGamingVga = (strpos($vga, 'rtx') !== false || strpos($vga, 'gtx') !== false || strpos($vga, 'radeon rx') !== false);

                if ($groupLower === 'student' || $groupLower === 'sinh viên') {
                    if ($price <= 15000000) {
                        $score += 15;
                    }
                    if ($ram >= 16) {
                        $score += 5;
                    }
                    if (!$isGamingVga && $laptop['category_id'] == 2) {
                        $score += 10;
                        $reasons[] = "Dòng máy văn phòng mỏng nhẹ, pin tốt, rất tiện mang lên giảng đường.";
                    }
                } elseif ($groupLower === 'gamer' || $groupLower === 'game thủ') {
                    if ($isGamingVga) {
                        $score += 25;
                        $reasons[] = "Trang bị card đồ họa rời chuyên dụng để chiến game mượt mà.";
                    } else {
                        $score -= 30;
                    }
                    if ($laptop['category_id'] == 1) { // Laptop Gaming
                        $score += 15;
                    }
                } elseif ($groupLower === 'designer' || $groupLower === 'đồ họa') {
                    if ($ram >= 16) {
                        $score += 15;
                        $reasons[] = "Có sẵn 16GB RAM giúp xử lý mượt mà các layer đồ họa nặng Photoshop/Illustrator.";
                    } else {
                        $score -= 10;
                    }
                    if ($isGamingVga) {
                        $score += 15;
                        $reasons[] = "Card đồ họa mạnh mẽ giúp tối ưu hóa render video Premiere/After Effects.";
                    }
                    if (strpos($cpu, 'i7') !== false || strpos($cpu, 'ryzen 7') !== false || strpos($cpu, 'i9') !== false) {
                        $score += 10;
                    }
                } elseif ($groupLower === 'coder' || $groupLower === 'lập trình viên') {
                    if ($ram >= 16) {
                        $score += 25;
                        $reasons[] = "Dung lượng RAM 16GB lý tưởng để chạy máy ảo Docker, Android Studio và đa nhiệm IDE.";
                    } else {
                        $score -= 15;
                        $reasons[] = "RAM 8GB hơi ít để lập trình lâu dài, khuyến nghị nâng cấp.";
                    }
                    if (strpos($cpu, 'i5') !== false || strpos($cpu, 'i7') !== false || strpos($cpu, 'ryzen') !== false) {
                        $score += 10;
                    }
                } elseif ($groupLower === 'worker' || $groupLower === 'người đi làm') {
                    if ($laptop['category_id'] == 2) { // Văn phòng
                        $score += 15;
                        $reasons[] = "Thiết kế trang nhã lịch sự, phù hợp môi trường công sở.";
                    }
                }

                // 3. Phân tích độ ưu tiên (Priority)
                $prioLower = strtolower($userPriority);
                if ($prioLower === 'price' || $prioLower === 'giá') {
                    // Thưởng thêm cho các máy giá rẻ hơn
                    $priceRatio = 1.0 - ($price / 30000000); // Tỷ lệ so với 30M
                    if ($priceRatio > 0) {
                        $score += $priceRatio * 20;
                    }
                } elseif ($prioLower === 'performance' || $prioLower === 'hiệu năng') {
                    if (strpos($cpu, 'i7') !== false || strpos($cpu, 'i9') !== false || strpos($cpu, 'ryzen 7') !== false) {
                        $score += 15;
                        $reasons[] = "Sở hữu CPU dòng cao cấp mang lại hiệu năng đa nhân mạnh mẽ.";
                    }
                    if ($ram >= 16) {
                        $score += 10;
                    }
                } elseif ($prioLower === 'pin' || $prioLower === 'battery') {
                    if (!$isGamingVga && $laptop['category_id'] == 2) {
                        $score += 15;
                        $reasons[] = "Cấu hình tiết kiệm điện năng giúp kéo dài thời gian sử dụng pin từ 5-7 tiếng.";
                    } else {
                        $score -= 10;
                    }
                } elseif ($prioLower === 'mỏng nhẹ' || $prioLower === 'thin_light') {
                    if ($laptop['category_id'] == 2) {
                        $score += 20;
                    } else {
                        $score -= 20;
                    }
                }

                // Giới hạn điểm tối đa 98% và tối thiểu 40%
                if ($score > 98) $score = 98;
                if ($score < 40) $score = 40;

                // Nếu chưa có lý do nào, thêm lý do mặc định
                if (empty($reasons)) {
                    $reasons[] = "Sản phẩm chính hãng với cấu hình tốt trong tầm giá.";
                }

                $scoredLaptops[] = [
                    'id' => $laptop['id'],
                    'name' => $laptop['name'],
                    'price' => $price,
                    'price_formatted' => number_format($price, 0, ',', '.') . 'đ',
                    'image' => $laptop['image'],
                    'slug' => $laptop['slug'],
                    'score' => round($score),
                    'specs' => [
                        'CPU' => $specs['CPU'] ?? 'Intel/AMD',
                        'RAM' => $specs['RAM'] ?? '8GB',
                        'SSD' => $specs['SSD'] ?? '512GB',
                        'VGA' => $specs['VGA'] ?? 'Onboard'
                    ],
                    'reasons' => array_unique(array_slice($reasons, 0, 3))
                ];
            }

            // Sắp xếp giảm dần theo điểm số
            usort($scoredLaptops, function($a, $b) {
                return $b['score'] <=> $a['score'];
            });

            // Lấy top 3 chiếc phù hợp nhất
            $recommendations = array_slice($scoredLaptops, 0, 3);

            // Chuẩn bị tin nhắn phản hồi của AI
            $aiMessage = "🤖 **TechPilot AI đề xuất cho bạn:**\n\n";
            if (empty($recommendations)) {
                $aiMessage = "🤖 Xin lỗi, hiện tại hệ thống chưa tìm được laptop nào phù hợp với hạn mức ngân sách của bạn. Bạn hãy thử tăng ngân sách hoặc liên hệ trực tiếp đội ngũ TechPilot để được tư vấn kỹ hơn nhé!";
            } else {
                $aiMessage .= "Dựa trên hồ sơ của bạn:\n";
                $aiMessage .= "• Nhóm đối tượng: **" . ($userGroup !== '' ? $userGroup : 'Chưa chọn') . "**\n";
                $aiMessage .= "• Ngân sách: **" . ($userBudgetStr !== '' ? number_format($maxBudget, 0, ',', '.') . 'đ' : 'Chưa chọn') . "**\n";
                $aiMessage .= "• Ưu tiên: **" . ($userPriority !== '' ? $userPriority : 'Chân thực') . "**\n\n";
                $aiMessage .= "Dưới đây là 3 mẫu laptop phù hợp nhất đã được chọn lọc và chấm điểm:";
            }

            echo json_encode([
                'success' => true,
                'ai_message' => $aiMessage,
                'recommendations' => $recommendations
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Xử lý ngôn ngữ tự nhiên cơ bản bằng luật từ khóa (Keyword routing)
     */
    private function handleNaturalLanguage(string $q): ?array
    {
        $q = strtolower(removeVietnameseAccents($q));

        // Kiểm tra xem người dùng có đang hỏi tìm máy theo mức giá cụ thể không (Ví dụ: "có máy 3 triệu không")
        $targetPrice = $this->extractTargetPrice($q);
        if ($targetPrice !== null) {
            $stmt = $this->db->prepare(
                "SELECT p.*, b.name as brand_name, c.name as category_name 
                 FROM products p
                 LEFT JOIN brands b ON p.brand_id = b.id
                 LEFT JOIN categories c ON p.category_id = c.id
                 WHERE p.status = 'active'
                 ORDER BY ABS(p.price - ?) ASC
                 LIMIT 5"
            );
            $stmt->execute([$targetPrice]);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($products)) {
                $recs = [];
                foreach ($products as $p) {
                    $specs = json_decode($p['specs'], true) ?? [];
                    // Tính độ phù hợp dựa trên khoảng cách giá lệch (càng gần càng cao, tối đa 98%)
                    $priceDiff = abs($p['price'] - $targetPrice);
                    $score = 98 - (int)($priceDiff / 150000); 
                    if ($score > 98) $score = 98;
                    if ($score < 40) $score = 40;

                    $recs[] = [
                        'id' => $p['id'],
                        'name' => $p['name'],
                        'price' => (float)$p['price'],
                        'price_formatted' => number_format($p['price'], 0, ',', '.') . 'đ',
                        'image' => $p['image'],
                        'slug' => $p['slug'],
                        'score' => $score,
                        'specs' => [
                            'CPU' => $specs['CPU'] ?? 'Intel/AMD',
                            'RAM' => $specs['RAM'] ?? '8GB',
                            'SSD' => $specs['SSD'] ?? '512GB',
                            'VGA' => $specs['VGA'] ?? 'Onboard'
                        ],
                        'reasons' => [
                            "Mức giá thực tế: " . number_format($p['price'], 0, ',', '.') . "đ",
                            "Gần nhất với mức ngân sách " . number_format($targetPrice, 0, ',', '.') . "đ bạn yêu cầu."
                        ]
                    ];
                }

                $aiMessage = "🤖 **Dạ có!** Dưới đây là danh sách 5 sản phẩm có mức giá gần với **" . number_format($targetPrice, 0, ',', '.') . "đ** nhất hiện đang được bán tại cửa hàng TechPilot:";

                return [
                    'success' => true,
                    'type' => 'recommendations',
                    'ai_message' => $aiMessage,
                    'recommendations' => $recs
                ];
            } else {
                return [
                    'success' => true,
                    'type' => 'text',
                    'message' => "🤖 Dạ hiện tại TechPilot chưa có sản phẩm nào có tầm giá gần mức **" . number_format($targetPrice, 0, ',', '.') . "đ**."
                ];
            }
        }

        // 1. Hỏi về RAM 8GB vs 16GB
        if (strpos($q, 'ram') !== false && (strpos($q, '8g') !== false && strpos($q, '16g') !== false || strpos($q, 'khac gi') !== false || strpos($q, 'so sanh') !== false)) {
            return [
                'success' => true,
                'type' => 'text',
                'message' => "🤖 **Sự khác biệt giữa RAM 8GB và 16GB:**\n\n" .
                             "• **RAM 8GB**:\n" .
                             "  ✔ Đủ đáp ứng các tác vụ văn phòng nhẹ nhàng (Word, Excel, PowerPoint).\n" .
                             "  ✔ Đọc tài liệu, lướt web dưới 10 tabs Chrome cùng lúc.\n" .
                             "  ✔ Học lập trình web cơ bản (HTML/CSS/JS).\n\n" .
                             "• **RAM 16GB**:\n" .
                             "  ✔ Cực kỳ cần thiết cho thiết kế đồ họa (Photoshop, Illustrator, Premiere).\n" .
                             "  ✔ Lập trình nâng cao (Android Studio, chạy máy ảo Docker, máy ảo Android, Java).\n" .
                             "  ✔ Chiến game mượt mà hơn mà không lo giật lag do tràn RAM.\n" .
                             "  ✔ Đa nhiệm thoải mái mở hàng chục tab Chrome.\n\n" .
                             "👉 **Khuyên dùng**: Nếu ngân sách cho phép, bạn nên chọn luôn bản 16GB để dùng ổn định lâu dài trong 3-5 năm tới mà không cần lo lắng chuyện nâng cấp."
            ];
        }

        // 2. CPU i3 học lập trình được không?
        if ((strpos($q, 'i3') !== false || strpos($q, 'core i3') !== false) && (strpos($q, 'lap trinh') !== false || strpos($q, 'code') !== false || strpos($q, 'hoc') !== false)) {
            return [
                'success' => true,
                'type' => 'text',
                'message' => "🤖 **CPU i3 có học lập trình được không?**\n\n" .
                             "**CÓ THỂ ĐƯỢC.** i3 hoàn toàn học lập trình tốt nếu bạn học các mảng sau:\n" .
                             "✔ Lập trình Web Front-end (HTML, CSS, JavaScript).\n" .
                             "✔ Lập trình PHP, NodeJS cơ bản.\n" .
                             "✔ Cấu trúc dữ liệu và giải thuật (C/C++, Java core, Python).\n\n" .
                             "⚠️ **Tuy nhiên, bạn nên chọn CPU i5 hoặc i7 nếu học:**\n" .
                             "• Lập trình trí tuệ nhân tạo (AI/Machine Learning).\n" .
                             "• Dựng game 3D bằng Unity hoặc Unreal Engine.\n" .
                             "• Lập trình ứng dụng di động (Android Studio chạy máy ảo nặng).\n\n" .
                             "👉 **Lời khuyên**: Nếu là sinh viên CNTT năm nhất, chip i3 là giải pháp tiết kiệm ngân sách rất tốt. Nhưng từ năm 3 trở đi, nếu làm các đồ án lớn, bạn nên nâng cấp lên tối thiểu Core i5 để biên dịch code nhanh hơn."
            ];
        }

        // 3. i3 vs i7 khác nhau thế nào
        if (strpos($q, 'i3') !== false && strpos($q, 'i7') !== false && (strpos($q, 'so sanh') !== false || strpos($q, 'khac') !== false)) {
            return [
                'success' => true,
                'type' => 'text',
                'message' => "🤖 **So sánh nhanh CPU Core i3 và Core i7:**\n\n" .
                             "• **Core i3 (Thường dưới 10 triệu)**:\n" .
                             "  ✔ Dành cho: Sinh viên học tập, người lớn tuổi đọc báo, văn phòng.\n" .
                             "  ✔ Ưu điểm: Giá cực kỳ rẻ, mát máy, thời lượng pin sử dụng lâu hơn.\n\n" .
                             "• **Core i7 (Thường trên 15-20 triệu)**:\n" .
                             "  ✔ Dành cho: Designer, Coder chuyên nghiệp, Game thủ.\n" .
                             "  ✔ Ưu điểm: Xử lý đa nhân siêu mạnh, render đồ họa nhanh, đa nhiệm mượt mà.\n\n" .
                             "-------------------\n" .
                             "👉 **Tóm lại**: Học sinh sinh viên nên mua i3 để tiết kiệm chi phí học tập. Người đi làm và làm chuyên môn kỹ thuật/AI/đồ họa thì chọn i7 là khoản đầu tư thông minh nhất."
            ];
        }

        // 4. Máy này chơi Valorant được không? / Game esport
        if (strpos($q, 'valorant') !== false || strpos($q, 'lien minh') !== false || strpos($q, 'lol') !== false || strpos($q, 'csgo') !== false || strpos($q, 'fifa') !== false || strpos($q, 'fo4') !== false) {
            return [
                'success' => true,
                'type' => 'text',
                'message' => "🤖 **Khả năng chơi Valorant / Liên Minh / Game Esport:**\n\n" .
                             "• **Với Laptop Văn Phòng (Không card rời)**:\n" .
                             "  ✔ CPU Core i5 (Iris Xe Graphics) hoặc Ryzen 5 trở lên: Chơi mượt ở mức thiết lập **Medium (Trung bình)**, FPS ổn định trong khoảng **90-120 FPS**.\n" .
                             "  ✔ CPU i3 đời cũ: Có thể chơi được ở Low setting nhưng thỉnh thoảng sẽ bị tụt khung hình khi vào giao tranh lớn.\n\n" .
                             "• **Với Laptop Gaming (Có card rời GTX/RTX)**:\n" .
                             "  ✔ Chơi cực mượt ở thiết lập **Max Setting**, FPS dễ dàng đạt trên **165+ FPS**, tương thích hoàn hảo với màn hình tần số quét cao.\n" .
                             "  \n" .
                             "👉 Bạn có thể click nút **[ Tư vấn theo nhu cầu ]** rồi chọn ưu tiên là **[ Chơi game ]** để trợ lý AI tìm ngay cho bạn các mẫu laptop gaming có card đồ họa rời tốt nhất!"
            ];
        }

        // 5. Laptop dùng được bao lâu? Tuổi thọ máy
        if (strpos($q, 'dung duoc bao lau') !== false || strpos($q, 'ben') !== false || strpos($q, 'tuoi tho') !== false) {
            return [
                'success' => true,
                'type' => 'text',
                'message' => "🤖 **Tuổi thọ trung bình của laptop:**\n\n" .
                             "• **Từ 4 - 6 năm**:\n" .
                             "  Nếu bạn chỉ sử dụng cho các công việc học tập, soạn thảo văn bản, và văn phòng cơ bản. Giữ máy sạch sẽ và vệ sinh tra keo tản nhiệt định kỳ mỗi năm một lần.\n\n" .
                             "• **Từ 3 năm**:\n" .
                             "  Nếu bạn sử dụng máy liên tục để render phim ảnh 4K, huấn luyện mô hình AI, chạy Docker tải nặng liên tục. Máy sẽ nhanh hao pin hơn và bạn có thể cần nâng cấp thêm RAM/SSD để bắt kịp các phần mềm mới.\n\n" .
                             "👉 **Khuyên dùng**: Chọn máy có khung kim loại (Aluminium) và có khả năng nâng cấp RAM/SSD sẽ giúp kéo dài tuổi thọ sử dụng của máy thêm 2-3 năm nữa."
            ];
        }

        // 6. Chào hỏi
        if (strpos($q, 'hello') !== false || strpos($q, 'chao') !== false || strpos($q, 'hi') !== false || strpos($q, 'xin chao') !== false) {
            return [
                'success' => true,
                'type' => 'text',
                'message' => "🤖 **Xin chào! 👋 Tôi là trợ lý ảo TechPilot AI.**\n\nTôi ở đây để hỗ trợ bạn chọn máy tính, so sánh cấu hình và giải đáp mọi thắc mắc. Bạn có thể sử dụng các nút bấm nhanh bên dưới hoặc gõ câu hỏi trực tiếp nhé!"
            ];
        }

        // 7. Muốn mua laptop
        if (strpos($q, 'muon mua') !== false || strpos($q, 'tu van') !== false || strpos($q, 'laptop nao') !== false) {
            return [
                'success' => true,
                'type' => 'start_quiz',
                'message' => "🤖 Vâng, tôi rất sẵn lòng tư vấn cho bạn! Hãy nhấn nút bên dưới để bắt đầu quy trình chọn lọc laptop phù hợp với nhu cầu và ngân sách của bạn nhé."
            ];
        }

        // Fallback: Tìm thử xem có tên sản phẩm nào xuất hiện trong câu hỏi không
        return null;
    }

    /**
     * Hỗ trợ trích xuất số tiền người dùng nhập từ ngôn ngữ tự nhiên
     */
    private function extractTargetPrice(string $text): ?float
    {
        // Loại bỏ dấu tiếng Việt để đồng nhất so sánh
        $text = strtolower(removeVietnameseAccents($text));
        
        // Tìm dạng: số + tr hoặc số + trieu (có thể có phần thập phân như 3.5tr, 12,5tr, 8,5 triệu)
        if (preg_match('/(\d+([.,]\d+)?)\s*(trieu|tr)/i', $text, $matches)) {
            $num = (float)str_replace(',', '.', $matches[1]);
            return $num * 1000000;
        }

        // Tìm dạng số tiền đầy đủ: 3.000.000, 3,000,000, 3000000
        if (preg_match('/(\d{1,3}([.,]\d{3})+)/', $text, $matches)) {
            $cleaned = str_replace(['.', ','], '', $matches[1]);
            return (float)$cleaned;
        }

        // Tìm số đơn lẻ lớn hơn 100000
        if (preg_match('/(\d{5,10})/', $text, $matches)) {
            return (float)$matches[1];
        }

        return null;
    }
}

/**
 * Helper loại bỏ dấu tiếng Việt để so sánh chuỗi chính xác
 */
if (!function_exists('removeVietnameseAccents')) {
    function removeVietnameseAccents(string $str): string
    {
        $unicode = [
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd' => 'đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ệ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
            'A' => 'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ằ|Ẳ|Ẵ|Ặ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'D' => 'Đ',
            'E' => 'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'I' => 'Í|Ì|R|Ĩ|Ị',
            'O' => 'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'U' => 'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'Y' => 'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
        ];
        foreach ($unicode as $nonUnicode => $uni) {
            $str = preg_replace("/($uni)/i", $nonUnicode, $str);
        }
        return $str;
    }
}

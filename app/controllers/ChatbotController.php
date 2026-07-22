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
     * API: Trả về danh sách tất cả sản phẩm hoạt động để hiển thị trong các dropdown/tìm kiếm so sánh
     */
    public function products(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if ($this->db === null) {
            echo json_encode(['success' => false, 'message' => 'Lỗi kết nối database']);
            exit;
        }

        try {
            // Lấy tất cả sản phẩm đang hoạt động để phục vụ so sánh & tìm kiếm nhanh
            $stmt = $this->db->prepare(
                "SELECT id, name, price, image, specs 
                 FROM products 
                 WHERE status = 'active'
                 ORDER BY name ASC"
            );
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Parse specs
            foreach ($products as &$p) {
                $p['price_formatted'] = number_format($p['price'], 0, ',', '.') . 'đ';
                $p['specs_decoded'] = json_decode($p['specs'] ?? '', true) ?? [];
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
            $leftSpecs = json_decode($leftProduct['specs'] ?? '', true) ?? [];
            $rightSpecs = json_decode($rightProduct['specs'] ?? '', true) ?? [];

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
     * API: Nhận câu hỏi tự nhiên hoặc profile khảo sát để tư vấn + giải thích sử dụng Gemini AI
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
        if ($userBudgetStr === 'under_5m') $maxBudget = 10000000; // Lên mức tối thiểu thích hợp
        elseif ($userBudgetStr === '5_10m') $maxBudget = 15000000;
        elseif ($userBudgetStr === '10_20m') $maxBudget = 25000000;
        elseif ($userBudgetStr === 'over_20m') $maxBudget = 999000000;
        elseif (is_numeric($userBudgetStr)) $maxBudget = (float)$userBudgetStr;

        // Nếu người dùng gửi tin nhắn trò chuyện tự nhiên
        if ($queryText !== '') {
            // Khởi động session nếu chưa có
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Khởi tạo ngữ cảnh chatbot nếu chưa có hoặc muốn reset
            if (!isset($_SESSION['chatbot_context']) || strpos(strtolower($queryText), 'tu dau') !== false || strpos(strtolower($queryText), 'reset') !== false) {
                $_SESSION['chatbot_context'] = [
                    'budget' => 'Chưa biết',
                    'device_type' => 'Chưa biết',
                    'purpose' => 'Chưa biết',
                    'software' => 'Chưa biết',
                    'priority' => 'Chưa biết'
                ];
            }

            // 1. KIỂM TRA XEM CÓ PHẢI HỎI ĐÁP / THẢO LUẬN VỀ SẢN PHẨM ĐÃ XUẤT HIỆN
            $rawLower = strtolower($this->removeVietnameseAccents($queryText));
            $isProductDiscussion = false;
            $discussedProduct = null;

            // Tìm mã số sản phẩm (ví dụ: "model 14", "san pham 14", "may 14" hoặc chỉ đơn giản là số "14")
            if (preg_match('/(?:model|mau|sp|san pham|id|so|chiec|cai)?\s*(\d+)/i', $rawLower, $numMatches)) {
                $productId = (int)$numMatches[1];
                try {
                    $stmt = $this->db->prepare("SELECT * FROM products WHERE id = ? AND status = 'active'");
                    $stmt->execute([$productId]);
                    $discussedProduct = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($discussedProduct) {
                        $isProductDiscussion = true;
                    }
                } catch (Exception $e) {}
            }

            // Nếu là thảo luận sản phẩm cụ thể, chuyển sang Gemini trả lời trực tiếp mà không đi qua khảo sát
            if ($isProductDiscussion && $discussedProduct) {
                require_once ROOT_PATH . '/app/services/GeminiService.php';
                $specs = json_decode($discussedProduct['specs'] ?? '{}', true) ?: [];
                $specsStr = implode(', ', array_map(function($k, $v) { 
                    $valStr = is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : (string)$v;
                    return "$k: $valStr"; 
                }, array_keys($specs), $specs));
                
                $productPrompt = "Bạn là TechPilot AI Advisor. Khách hàng đang hỏi về sản phẩm: {$discussedProduct['name']} (Giá: " . number_format($discussedProduct['price'], 0, ',', '.') . "đ. Cấu hình: $specsStr).\n" .
                                  "Câu hỏi của khách: \"$queryText\"\n\n" .
                                  "Hãy trả lời tự nhiên, thân thiện và chính xác về sản phẩm này (giải thích tại sao đắt/rẻ, có ưu điểm gì, chiến game tốt không...). Gọi khách là 'bạn', xưng 'mình'. Trả lời ngắn gọn 2-3 câu.";
                
                try {
                    $answer = GeminiService::callGemini($productPrompt, ['type' => 'product_chat', 'product' => $discussedProduct]);
                    echo json_encode([
                        'success' => true,
                        'type' => 'text',
                        'message' => $answer
                    ]);
                    exit;
                } catch (Exception $e) {}
            }

            $response = $this->handleNaturalLanguage($queryText);
            if ($response !== null) {
                echo json_encode($response);
                exit;
            }

            // Hỏi đáp AI sử dụng Gemini và nạp session context
            require_once ROOT_PATH . '/app/services/GeminiService.php';
            require_once ROOT_PATH . '/app/services/ProductIntelligenceService.php';

            // 1. Phân tích để cập nhật thông tin trong session từ câu trả lời mới của khách
            
            // Tìm số tiền có trong câu hỏi (Ví dụ: "15 triệu", "15tr", "10 triệu")
            if (preg_match('/(\d+)\s*(triệu|trieu|tr)/ui', $queryText, $matches)) {
                $_SESSION['chatbot_context']['budget'] = $matches[1] . ' triệu';
            } elseif (preg_match('/(\d+)(000000)/', $queryText, $matches)) {
                $_SESSION['chatbot_context']['budget'] = ($matches[1]) . ' triệu';
            }

            // Phân tích loại máy
            if (strpos($rawLower, 'laptop') !== false || strpos($rawLower, 'may xach tay') !== false) {
                $_SESSION['chatbot_context']['device_type'] = 'Laptop';
            } elseif (strpos($rawLower, 'pc') !== false || strpos($rawLower, 'may ban') !== false || strpos($rawLower, 'may tinh bo') !== false) {
                $_SESSION['chatbot_context']['device_type'] = 'PC';
            }

            // Phân tích mục đích / phần mềm
            if (strpos($rawLower, 'choi game') !== false || strpos($rawLower, 'game') !== false || strpos($rawLower, 'gaming') !== false) {
                $_SESSION['chatbot_context']['purpose'] = 'Chơi game';
            } elseif (strpos($rawLower, 'lap trinh') !== false || strpos($rawLower, 'coder') !== false || strpos($rawLower, 'it ') !== false || strpos($rawLower, 'hoc it') !== false) {
                $_SESSION['chatbot_context']['purpose'] = 'Lập trình';
            } elseif (strpos($rawLower, 'do hoa') !== false || strpos($rawLower, 'design') !== false || strpos($rawLower, 'thiet ke') !== false || strpos($rawLower, 'photoshop') !== false) {
                $_SESSION['chatbot_context']['purpose'] = 'Thiết kế đồ họa';
            } elseif (strpos($rawLower, 'van phong') !== false || strpos($rawLower, 'hoc tap') !== false || strpos($rawLower, 'excel') !== false) {
                $_SESSION['chatbot_context']['purpose'] = 'Học tập / Văn phòng';
            }

            if (strpos($rawLower, 'vs code') !== false || strpos($rawLower, 'android studio') !== false || strpos($rawLower, 'docker') !== false) {
                $_SESSION['chatbot_context']['software'] = 'Lập trình (VS Code, Android Studio...)';
            } elseif (strpos($rawLower, 'photoshop') !== false || strpos($rawLower, 'premiere') !== false || strpos($rawLower, 'cad') !== false) {
                $_SESSION['chatbot_context']['software'] = 'Đồ họa (Photoshop, Premiere...)';
            }

            // Phân tích ưu tiên
            if (strpos($rawLower, 'hieu nang') !== false || strpos($rawLower, 'cau hinh') !== false || strpos($rawLower, 'khoe') !== false) {
                $_SESSION['chatbot_context']['priority'] = 'Hiệu năng';
            } elseif (strpos($rawLower, 'pin') !== false || strpos($rawLower, 'trau') !== false) {
                $_SESSION['chatbot_context']['priority'] = 'Pin lâu';
            } elseif (strpos($rawLower, 'mong nhe') !== false || strpos($rawLower, 'nhe') !== false || strpos($rawLower, 'gon') !== false) {
                $_SESSION['chatbot_context']['priority'] = 'Mỏng nhẹ';
            } elseif (strpos($rawLower, 'tiet kiem') !== false || strpos($rawLower, 'gia re') !== false) {
                $_SESSION['chatbot_context']['priority'] = 'Tiết kiệm chi phí';
            }

            // Lấy danh mục phù hợp
            $catId = 2; // Mặc định laptop văn phòng
            if ($_SESSION['chatbot_context']['device_type'] === 'PC') {
                $catId = 3; // PC lắp sẵn
            } elseif ($_SESSION['chatbot_context']['purpose'] === 'Chơi game') {
                $catId = 1; // Laptop gaming
            }

            // Lấy danh sách sản phẩm mẫu từ DB
            $productsContext = "";
            $candidatesMap = [];
            try {
                $stmt = $this->db->prepare(
                    "SELECT p.*, b.name as brand_name, c.name as category_name, c.slug as category_slug
                     FROM products p 
                     LEFT JOIN brands b ON p.brand_id = b.id
                     LEFT JOIN categories c ON p.category_id = c.id
                     WHERE p.category_id = ? AND p.status = 'active' AND p.stock > 0 
                     LIMIT 6"
                );
                $stmt->execute([$catId]);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($rows)) {
                    $productsContext = "Dưới đây là một số sản phẩm thật có sẵn tại cửa hàng TechPilot để đề xuất khi khách hàng cung cấp đủ dữ liệu:\n";
                    foreach ($rows as $r) {
                        $candidatesMap[$r['id']] = $r;
                        $specs = json_decode($r['specs'] ?? '{}', true) ?: [];
                        $specsStr = implode(', ', array_map(function($k, $v) { 
                            $valStr = is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : (string)$v;
                            return "$k: $valStr"; 
                        }, array_keys($specs), $specs));
                        $productsContext .= "- Tên: {$r['name']} (ID: {$r['id']}). Giá: " . number_format($r['price'], 0, ',', '.') . "đ. Cấu hình: $specsStr.\n";
                    }
                }
            } catch (Exception $e) {}

            // Đếm số lượng thông tin quan trọng đã biết
            $knownFieldsCount = 0;
            if ($_SESSION['chatbot_context']['budget'] !== 'Chưa biết') $knownFieldsCount++;
            if ($_SESSION['chatbot_context']['device_type'] !== 'Chưa biết') $knownFieldsCount++;
            if ($_SESSION['chatbot_context']['purpose'] !== 'Chưa biết') $knownFieldsCount++;
            if ($_SESSION['chatbot_context']['software'] !== 'Chưa biết') $knownFieldsCount++;
            if ($_SESSION['chatbot_context']['priority'] !== 'Chưa biết') $knownFieldsCount++;

            // Yêu cầu có ít nhất 3 câu trả lời / 3 thông tin thu thập được trước khi đề xuất máy
            $forceAskMore = ($knownFieldsCount < 3);

            // Tạo system prompt chỉ định vai trò tư vấn tự nhiên bám sát luồng thông tin
            $promptText = "Bạn là TechPilot AI Advisor - Một chuyên viên tư vấn laptop và PC thân thiện, lịch sự và chuyên nghiệp.\n" .
                          "Nhiệm vụ của bạn là trò chuyện tự nhiên với khách hàng để tìm ra sản phẩm phù hợp nhất.\n\n" .
                          "=== THÔNG TIN HỘI THOẠI HIỆN TẠI ===\n" .
                          "- Ngân sách: " . $_SESSION['chatbot_context']['budget'] . "\n" .
                          "- Loại máy: " . $_SESSION['chatbot_context']['device_type'] . "\n" .
                          "- Mục đích: " . $_SESSION['chatbot_context']['purpose'] . "\n" .
                          "- Phần mềm: " . $_SESSION['chatbot_context']['software'] . "\n" .
                          "- Ưu tiên: " . $_SESSION['chatbot_context']['priority'] . "\n\n" .
                          "=== LUỒNG HỎI ĐÁP CỦA BẠN ===\n" .
                          "Hãy kiểm tra thông tin hội thoại từ trên xuống dưới:\n" .
                          "1. Nếu Ngân sách chưa biết -> Hãy hỏi Ngân sách của khách hàng.\n" .
                          "2. Nếu Ngân sách đã biết nhưng chưa biết Loại máy (Laptop hay PC) -> Hãy hỏi Laptop hay PC.\n" .
                          "3. Nếu Loại máy đã biết nhưng chưa biết Mục đích sử dụng -> Hãy hỏi mục đích (Học tập, Lập trình, Đồ họa, Chơi game...)\n" .
                          "4. Nếu Mục đích đã biết nhưng chưa biết Phần mềm -> Hãy hỏi các phần mềm cụ thể hay dùng (VS Code, Photoshop, AutoCAD...).\n" .
                          "5. Nếu Phần mềm đã biết nhưng chưa biết Ưu tiên -> Hãy hỏi xem khách ưu tiên gì hơn (Hiệu năng, độ nhẹ, pin lâu, tiết kiệm...).\n";

            if ($forceAskMore) {
                $promptText .= "CHÚ Ý ĐẶC BIỆT: Bạn đang có ít thông tin ($knownFieldsCount/5 trường). Bạn TUYỆT ĐỐI KHÔNG ĐƯỢC ĐỀ XUẤT SẢN PHẨM vào lúc này. Không thêm tag RECOMMENDED_IDS. Hãy tiếp tục hỏi câu tiếp theo thật tự nhiên.\n\n";
            } else {
                $promptText .= "6. Nếu ĐÃ ĐỦ THÔNG TIN (từ 3 thông tin trở lên) -> Tiến hành đề xuất tối đa 3 sản phẩm phù hợp nhất trong danh sách bên dưới kèm lý do kết luận cụ thể.\n\n";
            }

            $promptText .= "=== QUY TẮC PHẢN HỒI QUAN TRỌNG ===\n" .
                          "- Gọi khách là 'bạn', xưng 'mình'.\n" .
                          "- Trả lời ngắn gọn, tự nhiên như nhân viên cửa hàng thực tế, tuyệt đối KHÔNG trả lời máy móc hay dùng các câu 'Dựa trên thông tin...', 'Tôi là AI...'\n" .
                          "- Mỗi lượt chat CHỈ HỎI ĐÚNG 1 CÂU quan trọng nhất còn thiếu. Không hỏi dồn dập nhiều câu cùng lúc.\n" .
                          "- Luôn phản hồi/dẫn dắt câu trả lời trước của khách trước khi đặt câu hỏi tiếp theo (ví dụ: 'Cảm ơn bạn. Với tầm giá này mình có khá nhiều lựa chọn...', 'Đã rõ, lập trình thì Android Studio khá nặng...').\n" .
                          "- Nếu đã đủ dữ liệu tư vấn (và không bị buộc hỏi thêm), hãy gợi ý sản phẩm thật và ghi thẻ [RECOMMENDED_IDS: x, y, z] ở dòng cuối cùng.\n\n" .
                          $productsContext . "\n" .
                          "Tin nhắn mới nhất của khách hàng: \"$queryText\"";

            try {
                $answer = GeminiService::callGemini($promptText, ['type' => 'general']);
                
                // Kiểm tra xem có đề xuất sản phẩm nào không (chỉ chấp nhận khi không bị bắt buộc hỏi tiếp)
                if (!$forceAskMore && preg_match('/\[RECOMMENDED_IDS:\s*([\d\s,]+)\]/', $answer, $matches)) {
                    $rawIds = explode(',', $matches[1]);
                    $recommendedIds = array_map('intval', array_map('trim', $rawIds));
                    
                    $finalRecs = [];
                    foreach ($recommendedIds as $id) {
                        if (isset($candidatesMap[$id])) {
                            $p = $candidatesMap[$id];
                            $specs = json_decode($p['specs'] ?? '{}', true) ?: [];
                            $vfm = ProductIntelligenceService::calculateValueForMoney($p);
                            $finalRecs[] = [
                                'id' => $p['id'],
                                'name' => $p['name'],
                                'price' => (float)$p['price'],
                                'price_formatted' => number_format($p['price'], 0, ',', '.') . 'đ',
                                'image' => $p['image'],
                                'slug' => $p['slug'],
                                'score' => rand(90, 97),
                                'specs' => [
                                    'CPU' => $specs['CPU'] ?? $specs['cpu'] ?? 'N/A',
                                    'RAM' => $specs['RAM'] ?? $specs['ram'] ?? '8GB',
                                    'SSD' => $specs['SSD'] ?? $specs['ssd'] ?? '512GB',
                                    'VGA' => $specs['VGA'] ?? $specs['vga'] ?? 'Onboard'
                                ],
                                'reasons' => [
                                    "Độ đáng tiền (VFM): {$vfm}/10",
                                    "Khuyên dùng bởi AI TechPilot"
                                ]
                            ];
                        }
                    }

                    // Loại bỏ thẻ ra khỏi câu trả lời để hiển thị sạch đẹp
                    $cleanAnswer = preg_replace('/\[RECOMMENDED_IDS:\s*[\d\s,]+\]/', '', $answer);

                    if (!empty($finalRecs)) {
                        echo json_encode([
                            'success' => true,
                            'type' => 'recommendations',
                            'ai_message' => trim($cleanAnswer),
                            'recommendations' => $finalRecs
                        ]);
                        exit;
                    }
                }

                // Loại bỏ thẻ RECOMMENDED_IDS nếu AI lỡ viết ra khi chưa đủ 3 câu
                $cleanAnswer = preg_replace('/\[RECOMMENDED_IDS:\s*[\d\s,]+\]/', '', $answer);

                // Không có đề xuất sản phẩm cụ thể, trả về tin nhắn text bình thường
                echo json_encode([
                    'success' => true,
                    'type' => 'text',
                    'message' => trim($cleanAnswer)
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Lỗi kết nối AI: ' . $e->getMessage()
                ]);
            }
            exit;
        }

        // Chạy bộ máy chấm điểm tư vấn (Recommendation Engine) tích hợp AI Gemini cho Quiz Flow
        try {
            require_once ROOT_PATH . '/app/services/GeminiService.php';
            require_once ROOT_PATH . '/app/services/ProductIntelligenceService.php';

            $categories = [1, 2]; // Laptop mặc định cho Quiz
            $placeholders = implode(',', array_fill(0, count($categories), '?'));
            $stmt = $this->db->prepare(
                "SELECT p.*, b.name as brand_name, c.name as category_name, c.slug as category_slug
                 FROM products p
                 LEFT JOIN brands b ON p.brand_id = b.id
                 LEFT JOIN categories c ON p.category_id = c.id
                 WHERE p.category_id IN ($placeholders) AND p.status = 'active' AND p.stock > 0 AND p.price <= ?"
            );
            $stmt->execute(array_merge($categories, [$maxBudget]));
            $laptops = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($laptops)) {
                echo json_encode([
                    'success' => true,
                    'type' => 'text',
                    'message' => "🤖 Dạ hiện tại TechPilot chưa có mẫu laptop nào còn hàng dưới mức giá " . number_format($maxBudget, 0, ',', '.') . "đ. Bạn vui lòng nâng mức ngân sách hoặc liên hệ bộ phận kỹ thuật để được hỗ trợ thêm nhé!"
                ]);
                exit;
            }

            // Chấm điểm Heuristic sơ bộ
            $scoredLaptops = [];
            foreach ($laptops as $laptop) {
                $specs = json_decode($laptop['specs'] ?? '', true) ?? [];
                $price = (float)$laptop['price'];
                $score = 50.0;

                $cpu = strtolower($specs['CPU'] ?? $specs['cpu'] ?? '');
                $ram = (int)filter_var($specs['RAM'] ?? $specs['ram'] ?? '8', FILTER_SANITIZE_NUMBER_INT);
                $vga = strtolower($specs['VGA'] ?? $specs['vga'] ?? '');
                $isGamingVga = (strpos($vga, 'rtx') !== false || strpos($vga, 'gtx') !== false || strpos($vga, 'radeon rx') !== false);

                // Group mapping
                $groupLower = strtolower($userGroup);
                if ($groupLower === 'sinh vien' || $groupLower === 'student' || $groupLower === 'sinh viên') {
                    if ($price <= 15000000) $score += 20;
                    if ($laptop['category_id'] == 2) $score += 15;
                } elseif ($groupLower === 'game thu' || $groupLower === 'gamer' || $groupLower === 'game thủ') {
                    if ($isGamingVga) $score += 25;
                    if ($laptop['category_id'] == 1) $score += 20;
                } elseif ($groupLower === 'designer' || $groupLower === 'do hoa' || $groupLower === 'đồ họa') {
                    if ($ram >= 16) $score += 20;
                    if ($isGamingVga) $score += 15;
                } elseif ($groupLower === 'coder' || $groupLower === 'lap trinh vien' || $groupLower === 'lập trình viên') {
                    if ($ram >= 16) $score += 25;
                    if (strpos($cpu, 'i5') !== false || strpos($cpu, 'ryzen 5') !== false) $score += 10;
                }

                // Priority mapping
                $prioLower = strtolower($userPriority);
                if ($prioLower === 'hieu nang' || $prioLower === 'performance' || $prioLower === 'hiệu năng') {
                    if ($ram >= 16) $score += 15;
                    if (strpos($cpu, 'i7') !== false || strpos($cpu, 'ryzen 7') !== false) $score += 15;
                } elseif ($prioLower === 'mong nhe' || $prioLower === 'lightweight' || $prioLower === 'mỏng nhẹ') {
                    if ($laptop['category_id'] == 2) $score += 20;
                } elseif ($prioLower === 'pin' || $prioLower === 'battery') {
                    if (!$isGamingVga && $laptop['category_id'] == 2) $score += 20;
                }

                $laptop['calc_score'] = $score;
                $scoredLaptops[] = $laptop;
            }

            usort($scoredLaptops, function($a, $b) {
                return $b['calc_score'] <=> $a['calc_score'];
            });

            // Lấy top 8 ứng viên
            $candidatesSubset = array_slice($scoredLaptops, 0, 8);

            // Gửi cho Gemini AI tư vấn xếp hạng
            $filters = [
                'budget_val' => $maxBudget,
                'category_name' => 'Laptop',
                'purpose' => $userGroup,
                'software' => '',
                'priority' => $userPriority,
                'brand' => '',
                'excluded' => ''
            ];

            $aiResult = ProductIntelligenceService::recommendProducts($filters, $candidatesSubset);

            // Map 3 ID từ AI
            $bestId = $aiResult['best_id'];
            $savingId = $aiResult['saving_id'];
            $perfId = $aiResult['perf_id'];

            // Lấy thực tế từ subset
            $bestP = null; $savingP = null; $perfP = null;
            foreach ($candidatesSubset as $c) {
                if ($c['id'] == $bestId) $bestP = $c;
                if ($c['id'] == $savingId) $savingP = $c;
                if ($c['id'] == $perfId) $perfP = $c;
            }

            if (!$bestP) $bestP = $candidatesSubset[0];
            if (!$savingP) $savingP = $candidatesSubset[count($candidatesSubset)-1];
            if (!$perfP) $perfP = $candidatesSubset[0];

            // Dựng danh sách đề xuất gửi chatbot
            $finalRecs = [];
            $buildRecItem = function($p, $typeLabel, $scoreVal) {
                $specs = json_decode($p['specs'] ?? '{}', true) ?: [];
                $vfm = ProductIntelligenceService::calculateValueForMoney($p);
                return [
                    'id' => $p['id'],
                    'name' => "[{$typeLabel}] " . $p['name'],
                    'price' => (float)$p['price'],
                    'price_formatted' => number_format($p['price'], 0, ',', '.') . 'đ',
                    'image' => $p['image'],
                    'slug' => $p['slug'],
                    'score' => $scoreVal,
                    'specs' => [
                        'CPU' => $specs['CPU'] ?? $specs['cpu'] ?? 'N/A',
                        'RAM' => $specs['RAM'] ?? $specs['ram'] ?? '8GB',
                        'SSD' => $specs['SSD'] ?? $specs['ssd'] ?? '512GB',
                        'VGA' => $specs['VGA'] ?? $specs['vga'] ?? 'Onboard'
                    ],
                    'reasons' => [
                        "Độ đáng tiền (VFM): {$vfm}/10",
                        "Phù hợp nhất với hạn mức tài chính của bạn.",
                        "Bảo hành chính hãng tại TechPilot."
                    ]
                ];
            };

            $finalRecs[] = $buildRecItem($bestP, 'Phù hợp nhất', rand(94,98));
            if ($savingP['id'] !== $bestP['id']) {
                $finalRecs[] = $buildRecItem($savingP, 'Tiết kiệm nhất', rand(80,87));
            }
            if ($perfP['id'] !== $bestP['id'] && $perfP['id'] !== $savingP['id']) {
                $finalRecs[] = $buildRecItem($perfP, 'Hiệu năng cao nhất', rand(88,93));
            }

            $aiMessage = "🤖 **Trợ lý AI phân tích và đề xuất:**\n\n" . $aiResult['reasons'] . "\n\n⚠️ **Cân nhắc:** " . $aiResult['tradeoffs'];

            echo json_encode([
                'success' => true,
                'type' => 'recommendations',
                'ai_message' => $aiMessage,
                'recommendations' => $finalRecs
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
        $q = strtolower($this->removeVietnameseAccents($q));

        // 1. Kiểm tra xem người dùng có đang hỏi tìm máy theo mức giá cụ thể không (Ví dụ: "có máy 3 triệu không")
        $targetPrice = $this->extractTargetPrice($q);
        if ($targetPrice !== null) {
            $categories = $this->detectCategoryFilter($q);

            $sql = "SELECT p.*, b.name as brand_name, c.name as category_name 
                    FROM products p
                    LEFT JOIN brands b ON p.brand_id = b.id
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.status = 'active'";

            $params = [];
            if (!empty($categories)) {
                $placeholders = implode(',', array_fill(0, count($categories), '?'));
                $sql .= " AND p.category_id IN ($placeholders)";
                $params = array_merge($categories, [$targetPrice]);
            } else {
                $params = [$targetPrice];
            }

            $sql .= " ORDER BY ABS(p.price - ?) ASC LIMIT 5";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($products)) {
                $recs = [];
                foreach ($products as $p) {
                    $specs = json_decode($p['specs'] ?? '', true) ?? [];
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

                $productTypeLabel = 'sản phẩm';
                if (!empty($categories)) {
                    if (array_intersect([1, 2], $categories)) {
                        $productTypeLabel = 'mẫu Laptop';
                    } elseif (array_intersect([3, 6], $categories)) {
                        $productTypeLabel = 'mẫu PC / Máy tính bộ';
                    } elseif (array_intersect([5], $categories)) {
                        $productTypeLabel = 'mẫu Màn hình (LCD)';
                    } elseif (array_intersect([7, 8], $categories)) {
                        $productTypeLabel = 'món Phụ kiện / Gaming Gear';
                    } else {
                        $productTypeLabel = 'món Linh kiện PC';
                    }
                }

                $aiMessage = "🤖 **Dạ có!** Dưới đây là danh sách 5 " . $productTypeLabel . " có mức giá gần với **" . number_format($targetPrice, 0, ',', '.') . "đ** nhất hiện đang được bán tại cửa hàng TechPilot:";

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

        // 2. Kiểm tra xem người dùng có đang hỏi tìm sản phẩm giá rẻ / tiết kiệm không
        $cheapKeywords = ['re', 'gia re', 'thap', 'tiet kiem', 'ngan sach thap', 're nhat', 'binh dan', 'gia tot'];
        $isCheapQuery = false;
        $spacedQ = ' ' . trim(preg_replace('/[^\w\s]/u', ' ', $q)) . ' ';
        foreach ($cheapKeywords as $kw) {
            if (strpos($spacedQ, ' ' . trim($kw) . ' ') !== false) {
                $isCheapQuery = true;
                break;
            }
        }

        if ($isCheapQuery) {
            $categories = $this->detectCategoryFilter($q);

            $sql = "SELECT p.*, b.name as brand_name, c.name as category_name 
                    FROM products p
                    LEFT JOIN brands b ON p.brand_id = b.id
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.status = 'active'";

            $params = [];
            if (!empty($categories)) {
                $placeholders = implode(',', array_fill(0, count($categories), '?'));
                $sql .= " AND p.category_id IN ($placeholders)";
                $params = $categories;
            }

            $sql .= " ORDER BY p.price ASC LIMIT 5";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($products)) {
                $recs = [];
                foreach ($products as $p) {
                    $specs = json_decode($p['specs'] ?? '', true) ?? [];

                    $recs[] = [
                        'id' => $p['id'],
                        'name' => $p['name'],
                        'price' => (float)$p['price'],
                        'price_formatted' => number_format($p['price'], 0, ',', '.') . 'đ',
                        'image' => $p['image'],
                        'slug' => $p['slug'],
                        'score' => 95,
                        'specs' => [
                            'CPU' => $specs['CPU'] ?? ($specs['cpu'] ?? 'N/A'),
                            'RAM' => $specs['RAM'] ?? ($specs['ram'] ?? 'N/A'),
                            'SSD' => $specs['SSD'] ?? ($specs['ssd'] ?? ($specs['Ổ cứng'] ?? ($specs['o cung'] ?? 'N/A'))),
                            'VGA' => $specs['VGA'] ?? ($specs['vga'] ?? 'N/A')
                        ],
                        'reasons' => [
                            "Mức giá siêu hời: " . number_format($p['price'], 0, ',', '.') . "đ",
                            "Sản phẩm bán chạy trong phân khúc giá rẻ.",
                            "Bảo hành chính hãng TechPilot."
                        ]
                    ];
                }

                $catLabel = 'sản phẩm';
                if (!empty($categories)) {
                    if (array_intersect([1, 2], $categories)) {
                        $catLabel = 'mẫu Laptop';
                    } elseif (array_intersect([3, 6], $categories)) {
                        $catLabel = 'bộ PC';
                    } elseif (array_intersect([5], $categories)) {
                        $catLabel = 'mẫu Màn hình (LCD)';
                    } elseif (array_intersect([7, 8], $categories)) {
                        $catLabel = 'món Phụ kiện / Gear';
                    } else {
                        $catLabel = 'món Linh kiện PC';
                    }
                }

                $aiMessage = "🤖 **Dạ, đây là các " . $catLabel . " giá rẻ, tiết kiệm nhất hiện có tại TechPilot:**";

                return [
                    'success' => true,
                    'type' => 'recommendations',
                    'ai_message' => $aiMessage,
                    'recommendations' => $recs
                ];
            }
        }

        // 3. Các câu hỏi giao tiếp cơ bản (Greetings & FAQs)
        
        // Chào hỏi
        if ($this->hasKeywords($q, ['hello', 'chao', 'hi', 'xin chao', 'halo', 'helo', 'chao ban', 'chao shop'])) {
            return [
                'success' => true,
                'type' => 'text',
                'message' => "🤖 **Xin chào! 👋 Tôi là trợ lý ảo TechPilot AI.**\n\nTôi ở đây để hỗ trợ bạn chọn máy tính, so sánh cấu hình và giải đáp mọi thắc mắc. Bạn có thể sử dụng các nút bấm nhanh bên dưới hoặc gõ câu hỏi trực tiếp nhé!"
            ];
        }

        // Cửa hàng / Địa chỉ
        if ($this->hasKeywords($q, ['dia chi', 'o dau', 'showroom', 'cua hang', 'diem ban', 'xem may o dau', 'toi dau mua', 'chi nhanh'])) {
            return [
                'success' => true,
                'type' => 'text',
                'message' => "🤖 **Địa chỉ cửa hàng TechPilot:**\n\n📍 **Trụ sở chính**: 123 Đường Ba Tháng Hai, Quận 10, TP. Hồ Chí Minh.\n📞 **Hotline**: 1900.xxxx (8:00 - 21:30 hàng ngày).\n🚗 Có chỗ đỗ xe ô tô miễn phí và phòng trải nghiệm máy tính hiện đại bậc nhất. Rất hân hạnh được đón tiếp bạn!"
            ];
        }

        // Liên hệ / SĐT
        if ($this->hasKeywords($q, ['sdt', 'so dien thoai', 'hotline', 'lien he', 'facebook', 'zalo', 'lh', 'fanpage', 'email'])) {
            return [
                'success' => true,
                'type' => 'text',
                'message' => "🤖 **Thông tin liên hệ của TechPilot:**\n\n• **Hotline hỗ trợ**: 1900.xxxx\n• **Hotline kỹ thuật**: 0909.xxx.xxx\n• **Zalo OA**: TechPilot Việt Nam\n• **Fanpage**: facebook.com/techpilot.store\n• **Email**: contact@techpilot.vn\n\n👉 Bạn cần hỗ trợ gấp có thể gọi hotline để được phục vụ ngay nhé!"
            ];
        }

        // Giờ làm việc
        if ($this->hasKeywords($q, ['mo cua', 'gio lam viec', 'dong cua', 'thoi gian lam viec', 'may gio'])) {
            return [
                'success' => true,
                'type' => 'text',
                'message' => "🤖 **Thời gian làm việc của TechPilot:**\n\n⏰ **Giờ mở cửa**: 08:00 - 21:30 hàng ngày (kể cả Thứ Bảy, Chủ Nhật và các ngày lễ).\n👉 Phòng kỹ thuật và bảo hành làm việc từ 08:30 - 18:00 (Thứ Hai đến Thứ Bảy)."
            ];
        }

        // Giao hàng / Ship
        if ($this->hasKeywords($q, ['ship', 'giao hang', 'van chuyen', 'cod', 'phi ship', 'co ship khong'])) {
            return [
                'success' => true,
                'type' => 'text',
                'message' => "🤖 **Chính sách giao hàng tại TechPilot:**\n\n🚀 **Nội thành TP.HCM**: Giao hỏa tốc trong vòng 2 giờ đối với các đơn hàng linh kiện, PC, Laptop có sẵn.\n🚚 **Toàn quốc**: Giao hàng tận nơi qua GHTK, Viettel Post từ 2 - 4 ngày làm việc.\n💰 **Phí ship**: Miễn phí vận chuyển toàn quốc cho đơn hàng từ 5.000.000đ trở lên. Hỗ trợ đồng kiểm trước khi nhận hàng và thanh toán (COD)."
            ];
        }

        // Thanh toán / Trả góp
        if ($this->hasKeywords($q, ['thanh toan', 'tra gop', 'chuyen khoan', 'tien mat', 'quet the', 'atm', 'visa'])) {
            return [
                'success' => true,
                'type' => 'text',
                'message' => "🤖 **Các phương thức thanh toán hỗ trợ tại TechPilot:**\n\n1. **Tiền mặt** trực tiếp tại cửa hàng hoặc thanh toán COD khi nhận hàng.\n2. **Chuyển khoản ngân hàng** nhanh qua mã QR.\n3. **Quẹt thẻ**: Hỗ trợ thẻ ATM nội địa, Visa, Mastercard, JCB.\n4. **Trả góp 0%** lãi suất qua thẻ tín dụng (hỗ trợ hơn 25 ngân hàng liên kết) hoặc trả góp qua công ty tài chính (HD Saison, MCredit) chỉ cần CCCD."
            ];
        }

        // Bảo hành / Đổi trả
        if ($this->hasKeywords($q, ['bao hanh', 'doi tra', 'loi thi sao', 'hu thi sao', 'loi san pham'])) {
            return [
                'success' => true,
                'type' => 'text',
                'message' => "🤖 **Chính sách bảo hành và đổi trả tại TechPilot:**\n\n• **Bảo hành chính hãng**: Tất cả sản phẩm Laptop, PC, Linh kiện đều được bảo hành chính hãng từ 12 - 36 tháng theo tiêu chuẩn nhà sản xuất.\n• **Đổi trả 1-đổi-1**: Đổi mới miễn phí trong vòng 7 ngày đầu nếu có lỗi phần cứng từ nhà sản xuất.\n• **Hỗ trợ trọn đời**: Miễn phí cài đặt phần mềm cơ bản, vệ sinh máy định kỳ trong 1 năm đầu mua sản phẩm."
            ];
        }

        // Nguồn gốc sản phẩm
        if ($this->hasKeywords($q, ['chinh hang', 'nguon goc', 'hang fake', 'nhai', 'moi hay cu', 'new', 'cu', 'second hand', 'xach tay'])) {
            return [
                'success' => true,
                'type' => 'text',
                'message' => "🤖 **Cam kết nguồn gốc sản phẩm tại TechPilot:**\n\n• **100% Chính hãng**: TechPilot cam kết chỉ phân phối hàng chính hãng, đầy đủ hóa đơn VAT, chứng từ CO/CQ từ nhà sản xuất.\n• **Tình trạng sản phẩm**: Tất cả sản phẩm bán ra đều là hàng mới 100% nguyên seal box (trừ trường hợp các dòng máy demo trưng bày được thanh lý sẽ có ghi chú rõ ràng).\n❌ **Nói không với hàng dựng, hàng giả, hàng kém chất lượng.**"
            ];
        }

        // Khuyến mãi
        if ($this->hasKeywords($q, ['khuyen mai', 'giam gia', 'uu dai', 'voucher', 'quatang', 'qua tang'])) {
            return [
                'success' => true,
                'type' => 'text',
                'message' => "🤖 **Chương trình khuyến mãi hiện tại của TechPilot:**\n\n🎁 **Ưu đãi mua PC/Laptop**:\n• Tặng balo thời trang + chuột gaming cao cấp.\n• Voucher giảm giá 10% khi mua kèm phụ kiện (chuột, bàn phím, màn hình).\n• Tặng gói vệ sinh máy miễn phí trọn đời.\n🎓 **Đặc quyền sinh viên**: Giảm ngay thêm 300.000đ - 500.000đ (áp dụng kèm thẻ sinh viên còn hiệu lực)."
            ];
        }

        // Nhóm hàng kinh doanh
        if ($this->hasKeywords($q, ['ban nhung gi', 'ban gi', 'kinh doanh gi', 'co san pham nao', 'co ban nhung gi'])) {
            return [
                'success' => true,
                'type' => 'text',
                'message' => "🤖 **Các mặt hàng kinh doanh tại TechPilot:**\n\n💻 **Laptop**: Laptop Gaming, Laptop Văn phòng, Macbook.\n🖥️ **PC**: PC Build sẵn chuyên Gaming, Đồ họa, Máy tính đồng bộ văn phòng.\n⚙️ **Linh kiện PC**: CPU, Mainboard, RAM, VGA, SSD/HDD, Nguồn (PSU), Case, Tản nhiệt.\n📺 **Màn hình**: Màn hình gaming tần số quét cao, màn hình đồ họa màu chuẩn, màn hình văn phòng.\n🖱️ **Gaming Gear & Phụ kiện**: Bàn phím cơ, chuột không dây, tai nghe, loa, bàn ghế gaming.\n\n👉 Bạn cần tư vấn nhóm hàng nào cụ thể không ạ?"
            ];
        }

        // 4. Hỏi về RAM 8GB vs 16GB
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

        // 5. CPU i3 học lập trình được không?
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

        // 6. i3 vs i7 khác nhau thế nào
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

        // 7. Máy này chơi Valorant được không? / Game esport
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

        // 8. Laptop dùng được bao lâu? Tuổi thọ máy
        if (strpos($q, 'dung duoc bao lau') !== false || strpos($q, 'ben') !== false || strpos($q, 'tuoi tho') !== false) {
            return [
                'success' => true,
                'type' => 'text',
                'message' => "🤖 **Tuọc thọ trung bình của laptop:**\n\n" .
                             "• **Từ 4 - 6 năm**:\n" .
                             "  Nếu bạn chỉ sử dụng cho các công việc học tập, soạn thảo văn bản, và văn phòng cơ bản. Giữ máy sạch sẽ và vệ sinh tra keo tản nhiệt định kỳ mỗi năm một lần.\n\n" .
                             "• **Từ 3 năm**:\n" .
                             "  Nếu bạn sử dụng máy liên tục để render phim ảnh 4K, huấn luyện mô hình AI, chạy Docker tải nặng liên tục. Máy sẽ nhanh hao pin hơn và bạn có thể cần nâng cấp thêm RAM/SSD để bắt kịp các phần mềm mới.\n\n" .
                             "👉 **Khuyên dùng**: Chọn máy có khung kim loại (Aluminium) và có khả năng nâng cấp RAM/SSD sẽ giúp kéo dài tuổi thọ sử dụng của máy thêm 2-3 năm nữa."
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
        $text = strtolower($this->removeVietnameseAccents($text));
        
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

    /**
     * Tự động phát hiện các danh mục sản phẩm tương ứng dựa trên từ khóa trong câu hỏi
     */
    private function detectCategoryFilter(string $text): array
    {
        $text = strtolower($this->removeVietnameseAccents($text));
        
        // 1. Linh kiện chi tiết PC
        if (strpos($text, 'cpu') !== false || strpos($text, 'chip') !== false) {
            return [10];
        }
        if (strpos($text, 'mainboard') !== false || strpos($text, 'bo mach') !== false || strpos($text, 'main') !== false) {
            return [11];
        }
        if (strpos($text, 'ram') !== false) {
            return [12];
        }
        if (strpos($text, 'vga') !== false || strpos($text, 'card do hoa') !== false || strpos($text, 'card roi') !== false || strpos($text, 'gpu') !== false) {
            return [13];
        }
        if (strpos($text, 'ssd') !== false) {
            return [14];
        }
        if (strpos($text, 'hdd') !== false) {
            return [15];
        }
        if (strpos($text, 'o cung') !== false) {
            return [14, 15];
        }
        if (strpos($text, 'nguon') !== false || strpos($text, 'psu') !== false) {
            return [16];
        }
        if (strpos($text, 'case') !== false || strpos($text, 'vo may') !== false) {
            return [17];
        }
        if (strpos($text, 'tan nhiet') !== false || strpos($text, 'quat chip') !== false || strpos($text, 'tan nuoc') !== false) {
            return [18];
        }

        // 2. PC build / Máy tính bộ / Thùng máy / Cây PC
        if (strpos($text, 'pc') !== false 
            || strpos($text, 'may tinh bo') !== false 
            || strpos($text, 'de ban') !== false 
            || strpos($text, 'cay pc') !== false
            || strpos($text, 'thung may') !== false) {
            return [3, 6];
        }
        
        // 3. Màn hình / LCD / Monitor
        if (strpos($text, 'man hinh') !== false 
            || strpos($text, 'lcd') !== false 
            || strpos($text, 'monitor') !== false) {
            return [5];
        }
        
        // 4. Accessories / Gaming Gear / Phụ kiện
        if (strpos($text, 'phu kien') !== false 
            || strpos($text, 'gear') !== false 
            || strpos($text, 'ban phim') !== false 
            || strpos($text, 'chuot') !== false 
            || strpos($text, 'tai nghe') !== false 
            || strpos($text, 'loa') !== false 
            || strpos($text, 'headset') !== false 
            || strpos($text, 'keyboard') !== false 
            || strpos($text, 'mouse') !== false) {
            return [7, 8];
        }
        
        // 5. Linh kiện PC nói chung
        if (strpos($text, 'linh kien') !== false) {
            return [4, 10, 11, 12, 13, 14, 15, 16, 17, 18];
        }
        
        // 6. Laptop / Máy tính xách tay
        if (strpos($text, 'laptop') !== false 
            || strpos($text, 'xach tay') !== false 
            || strpos($text, 'lap top') !== false
            || strpos($text, 'lapptop') !== false
            || strpos($text, 'lapptopp') !== false) {
            return [1, 2];
        }
        
        return [];
    }

    /**
     * Helper loại bỏ dấu tiếng Việt để so sánh chuỗi chính xác
     */
    private function removeVietnameseAccents(string $str): string
    {
        $unicode = [
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd' => 'đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
        ];
        foreach ($unicode as $nonUnicode => $uni) {
            $str = preg_replace("/($uni)/iu", $nonUnicode, $str);
        }
        return $str;
    }

    /**
     * Helper kiểm tra xem chuỗi có chứa từ khóa nào trong danh sách không
     */
    private function hasKeywords(string $text, array $keywords): bool
    {
        $cleanedText = preg_replace('/[^\w\s]/u', ' ', $text);
        $cleanedText = preg_replace('/\s+/', ' ', $cleanedText);
        $spacedText = ' ' . trim($cleanedText) . ' ';

        foreach ($keywords as $keyword) {
            $keywordClean = preg_replace('/[^\w\s]/u', ' ', $keyword);
            $keywordClean = preg_replace('/\s+/', ' ', $keywordClean);
            if (strpos($spacedText, ' ' . trim($keywordClean) . ' ') !== false) {
                return true;
            }
        }
        return false;
    }
}

<?php
/**
 * Dịch vụ kết nối API Gemini AI và Mock fallback thông minh
 */

class GeminiService
{
    /**
     * Gọi API Gemini để sinh nội dung dựa trên prompt.
     * Nếu không có API Key, hệ thống sẽ tự động chuyển sang chế độ Mock thông minh để tránh lỗi.
     *
     * @param string $prompt Nội dung yêu cầu gửi cho AI
     * @param array|null $contextData Dữ liệu ngữ cảnh bổ sung để Mock trả kết quả thực tế
     * @return string Kết quả văn bản từ AI (hoặc Mock)
     */
    public static function callGemini(string $prompt, ?array $contextData = null): string
    {
        $config = require ROOT_PATH . '/config/gemini.php';
        $apiKey = trim($config['api_key'] ?? '');

        if ($apiKey !== '') {
            return self::sendApiRequest($apiKey, $prompt);
        }

        // Chế độ Mock dự phòng thông minh khi chưa có API Key
        return self::generateMockResponse($prompt, $contextData);
    }

    /**
     * Gửi yêu cầu HTTP POST thực tế tới Google Gemini API
     */
    private static function sendApiRequest(string $apiKey, string $prompt): string
    {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey;
        
        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Tránh lỗi chứng chỉ SSL trên localhost

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            return "🤖 [Lỗi kết nối API] Không thể kết nối tới máy chủ Gemini AI.";
        }

        if ($httpCode !== 200) {
            $errData = json_decode($response, true);
            $errMessage = $errData['error']['message'] ?? 'Lỗi không xác định từ máy chủ API.';
            return "🤖 [Lỗi API HTTP {$httpCode}] {$errMessage}";
        }

        $data = json_decode($response, true);
        $textResult = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        
        if ($textResult === '') {
            return "🤖 Không có phản hồi hợp lệ từ mô hình AI.";
        }

        return $textResult;
    }

    /**
     * Sinh phản hồi giả lập (Mock) thông minh dựa trên ngữ cảnh để giao diện chạy mượt mà
     */
    private static function generateMockResponse(string $prompt, ?array $context): string
    {
        $type = $context['type'] ?? 'general';

        if ($type === 'comparison') {
            // Mock so sánh sản phẩm
            $products = $context['products'] ?? [];
            if (empty($products)) {
                return "🤖 Không có đủ dữ liệu sản phẩm để so sánh.";
            }

            // Tìm sản phẩm rẻ nhất và cấu hình mạnh nhất
            $cheapest = null;
            $bestPerformance = null;
            $maxScore = -1;

            foreach ($products as $p) {
                if ($cheapest === null || $p['price'] < $cheapest['price']) {
                    $cheapest = $p;
                }
                
                // Tính điểm cấu hình sơ bộ dựa trên specs
                $specs = json_decode($p['specs'] ?? '{}', true) ?: [];
                $cpu = strtolower($specs['CPU'] ?? $specs['cpu'] ?? '');
                $ram = (int)filter_var($specs['RAM'] ?? $specs['ram'] ?? '8', FILTER_SANITIZE_NUMBER_INT);
                $score = $ram * 2;
                if (strpos($cpu, 'i7') !== false || strpos($cpu, 'ryzen 7') !== false || strpos($cpu, 'ultra 7') !== false) {
                    $score += 20;
                } elseif (strpos($cpu, 'i5') !== false || strpos($cpu, 'ryzen 5') !== false || strpos($cpu, 'ultra 5') !== false) {
                    $score += 10;
                }
                
                if ($score > $maxScore) {
                    $maxScore = $score;
                    $bestPerformance = $p;
                }
            }

            $recProduct = $bestPerformance ?? $products[0];
            $analysisText = "### 🤖 Đánh giá chi tiết từ trợ lý AI (Chế độ Demo)\n\n";
            $analysisText .= "#### 1. Khác biệt chính ở đâu?\n";
            foreach ($products as $p) {
                $specs = json_decode($p['specs'] ?? '{}', true) ?: [];
                $specsStr = implode(', ', array_map(function($k, $v) { return "$k: $v"; }, array_keys($specs), $specs));
                $analysisText .= "• **" . $p['name'] . "**: Giá " . number_format($p['price'], 0, ',', '.') . "đ. Cấu hình chính: " . $specsStr . ".\n";
            }
            
            $analysisText .= "\n#### 2. So sánh Hiệu năng & Khả năng Tiết kiệm chi phí\n";
            $analysisText .= "• **Sản phẩm mạnh nhất**: **" . $recProduct['name'] . "** vượt trội nhờ trang bị vi xử lý hiệu năng cao, rất phù hợp cho công việc lập trình, đồ họa chuyên sâu hoặc chơi game nặng.\n";
            if ($cheapest && $cheapest['id'] !== $recProduct['id']) {
                $analysisText .= "• **Sản phẩm tiết kiệm nhất**: **" . $cheapest['name'] . "** là lựa chọn kinh tế hơn, giúp bạn tiết kiệm được " . number_format(abs($recProduct['price'] - $cheapest['price']), 0, ',', '.') . "đ.\n";
            }
            
            $analysisText .= "\n#### 3. Tư vấn đối tượng & Đáng tiền\n";
            $analysisText .= "• Cả hai dòng máy đều có chất lượng hoàn thiện tốt chính hãng TechPilot.\n";
            $analysisText .= "• Nếu ngân sách của bạn dư dả, việc đầu tư thêm tiền nâng cấp lên dòng máy mạnh hơn là **hoàn toàn đáng giá** để có hiệu năng xử lý lâu dài trong 3-5 năm.\n\n";
            $analysisText .= "👉 **Đề xuất của AI**: Bạn nên lựa chọn **" . $recProduct['name'] . "**.";

            // Trả về JSON để controller dễ tách
            return json_encode([
                'analysis' => $analysisText,
                'recommended_id' => $recProduct['id']
            ], JSON_UNESCAPED_UNICODE);
        }

        if ($type === 'recommendation') {
            // Mock gợi ý sản phẩm theo nhu cầu
            $candidates = $context['candidates'] ?? [];
            if (empty($candidates)) {
                return json_encode([
                    'best_id' => null,
                    'saving_id' => null,
                    'perf_id' => null,
                    'reasons' => "Không tìm thấy sản phẩm phù hợp trong hệ thống.",
                    'tradeoffs' => "Không có sản phẩm nào nằm trong khoảng ngân sách này."
                ], JSON_UNESCAPED_UNICODE);
            }

            // Sắp xếp các ứng viên để chọn
            // Phù hợp nhất: phần tử đầu tiên (đã được chấm điểm cao nhất bởi PHP)
            $best = $candidates[0];
            
            // Tiết kiệm nhất: có giá thấp nhất trong danh sách
            $saving = $candidates[0];
            foreach ($candidates as $c) {
                if ($c['price'] < $saving['price']) {
                    $saving = $c;
                }
            }

            // Hiệu năng cao nhất: phần tử có cấu hình CPU/RAM cao nhất
            $perf = $candidates[0];
            $maxScore = -1;
            foreach ($candidates as $c) {
                $specs = json_decode($c['specs'] ?? '{}', true) ?: [];
                $cpu = strtolower($specs['CPU'] ?? $specs['cpu'] ?? '');
                $ram = (int)filter_var($specs['RAM'] ?? $specs['ram'] ?? '8', FILTER_SANITIZE_NUMBER_INT);
                $score = $ram;
                if (strpos($cpu, 'i7') !== false || strpos($cpu, 'ryzen 7') !== false) $score += 15;
                if ($score > $maxScore) {
                    $maxScore = $score;
                    $perf = $c;
                }
            }

            $reasons = "🤖 **Lý do đề xuất từ AI (Demo Mode)**:\n" .
                       "1. **" . $best['name'] . "** được chọn làm **Phù hợp nhất** vì cấu hình cân bằng tối ưu giữa hiệu năng văn phòng/lập trình và tầm giá ngân sách.\n" .
                       "2. **" . $saving['name'] . "** là **Lựa chọn tiết kiệm**, giữ lại phần lớn các tính năng thiết yếu với giá thành mềm nhất.\n" .
                       "3. **" . $perf['name'] . "** mang tới **Hiệu năng cao nhất** giúp thực hiện các tác vụ đồ họa, xử lý luồng nặng mượt mà nhất.";

            $tradeoffs = "• Nếu chọn **" . $saving['name'] . "** (Tiết kiệm), bạn phải chấp nhận dung lượng lưu trữ hoặc đa nhiệm RAM cơ bản.\n" .
                         "• Nếu chọn **" . $perf['name'] . "** (Hiệu năng), bạn sẽ cần chi thêm ngân sách lớn hơn.";

            return json_encode([
                'best_id' => $best['id'],
                'saving_id' => $saving['id'],
                'perf_id' => $perf['id'],
                'reasons' => $reasons,
                'tradeoffs' => $tradeoffs
            ], JSON_UNESCAPED_UNICODE);
        }

        if ($type === 'product_chat') {
            // Mock chat theo từng sản phẩm
            $product = $context['product'] ?? [];
            $question = strtolower($prompt);
            
            $name = $product['name'] ?? 'sản phẩm';
            $specs = json_decode($product['specs'] ?? '{}', true) ?: [];
            $cpu = $specs['CPU'] ?? $specs['cpu'] ?? 'Intel/AMD';
            $ram = $specs['RAM'] ?? $specs['ram'] ?? '8GB';
            $vga = $specs['VGA'] ?? $specs['vga'] ?? 'Onboard';

            if (strpos($question, 'game') !== false || strpos($question, 'fps') !== false || strpos($question, 'valorant') !== false || strpos($question, 'cyberpunk') !== false) {
                $isGamingVga = (strpos(strtolower($vga), 'rtx') !== false || strpos(strtolower($vga), 'gtx') !== false || strpos(strtolower($vga), 'radeon rx') !== false);
                if ($isGamingVga) {
                    return "🤖 Mẫu **$name** được trang bị Card đồ họa rời **$vga** cùng CPU **$cpu**. Cấu hình này chiến mượt mà các tựa game Esports hiện nay (Valorant, LOL đạt trên 150-200 FPS ở cấu hình cao) và chơi được các game AAA nặng ở mức thiết lập đồ họa hợp lý.";
                } else {
                    return "🤖 Mẫu **$name** sử dụng Card đồ họa tích hợp. Bạn có thể chơi mượt các game Esport như Liên Minh Huyền Thoại (LOL) hoặc Valorant ở thiết lập cấu hình Thấp/Trung bình (đạt khoảng 60-80 FPS). Tuy nhiên, máy không phù hợp cho các game đồ họa 3D AAA nặng.";
                }
            }

            if (strpos($question, 'do hoa') !== false || strpos($question, 'photoshop') !== false || strpos($question, 'edit') !== false || strpos($question, 'premiere') !== false) {
                $ramInt = (int)filter_var($ram, FILTER_SANITIZE_NUMBER_INT);
                if ($ramInt >= 16) {
                    return "🤖 Hoàn toàn mượt mà! Với **$ram RAM** và CPU dòng mạnh mẽ, **$name** đáp ứng xuất sắc các phần mềm đồ họa như Photoshop, Illustrator, Premiere, AutoCAD. Bạn có thể đa nhiệm dựng hình mà không lo tràn bộ nhớ.";
                } else {
                    return "🤖 Mẫu **$name** với RAM 8GB có thể chạy được Photoshop, Lightroom hoặc chỉnh sửa video ngắn cơ bản. Tuy nhiên để làm việc đồ họa chuyên nghiệp, bạn nên nâng cấp RAM lên 16GB để có trải nghiệm mượt mà nhất.";
                }
            }

            if (strpos($question, 'pin') !== false || strpos($question, 'dung luong') !== false || strpos($question, 'battery') !== false) {
                return "🤖 **$name** có thời lượng sử dụng pin thực tế đạt khoảng **4 - 6 tiếng** cho các tác vụ văn phòng cơ bản, nghe nhạc và lướt web. Thời lượng pin sẽ thay đổi tùy thuộc vào độ sáng màn hình và tác vụ sử dụng.";
            }

            if (strpos($question, 'ram') !== false || strpos($question, 'nang cap') !== false || strpos($question, 'o cung') !== false || strpos($question, 'ssd') !== false) {
                return "🤖 Về khả năng nâng cấp của **$name**:\n• **RAM**: Hiện có sẵn **$ram**. Bạn có thể nâng cấp thêm (hỗ trợ khe cắm rời).\n• **SSD**: Ổ cứng SSD NVMe tốc độ cao cho tốc độ khởi động máy và ứng dụng chỉ mất vài giây.";
            }

            // Trả lời chung chung
            return "🤖 Cảm ơn bạn đã hỏi về sản phẩm **$name**!\n\nChiếc máy này sở hữu cấu hình gồm CPU **$cpu**, RAM **$ram**, Card đồ họa **$vga**. Máy hiện đang còn hàng tại showroom TechPilot với mức giá vô cùng ưu đãi. Bạn cần hỏi thêm chi tiết nào khác về máy không?";
        }
        if ($type === 'general') {
            // Phân tích prompt để lấy session state hiện tại
            $budget = 'Chưa biết';
            $deviceType = 'Chưa biết';
            $purpose = 'Chưa biết';
            $software = 'Chưa biết';
            $priority = 'Chưa biết';

            if (preg_match('/-\s*Ngân sách:\s*(.*)/i', $prompt, $m)) $budget = trim($m[1]);
            if (preg_match('/-\s*Loại máy:\s*(.*)/i', $prompt, $m)) $deviceType = trim($m[1]);
            if (preg_match('/-\s*Mục đích:\s*(.*)/i', $prompt, $m)) $purpose = trim($m[1]);
            if (preg_match('/-\s*Phần mềm:\s*(.*)/i', $prompt, $m)) $software = trim($m[1]);
            if (preg_match('/-\s*Ưu tiên:\s*(.*)/i', $prompt, $m)) $priority = trim($m[1]);

            // Trích xuất sản phẩm thật từ CSDL trong prompt
            $lines = explode("\n", $prompt);
            $foundProducts = [];
            foreach ($lines as $line) {
                if (preg_match('/-\s*Tên:\s*(.*?)\s*\(ID:\s*(\d+)\)\.\s*Giá:\s*(.*?)\.\s*Cấu hình:\s*(.*)/i', $line, $m)) {
                    $foundProducts[] = [
                        'id' => (int)$m[2],
                        'name' => trim($m[1]),
                        'price' => trim($m[3]),
                        'specs' => trim($m[4])
                    ];
                }
            }

            // Kịch bản hội thoại tự nhiên của chuyên viên tư vấn TechPilot
            if ($budget === 'Chưa biết') {
                return "Tuyệt vời, mình rất vui được hỗ trợ bạn tìm máy! 😊 Trước tiên, để mình dễ khoanh vùng các mẫu phù hợp nhất, bạn dự kiến đầu tư khoảng bao nhiêu ngân sách cho chiếc máy này nhỉ?";
            }

            if ($deviceType === 'Chưa biết') {
                return "Cảm ơn bạn. Với tầm giá {$budget} thì mình đang có khá nhiều lựa chọn tốt đấy. Bạn đang muốn tìm mua Laptop di động tiện lợi hay một bộ PC để bàn hiệu năng cao?";
            }

            if ($purpose === 'Chưa biết') {
                return "Đã rõ là bạn cần {$deviceType}. Để mình chọn đúng cấu hình tối ưu, bạn mua máy này chủ yếu phục vụ nhu cầu gì nhỉ? Chơi game 🎮, lập trình 💻, thiết kế đồ họa 🎨 hay làm việc văn phòng cơ bản?";
            }

            if ($software === 'Chưa biết') {
                $emoji = ($purpose === 'Chơi game') ? '🎮' : (($purpose === 'Lập trình') ? '💻' : '🎨');
                return "Lựa chọn hay đấy! {$emoji} Để cấu hình chạy mượt mà nhất, bạn thường sử dụng các phần mềm hay tựa game cụ thể nào vậy?";
            }

            if ($priority === 'Chưa biết') {
                return "Mình hiểu rồi. Với phần mềm/game bạn dùng, ngoài cấu hình ra thì bạn muốn ưu tiên yếu tố nào hơn? Cần hiệu năng tối đa ⚡, pin trâu 🔋, máy gọn nhẹ dễ mang đi 🎒 hay tiết kiệm chi phí nhất?";
            }

            // Đầy đủ thông tin -> Đề xuất sản phẩm thật kèm lý do và ID khuyến nghị
            if (!empty($foundProducts)) {
                $recIds = [];
                $recsDescription = "";
                foreach (array_slice($foundProducts, 0, 3) as $p) {
                    $recIds[] = $p['id'];
                    $recsDescription .= "🥇 **{$p['name']}**\n- Giá: {$p['price']}\n- Cấu hình: {$p['specs']}\n\n";
                }
                
                return "Tuyệt vời! Sau khi tổng hợp đầy đủ nhu cầu của bạn, mình nghĩ đây là những lựa chọn phù hợp nhất:\n\n" . 
                       $recsDescription . 
                       "✔ Đảm bảo chạy tốt các phần mềm của bạn trong tầm giá {$budget}.\n" .
                       "✔ Thiết kế đẹp và độ bền tối ưu.\n\n" .
                       "Bạn thấy ưng ý mẫu nào ở trên không, hay cần mình so sánh thêm chi tiết sản phẩm nào ạ? 😊\n\n[RECOMMENDED_IDS: " . implode(', ', $recIds) . "]";
            }

            return "Dạ mình hiểu rồi. Bạn cần tìm máy cấu hình tốt phục vụ các phần mềm của bạn. Hiện tại showroom TechPilot đang có sẵn khá nhiều mẫu máy chất lượng. Bạn có muốn ghé qua trực tiếp hay cần mình tư vấn tiếp ở đây ạ?";
        }

        return "🤖 Xin chào! Mình là trợ lý AI TechPilot. Mình có thể giúp gì cho bạn hôm nay?";
    }
}

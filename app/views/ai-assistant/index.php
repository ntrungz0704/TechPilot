<?php include ROOT_PATH . '/app/views/layouts/header.php'; ?>

<!-- Nhập Google Font và CSS riêng cho AI Assistant -->
<style>
    .ai-assistant-container {
        max-width: 1000px;
        margin: 40px auto 60px auto;
        padding: 0 16px;
        font-family: 'Inter', 'Outfit', sans-serif;
    }

    /* Glassmorphism Card cho Wizard */
    .ai-card {
        background: var(--surface-card, #FFFFFF);
        border: 1px solid var(--border, #E2E8F0);
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
        padding: 40px;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .dark-mode .ai-card {
        background: #1E293B;
        border-color: #334155;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    }

    /* Gradient Header */
    .ai-header-tag {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 30px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        background: linear-gradient(135deg, rgba(10, 91, 255, 0.1) 0%, rgba(10, 91, 255, 0.2) 100%);
        color: var(--primary);
        margin-bottom: 15px;
    }

    .ai-title {
        font-size: 28px;
        font-weight: 800;
        margin: 0 0 10px 0;
        background: linear-gradient(135deg, var(--text-primary, #0F172A) 30%, var(--primary) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .ai-subtitle {
        color: var(--text-secondary, #64748B);
        font-size: 15px;
        margin-bottom: 40px;
    }

    /* Steps Tracker */
    .ai-steps {
        display: flex;
        justify-content: space-between;
        margin-bottom: 40px;
        position: relative;
    }
    .ai-steps::before {
        content: '';
        position: absolute;
        top: 15px; left: 0; right: 0;
        height: 2px;
        background-color: var(--border, #E2E8F0);
        z-index: 1;
    }
    .dark-mode .ai-steps::before {
        background-color: #334155;
    }
    .ai-step-item {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 20%;
    }
    .ai-step-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: var(--surface-card, #FFFFFF);
        border: 2px solid var(--border, #E2E8F0);
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 13px;
        transition: all 0.3s;
    }
    .dark-mode .ai-step-circle {
        background-color: #1E293B;
        border-color: #334155;
    }
    .ai-step-label {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-secondary);
        margin-top: 8px;
        text-align: center;
    }
    .ai-step-item.active .ai-step-circle {
        border-color: var(--primary);
        background-color: var(--primary);
        color: #FFFFFF;
        box-shadow: 0 0 12px rgba(10, 91, 255, 0.4);
    }
    .ai-step-item.active .ai-step-label {
        color: var(--primary);
        font-weight: 700;
    }
    .ai-step-item.completed .ai-step-circle {
        border-color: #10B981;
        background-color: #10B981;
        color: #FFFFFF;
    }

    /* Wizard Content Panels */
    .ai-panel {
        display: none;
        animation: fadeIn 0.4s ease forwards;
    }
    .ai-panel.active {
        display: block;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Option Cards Grids */
    .options-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
        margin-bottom: 30px;
    }
    .option-card {
        border: 2px solid var(--border, #E2E8F0);
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
    }
    .dark-mode .option-card {
        border-color: #334155;
    }
    .option-card i {
        font-size: 28px;
        color: var(--text-secondary);
        transition: color 0.2s;
    }
    .option-card span {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-primary);
    }
    .option-card p {
        font-size: 11px;
        color: var(--text-secondary);
        margin: 0;
    }
    .option-card:hover {
        border-color: var(--primary);
        transform: translateY(-2px);
    }
    .option-card:hover i {
        color: var(--primary);
    }
    .option-card.selected {
        border-color: var(--primary);
        background-color: rgba(10, 91, 255, 0.03);
        box-shadow: 0 8px 20px rgba(10, 91, 255, 0.08);
    }
    .option-card.selected i {
        color: var(--primary);
    }

    /* Inputs styling */
    .ai-input-group {
        margin-bottom: 24px;
    }
    .ai-input-group label {
        display: block;
        font-size: 13.5px;
        font-weight: 700;
        margin-bottom: 8px;
        color: var(--text-primary);
    }
    .ai-input-group input, .ai-input-group textarea {
        width: 100%;
        padding: 12px 16px;
        border-radius: 10px;
        border: 1px solid var(--border, #E2E8F0);
        font-size: 14px;
        outline: none;
        background-color: var(--surface-card, #FFFFFF);
        color: var(--text-primary);
        box-sizing: border-box;
    }
    .dark-mode .ai-input-group input, .dark-mode .ai-input-group textarea {
        border-color: #334155;
        background-color: #1E293B;
    }
    .ai-input-group input:focus, .ai-input-group textarea:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(10, 91, 255, 0.15);
    }

    /* Controls Buttons */
    .ai-controls {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
        border-top: 1px solid var(--border, #E2E8F0);
        padding-top: 20px;
    }
    .dark-mode .ai-controls {
        border-color: #334155;
    }

    /* Recommendation Cards styling */
    .recs-container {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-top: 40px;
    }
    @media (max-width: 900px) {
        .recs-container {
            grid-template-columns: 1fr;
        }
    }
    .rec-card {
        border: 1px solid var(--border, #E2E8F0);
        border-radius: 20px;
        background-color: var(--surface-card, #FFFFFF);
        overflow: hidden;
        position: relative;
        display: flex;
        flex-direction: column;
        box-shadow: 0 10px 30px rgba(0,0,0,0.02);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .dark-mode .rec-card {
        border-color: #334155;
        background-color: #1E293B;
    }
    .rec-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.06);
    }
    
    /* Ribbons / Badges */
    .rec-badge {
        position: absolute;
        top: 15px; left: 15px;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #FFFFFF;
        z-index: 10;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .badge-best { background: linear-gradient(135deg, #10B981 0%, #059669 100%); }
    .badge-saving { background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%); }
    .badge-perf { background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%); }

    /* Circular Suitability Score */
    .suitability-circle-container {
        position: absolute;
        top: 15px; right: 15px;
        display: flex;
        flex-direction: column;
        align-items: center;
        z-index: 10;
    }
    .suitability-circle {
        width: 44px; height: 44px;
        border-radius: 50%;
        background-color: #F8FAFC;
        border: 2px solid #10B981;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 11.5px;
        color: #10B981;
        box-shadow: 0 4px 10px rgba(16, 185, 129, 0.15);
    }
    .dark-mode .suitability-circle {
        background-color: #0F172A;
    }

    /* Wishlist Heart Toggle */
    .favorite-toggle-btn {
        position: absolute;
        bottom: 80px; right: 15px;
        background: var(--surface-card);
        border: 1px solid var(--border);
        color: #9CA3AF;
        border-radius: 50%;
        width: 38px; height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 10;
        transition: all 0.2s;
    }
    .dark-mode .favorite-toggle-btn {
        background-color: #1E293B;
        border-color: #334155;
    }
    .favorite-toggle-btn:hover {
        color: #EF4444;
        border-color: #EF4444;
        background-color: rgba(239, 68, 68, 0.05);
    }
    .favorite-toggle-btn.liked {
        color: #EF4444;
        border-color: #EF4444;
        background-color: rgba(239, 68, 68, 0.1);
    }
</style>

<div class="ai-assistant-container">
    <div class="ai-card" id="wizardCard">
        <!-- Tag -->
        <span class="ai-header-tag"><i class="fa-solid fa-wand-magic-sparkles" style="margin-right: 6px;"></i> TechPilot Smart AI</span>
        <h3 class="ai-title">Trợ lý ảo mua sắm AI</h3>
        <p class="ai-subtitle">Trả lời nhanh 5 câu hỏi khảo sát bên dưới để AI tìm kiếm và đề xuất cấu hình hoàn hảo nhất cho bạn từ kho hàng thực tế của chúng tôi.</p>

        <!-- Tracker -->
        <div class="ai-steps">
            <div class="ai-step-item active" id="stepIndicator-1">
                <span class="ai-step-circle">1</span>
                <span class="ai-step-label">Ngân sách</span>
            </div>
            <div class="ai-step-item" id="stepIndicator-2">
                <span class="ai-step-circle">2</span>
                <span class="ai-step-label">Loại máy</span>
            </div>
            <div class="ai-step-item" id="stepIndicator-3">
                <span class="ai-step-circle">3</span>
                <span class="ai-step-label">Mục đích</span>
            </div>
            <div class="ai-step-item" id="stepIndicator-4">
                <span class="ai-step-circle">4</span>
                <span class="ai-step-label">Ưu tiên</span>
            </div>
            <div class="ai-step-item" id="stepIndicator-5">
                <span class="ai-step-circle">5</span>
                <span class="ai-step-label">Hãng & Bộ lọc</span>
            </div>
        </div>

        <!-- Form bắt đầu -->
        <form id="aiAssistantForm" onsubmit="return false;">
            <!-- PANEL 1: Ngân sách -->
            <div class="ai-panel active" id="panel-1">
                <h4 style="margin-bottom: 20px; font-weight: 700; color: var(--text-primary);">Ngân sách tối đa bạn sẵn sàng chi trả là bao nhiêu?</h4>
                <div class="options-grid">
                    <div class="option-card" onclick="selectOption('budget', 'under_10m', this)">
                        <i class="fa-solid fa-wallet"></i>
                        <span>Dưới 10 Triệu</span>
                        <p>Tiết kiệm tối đa</p>
                    </div>
                    <div class="option-card" onclick="selectOption('budget', '10_15m', this)">
                        <i class="fa-solid fa-scale-balanced"></i>
                        <span>10 - 15 Triệu</span>
                        <p>Tầm giá học tập tốt</p>
                    </div>
                    <div class="option-card selected" onclick="selectOption('budget', '15_25m', this)">
                        <i class="fa-solid fa-laptop-code"></i>
                        <span>15 - 25 Triệu</span>
                        <p>Hiệu năng trung bình khá</p>
                    </div>
                    <div class="option-card" onclick="selectOption('budget', '25_35m', this)">
                        <i class="fa-solid fa-gem"></i>
                        <span>25 - 35 Triệu</span>
                        <p>Cấu hình chuyên nghiệp</p>
                    </div>
                </div>
                <input type="hidden" name="budget" id="input-budget" value="15_25m">
            </div>

            <!-- PANEL 2: Nhóm sản phẩm -->
            <div class="ai-panel" id="panel-2">
                <h4 style="margin-bottom: 20px; font-weight: 700; color: var(--text-primary);">Bạn cần tìm dòng sản phẩm nào?</h4>
                <div class="options-grid">
                    <div class="option-card selected" onclick="selectOption('category', 'laptop', this)">
                        <i class="fa-solid fa-laptop"></i>
                        <span>Laptop</span>
                        <p>Di động, tiện mang đi</p>
                    </div>
                    <div class="option-card" onclick="selectOption('category', 'pc', this)">
                        <i class="fa-solid fa-desktop"></i>
                        <span>Máy bộ PC</span>
                        <p>Bàn làm việc cố định</p>
                    </div>
                    <div class="option-card" onclick="selectOption('category', 'monitor', this)">
                        <i class="fa-solid fa-tv"></i>
                        <span>Màn hình (LCD)</span>
                        <p>Hiển thị sắc nét</p>
                    </div>
                    <div class="option-card" onclick="selectOption('category', 'gear', this)">
                        <i class="fa-solid fa-keyboard"></i>
                        <span>Phụ kiện / Gear</span>
                        <p>Chuột, phím, tai nghe</p>
                    </div>
                </div>
                <input type="hidden" name="category" id="input-category" value="laptop">
            </div>

            <!-- PANEL 3: Nhu cầu chính & phần mềm -->
            <div class="ai-panel" id="panel-3">
                <h4 style="margin-bottom: 20px; font-weight: 700; color: var(--text-primary);">Mục đích sử dụng chính của bạn là gì?</h4>
                <div class="options-grid">
                    <div class="option-card selected" onclick="selectOption('purpose', 'general', this)">
                        <i class="fa-solid fa-briefcase"></i>
                        <span>Học tập & Văn phòng</span>
                        <p>Word, Excel, xem phim</p>
                    </div>
                    <div class="option-card" onclick="selectOption('purpose', 'gaming', this)">
                        <i class="fa-solid fa-gamepad"></i>
                        <span>Chơi Game</span>
                        <p>Valorant, LOL, AAA nặng</p>
                    </div>
                    <div class="option-card" onclick="selectOption('purpose', 'design', this)">
                        <i class="fa-solid fa-palette"></i>
                        <span>Đồ họa & Dựng phim</span>
                        <p>Photoshop, Premiere</p>
                    </div>
                    <div class="option-card" onclick="selectOption('purpose', 'coding', this)">
                        <i class="fa-solid fa-code"></i>
                        <span>Lập trình / Coder</span>
                        <p>Docker, VS Code, Android Studio</p>
                    </div>
                </div>
                <input type="hidden" name="purpose" id="input-purpose" value="general">

                <div class="ai-input-group">
                    <label for="input-software">Phần mềm hoặc tựa game bạn sẽ sử dụng thường xuyên nhất:</label>
                    <input type="text" name="software" id="input-software" placeholder="Ví dụ: Photoshop, AutoCAD, Valorant, CS2...">
                </div>
            </div>

            <!-- PANEL 4: Tiêu chí ưu tiên -->
            <div class="ai-panel" id="panel-4">
                <h4 style="margin-bottom: 20px; font-weight: 700; color: var(--text-primary);">Tiêu chí phần cứng nào bạn ưu tiên hàng đầu?</h4>
                <div class="options-grid">
                    <div class="option-card selected" onclick="selectOption('priority', 'performance', this)">
                        <i class="fa-solid fa-gauge-high"></i>
                        <span>Hiệu năng cực đỉnh</span>
                        <p>CPU, VGA khỏe nhất tầm giá</p>
                    </div>
                    <div class="option-card" onclick="selectOption('priority', 'lightweight', this)">
                        <i class="fa-solid fa-feather"></i>
                        <span>Mỏng nhẹ, di động</span>
                        <p>Trọng lượng nhẹ dưới 1.5kg</p>
                    </div>
                    <div class="option-card" onclick="selectOption('priority', 'battery', this)">
                        <i class="fa-solid fa-battery-three-quarters"></i>
                        <span>Thời lượng pin dài</span>
                        <p>Thời lượng sử dụng pin trâu</p>
                    </div>
                    <div class="option-card" onclick="selectOption('priority', 'upgrade', this)">
                        <i class="fa-solid fa-screwdriver-wrench"></i>
                        <span>Dễ dàng nâng cấp</span>
                        <p>Có khe cắm RAM, SSD mở rộng</p>
                    </div>
                </div>
                <input type="hidden" name="priority" id="input-priority" value="performance">
            </div>

            <!-- PANEL 5: Hãng yêu thích & Bộ lọc phụ -->
            <div class="ai-panel" id="panel-5">
                <h4 style="margin-bottom: 20px; font-weight: 700; color: var(--text-primary);">Cấu hình hãng thương hiệu yêu thích và loại trừ:</h4>
                <div class="ai-input-group">
                    <label for="input-brand">Thương hiệu bạn yêu thích nhất (nếu có):</label>
                    <input type="text" name="brand" id="input-brand" placeholder="Ví dụ: ASUS, Dell, MSI, Gigabyte...">
                </div>

                <div class="ai-input-group">
                    <label for="input-excluded">Thương hiệu hoặc cấu hình bạn MUỐN LOẠI TRỪ (không mua):</label>
                    <input type="text" name="excluded" id="input-excluded" placeholder="Ví dụ: Apple, Celeron, 4GB RAM...">
                </div>
            </div>

            <!-- Điều khiển chuyển trang -->
            <div class="ai-controls">
                <button type="button" class="btn btn--secondary" id="btnPrev" style="visibility: hidden; padding: 10px 24px;" onclick="prevStep()"><i class="fa-solid fa-arrow-left" style="margin-right: 6px;"></i> Quay lại</button>
                <button type="button" class="btn" id="btnNext" style="padding: 10px 24px;" onclick="nextStep()">Tiếp tục <i class="fa-solid fa-arrow-right" style="margin-left: 6px;"></i></button>
            </div>
        </form>
    </div>

    <!-- KHUNG KẾT QUẢ AI ĐỀ XUẤT (SẼ HIỂN THỊ KHI SUBMIT) -->
    <div id="aiRecsResult" style="display: none;">
        <div class="section__head" style="margin-top: 40px; margin-bottom: 10px;">
            <h3>🤖 Kết quả phân tích & đề xuất tối ưu từ AI</h3>
            <p style="color: var(--text-secondary); font-size: 14.5px; margin: 5px 0 0 0;">Dựa trên hồ sơ khảo sát của bạn, Trợ lý AI đã lọc ra 3 sản phẩm phù hợp nhất còn hàng trong hệ thống.</p>
        </div>

        <div class="recs-container" id="recsCardsContainer">
            <!-- 3 card sản phẩm Phù Hợp, Tiết Kiệm, Hiệu Năng Cao sẽ được nạp động -->
        </div>

        <!-- Khung lý do và điểm đánh đổi từ AI -->
        <div style="margin-top: 30px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px; background: var(--bg-white); border: 1px solid var(--border); border-radius: 16px; padding: 24px; box-shadow: var(--shadow-card);">
            <div>
                <h4 style="margin: 0 0 12px 0; color: #1E3A8A; font-weight: 700; font-size: 15px;"><i class="fa-solid fa-circle-info" style="color: var(--primary); margin-right: 6px;"></i> Đánh giá tổng quan từ AI</h4>
                <div id="aiRecsReasons" style="font-size: 13.5px; line-height: 1.6; color: var(--text-secondary);"></div>
            </div>
            <div>
                <h4 style="margin: 0 0 12px 0; color: #991B1B; font-weight: 700; font-size: 15px;"><i class="fa-solid fa-triangle-exclamation" style="color: #EF4444; margin-right: 6px;"></i> Điểm cần cân nhắc / đánh đổi</h4>
                <div id="aiRecsTradeoffs" style="font-size: 13.5px; line-height: 1.6; color: var(--text-secondary);"></div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 30px; margin-bottom: 50px;">
            <button class="btn btn--secondary" style="padding: 12px 30px;" onclick="resetWizard()"><i class="fa-solid fa-rotate-left"></i> Làm khảo sát mới</button>
        </div>
    </div>
</div>

<script>
    let currentStep = 1;
    const totalSteps = 5;

    // Chọn option trong grid
    function selectOption(inputName, optionValue, cardElement) {
        // Xóa class selected của các option cùng nhóm
        cardElement.parentNode.querySelectorAll('.option-card').forEach(card => {
            card.classList.remove('selected');
        });
        // Thêm selected cho card hiện tại
        cardElement.classList.add('selected');
        // Gán value cho input ẩn
        document.getElementById(`input-${inputName}`).value = optionValue;
    }

    // Nút Tiếp tục
    function nextStep() {
        if (currentStep < totalSteps) {
            // Đánh dấu step cũ hoàn thành
            document.getElementById(`stepIndicator-${currentStep}`).classList.remove('active');
            document.getElementById(`stepIndicator-${currentStep}`).classList.add('completed');

            // Chuyển panel
            document.getElementById(`panel-${currentStep}`).classList.remove('active');
            currentStep++;
            document.getElementById(`panel-${currentStep}`).classList.add('active');

            // Kích hoạt step mới
            document.getElementById(`stepIndicator-${currentStep}`).classList.add('active');

            // Cập nhật các nút điều khiển
            document.getElementById('btnPrev').style.visibility = 'visible';

            if (currentStep === totalSteps) {
                document.getElementById('btnNext').innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> Lấy đề xuất AI';
            }
        } else {
            // Thực hiện gửi form khảo sát (Submit)
            submitAiSurvey();
        }
    }

    // Nút Quay lại
    function prevStep() {
        if (currentStep > 1) {
            // Đánh dấu step cũ chưa hoàn thành
            document.getElementById(`stepIndicator-${currentStep}`).classList.remove('active');
            
            // Chuyển panel
            document.getElementById(`panel-${currentStep}`).classList.remove('active');
            currentStep--;
            document.getElementById(`panel-${currentStep}`).classList.add('active');

            // Kích hoạt step mới
            document.getElementById(`stepIndicator-${currentStep}`).classList.remove('completed');
            document.getElementById(`stepIndicator-${currentStep}`).classList.add('active');

            // Cập nhật các nút
            if (currentStep === 1) {
                document.getElementById('btnPrev').style.visibility = 'hidden';
            }
            document.getElementById('btnNext').innerHTML = 'Tiếp tục <i class="fa-solid fa-arrow-right" style="margin-left: 6px;"></i>';
        }
    }

    // Submit form khảo sát bằng AJAX
    function submitAiSurvey() {
        const form = document.getElementById('aiAssistantForm');
        const formData = new FormData(form);
        const card = document.getElementById('wizardCard');

        // Hiển thị trạng thái loading skeleton bên trong wizard card
        card.innerHTML = `
            <div style="text-align: center; padding: 40px 0;">
                <i class="fa-solid fa-circle-notch fa-spin" style="font-size: 56px; color: var(--primary); margin-bottom: 20px; display: block;"></i>
                <h3 style="margin-bottom: 10px; font-weight: 800;">Trợ lý AI đang chấm điểm cấu hình...</h3>
                <p style="color: var(--text-secondary); max-width: 500px; margin: 0 auto; font-size: 14px;">Hệ thống đang truy cập cơ sở dữ liệu kho hàng, so sánh các linh kiện CPU/VGA, ước tính FPS game và gửi danh sách tối ưu nhất tới máy chủ AI để lập báo cáo...</p>
            </div>
        `;

        fetch('<?= url("ai/recommend") ?>', {
            method: 'POST',
            body: new URLSearchParams(formData)
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                // Ẩn wizard card và hiển thị kết quả
                card.style.display = 'none';
                document.getElementById('aiRecsResult').style.display = 'block';

                // Nạp lý do và điểm đánh đổi
                document.getElementById('aiRecsReasons').innerHTML = formatMarkdown(res.reasons);
                document.getElementById('aiRecsTradeoffs').innerHTML = formatMarkdown(res.tradeoffs);

                // Dựng card sản phẩm
                const cardsContainer = document.getElementById('recsCardsContainer');
                cardsContainer.innerHTML = ''; // Clear old content

                const types = [
                    { key: 'best', label: 'Phù hợp nhất', badgeClass: 'badge-best' },
                    { key: 'saving', label: 'Tiết kiệm', badgeClass: 'badge-saving' },
                    { key: 'perf', label: 'Hiệu năng cao', badgeClass: 'badge-perf' }
                ];

                types.forEach(t => {
                    const prod = res[t.key];
                    if (!prod) return;

                    // Build FPS list HTML
                    let fpsHtml = '';
                    if (prod.fps_list && Object.keys(prod.fps_list).length > 0) {
                        fpsHtml = `
                            <div style="margin-top: 15px; border-top: 1px dashed var(--border); padding-top: 10px;">
                                <span style="font-size: 12px; font-weight: 700; color: var(--text-primary); display:block; margin-bottom: 6px;">Ước tính FPS Game:</span>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4px; font-size: 11px;">
                                    ${Object.keys(prod.fps_list).slice(0, 4).map(k => `
                                        <div style="display:flex; justify-content:space-between; padding-right:8px;">
                                            <span style="color:var(--text-secondary);">${prod.fps_list[k].name}:</span>
                                            <strong style="color:#1E40AF;">${prod.fps_list[k].fps}</strong>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                        `;
                    }

                    const cardHtml = `
                        <div class="rec-card">
                            <!-- Badge loại đề xuất -->
                            <span class="rec-badge ${t.badgeClass}">${t.label}</span>

                            <!-- Điểm phù hợp -->
                            <div class="suitability-circle-container">
                                <div class="suitability-circle" title="Điểm phù hợp với nhu cầu của bạn">${prod.suitability_score}%</div>
                                <span style="font-size: 9px; font-weight:700; color: #10B981; margin-top:2px;">PHÙ HỢP</span>
                            </div>

                            <!-- Ảnh sản phẩm -->
                            <div style="padding: 50px 20px 20px 20px; text-align: center; background-color: #F8FAFC; border-bottom: 1px solid var(--border);">
                                <img src="<?= url('assets/images/') ?>${prod.image}" alt="${prod.name}" style="height: 120px; object-fit: contain;">
                            </div>

                            <!-- Nội dung chi tiết -->
                            <div style="padding: 20px; flex: 1; display: flex; flex-direction: column;">
                                <h4 style="font-size: 14.5px; font-weight: 700; margin: 0 0 8px 0; height: 40px; overflow: hidden; line-height: 1.4; color: var(--text-primary);">${prod.name}</h4>
                                
                                <div style="margin-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                                    <strong style="color: var(--primary); font-size: 17px;">${prod.price_formatted}</strong>
                                    <span class="badge--vfm ${prod.pp_ratio.class}" style="font-size:10px;">${prod.pp_ratio.label} (P/P)</span>
                                </div>

                                <!-- Cấu hình tóm tắt -->
                                <div style="font-size: 12px; color: var(--text-secondary); display:flex; flex-direction:column; gap:4px;">
                                    <div><i class="fa-solid fa-microchip" style="width: 16px;"></i> CPU: ${prod.specs.CPU}</div>
                                    <div><i class="fa-solid fa-memory" style="width: 16px;"></i> RAM: ${prod.specs.RAM}</div>
                                    <div><i class="fa-solid fa-hard-drive" style="width: 16px;"></i> SSD: ${prod.specs.SSD}</div>
                                    <div><i class="fa-solid fa-images" style="width: 16px;"></i> VGA: ${prod.specs.VGA.split(' ').slice(0,2).join(' ')}</div>
                                </div>

                                <!-- Ước tính FPS Game -->
                                ${fpsHtml}

                                <!-- Giá trị VFM -->
                                <div style="margin-top: 15px; background: rgba(16, 185, 129, 0.04); border-radius: 8px; padding: 8px; display:flex; align-items:center; justify-content:space-between; font-size:12px;">
                                    <span style="color: var(--text-secondary); font-weight: 600;">Độ đáng tiền (VFM):</span>
                                    <span style="color:#10B981; font-weight: 700;"><i class="fa-solid fa-star" style="color:#FBBF24;"></i> ${prod.vfm_score}/10</span>
                                </div>

                                <!-- Nút yêu thích trái tim -->
                                <button type="button" class="favorite-toggle-btn" id="favBtn-${prod.id}" onclick="saveFavoriteRecommendation(${prod.id})">
                                    <i class="fa-solid fa-heart"></i>
                                </button>

                                <!-- Nút hành động chân trang card -->
                                <div style="margin-top: auto; padding-top: 20px; display: grid; grid-template-columns: 1fr 1.2fr; gap: 8px;">
                                    <a href="<?= url('product/detail/') ?>${prod.slug}" class="btn btn--secondary btn--sm" style="text-align: center; height: 36px; line-height: 20px;">Chi tiết</a>
                                    <form method="post" action="<?= url('cart/add') ?>">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                        <input type="hidden" name="product_id" value="${prod.id}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn--sm" style="width:100%; height: 36px;"><i class="fa-solid fa-cart-plus"></i> Thêm giỏ</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    `;
                    cardsContainer.insertAdjacentHTML('beforeend', cardHtml);
                });

            } else {
                alert("Lỗi từ AI: " + res.message);
                resetWizard();
            }
        })
        .catch(err => {
            alert("Lỗi mạng khi tải đề xuất AI.");
            console.error(err);
            resetWizard();
        });
    }

    // Lưu gợi ý AI yêu thích
    function saveFavoriteRecommendation(productId) {
        const btn = document.getElementById(`favBtn-${productId}`);
        btn.disabled = true;

        const data = new URLSearchParams();
        data.append('product_id', productId);
        data.append('_csrf', '<?= $_SESSION["csrf_token"] ?? "" ?>');

        fetch('<?= url("ai/favorite") ?>', {
            method: 'POST',
            body: data
        })
        .then(res => res.json())
        .then(res => {
            btn.disabled = false;
            if (res.success) {
                btn.classList.add('liked');
                alert(res.message);
            } else {
                alert("Không thể lưu yêu thích: " + res.message);
            }
        })
        .catch(err => {
            btn.disabled = false;
            alert("Lỗi kết nối lưu yêu thích.");
        });
    }

    // Đưa Wizard về trạng thái đầu
    function resetWizard() {
        location.reload();
    }

    // Format Markdown
    function formatMarkdown(text) {
        return text
            .replace(/\n/g, '<br>')
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/• (.*?)(<br>|$)/g, '<li style="margin-left: 15px; margin-bottom: 4px;">$1</li>');
    }
</script>

<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>

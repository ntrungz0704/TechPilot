<?php
$aiConfig = require ROOT_PATH . '/config/ai-recommendation.php';
?>
<!-- Styles cho AI Assistant -->
<style>
    .ai-assistant-container {
        max-width: 1400px;
        width: 100%;
        margin: 20px auto 60px auto;
        padding: 0 20px;
        font-family: 'Inter', 'Outfit', sans-serif;
        box-sizing: border-box;
    }

    .ai-card {
        max-width: 960px;
        margin: 0 auto;
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
        margin-bottom: 35px;
    }

    /* Steps Tracker */
    .ai-steps {
        display: flex;
        justify-content: space-between;
        margin-bottom: 35px;
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

    /* Panels & Options Grid */
    .ai-panel {
        display: none;
        animation: fadeIn 0.4s ease forwards;
    }
    .ai-panel.active {
        display: block;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .options-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 25px;
    }

    .option-card {
        border: 2px solid var(--border, #E2E8F0);
        border-radius: 14px;
        padding: 20px;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: center;
        background: var(--surface-card, #FFFFFF);
    }
    .dark-mode .option-card {
        background: #1E293B;
        border-color: #334155;
    }
    .option-card:hover {
        border-color: var(--primary);
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(10, 91, 255, 0.08);
    }
    .option-card.selected {
        border-color: var(--primary);
        background-color: rgba(10, 91, 255, 0.04);
        box-shadow: 0 0 0 2px var(--primary);
    }
    .dark-mode .option-card.selected {
        background-color: rgba(10, 91, 255, 0.15);
    }
    .option-card i {
        font-size: 26px;
        color: var(--primary);
        margin-bottom: 10px;
        display: block;
    }
    .option-card span {
        font-weight: 700;
        font-size: 14px;
        display: block;
        color: var(--text-primary);
        margin-bottom: 4px;
    }
    .option-card p {
        font-size: 12px;
        color: var(--text-secondary);
        margin: 0;
        line-height: 1.3;
    }

    .subcat-pill-container {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 20px;
    }
    .subcat-pill {
        padding: 8px 18px;
        border-radius: 20px;
        border: 1px solid var(--border);
        background: #F8FAFC;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        color: var(--text-primary);
    }
    .subcat-pill.active {
        background: var(--primary);
        color: #FFFFFF;
        border-color: var(--primary);
    }

    .ai-input-group {
        margin-top: 20px;
    }
    .ai-input-group label {
        display: block;
        font-size: 13.5px;
        font-weight: 600;
        margin-bottom: 8px;
        color: var(--text-primary);
    }
    .ai-input-group input, .ai-input-group select {
        width: 100%;
        padding: 12px 16px;
        border-radius: 10px;
        border: 1px solid var(--border, #CBD5E1);
        font-size: 14px;
        background: var(--surface-card, #FFFFFF);
        color: var(--text-primary);
        outline: none;
    }
    .ai-input-group input:focus, .ai-input-group select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(10, 91, 255, 0.15);
    }

    .ai-controls {
        display: flex;
        justify-content: space-between;
        margin-top: 35px;
        padding-top: 20px;
        border-top: 1px solid var(--border);
    }

    /* Cards kết quả đề xuất */
    .recs-container {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
        margin-top: 25px;
    }
    @media (max-width: 1024px) {
        .recs-container {
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        }
    }
    .rec-card {
        border: 1px solid var(--border);
        border-radius: 20px;
        background: var(--surface-card, #FFFFFF);
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06);
        display: flex;
        flex-direction: column;
        position: relative;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .dark-mode .rec-card {
        background: #1E293B;
        border-color: #334155;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
    }
    .rec-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.12);
    }

    .rec-card__header {
        position: relative;
        padding: 48px 20px 20px;
        text-align: center;
        background: linear-gradient(180deg, #F1F5F9 0%, #FFFFFF 100%);
        border-bottom: 1px solid var(--border, #E2E8F0);
    }
    .dark-mode .rec-card__header {
        background: linear-gradient(180deg, #0F172A 0%, #1E293B 100%);
        border-bottom-color: #334155;
    }
    .rec-card__header img {
        height: 120px;
        object-fit: contain;
        filter: drop-shadow(0 4px 12px rgba(0,0,0,0.08));
        transition: transform 0.3s ease;
    }
    .rec-card:hover .rec-card__header img {
        transform: scale(1.05);
    }

    .rec-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        color: #FFFFFF;
        z-index: 3;
        letter-spacing: 0.3px;
    }
    .badge-best_fit { background: linear-gradient(135deg, #10B981, #059669); }
    .badge-best_value { background: linear-gradient(135deg, #3B82F6, #1D4ED8); }
    .badge-max_performance { background: linear-gradient(135deg, #8B5CF6, #6D28D9); }

    .rec-score-ring {
        position: absolute;
        top: 10px;
        right: 12px;
        width: 52px;
        height: 52px;
    }
    .rec-score-ring svg {
        width: 100%;
        height: 100%;
        transform: rotate(-90deg);
    }
    .rec-score-ring .ring-bg {
        fill: none;
        stroke: var(--border, #E2E8F0);
        stroke-width: 4;
    }
    .rec-score-ring .ring-fg {
        fill: none;
        stroke-width: 4;
        stroke-linecap: round;
        transition: stroke-dashoffset 1s ease;
    }
    .rec-score-ring .ring-label {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 14px;
        color: var(--text-primary);
        line-height: 1;
    }
    .rec-score-ring .ring-label small {
        font-size: 8px;
        font-weight: 600;
        color: var(--text-secondary);
        margin-top: 1px;
    }

    .rec-card__body {
        padding: 20px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .rec-card__name {
        font-size: 15px;
        font-weight: 700;
        margin: 0 0 8px 0;
        line-height: 1.45;
        color: var(--text-primary);
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .rec-card__price-row {
        display: flex;
        align-items: baseline;
        gap: 8px;
        margin-bottom: 16px;
    }
    .rec-card__price {
        color: var(--primary);
        font-weight: 800;
        font-size: 20px;
    }
    .rec-card__stock {
        font-size: 12px;
        color: #10B981;
        font-weight: 600;
    }

    /* Specs Grid */
    .rec-specs-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-bottom: 16px;
        padding: 14px;
        background: #F8FAFC;
        border-radius: 12px;
        border: 1px solid rgba(0,0,0,0.04);
    }
    .dark-mode .rec-specs-grid {
        background: #0F172A;
        border-color: #334155;
    }
    .rec-spec-item {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        font-size: 12.5px;
        line-height: 1.35;
    }
    .rec-spec-item i {
        color: var(--primary);
        font-size: 11px;
        margin-top: 2px;
        width: 14px;
        text-align: center;
        flex-shrink: 0;
    }
    .rec-spec-item .spec-label {
        color: var(--text-secondary);
        font-weight: 500;
        white-space: nowrap;
    }
    .rec-spec-item .spec-value {
        color: var(--text-primary);
        font-weight: 700;
    }

    /* Reasons & Tradeoffs */
    .rec-insights {
        margin-bottom: 16px;
    }
    .rec-insights-title {
        font-size: 12.5px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 6px;
    }
    .rec-insights-title.positive { color: #047857; }
    .rec-insights-title.caution { color: #B45309; }

    .rec-insights ul {
        margin: 0;
        padding-left: 16px;
        font-size: 12px;
        color: var(--text-secondary);
        line-height: 1.55;
    }
    .rec-insights ul li {
        margin-bottom: 3px;
    }

    .rec-card__actions {
        margin-top: auto;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        padding-top: 16px;
        border-top: 1px solid var(--border);
    }
    .rec-card__actions a,
    .rec-card__actions button {
        padding: 10px 12px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        text-align: center;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s ease;
        border: none;
    }
    .rec-btn-detail {
        background: var(--surface-card, #F1F5F9);
        color: var(--text-primary);
        border: 1px solid var(--border) !important;
    }
    .rec-btn-detail:hover {
        background: var(--primary);
        color: #FFFFFF;
        border-color: var(--primary) !important;
    }
    .rec-btn-compare {
        background: linear-gradient(135deg, #10B981, #059669);
        color: #FFFFFF;
    }
    .rec-btn-compare:hover {
        background: linear-gradient(135deg, #059669, #047857);
        transform: translateY(-1px);
    }

    @media (max-width: 768px) {
        .recs-container {
            grid-template-columns: 1fr;
        }
        .rec-specs-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="ai-assistant-container">
    <div class="ai-card" id="wizardCard">
        <span class="ai-header-tag"><i class="fa-solid fa-wand-magic-sparkles"></i> TechPilot AI 4.0</span>
        <h2 class="ai-title">Khảo Sát AI Gợi Ý Sản Phẩm Tối Ưu</h2>
        <p class="ai-subtitle">Trả lời 5 câu hỏi nhanh để Trợ lý AI phân tích và lọc ra 3 sản phẩm phù hợp nhất trong kho hàng.</p>

        <!-- Progress Steps -->
        <div class="ai-steps">
            <div class="ai-step-item active" id="stepIndicator-1">
                <div class="ai-step-circle">1</div>
                <div class="ai-step-label">Ngân sách</div>
            </div>
            <div class="ai-step-item" id="stepIndicator-2">
                <div class="ai-step-circle">2</div>
                <div class="ai-step-label">Loại máy</div>
            </div>
            <div class="ai-step-item" id="stepIndicator-3">
                <div class="ai-step-circle">3</div>
                <div class="ai-step-label">Mục đích</div>
            </div>
            <div class="ai-step-item" id="stepIndicator-4">
                <div class="ai-step-circle">4</div>
                <div class="ai-step-label">Ưu tiên</div>
            </div>
            <div class="ai-step-item" id="stepIndicator-5">
                <div class="ai-step-circle">5</div>
                <div class="ai-step-label">Bộ lọc</div>
            </div>
        </div>

        <form id="aiAssistantForm" onsubmit="return false;">
            <input type="hidden" name="_csrf" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="budget_code" id="input-budget_code" value="">
            <input type="hidden" name="category" id="input-category" value="laptop">
            <input type="hidden" name="subcategory" id="input-subcategory" value="">
            <input type="hidden" name="purpose" id="input-purpose" value="">
            <input type="hidden" name="priority" id="input-priority" value="">

            <!-- PANEL 1: Ngân sách -->
            <div class="ai-panel active" id="panel-1">
                <h4 style="margin-bottom: 20px; font-weight: 700; color: var(--text-primary);">Bước 1: Chọn khoảng ngân sách đầu tư của bạn:</h4>
                <div class="options-grid" id="grid-budget">
                    <?php foreach ($aiConfig['budgets'] as $code => $b): ?>
                        <div class="option-card" onclick="selectBudget('<?= $code ?>', this)">
                            <i class="fa-solid fa-coins"></i>
                            <span><?= e($b['label']) ?></span>
                            <p>Phân khúc <?= e($code) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- PANEL 2: Loại sản phẩm -->
            <div class="ai-panel" id="panel-2">
                <h4 style="margin-bottom: 20px; font-weight: 700; color: var(--text-primary);">Bước 2: Chọn dòng sản phẩm bạn cần tư vấn:</h4>
                <div class="options-grid">
                    <div class="option-card selected" onclick="selectCategory('laptop', this)">
                        <i class="fa-solid fa-laptop"></i>
                        <span>Laptop</span>
                        <p>Máy tính xách tay di động</p>
                    </div>
                    <div class="option-card" onclick="selectCategory('pc', this)">
                        <i class="fa-solid fa-desktop"></i>
                        <span>PC Lắp Sẵn</span>
                        <p>Máy bộ để bàn cố định</p>
                    </div>
                    <div class="option-card" onclick="selectCategory('monitor', this)">
                        <i class="fa-solid fa-tv"></i>
                        <span>Màn hình</span>
                        <p>Hiển thị đồ họa / Gaming</p>
                    </div>
                    <div class="option-card" onclick="selectCategory('gear', this)">
                        <i class="fa-solid fa-gamepad"></i>
                        <span>Gaming Gear</span>
                        <p>Bàn phím, chuột, tai nghe...</p>
                    </div>
                </div>

                <div id="subcatContainer" style="display: none; margin-top: 20px;">
                    <label style="font-size: 13.5px; font-weight: 700; margin-bottom: 10px; display: block;">Chọn loại Gear cụ thể:</label>
                    <div class="subcat-pill-container" id="subcatPills"></div>
                </div>
            </div>

            <!-- PANEL 3: Mục đích sử dụng -->
            <div class="ai-panel" id="panel-3">
                <h4 style="margin-bottom: 20px; font-weight: 700; color: var(--text-primary);" id="panel3Title">Bước 3: Chọn mục đích sử dụng chính:</h4>
                <div class="options-grid" id="grid-purposes"></div>

                <div class="ai-input-group">
                    <label for="input-software">Phần mềm hoặc tựa game bạn thường dùng nhất (nếu có):</label>
                    <input type="text" name="software" id="input-software" placeholder="Ví dụ: Premiere, AutoCAD, Docker, Wukong, Valorant...">
                </div>
            </div>

            <!-- PANEL 4: Tiêu chí ưu tiên động -->
            <div class="ai-panel" id="panel-4">
                <h4 style="margin-bottom: 20px; font-weight: 700; color: var(--text-primary);" id="panel4Title">Bước 4: Tiêu chí ưu tiên hàng đầu:</h4>
                <div class="options-grid" id="grid-priorities"></div>
            </div>

            <!-- PANEL 5: Hãng & Bộ lọc loại trừ -->
            <div class="ai-panel" id="panel-5">
                <h4 style="margin-bottom: 20px; font-weight: 700; color: var(--text-primary);">Bước 5: Thương hiệu và yêu cầu loại trừ:</h4>
                <div class="ai-input-group">
                    <label for="input-brand">Thương hiệu ưu tiên (nếu có):</label>
                    <input type="text" name="brand" id="input-brand" placeholder="Ví dụ: ASUS, MSI, Dell, Gigabyte, Razer...">
                </div>

                <div class="ai-input-group">
                    <label for="input-excluded">Thương hiệu hoặc yếu tố MUỐN LOẠI TRỪ:</label>
                    <input type="text" name="excluded" id="input-excluded" placeholder="Ví dụ: Apple, Celeron, 8GB RAM...">
                </div>
            </div>

            <!-- Nút điều hướng Wizard -->
            <div class="ai-controls">
                <button type="button" class="btn btn--secondary" id="btnPrev" style="visibility: hidden; padding: 10px 24px;" onclick="prevStep()"><i class="fa-solid fa-arrow-left" style="margin-right: 6px;"></i> Quay lại</button>
                <button type="button" class="btn" id="btnNext" style="padding: 10px 24px;" onclick="nextStep()">Tiếp tục <i class="fa-solid fa-arrow-right" style="margin-left: 6px;"></i></button>
            </div>
        </form>
    </div>

    <!-- Khung kết quả hiển thị 3 đề xuất -->
    <div id="aiRecsResult" style="display: none;">
        <div class="section__head" style="margin-top: 30px; margin-bottom: 10px;">
            <span class="ai-header-tag"><i class="fa-solid fa-check-double"></i> Kết quả đã phân tích</span>
            <h3 id="resultTitle">🤖 3 Đề Xuất Tối Ưu Từ TechPilot AI</h3>
            <p style="color: var(--text-secondary); font-size: 14.5px; margin: 5px 0 0 0;" id="resultSummary"></p>
        </div>

        <div class="recs-container" id="recsCardsContainer"></div>

        <div style="text-align: center; margin-top: 35px; margin-bottom: 50px;">
            <button class="btn btn--secondary" style="padding: 12px 30px;" onclick="resetWizard()"><i class="fa-solid fa-rotate-left"></i> Làm khảo sát mới</button>
        </div>
    </div>
</div>

<script>
    window.TP_AI_CONFIG = <?= json_encode($aiConfig, JSON_UNESCAPED_UNICODE) ?>;
    let currentStep = 1;
    const totalSteps = 5;

    /* ===== SPEC DISPLAY MAPPING ===== */
    const SPEC_MAP = {
        // Laptop / PC CPU
        'cpu_model':      { label: 'CPU',          icon: 'fa-microchip' },
        'CPU':            { label: 'CPU',          icon: 'fa-microchip' },
        'Bộ vi xử lý':   { label: 'CPU',          icon: 'fa-microchip' },
        // GPU
        'gpu_model':      { label: 'Card đồ họa',  icon: 'fa-display' },
        'VGA':            { label: 'Card đồ họa',  icon: 'fa-display' },
        'Card đồ họa':   { label: 'Card đồ họa',  icon: 'fa-display' },
        // RAM
        'ram_gb':         { label: 'RAM',           icon: 'fa-memory', suffix: 'GB' },
        'ram_type':       { label: 'Loại RAM',      icon: 'fa-memory' },
        'ram_speed_mhz':  { label: 'Bus RAM',       icon: 'fa-gauge-high', suffix: 'MHz' },
        'RAM':            { label: 'RAM',           icon: 'fa-memory' },
        'Bộ nhớ RAM':    { label: 'RAM',           icon: 'fa-memory' },
        // Storage
        'storage':        { label: 'Ổ cứng',        icon: 'fa-hard-drive' },
        'SSD':            { label: 'Ổ cứng',        icon: 'fa-hard-drive' },
        'Ổ cứng':        { label: 'Ổ cứng',        icon: 'fa-hard-drive' },
        'Lưu trữ':       { label: 'Ổ cứng',        icon: 'fa-hard-drive' },
        // Display
        'Màn hình':       { label: 'Màn hình',      icon: 'fa-tv' },
        'screen_size':    { label: 'Màn hình',      icon: 'fa-tv' },
        'display':        { label: 'Màn hình',      icon: 'fa-tv' },
        'panel_type':     { label: 'Tấm nền',       icon: 'fa-tv' },
        'resolution':     { label: 'Độ phân giải',  icon: 'fa-expand' },
        'refresh_rate':   { label: 'Tần số quét',   icon: 'fa-bolt' },
        // Mainboard
        'mainboard_model':{ label: 'Bo mạch chủ',   icon: 'fa-server' },
        'Mainboard':      { label: 'Bo mạch chủ',   icon: 'fa-server' },
        // PSU
        'psu_wattage':    { label: 'Nguồn (PSU)',    icon: 'fa-plug', suffix: 'W' },
        'psu_certification':{ label: 'Chứng nhận PSU', icon: 'fa-certificate' },
        // Case
        'case_model':     { label: 'Thùng máy',     icon: 'fa-box' },
        // Cooler
        'cooler_model':   { label: 'Tản nhiệt',     icon: 'fa-fan' },
        // OS
        'os':             { label: 'Hệ điều hành',  icon: 'fa-windows', isBrand: true },
        'Hệ điều hành':  { label: 'Hệ điều hành',  icon: 'fa-windows', isBrand: true },
        // Battery (laptop)
        'Pin':            { label: 'Pin',            icon: 'fa-battery-full' },
        'battery':        { label: 'Pin',            icon: 'fa-battery-full' },
        // Weight
        'Trọng lượng':   { label: 'Trọng lượng',    icon: 'fa-weight-hanging' },
        'weight':         { label: 'Trọng lượng',    icon: 'fa-weight-hanging' },
        // Additional CPU/RAM/Display keys
        'cpu_cores':      { label: 'Số nhân CPU',   icon: 'fa-microchip' },
        'cpu_threads':    { label: 'Số luồng CPU',  icon: 'fa-microchip' },
        'max_ram_gb':     { label: 'RAM tối đa',    icon: 'fa-memory', suffix: 'GB' },
        'ram_slots':      { label: 'Khe RAM',       icon: 'fa-memory' },
        'bus_ram':        { label: 'Bus RAM',       icon: 'fa-gauge-high', suffix: 'MHz' },
        'Bus RAM':        { label: 'Bus RAM',       icon: 'fa-gauge-high' },
        'Loại RAM':       { label: 'Loại RAM',      icon: 'fa-memory' },
        'refresh_rate_hz':{ label: 'Tần số quét',   icon: 'fa-bolt', suffix: 'Hz' },
        'response_time_ms':{ label: 'Phản hồi',     icon: 'fa-stopwatch', suffix: 'ms' },
        'weight_kg':      { label: 'Trọng lượng',   icon: 'fa-weight-hanging', suffix: 'kg' },
        'battery_wh':     { label: 'Dung lượng pin',icon: 'fa-battery-full', suffix: 'Wh' },
        // Upgrade
        'upgrade_support':{ label: 'Nâng cấp được', icon: 'fa-wrench' },
        // Power draw
        'estimated_power_w':{ label: 'Công suất TDP', icon: 'fa-bolt', suffix: 'W' },
    };

    /* Keys to skip entirely (not useful for end users) */
    const SKIP_KEYS = [
        'schema_version', 'attributes', 'raw_specs', 'vfm_score', 'category_slug',
        'model', 'compatibility', 'required_psu_w', 'Thông số kỹ thuật'
    ];

    /* Priority order for specs display */
    const SPEC_PRIORITY = [
        'cpu_model', 'CPU', 'Bộ vi xử lý',
        'gpu_model', 'VGA', 'Card đồ họa',
        'ram_gb', 'RAM', 'Bộ nhớ RAM',
        'storage', 'SSD', 'Ổ cứng', 'Lưu trữ',
        'Màn hình', 'screen_size', 'display',
        'mainboard_model', 'Mainboard',
        'psu_wattage',
        'case_model',
        'cooler_model',
        'os', 'Hệ điều hành',
        'upgrade_support'
    ];

    function formatSpecLabel(key) {
        if (SPEC_MAP[key]) return SPEC_MAP[key].label;
        const cleanMap = {
            'cpu_cores': 'Số nhân CPU',
            'cpu_threads': 'Số luồng CPU',
            'max_ram_gb': 'RAM tối đa',
            'ram_slots': 'Số khe RAM',
            'bus_ram': 'Bus RAM',
            'ram_type': 'Loại RAM',
            'display_size': 'Màn hình',
            'refresh_rate_hz': 'Tần số quét',
            'response_time_ms': 'Phản hồi',
            'weight_kg': 'Trọng lượng',
            'battery_wh': 'Dung lượng pin',
            'psu_wattage': 'Công suất nguồn'
        };
        if (cleanMap[key]) return cleanMap[key];
        return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }

    function buildSpecsHtml(specs) {
        if (!specs || Object.keys(specs).length === 0) {
            return '<div class="rec-spec-item" style="grid-column: 1/-1; text-align:center; color:var(--text-secondary);">Chưa có dữ liệu chi tiết</div>';
        }

        const usedLabels = new Set();
        const sortedKeys = [];

        // Add priority keys first
        SPEC_PRIORITY.forEach(pk => {
            if (specs[pk] !== undefined && specs[pk] !== '' && !SKIP_KEYS.includes(pk)) {
                sortedKeys.push(pk);
            }
        });
        // Add remaining keys
        Object.keys(specs).forEach(k => {
            if (!sortedKeys.includes(k) && !SKIP_KEYS.includes(k) && specs[k] !== '') {
                sortedKeys.push(k);
            }
        });

        let html = '';
        let count = 0;

        sortedKeys.forEach(key => {
            if (count >= 8) return; // Max 8 specs
            const mapping = SPEC_MAP[key];
            const label = mapping ? mapping.label : formatSpecLabel(key);

            // Deduplicate by label
            if (usedLabels.has(label)) return;
            usedLabels.add(label);

            let value = specs[key];
            if (typeof value === 'object') value = JSON.stringify(value);
            value = String(value);

            // Add suffix if needed
            if (mapping && mapping.suffix && !value.includes(mapping.suffix)) {
                value += ' ' + mapping.suffix;
            }

            const icon = mapping ? mapping.icon : 'fa-circle-info';
            const iconPrefix = (mapping && mapping.isBrand) ? 'fa-brands' : 'fa-solid';

            html += `<div class="rec-spec-item">
                <i class="${iconPrefix} ${icon}"></i>
                <div><span class="spec-label">${label}</span><br><span class="spec-value">${value}</span></div>
            </div>`;
            count++;
        });

        return html;
    }

    function buildScoreRing(score, roleKey) {
        const circumference = 2 * Math.PI * 20; // r=20
        const offset = circumference - (score / 100) * circumference;
        const colorMap = {
            'best_fit': '#10B981',
            'best_value': '#3B82F6',
            'max_performance': '#8B5CF6'
        };
        const color = colorMap[roleKey] || '#10B981';

        return `<div class="rec-score-ring">
            <svg viewBox="0 0 48 48">
                <circle class="ring-bg" cx="24" cy="24" r="20"/>
                <circle class="ring-fg" cx="24" cy="24" r="20"
                    stroke="${color}"
                    stroke-dasharray="${circumference}"
                    stroke-dashoffset="${offset}"/>
            </svg>
            <div class="ring-label">${score}<small>điểm</small></div>
        </div>`;
    }

    document.addEventListener('DOMContentLoaded', function() {
        renderPanelOptions('laptop');
    });

    function selectBudget(code, cardEl) {
        document.querySelectorAll('#grid-budget .option-card').forEach(c => c.classList.remove('selected'));
        cardEl.classList.add('selected');
        document.getElementById('input-budget_code').value = code;
    }

    function selectCategory(catKey, cardEl) {
        document.querySelectorAll('#panel-2 .options-grid .option-card').forEach(c => c.classList.remove('selected'));
        cardEl.classList.add('selected');
        document.getElementById('input-category').value = catKey;

        const subContainer = document.getElementById('subcatContainer');
        if (catKey === 'gear') {
            subContainer.style.display = 'block';
            renderSubcatPills();
        } else {
            subContainer.style.display = 'none';
            document.getElementById('input-subcategory').value = '';
            renderPanelOptions(catKey);
        }
    }

    function renderSubcatPills() {
        const gearSubcats = window.TP_AI_CONFIG.categories.gear.subcategories;
        const container = document.getElementById('subcatPills');
        container.innerHTML = '';

        let firstKey = null;
        Object.keys(gearSubcats).forEach((sKey, idx) => {
            if (idx === 0) firstKey = sKey;
            const sub = gearSubcats[sKey];
            const pill = document.createElement('div');
            pill.className = 'subcat-pill' + (idx === 0 ? ' active' : '');
            pill.innerText = sub.label;
            pill.onclick = function() {
                document.querySelectorAll('.subcat-pill').forEach(p => p.classList.remove('active'));
                pill.classList.add('active');
                document.getElementById('input-subcategory').value = sKey;
                renderPanelOptions('gear', sKey);
            };
            container.appendChild(pill);
        });

        document.getElementById('input-subcategory').value = firstKey;
        renderPanelOptions('gear', firstKey);
    }

    function renderPanelOptions(catKey, subKey = '') {
        // Render Purposes
        let pList = [];
        if (catKey === 'gear' && subKey && window.TP_AI_CONFIG.purposes.gear[subKey]) {
            pList = window.TP_AI_CONFIG.purposes.gear[subKey];
        } else if (window.TP_AI_CONFIG.purposes[catKey]) {
            pList = window.TP_AI_CONFIG.purposes[catKey];
        }

        const pGrid = document.getElementById('grid-purposes');
        pGrid.innerHTML = pList.map((item, idx) => `
            <div class="option-card ${idx === 0 ? 'selected' : ''}" onclick="selectPurpose('${item.code}', this)">
                <i class="fa-solid fa-bullseye"></i>
                <span>${item.label}</span>
                <p>${item.desc || ''}</p>
            </div>
        `).join('');
        if (pList.length > 0) document.getElementById('input-purpose').value = pList[0].code;

        // Render Priorities (PC HAS NO BATTERY/LIGHTWEIGHT!)
        let prList = [];
        if (catKey === 'gear' && subKey && window.TP_AI_CONFIG.priorities.gear[subKey]) {
            prList = window.TP_AI_CONFIG.priorities.gear[subKey];
        } else if (window.TP_AI_CONFIG.priorities[catKey]) {
            prList = window.TP_AI_CONFIG.priorities[catKey];
        }

        const prGrid = document.getElementById('grid-priorities');
        prGrid.innerHTML = prList.map((item, idx) => `
            <div class="option-card ${idx === 0 ? 'selected' : ''}" onclick="selectPriority('${item.code}', this)">
                <i class="fa-solid fa-star"></i>
                <span>${item.label}</span>
            </div>
        `).join('');
        if (prList.length > 0) document.getElementById('input-priority').value = prList[0].code;
    }

    function selectPurpose(code, cardEl) {
        document.querySelectorAll('#grid-purposes .option-card').forEach(c => c.classList.remove('selected'));
        cardEl.classList.add('selected');
        document.getElementById('input-purpose').value = code;
    }

    function selectPriority(code, cardEl) {
        document.querySelectorAll('#grid-priorities .option-card').forEach(c => c.classList.remove('selected'));
        cardEl.classList.add('selected');
        document.getElementById('input-priority').value = code;
    }

    function nextStep() {
        if (currentStep === 1) {
            const bVal = document.getElementById('input-budget_code').value;
            if (!bVal) {
                alert('Vui lòng chọn khoảng ngân sách đầu tư trước khi tiếp tục!');
                return;
            }
        }

        if (currentStep < totalSteps) {
            document.getElementById(`stepIndicator-${currentStep}`).classList.remove('active');
            document.getElementById(`stepIndicator-${currentStep}`).classList.add('completed');
            document.getElementById(`panel-${currentStep}`).classList.remove('active');
            currentStep++;
            document.getElementById(`panel-${currentStep}`).classList.add('active');
            document.getElementById(`stepIndicator-${currentStep}`).classList.add('active');

            document.getElementById('btnPrev').style.visibility = 'visible';
            if (currentStep === totalSteps) {
                document.getElementById('btnNext').innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> Phân Tích & Lấy Đề Xuất AI';
            }
        } else {
            submitAiSurvey();
        }
    }

    function prevStep() {
        if (currentStep > 1) {
            document.getElementById(`stepIndicator-${currentStep}`).classList.remove('active');
            document.getElementById(`panel-${currentStep}`).classList.remove('active');
            currentStep--;
            document.getElementById(`panel-${currentStep}`).classList.add('active');
            document.getElementById(`stepIndicator-${currentStep}`).classList.remove('completed');
            document.getElementById(`stepIndicator-${currentStep}`).classList.add('active');

            if (currentStep === 1) {
                document.getElementById('btnPrev').style.visibility = 'hidden';
            }
            document.getElementById('btnNext').innerHTML = 'Tiếp tục <i class="fa-solid fa-arrow-right" style="margin-left: 6px;"></i>';
        }
    }

    const RECOMMEND_STORAGE_KEY = 'techpilot_ai_recommend_result';

    document.addEventListener('DOMContentLoaded', function() {
        try {
            const raw = sessionStorage.getItem(RECOMMEND_STORAGE_KEY);
            if (raw) {
                const cached = JSON.parse(raw);
                if (cached && cached.res && cached.res.success && (Date.now() - (cached.timestamp || 0) < 86400000)) {
                    renderRecommendationResults(cached.res, false);
                }
            }
        } catch (e) {
            console.error('Error restoring AI survey result:', e);
        }
    });

    function submitAiSurvey() {
        const form = document.getElementById('aiAssistantForm');
        const formData = new FormData(form);
        const card = document.getElementById('wizardCard');

        card.innerHTML = `
            <div style="text-align: center; padding: 50px 20px;">
                <i class="fa-solid fa-circle-notch fa-spin" style="font-size: 54px; color: var(--primary); margin-bottom: 20px; display: block;"></i>
                <h3 style="margin-bottom: 10px; font-weight: 800;">Trợ lý AI đang chấm điểm cấu hình...</h3>
                <p style="color: var(--text-secondary); max-width: 500px; margin: 0 auto; font-size: 14px;">Hệ thống đang chạy thuật toán phân tích 100 điểm, lọc kho sản phẩm thật và tạo báo cáo tư vấn tối ưu...</p>
            </div>
        `;

        const csrfToken = '<?= $_SESSION["csrf_token"] ?? "" ?>';

        fetch('<?= url("ai/recommend") ?>', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: new URLSearchParams(formData)
        })
        .then(res => {
            const contentType = res.headers.get('content-type') || '';
            if (!contentType.includes('application/json')) {
                throw new Error('Máy chủ trả về kết quả không phải JSON (Mã lỗi ' + res.status + ')');
            }
            return res.json();
        })
        .then(res => {
            if (res.success) {
                renderRecommendationResults(res, true);
            } else {
                alert("Thông báo: " + (res.error ? res.error.message : res.message || "Không có kết quả phù hợp."));
                resetWizard();
            }
        })
        .catch(err => {
            alert("Lỗi: " + err.message);
            resetWizard();
        });
    }

    function renderRecommendationResults(res, saveToStorage = true) {
        if (saveToStorage) {
            try {
                sessionStorage.setItem(RECOMMEND_STORAGE_KEY, JSON.stringify({
                    timestamp: Date.now(),
                    res: res
                }));
            } catch (e) {}
        }

        const card = document.getElementById('wizardCard');
        if (card) card.style.display = 'none';

        const resultBox = document.getElementById('aiRecsResult');
        if (!resultBox) return;

        resultBox.style.display = 'block';
        document.getElementById('resultSummary').innerText = res.summary || '';

        const container = document.getElementById('recsCardsContainer');
        container.innerHTML = '';

        const csrfToken = '<?= $_SESSION["csrf_token"] ?? "" ?>';

        (res.recommendations || []).forEach(item => {
            const p = item.product;
            const roleKey = item.role || 'best_fit';
            const roleLabel = item.role_label || 'Đề xuất AI';
            const specs = p.specs || {};
            const score = item.score || 90;

            const specsHtml = buildSpecsHtml(specs);
            const scoreRing = buildScoreRing(score, roleKey);

            const reasonsHtml = (item.reasons || []).map(r => `<li>${r}</li>`).join('');
            const tradeoffsHtml = (item.tradeoffs || []).map(t => `<li>${t}</li>`).join('');

            const cardHtml = `
                <div class="rec-card">
                    <div class="rec-card__header">
                        <span class="rec-badge badge-${roleKey}">${roleLabel}</span>
                        ${scoreRing}
                        <img src="${p.image_url}" alt="${p.name}" loading="lazy">
                    </div>

                    <div class="rec-card__body">
                        <h4 class="rec-card__name">${p.name}</h4>

                        <div class="rec-card__price-row">
                            <span class="rec-card__price">${p.price_formatted}</span>
                            <span class="rec-card__stock">Còn ${p.stock} máy</span>
                        </div>

                        <div class="rec-specs-grid">
                            ${specsHtml}
                        </div>

                        ${reasonsHtml ? `<div class="rec-insights">
                            <div class="rec-insights-title positive"><i class="fa-solid fa-circle-check"></i> Điểm nổi bật</div>
                            <ul>${reasonsHtml}</ul>
                        </div>` : ''}

                        ${tradeoffsHtml ? `<div class="rec-insights">
                            <div class="rec-insights-title caution"><i class="fa-solid fa-triangle-exclamation"></i> Cần lưu ý</div>
                            <ul>${tradeoffsHtml}</ul>
                        </div>` : ''}

                        <div class="rec-card__actions">
                            <a href="<?= url('product/detail/') ?>${p.slug || p.id}" class="rec-btn-detail" target="_blank"><i class="fa-solid fa-eye"></i> Xem chi tiết</a>
                            <form method="post" action="<?= url('compare/add') ?>" target="_blank" style="margin:0;">
                                <input type="hidden" name="_csrf" value="${csrfToken}">
                                <input type="hidden" name="product_id" value="${p.id}">
                                <button type="submit" class="rec-btn-compare" style="width:100%;"><i class="fa-solid fa-scale-balanced"></i> So sánh</button>
                            </form>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', cardHtml);
        });

        // Scroll to top of results smoothly
        resultBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function resetWizard() {
        try {
            sessionStorage.removeItem(RECOMMEND_STORAGE_KEY);
        } catch (e) {}
        location.href = '<?= url("ai-assistant") ?>';
    }
</script>

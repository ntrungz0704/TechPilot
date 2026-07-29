<?php
$aiConfig = require ROOT_PATH . '/config/ai-recommendation.php';
?>
<!-- Styles cho AI Assistant -->
<style>
    .ai-assistant-container {
        max-width: 1040px;
        margin: 40px auto 60px auto;
        padding: 0 16px;
        font-family: 'Inter', 'Outfit', sans-serif;
    }

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
        grid-template-columns: repeat(auto-fit, minmax(290px, 1fr));
        gap: 20px;
        margin-top: 25px;
    }
    .rec-card {
        border: 1px solid var(--border);
        border-radius: 16px;
        background: var(--surface-card, #FFFFFF);
        overflow: hidden;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        display: flex;
        flex-direction: column;
        position: relative;
        transition: transform 0.2s ease;
    }
    .rec-card:hover {
        transform: translateY(-4px);
    }
    .rec-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        color: #FFFFFF;
        z-index: 3;
    }
    .badge-best_fit { background: linear-gradient(135deg, #10B981, #059669); }
    .badge-best_value { background: linear-gradient(135deg, #3B82F6, #1D4ED8); }
    .badge-max_performance { background: linear-gradient(135deg, #8B5CF6, #6D28D9); }

    .suitability-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        background: rgba(16, 185, 129, 0.1);
        color: #059669;
        font-weight: 800;
        font-size: 13px;
        padding: 4px 10px;
        border-radius: 8px;
        border: 1px solid rgba(16, 185, 129, 0.3);
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
                card.style.display = 'none';
                document.getElementById('aiRecsResult').style.display = 'block';
                document.getElementById('resultSummary').innerText = res.summary || '';

                const container = document.getElementById('recsCardsContainer');
                container.innerHTML = '';

                (res.recommendations || []).forEach(item => {
                    const p = item.product;
                    const roleKey = item.role || 'best_fit';
                    const roleLabel = item.role_label || 'Đề xuất AI';
                    const specs = p.specs || {};
                    const score = item.score || 90;

                    let specsHtml = '';
                    Object.keys(specs).slice(0, 4).forEach(k => {
                        specsHtml += `<div><i class="fa-solid fa-angle-right" style="color:var(--primary); font-size:11px;"></i> <strong>${k}:</strong> ${specs[k]}</div>`;
                    });

                    const reasonsHtml = (item.reasons || []).map(r => `<li style="margin-bottom:4px;">${r}</li>`).join('');
                    const tradeoffsHtml = (item.tradeoffs || []).map(t => `<li style="margin-bottom:4px;">${t}</li>`).join('');

                    const cardHtml = `
                        <div class="rec-card">
                            <span class="rec-badge badge-${roleKey}">${roleLabel}</span>
                            <span class="suitability-badge">🎯 ${score}/100 ĐIỂM</span>

                            <div style="padding: 45px 20px 20px 20px; text-align: center; background-color: #F8FAFC; border-bottom: 1px solid var(--border);">
                                <img src="${p.image_url}" alt="${p.name}" style="height: 110px; object-fit: contain;">
                            </div>

                            <div style="padding: 20px; flex: 1; display: flex; flex-direction: column;">
                                <strong style="font-size: 14.5px; font-weight: 700; margin: 0 0 6px 0; height: 42px; overflow: hidden; line-height: 1.4; color: var(--text-primary);">${p.name}</strong>
                                
                                <div style="margin-bottom: 12px;">
                                    <span style="color: var(--primary); font-weight: 800; font-size: 18px;">${p.price_formatted}</span>
                                    <span style="font-size: 12px; color: #10B981; margin-left: 8px;">(Còn ${p.stock} máy)</span>
                                </div>

                                <div style="font-size: 12.5px; color: var(--text-secondary); display:flex; flex-direction:column; gap:4px; margin-bottom: 15px; background: #F8FAFC; padding: 10px; border-radius: 8px;">
                                    ${specsHtml}
                                </div>

                                <div style="margin-bottom: 15px;">
                                    <strong style="font-size: 12.5px; color: #047857; display:block; margin-bottom:4px;"><i class="fa-solid fa-circle-check"></i> Điểm nổi bật:</strong>
                                    <ul style="margin:0; padding-left:18px; font-size:12px; color:var(--text-secondary);">
                                        ${reasonsHtml}
                                    </ul>
                                </div>

                                <div style="margin-bottom: 20px;">
                                    <strong style="font-size: 12.5px; color: #B91C1C; display:block; margin-bottom:4px;"><i class="fa-solid fa-triangle-exclamation"></i> Cân nhắc:</strong>
                                    <ul style="margin:0; padding-left:18px; font-size:12px; color:var(--text-secondary);">
                                        ${tradeoffsHtml}
                                    </ul>
                                </div>

                                <div style="margin-top: auto; display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                    <a href="<?= url('product/detail/') ?>${p.slug || p.id}" class="btn btn--secondary btn--sm" style="text-align: center; font-weight: 700; text-decoration: none;" target="_blank">Xem chi tiết</a>
                                    <form method="post" action="<?= url('compare/add') ?>" target="_blank" style="margin:0;">
                                        <input type="hidden" name="_csrf" value="${csrfToken}">
                                        <input type="hidden" name="product_id" value="${p.id}">
                                        <button type="submit" class="btn btn--sm" style="width: 100%; font-weight: 700; background-color: #10B981; color: #FFFFFF; border: none; cursor: pointer;">
                                            <i class="fa-solid fa-scale-balanced"></i> So sánh
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    `;
                    container.insertAdjacentHTML('beforeend', cardHtml);
                });
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

    function resetWizard() {
        location.reload();
    }
</script>

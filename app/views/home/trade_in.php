<style>
    /* ==========================================
       TechPilot Trade-In Redesign CSS Styles
       ========================================== */
    .trade-in-wrapper {
        margin-bottom: 60px;
        font-family: 'Inter', sans-serif;
    }
    
    /* Header Banner styling */
    .trade-in-banner {
        background: linear-gradient(135deg, #FF3B30 0%, #C7000B 100%);
        color: #FFFFFF;
        padding: 50px 40px;
        border-radius: var(--radius-card);
        position: relative;
        overflow: hidden;
        margin-top: 15px;
        box-shadow: 0 10px 30px rgba(199, 0, 11, 0.15);
    }
    
    .trade-in-banner::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: repeating-linear-gradient(45deg, rgba(255,255,255,0.05), rgba(255,255,255,0.05) 15px, rgba(0,0,0,0.08) 15px, rgba(0,0,0,0.08) 30px);
    }
    
    .trade-in-banner-content {
        position: relative;
        z-index: 2;
        max-width: 800px;
    }
    
    .trade-in-tag {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        font-weight: 700;
        display: inline-block;
        margin-bottom: 12px;
        background: rgba(255, 255, 255, 0.2);
        padding: 4px 10px;
        border-radius: 4px;
        backdrop-filter: blur(4px);
    }
    
    .trade-in-title {
        font-size: 40px;
        font-weight: 800;
        line-height: 1.2;
        margin-bottom: 16px;
        text-transform: uppercase;
    }
    
    .trade-in-title span {
        background: #FFFFFF;
        color: #C7000B;
        padding: 2px 10px;
        border-radius: 6px;
        display: inline-block;
    }
    
    .trade-in-desc {
        font-size: 15px;
        opacity: 0.9;
        line-height: 1.7;
        margin-bottom: 28px;
    }
    
    .trade-in-buttons {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }
    
    .zalo-btn-mock {
        background: #0084FF;
        color: #FFFFFF !important;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        border-radius: 50px;
        border: none;
        cursor: pointer;
        font-size: 13px;
        transition: all 0.2s;
        box-shadow: 0 4px 15px rgba(0, 132, 255, 0.3);
    }
    
    .zalo-btn-mock:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 132, 255, 0.4);
    }
    
    .zalo-btn-icon {
        width: 18px;
        height: 18px;
        background: #FFFFFF;
        color: #0084FF;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 10px;
    }
    
    .steps-btn {
        background: transparent;
        color: #FFFFFF;
        border: 1px solid rgba(255,255,255,0.4);
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        border-radius: 50px;
        cursor: pointer;
        font-size: 13px;
        transition: all 0.2s;
    }
    
    .steps-btn:hover {
        background: rgba(255,255,255,0.1);
        border-color: #FFFFFF;
    }
    
    /* Category icon bar under banner */
    .trade-in-menu-bar {
        background: var(--surface-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-elem);
        margin-top: 25px;
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        padding: 12px;
        gap: 8px;
        box-shadow: var(--shadow-card);
    }
    
    .trade-in-menu-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        padding: 10px;
        cursor: pointer;
        border-radius: 8px;
        color: var(--text-secondary);
        transition: var(--transition);
    }
    
    .trade-in-menu-item:hover {
        background: var(--surface-muted);
        color: var(--primary);
    }
    
    .trade-in-menu-item.is-active {
        color: var(--accent);
        background: rgba(255, 77, 79, 0.04);
    }
    
    .menu-icon-circle {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: var(--surface-muted);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        transition: var(--transition);
        color: var(--text-primary);
    }
    
    .trade-in-menu-item:hover .menu-icon-circle {
        background: var(--primary);
        color: #FFFFFF;
    }
    
    .trade-in-menu-item.is-active .menu-icon-circle {
        background: var(--accent);
        color: #FFFFFF;
    }
    
    .menu-text {
        font-size: 12px;
        font-weight: 600;
        text-align: center;
    }
    
    /* Calculator Section */
    .calc-card {
        background: var(--surface-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-card);
        padding: 35px;
        margin-top: 30px;
        box-shadow: var(--shadow-card);
    }
    
    .calc-title {
        font-size: 24px;
        font-weight: 800;
        margin-bottom: 6px;
        text-transform: uppercase;
        color: var(--text-primary);
    }
    
    .calc-subtitle {
        color: var(--text-secondary);
        font-size: 13px;
        margin-bottom: 25px;
    }
    
    .calc-label {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-primary);
        margin-bottom: 10px;
        display: block;
    }
    
    /* Category Tabs */
    .category-tabs {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 25px;
    }
    
    .category-tab-btn {
        border: 1px solid var(--border);
        background: var(--surface-card);
        color: var(--text-primary);
        padding: 12px;
        font-size: 13px;
        font-weight: 700;
        border-radius: var(--radius-elem);
        cursor: pointer;
        text-align: center;
        transition: var(--transition);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    .category-tab-btn:hover {
        border-color: var(--primary);
        color: var(--primary);
        background: var(--surface-muted);
    }
    
    .category-tab-btn.is-active {
        background: var(--accent);
        border-color: var(--accent);
        color: #FFFFFF;
    }
    
    /* Specs layout */
    .specs-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        margin-bottom: 25px;
    }
    
    .spec-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    
    .spec-select {
        width: 100%;
        border: 1px solid var(--border);
        border-radius: var(--radius-elem);
        padding: 12px 14px;
        background: var(--surface-card);
        color: var(--text-primary);
        font-size: 13px;
        cursor: pointer;
        transition: var(--transition);
    }
    
    .spec-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(10, 91, 255, 0.1);
    }
    
    /* Condition (Ngoại quan) cards */
    .appearance-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin-bottom: 25px;
    }
    
    .appearance-card {
        border: 1px solid var(--border);
        background: var(--surface-card);
        border-radius: var(--radius-elem);
        padding: 16px;
        display: flex;
        gap: 12px;
        cursor: pointer;
        transition: var(--transition);
        position: relative;
    }
    
    .appearance-card:hover {
        border-color: var(--primary);
        background: var(--surface-muted);
    }
    
    .appearance-card.is-active {
        border-color: var(--accent);
        background: rgba(255, 77, 79, 0.04);
    }
    
    .appearance-radio {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 2px solid var(--border);
        margin-top: 2px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition);
        flex-shrink: 0;
    }
    
    .appearance-card.is-active .appearance-radio {
        border-color: var(--accent);
    }
    
    .appearance-card.is-active .appearance-radio::after {
        content: '';
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--accent);
    }
    
    .appearance-info h4 {
        font-size: 14px;
        font-weight: 700;
        margin-bottom: 4px;
        color: var(--text-primary);
    }
    
    .appearance-info p {
        font-size: 12px;
        color: var(--text-secondary);
        line-height: 1.5;
    }
    
    /* Checkbox list */
    .checkbox-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 25px;
    }
    
    .checkbox-card {
        border: 1px solid var(--border);
        background: var(--surface-card);
        border-radius: var(--radius-elem);
        padding: 12px 14px;
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: var(--transition);
        user-select: none;
    }
    
    .checkbox-card:hover {
        border-color: var(--primary);
        background: var(--surface-muted);
    }
    
    .checkbox-card.is-checked {
        border-color: var(--accent);
        background: rgba(255, 77, 79, 0.02);
    }
    
    .checkbox-icon {
        width: 16px;
        height: 16px;
        border-radius: 4px;
        border: 2px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 9px;
        color: #FFFFFF;
        transition: var(--transition);
        flex-shrink: 0;
    }
    
    .checkbox-card.is-checked .checkbox-icon {
        background: var(--accent);
        border-color: var(--accent);
    }
    
    .checkbox-text {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-primary);
    }
    
    /* Submit actions */
    .calc-submit-btn {
        width: 100%;
        background: var(--accent);
        color: #FFFFFF;
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 14px;
        border: none;
        border-radius: var(--radius-elem);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: var(--transition);
    }
    
    .calc-submit-btn:hover {
        background: #e04345;
        box-shadow: 0 4px 12px rgba(255, 77, 79, 0.2);
    }
    
    /* Result Block styling */
    .result-card {
        margin-top: 25px;
        background: var(--surface-muted);
        border: 1px solid var(--border);
        border-radius: var(--radius-card);
        padding: 24px;
        display: none;
        animation: fadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    }
    
    .result-header {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 700;
        color: var(--text-secondary);
        margin-bottom: 8px;
    }
    
    .result-price-range {
        font-size: 32px;
        font-weight: 800;
        color: var(--accent);
        margin-bottom: 12px;
    }
    
    .result-details {
        font-size: 13px;
        color: var(--text-secondary);
        line-height: 1.6;
    }
    
    /* Bottom alert banner */
    .bottom-trade-bar {
        background: #111827;
        color: #FFFFFF;
        border-radius: 12px;
        padding: 20px 25px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 35px;
        gap: 20px;
    }
    
    html.dark-mode .bottom-trade-bar {
        background: var(--surface-card);
        border: 1px solid var(--border);
    }
    
    .bottom-trade-left {
        display: flex;
        align-items: flex-start;
        gap: 15px;
    }
    
    .bottom-trade-icon {
        width: 40px;
        height: 40px;
        background: var(--accent);
        color: #FFFFFF;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    
    .bottom-trade-info h3 {
        font-size: 15px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }
    
    .bottom-trade-info p {
        font-size: 12px;
        opacity: 0.8;
        line-height: 1.5;
    }
    
    .bottom-trade-actions {
        display: flex;
        gap: 10px;
        flex-shrink: 0;
    }
    
    .bottom-btn-outline {
        border: 1px solid rgba(255,255,255,0.3);
        color: #FFFFFF;
        padding: 10px 18px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        background: transparent;
        transition: var(--transition);
    }
    
    .bottom-btn-outline:hover {
        background: rgba(255,255,255,0.08);
        border-color: #FFFFFF;
    }
    
    .bottom-btn-solid {
        background: var(--accent);
        color: #FFFFFF;
        padding: 10px 18px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        border: none;
        transition: var(--transition);
    }
    
    .bottom-btn-solid:hover {
        background: #e04345;
    }
    
    /* Mock overlay modal for Zalo */
    .zalo-modal {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: var(--overlay-backdrop);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
    }
    
    .zalo-modal.is-open {
        opacity: 1;
        pointer-events: auto;
    }
    
    .zalo-modal-card {
        background: var(--surface-card);
        border-radius: var(--radius-card);
        width: 100%;
        max-width: 440px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        text-align: center;
        position: relative;
        border: 1px solid var(--border);
        transform: translateY(20px);
        transition: transform 0.3s ease;
    }
    
    .zalo-modal.is-open .zalo-modal-card {
        transform: translateY(0);
    }
    
    .zalo-modal-close {
        position: absolute;
        top: 15px; right: 15px;
        font-size: 16px;
        color: var(--text-secondary);
        cursor: pointer;
        transition: var(--transition);
    }
    
    .zalo-modal-close:hover {
        color: var(--text-primary);
    }
    
    .zalo-modal-avatar {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: #0084FF;
        color: #FFFFFF;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        margin: 0 auto 15px;
    }
    
    .zalo-modal h3 {
        font-size: 18px;
        font-weight: 800;
        margin-bottom: 6px;
        color: var(--text-primary);
    }
    
    .zalo-modal p {
        font-size: 13px;
        color: var(--text-secondary);
        line-height: 1.6;
        margin-bottom: 20px;
    }
    
    .zalo-modal-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        background: var(--surface-muted);
        color: var(--text-primary);
        font-size: 12px;
        font-weight: 600;
    }
    
    /* Layout structural updates for 4-steps */
    .steps-section {
        margin-top: 40px;
    }
    
    .steps-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        margin-top: 20px;
    }
    
    .step-card {
        background: var(--surface-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-elem);
        padding: 24px;
        box-shadow: var(--shadow-card);
        transition: var(--transition);
    }
    
    .step-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-hover);
    }
    
    .step-num {
        font-size: 24px;
        font-weight: 800;
        color: var(--accent);
        margin-bottom: 12px;
        display: block;
    }
    
    .step-card h4 {
        font-size: 14px;
        font-weight: 700;
        margin-bottom: 8px;
        color: var(--text-primary);
    }
    
    .step-card p {
        font-size: 12px;
        color: var(--text-secondary);
        line-height: 1.6;
    }
    
    /* Responsive styling rules */
    @media (max-width: 992px) {
        .specs-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .checkbox-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .bottom-trade-bar {
            flex-direction: column;
            align-items: stretch;
        }
        .bottom-trade-actions {
            justify-content: flex-end;
        }
        .steps-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 768px) {
        .trade-in-menu-bar {
            grid-template-columns: repeat(3, 1fr);
        }
        .category-tabs {
            grid-template-columns: repeat(2, 1fr);
        }
        .appearance-grid {
            grid-template-columns: 1fr;
        }
        .trade-in-title {
            font-size: 28px;
        }
        .specs-grid {
            grid-template-columns: 1fr;
        }
        .steps-grid {
            grid-template-columns: 1fr;
        }
        .checkbox-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<section class="container breadcrumb">
    <a href="<?= url('/') ?>">Trang chủ</a> <i class="fa-solid fa-chevron-right"></i>
    <span>Thu cũ đổi mới máy cũ</span>
</section>

<div class="container trade-in-wrapper">
    <!-- 1. Header Banner Block -->
    <div class="trade-in-banner">
        <div class="trade-in-banner-content">
            <span class="trade-in-tag">Măng công nghệ cũ TechPilot</span>
            <h1 class="trade-in-title">Bán dễ dàng.<span>Lên đời tiết kiệm</span></h1>
            <p class="trade-in-desc">
                Liên hệ TechPilot qua Zalo chi nhánh NM Hiếu hoặc Zalo chi nhánh TDK để được tư vấn thu cũ đổi mới LCD, CPU, Mainboard, VGA sau khi kiểm tra thực tế.
            </p>
            <div class="trade-in-buttons">
                <button type="button" class="zalo-btn-mock" onclick="openZaloModal('TDK')">
                    <span class="zalo-btn-icon">Z</span> Zalo TDK
                </button>
                <button type="button" class="zalo-btn-mock" onclick="openZaloModal('NM Hiếu')">
                    <span class="zalo-btn-icon">Z</span> Zalo NM Hiếu
                </button>
                <a href="#steps" class="steps-btn">
                    <i class="fa-solid fa-list-ol"></i> 4 bước thu mua
                </a>
            </div>
        </div>
    </div>

    <!-- 2. Category Quick Menu Bar -->
    <div class="trade-in-menu-bar">
        <div class="trade-in-menu-item is-active" onclick="switchCategoryTab('LCD')">
            <div class="menu-icon-circle"><i class="fa-solid fa-calculator"></i></div>
            <span class="menu-text">Tra cứu giá thu</span>
        </div>
        <div class="trade-in-menu-item" onclick="switchCategoryTab('LCD')">
            <div class="menu-icon-circle"><i class="fa-solid fa-desktop"></i></div>
            <span class="menu-text">Màn hình</span>
        </div>
        <div class="trade-in-menu-item" onclick="switchCategoryTab('CPU')">
            <div class="menu-icon-circle"><i class="fa-solid fa-microchip"></i></div>
            <span class="menu-text">CPU</span>
        </div>
        <div class="trade-in-menu-item" onclick="switchCategoryTab('Mainboard')">
            <div class="menu-icon-circle"><i class="fa-solid fa-cube"></i></div>
            <span class="menu-text">Mainboard</span>
        </div>
        <div class="trade-in-menu-item" onclick="switchCategoryTab('VGA')">
            <div class="menu-icon-circle"><i class="fa-solid fa-server"></i></div>
            <span class="menu-text">VGA</span>
        </div>
        <a href="<?= url('home/search?cat=hang-cu-gia-tot') ?>" class="trade-in-menu-item">
            <div class="menu-icon-circle"><i class="fa-solid fa-tags"></i></div>
            <span class="menu-text">Hàng cũ giá tốt</span>
        </a>
    </div>

    <!-- 3. Calculator Main Card -->
    <div class="calc-card" id="calculator">
        <h2 class="calc-title">Ước tính giá thu</h2>
        <p class="calc-subtitle">Chọn nhóm hàng và thông tin sản phẩm để xem khoảng giá tham khảo trước khi chat TechPilot.</p>
        
        <!-- Nhóm Hàng Tabs -->
        <span class="calc-label">Nhóm hàng</span>
        <div class="category-tabs">
            <button type="button" class="category-tab-btn is-active" id="tab_LCD" onclick="switchCategoryTab('LCD')">
                <i class="fa-solid fa-desktop"></i> LCD
            </button>
            <button type="button" class="category-tab-btn" id="tab_CPU" onclick="switchCategoryTab('CPU')">
                <i class="fa-solid fa-microchip"></i> CPU
            </button>
            <button type="button" class="category-tab-btn" id="tab_Mainboard" onclick="switchCategoryTab('Mainboard')">
                <i class="fa-solid fa-cube"></i> Mainboard
            </button>
            <button type="button" class="category-tab-btn" id="tab_VGA" onclick="switchCategoryTab('VGA')">
                <i class="fa-solid fa-server"></i> VGA
            </button>
        </div>

        <!-- Dynamic Specifications Dropdowns -->
        <div id="specsContainer">
            <span class="calc-label" id="specsLabel">Thông số LCD</span>
            <div class="specs-grid" id="specsGrid">
                <!-- Built dynamically by JS -->
            </div>
        </div>

        <!-- Ngoại Quan Choice Cards -->
        <span class="calc-label">Ngoại quan</span>
        <div class="appearance-grid">
            <div class="appearance-card is-active" id="appear_good" onclick="selectAppearance('good')">
                <div class="appearance-radio"></div>
                <div class="appearance-info">
                    <h4>Ngoại quan đẹp</h4>
                    <p>Ít trầy xước, tem/ốc và cổng kết nối bình thường.</p>
                </div>
            </div>
            <div class="appearance-card" id="appear_bad" onclick="selectAppearance('bad')">
                <div class="appearance-radio"></div>
                <div class="appearance-info">
                    <h4>Ngoại quan xấu</h4>
                    <p>Trầy xước rỉ, móp, oxy hóa hoặc cần TechPilot kiểm tra kỹ.</p>
                </div>
            </div>
        </div>

        <!-- Checkbox accessories -->
        <span class="calc-label">Tình trạng đi kèm</span>
        <div class="checkbox-grid">
            <div class="checkbox-card is-checked" id="chk_box" onclick="toggleCheckbox('box')">
                <div class="checkbox-icon"><i class="fa-solid fa-check"></i></div>
                <span class="checkbox-text">Còn hộp</span>
            </div>
            <div class="checkbox-card is-checked" id="chk_warranty" onclick="toggleCheckbox('warranty')">
                <div class="checkbox-icon"><i class="fa-solid fa-check"></i></div>
                <span class="checkbox-text">Còn bảo hành hãng</span>
            </div>
            <div class="checkbox-card is-checked" id="chk_cable" onclick="toggleCheckbox('cable')">
                <div class="checkbox-icon"><i class="fa-solid fa-check"></i></div>
                <span class="checkbox-text">Còn cáp tín hiệu</span>
            </div>
            <div class="checkbox-card is-checked" id="chk_power" onclick="toggleCheckbox('power')">
                <div class="checkbox-icon"><i class="fa-solid fa-check"></i></div>
                <span class="checkbox-text">Còn nguồn / adapter</span>
            </div>
        </div>

        <!-- Submit Button -->
        <button type="button" class="calc-submit-btn" onclick="estimatePrice()">
            <i class="fa-solid fa-calculator"></i> Ước tính giá thu
        </button>

        <!-- Dynamic Results Card -->
        <div class="result-card" id="resultCard">
            <div class="result-header">Khoảng giá thu mua ước tính</div>
            <div class="result-price-range" id="priceRangeText">0đ - 0đ</div>
            <div class="result-details">
                * Đây là mức giá tham khảo ước tính dựa trên thông tin bạn cung cấp. Giá chính thức sẽ được kỹ thuật viên báo lại sau khi trực tiếp kiểm nghiệm sản phẩm.<br>
                Hãy liên hệ Zalo của chúng tôi ở bên dưới để đặt lịch kiểm nghiệm sớm nhất!
            </div>
        </div>
    </div>

    <!-- 4. 4-Steps Process Section -->
    <section class="steps-section" id="steps">
        <div class="section__head">
            <h2>4 bước bán hoặc lên đời</h2>
        </div>
        <p style="color: var(--text-secondary); line-height: 1.7; max-width: 860px;">
            Giá thu không cố định trước khi kiểm tra. TechPilot sẽ tư vấn phương án phù hợp nếu khách muốn lên đời màn hình, VGA, CPU hoặc mainboard.
        </p>
        <div class="steps-grid">
            <div class="step-card">
                <span class="step-num">01</span>
                <h4>Liên hệ</h4>
                <p>Nhắn tin với NM Hiếu hoặc TDK với model cụ thể, ảnh chụp thực tế và thông tin chi tiết về tình trạng thiết bị.</p>
            </div>
            <div class="step-card">
                <span class="step-num">02</span>
                <h4>Kiểm tra</h4>
                <p>Đem thiết bị qua showroom TechPilot hoặc chuẩn bị theo đúng hướng dẫn của nhân viên tư vấn từ xa.</p>
            </div>
            <div class="step-card">
                <span class="step-num">03</span>
                <h4>Báo giá</h4>
                <p>Mức giá thu mua chính thức được bộ phận kỹ thuật quyết định sau khi đo đạc, kiểm nghiệm linh kiện thực tế.</p>
            </div>
            <div class="step-card">
                <span class="step-num">04</span>
                <h4>Nhận tiền hoặc lên đời</h4>
                <p>Khách hàng chọn nhận tiền mặt/chuyển khoản trực tiếp hoặc bù thêm chênh lệch để đổi sản phẩm mới hơn.</p>
            </div>
        </div>
    </section>

    <!-- 5. Bottom Sticky-like Action Bar -->
    <div class="bottom-trade-bar">
        <div class="bottom-trade-left">
            <div class="bottom-trade-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div class="bottom-trade-info">
                <h3>Không có bảng giá cố định - TechPilot tư vấn sau kiểm tra</h3>
                <p>Trang này không công bố bảng giá thu trước. Liên hệ qua Zalo NM Hiếu hoặc Zalo TDK để nhân viên hướng dẫn thông tin cần chuẩn bị.</p>
            </div>
        </div>
        <div class="bottom-trade-actions">
            <button type="button" class="bottom-btn-outline" onclick="openZaloModal('TDK')">Liên hệ TDK</button>
            <button type="button" class="bottom-btn-solid" onclick="openZaloModal('NM Hiếu')">Liên hệ NM Hiếu</button>
        </div>
    </div>
</div>

<!-- Mock Zalo Overlay Modal -->
<div class="zalo-modal" id="zaloModal" onclick="closeZaloModal(event)">
    <div class="zalo-modal-card" onclick="event.stopPropagation()">
        <span class="zalo-modal-close" onclick="closeZaloModal(null)"><i class="fa-solid fa-xmark"></i></span>
        <div class="zalo-modal-avatar">
            <i class="fa-solid fa-user-tie"></i>
        </div>
        <h3 id="modalName">Zalo NM Hiếu</h3>
        <p>Đây là giao diện Zalo của TechPilot. Bạn có thể quét mã QR hoặc click kết nối Zalo trực tiếp sau khi hệ thống liên kết được kích hoạt chính thức.</p>
        <span class="zalo-modal-badge" id="modalBranch">Hình minh họa liên hệ</span>
    </div>
</div>

<!-- ==========================================
     TechPilot Trade-In Dynamic JavaScript
     ========================================== -->
<script>
    // System specifications dataset
    const specsData = {
        LCD: {
            label: 'Thông số LCD',
            fields: [
                { id: 'lcd_size', label: 'Kích thước', options: ['22 inch', '24 inch', '27 inch', '32 inch', 'Khác'] },
                { id: 'lcd_res', label: 'Độ phân giải', options: ['FHD (1920x1080)', '2K (2560x1440)', '4K (3840x2160)', 'Khác'] },
                { id: 'lcd_panel', label: 'Tấm nền', options: ['IPS', 'VA', 'TN', 'OLED', 'Khác'] },
                { id: 'lcd_refresh', label: 'Tần số quét', options: ['60Hz / 75Hz', '144Hz / 165Hz', '240Hz / 360Hz', 'Khác'] }
            ],
            basePriceMin: 800000,
            basePriceMax: 1200000
        },
        CPU: {
            label: 'Thông số CPU',
            fields: [
                { id: 'cpu_brand', label: 'Hãng', options: ['Intel', 'AMD'] },
                { id: 'cpu_series', label: 'Dòng CPU', options: ['Core i3 / Ryzen 3', 'Core i5 / Ryzen 5', 'Core i7 / Ryzen 7', 'Core i9 / Ryzen 9'] },
                { id: 'cpu_gen', label: 'Thế hệ', options: ['Intel Gen 10 - 11 / Ryzen 3000', 'Gen 12 / Ryzen 5000', 'Gen 13 / Ryzen 7000', 'Gen 14 / Ryzen 9000'] }
            ],
            basePriceMin: 1200000,
            basePriceMax: 1800000
        },
        Mainboard: {
            label: 'Thông số Mainboard',
            fields: [
                { id: 'main_brand', label: 'Hãng sản xuất', options: ['ASUS', 'MSI', 'GIGABYTE', 'ASRock'] },
                { id: 'main_chipset', label: 'Chipset dòng', options: ['H510 / H610 / A620', 'B660 / B760 / B650', 'Z690 / Z790 / X670'] }
            ],
            basePriceMin: 500000,
            basePriceMax: 900000
        },
        VGA: {
            label: 'Thông số VGA',
            fields: [
                { id: 'vga_brand', label: 'Hãng GPU', options: ['NVIDIA GeForce', 'AMD Radeon'] },
                { id: 'vga_series', label: 'Dòng VGA', options: ['GTX 16 Series', 'RTX 20 Series', 'RTX 30 Series', 'RTX 40 Series', 'RX 6000 / 7000 Series'] },
                { id: 'vga_model', label: 'Dòng Model', options: ['xx60 / xx60 Ti (VD: RTX 4060)', 'xx70 / xx70 Ti (VD: RTX 4070)', 'xx80 / xx90 (VD: RTX 4080)', 'Khác'] }
            ],
            basePriceMin: 2000000,
            basePriceMax: 3000000
        }
    };

    // State Variables
    let currentCategory = 'LCD';
    let selectedAppearance = 'good';
    let checkboxStates = {
        box: true,
        warranty: true,
        cable: true,
        power: true
    };

    // Initialize page on load
    document.addEventListener('DOMContentLoaded', function () {
        renderSpecsForm('LCD');
    });

    // Switch Category Tab and Category Menu bar
    function switchCategoryTab(cat) {
        currentCategory = cat;
        
        // Update tabs active state
        document.querySelectorAll('.category-tab-btn').forEach(btn => btn.classList.remove('is-active'));
        const activeTab = document.getElementById('tab_' + cat);
        if (activeTab) activeTab.classList.add('is-active');

        // Update horizontal category bar active state
        document.querySelectorAll('.trade-in-menu-item').forEach(item => {
            item.classList.remove('is-active');
            const labelText = item.querySelector('.menu-text').innerText.trim();
            if ((cat === 'LCD' && labelText === 'Màn hình') || 
                (cat === 'CPU' && labelText === 'CPU') ||
                (cat === 'Mainboard' && labelText === 'Mainboard') ||
                (cat === 'VGA' && labelText === 'VGA')) {
                item.classList.add('is-active');
            }
        });

        // Render fields
        renderSpecsForm(cat);
        
        // Hide previous results
        document.getElementById('resultCard').style.display = 'none';
        
        // Scroll to calculator
        document.getElementById('calculator').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // Dynamic rendering of fields
    function renderSpecsForm(cat) {
        const container = document.getElementById('specsGrid');
        const label = document.getElementById('specsLabel');
        
        const catData = specsData[cat];
        label.innerText = catData.label;
        container.innerHTML = '';

        catData.fields.forEach(field => {
            const fieldWrapper = document.createElement('div');
            fieldWrapper.className = 'spec-field';

            const labelElem = document.createElement('label');
            labelElem.className = 'calc-label';
            labelElem.style.marginBottom = '6px';
            labelElem.innerText = field.label;

            const selectElem = document.createElement('select');
            selectElem.className = 'spec-select';
            selectElem.id = field.id;

            field.options.forEach(opt => {
                const optElem = document.createElement('option');
                optElem.value = opt;
                optElem.innerText = opt;
                selectElem.appendChild(optElem);
            });

            fieldWrapper.appendChild(labelElem);
            fieldWrapper.appendChild(selectElem);
            container.appendChild(fieldWrapper);
        });

        // Adjust grid template columns based on number of fields
        container.style.gridTemplateColumns = `repeat(${catData.fields.length}, 1fr)`;
        if (window.innerWidth <= 992) {
            container.style.gridTemplateColumns = 'repeat(2, 1fr)';
        }
        if (window.innerWidth <= 768) {
            container.style.gridTemplateColumns = '1fr';
        }
    }

    // Ngoại quan selection handler
    function selectAppearance(type) {
        selectedAppearance = type;
        document.getElementById('appear_good').classList.remove('is-active');
        document.getElementById('appear_bad').classList.remove('is-active');
        
        if (type === 'good') {
            document.getElementById('appear_good').classList.add('is-active');
        } else {
            document.getElementById('appear_bad').classList.add('is-active');
        }
    }

    // Checkboxes toggle handler
    function toggleCheckbox(id) {
        checkboxStates[id] = !checkboxStates[id];
        const card = document.getElementById('chk_' + id);
        
        if (checkboxStates[id]) {
            card.classList.add('is-checked');
        } else {
            card.classList.remove('is-checked');
        }
    }

    // Price calculator estimation function
    function estimatePrice() {
        const catData = specsData[currentCategory];
        let baseMin = catData.basePriceMin;
        let baseMax = catData.basePriceMax;

        // Calculate based on dropdown indexes (newer/higher specs = higher price)
        let totalDropdownScore = 0;
        let numDropdowns = catData.fields.length;
        
        catData.fields.forEach(field => {
            const selectEl = document.getElementById(field.id);
            if (selectEl) {
                const idx = selectEl.selectedIndex;
                const totalOptions = selectEl.options.length;
                totalDropdownScore += (idx / (totalOptions - 1 || 1));
            }
        });

        let dropdownMultiplier = 1.0 + (totalDropdownScore / numDropdowns) * 0.8;
        let minPrice = baseMin * dropdownMultiplier;
        let maxPrice = baseMax * dropdownMultiplier;

        // Apply Ngoại quan condition multiplier
        if (selectedAppearance === 'bad') {
            minPrice *= 0.55;
            maxPrice *= 0.55;
        }

        // Add small accessory bonuses
        if (checkboxStates.box) {
            minPrice += 50000; maxPrice += 100000;
        }
        if (checkboxStates.warranty) {
            minPrice += 150000; maxPrice += 300000;
        }
        if (checkboxStates.cable) {
            minPrice += 20000; maxPrice += 50000;
        }
        if (checkboxStates.power) {
            minPrice += 50000; maxPrice += 100000;
        }

        // Format to millions/thousands
        const formatNumber = (num) => {
            return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(Math.round(num / 50000) * 50000);
        };

        // Display results
        const resultCard = document.getElementById('resultCard');
        const priceText = document.getElementById('priceRangeText');
        
        priceText.innerText = `${formatNumber(minPrice)} - ${formatNumber(maxPrice)}`;
        resultCard.style.display = 'block';
        resultCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // Modal Control Functions (Illustrational Zalo)
    function openZaloModal(name) {
        document.getElementById('modalName').innerText = 'Zalo ' + name;
        document.getElementById('modalBranch').innerText = 'Tư vấn viên TechPilot: Zalo ' + name;
        document.getElementById('zaloModal').classList.add('is-open');
    }

    function closeZaloModal(event) {
        if (!event || event.target.id === 'zaloModal' || event.target.closest('.zalo-modal-close')) {
            document.getElementById('zaloModal').classList.remove('is-open');
        }
    }
</script>

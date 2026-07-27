<!-- Premium TechPilot Trade-In Redesign (Version 2.0) -->
<style>
    :root {
        --trade-primary: #3b82f6;
        --trade-primary-glow: rgba(59, 130, 246, 0.15);
        --trade-lcd-color: #3b82f6;
        --trade-cpu-color: #a855f7;
        --trade-main-color: #f59e0b;
        --trade-vga-color: #ef4444;
    }

    .trade-in-wrapper {
        margin-bottom: 80px;
        font-family: 'Outfit', 'Inter', sans-serif;
        color: var(--text-primary);
    }
    
    /* Header Banner with Cosmic Mesh Gradient */
    .trade-in-banner {
        position: relative;
        background: radial-gradient(circle at 0% 0%, rgba(59, 130, 246, 0.8) 0%, rgba(147, 51, 234, 0.8) 50%, rgba(17, 24, 39, 1) 100%);
        color: #FFFFFF;
        padding: 80px 50px;
        border-radius: 24px;
        overflow: hidden;
        margin-top: 20px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .trade-in-banner::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: radial-gradient(circle at 100% 100%, rgba(255, 255, 255, 0.1) 0%, transparent 40%);
        pointer-events: none;
    }
    
    .trade-in-banner-content {
        position: relative;
        z-index: 2;
        max-width: 750px;
    }
    
    .trade-in-tag {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 2px;
        font-weight: 800;
        display: inline-block;
        margin-bottom: 16px;
        background: rgba(255, 255, 255, 0.15);
        padding: 6px 14px;
        border-radius: 50px;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .trade-in-title {
        font-size: 48px;
        font-weight: 900;
        line-height: 1.15;
        margin-bottom: 20px;
        text-transform: uppercase;
        letter-spacing: -1px;
    }
    
    .trade-in-title span {
        background: linear-gradient(90deg, #3b82f6, #8b5cf6, #ec4899);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-weight: 900;
    }
    
    .trade-in-desc {
        font-size: 16px;
        opacity: 0.85;
        line-height: 1.8;
        margin-bottom: 35px;
    }
    
    .trade-in-buttons {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
    }
    
    .zalo-btn-mock {
        background: linear-gradient(135deg, #0084FF 0%, #00c6ff 100%);
        color: #FFFFFF !important;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 14px 28px;
        border-radius: 50px;
        border: none;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 8px 25px rgba(0, 132, 255, 0.3);
    }
    
    .zalo-btn-mock:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 30px rgba(0, 132, 255, 0.45);
    }
    
    .zalo-btn-icon {
        width: 22px;
        height: 22px;
        background: #FFFFFF;
        color: #0084FF;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        font-size: 11px;
    }
    
    .steps-btn {
        background: rgba(255, 255, 255, 0.08);
        color: #FFFFFF !important;
        border: 1px solid rgba(255, 255, 255, 0.2);
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 14px 28px;
        border-radius: 50px;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.3s;
        backdrop-filter: blur(10px);
    }
    
    .steps-btn:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: #FFFFFF;
    }
    
    /* Interactive Navigation Bar */
    .trade-in-menu-bar {
        background: var(--surface-card);
        border: 1px solid var(--border);
        border-radius: 20px;
        margin-top: 35px;
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        padding: 15px;
        gap: 12px;
        box-shadow: var(--shadow-card);
    }
    
    .trade-in-menu-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        padding: 12px;
        cursor: pointer;
        border-radius: 14px;
        color: var(--text-secondary);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .trade-in-menu-item:hover {
        background: var(--surface-muted);
        color: var(--trade-primary);
        transform: translateY(-2px);
    }
    
    .trade-in-menu-item.is-active {
        color: var(--trade-primary);
        background: var(--trade-primary-glow);
        border: 1px solid rgba(59, 130, 246, 0.3);
    }
    
    .menu-icon-circle {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: var(--surface-muted);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        transition: all 0.3s;
        color: var(--text-primary);
    }
    
    .trade-in-menu-item:hover .menu-icon-circle {
        background: var(--trade-primary);
        color: #FFFFFF;
    }
    
    .trade-in-menu-item.is-active .menu-icon-circle {
        background: var(--trade-primary);
        color: #FFFFFF;
    }
    
    .menu-text {
        font-size: 13px;
        font-weight: 700;
        text-align: center;
    }
    
    /* Calculator Section - Glassmorphism Card with Dynamic Border Glow */
    .calc-card {
        background: var(--surface-card);
        border: 2px solid var(--border);
        border-radius: 24px;
        padding: 45px;
        margin-top: 40px;
        box-shadow: var(--shadow-card);
        transition: border-color 0.4s ease, box-shadow 0.4s ease;
        position: relative;
    }
    
    .calc-card.lcd-active { border-color: var(--trade-lcd-color); box-shadow: 0 10px 40px rgba(59,130,246,0.1); }
    .calc-card.cpu-active { border-color: var(--trade-cpu-color); box-shadow: 0 10px 40px rgba(168,85,247,0.1); }
    .calc-card.mainboard-active { border-color: var(--trade-main-color); box-shadow: 0 10px 40px rgba(245,158,11,0.1); }
    .calc-card.vga-active { border-color: var(--trade-vga-color); box-shadow: 0 10px 40px rgba(239,68,68,0.1); }

    .calc-title {
        font-size: 28px;
        font-weight: 900;
        margin-bottom: 8px;
        text-transform: uppercase;
        color: var(--text-primary);
        letter-spacing: -0.5px;
    }
    
    .calc-subtitle {
        color: var(--text-secondary);
        font-size: 14px;
        margin-bottom: 35px;
    }
    
    .calc-label {
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--text-primary);
        margin-bottom: 12px;
        display: block;
        opacity: 0.8;
    }
    
    /* Category Tab buttons */
    .category-tabs {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 35px;
    }
    
    .category-tab-btn {
        border: 1px solid var(--border);
        background: var(--surface-card);
        color: var(--text-primary);
        padding: 16px;
        font-size: 14px;
        font-weight: 700;
        border-radius: 12px;
        cursor: pointer;
        text-align: center;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    
    .category-tab-btn:hover {
        border-color: var(--trade-primary);
        color: var(--trade-primary);
        background: var(--surface-muted);
        transform: translateY(-2px);
    }
    
    .category-tab-btn.is-active {
        background: var(--trade-primary);
        border-color: var(--trade-primary);
        color: #FFFFFF;
        box-shadow: 0 6px 20px var(--trade-primary-glow);
    }

    #tab_LCD.is-active { background: var(--trade-lcd-color); border-color: var(--trade-lcd-color); }
    #tab_CPU.is-active { background: var(--trade-cpu-color); border-color: var(--trade-cpu-color); }
    #tab_Mainboard.is-active { background: var(--trade-main-color); border-color: var(--trade-main-color); }
    #tab_VGA.is-active { background: var(--trade-vga-color); border-color: var(--trade-vga-color); }
    
    /* Specs layout */
    .specs-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 35px;
    }
    
    .spec-field {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    
    .spec-select {
        width: 100%;
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 14px 18px;
        background: var(--surface-card);
        color: var(--text-primary);
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
    }
    
    .spec-select:focus {
        border-color: var(--trade-primary);
        box-shadow: 0 0 0 4px var(--trade-primary-glow);
    }
    
    /* Appearance condition cards */
    .appearance-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 35px;
    }
    
    .appearance-card {
        border: 1px solid var(--border);
        background: var(--surface-card);
        border-radius: 16px;
        padding: 20px;
        display: flex;
        gap: 16px;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }
    
    .appearance-card:hover {
        border-color: var(--trade-primary);
        background: var(--surface-muted);
        transform: translateY(-2px);
    }
    
    .appearance-card.is-active {
        border-color: var(--trade-primary);
        background: var(--trade-primary-glow);
        box-shadow: 0 4px 15px var(--trade-primary-glow);
    }
    
    .appearance-radio {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 2px solid var(--border);
        margin-top: 2px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
        flex-shrink: 0;
    }
    
    .appearance-card.is-active .appearance-radio {
        border-color: var(--trade-primary);
    }
    
    .appearance-card.is-active .appearance-radio::after {
        content: '';
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--trade-primary);
    }
    
    .appearance-info h4 {
        font-size: 15px;
        font-weight: 800;
        margin-bottom: 6px;
        color: var(--text-primary);
    }
    
    .appearance-info p {
        font-size: 13px;
        color: var(--text-secondary);
        line-height: 1.6;
    }
    
    /* Checkbox list */
    .checkbox-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 35px;
    }
    
    .checkbox-card {
        border: 1px solid var(--border);
        background: var(--surface-card);
        border-radius: 12px;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        user-select: none;
    }
    
    .checkbox-card:hover {
        border-color: var(--trade-primary);
        background: var(--surface-muted);
        transform: translateY(-2px);
    }
    
    .checkbox-card.is-checked {
        border-color: var(--trade-primary);
        background: var(--trade-primary-glow);
    }
    
    .checkbox-icon {
        width: 18px;
        height: 18px;
        border-radius: 6px;
        border: 2px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        color: #FFFFFF;
        transition: all 0.3s;
        flex-shrink: 0;
    }
    
    .checkbox-card.is-checked .checkbox-icon {
        background: var(--trade-primary);
        border-color: var(--trade-primary);
    }
    
    .checkbox-text {
        font-size: 13px;
        font-weight: 700;
        color: var(--text-primary);
    }
    
    /* Submit button */
    .calc-submit-btn {
        width: 100%;
        background: linear-gradient(135deg, var(--trade-primary) 0%, #1e40af 100%);
        color: #FFFFFF;
        font-size: 15px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 18px;
        border: none;
        border-radius: 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 15px var(--trade-primary-glow);
    }
    
    .calc-submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
    }
    
    /* Animated Dynamic Result Card */
    .result-card {
        margin-top: 30px;
        background: radial-gradient(circle at 0% 0%, rgba(59, 130, 246, 0.08) 0%, rgba(147, 51, 234, 0.04) 100%);
        border: 2px dashed rgba(59, 130, 246, 0.4);
        border-radius: 20px;
        padding: 30px;
        display: none;
        animation: slideUpFade 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    }
    
    @keyframes slideUpFade {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .result-header {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        font-weight: 800;
        color: var(--text-secondary);
        margin-bottom: 10px;
    }
    
    .result-price-range {
        font-size: 38px;
        font-weight: 900;
        color: var(--trade-primary);
        margin-bottom: 15px;
        text-shadow: 0 4px 10px rgba(59,130,246,0.1);
    }
    
    .result-details {
        font-size: 14px;
        color: var(--text-secondary);
        line-height: 1.7;
    }
    
    /* Clean Steps Section */
    .steps-section {
        margin-top: 60px;
    }
    
    .steps-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-top: 30px;
    }
    
    .step-card {
        background: var(--surface-card);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 30px 24px;
        box-shadow: var(--shadow-card);
        transition: all 0.3s;
        position: relative;
    }
    
    .step-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-hover);
        border-color: var(--trade-primary);
    }
    
    .step-num {
        font-size: 32px;
        font-weight: 900;
        color: var(--trade-primary);
        margin-bottom: 15px;
        display: block;
        opacity: 0.8;
    }
    
    .step-card h4 {
        font-size: 16px;
        font-weight: 800;
        margin-bottom: 10px;
        color: var(--text-primary);
        text-transform: uppercase;
    }
    
    .step-card p {
        font-size: 13px;
        color: var(--text-secondary);
        line-height: 1.7;
    }
    
    /* Bottom Alert Bar */
    .bottom-trade-bar {
        background: linear-gradient(90deg, #111827 0%, #1e1b4b 100%);
        color: #FFFFFF;
        border-radius: 20px;
        padding: 30px 40px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 50px;
        gap: 30px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }
    
    .bottom-trade-left {
        display: flex;
        align-items: center;
        gap: 20px;
    }
    
    .bottom-trade-icon {
        width: 54px;
        height: 54px;
        background: rgba(255, 255, 255, 0.1);
        color: var(--trade-primary);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
        border: 1px solid rgba(255,255,255,0.15);
    }
    
    .bottom-trade-info h3 {
        font-size: 16px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }
    
    .bottom-trade-info p {
        font-size: 13px;
        opacity: 0.8;
        line-height: 1.6;
    }
    
    .bottom-trade-actions {
        display: flex;
        gap: 14px;
        flex-shrink: 0;
    }
    
    .bottom-btn-outline {
        border: 1px solid rgba(255,255,255,0.3);
        color: #FFFFFF;
        padding: 12px 24px;
        border-radius: 50px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        background: transparent;
        transition: all 0.3s;
    }
    
    .bottom-btn-outline:hover {
        background: rgba(255,255,255,0.08);
        border-color: #FFFFFF;
    }
    
    .bottom-btn-solid {
        background: var(--trade-primary);
        color: #FFFFFF;
        padding: 12px 24px;
        border-radius: 50px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        border: none;
        transition: all 0.3s;
        box-shadow: 0 4px 15px var(--trade-primary-glow);
    }
    
    .bottom-btn-solid:hover {
        background: var(--primary-hover);
        transform: translateY(-2px);
    }
    
    /* Modern Zalo Modal Overlay */
    .zalo-modal {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(8px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        opacity: 0;
        pointer-events: none;
        transition: all 0.3s ease;
    }
    
    .zalo-modal.is-open {
        opacity: 1;
        pointer-events: auto;
    }
    
    .zalo-modal-card {
        background: var(--surface-card);
        border-radius: 24px;
        width: 100%;
        max-width: 440px;
        padding: 40px 30px;
        box-shadow: 0 25px 50px rgba(0,0,0,0.3);
        text-align: center;
        position: relative;
        border: 1px solid var(--border);
        transform: scale(0.9);
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    
    .zalo-modal.is-open .zalo-modal-card {
        transform: scale(1);
    }
    
    .zalo-modal-close {
        position: absolute;
        top: 20px; right: 20px;
        font-size: 18px;
        color: var(--text-secondary);
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .zalo-modal-close:hover {
        color: var(--text-primary);
        transform: rotate(90deg);
    }
    
    .zalo-modal-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #0084FF 0%, #00c6ff 100%);
        color: #FFFFFF;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        margin: 0 auto 20px;
        box-shadow: 0 8px 25px rgba(0, 132, 255, 0.3);
    }
    
    .zalo-modal h3 {
        font-size: 20px;
        font-weight: 900;
        margin-bottom: 8px;
        color: var(--text-primary);
    }
    
    .zalo-modal p {
        font-size: 14px;
        color: var(--text-secondary);
        line-height: 1.7;
        margin-bottom: 25px;
    }
    
    .zalo-modal-badge {
        display: inline-block;
        padding: 6px 16px;
        border-radius: 20px;
        background: var(--surface-muted);
        color: var(--text-primary);
        font-size: 13px;
        font-weight: 700;
        border: 1px solid var(--border);
    }
    
    /* Responsive grids */
    @media (max-width: 992px) {
        .specs-grid { grid-template-columns: repeat(2, 1fr); }
        .checkbox-grid { grid-template-columns: repeat(2, 1fr); }
        .bottom-trade-bar { flex-direction: column; align-items: stretch; }
        .bottom-trade-actions { justify-content: flex-end; }
        .steps-grid { grid-template-columns: repeat(2, 1fr); }
    }
    
    @media (max-width: 768px) {
        .trade-in-menu-bar { grid-template-columns: repeat(3, 1fr); }
        .category-tabs { grid-template-columns: repeat(2, 1fr); }
        .appearance-grid { grid-template-columns: 1fr; }
        .trade-in-title { font-size: 32px; }
        .specs-grid { grid-template-columns: 1fr; }
        .steps-grid { grid-template-columns: 1fr; }
        .checkbox-grid { grid-template-columns: 1fr; }
    }
</style>

<section class="container breadcrumb">
    <a href="<?= url('/') ?>">Trang chủ</a> <i class="fa-solid fa-chevron-right"></i>
    <span>Thu cũ đổi mới máy cũ</span>
</section>

<div class="container trade-in-wrapper">
    <!-- 1. Header Banner -->
    <div class="trade-in-banner">
        <div class="trade-in-banner-content">
            <span class="trade-in-tag">Nâng cấp công nghệ cùng TechPilot</span>
            <h1 class="trade-in-title">Bán dễ dàng. <span>Lên đời tiết kiệm</span></h1>
            <p class="trade-in-desc">
                Liên hệ TechPilot qua Zalo chi nhánh NM Hiếu hoặc Zalo chi nhánh TDK để được tư vấn thu cũ đổi mới LCD, CPU, Mainboard, VGA sau khi kiểm tra thực tế.
            </p>
            <div class="trade-in-buttons">
                <button type="button" class="zalo-btn-mock" onclick="openZaloModal('TDK')">
                    <span class="zalo-btn-icon">Z</span> Kết nối Zalo TDK
                </button>
                <button type="button" class="zalo-btn-mock" onclick="openZaloModal('NM Hiếu')">
                    <span class="zalo-btn-icon">Z</span> Kết nối Zalo NM Hiếu
                </button>
                <a href="#steps" class="steps-btn">
                    <i class="fa-solid fa-list-ol"></i> 4 bước thu mua
                </a>
            </div>
        </div>
    </div>

    <!-- 2. Category Quick Menu Navigation -->
    <div class="trade-in-menu-bar">
        <div class="trade-in-menu-item is-active" id="menu_LCD" onclick="switchCategoryTab('LCD')">
            <div class="menu-icon-circle"><i class="fa-solid fa-desktop"></i></div>
            <span class="menu-text">Màn hình</span>
        </div>
        <div class="trade-in-menu-item" id="menu_CPU" onclick="switchCategoryTab('CPU')">
            <div class="menu-icon-circle"><i class="fa-solid fa-microchip"></i></div>
            <span class="menu-text">CPU</span>
        </div>
        <div class="trade-in-menu-item" id="menu_Mainboard" onclick="switchCategoryTab('Mainboard')">
            <div class="menu-icon-circle"><i class="fa-solid fa-cube"></i></div>
            <span class="menu-text">Mainboard</span>
        </div>
        <div class="trade-in-menu-item" id="menu_VGA" onclick="switchCategoryTab('VGA')">
            <div class="menu-icon-circle"><i class="fa-solid fa-server"></i></div>
            <span class="menu-text">VGA</span>
        </div>
        <a href="<?= url('home/search?cat=hang-cu-gia-tot') ?>" class="trade-in-menu-item">
            <div class="menu-icon-circle"><i class="fa-solid fa-tags"></i></div>
            <span class="menu-text">Hàng cũ giá tốt</span>
        </a>
        <a href="#calculator" class="trade-in-menu-item" style="border-left: 1px solid var(--border); padding-left: 20px;">
            <div class="menu-icon-circle" style="background: var(--trade-primary); color: #FFFFFF;"><i class="fa-solid fa-calculator"></i></div>
            <span class="menu-text" style="color: var(--trade-primary);">Tra cứu ngay</span>
        </a>
    </div>

    <!-- 3. Calculator Main Card -->
    <div class="calc-card lcd-active" id="calculator">
        <h2 class="calc-title">Ước tính giá thu</h2>
        <p class="calc-subtitle">Chọn nhóm hàng và thông tin cấu hình sản phẩm để xem khoảng giá tham khảo trước khi chat TechPilot.</p>
        
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
                <!-- Dynamic dropdown injection -->
            </div>
        </div>

        <!-- Ngoại Quan Choice Cards -->
        <span class="calc-label">Ngoại quan sản phẩm</span>
        <div class="appearance-grid">
            <div class="appearance-card is-active" id="appear_good" onclick="selectAppearance('good')">
                <div class="appearance-radio"></div>
                <div class="appearance-info">
                    <h4>Ngoại quan đẹp</h4>
                    <p>Ít trầy xước, tem/ốc niêm phong nguyên bản và các cổng kết nối bình thường.</p>
                </div>
            </div>
            <div class="appearance-card" id="appear_bad" onclick="selectAppearance('bad')">
                <div class="appearance-radio"></div>
                <div class="appearance-info">
                    <h4>Ngoại quan cũ / trầy xước</h4>
                    <p>Trầy xước rỉ, móp nhẹ, oxy hóa kim loại hoặc cần kỹ thuật viên kiểm tra kỹ.</p>
                </div>
            </div>
        </div>

        <!-- Checkbox accessories -->
        <span class="calc-label">Tình trạng phụ kiện đi kèm</span>
        <div class="checkbox-grid">
            <div class="checkbox-card is-checked" id="chk_box" onclick="toggleCheckbox('box')">
                <div class="checkbox-icon"><i class="fa-solid fa-check"></i></div>
                <span class="checkbox-text">Còn hộp (Box)</span>
            </div>
            <div class="checkbox-card is-checked" id="chk_warranty" onclick="toggleCheckbox('warranty')">
                <div class="checkbox-icon"><i class="fa-solid fa-check"></i></div>
                <span class="checkbox-text">Còn bảo hành hãng</span>
            </div>
            <div class="checkbox-card is-checked" id="chk_cable" onclick="toggleCheckbox('cable')">
                <div class="checkbox-icon"><i class="fa-solid fa-check"></i></div>
                <span class="checkbox-text">Cáp tín hiệu gốc</span>
            </div>
            <div class="checkbox-card is-checked" id="chk_power" onclick="toggleCheckbox('power')">
                <div class="checkbox-icon"><i class="fa-solid fa-check"></i></div>
                <span class="checkbox-text">Bộ nguồn adapter zin</span>
            </div>
        </div>

        <!-- Submit Button -->
        <button type="button" class="calc-submit-btn" onclick="estimatePrice()">
            <i class="fa-solid fa-calculator"></i> Ước tính giá thu mua
        </button>

        <!-- Dynamic Results Card -->
        <div class="result-card" id="resultCard">
            <div class="result-header">Khoảng giá thu mua ước tính</div>
            <div class="result-price-range" id="priceRangeText">0đ - 0đ</div>
            <div class="result-details">
                * Mức giá ước tính dựa trên thông tin khai báo. Giá chính thức sẽ được thông báo sau khi kỹ thuật viên kiểm tra trực tiếp sản phẩm.<br>
                Hãy liên hệ Zalo của chúng tôi ở bên dưới để nhận hỗ trợ nhanh chóng nhất!
            </div>
        </div>
    </div>

    <!-- 4. 4-Steps Process Section -->
    <section class="steps-section" id="steps">
        <div class="section__head">
            <h2>4 bước bán hoặc lên đời</h2>
        </div>
        <p style="color: var(--text-secondary); line-height: 1.8; max-width: 860px; font-size: 14px;">
            Hỗ trợ đổi cũ lấy mới với mức trợ giá tốt nhất thị trường. Quy trình thẩm định trực tiếp nhanh chóng tại quầy kỹ thuật TechPilot.
        </p>
        <div class="steps-grid">
            <div class="step-card">
                <span class="step-num">01</span>
                <h4>Liên hệ tư vấn</h4>
                <p>Gửi model chi tiết, kèm ảnh chụp các góc cạnh của linh kiện/máy qua Zalo NM Hiếu hoặc Zalo TDK.</p>
            </div>
            <div class="step-card">
                <span class="step-num">02</span>
                <h4>Kiểm tra tại chỗ</h4>
                <p>Mang máy đến showroom TechPilot để kỹ thuật viên test kỹ năng lực hoạt động và ngoại quan thực tế.</p>
            </div>
            <div class="step-card">
                <span class="step-num">03</span>
                <h4>Chốt giá thu</h4>
                <p>Nhận báo giá thu mua chính xác và cam kết không ép giá dựa trên tình trạng hao mòn thực tế của máy.</p>
            </div>
            <div class="step-card">
                <span class="step-num">04</span>
                <h4>Nhận tiền mặt/Đổi mới</h4>
                <p>TechPilot thanh toán tiền mặt/chuyển khoản lập tức hoặc cấn trừ tiền để bạn lấy máy mới cấu hình cao hơn.</p>
            </div>
        </div>
    </section>

    <!-- 5. Bottom Sticky-like Action Bar -->
    <div class="bottom-trade-bar">
        <div class="bottom-trade-left">
            <div class="bottom-trade-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div class="bottom-trade-info">
                <h3>Thẩm định chính xác - Trợ giá lên đời cao nhất</h3>
                <p>Liên hệ qua Zalo NM Hiếu hoặc Zalo TDK để nhận báo giá dự tính và hướng dẫn chuẩn bị thiết bị kiểm tra nhanh nhất.</p>
            </div>
        </div>
        <div class="bottom-trade-actions">
            <button type="button" class="bottom-btn-outline" onclick="openZaloModal('TDK')">Liên hệ TDK</button>
            <button type="button" class="bottom-btn-solid" onclick="openZaloModal('NM Hiếu')">Liên hệ NM Hiếu</button>
        </div>
    </div>
</div>

<!-- Modern Zalo Overlay Modal -->
<div class="zalo-modal" id="zaloModal" onclick="closeZaloModal(event)">
    <div class="zalo-modal-card" onclick="event.stopPropagation()">
        <span class="zalo-modal-close" onclick="closeZaloModal(null)"><i class="fa-solid fa-xmark"></i></span>
        <div class="zalo-modal-avatar">
            <i class="fa-solid fa-comment-dots"></i>
        </div>
        <h3 id="modalName">Zalo NM Hiếu</h3>
        <p>Hệ thống đang kết nối Zalo. Vui lòng quét mã QR trên màn hình hoặc click để gửi trực tiếp thông tin cấu hình cần bán cho tư vấn viên.</p>
        <span class="zalo-modal-badge" id="modalBranch">Kênh tư vấn hỗ trợ TechPilot</span>
    </div>
</div>

<script>
    const specsData = {
        LCD: {
            label: 'Thông số màn hình (LCD)',
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
            label: 'Thông số bộ vi xử lý (CPU)',
            fields: [
                { id: 'cpu_brand', label: 'Hãng sản xuất', options: ['Intel', 'AMD'] },
                { id: 'cpu_series', label: 'Dòng CPU', options: ['Core i3 / Ryzen 3', 'Core i5 / Ryzen 5', 'Core i7 / Ryzen 7', 'Core i9 / Ryzen 9'] },
                { id: 'cpu_gen', label: 'Thế hệ', options: ['Intel Gen 10 - 11 / Ryzen 3000', 'Gen 12 / Ryzen 5000', 'Gen 13 / Ryzen 7000', 'Gen 14 / Ryzen 9000'] }
            ],
            basePriceMin: 1200000,
            basePriceMax: 1800000
        },
        Mainboard: {
            label: 'Thông số bo mạch chủ (Mainboard)',
            fields: [
                { id: 'main_brand', label: 'Thương hiệu', options: ['ASUS', 'MSI', 'GIGABYTE', 'ASRock'] },
                { id: 'main_chipset', label: 'Dòng Chipset', options: ['Dòng H (H510/H610...)', 'Dòng B (B660/B760...)', 'Dòng Z / X (Z790/X670...)'] }
            ],
            basePriceMin: 500000,
            basePriceMax: 900000
        },
        VGA: {
            label: 'Thông số card đồ họa (VGA)',
            fields: [
                { id: 'vga_brand', label: 'Hãng GPU', options: ['NVIDIA GeForce', 'AMD Radeon'] },
                { id: 'vga_series', label: 'Dòng VGA', options: ['GTX 16 Series', 'RTX 20 Series', 'RTX 30 Series', 'RTX 40 Series', 'RX 6000 / 7000 Series'] },
                { id: 'vga_model', label: 'Dòng phân khúc', options: ['Phân khúc x50/x60 (VD: RTX 4060)', 'Phân khúc x70 (VD: RTX 4070)', 'Phân khúc x80/x90 (VD: RTX 4090)'] }
            ],
            basePriceMin: 2000000,
            basePriceMax: 3000000
        }
    };

    let currentCategory = 'LCD';
    let selectedAppearance = 'good';
    let checkboxStates = {
        box: true,
        warranty: true,
        cable: true,
        power: true
    };

    document.addEventListener('DOMContentLoaded', function () {
        renderSpecsForm('LCD');
    });

    function switchCategoryTab(cat) {
        currentCategory = cat;
        
        // Update active classes for category buttons
        document.querySelectorAll('.category-tab-btn').forEach(btn => btn.classList.remove('is-active'));
        const activeTab = document.getElementById('tab_' + cat);
        if (activeTab) activeTab.classList.add('is-active');

        // Update active classes for vertical menu navigation items
        document.querySelectorAll('.trade-in-menu-item').forEach(item => {
            item.classList.remove('is-active');
        });
        const activeMenu = document.getElementById('menu_' + cat);
        if (activeMenu) activeMenu.classList.add('is-active');

        // Set layout variables
        const calcCard = document.getElementById('calculator');
        calcCard.className = 'calc-card ' + cat.toLowerCase() + '-active';

        renderSpecsForm(cat);
        document.getElementById('resultCard').style.display = 'none';
    }

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

        container.style.gridTemplateColumns = `repeat(${catData.fields.length}, 1fr)`;
        if (window.innerWidth <= 992) {
            container.style.gridTemplateColumns = 'repeat(2, 1fr)';
        }
        if (window.innerWidth <= 768) {
            container.style.gridTemplateColumns = '1fr';
        }
    }

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

    function toggleCheckbox(id) {
        checkboxStates[id] = !checkboxStates[id];
        const card = document.getElementById('chk_' + id);
        
        if (checkboxStates[id]) {
            card.classList.add('is-checked');
        } else {
            card.classList.remove('is-checked');
        }
    }

    function estimatePrice() {
        const catData = specsData[currentCategory];
        let baseMin = catData.basePriceMin;
        let baseMax = catData.basePriceMax;

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

        let dropdownMultiplier = 1.0 + (totalDropdownScore / numDropdowns) * 0.9;
        let minPrice = baseMin * dropdownMultiplier;
        let maxPrice = baseMax * dropdownMultiplier;

        if (selectedAppearance === 'bad') {
            minPrice *= 0.55;
            maxPrice *= 0.55;
        }

        if (checkboxStates.box) { minPrice += 50000; maxPrice += 100000; }
        if (checkboxStates.warranty) { minPrice += 200000; maxPrice += 400000; }
        if (checkboxStates.cable) { minPrice += 30000; maxPrice += 60000; }
        if (checkboxStates.power) { minPrice += 50000; maxPrice += 120000; }

        const formatNumber = (num) => {
            return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(Math.round(num / 50000) * 50000);
        };

        const resultCard = document.getElementById('resultCard');
        const priceText = document.getElementById('priceRangeText');
        
        priceText.innerText = `${formatNumber(minPrice)} - ${formatNumber(maxPrice)}`;
        resultCard.style.display = 'block';
        resultCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

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

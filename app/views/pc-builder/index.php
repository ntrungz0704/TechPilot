<!-- CSS riêng cho trang Build PC nhằm đảm bảo giao diện lung linh, premium -->
<style>
    .pc-builder-container {
        max-width: 1440px;
        margin: 40px auto;
        padding: 0 40px;
        font-family: 'Inter', sans-serif;
    }
    @media (max-width: 768px) {
        .pc-builder-container {
            padding: 0 20px;
        }
    }
    .pc-builder-header {
        text-align: center;
        margin-bottom: 25px;
    }
    .pc-builder-header h1 {
        font-size: 32px;
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: 10px;
        background: linear-gradient(135deg, #0B63E5, #3B82F6);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .pc-builder-header p {
        color: var(--text-secondary);
        font-size: 15.5px;
    }

    /* Flow Tab Bar */
    .tp-flow-banner {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        padding: 16px 24px;
        margin-bottom: 30px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    .btn-flow-tab {
        background: #FFFFFF;
        border: 1px solid #CBD5E1;
        color: #475569;
        transition: all 0.2s ease;
    }
    .btn-flow-tab:hover {
        background: #F1F5F9;
        border-color: #94A3B8;
        color: #0F172A;
    }
    .btn-flow-tab.active {
        background: #0B63E5 !important;
        border-color: #0B63E5 !important;
        color: #FFFFFF !important;
        box-shadow: 0 4px 12px rgba(11, 99, 229, 0.25);
    }

    .pc-builder-layout {
        display: grid;
        grid-template-columns: 1fr 360px;
        gap: 30px;
        align-items: start;
    }
    @media (max-width: 991px) {
        .pc-builder-layout {
            grid-template-columns: 1fr;
        }
    }
    /* Bảng các linh kiện */
    .pc-builder-list {
        background: #FFFFFF;
        border: 1px solid var(--border);
        border-radius: 16px;
        box-shadow: var(--shadow-card);
        padding: 15px;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    .pc-builder-row {
        display: grid;
        grid-template-columns: 80px 180px 1fr 220px;
        align-items: center;
        padding: 16px 20px;
        border: 1px solid var(--border);
        border-radius: 12px;
        background: #FAFAFA;
        transition: all 0.2s ease;
    }
    .pc-builder-row:hover {
        border-color: #CBD5E1;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        background: #FFFFFF;
    }
    .pc-builder-icon {
        width: 48px;
        height: 48px;
        background: #EFF6FF;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #0B63E5;
        font-size: 20px;
    }
    .pc-builder-part-name {
        font-size: 15px;
        font-weight: 700;
        color: var(--text-primary);
    }
    .pc-builder-selected-info {
        display: flex;
        align-items: center;
        gap: 15px;
        min-width: 0;
    }
    .pc-builder-selected-img {
        width: 50px;
        height: 50px;
        object-fit: contain;
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 2px;
        background: #FFFFFF;
        flex-shrink: 0;
    }
    .pc-builder-selected-details {
        display: flex;
        flex-direction: column;
        gap: 4px;
        min-width: 0;
    }
    .pc-builder-selected-title {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-primary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .pc-builder-selected-price {
        font-size: 13.5px;
        font-weight: 700;
        color: #EF4444;
    }
    .pc-builder-placeholder {
        color: var(--text-secondary);
        font-size: 14px;
        font-style: italic;
    }
    .pc-builder-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
    }
    .btn-select-part, .btn-owned-part {
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        border: 1px solid #0B63E5;
        background: #EFF6FF;
        color: #0B63E5;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-select-part:hover {
        background: #0B63E5;
        color: #FFFFFF;
    }
    .btn-owned-part {
        border-color: #10B981;
        background: #ECFDF5;
        color: #059669;
    }
    .btn-owned-part:hover, .btn-owned-part.active {
        background: #10B981;
        color: #FFFFFF;
    }
    .btn-change-part {
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 12.5px;
        font-weight: 600;
        border: 1px solid #CBD5E1;
        background: #FFFFFF;
        color: #475569;
        cursor: pointer;
    }
    .btn-change-part:hover {
        border-color: #0B63E5;
        color: #0B63E5;
    }
    .btn-remove-part {
        padding: 8px 10px;
        border-radius: 8px;
        font-size: 12.5px;
        border: 1px solid #FCA5A5;
        background: #FEF2F2;
        color: #EF4444;
        cursor: pointer;
    }
    .btn-remove-part:hover {
        background: #EF4444;
        color: #FFFFFF;
    }

    /* Sidebar summary */
    .pc-builder-summary-card {
        background: #FFFFFF;
        border: 1px solid var(--border);
        border-radius: 16px;
        box-shadow: var(--shadow-card);
        padding: 24px;
        position: sticky;
        top: 20px;
    }
    .pc-builder-summary-title {
        font-size: 18px;
        font-weight: 800;
        color: var(--text-primary);
        padding-bottom: 12px;
        border-bottom: 1px solid var(--border);
        margin-bottom: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .pc-builder-summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 12px;
        font-size: 14px;
        color: var(--text-secondary);
    }
    .pc-builder-total-price {
        font-size: 24px;
        font-weight: 800;
        color: #EF4444;
        text-align: right;
        margin: 15px 0 20px 0;
    }
    .btn-add-config-to-cart {
        width: 100%;
        padding: 14px;
        border-radius: 10px;
        background: linear-gradient(135deg, #0B63E5, #2563EB);
        color: #FFFFFF;
        font-size: 16px;
        font-weight: 700;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 4px 12px rgba(11, 99, 229, 0.25);
    }
    .btn-add-config-to-cart:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(11, 99, 229, 0.35);
    }
    .btn-reset-config {
        width: 100%;
        margin-top: 10px;
        padding: 10px;
        border-radius: 8px;
        background: transparent;
        color: #64748B;
        font-size: 13.5px;
        font-weight: 600;
        border: 1px solid #CBD5E1;
        cursor: pointer;
    }
    .btn-reset-config:hover {
        background: #F8FAFC;
        color: #0F172A;
    }

    /* Modal Select Product */
    .pc-modal-backdrop {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .pc-modal {
        background: #FFFFFF;
        width: 100%;
        max-width: 900px;
        max-height: 85vh;
        border-radius: 20px;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .pc-modal-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .pc-modal-title {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
    }
    .pc-modal-close {
        background: none;
        border: none;
        font-size: 22px;
        color: #64748B;
        cursor: pointer;
    }
    .pc-modal-body {
        padding: 20px 24px;
        overflow-y: auto;
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    .pc-search-box {
        position: relative;
    }
    .pc-search-box input {
        width: 100%;
        padding: 12px 16px 12px 42px;
        border-radius: 10px;
        border: 1px solid var(--border);
        font-size: 14px;
        outline: none;
    }
    .pc-search-box i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94A3B8;
    }
    .pc-modal-products-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .pc-modal-item {
        display: grid;
        grid-template-columns: 60px 1fr 140px 110px;
        align-items: center;
        padding: 12px 16px;
        border: 1px solid var(--border);
        border-radius: 12px;
        gap: 15px;
        transition: all 0.2s ease;
    }
    .pc-modal-item:hover {
        border-color: #0B63E5;
        background: #F8FAFC;
    }
    .pc-modal-item-img {
        width: 50px;
        height: 50px;
        object-fit: contain;
    }
    .pc-modal-item-name {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-primary);
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .pc-modal-item-price {
        font-size: 15px;
        font-weight: 700;
        color: #EF4444;
    }
    .btn-choose-item {
        padding: 8px 14px;
        border-radius: 8px;
        background: #0B63E5;
        color: #FFFFFF;
        border: none;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
    }
    .pc-builder-compatibility-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11.5px;
        font-weight: 600;
        color: #059669;
        background: #D1FAE5;
        padding: 2px 8px;
        border-radius: 4px;
        width: fit-content;
    }
    .pc-builder-blocker-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11.5px;
        font-weight: 600;
        color: #DC2626;
        background: #FEE2E2;
        padding: 2px 8px;
        border-radius: 4px;
        width: fit-content;
    }
    .pc-builder-reason-text {
        display: block;
        font-size: 11px;
        color: #DC2626;
        font-weight: 500;
    }

    /* Pre-built PCs Card Grid */
    .prebuilt-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
    }
    .prebuilt-card {
        background: #FFFFFF;
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 18px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: all 0.2s ease;
    }
    .prebuilt-card:hover {
        box-shadow: var(--shadow-card);
        border-color: #0B63E5;
    }
    .prebuilt-img {
        width: 100%;
        height: 180px;
        object-fit: contain;
        margin-bottom: 12px;
    }
</style>

<div class="pc-builder-container">
    <div class="pc-builder-header">
        <h1><i class="fa-solid fa-sliders"></i> Xây Dựng Cấu Hình PC & Tự Chọn Linh Kiện</h1>
        <p>Hệ thống tự động kiểm tra tương thích Socket, RAM, Kích thước Case & Ước tính Nguồn (PSU) chính xác 100%</p>
    </div>

    <!-- Thanh Chọn Luồng Nghiệp Vụ (3 Flow Selector) -->
    <div class="tp-flow-banner">
        <div style="font-weight:700; color:#1E293B; margin-bottom:10px; font-size:14.5px;">
            <i class="fa-solid fa-route" style="color:#0B63E5;"></i> Bạn đang muốn xây dựng cấu hình thế nào?
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-flow-tab active" id="tabFlowBuildFull" onclick="setFlowMode('build_full')">
                <i class="fa-solid fa-desktop me-1"></i> 🖥 Build máy mới hoàn chỉnh
            </button>
            <button type="button" class="btn btn-sm btn-flow-tab" id="tabFlowUpgrade" onclick="setFlowMode('upgrade')">
                <i class="fa-solid fa-wrench me-1"></i> 🔧 Nâng cấp máy đang có / Đã có sẵn linh kiện
            </button>
            <button type="button" class="btn btn-sm btn-flow-tab" id="tabFlowPrebuilt" onclick="setFlowMode('prebuilt')">
                <i class="fa-solid fa-box-open me-1"></i> 📦 Chọn PC lắp sẵn TechPilot
            </button>
        </div>
    </div>

    <!-- TAB 1 & TAB 2: Giao diện Builder Tự chọn linh kiện -->
    <div id="pcBuilderMainView" class="pc-builder-layout">
        <!-- Cột trái: Danh sách linh kiện chọn -->
        <main class="pc-builder-list">
            <?php foreach ($data['parts'] as $key => $info): ?>
                <div class="pc-builder-row" data-part="<?= $key ?>">
                    <div class="pc-builder-icon">
                        <i class="<?= $info['icon'] ?>"></i>
                    </div>
                    <div class="pc-builder-part-name">
                        <?= $info['name'] ?>
                    </div>
                    <div class="pc-builder-selected-info" id="selected-info-<?= $key ?>">
                        <span class="pc-builder-placeholder">Chưa chọn linh kiện</span>
                    </div>
                    <div class="pc-builder-actions" id="actions-<?= $key ?>">
                        <button type="button" class="btn-select-part" onclick="openSelectModal('<?= $key ?>', '<?= addslashes($info['name']) ?>')">
                            <i class="fa-solid fa-plus"></i> Chọn mua
                        </button>
                        <button type="button" class="btn-owned-part" onclick="toggleOwnedPart('<?= $key ?>', '<?= addslashes($info['name']) ?>')" title="Khai báo bạn đã sở hữu linh kiện này">
                            <i class="fa-solid fa-check"></i> Đã có
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </main>

        <!-- Cột phải: Khung tổng quan Nguồn (PSU) & Tóm tắt cấu hình -->
        <aside style="display:flex; flex-direction:column; gap:20px;">
            <!-- Khung phân tích nguồn PSU -->
            <div class="pc-builder-summary-card" style="padding: 20px;">
                <div class="pc-builder-summary-title" style="font-size: 16px; margin-bottom: 12px;">
                    <span><i class="fa-solid fa-bolt" style="color: #EAB308;"></i> Ước tính công suất Nguồn</span>
                </div>
                <div id="psu-analysis-placeholder" style="font-size:13px; color:var(--text-secondary); text-align:center; padding:15px 0;">
                    Vui lòng chọn CPU hoặc VGA để hệ thống bắt đầu tính toán công suất nguồn phù hợp.
                </div>
                <div id="psu-analysis-content" style="display:none;">
                    <div class="pc-builder-summary-row" style="font-size:13px;">
                        <span>Công suất tải đỉnh ước tính:</span>
                        <span id="psu-estimated-w" style="font-weight: 700; color: #0B63E5;">0W</span>
                    </div>
                    <div class="pc-builder-summary-row" style="font-size:13px; margin-top:4px;">
                        <span>Tối thiểu do VGA đề xuất:</span>
                        <span id="psu-gpu-minimum-w" style="font-weight: 600; color: var(--text-secondary);">0W</span>
                    </div>
                    <div class="pc-builder-summary-row" style="font-size:12px; margin-top:4px; font-style:italic; color:#64748B;" id="psu-vga-status-row">
                        <span id="psu-vga-status-label">(Chưa chọn VGA)</span>
                    </div>
                    <div class="pc-builder-summary-row" style="margin-top:8px; border-top: 1px dashed #E2E8F0; padding-top:8px;">
                        <span style="font-weight:600;">Nguồn khuyến nghị:</span>
                        <span id="psu-recommended-w" style="font-weight: 700; color: #EF4444; font-size:16px;">300W</span>
                    </div>
                    <div class="pc-builder-summary-row" style="font-size:13px; margin-top:6px;" id="psu-status-badge-row">
                        <span>Trạng thái nguồn:</span>
                        <span id="psu-status-badge" style="font-weight:700; padding:2px 8px; border-radius:4px; font-size:12px; background:#E2E8F0; color:#475569;">Đang kiểm tra</span>
                    </div>
                    <div style="font-size:11px; color:#94A3B8; font-style:italic; line-height:1.4; margin-top:8px; text-align:right;">
                        * Đã bao gồm 30% dự phòng công suất an toàn.
                    </div>
                </div>
            </div>

            <!-- Tổng tiền & Danh sách lỗi -->
            <div class="pc-builder-summary-card">
                <div class="pc-builder-summary-title">
                    <span>Tóm tắt cấu hình</span>
                    <i class="fa-solid fa-desktop" style="color: #0B63E5;"></i>
                </div>
                
                <div class="pc-builder-summary-row">
                    <span>Linh kiện trong cấu hình:</span>
                    <span id="selected-count" style="font-weight: 700; color: var(--text-primary);">0</span>
                </div>
                <div class="pc-builder-summary-row" style="font-size:13px;">
                    <span>Mua mới / Đã có sẵn:</span>
                    <span><strong id="buy-count" style="color:#0B63E5;">0</strong> mua / <strong id="owned-count" style="color:#059669;">0</strong> sẵn</span>
                </div>

                <!-- Blockers & Warnings List -->
                <div id="build-alerts-container" style="display:none; margin-top:15px; font-size:12.5px;">
                    <div style="font-weight:700; margin-bottom:8px; color:var(--text-primary);">Kiểm tra tương thích:</div>
                    <div id="build-alerts-list" style="display:flex; flex-direction:column; gap:8px;"></div>
                </div>
                
                <div style="border-top: 1px solid var(--border); margin: 15px 0;"></div>
                
                <div style="font-size: 13.5px; color: var(--text-secondary); text-align: right;">Tổng giá tiền cần thanh toán:</div>
                <div class="pc-builder-total-price" id="total-price-display">0đ</div>
                
                <button type="button" class="btn-add-config-to-cart" id="btnAddToCartSubmit" onclick="handleAddToCartClick()">
                    <i class="fa-solid fa-cart-plus"></i> <span id="btnAddToCartText">Thêm vào giỏ hàng</span>
                </button>
                
                <button type="button" class="btn-reset-config" onclick="resetConfig()">
                    <i class="fa-solid fa-trash-can"></i> Xóa hết cấu hình
                </button>
            </div>
        </aside>
    </div>

    <!-- TAB 3: Giao diện PC Lắp Sẵn mẫu -->
    <div id="pcPrebuiltMainView" style="display:none;">
        <div style="margin-bottom:20px;">
            <h3 style="font-size:20px; font-weight:800; color:#1E293B;">📦 Danh Sách Bộ PC Lắp Sẵn Tối Ưu Bởi TechPilot</h3>
            <p style="color:#64748B; font-size:14.5px;">Đã được các chuyên gia TechPilot lắp ráp và test độ ổn định. Mua nguyên bộ hoặc tùy chỉnh theo ý bạn!</p>
        </div>
        <div class="prebuilt-grid" id="prebuiltProductsGrid">
            <div style="padding:30px; text-align:center; color:#64748B; grid-column: 1/-1;">
                <i class="fa-solid fa-spinner fa-spin me-2"></i> Đang tải danh sách PC lắp sẵn...
            </div>
        </div>
    </div>
</div>

<!-- Modal chọn sản phẩm linh kiện -->
<div class="pc-modal-backdrop" id="pcSelectModalBackdrop" onclick="closeSelectModal()">
    <div class="pc-modal" onclick="event.stopPropagation()">
        <div class="pc-modal-header">
            <h4 class="pc-modal-title" id="pcModalTitle">Chọn linh kiện</h4>
            <button type="button" class="pc-modal-close" onclick="closeSelectModal()">&times;</button>
        </div>
        <div class="pc-modal-body">
            <!-- Alert tương thích -->
            <div id="compatibilityAlert" style="display:none; padding: 10px 14px; background-color: #EFF6FF; border-left: 4px solid #3B82F6; color: #1E3A8A; font-size: 12.5px; border-radius: 4px; font-weight: 600; margin-bottom: 5px;">
                <i class="fa-solid fa-circle-info"></i> Tự động hiển thị linh kiện tương thích với thiết bị của bạn.
            </div>
            
            <!-- Search bar -->
            <div class="pc-search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="pcModalSearchInput" placeholder="Tìm kiếm sản phẩm theo tên..." oninput="debounceSearch()">
            </div>
            
            <!-- Products list container -->
            <div id="pcModalProductsListContainer">
                <div class="pc-modal-loading"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải danh sách linh kiện...</div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Xác Nhận Cảnh Báo (Warning Confirmation Modal) -->
<div class="pc-modal-backdrop" id="pcWarningModalBackdrop">
    <div class="pc-modal" style="max-width:550px;" onclick="event.stopPropagation()">
        <div class="pc-modal-header" style="background:#FFFBEB; border-bottom:1px solid #FCD34D;">
            <h4 class="pc-modal-title" style="color:#B45309;"><i class="fa-solid fa-triangle-exclamation me-2"></i> Cấu hình có cảnh báo</h4>
            <button type="button" class="pc-modal-close" onclick="closeWarningModal()">&times;</button>
        </div>
        <div class="pc-modal-body" style="gap:12px;">
            <p style="font-size:14px; color:#475569; margin:0;">
                Cấu hình của bạn có một số lưu ý/cảnh báo nhưng vẫn có thể tiếp tục. Bạn có muốn tiếp tục thêm vào giỏ hàng không?
            </p>
            <div id="modalWarningListContainer" style="display:flex; flex-direction:column; gap:8px;"></div>
            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:15px;">
                <button type="button" class="btn btn-secondary" onclick="closeWarningModal()">Quay lại chỉnh sửa</button>
                <button type="button" class="btn btn-primary" onclick="proceedAddToCartAfterWarning()" style="background:#0B63E5; border:none; font-weight:700;">Tôi hiểu và tiếp tục</button>
            </div>
        </div>
    </div>
</div>

<!-- Javascript xử lý tương tác dynamic -->
<script>
    let pcConfig = {};
    let activePartKey = '';
    let searchDebounceTimeout = null;
    let psuAnalysisAbortController = null;
    let currentFlowMode = 'build_full'; // build_full | upgrade | prebuilt
    let currentWarnings = [];
    let currentBlockers = [];

    // Phục hồi từ localStorage nếu đã chọn từ trước
    if (localStorage.getItem('pc_config')) {
        try {
            pcConfig = JSON.parse(localStorage.getItem('pc_config')) || {};
        } catch (e) {
            pcConfig = {};
        }
    }
    if (localStorage.getItem('pc_flow_mode')) {
        currentFlowMode = localStorage.getItem('pc_flow_mode');
    }

    document.addEventListener('DOMContentLoaded', () => {
        setFlowMode(currentFlowMode, false);
        updateUI();
        analyzeBuild();
    });

    function setFlowMode(mode, save = true) {
        currentFlowMode = mode;
        if (save) localStorage.setItem('pc_flow_mode', mode);

        document.getElementById('tabFlowBuildFull').classList.remove('active');
        document.getElementById('tabFlowUpgrade').classList.remove('active');
        document.getElementById('tabFlowPrebuilt').classList.remove('active');

        const mainView = document.getElementById('pcBuilderMainView');
        const prebuiltView = document.getElementById('pcPrebuiltMainView');

        if (mode === 'prebuilt') {
            document.getElementById('tabFlowPrebuilt').classList.add('active');
            mainView.style.display = 'none';
            prebuiltView.style.display = 'block';
            loadPrebuiltPcs();
        } else {
            mainView.style.display = 'grid';
            prebuiltView.style.display = 'none';
            if (mode === 'upgrade') {
                document.getElementById('tabFlowUpgrade').classList.add('active');
            } else {
                document.getElementById('tabFlowBuildFull').classList.add('active');
            }
            analyzeBuild();
        }
    }

    function toggleOwnedPart(partKey, partName) {
        if (pcConfig[partKey] && pcConfig[partKey].isOwned) {
            delete pcConfig[partKey];
        } else {
            pcConfig[partKey] = {
                id: -1,
                name: '[Đã có sẵn] ' + partName,
                price: 0,
                isOwned: true,
                imageUrl: '/assets/images/placeholder.jpg',
                specs: {}
            };
        }
        localStorage.setItem('pc_config', JSON.stringify(pcConfig));
        updateUI();
        analyzeBuild();
    }

    function openSelectModal(partKey, partName) {
        activePartKey = partKey;
        document.getElementById('pcModalTitle').innerText = 'Chọn ' + partName;
        document.getElementById('pcModalSearchInput').value = '';
        
        const alertBox = document.getElementById('compatibilityAlert');
        if (partKey === 'mainboard' && pcConfig.cpu && !pcConfig.cpu.isOwned) {
            alertBox.style.display = 'block';
            alertBox.innerHTML = `<i class="fa-solid fa-circle-info"></i> Lọc Mainboard tương thích với Socket của CPU <strong>${pcConfig.cpu.name}</strong>.`;
        } else if (partKey === 'cpu' && pcConfig.mainboard && !pcConfig.mainboard.isOwned) {
            alertBox.style.display = 'block';
            alertBox.innerHTML = `<i class="fa-solid fa-circle-info"></i> Lọc CPU tương thích với Socket của Mainboard <strong>${pcConfig.mainboard.name}</strong>.`;
        } else {
            alertBox.style.display = 'none';
        }

        document.getElementById('pcSelectModalBackdrop').style.display = 'flex';
        loadProducts();
    }

    function closeSelectModal() {
        document.getElementById('pcSelectModalBackdrop').style.display = 'none';
        activePartKey = '';
    }

    function closeWarningModal() {
        document.getElementById('pcWarningModalBackdrop').style.display = 'none';
    }

    function debounceSearch() {
        clearTimeout(searchDebounceTimeout);
        searchDebounceTimeout = setTimeout(() => {
            loadProducts();
        }, 300);
    }

    function loadProducts() {
        const container = document.getElementById('pcModalProductsListContainer');
        container.innerHTML = '<div class="pc-modal-loading"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải linh kiện...</div>';

        const searchVal = encodeURIComponent(document.getElementById('pcModalSearchInput').value.trim());
        
        const cpuId = (pcConfig.cpu && !pcConfig.cpu.isOwned) ? pcConfig.cpu.id : 0;
        const mainboardId = (pcConfig.mainboard && !pcConfig.mainboard.isOwned) ? pcConfig.mainboard.id : 0;
        const ramId = (pcConfig.ram && !pcConfig.ram.isOwned) ? pcConfig.ram.id : 0;
        const gpuId = (pcConfig.vga && !pcConfig.vga.isOwned) ? pcConfig.vga.id : 0;
        const coolerId = (pcConfig.cooler && !pcConfig.cooler.isOwned) ? pcConfig.cooler.id : 0;
        const caseId = (pcConfig.case && !pcConfig.case.isOwned) ? pcConfig.case.id : 0;
        const storageId = (pcConfig.storage && !pcConfig.storage.isOwned) ? pcConfig.storage.id : 0;

        const url = '<?= url("pc-builder/products") ?>?part=' + activePartKey + 
                    '&search=' + searchVal + 
                    '&cpu_id=' + cpuId + 
                    '&mainboard_id=' + mainboardId + 
                    '&ram_id=' + ramId +
                    '&vga_id=' + gpuId +
                    '&cooler_id=' + coolerId +
                    '&case_id=' + caseId +
                    '&storage_id=' + storageId;

        fetch(url)
            .then(res => res.json())
            .then(resData => {
                const data = Array.isArray(resData) ? resData : (resData.data || []);
                if (!data || data.length === 0) {
                    container.innerHTML = '<div class="pc-modal-empty" style="text-align:center;padding:30px;color:#64748B;">Không tìm thấy linh kiện phù hợp.</div>';
                    return;
                }

                let html = '<div class="pc-modal-products-list">';
                data.forEach(p => {
                    const isOutOfStock = parseInt(p.stock) <= 0;
                    const isCompatible = p.compatible !== false;
                    
                    let statusBadge = '';
                    let actionButton = '';
                    
                    if (isOutOfStock) {
                        statusBadge = `<span class="pc-builder-blocker-badge">Hết hàng</span>`;
                        actionButton = `<button disabled class="btn-choose-item" style="background:#CBD5E1;color:#64748B;cursor:not-allowed;">Hết hàng</button>`;
                    } else if (!isCompatible) {
                        statusBadge = `<span class="pc-builder-blocker-badge"><i class="fa-solid fa-triangle-exclamation"></i> Không tương thích</span>`;
                        let reasons = '';
                        if (p.blockers && p.blockers.length > 0) {
                            reasons = p.blockers.map(r => `<span class="pc-builder-reason-text">• ${r}</span>`).join('');
                        }
                        statusBadge += reasons;
                        actionButton = `<button disabled class="btn-choose-item" style="background:#FEE2E2;color:#EF4444;border:1px solid #FCA5A5;cursor:not-allowed;">Không vừa</button>`;
                    } else {
                        statusBadge = `<span class="pc-builder-compatibility-badge"><i class="fa-solid fa-circle-check"></i> Tương thích tốt</span>`;
                        actionButton = `<button class="btn-choose-item" onclick="selectProduct(${p.id}, '${escapeQuote(p.name)}', ${p.price}, '${p.image_url}')">Chọn mua</button>`;
                    }

                    html += `
                        <div class="pc-modal-item" style="${!isCompatible ? 'background:#FFF8F8;opacity:0.85;' : ''}">
                            <img class="pc-modal-item-img" src="${p.image_url}" alt="${p.name}">
                            <div class="pc-modal-item-name">
                                <div style="font-weight:700;">${p.name}</div>
                                ${statusBadge}
                            </div>
                            <div class="pc-modal-item-price">${p.price_formatted}</div>
                            <div>
                                ${actionButton}
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                container.innerHTML = html;
            })
            .catch(err => {
                container.innerHTML = '<div class="pc-modal-empty" style="text-align:center;padding:30px;color:#EF4444;">Lỗi tải dữ liệu.</div>';
            });
    }

    function selectProduct(id, name, price, imageUrl) {
        pcConfig[activePartKey] = { id, name, price, imageUrl, isOwned: false };
        localStorage.setItem('pc_config', JSON.stringify(pcConfig));
        updateUI();
        analyzeBuild();
        closeSelectModal();
    }

    function removeProduct(partKey) {
        delete pcConfig[partKey];
        localStorage.setItem('pc_config', JSON.stringify(pcConfig));
        updateUI();
        analyzeBuild();
    }

    function updateUI() {
        let total = 0;
        let buyCount = 0;
        let ownedCount = 0;

        document.querySelectorAll('.pc-builder-row').forEach(row => {
            const partKey = row.getAttribute('data-part');
            const infoContainer = document.getElementById('selected-info-' + partKey);
            const actionContainer = document.getElementById('actions-' + partKey);
            const partTitle = row.querySelector('.pc-builder-part-name').innerText.trim();

            if (pcConfig[partKey]) {
                const item = pcConfig[partKey];
                if (item.isOwned) {
                    ownedCount++;
                    infoContainer.innerHTML = `
                        <div class="pc-builder-selected-info">
                            <div class="pc-builder-selected-details">
                                <span class="pc-builder-selected-title" style="color:#059669; font-weight:700;">
                                    <i class="fa-solid fa-circle-check me-1"></i> [Đã có sẵn] ${item.name.replace('[Đã có sẵn] ', '')}
                                </span>
                                <span style="font-size:12px; color:#64748B;">Linh kiện đã sở hữu (0đ)</span>
                            </div>
                        </div>
                    `;
                    actionContainer.innerHTML = `
                        <button type="button" class="btn-owned-part active" onclick="toggleOwnedPart('${partKey}', '${escapeQuote(partTitle)}')">
                            <i class="fa-solid fa-check"></i> Bỏ 'Đã có'
                        </button>
                        <button type="button" class="btn-remove-part" onclick="removeProduct('${partKey}')" title="Xóa"><i class="fa-solid fa-trash-can"></i></button>
                    `;
                } else {
                    buyCount++;
                    total += parseFloat(item.price);
                    infoContainer.innerHTML = `
                        <div class="pc-builder-selected-info">
                            <img class="pc-builder-selected-img" src="${item.imageUrl}" alt="${item.name}">
                            <div class="pc-builder-selected-details">
                                <span class="pc-builder-selected-title" title="${item.name}">${item.name}</span>
                                <span class="pc-builder-selected-price">${formatMoney(item.price)}đ</span>
                            </div>
                        </div>
                    `;
                    actionContainer.innerHTML = `
                        <button type="button" class="btn-change-part" onclick="openSelectModal('${partKey}', '${escapeQuote(partTitle)}')">Thay đổi</button>
                        <button type="button" class="btn-remove-part" onclick="removeProduct('${partKey}')" title="Xóa"><i class="fa-solid fa-trash-can"></i></button>
                    `;
                }
            } else {
                infoContainer.innerHTML = `<span class="pc-builder-placeholder">Chưa chọn linh kiện</span>`;
                actionContainer.innerHTML = `
                    <button type="button" class="btn-select-part" onclick="openSelectModal('${partKey}', '${escapeQuote(partTitle)}')">
                        <i class="fa-solid fa-plus"></i> Chọn mua
                    </button>
                    <button type="button" class="btn-owned-part" onclick="toggleOwnedPart('${partKey}', '${escapeQuote(partTitle)}')">
                        <i class="fa-solid fa-check"></i> Đã có
                    </button>
                `;
            }
        });

        const totalSelected = buyCount + ownedCount;
        document.getElementById('selected-count').innerText = totalSelected;
        document.getElementById('buy-count').innerText = buyCount;
        document.getElementById('owned-count').innerText = ownedCount;
        document.getElementById('total-price-display').innerText = formatMoney(total) + 'đ';

        const btnCartText = document.getElementById('btnAddToCartText');
        if (btnCartText) {
            btnCartText.innerText = buyCount > 0 ? `Thêm ${buyCount} linh kiện vào giỏ` : 'Thêm vào giỏ hàng';
        }
    }

    async function analyzeBuild() {
        if (psuAnalysisAbortController) {
            psuAnalysisAbortController.abort();
        }
        psuAnalysisAbortController = new AbortController();

        const payload = {};
        for (const [k, v] of Object.entries(pcConfig)) {
            if (v && v.isOwned) {
                payload[k] = { id: -1, name: v.name, price: 0, specs: v.specs || {} };
            } else if (v && v.id > 0) {
                payload[k] = v.id;
            }
        }

        const url = '<?= url("pc-builder/analysis") ?>';
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        try {
            const response = await fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify(payload),
                signal: psuAnalysisAbortController.signal
            });

            const rawText = await response.text();
            let data = JSON.parse(rawText);

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Lỗi kết nối API phân tích');
            }

            const power = data.power;
            const psuPlaceholder = document.getElementById('psu-analysis-placeholder');
            const psuContent = document.getElementById('psu-analysis-content');
            
            const hasCpu = !!pcConfig.cpu;
            const hasGpu = !!pcConfig.vga;

            if (!hasCpu && !hasGpu) {
                psuPlaceholder.style.display = 'block';
                psuContent.style.display = 'none';
            } else {
                psuPlaceholder.style.display = 'none';
                psuContent.style.display = 'block';
                
                document.getElementById('psu-estimated-w').innerText = Math.round(power.estimated_peak_w) + 'W';
                document.getElementById('psu-gpu-minimum-w').innerText = (power.gpu_minimum_psu_w > 0 ? power.gpu_minimum_psu_w : 0) + 'W';
                document.getElementById('psu-recommended-w').innerText = power.recommended_psu_w + 'W';

                const vgaStatusLabel = document.getElementById('psu-vga-status-label');
                if (vgaStatusLabel) {
                    vgaStatusLabel.innerText = hasGpu ? 'Đã bao gồm Card màn hình rời' : '(Chưa bao gồm Card màn hình rời)';
                }

                // Sửa lỗi mapping trạng thái nguồn PSU
                const badge = document.getElementById('psu-status-badge');
                if (badge) {
                    if (!pcConfig.psu) {
                        badge.style.background = '#E2E8F0';
                        badge.style.color = '#475569';
                        badge.innerText = 'Chưa chọn Nguồn';
                    } else if (power.is_selected_psu_sufficient === true) {
                        if (power.headroom_percent < 15) {
                            badge.style.background = '#FEF3C7';
                            badge.style.color = '#D97706';
                            badge.innerText = 'Vừa đủ (Dự phòng <15%)';
                        } else {
                            badge.style.background = '#D1FAE5';
                            badge.style.color = '#059669';
                            badge.innerText = 'Đủ công suất';
                        }
                    } else if (power.is_selected_psu_sufficient === false) {
                        badge.style.background = '#FEE2E2';
                        badge.style.color = '#DC2626';
                        badge.innerText = 'Không đủ công suất';
                    } else {
                        badge.style.background = '#D1FAE5';
                        badge.style.color = '#059669';
                        badge.innerText = pcConfig.psu.isOwned ? 'Đã có sẵn' : 'Đã chọn Nguồn';
                    }
                }
            }

            // Hiển thị cảnh báo & Blocker
            const alertsContainer = document.getElementById('build-alerts-container');
            const alertsList = document.getElementById('build-alerts-list');
            const btnAddToCart = document.getElementById('btnAddToCartSubmit');
            
            alertsList.innerHTML = '';
            currentBlockers = data.blockers || [];
            currentWarnings = data.warnings || [];
            let hasBlockers = currentBlockers.length > 0;
            let missingCores = [];

            if (currentFlowMode === 'build_full') {
                const coreKeys = ['cpu', 'mainboard', 'ram', 'storage', 'psu', 'case'];
                coreKeys.forEach(k => {
                    if (!pcConfig[k]) missingCores.push(k.toUpperCase());
                });
            }

            currentBlockers.forEach(msg => {
                alertsList.innerHTML += `
                    <div style="color:#EF4444; background:#FEF2F2; border: 1px solid #FCA5A5; padding:8px 12px; border-radius:6px; font-weight:600;">
                        <i class="fa-solid fa-circle-xmark"></i> ${msg}
                    </div>
                `;
            });

            if (missingCores.length > 0) {
                hasBlockers = true;
                alertsList.innerHTML += `
                    <div style="color:#EF4444; background:#FEF2F2; border: 1px solid #FCA5A5; padding:8px 12px; border-radius:6px; font-weight:600;">
                        <i class="fa-solid fa-circle-xmark"></i> Thiếu linh kiện cốt lõi: ${missingCores.join(', ')}
                    </div>
                `;
            }

            currentWarnings.forEach(msg => {
                alertsList.innerHTML += `
                    <div style="color:#D97706; background:#FFFBEB; border: 1px solid #FCD34D; padding:8px 12px; border-radius:6px; font-weight:600;">
                        <i class="fa-solid fa-triangle-exclamation"></i> ${msg}
                    </div>
                `;
            });

            if (hasBlockers || currentWarnings.length > 0) {
                alertsContainer.style.display = 'block';
            } else {
                alertsContainer.style.display = 'none';
            }

            if (hasBlockers) {
                btnAddToCart.disabled = true;
                btnAddToCart.style.opacity = '0.5';
                btnAddToCart.style.cursor = 'not-allowed';
            } else {
                btnAddToCart.disabled = false;
                btnAddToCart.style.opacity = '1';
                btnAddToCart.style.cursor = 'pointer';
            }
        } catch (err) {
            if (err.name !== 'AbortError') {
                console.error("Analysis error: ", err);
            }
        }
    }

    function handleAddToCartClick() {
        if (currentBlockers.length > 0) {
            alert('Cấu hình có lỗi tương thích nghiêm trọng. Vui lòng sửa lỗi trước khi thêm vào giỏ.');
            return;
        }

        if (currentWarnings.length > 0) {
            // Mở Warning Confirmation Modal
            const container = document.getElementById('modalWarningListContainer');
            container.innerHTML = '';
            currentWarnings.forEach(w => {
                container.innerHTML += `
                    <div style="color:#B45309; background:#FEF3C7; border:1px solid #FDE68A; padding:8px 12px; border-radius:6px; font-size:13px; font-weight:600;">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> ${w}
                    </div>
                `;
            });
            document.getElementById('pcWarningModalBackdrop').style.display = 'flex';
            return;
        }

        proceedAddToCartAfterWarning();
    }

    function proceedAddToCartAfterWarning() {
        closeWarningModal();

        const buyableIds = [];
        const ownedKeys = [];

        for (const [k, v] of Object.entries(pcConfig)) {
            if (v) {
                if (v.isOwned) {
                    ownedKeys.push(k);
                } else if (v.id > 0) {
                    buyableIds.push(v.id);
                }
            }
        }

        if (buyableIds.length === 0) {
            alert('Vui lòng chọn ít nhất 1 linh kiện mua mới tại TechPilot trước khi thêm vào giỏ.');
            return;
        }

        const formData = new FormData();
        buyableIds.forEach(id => formData.append('product_ids[]', id));
        ownedKeys.forEach(k => formData.append('owned_keys[]', k));
        formData.append('mode', currentFlowMode);
        
        const csrfTokenVal = document.querySelector('input[name="csrf_token"]')?.value 
            || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
            || '<?= $_SESSION["csrf_token"] ?? "" ?>';
        formData.append('csrf_token', csrfTokenVal);

        fetch('<?= url("pc-builder/add-to-cart") ?>', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': csrfTokenVal
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                localStorage.removeItem('pc_config');
                pcConfig = {};
                window.location.href = '<?= url("cart") ?>';
            } else {
                alert('Thất bại: ' + data.message);
            }
        })
        .catch(err => {
            alert('Đã xảy ra lỗi khi thêm sản phẩm vào giỏ hàng.');
        });
    }

    let prebuiltPcsMap = {};

    function loadPrebuiltPcs() {
        const grid = document.getElementById('prebuiltProductsGrid');
        grid.innerHTML = '<div style="padding:30px; text-align:center; color:#64748B; grid-column: 1/-1;"><i class="fa-solid fa-spinner fa-spin me-2"></i> Đang nạp danh sách PC lắp sẵn...</div>';

        fetch('<?= url("pc-builder/prebuilt") ?>')
            .then(res => res.json())
            .then(res => {
                if (!res.success || !res.data || res.data.length === 0) {
                    grid.innerHTML = '<div style="padding:30px; text-align:center; color:#64748B; grid-column: 1/-1;">Hiện tại chưa có mẫu PC lắp sẵn nào.</div>';
                    return;
                }

                prebuiltPcsMap = {};
                let html = '';
                res.data.forEach(pc => {
                    prebuiltPcsMap[pc.id] = pc;
                    html += `
                        <div class="prebuilt-card">
                            <img class="prebuilt-img" src="${pc.image_url}" alt="${pc.name}" onerror="this.onerror=null; this.src='<?= url('assets/images/categories/category-pc.png') ?>';">
                            <div style="font-weight:700; font-size:15px; color:#0F172A; margin-bottom:8px;">${pc.name}</div>
                            <div style="font-size:16px; font-weight:800; color:#EF4444; margin-bottom:12px;">${pc.price_formatted}</div>
                            <div style="display:flex; flex-direction:column; gap:8px;">
                                <a href="${'<?= url("product/detail/") ?>' + pc.slug}" class="btn btn-sm btn-outline-primary w-100" style="font-weight:600;">Xem chi tiết & Mua</a>
                                <button type="button" class="btn btn-sm btn-primary w-100" onclick="customizePrebuiltPc(${pc.id})" style="font-weight:700; background:#0B63E5;">
                                    <i class="fa-solid fa-sliders me-1"></i> Tùy chỉnh cấu hình này
                                </button>
                            </div>
                        </div>
                    `;
                });
                grid.innerHTML = html;
            })
            .catch(err => {
                grid.innerHTML = '<div style="padding:30px; text-align:center; color:#EF4444; grid-column: 1/-1;">Lỗi tải dữ liệu PC lắp sẵn.</div>';
            });
    }

    function customizePrebuiltPc(pcId) {
        const pc = prebuiltPcsMap[pcId];
        if (!pc || !pc.components || Object.keys(pc.components).length === 0) {
            alert('Không thể nạp linh kiện chi tiết của mẫu PC này.');
            return;
        }

        pcConfig = {};
        for (const [key, comp] of Object.entries(pc.components)) {
            if (comp && comp.id) {
                pcConfig[key] = {
                    id: comp.id,
                    name: comp.name,
                    price: comp.price,
                    price_formatted: comp.price_formatted,
                    image_url: comp.image_url,
                    specs: comp.specs,
                    isOwned: false
                };
            }
        }

        localStorage.setItem('pc_config', JSON.stringify(pcConfig));
        updateUI();
        analyzeBuild();
        setFlowMode('build_full');

        alert(`Đã nạp đầy đủ cấu hình PC "${pc.name}" vào PC Builder! Bạn có thể tùy chỉnh hoặc thay thế từng linh kiện theo ý muốn.`);
    }

    function resetConfig() {
        if (confirm('Bạn có chắc chắn muốn xóa tất cả linh kiện đã chọn?')) {
            pcConfig = {};
            localStorage.removeItem('pc_config');
            updateUI();
            analyzeBuild();
        }
    }

    function formatMoney(amount) {
        return new Intl.NumberFormat('vi-VN').format(amount);
    }

    function escapeQuote(str) {
        if (!str) return '';
        return String(str)
            .replace(/\\/g, "\\\\")
            .replace(/'/g, "\\'")
            .replace(/"/g, "&quot;");
    }
</script>

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
        margin-bottom: 35px;
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
        grid-template-columns: 80px 200px 1fr 150px;
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
        min-width: 0;
    }
    .pc-builder-selected-title {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-primary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
        margin-bottom: 2px;
    }
    .pc-builder-selected-price {
        font-size: 14px;
        font-weight: 700;
        color: #EF4444;
        display: block;
    }
    .pc-builder-placeholder {
        color: #94A3B8;
        font-size: 13.5px;
        font-style: italic;
    }
    .pc-builder-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    .btn-select-part {
        background: #0B63E5;
        color: #FFFFFF;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13.5px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-select-part:hover {
        background: #0051C4;
    }
    .btn-change-part {
        background: #FFFFFF;
        color: var(--text-primary);
        border: 1px solid #CBD5E1;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13.5px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-change-part:hover {
        background: #F8FAFC;
        border-color: #94A3B8;
    }
    .btn-remove-part {
        background: #FEF2F2;
        color: #EF4444;
        border: 1px solid #FEE2E2;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-remove-part:hover {
        background: #FEE2E2;
        border-color: #FCA5A5;
    }

    @media (max-width: 767px) {
        .pc-builder-row {
            grid-template-columns: 1fr;
            gap: 15px;
            text-align: center;
            justify-items: center;
        }
        .pc-builder-selected-info {
            flex-direction: column;
            text-align: center;
        }
        .pc-builder-actions {
            justify-content: center;
            width: 100%;
        }
    }

    /* Sidebar Summary */
    .pc-builder-summary {
        position: sticky;
        top: 20px;
    }
    .pc-builder-summary-card {
        background: #FFFFFF;
        border: 1px solid var(--border);
        border-radius: 16px;
        box-shadow: var(--shadow-card);
        padding: 20px;
    }
    .pc-builder-summary-title {
        font-size: 18px;
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .pc-builder-summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 12px;
        font-size: 14.5px;
        color: var(--text-secondary);
    }
    .pc-builder-total-price {
        font-size: 26px;
        font-weight: 800;
        color: #EF4444;
        text-align: right;
        margin: 10px 0 20px;
    }
    .btn-add-config-to-cart {
        width: 100%;
        background: #EF4444;
        color: #FFFFFF;
        border: none;
        padding: 14px;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-bottom: 15px;
    }
    .btn-add-config-to-cart:hover {
        background: #DC2626;
    }
    .btn-reset-config {
        width: 100%;
        background: #FFFFFF;
        color: #64748B;
        border: 1px solid #CBD5E1;
        padding: 12px;
        border-radius: 10px;
        font-size: 14.5px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .btn-reset-config:hover {
        background: #F8FAFC;
        color: #334155;
    }

    /* Modal chọn linh kiện */
    .pc-modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        backdrop-filter: blur(4px);
    }
    .pc-modal {
        background: #FFFFFF;
        width: 100%;
        max-width: 800px;
        border-radius: 20px;
        display: flex;
        flex-direction: column;
        max-height: 90vh;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }
    .pc-modal-header {
        padding: 20px 25px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .pc-modal-title {
        font-size: 18px;
        font-weight: 800;
        color: var(--text-primary);
        margin: 0;
    }
    .pc-modal-close {
        background: none;
        border: none;
        font-size: 24px;
        color: #94A3B8;
        cursor: pointer;
        transition: all 0.2s;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
    .pc-modal-close:hover {
        background: #F1F5F9;
        color: var(--text-primary);
    }
    .pc-modal-body {
        padding: 25px;
        overflow-y: auto;
        flex: 1;
    }
    .pc-search-box {
        position: relative;
        margin-bottom: 25px;
    }
    .pc-search-box input {
        width: 100%;
        padding: 14px 20px 14px 45px;
        border: 1px solid #CBD5E1;
        border-radius: 12px;
        font-size: 15px;
        transition: all 0.2s;
        font-family: inherit;
    }
    .pc-search-box input:focus {
        outline: none;
        border-color: #0B63E5;
        box-shadow: 0 0 0 4px rgba(11, 99, 229, 0.1);
    }
    .pc-search-box i {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: #94A3B8;
        font-size: 16px;
    }
    .pc-modal-products-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    .pc-modal-item {
        display: grid;
        grid-template-columns: 80px 1fr 150px 120px;
        align-items: center;
        padding: 15px;
        border: 1px solid var(--border);
        border-radius: 12px;
        transition: all 0.2s;
    }
    .pc-modal-item:hover {
        border-color: #0B63E5;
        background: #FAFAFA;
    }
    .pc-modal-item-img {
        width: 60px;
        height: 60px;
        object-fit: contain;
        border: 1px solid var(--border);
        border-radius: 6px;
        padding: 2px;
        background: #FFFFFF;
    }
    .pc-modal-item-name {
        font-size: 13.5px;
        font-weight: 600;
        color: var(--text-primary);
        padding-right: 15px;
    }
    .pc-modal-item-price {
        font-size: 14.5px;
        font-weight: 700;
        color: #EF4444;
    }
    .btn-choose-item {
        background: #0B63E5;
        color: #FFFFFF;
        border: none;
        padding: 7px 14px;
        border-radius: 6px;
        font-size: 12.5px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-choose-item:hover {
        background: #0051C4;
    }
    .pc-modal-loading {
        text-align: center;
        padding: 30px;
        color: var(--text-secondary);
        font-size: 14px;
    }
    .pc-modal-empty {
        text-align: center;
        padding: 35px;
        color: #94A3B8;
        font-size: 14px;
        font-style: italic;
    }
    .pc-builder-compatibility-badge {
        font-size: 11px;
        background-color: #D1FAE5;
        color: #065F46;
        padding: 2px 8px;
        border-radius: 9999px;
        font-weight: 700;
        margin-top: 5px;
        display: inline-block;
    }
    .pc-builder-blocker-badge {
        font-size: 11px;
        background-color: #FEE2E2;
        color: #991B1B;
        padding: 2px 8px;
        border-radius: 9999px;
        font-weight: 700;
        margin-top: 5px;
        display: inline-block;
    }
    .pc-builder-reason-text {
        font-size: 12px;
        color: #EF4444;
        margin-top: 4px;
        font-weight: 500;
        display: block;
    }
</style>

<div class="container pc-builder-container">
    <div class="pc-builder-header">
        <h1>Xây dựng cấu hình PC theo yêu cầu</h1>
        <p>Lựa chọn linh kiện tối ưu hiệu năng - Tương thích hoàn hảo - Hỗ trợ lắp ráp miễn phí</p>
    </div>

    <div class="pc-builder-layout">
        <!-- Danh sách cấu hình -->
        <div class="pc-builder-list">
            <?php foreach ($parts as $key => $info): ?>
                <div class="pc-builder-row" data-part="<?= e($key) ?>">
                    <!-- 1. Icon bộ phận -->
                    <div class="pc-builder-icon">
                        <i class="<?= e($info['icon']) ?>"></i>
                    </div>
                    
                    <!-- 2. Tên bộ phận -->
                    <div class="pc-builder-part-name">
                        <?= e($info['name']) ?>
                    </div>
                    
                    <!-- 3. Chi tiết linh kiện đã chọn -->
                    <div class="pc-builder-selected-info" id="selected-info-<?= e($key) ?>">
                        <span class="pc-builder-placeholder">Chưa chọn linh kiện</span>
                    </div>
                    
                    <!-- 4. Nút hành động -->
                    <div class="pc-builder-actions" id="actions-<?= e($key) ?>">
                        <button type="button" class="btn-select-part" onclick="openSelectModal('<?= e($key) ?>', '<?= e($info['name']) ?>')">
                            <i class="fa-solid fa-plus"></i> Chọn linh kiện
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Widget tổng hợp cấu hình bên phải -->
        <aside class="pc-builder-summary">
            <!-- Phân tích nguồn PSU -->
            <div class="pc-builder-summary-card" style="margin-bottom: 20px; border-color: #E2E8F0;">
                <div class="pc-builder-summary-title" style="font-size:16px;">
                    <span>Phân tích công suất nguồn</span>
                    <i class="fa-solid fa-bolt" style="color: #F59E0B;"></i>
                </div>
                
                <div id="psu-analysis-placeholder" style="font-size:13px; color:#64748B; font-style:italic; padding: 10px 0; text-align: center;">
                    Chọn CPU để nhận đề xuất nguồn.
                </div>

                <div id="psu-analysis-content" style="display: none;">
                    <div class="pc-builder-summary-row" style="font-size:13px;">
                        <span>Công suất tải đỉnh:</span>
                        <span id="psu-estimated-w" style="font-weight: 600; color: var(--text-primary);">0W</span>
                    </div>
                    <div class="pc-builder-summary-row" style="font-size:13px; margin-top:4px;">
                        <span>Yêu cầu tối thiểu từ GPU:</span>
                        <span id="psu-gpu-minimum-w" style="font-weight: 600; color: var(--text-secondary);">0W</span>
                    </div>
                    <div class="pc-builder-summary-row" style="margin-top:8px; border-top: 1px dashed #E2E8F0; padding-top:8px;">
                        <span style="font-weight:600;">Nguồn khuyến nghị:</span>
                        <span id="psu-recommended-w" style="font-weight: 700; color: #EF4444; font-size:16px;">300W</span>
                    </div>
                    <div style="font-size:11px; color:#94A3B8; font-style:italic; line-height:1.4; margin-top:8px; text-align:right;">
                        * Đã tính 30% dự phòng an toàn.
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
                    <span>Số linh kiện đã chọn:</span>
                    <span id="selected-count" style="font-weight: 700; color: var(--text-primary);">0</span>
                </div>

                <!-- Blockers & Warnings List -->
                <div id="build-alerts-container" style="display:none; margin-top:15px; font-size:12.5px;">
                    <div style="font-weight:700; margin-bottom:8px; color:var(--text-primary);">Kiểm tra tương thích:</div>
                    <div id="build-alerts-list" style="display:flex; flex-direction:column; gap:8px;"></div>
                </div>
                
                <div style="border-top: 1px solid var(--border); margin: 15px 0;"></div>
                
                <div style="font-size: 13.5px; color: var(--text-secondary); text-align: right;">Tổng giá tiền tạm tính:</div>
                <div class="pc-builder-total-price" id="total-price-display">0đ</div>
                
                <button type="button" class="btn-add-config-to-cart" id="btnAddToCartSubmit" onclick="addConfigToCart()">
                    <i class="fa-solid fa-cart-plus"></i> Thêm vào giỏ hàng
                </button>
                
                <button type="button" class="btn-reset-config" onclick="resetConfig()">
                    <i class="fa-solid fa-trash-can"></i> Xóa hết cấu hình
                </button>
            </div>
        </aside>
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

<!-- Javascript xử lý tương tác dynamic -->
<script>
    // Lưu trữ cấu hình PC đã chọn của khách hàng
    let pcConfig = {};
    let activePartKey = '';
    let searchDebounceTimeout = null;

    // Phục hồi từ localStorage nếu đã chọn từ trước
    if (localStorage.getItem('pc_config')) {
        try {
            pcConfig = JSON.parse(localStorage.getItem('pc_config')) || {};
            updateUI();
            analyzeBuild();
        } catch (e) {
            pcConfig = {};
        }
    }

    function openSelectModal(partKey, partName) {
        activePartKey = partKey;
        document.getElementById('pcModalTitle').innerText = 'Chọn ' + partName;
        document.getElementById('pcModalSearchInput').value = '';
        
        // Hiện thông báo tự động tương thích đối với các linh kiện
        const alertBox = document.getElementById('compatibilityAlert');
        if (partKey === 'mainboard' && pcConfig.cpu) {
            alertBox.style.display = 'block';
            alertBox.innerHTML = `<i class="fa-solid fa-circle-info"></i> Lọc Mainboard tương thích với Socket của CPU <strong>${pcConfig.cpu.name}</strong>.`;
        } else if (partKey === 'cpu' && pcConfig.mainboard) {
            alertBox.style.display = 'block';
            alertBox.innerHTML = `<i class="fa-solid fa-circle-info"></i> Lọc CPU tương thích với Socket của Mainboard <strong>${pcConfig.mainboard.name}</strong>.`;
        } else if (partKey === 'ram' && pcConfig.mainboard) {
            alertBox.style.display = 'block';
            alertBox.innerHTML = `<i class="fa-solid fa-circle-info"></i> Lọc RAM tương thích với chuẩn hỗ trợ (DDR4/DDR5) của Mainboard <strong>${pcConfig.mainboard.name}</strong>.`;
        } else if (partKey === 'psu') {
            alertBox.style.display = 'block';
            const recW = document.getElementById('psu-recommended-w').innerText;
            alertBox.innerHTML = `<i class="fa-solid fa-circle-info"></i> Chỉ hiển thị các Nguồn (PSU) có công suất từ <strong>${recW}</strong> trở lên cho cấu hình hiện tại.`;
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
        
        // Gửi ID các linh kiện đã chọn để API backend chạy kiểm tra tương thích chéo
        const cpuId = pcConfig.cpu ? pcConfig.cpu.id : 0;
        const mainboardId = pcConfig.mainboard ? pcConfig.mainboard.id : 0;
        const ramId = pcConfig.ram ? pcConfig.ram.id : 0;
        const gpuId = pcConfig.vga ? pcConfig.vga.id : 0;
        const coolerId = pcConfig.cooler ? pcConfig.cooler.id : 0;
        const caseId = pcConfig.case ? pcConfig.case.id : 0;
        const storageId = pcConfig.storage ? pcConfig.storage.id : 0;

        const url = '<?= url("pc-builder/products") ?>?part=' + activePartKey + 
                    '&search=' + searchVal + 
                    '&cpu_id=' + cpuId + 
                    '&mainboard_id=' + mainboardId + 
                    '&ram_id=' + ramId +
                    '&gpu_id=' + gpuId +
                    '&cooler_id=' + coolerId +
                    '&case_id=' + caseId +
                    '&storage_id=' + storageId;

        fetch(url)
            .then(res => res.json())
            .then(resData => {
                const data = Array.isArray(resData) ? resData : (resData.data || []);
                if (!data || data.length === 0) {
                    container.innerHTML = '<div class="pc-modal-empty">Không tìm thấy linh kiện tương thích nào phù hợp.</div>';
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
                        // Tạo text hiển thị lý do lỗi
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
                container.innerHTML = '<div class="pc-modal-empty">Có lỗi xảy ra khi tải danh sách sản phẩm.</div>';
            });
    }

    function selectProduct(id, name, price, imageUrl) {
        pcConfig[activePartKey] = { id, name, price, imageUrl };
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
        let count = 0;

        document.querySelectorAll('.pc-builder-row').forEach(row => {
            const partKey = row.getAttribute('data-part');
            const infoContainer = document.getElementById('selected-info-' + partKey);
            const actionContainer = document.getElementById('actions-' + partKey);

            if (pcConfig[partKey]) {
                const item = pcConfig[partKey];
                total += parseFloat(item.price);
                count++;

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
                    <button type="button" class="btn-change-part" onclick="openSelectModal('${partKey}', '${row.querySelector('.pc-builder-part-name').innerText.trim()}')">Thay đổi</button>
                    <button type="button" class="btn-remove-part" onclick="removeProduct('${partKey}')" title="Xóa linh kiện"><i class="fa-solid fa-trash-can"></i></button>
                `;
            } else {
                infoContainer.innerHTML = `<span class="pc-builder-placeholder">Chưa chọn linh kiện</span>`;
                actionContainer.innerHTML = `
                    <button type="button" class="btn-select-part" onclick="openSelectModal('${partKey}', '${row.querySelector('.pc-builder-part-name').innerText.trim()}')">
                        <i class="fa-solid fa-plus"></i> Chọn linh kiện
                    </button>
                `;
            }
        });

        document.getElementById('selected-count').innerText = count;
        document.getElementById('total-price-display').innerText = formatMoney(total) + 'đ';
    }

    /** Gọi API phân tích tương thích chéo toàn cấu hình & tính toán công suất nguồn */
    function analyzeBuild() {
        const cpuId = pcConfig.cpu ? pcConfig.cpu.id : 0;
        const mainboardId = pcConfig.mainboard ? pcConfig.mainboard.id : 0;
        const ramId = pcConfig.ram ? pcConfig.ram.id : 0;
        const gpuId = pcConfig.vga ? pcConfig.vga.id : 0;
        const coolerId = pcConfig.cooler ? pcConfig.cooler.id : 0;
        const caseId = pcConfig.case ? pcConfig.case.id : 0;
        const psuId = pcConfig.psu ? pcConfig.psu.id : 0;
        const storageId = pcConfig.storage ? pcConfig.storage.id : 0;

        const url = '<?= url("pc-builder/analysis") ?>?cpu_id=' + cpuId + 
                    '&mainboard_id=' + mainboardId + 
                    '&ram_id=' + ramId +
                    '&gpu_id=' + gpuId +
                    '&cooler_id=' + coolerId +
                    '&case_id=' + caseId +
                    '&psu_id=' + psuId +
                    '&storage_id=' + storageId;

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const power = data.power;
                    
                    const psuPlaceholder = document.getElementById('psu-analysis-placeholder');
                    const psuContent = document.getElementById('psu-analysis-content');
                    
                    if (cpuId === 0 && gpuId === 0) {
                        psuPlaceholder.style.display = 'block';
                        psuContent.style.display = 'none';
                    } else {
                        psuPlaceholder.style.display = 'none';
                        psuContent.style.display = 'block';
                        
                        document.getElementById('psu-estimated-w').innerText = Math.round(power.estimated_peak_w) + 'W';
                        document.getElementById('psu-gpu-minimum-w').innerText = (power.gpu_minimum_psu_w > 0 ? power.gpu_minimum_psu_w : 0) + 'W';
                        document.getElementById('psu-recommended-w').innerText = power.recommended_psu_w + 'W';
                    }

                    // Hiển thị danh sách cảnh báo & lỗi
                    const alertsContainer = document.getElementById('build-alerts-container');
                    const alertsList = document.getElementById('build-alerts-list');
                    const btnAddToCart = document.getElementById('btnAddToCartSubmit');
                    
                    alertsList.innerHTML = '';
                    let hasBlockers = false;
                    let missingCores = [];

                    // Yêu cầu linh kiện cốt lõi để mua hàng
                    if (cpuId === 0) missingCores.push('CPU');
                    if (mainboardId === 0) missingCores.push('Bo mạch chủ');
                    if (ramId === 0) missingCores.push('RAM');
                    if (storageId === 0) missingCores.push('Ổ cứng');
                    if (psuId === 0) missingCores.push('Nguồn (PSU)');

                    // Duyệt các lỗi nghiêm trọng (Blockers)
                    if (data.blockers && data.blockers.length > 0) {
                        hasBlockers = true;
                        data.blockers.forEach(msg => {
                            alertsList.innerHTML += `
                                <div style="color:#EF4444; background:#FEF2F2; border: 1px solid #FCA5A5; padding:8px 12px; border-radius:6px; font-weight:600;">
                                    <i class="fa-solid fa-circle-xmark"></i> ${msg}
                                </div>
                            `;
                        });
                    }

                    if (missingCores.length > 0) {
                        hasBlockers = true; // Not technically a "blocker" array element from backend, but it blocks purchase
                        alertsList.innerHTML += `
                            <div style="color:#EF4444; background:#FEF2F2; border: 1px solid #FCA5A5; padding:8px 12px; border-radius:6px; font-weight:600;">
                                <i class="fa-solid fa-circle-xmark"></i> Thiếu linh kiện cốt lõi: ${missingCores.join(', ')}
                            </div>
                        `;
                    }

                    // Duyệt các cảnh báo (Warnings)
                    if (data.warnings && data.warnings.length > 0) {
                        data.warnings.forEach(msg => {
                            alertsList.innerHTML += `
                                <div style="color:#D97706; background:#FFFBEB; border: 1px solid #FCD34D; padding:8px 12px; border-radius:6px; font-weight:600;">
                                    <i class="fa-solid fa-triangle-exclamation"></i> ${msg}
                                </div>
                            `;
                        });
                    }

                    if (hasBlockers || (data.warnings && data.warnings.length > 0)) {
                        alertsContainer.style.display = 'block';
                    } else {
                        alertsContainer.style.display = 'none';
                    }

                    // Vô hiệu hóa nút thêm vào giỏ hàng nếu cấu hình có Blockers
                    if (hasBlockers) {
                        btnAddToCart.disabled = true;
                        btnAddToCart.style.opacity = '0.5';
                        btnAddToCart.style.cursor = 'not-allowed';
                    } else {
                        btnAddToCart.disabled = false;
                        btnAddToCart.style.opacity = '1';
                        btnAddToCart.style.cursor = 'pointer';
                    }
                }
            })
            .catch(err => {
                console.error("Analysis error: ", err);
            });
    }

    function resetConfig() {
        if (confirm('Bạn có chắc chắn muốn xóa tất cả linh kiện đã chọn?')) {
            pcConfig = {};
            localStorage.removeItem('pc_config');
            updateUI();
            analyzeBuild();
        }
    }

    function addConfigToCart() {
        const productIds = Object.values(pcConfig).map(item => item.id);
        if (productIds.length === 0) {
            alert('Vui lòng chọn ít nhất 1 linh kiện trước khi thêm vào giỏ hàng.');
            return;
        }

        const formData = new FormData();
        productIds.forEach(id => formData.append('product_ids[]', id));
        
        // CSRF Token
        const csrfToken = document.querySelector('input[name="csrf_token"]') || document.querySelector('input[name="_csrf"]');
        if (csrfToken) {
            formData.append('csrf_token', csrfToken.value);
        }

        fetch('<?= url("pc-builder/add-to-cart") ?>', {
            method: 'POST',
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

    function formatMoney(amount) {
        return new Intl.NumberFormat('vi-VN').format(amount);
    }

    function escapeQuote(str) {
        return str.replace(/'/g, "\\'");
    }
</script>

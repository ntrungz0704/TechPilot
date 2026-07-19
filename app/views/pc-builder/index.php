<?php include ROOT_PATH . '/app/views/layouts/header.php'; ?>

<!-- CSS riêng cho trang Build PC nhằm đảm bảo giao diện lung linh, premium -->
<style>
    .pc-builder-container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 15px;
        font-family: 'Inter', sans-serif;
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
        grid-template-columns: 1fr 340px;
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
        margin-bottom: 3px;
    }
    .pc-builder-selected-price {
        font-size: 13.5px;
        font-weight: 700;
        color: #0B63E5;
    }
    .pc-builder-placeholder {
        font-size: 13.5px;
        color: #94A3B8;
        font-style: italic;
    }
    .pc-builder-actions {
        text-align: right;
    }
    .btn-select-part {
        background-color: #0B63E5;
        color: #FFFFFF;
        border: none;
        padding: 9px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }
    .btn-select-part:hover {
        background-color: #0051C4;
        transform: translateY(-1px);
    }
    .btn-change-part {
        background: transparent;
        color: #64748B;
        border: 1px solid #CBD5E1;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        margin-right: 6px;
    }
    .btn-change-part:hover {
        color: #0B63E5;
        border-color: #0B63E5;
        background: #EFF6FF;
    }
    .btn-remove-part {
        background: transparent;
        color: #EF4444;
        border: 1px solid #FEE2E2;
        width: 32px;
        height: 32px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-remove-part:hover {
        background: #FEE2E2;
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
            text-align: center;
            width: 100%;
        }
    }

    /* Sidebar Summary */
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
        margin-bottom: 20px;
        border-bottom: 1px solid var(--border);
        padding-bottom: 12px;
        display: flex;
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
        font-size: 22px;
        font-weight: 800;
        color: #EF4444;
        text-align: right;
        margin: 20px 0;
    }
    .btn-add-config-to-cart {
        width: 100%;
        background: linear-gradient(135deg, #0B63E5, #3B82F6);
        color: #FFFFFF;
        border: none;
        padding: 14px 20px;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s;
        box-shadow: 0 4px 12px rgba(11, 99, 229, 0.2);
    }
    .btn-add-config-to-cart:hover {
        opacity: 0.95;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(11, 99, 229, 0.3);
    }
    .btn-reset-config {
        width: 100%;
        background: transparent;
        color: #64748B;
        border: 1px solid #CBD5E1;
        padding: 11px 20px;
        border-radius: 10px;
        font-size: 13.5px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 10px;
        transition: all 0.2s;
    }
    .btn-reset-config:hover {
        background: #F8FAFC;
        color: var(--text-primary);
    }

    /* Modal chọn linh kiện */
    .pc-modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 2000;
        padding: 15px;
    }
    .pc-modal {
        background: #FFFFFF;
        width: 100%;
        max-width: 800px;
        max-height: 85vh;
        border-radius: 16px;
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        animation: modalFadeIn 0.25s ease-out;
    }
    @keyframes modalFadeIn {
        from { transform: scale(0.95); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    .pc-modal-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .pc-modal-title {
        font-size: 18px;
        font-weight: 800;
        color: var(--text-primary);
    }
    .pc-modal-close {
        background: none;
        border: none;
        font-size: 20px;
        color: #94A3B8;
        cursor: pointer;
        transition: color 0.2s;
    }
    .pc-modal-close:hover {
        color: var(--text-primary);
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
        border: 1px solid var(--border);
        border-radius: 10px;
        font-size: 14.5px;
        outline: none;
        transition: all 0.2s;
    }
    .pc-search-box input:focus {
        border-color: #0B63E5;
        box-shadow: 0 0 0 3px rgba(11, 99, 229, 0.15);
    }
    .pc-search-box i {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #94A3B8;
        font-size: 15px;
    }
    /* Danh sách sản phẩm trong modal */
    .pc-modal-products-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .pc-modal-item {
        display: grid;
        grid-template-columns: 60px 1fr 140px 100px;
        align-items: center;
        padding: 12px 16px;
        border: 1px solid var(--border);
        border-radius: 10px;
        transition: all 0.2s;
    }
    .pc-modal-item:hover {
        border-color: #0B63E5;
        background: #F8FAFC;
    }
    .pc-modal-item-img {
        width: 44px;
        height: 44px;
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
</style>

<div class="pc-builder-container">
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
            <div class="pc-builder-summary-card">
                <div class="pc-builder-summary-title">
                    <span>Tóm tắt cấu hình</span>
                    <i class="fa-solid fa-desktop" style="color: #0B63E5;"></i>
                </div>
                
                <div class="pc-builder-summary-row">
                    <span>Số linh kiện đã chọn:</span>
                    <span id="selected-count" style="font-weight: 700; color: var(--text-primary);">0</span>
                </div>
                
                <div style="border-top: 1px solid var(--border); margin: 15px 0;"></div>
                
                <div style="font-size: 13.5px; color: var(--text-secondary); text-align: right;">Tổng giá tiền tạm tính:</div>
                <div class="pc-builder-total-price" id="total-price-display">0đ</div>
                
                <button type="button" class="btn-add-config-to-cart" onclick="addConfigToCart()">
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
        } catch (e) {
            pcConfig = {};
        }
    }

    function openSelectModal(partKey, partName) {
        activePartKey = partKey;
        document.getElementById('pcModalTitle').innerText = 'Chọn ' + partName;
        document.getElementById('pcModalSearchInput').value = '';
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
        const url = '<?= url("pc-builder/products") ?>?part=' + activePartKey + '&search=' + searchVal;

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (!data || data.length === 0) {
                    container.innerHTML = '<div class="pc-modal-empty">Không tìm thấy linh kiện nào phù hợp.</div>';
                    return;
                }

                let html = '<div class="pc-modal-products-list">';
                data.forEach(p => {
                    const isOutOfStock = parseInt(p.stock) <= 0;
                    html += `
                        <div class="pc-modal-item">
                            <img class="pc-modal-item-img" src="${p.image_url}" alt="${p.name}">
                            <div class="pc-modal-item-name">${p.name}</div>
                            <div class="pc-modal-item-price">${p.price_formatted}</div>
                            <div>
                                ${isOutOfStock 
                                    ? '<button disabled class="btn-choose-item" style="background:#CBD5E1;color:#64748B;cursor:not-allowed;">Hết hàng</button>' 
                                    : `<button class="btn-choose-item" onclick="selectProduct(${p.id}, '${escapeQuote(p.name)}', ${p.price}, '${p.image_url}')">Chọn mua</button>`
                                }
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
        closeSelectModal();
    }

    function removeProduct(partKey) {
        delete pcConfig[partKey];
        localStorage.setItem('pc_config', JSON.stringify(pcConfig));
        updateUI();
    }

    function updateUI() {
        let total = 0;
        let count = 0;

        // Reset tất cả hàng về trạng thái placeholder ban đầu
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

        // Cập nhật widget tóm tắt bên phải
        document.getElementById('selected-count').innerText = count;
        document.getElementById('total-price-display').innerText = formatMoney(total) + 'đ';
    }

    function resetConfig() {
        if (confirm('Bạn có chắc chắn muốn xóa tất cả linh kiện đã chọn?')) {
            pcConfig = {};
            localStorage.removeItem('pc_config');
            updateUI();
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
                // Clear cấu hình sau khi thêm giỏ hàng thành công
                localStorage.removeItem('pc_config');
                pcConfig = {};
                // Chuyển hướng tới trang giỏ hàng
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

<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>

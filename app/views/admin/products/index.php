<div class="card">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
        <h3 class="card-title" style="margin-bottom: 0;">Danh sách sản phẩm</h3>
        <div style="display: flex; gap: 10px;">
            <a href="<?= url('admin/inventory/logs') ?>" class="btn btn--outline" style="border-color: #2563EB; color: #2563EB;"><i class="fa-solid fa-clock-rotate-left"></i> Lịch sử Nhập/Xuất kho</a>
            <a href="<?= url('admin/products/create') ?>" class="btn"><i class="fa-solid fa-plus"></i> Thêm sản phẩm mới</a>
        </div>
    </div>

    <!-- Filters & Search Form -->
    <form method="get" action="<?= url('admin/products') ?>" style="margin-bottom: 25px; display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px;">
        <div>
            <label style="font-size: 12px; font-weight: 700; color: var(--text-secondary); display: block; margin-bottom: 6px;">Tìm theo tên</label>
            <input type="text" name="search" class="form-control" placeholder="Từ khoá..." value="<?= e($search) ?>">
        </div>
        <div>
            <label style="font-size: 12px; font-weight: 700; color: var(--text-secondary); display: block; margin-bottom: 6px;">Danh mục</label>
            <select name="category_id" class="form-control">
                <option value="">Tất cả danh mục</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= (int)$cat['id'] ?>" <?= $categoryId === (int)$cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="font-size: 12px; font-weight: 700; color: var(--text-secondary); display: block; margin-bottom: 6px;">Thương hiệu</label>
            <select name="brand_id" class="form-control">
                <option value="">Tất cả thương hiệu</option>
                <?php foreach ($brands as $b): ?>
                    <option value="<?= (int)$b['id'] ?>" <?= $brandId === (int)$b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="font-size: 12px; font-weight: 700; color: var(--text-secondary); display: block; margin-bottom: 6px;">Trạng thái</label>
            <select name="status" class="form-control">
                <option value="">Tất cả trạng thái</option>
                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Hiển thị</option>
                <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Tạm ẩn/Khoá</option>
            </select>
        </div>
        <div style="display: flex; align-items: flex-end; gap: 8px;">
            <label class="checkbox" style="font-size: 12px; font-weight: 700; color: var(--text-secondary); display: flex; align-items: center; gap: 6px; height: 40px; margin-bottom: 0; cursor: pointer;">
                <input type="checkbox" name="low_stock" value="1" <?= $lowStock === 1 ? 'checked' : '' ?>> Tồn kho thấp (<10)
            </label>
        </div>
        <div style="display: flex; align-items: flex-end; gap: 8px;">
            <button type="submit" class="btn btn--outline" style="width: 100%; height: 40px; justify-content: center;"><i class="fa-solid fa-filter"></i> Lọc</button>
            <?php if ($search !== '' || $categoryId > 0 || $brandId > 0 || $status !== '' || $lowStock > 0): ?>
                <a href="<?= url('admin/products') ?>" class="btn btn--secondary" style="height: 40px; display: inline-flex; align-items: center; justify-content: center;" title="Xoá tất cả bộ lọc"><i class="fa-solid fa-filter-circle-xmark"></i></a>
            <?php endif; ?>
        </div>
    </form>

    <style>
        .status-toggle-switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
            cursor: pointer;
            vertical-align: middle;
        }
        .status-toggle-input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .status-toggle-slider {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: #CBD5E1;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 24px;
        }
        .status-toggle-knob {
            position: absolute;
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .status-toggle-input:checked + .status-toggle-slider {
            background-color: #10B981;
        }
        .status-toggle-input:checked + .status-toggle-slider .status-toggle-knob {
            transform: translateX(20px);
        }
        .status-toggle-input:disabled + .status-toggle-slider {
            opacity: 0.6;
            cursor: wait;
        }
    </style>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 60px; text-align: center;">STT</th>
                    <th>Tên sản phẩm</th>
                    <th>Danh mục / Brand</th>
                    <th>Giá gốc</th>
                    <th>Giá Sale</th>
                    <th>Tồn kho</th>
                    <th style="text-align: center; width: 100px;">Trạng thái</th>
                    <th style="width: 160px; text-align: center;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($products)): ?>
                    <?php 
                        $pageLimit = (int)($limit ?? 10);
                        $stt = ($page - 1) * $pageLimit;
                        foreach ($products as $idx => $p): 
                            $stt++;
                    ?>
                        <tr>
                            <td style="text-align: center; font-weight: 600; color: var(--text-secondary);"><?= $stt ?></td>
                            <td>
                                <strong style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; font-size: 13.5px;"><?= e($p['name']) ?></strong>
                            </td>
                            <td>
                                <span style="font-size: 12.5px; display: block; font-weight: 600; color: var(--text-primary);"><?= e($p['category_name']) ?></span>
                                <small style="color: var(--text-secondary);"><?= e($p['brand_name']) ?></small>
                            </td>
                            <td><?= formatPrice($p['price']) ?></td>
                            <td>
                                <?php if ($p['sale_price'] !== null): ?>
                                    <span style="color: var(--primary); font-weight: 700;"><?= formatPrice($p['sale_price']) ?></span>
                                <?php else: ?>
                                    <span style="color: #9CA3AF; font-style: italic;">Không sale</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 4px;">
                                    <?php if ((int)$p['stock'] < 10): ?>
                                        <span class="badge badge--danger" style="font-weight: 700;"><?= (int)$p['stock'] ?> chiếc</span>
                                    <?php else: ?>
                                        <span class="badge badge--success" style="background-color: #E0F2FE; color: #0369A1; font-weight: 700;"><?= (int)$p['stock'] ?> chiếc</span>
                                    <?php endif; ?>
                                    <button type="button" onclick="openStockModal(<?= (int)$p['id'] ?>, '<?= e(addslashes($p['name'])) ?>', <?= (int)$p['stock'] ?>)" class="btn btn--outline btn--sm" style="padding: 2px 8px; font-size: 11px; white-space: nowrap; margin-top: 2px; border-color: var(--primary); color: var(--primary);" title="Nhập hoặc xuất kho cho sản phẩm này">
                                        <i class="fa-solid fa-boxes-packing"></i> Nhập/Xuất
                                    </button>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <?php
                                $statusBadgeMap = [
                                    'active'       => ['bg' => '#D1FAE5', 'color' => '#065F46', 'label' => 'Đang bán'],
                                    'hidden'       => ['bg' => '#F3F4F6', 'color' => '#374151', 'label' => 'Tạm ẩn'],
                                    'out_of_stock' => ['bg' => '#FFEDD5', 'color' => '#C2410C', 'label' => 'Hết hàng'],
                                    'discontinued' => ['bg' => '#FEE2E2', 'color' => '#991B1B', 'label' => 'Ngừng KD'],
                                    'archived'     => ['bg' => '#F3E8FF', 'color' => '#6B21A8', 'label' => 'Lưu trữ'],
                                    'inactive'     => ['bg' => '#E5E7EB', 'color' => '#4B5563', 'label' => 'Tạm khóa'],
                                    'draft'        => ['bg' => '#FEF3C7', 'color' => '#92400E', 'label' => 'Bản nháp']
                                ];
                                $stInfo = $statusBadgeMap[$p['status']] ?? ['bg' => '#F3F4F6', 'color' => '#374151', 'label' => $p['status']];
                                ?>
                                <label class="status-toggle-switch" title="Bật/tắt trạng thái hiển thị">
                                    <input type="checkbox"
                                           class="status-toggle-input"
                                           data-id="<?= (int)$p['id'] ?>"
                                           <?= $p['status'] === 'active' ? 'checked' : '' ?>
                                           onchange="toggleProductStatus(this, <?= (int)$p['id'] ?>)">
                                    <span class="status-toggle-slider">
                                        <span class="status-toggle-knob"></span>
                                    </span>
                                </label>
                                <span class="badge" id="status-label-<?= (int)$p['id'] ?>" style="background-color: <?= $stInfo['bg'] ?>; color: <?= $stInfo['color'] ?>; font-weight: 700; display: inline-block; margin-top: 4px; font-size: 11px;">
                                    <?= $stInfo['label'] ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: flex; gap: 6px; justify-content: center; align-items: center; min-height: 38px; flex-wrap: wrap;">
                                    <a href="<?= url('admin/products/edit/' . $p['id']) ?>" class="btn btn--outline btn--sm" style="padding: 6px 10px; font-size: 12px; white-space: nowrap;"><i class="fa-solid fa-pen-to-square"></i> Sửa</a>
                                    
                                    <form method="post" action="<?= url('admin/products/delete/' . $p['id']) ?>" onsubmit="return confirm('<?= ((int)($p['sold_count'] ?? 0) > 0 || $p['status'] === 'archived') ? 'Sản phẩm đã có lịch sử kinh doanh. Thao tác này sẽ chuyển sang trạng thái Lưu trữ (Archived) bảo toàn dữ liệu.' : 'Bạn có chắc chắn muốn xóa/lưu trữ sản phẩm này không?' ?>');" style="margin: 0;">
                                        <?= csrf_field() ?>
                                        <?php if ((int)($p['sold_count'] ?? 0) > 0 || $p['status'] === 'archived'): ?>
                                            <button type="submit" class="btn btn--secondary btn--sm" style="padding: 6px 10px; font-size: 12px; white-space: nowrap; background: #F3E8FF; color: #6B21A8; border: 1px solid #D8B4FE;" title="Lưu trữ bảo toàn dữ liệu"><i class="fa-solid fa-box-archive"></i> Lưu trữ</button>
                                        <?php else: ?>
                                            <button type="submit" class="btn btn--danger btn--sm" style="padding: 6px 10px; font-size: 12px; white-space: nowrap;" title="Xóa sản phẩm"><i class="fa-solid fa-trash-can"></i> Xóa</button>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-secondary); padding: 30px;">Không tìm thấy sản phẩm nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
    function toggleProductStatus(input, productId) {
        const originalChecked = !input.checked;
        input.disabled = true;
        
        fetch('<?= url("admin/products/toggle-status/") ?>' + productId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-Token': '<?= $_SESSION["csrf_token"] ?? "" ?>'
            },
            body: '_csrf=' + encodeURIComponent('<?= $_SESSION["csrf_token"] ?? "" ?>')
        })
        .then(res => res.json())
        .then(data => {
            input.disabled = false;
            if (data.success) {
                const label = document.getElementById('status-label-' + productId);
                if (label) {
                    label.innerText = data.status_text;
                    label.style.color = data.status === 'active' ? '#10B981' : '#6B7280';
                }
            } else {
                input.checked = originalChecked;
                alert(data.error || 'Không thể thay đổi trạng thái');
            }
        })
        .catch(err => {
            input.disabled = false;
            input.checked = originalChecked;
            alert('Lỗi kết nối máy chủ');
        });
    }
    </script>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div style="display: flex; justify-content: center; align-items: center; gap: 6px; margin-top: 25px; flex-wrap: wrap;">
            <!-- Prev Arrow -->
            <?php if ($page > 1): 
                $q = $_GET; $q['page'] = $page - 1; 
            ?>
                <a href="<?= url('admin/products?' . http_build_query($q)) ?>" class="btn btn--outline" style="padding: 6px 12px; font-size: 13px;" title="Trang trước"><i class="fa-solid fa-chevron-left"></i></a>
            <?php endif; ?>

            <?php
            $window = 2;
            $startPage = max(1, $page - $window);
            $endPage = min($totalPages, $page + $window);

            if ($startPage > 1): 
                $q = $_GET; $q['page'] = 1;
            ?>
                <a href="<?= url('admin/products?' . http_build_query($q)) ?>" class="btn btn--outline" style="padding: 6px 12px; font-size: 13px;">1</a>
                <?php if ($startPage > 2): ?>
                    <span style="padding: 6px 8px; color: var(--text-secondary); font-weight: 700;">...</span>
                <?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $startPage; $i <= $endPage; $i++): 
                $q = $_GET; $q['page'] = $i;
            ?>
                <a href="<?= url('admin/products?' . http_build_query($q)) ?>" class="btn <?= $page === $i ? '' : 'btn--outline' ?>" style="padding: 6px 12px; font-size: 13px; font-weight: 700;"><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($endPage < $totalPages): 
                $q = $_GET; $q['page'] = $totalPages;
            ?>
                <?php if ($endPage < $totalPages - 1): ?>
                    <span style="padding: 6px 8px; color: var(--text-secondary); font-weight: 700;">...</span>
                <?php endif; ?>
                <a href="<?= url('admin/products?' . http_build_query($q)) ?>" class="btn btn--outline" style="padding: 6px 12px; font-size: 13px;"><?= $totalPages ?></a>
            <?php endif; ?>

            <!-- Next Arrow -->
            <?php if ($page < $totalPages): 
                $q = $_GET; $q['page'] = $page + 1; 
            ?>
                <a href="<?= url('admin/products?' . http_build_query($q)) ?>" class="btn btn--outline" style="padding: 6px 12px; font-size: 13px;" title="Trang sau"><i class="fa-solid fa-chevron-right"></i></a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- MODAL NHẬP / XUẤT KHO SẢN PHẨM -->
<div id="stockModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 99999; justify-content: center; align-items: center; padding: 20px;">
    <div style="background: #FFFFFF; border-radius: 12px; max-width: 520px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden; animation: modalFadeIn 0.2s ease-out;">
        <div style="background: linear-gradient(135deg, #0F172A, #1E293B); padding: 18px 24px; display: flex; justify-content: space-between; align-items: center; color: #FFFFFF;">
            <h4 style="margin: 0; font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-boxes-packing" style="color: #38BDF8;"></i> Điều chỉnh tồn kho sản phẩm
            </h4>
            <button type="button" onclick="closeStockModal()" style="background: none; border: none; color: #94A3B8; font-size: 20px; cursor: pointer; padding: 0;">&times;</button>
        </div>

        <form method="post" action="<?= url('admin/products/adjust-stock') ?>" id="stockModalForm" style="padding: 24px;" onsubmit="handleStockModalSubmit(event)">
            <?= csrf_field() ?>
            <input type="hidden" name="product_id" id="modal_product_id" value="0">
            <input type="hidden" name="idempotency_key" id="modal_idempotency_key" value="">

            <div style="margin-bottom: 16px; background: #F8FAFC; border-radius: 8px; padding: 14px 16px; border: 1px solid #E2E8F0;">
                <label style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; display: block; margin-bottom: 4px;">Sản phẩm đã chọn</label>
                <div id="modal_product_name" style="font-weight: 700; font-size: 14px; color: #0F172A; line-height: 1.4;">-</div>
                <div style="display: flex; gap: 16px; margin-top: 8px; font-size: 12.5px;">
                    <div>Tồn hiện tại: <strong id="modal_current_stock" style="color: #2563EB;">0</strong> đơn vị</div>
                    <div>Dự kiến sau thao tác: <strong id="modal_expected_stock" style="color: #16A34A;">0</strong> đơn vị</div>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="font-size: 13px; font-weight: 700; color: #334155; display: block; margin-bottom: 8px;">Loại thao tác <span style="color: #EF4444;">*</span></label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <label style="display: flex; align-items: center; justify-content: center; gap: 8px; border: 2px solid #22C55E; border-radius: 8px; padding: 10px; cursor: pointer; background: #F0FDF4; font-weight: 700; font-size: 13px; color: #15803D;">
                        <input type="radio" name="action_type" value="import" checked onchange="updateModalTheme()"> <i class="fa-solid fa-square-plus"></i> Nhập kho (+)
                    </label>
                    <label style="display: flex; align-items: center; justify-content: center; gap: 8px; border: 2px solid #EF4444; border-radius: 8px; padding: 10px; cursor: pointer; background: #FEF2F2; font-weight: 700; font-size: 13px; color: #B91C1C;">
                        <input type="radio" name="action_type" value="export" onchange="updateModalTheme()"> <i class="fa-solid fa-square-minus"></i> Xuất kho (-)
                    </label>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="font-size: 13px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Số lượng thay đổi <span style="color: #EF4444;">*</span></label>
                <input type="number" name="quantity" id="modal_quantity" class="form-control" min="1" value="1" required style="font-size: 15px; font-weight: 700;" oninput="updateExpectedStock()">
            </div>

            <div style="margin-bottom: 16px;">
                <label style="font-size: 13px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Loại lý do <span style="color: #EF4444;">*</span></label>
                <select name="reason_code" id="modal_reason_code" class="form-control" style="font-size: 13px;" onchange="updateModalTheme()">
                    <option value="supplier_import">Nhập từ nhà cung cấp</option>
                    <option value="inventory_check">Điều chỉnh kiểm kê</option>
                    <option value="damaged_goods">Hàng hỏng / lỗi kỹ thuật</option>
                    <option value="sample_export">Xuất hàng mẫu / quà tặng</option>
                    <option value="supplier_return">Trả hàng nhà cung cấp</option>
                    <option value="restock_return">Nhập lại từ đơn hoàn trả</option>
                    <option value="other" selected>Khác</option>
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="font-size: 13px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Ghi chú / Chi tiết <span id="note_required_badge" style="color: #EF4444; display: none;">* (Bắt buộc khi Xuất kho)</span></label>
                <input type="text" name="note" id="modal_note" class="form-control" placeholder="Ví dụ: Xuất mẫu cho phòng kỹ thuật / Kiểm kê phát hiện chênh lệch..." style="font-size: 13px;">
            </div>

            <div id="modal_error_msg" style="display: none; background: #FEF2F2; border: 1px solid #FCA5A5; color: #991B1B; padding: 10px 14px; border-radius: 8px; font-size: 12.5px; margin-bottom: 16px; font-weight: 600;"></div>

            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" class="btn btn--outline" onclick="closeStockModal()">Hủy bỏ</button>
                <button type="submit" class="btn" id="modal_submit_btn" style="background: #22C55E; border: none; font-weight: 700;"><i class="fa-solid fa-check"></i> Xác nhận thực hiện</button>
            </div>
        </form>
    </div>
</div>

<script>
let currentRawStock = 0;

function openStockModal(productId, productName, currentStock) {
    currentRawStock = Number(currentStock) || 0;
    document.getElementById('modal_product_id').value = productId;
    document.getElementById('modal_product_name').innerText = productName;
    document.getElementById('modal_current_stock').innerText = currentRawStock.toLocaleString('vi-VN');
    document.getElementById('modal_quantity').value = 1;
    document.getElementById('modal_note').value = '';
    document.getElementById('modal_idempotency_key').value = 'adj_' + productId + '_' + Date.now() + '_' + Math.random().toString(36).substr(2, 6);
    document.getElementById('modal_error_msg').style.display = 'none';

    updateModalTheme();
    updateExpectedStock();

    const modal = document.getElementById('stockModal');
    modal.style.display = 'flex';
}

function closeStockModal() {
    const modal = document.getElementById('stockModal');
    modal.style.display = 'none';
}

function updateExpectedStock() {
    const isImport = document.querySelector('input[name="action_type"]:checked').value === 'import';
    const qty = Math.max(1, parseInt(document.getElementById('modal_quantity').value) || 0);
    const expected = isImport ? (currentRawStock + qty) : (currentRawStock - qty);

    const expElem = document.getElementById('modal_expected_stock');
    expElem.innerText = expected.toLocaleString('vi-VN');

    const errBox = document.getElementById('modal_error_msg');
    if (!isImport && qty > currentRawStock) {
        expElem.style.color = '#DC2626';
        errBox.style.display = 'block';
        errBox.innerText = 'Số lượng xuất kho (' + qty + ') vượt quá số tồn kho hiện tại (' + currentRawStock + '). Vui lòng giảm số lượng!';
        document.getElementById('modal_submit_btn').disabled = true;
        document.getElementById('modal_submit_btn').style.opacity = '0.6';
    } else {
        expElem.style.color = isImport ? '#16A34A' : '#2563EB';
        errBox.style.display = 'none';
        document.getElementById('modal_submit_btn').disabled = false;
        document.getElementById('modal_submit_btn').style.opacity = '1';
    }
}

function updateModalTheme() {
    const isImport = document.querySelector('input[name="action_type"]:checked').value === 'import';
    const submitBtn = document.getElementById('modal_submit_btn');
    const noteBadge = document.getElementById('note_required_badge');
    const noteInput = document.getElementById('modal_note');

    if (isImport) {
        submitBtn.style.background = '#22C55E';
        submitBtn.innerHTML = '<i class="fa-solid fa-check"></i> Xác nhận Nhập kho';
        noteBadge.style.display = 'none';
        noteInput.removeAttribute('required');
    } else {
        submitBtn.style.background = '#EF4444';
        submitBtn.innerHTML = '<i class="fa-solid fa-check"></i> Xác nhận Xuất kho';
        noteBadge.style.display = 'inline';
        noteInput.setAttribute('required', 'required');
    }
    updateExpectedStock();
}

function handleStockModalSubmit(e) {
    const isImport = document.querySelector('input[name="action_type"]:checked').value === 'import';
    const qty = parseInt(document.getElementById('modal_quantity').value) || 0;
    const note = document.getElementById('modal_note').value.trim();

    if (!isImport && qty > currentRawStock) {
        e.preventDefault();
        alert('Không thể xuất vượt quá số tồn kho hiện có!');
        return false;
    }
    if (!isImport && !note) {
        e.preventDefault();
        alert('Vui lòng nhập ghi chú khi xuất kho!');
        return false;
    }

    const btn = document.getElementById('modal_submit_btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...';
}
</script>

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

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 80px;">ID</th>
                    <th style="width: 80px;">Ảnh</th>
                    <th>Tên sản phẩm</th>
                    <th>Danh mục / Brand</th>
                    <th>Giá gốc</th>
                    <th>Giá Sale</th>
                    <th>Tồn kho</th>
                    <th>Trạng thái</th>
                    <th style="width: 180px; text-align: center;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td><?= (int)$p['id'] ?></td>
                            <td>
                                <img src="<?= e(productImageUrl($p['image'] ?? '', $p['name'] ?? '')) ?>" alt="<?= e($p['name']) ?>" style="width: 44px; height: 44px; object-fit: contain; border: 1px solid var(--border); border-radius: 4px; padding: 2px; background: var(--bg-body);">
                            </td>
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
                            <td>
                                <span class="badge <?= $p['status'] === 'active' ? 'badge--success' : 'badge--danger' ?>">
                                    <?= $p['status'] === 'active' ? 'Hiển thị' : 'Ẩn/Khoá' ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: flex; gap: 6px; justify-content: center; align-items: center; min-height: 38px; flex-wrap: wrap;">
                                    <a href="<?= url('admin/products/edit/' . $p['id']) ?>" class="btn btn--outline btn--sm" style="padding: 6px 10px; font-size: 12px; white-space: nowrap;"><i class="fa-solid fa-pen-to-square"></i> Sửa</a>
                                    
                                    <form method="post" action="<?= url('admin/products/delete/' . $p['id']) ?>" onsubmit="return confirm('Bạn có chắc chắn muốn xoá sản phẩm này?');" style="margin: 0;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn--danger btn--sm" style="padding: 6px 10px; font-size: 12px; white-space: nowrap;"><i class="fa-solid fa-trash-can"></i> Xoá</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align: center; color: var(--text-secondary); padding: 30px;">Không tìm thấy sản phẩm nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

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
    <div style="background: #FFFFFF; border-radius: 12px; max-width: 480px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden; animation: modalFadeIn 0.2s ease-out;">
        <div style="background: linear-gradient(135deg, #0F172A, #1E293B); padding: 18px 24px; display: flex; justify-content: space-between; align-items: center; color: #FFFFFF;">
            <h4 style="margin: 0; font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-boxes-packing" style="color: #38BDF8;"></i> Điều chỉnh tồn kho sản phẩm
            </h4>
            <button type="button" onclick="closeStockModal()" style="background: none; border: none; color: #94A3B8; font-size: 20px; cursor: pointer; padding: 0;">&times;</button>
        </div>

        <form method="post" action="<?= url('admin/products/adjust-stock') ?>" style="padding: 24px;">
            <?= csrf_field() ?>
            <input type="hidden" name="product_id" id="modal_product_id" value="0">

            <div style="margin-bottom: 16px; background: #F8FAFC; border-radius: 8px; padding: 12px 16px; border: 1px solid #E2E8F0;">
                <label style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; display: block; margin-bottom: 4px;">Sản phẩm chọn</label>
                <div id="modal_product_name" style="font-weight: 700; font-size: 14px; color: #0F172A; line-height: 1.4;">-</div>
                <div style="font-size: 12px; color: #475569; margin-top: 4px;">Tồn kho hiện tại: <strong id="modal_current_stock" style="color: #2563EB;">0</strong> chiếc</div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="font-size: 13px; font-weight: 700; color: #334155; display: block; margin-bottom: 8px;">Loại thao tác</label>
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
                <label style="font-size: 13px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Số lượng nhập / xuất <span style="color: #EF4444;">*</span></label>
                <input type="number" name="quantity" id="modal_quantity" class="form-control" min="1" value="1" required style="font-size: 15px; font-weight: 700;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="font-size: 13px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Lý do / Ghi chú</label>
                <input type="text" name="note" class="form-control" placeholder="Ví dụ: Nhập hàng mới từ NCC / Kiểm kê điều chỉnh..." style="font-size: 13px;">
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" class="btn btn--outline" onclick="closeStockModal()">Hủy bỏ</button>
                <button type="submit" class="btn" id="modal_submit_btn" style="background: #22C55E; border: none; font-weight: 700;"><i class="fa-solid fa-check"></i> Xác nhận thực hiện</button>
            </div>
        </form>
    </div>
</div>

<script>
function openStockModal(productId, productName, currentStock) {
    document.getElementById('modal_product_id').value = productId;
    document.getElementById('modal_product_name').innerText = productName;
    document.getElementById('modal_current_stock').innerText = Number(currentStock).toLocaleString('vi-VN');
    document.getElementById('modal_quantity').value = 1;

    const modal = document.getElementById('stockModal');
    modal.style.display = 'flex';
}

function closeStockModal() {
    const modal = document.getElementById('stockModal');
    modal.style.display = 'none';
}

function updateModalTheme() {
    const isImport = document.querySelector('input[name="action_type"]:checked').value === 'import';
    const submitBtn = document.getElementById('modal_submit_btn');
    if (isImport) {
        submitBtn.style.background = '#22C55E';
        submitBtn.innerHTML = '<i class="fa-solid fa-check"></i> Xác nhận Nhập kho';
    } else {
        submitBtn.style.background = '#EF4444';
        submitBtn.innerHTML = '<i class="fa-solid fa-check"></i> Xác nhận Xuất kho';
    }
}
</script>

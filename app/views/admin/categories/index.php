<style>
    .status-toggle-switch {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
        margin: 0;
        vertical-align: middle;
    }
    .status-toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .status-toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #D1D5DB;
        transition: .3s;
        border-radius: 24px;
    }
    .status-toggle-knob {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
        box-shadow: 0 1px 3px rgba(0,0,0,0.3);
    }
    .status-toggle-input:checked + .status-toggle-slider {
        background-color: #10B981;
    }
    .status-toggle-input:checked + .status-toggle-slider .status-toggle-knob {
        transform: translateX(20px);
    }
</style>

<div class="card">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
        <h3 class="card-title" style="margin-bottom: 0;">Danh sách danh mục sản phẩm</h3>
        <a href="<?= url('admin/categories/create') ?>" class="btn"><i class="fa-solid fa-plus"></i> Thêm danh mục mới</a>
    </div>

    <!-- Search Form -->
    <form method="get" action="<?= url('admin/categories') ?>" style="margin-bottom: 20px; display: flex; gap: 10px;">
        <input type="text" name="search" class="form-control" placeholder="Tìm danh mục theo tên..." value="<?= e($search) ?>" style="max-width: 300px;">
        <button type="submit" class="btn btn--outline"><i class="fa-solid fa-magnifying-glass"></i> Tìm kiếm</button>
        <?php if ($search !== ''): ?>
            <a href="<?= url('admin/categories') ?>" class="btn btn--secondary">Xoá lọc</a>
        <?php endif; ?>
    </form>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 60px; text-align: center;">STT</th>
                    <th>Tên danh mục</th>
                    <th>Slug</th>
                    <th>Thứ tự</th>
                    <th>Số mẫu</th>
                    <th>Tổng tồn kho</th>
                    <th style="text-align: center;">Trạng thái</th>
                    <th style="width: 140px; text-align: center;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $index => $cat): ?>
                        <tr>
                            <td style="text-align: center; font-weight: 600; color: var(--text-secondary);"><?= $index + 1 ?></td>
                            <td><strong><?= e($cat['name']) ?></strong></td>
                            <td><code><?= e($cat['slug']) ?></code></td>
                            <td><?= (int)$cat['sort_order'] ?></td>
                            <td><span class="badge" style="background-color: #F3E8FF; color: #6B21A8; font-weight: 700;"><?= (int)($cat['product_models'] ?? $cat['product_count'] ?? 0) ?> mẫu</span></td>
                            <td><span class="badge" style="background-color: #E0F2FE; color: #0369A1; font-weight: 700;"><?= number_format((int)($cat['inventory_units'] ?? 0)) ?> đơn vị</span></td>
                            <td style="text-align: center;">
                                <label class="status-toggle-switch" title="Bật/tắt hiển thị danh mục. Khi ẩn danh mục, tất cả sản phẩm thuộc danh mục này sẽ tự động ẩn khỏi trang bán hàng.">
                                    <input type="checkbox"
                                           class="status-toggle-input"
                                           data-id="<?= (int)$cat['id'] ?>"
                                           <?= $cat['status'] === 'active' ? 'checked' : '' ?>
                                           onchange="toggleCategoryStatus(this, <?= (int)$cat['id'] ?>)">
                                    <span class="status-toggle-slider">
                                        <span class="status-toggle-knob"></span>
                                    </span>
                                </label>
                                <br>
                                <span class="badge" id="cat-status-label-<?= (int)$cat['id'] ?>" style="background-color: <?= $cat['status'] === 'active' ? '#D1FAE5' : '#F3F4F6' ?>; color: <?= $cat['status'] === 'active' ? '#065F46' : '#374151' ?>; font-weight: 700; display: inline-block; margin-top: 4px; font-size: 11px;">
                                    <?= $cat['status'] === 'active' ? 'Đang hoạt động' : 'Tạm ẩn' ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: flex; gap: 6px; justify-content: center; align-items: center; min-height: 38px;">
                                    <a href="<?= url('admin/categories/edit/' . $cat['id']) ?>" class="btn btn--outline btn--sm" style="padding: 6px 14px; font-size: 12.5px; white-space: nowrap; font-weight: 600;"><i class="fa-solid fa-pen-to-square"></i> Sửa</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-secondary); padding: 30px;">Không tìm thấy danh mục nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function toggleCategoryStatus(input, id) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    fetch('<?= url("admin/categories/toggle-status/") ?>' + id, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const label = document.getElementById('cat-status-label-' + id);
            if (label) {
                label.textContent = data.status_label;
                label.style.backgroundColor = data.new_status === 'active' ? '#D1FAE5' : '#F3F4F6';
                label.style.color = data.new_status === 'active' ? '#065F46' : '#374151';
            }
        } else {
            alert(data.message || 'Lỗi cập nhật trạng thái danh mục');
            input.checked = !input.checked;
        }
    })
    .catch(err => {
        alert('Không thể kết nối máy chủ');
        input.checked = !input.checked;
    });
}
</script>

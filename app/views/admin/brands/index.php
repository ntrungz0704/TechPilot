<div class="card">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
        <h3 class="card-title" style="margin-bottom: 0;">Danh sách thương hiệu</h3>
        <a href="<?= url('admin/brands/create') ?>" class="btn"><i class="fa-solid fa-plus"></i> Thêm thương hiệu mới</a>
    </div>

    <!-- Search Form -->
    <form method="get" action="<?= url('admin/brands') ?>" style="margin-bottom: 20px; display: flex; gap: 10px;">
        <input type="text" name="search" class="form-control" placeholder="Tìm thương hiệu theo tên..." value="<?= e($search) ?>" style="max-width: 300px;">
        <button type="submit" class="btn btn--outline"><i class="fa-solid fa-magnifying-glass"></i> Tìm kiếm</button>
        <?php if ($search !== ''): ?>
            <a href="<?= url('admin/brands') ?>" class="btn btn--secondary">Xoá lọc</a>
        <?php endif; ?>
    </form>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 60px; text-align: center;">STT</th>
                    <th style="width: 120px;">Logo</th>
                    <th>Tên thương hiệu</th>
                    <th>Slug</th>
                    <th>Mô tả</th>
                    <th>Số sản phẩm</th>
                    <th style="width: 140px; text-align: center;">Trạng thái</th>
                    <th style="width: 100px; text-align: center;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($brands)): ?>
                    <?php foreach ($brands as $index => $b): ?>
                        <tr>
                            <td style="text-align: center; font-weight: 600; color: var(--text-secondary);"><?= $index + 1 ?></td>
                            <td>
                                <?php $logoUrl = brandLogoUrl($b['logo'] ?? null, $b['slug'] ?? null); ?>
                                <?php if ($logoUrl): ?>
                                    <img src="<?= $logoUrl ?>" alt="<?= e($b['name']) ?>" style="height: 36px; max-width: 100px; object-fit: contain; display: block;">
                                <?php else: ?>
                                    <span style="color: #9CA3AF; font-size: 12px; font-style: italic;">Không có logo</span>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= e($b['name']) ?></strong></td>
                            <td><code><?= e($b['slug']) ?></code></td>
                            <td><span style="font-size: 13px; color: var(--text-secondary); display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden;"><?= e($b['description']) ?></span></td>
                            <td><span class="badge badge--success" style="background-color: #E0F2FE; color: #0369A1;"><?= (int)$b['product_count'] ?> sản phẩm</span></td>
                            <td style="text-align: center;">
                                <?php $isActive = (($b['status'] ?? 'active') === 'active'); ?>
                                <label class="toggle-switch" title="Bật/Tắt hiển thị thương hiệu">
                                    <input type="checkbox" 
                                           class="brand-status-toggle" 
                                           data-brand-id="<?= (int)$b['id'] ?>" 
                                           <?= $isActive ? 'checked' : '' ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <div style="font-size: 11px; margin-top: 4px; font-weight: 600; color: <?= $isActive ? '#10B981' : '#6B7280' ?>;" id="statusText_<?= (int)$b['id'] ?>">
                                    <?= $isActive ? 'Đang hoạt động' : 'Tạm ẩn' ?>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <a href="<?= url('admin/brands/edit/' . $b['id']) ?>" class="btn btn--outline btn--sm" style="padding: 6px 12px; font-size: 12px; white-space: nowrap;"><i class="fa-solid fa-pen-to-square"></i> Sửa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-secondary); padding: 30px;">Không tìm thấy thương hiệu nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggles = document.querySelectorAll('.brand-status-toggle');
    const csrfToken = '<?= csrf_token() ?>';

    toggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const brandId = this.dataset.brandId;
            const isChecked = this.checked;
            const statusTextEl = document.getElementById('statusText_' + brandId);
            
            const formData = new FormData();
            formData.append('_csrf', csrfToken);

            fetch('<?= url("admin/brands/toggle-status/") ?>' + brandId, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': csrfToken
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (statusTextEl) {
                        statusTextEl.textContent = data.status_label;
                        statusTextEl.style.color = (data.new_status === 'active') ? '#10B981' : '#6B7280';
                    }
                } else {
                    alert(data.message || 'Có lỗi xảy ra.');
                    this.checked = !isChecked;
                }
            })
            .catch(err => {
                console.error(err);
                alert('Không thể kết nối máy chủ.');
                this.checked = !isChecked;
            });
        });
    });
});
</script>

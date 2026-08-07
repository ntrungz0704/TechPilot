<div class="card">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
        <h3 class="card-title" style="margin-bottom: 0;">Quản lý Banner quảng cáo & Marketing</h3>
        <a href="<?= url('admin/banners/create') ?>" class="btn"><i class="fa-solid fa-plus"></i> Thêm banner mới</a>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 60px; text-align: center;">STT</th>
                    <th style="width: 160px;">Hình ảnh</th>
                    <th>Tiêu đề banner</th>
                    <th>Vị trí hiển thị (Type)</th>
                    <th>Thứ tự sắp xếp</th>
                    <th>Đường dẫn Link</th>
                    <th>Trạng thái</th>
                    <th style="width: 200px; text-align: center;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($banners)): ?>
                    <?php foreach ($banners as $index => $bn): ?>
                        <tr>
                            <td style="text-align: center; font-weight: 600; color: var(--text-secondary);"><?= $index + 1 ?></td>
                            <td>
                                <img src="<?= bannerImageUrl($bn['image'] ?? '') ?>" alt="<?= e($bn['title']) ?>" style="width: 140px; height: 55px; object-fit: cover; border: 1px solid var(--border); border-radius: 4px; padding: 2px; background: #FFF;">
                            </td>
                            <td><strong><?= e($bn['title']) ?></strong></td>
                            <td>
                                <span class="badge" style="background-color: #E0F2FE; color: #0369A1; font-weight: 700;">
                                    <?= e($bn['type']) ?>
                                </span>
                            </td>
                            <td><?= (int)$bn['position'] ?></td>
                            <td><code><?= e($bn['link']) ?></code></td>
                            <td style="text-align: center;">
                                <?php $isActive = (($bn['status'] ?? 'active') === 'active'); ?>
                                <label class="toggle-switch" title="Bật/Tắt hiển thị banner">
                                    <input type="checkbox" 
                                           class="banner-status-toggle" 
                                           data-banner-id="<?= (int)$bn['id'] ?>" 
                                           <?= $isActive ? 'checked' : '' ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <div style="font-size: 11px; margin-top: 4px; font-weight: 600; color: <?= $isActive ? '#10B981' : '#6B7280' ?>;" id="statusText_<?= (int)$bn['id'] ?>">
                                    <?= $isActive ? 'Hiển thị' : 'Tạm ẩn' ?>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <a href="<?= url('admin/banners/edit/' . $bn['id']) ?>" class="btn btn--outline btn--sm" style="padding: 6px 12px; font-size: 12px; white-space: nowrap;"><i class="fa-solid fa-pen-to-square"></i> Sửa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-secondary); padding: 30px;">Chưa có banner nào được tạo.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggles = document.querySelectorAll('.banner-status-toggle');
    const csrfToken = '<?= csrf_token() ?>';

    toggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const bannerId = this.dataset.bannerId;
            const isChecked = this.checked;
            const statusTextEl = document.getElementById('statusText_' + bannerId);
            
            const formData = new FormData();
            formData.append('_csrf', csrfToken);

            fetch('<?= url("admin/banners/toggle-status/") ?>' + bannerId, {
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

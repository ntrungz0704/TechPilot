<div class="card">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
        <h3 class="card-title" style="margin-bottom: 0;">Mã giảm giá & Khuyến mãi (Coupons)</h3>
        <a href="<?= url('admin/coupons/create') ?>" class="btn"><i class="fa-solid fa-plus"></i> Tạo mã giảm giá mới</a>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 80px;">ID</th>
                    <th>Mã code</th>
                    <th>Loại</th>
                    <th>Giá trị giảm</th>
                    <th>Đơn tối thiểu</th>
                    <th>Giới hạn lượt dùng</th>
                    <th>Đã dùng</th>
                    <th>Thời gian hiệu lực</th>
                    <th>Trạng thái</th>
                    <th style="width: 200px; text-align: center;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($coupons)): ?>
                    <?php foreach ($coupons as $cp): ?>
                        <tr>
                            <td><?= (int)$cp['id'] ?></td>
                            <td><strong style="color: var(--primary); font-size: 14.5px;"><?= e($cp['code']) ?></strong></td>
                            <td>
                                <?php
                                if ($cp['type'] === 'fixed') echo 'Giảm tiền mặt';
                                elseif ($cp['type'] === 'percent') echo 'Giảm phần trăm';
                                elseif ($cp['type'] === 'free_shipping') echo 'Miễn phí ship';
                                ?>
                            </td>
                            <td>
                                <strong>
                                    <?php if ($cp['type'] === 'percent'): ?>
                                        <?= (float)$cp['discount_value'] ?>%
                                    <?php else: ?>
                                        <?= formatPrice($cp['discount_value']) ?>
                                    <?php endif; ?>
                                </strong>
                            </td>
                            <td><?= formatPrice($cp['min_order_value']) ?></td>
                            <td><?= $cp['usage_limit'] !== null ? (int)$cp['usage_limit'] . ' lượt' : 'Không giới hạn' ?></td>
                            <td><span class="badge badge--success" style="background-color: #E0F2FE; color: #0369A1;"><?= (int)$cp['used_count'] ?> lượt</span></td>
                            <td>
                                <span style="font-size: 12.5px; display: block; color: var(--text-secondary);">Bắt đầu: <?= $cp['start_date'] ? date('d/m/Y H:i', strtotime($cp['start_date'])) : 'Không đặt' ?></span>
                                <span style="font-size: 12.5px; display: block; color: var(--text-secondary);">Kết thúc: <?= $cp['end_date'] ? date('d/m/Y H:i', strtotime($cp['end_date'])) : 'Không đặt' ?></span>
                            </td>
                            <td style="text-align: center;">
                                <?php $isActive = (($cp['status'] ?? 'active') === 'active'); ?>
                                <label class="toggle-switch" title="Bật/Tắt kích hoạt mã giảm giá">
                                    <input type="checkbox" 
                                           class="coupon-status-toggle" 
                                           data-coupon-id="<?= (int)$cp['id'] ?>" 
                                           <?= $isActive ? 'checked' : '' ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <div style="font-size: 11px; margin-top: 4px; font-weight: 600; color: <?= $isActive ? '#10B981' : '#6B7280' ?>;" id="statusText_<?= (int)$cp['id'] ?>">
                                    <?= $isActive ? 'Hoạt động' : 'Tạm khoá' ?>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <a href="<?= url('admin/coupons/edit/' . $cp['id']) ?>" class="btn btn--outline btn--sm" style="padding: 6px 12px; font-size: 12px; white-space: nowrap;"><i class="fa-solid fa-pen-to-square"></i> Sửa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" style="text-align: center; color: var(--text-secondary); padding: 30px;">Chưa có mã giảm giá nào được tạo.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggles = document.querySelectorAll('.coupon-status-toggle');
    const csrfToken = '<?= csrf_token() ?>';

    toggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const couponId = this.dataset.couponId;
            const isChecked = this.checked;
            const statusTextEl = document.getElementById('statusText_' + couponId);
            
            const formData = new FormData();
            formData.append('_csrf', csrfToken);

            fetch('<?= url("admin/coupons/toggle-status/") ?>' + couponId, {
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

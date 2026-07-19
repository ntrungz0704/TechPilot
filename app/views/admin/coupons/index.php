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
                            <td>
                                <span class="badge <?= $cp['status'] === 'active' ? 'badge--success' : 'badge--danger' ?>">
                                    <?= $cp['status'] === 'active' ? 'Hoạt động' : 'Tạm khoá' ?>
                                </span>
                            </td>
                            <td style="text-align: center; display: flex; gap: 8px; justify-content: center; align-items: center;">
                                <a href="<?= url('admin/coupons/edit/' . $cp['id']) ?>" class="btn btn--outline btn--sm" style="padding: 6px 12px; font-size: 12px;"><i class="fa-solid fa-pen-to-square"></i> Sửa</a>
                                
                                <form method="post" action="<?= url('admin/coupons/delete/' . $cp['id']) ?>" onsubmit="return confirm('Bạn có chắc chắn muốn xoá mã giảm giá này?');" style="margin: 0;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn--danger btn--sm" style="padding: 6px 12px; font-size: 12px;"><i class="fa-solid fa-trash-can"></i> Xoá</button>
                                </form>
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

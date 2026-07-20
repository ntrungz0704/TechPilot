<div class="card">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
        <h3 class="card-title" style="margin-bottom: 0;">Chương trình Flash Sale</h3>
        <a href="<?= url('admin/flash-sales/create') ?>" class="btn"><i class="fa-solid fa-plus"></i> Tạo chiến dịch mới</a>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 80px;">ID</th>
                    <th>Tên chiến dịch</th>
                    <th>Thời gian bắt đầu</th>
                    <th>Thời gian kết thúc</th>
                    <th>Số sản phẩm</th>
                    <th>Trạng thái</th>
                    <th style="width: 200px; text-align: center;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($flashSales)): ?>
                    <?php foreach ($flashSales as $fs): ?>
                        <tr>
                            <td><?= (int)$fs['id'] ?></td>
                            <td><strong><?= e($fs['title']) ?></strong></td>
                            <td><code><?= date('d/m/Y H:i', strtotime($fs['start_time'])) ?></code></td>
                            <td><code><?= date('d/m/Y H:i', strtotime($fs['end_time'])) ?></code></td>
                            <td><span class="badge badge--success" style="background-color: #E0F2FE; color: #0369A1;"><?= (int)$fs['item_count'] ?> sản phẩm</span></td>
                            <td>
                                <?php
                                $statusClass = 'badge--warning';
                                $statusLabel = 'Bản nháp';
                                if ($fs['status'] === 'active') { $statusClass = 'badge--success'; $statusLabel = 'Đang chạy'; }
                                if ($fs['status'] === 'ended') { $statusClass = 'badge--danger'; $statusLabel = 'Đã kết thúc'; }
                                if ($fs['status'] === 'cancelled') { $statusClass = 'badge--danger'; $statusLabel = 'Đã huỷ'; }
                                ?>
                                <span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: flex; gap: 8px; justify-content: center; align-items: center; min-height: 38px; flex-wrap: wrap;">
                                    <a href="<?= url('admin/flash-sales/edit/' . $fs['id']) ?>" class="btn btn--outline btn--sm" style="padding: 6px 12px; font-size: 12px; white-space: nowrap;"><i class="fa-solid fa-pen-to-square"></i> Sửa</a>
                                    
                                    <form method="post" action="<?= url('admin/flash-sales/delete/' . $fs['id']) ?>" onsubmit="return confirm('Bạn có chắc chắn muốn xoá chiến dịch Flash Sale này không?');" style="margin: 0;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn--danger btn--sm" style="padding: 6px 12px; font-size: 12px; white-space: nowrap;"><i class="fa-solid fa-trash-can"></i> Xoá</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-secondary); padding: 30px;">Chưa có chiến dịch Flash Sale nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

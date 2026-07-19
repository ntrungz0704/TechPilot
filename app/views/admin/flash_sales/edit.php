<div class="card" style="max-width: 800px; margin: 0 auto 30px;">
    <h3 class="card-title">Chỉnh sửa chiến dịch Flash Sale</h3>
    
    <form method="post" action="<?= url('admin/flash-sales/update/' . $flashSale['id']) ?>">
        <?= csrf_field() ?>
        
        <div class="form-group">
            <label for="title">Tiêu đề chiến dịch <span style="color: red;">*</span></label>
            <input type="text" name="title" id="title" class="form-control" value="<?= e($flashSale['title']) ?>" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="start_time">Thời gian bắt đầu <span style="color: red;">*</span></label>
                <input type="datetime-local" name="start_time" id="start_time" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime($flashSale['start_time'])) ?>" required>
            </div>

            <div class="form-group">
                <label for="end_time">Thời gian kết thúc <span style="color: red;">*</span></label>
                <input type="datetime-local" name="end_time" id="end_time" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime($flashSale['end_time'])) ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label for="status">Trạng thái chiến dịch</label>
            <select name="status" id="status" class="form-control">
                <option value="active" <?= $flashSale['status'] === 'active' ? 'selected' : '' ?>>Đang kích hoạt (Active)</option>
                <option value="draft" <?= $flashSale['status'] === 'draft' ? 'selected' : '' ?>>Bản nháp (Draft)</option>
                <option value="ended" <?= $flashSale['status'] === 'ended' ? 'selected' : '' ?>>Đã kết thúc (Ended)</option>
                <option value="cancelled" <?= $flashSale['status'] === 'cancelled' ? 'selected' : '' ?>>Đã huỷ (Cancelled)</option>
            </select>
        </div>

        <h4 style="font-weight: 700; margin: 25px 0 15px 0; font-size: 15px; border-bottom: 1px solid var(--border); padding-bottom: 8px;">Chọn sản phẩm tham gia và thiết lập giảm giá</h4>
        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 50px;">Chọn</th>
                        <th>Tên sản phẩm</th>
                        <th>Giá gốc (đ)</th>
                        <th>Giá Flash Sale (đ)</th>
                        <th>Mở bán</th>
                        <th>Đã bán</th>
                        <th>Giới hạn/khách</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                        <?php
                        $isSelected = isset($selectedItems[(int)$p['id']]);
                        $itemData = $isSelected ? $selectedItems[(int)$p['id']] : [];
                        ?>
                        <tr>
                            <td style="text-align: center;">
                                <input type="checkbox" name="items[<?= (int)$p['id'] ?>][active]" value="1" <?= $isSelected ? 'checked' : '' ?>>
                                <input type="hidden" name="items[<?= (int)$p['id'] ?>][sold_quantity]" value="<?= (int)($itemData['sold_quantity'] ?? 0) ?>">
                            </td>
                            <td><strong><?= e($p['name']) ?></strong></td>
                            <td><?= formatPrice($p['price']) ?></td>
                            <td>
                                <input type="number" name="items[<?= (int)$p['id'] ?>][discount_price]" class="form-control" style="width: 130px; padding: 6px 10px;" value="<?= $isSelected ? (float)$itemData['discount_price'] : '' ?>" placeholder="Giảm giá..." min="0">
                            </td>
                            <td>
                                <input type="number" name="items[<?= (int)$p['id'] ?>][allocation_quantity]" class="form-control" style="width: 100px; padding: 6px 10px;" value="<?= $isSelected ? (int)$itemData['allocation_quantity'] : '10' ?>" min="1">
                            </td>
                            <td>
                                <span class="badge badge--success" style="background-color: #E0F2FE; color: #0369A1;"><?= $isSelected ? (int)$itemData['sold_quantity'] : 0 ?> cái</span>
                            </td>
                            <td>
                                <input type="number" name="items[<?= (int)$p['id'] ?>][limit_per_user]" class="form-control" style="width: 80px; padding: 6px 10px;" value="<?= $isSelected ? (int)$itemData['limit_per_user'] : '2' ?>" min="1">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 30px;">
            <button type="submit" class="btn"><i class="fa-solid fa-floppy-disk"></i> Cập nhật chiến dịch</button>
            <a href="<?= url('admin/flash-sales') ?>" class="btn btn--secondary">Quay lại</a>
        </div>
    </form>
</div>

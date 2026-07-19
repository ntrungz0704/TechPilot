<div class="card" style="max-width: 600px; margin: 0 auto 30px;">
    <h3 class="card-title">Chỉnh sửa mã giảm giá</h3>
    
    <form method="post" action="<?= url('admin/coupons/update/' . $coupon['id']) ?>">
        <?= csrf_field() ?>
        
        <div class="form-group">
            <label for="code">Mã Code Giảm Giá <span style="color: red;">*</span></label>
            <input type="text" name="code" id="code" class="form-control" style="text-transform: uppercase;" value="<?= e($coupon['code']) ?>" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="type">Loại hình giảm giá</label>
                <select name="type" id="type" class="form-control">
                    <option value="fixed" <?= $coupon['type'] === 'fixed' ? 'selected' : '' ?>>Số tiền cố định (đ)</option>
                    <option value="percent" <?= $coupon['type'] === 'percent' ? 'selected' : '' ?>>Phần trăm (%)</option>
                    <option value="free_shipping" <?= $coupon['type'] === 'free_shipping' ? 'selected' : '' ?>>Miễn phí vận chuyển (Free Ship)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="discount_value">Giá trị giảm <span style="color: red;">*</span></label>
                <input type="number" name="discount_value" id="discount_value" class="form-control" value="<?= (float)$coupon['discount_value'] ?>" min="1" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="min_order_value">Giá trị đơn hàng tối thiểu (đ)</label>
                <input type="number" name="min_order_value" id="min_order_value" class="form-control" value="<?= (float)$coupon['min_order_value'] ?>" min="0">
            </div>

            <div class="form-group">
                <label for="max_discount">Mức giảm tối đa (đ - Áp dụng cho giảm phần trăm)</label>
                <input type="number" name="max_discount" id="max_discount" class="form-control" value="<?= $coupon['max_discount'] !== null ? (float)$coupon['max_discount'] : '' ?>" placeholder="Không giới hạn" min="0">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="usage_limit">Tổng giới hạn lượt sử dụng</label>
                <input type="number" name="usage_limit" id="usage_limit" class="form-control" value="<?= $coupon['usage_limit'] !== null ? (int)$coupon['usage_limit'] : '' ?>" placeholder="Không giới hạn" min="1">
            </div>

            <div class="form-group">
                <label for="usage_limit_per_user">Giới hạn sử dụng mỗi tài khoản</label>
                <input type="number" name="usage_limit_per_user" id="usage_limit_per_user" class="form-control" value="<?= (int)$coupon['usage_limit_per_user'] ?>" min="1">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="start_date">Ngày bắt đầu hiệu lực</label>
                <input type="datetime-local" name="start_date" id="start_date" class="form-control" value="<?= $coupon['start_date'] ? date('Y-m-d\TH:i', strtotime($coupon['start_date'])) : '' ?>">
            </div>

            <div class="form-group">
                <label for="end_date">Ngày kết thúc hiệu lực</label>
                <input type="datetime-local" name="end_date" id="end_date" class="form-control" value="<?= $coupon['end_date'] ? date('Y-m-d\TH:i', strtotime($coupon['end_date'])) : '' ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="description">Mô tả mã giảm giá</label>
            <textarea name="description" id="description" class="form-control" rows="3"><?= e($coupon['description']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="status">Trạng thái kích hoạt</label>
            <select name="status" id="status" class="form-control">
                <option value="active" <?= $coupon['status'] === 'active' ? 'selected' : '' ?>>Kích hoạt (Cho phép áp dụng)</option>
                <option value="inactive" <?= $coupon['status'] === 'inactive' ? 'selected' : '' ?>>Tạm ngưng (Không cho áp dụng)</option>
            </select>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 25px;">
            <button type="submit" class="btn"><i class="fa-solid fa-floppy-disk"></i> Cập nhật</button>
            <a href="<?= url('admin/coupons') ?>" class="btn btn--secondary">Quay lại</a>
        </div>
    </form>
</div>

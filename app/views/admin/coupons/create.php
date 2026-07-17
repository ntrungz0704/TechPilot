<div class="card" style="max-width: 600px; margin: 0 auto 30px;">
    <h3 class="card-title">Thêm mã giảm giá mới</h3>
    
    <form method="post" action="<?= url('admin/coupons/store') ?>">
        <?= csrf_field() ?>
        
        <div class="form-group">
            <label for="code">Mã Code Giảm Giá <span style="color: red;">*</span></label>
            <input type="text" name="code" id="code" class="form-control" style="text-transform: uppercase;" placeholder="Ví dụ: TECHPILOT100" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="type">Loại hình giảm giá</label>
                <select name="type" id="type" class="form-control">
                    <option value="fixed">Số tiền cố định (đ)</option>
                    <option value="percent">Phần trăm (%)</option>
                    <option value="free_shipping">Miễn phí vận chuyển (Free Ship)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="discount_value">Giá trị giảm <span style="color: red;">*</span></label>
                <input type="number" name="discount_value" id="discount_value" class="form-control" placeholder="Ví dụ: 100000" min="1" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="min_order_value">Giá trị đơn hàng tối thiểu (đ)</label>
                <input type="number" name="min_order_value" id="min_order_value" class="form-control" value="0" min="0">
            </div>

            <div class="form-group">
                <label for="max_discount">Mức giảm tối đa (đ - Áp dụng cho giảm phần trăm)</label>
                <input type="number" name="max_discount" id="max_discount" class="form-control" placeholder="Để trống nếu không giới hạn" min="0">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="usage_limit">Tổng giới hạn lượt sử dụng</label>
                <input type="number" name="usage_limit" id="usage_limit" class="form-control" placeholder="Để trống nếu không giới hạn" min="1">
            </div>

            <div class="form-group">
                <label for="usage_limit_per_user">Giới hạn sử dụng mỗi tài khoản</label>
                <input type="number" name="usage_limit_per_user" id="usage_limit_per_user" class="form-control" value="1" min="1">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="start_date">Ngày bắt đầu hiệu lực</label>
                <input type="datetime-local" name="start_date" id="start_date" class="form-control">
            </div>

            <div class="form-group">
                <label for="end_date">Ngày kết thúc hiệu lực</label>
                <input type="datetime-local" name="end_date" id="end_date" class="form-control">
            </div>
        </div>

        <div class="form-group">
            <label for="description">Mô tả mã giảm giá</label>
            <textarea name="description" id="description" class="form-control" rows="3" placeholder="Ví dụ: Giảm 100k cho đơn hàng từ 5 triệu..."></textarea>
        </div>

        <div class="form-group">
            <label for="status">Trạng thái kích hoạt</label>
            <select name="status" id="status" class="form-control">
                <option value="active">Kích hoạt (Cho phép áp dụng)</option>
                <option value="inactive">Tạm ngưng (Không cho áp dụng)</option>
            </select>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 25px;">
            <button type="submit" class="btn"><i class="fa-solid fa-floppy-disk"></i> Lưu mã giảm giá</button>
            <a href="<?= url('admin/coupons') ?>" class="btn btn--secondary">Quay lại</a>
        </div>
    </form>
</div>

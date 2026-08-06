<div class="card" style="margin-bottom: 30px;">
    <h3 class="card-title">Tạo chiến dịch Flash Sale</h3>
    
    <form method="post" action="<?= url('admin/flash-sales/store') ?>">
        <?= csrf_field() ?>
        
        <div class="form-group">
            <label for="title">Tiêu đề chiến dịch <span style="color: red;">*</span></label>
            <input type="text" name="title" id="title" class="form-control" placeholder="Ví dụ: Flash Sale Giữa Tháng 7" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="start_time">Thời gian bắt đầu <span style="color: red;">*</span></label>
                <input type="datetime-local" name="start_time" id="start_time" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="end_time">Thời gian kết thúc <span style="color: red;">*</span></label>
                <input type="datetime-local" name="end_time" id="end_time" class="form-control" required>
            </div>
        </div>

        <div class="form-group">
            <label for="status">Trạng thái chiến dịch</label>
            <select name="status" id="status" class="form-control">
                <option value="active">Đang kích hoạt (Active)</option>
                <option value="draft">Bản nháp (Draft)</option>
            </select>
        </div>

        <div style="background-color: #EFF6FF; border: 1px solid #BFDBFE; color: #1E40AF; padding: 12px 16px; border-radius: 8px; margin: 15px 0 20px 0; font-size: 13.5px; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-circle-info" style="font-size: 16px; color: #3B82F6; flex-shrink: 0;"></i>
            <span><strong>Quy tắc hệ thống:</strong> Tất cả sản phẩm được chọn trong đợt này sẽ đồng bộ <strong>cùng 1 thời gian Bắt đầu & Kết thúc</strong>. Hệ thống tự động ngăn chặn việc xếp 1 sản phẩm vào nhiều chiến dịch trùng khoảng thời gian.</span>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin: 25px 0 15px 0; flex-wrap: wrap; gap: 10px; border-bottom: 1px solid var(--border); padding-bottom: 12px;">
            <h4 style="font-weight: 700; font-size: 15px; margin: 0;">Chọn sản phẩm tham gia và thiết lập giảm giá</h4>
            <div style="display: flex; gap: 15px; align-items: center;">
                <label style="cursor: pointer; display: flex; align-items: center; gap: 6px; margin: 0; font-size: 13.5px; font-weight: 600;">
                    <input type="checkbox" id="fsFilterSelected" onchange="filterFlashSaleProducts()">
                    Chỉ hiện SP đã chọn
                </label>
                <div style="position: relative; min-width: 320px;">
                    <input type="text" id="fsProductSearchInput" class="form-control" placeholder="🔍 Gõ từ khóa tìm sản phẩm nhanh..." onkeyup="filterFlashSaleProducts()" style="padding-left: 36px; height: 38px; border-radius: 8px;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94A3B8;"></i>
                </div>
            </div>
        </div>
        <div class="table-responsive" style="max-height: 450px; overflow-y: auto;">
            <table class="table" id="fsProductTable">
                <thead>
                    <tr>
                        <th style="width: 50px;">Chọn</th>
                        <th>Tên sản phẩm</th>
                        <th>Giá bán hiện tại (đ)</th>
                        <th>Giá Flash Sale (đ)</th>
                        <th>Số lượng mở bán</th>
                        <th>Giới hạn mỗi khách</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                        <?php $regularPrice = (float)($p['regular_price'] ?? $p['price']); ?>
                        <tr>
                            <td style="text-align: center;">
                                <input type="checkbox" name="items[<?= (int)$p['id'] ?>][active]" value="1">
                            </td>
                            <td><strong><?= e($p['name']) ?></strong></td>
                            <td>
                                <?= formatPrice($regularPrice) ?>
                                <?php if ($regularPrice < (float)$p['price']): ?>
                                    <small style="display: block; color: var(--text-secondary);">Gốc: <?= formatPrice($p['price']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <input type="number" name="items[<?= (int)$p['id'] ?>][discount_price]" class="form-control" style="width: 130px; padding: 6px 10px;" placeholder="Ví dụ: 1500000" min="1" max="<?= max(1, (int)$regularPrice - 1) ?>">
                            </td>
                            <td>
                                <input type="number" name="items[<?= (int)$p['id'] ?>][allocation_quantity]" class="form-control" style="width: 100px; padding: 6px 10px;" value="10" min="1">
                            </td>
                            <td>
                                <input type="number" name="items[<?= (int)$p['id'] ?>][limit_per_user]" class="form-control" style="width: 80px; padding: 6px 10px;" value="2" min="1">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 30px;">
            <button type="submit" class="btn"><i class="fa-solid fa-floppy-disk"></i> Lưu chiến dịch</button>
            <a href="<?= url('admin/flash-sales') ?>" class="btn btn--secondary">Quay lại</a>
        </div>
    </form>
</div>

<script>
function filterFlashSaleProducts() {
    const q = (document.getElementById('fsProductSearchInput')?.value || '').toLowerCase().trim();
    const filterSelected = document.getElementById('fsFilterSelected')?.checked || false;
    const rows = document.querySelectorAll('#fsProductTable tbody tr');
    
    rows.forEach(tr => {
        const text = tr.innerText.toLowerCase();
        const isChecked = tr.querySelector('input[type="checkbox"]')?.checked;
        
        const matchSearch = (q === '' || text.includes(q));
        const matchSelected = (!filterSelected || isChecked);
        
        tr.style.display = (matchSearch && matchSelected) ? '' : 'none';
    });
}
</script>

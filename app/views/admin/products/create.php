<div class="card" style="max-width: 800px; margin: 0 auto 30px;">
    <h3 class="card-title">Thêm sản phẩm mới</h3>
    
    <form method="post" action="<?= url('admin/products/store') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="name">Tên sản phẩm <span style="color: red;">*</span></label>
                <input type="text" name="name" id="name" class="form-control" placeholder="Ví dụ: Asus ROG Zephyrus G16" required>
            </div>

            <div class="form-group">
                <label for="slug">Slug (Tự sinh nếu để trống)</label>
                <input type="text" name="slug" id="slug" class="form-control" placeholder="Ví dụ: asus-rog-zephyrus-g16">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="category_id">Danh mục sản phẩm <span style="color: red;">*</span></label>
                <select name="category_id" id="category_id" class="form-control" required>
                    <option value="">Chọn danh mục</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>"><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="brand_id">Thương hiệu <span style="color: red;">*</span></label>
                <select name="brand_id" id="brand_id" class="form-control" required>
                    <option value="">Chọn thương hiệu</option>
                    <?php foreach ($brands as $b): ?>
                        <option value="<?= (int)$b['id'] ?>"><?= e($b['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="price">Giá bán gốc (đ) <span style="color: red;">*</span></label>
                <input type="number" name="price" id="price" class="form-control" placeholder="Ví dụ: 35000000" min="0" required>
            </div>

            <div class="form-group">
                <label for="sale_price">Giá khuyến mãi (đ - Không bắt buộc)</label>
                <input type="number" name="sale_price" id="sale_price" class="form-control" placeholder="Ví dụ: 32000000" min="0">
            </div>

            <div class="form-group">
                <label for="stock">Số lượng tồn kho <span style="color: red;">*</span></label>
                <input type="number" name="stock" id="stock" class="form-control" value="10" min="0" required>
            </div>
        </div>

        <div class="form-group" style="border: 1px dashed var(--border); padding: 15px; border-radius: 8px; background-color: #F9FAFB;">
            <label for="image">Ảnh đại diện sản phẩm <span style="color: red;">*</span></label>
            <input type="file" name="image" id="image" class="form-control" style="border: none; padding: 5px 0;" required>
            <small style="color: var(--text-secondary); display: block; margin-top: 5px;">Chọn ảnh JPG, PNG hoặc WebP. Kích thước tối đa 5MB.</small>
        </div>

        <div class="form-group">
            <label for="specs">Thông số kỹ thuật (Định dạng JSON)</label>
            <textarea name="specs" id="specs" class="form-control" rows="4" placeholder='Ví dụ: {"CPU": "i7-13700H", "RAM": "16GB", "SSD": "512GB", "VGA": "RTX 4060"}'></textarea>
            <small style="color: var(--text-secondary); display: block; margin-top: 2px;">Vui lòng nhập đúng cú pháp JSON hợp lệ để so sánh sản phẩm hoạt động.</small>
        </div>

        <div class="form-group">
            <label for="description">Mô tả chi tiết sản phẩm</label>
            <textarea name="description" id="description" class="form-control" rows="6" placeholder="Mô tả các đặc điểm nổi bật của sản phẩm..."></textarea>
        </div>

        <div class="form-group">
            <label for="status">Trạng thái hiển thị</label>
            <select name="status" id="status" class="form-control">
                <option value="active">Hiển thị bán hàng (Active)</option>
                <option value="inactive">Tạm ẩn bán hàng (Inactive)</option>
            </select>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 25px;">
            <button type="submit" class="btn"><i class="fa-solid fa-floppy-disk"></i> Lưu lại</button>
            <a href="<?= url('admin/products') ?>" class="btn btn--secondary">Quay lại</a>
        </div>
    </form>
</div>

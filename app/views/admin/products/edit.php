<div class="card" style="max-width: 800px; margin: 0 auto 30px;">
    <h3 class="card-title">Chỉnh sửa sản phẩm</h3>
    
    <form method="post" action="<?= url('admin/products/update/' . $product['id']) ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="current_image" value="<?= e($product['image']) ?>">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="name">Tên sản phẩm <span style="color: red;">*</span></label>
                <input type="text" name="name" id="name" class="form-control" value="<?= e($product['name']) ?>" required>
            </div>

            <div class="form-group">
                <label for="slug">Slug (Tự sinh nếu để trống)</label>
                <input type="text" name="slug" id="slug" class="form-control" value="<?= e($product['slug']) ?>">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="category_id">Danh mục sản phẩm <span style="color: red;">*</span></label>
                <select name="category_id" id="category_id" class="form-control" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>" <?= $product['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="brand_id">Thương hiệu <span style="color: red;">*</span></label>
                <select name="brand_id" id="brand_id" class="form-control" required>
                    <?php foreach ($brands as $b): ?>
                        <option value="<?= (int)$b['id'] ?>" <?= $product['brand_id'] == $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="price">Giá bán gốc (đ) <span style="color: red;">*</span></label>
                <input type="number" name="price" id="price" class="form-control" value="<?= (float)$product['price'] ?>" min="0" required>
            </div>

            <div class="form-group">
                <label for="sale_price">Giá khuyến mãi (đ)</label>
                <input type="number" name="sale_price" id="sale_price" class="form-control" value="<?= $product['sale_price'] !== null ? (float)$product['sale_price'] : '' ?>" min="0">
            </div>

            <div class="form-group">
                <label for="stock">Số lượng tồn kho <span style="color: red;">*</span></label>
                <input type="number" name="stock" id="stock" class="form-control" value="<?= (int)$product['stock'] ?>" min="0" required>
            </div>
        </div>

        <div class="form-group" style="border: 1px dashed var(--border); padding: 15px; border-radius: 8px; background-color: #F9FAFB;">
            <label for="image">Ảnh sản phẩm</label>
            
            <?php if (!empty($product['image'])): ?>
                <div style="margin-bottom: 15px;">
                    <span style="font-size: 12px; color: var(--text-secondary); display: block; margin-bottom: 5px;">Ảnh hiện tại:</span>
                    <img src="<?= url('assets/images/' . e($product['image'])) ?>" alt="<?= e($product['name']) ?>" style="height: 80px; width: 80px; object-fit: contain; border: 1px solid var(--border); padding: 4px; border-radius: 4px; background: #FFF;">
                </div>
            <?php endif; ?>

            <input type="file" name="image" id="image" class="form-control" style="border: none; padding: 5px 0;">
            <small style="color: var(--text-secondary); display: block; margin-top: 5px;">Chọn ảnh để thay thế ảnh cũ.</small>
        </div>

        <div class="form-group">
            <label for="specs">Thông số kỹ thuật (Định dạng JSON)</label>
            <textarea name="specs" id="specs" class="form-control" rows="4"><?= e($product['specs']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="description">Mô tả chi tiết sản phẩm</label>
            <textarea name="description" id="description" class="form-control" rows="6"><?= e($product['description']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="status">Trạng thái hiển thị</label>
            <select name="status" id="status" class="form-control">
                <option value="active" <?= $product['status'] === 'active' ? 'selected' : '' ?>>Hiển thị bán hàng (Active)</option>
                <option value="inactive" <?= $product['status'] === 'inactive' ? 'selected' : '' ?>>Tạm ẩn bán hàng (Inactive)</option>
            </select>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 25px;">
            <button type="submit" class="btn"><i class="fa-solid fa-floppy-disk"></i> Cập nhật</button>
            <a href="<?= url('admin/products') ?>" class="btn btn--secondary">Quay lại</a>
        </div>
    </form>
</div>

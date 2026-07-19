<div class="card" style="max-width: 600px; margin: 0 auto 30px;">
    <h3 class="card-title">Chỉnh sửa danh mục</h3>
    
    <form method="post" action="<?= url('admin/categories/update/' . $category['id']) ?>">
        <?= csrf_field() ?>
        
        <div class="form-group">
            <label for="name">Tên danh mục <span style="color: red;">*</span></label>
            <input type="text" name="name" id="name" class="form-control" value="<?= e($category['name']) ?>" required>
        </div>

        <div class="form-group">
            <label for="slug">Slug (Đường dẫn tĩnh - Tự sinh nếu để trống)</label>
            <input type="text" name="slug" id="slug" class="form-control" value="<?= e($category['slug']) ?>">
        </div>

        <div class="form-group">
            <label for="description">Mô tả danh mục</label>
            <textarea name="description" id="description" class="form-control" rows="4"><?= e($category['description']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="sort_order">Thứ tự sắp xếp hiển thị</label>
            <input type="number" name="sort_order" id="sort_order" class="form-control" value="<?= (int)$category['sort_order'] ?>" min="0">
        </div>

        <div class="form-group">
            <label for="status">Trạng thái hoạt động</label>
            <select name="status" id="status" class="form-control">
                <option value="active" <?= $category['status'] === 'active' ? 'selected' : '' ?>>Kích hoạt (Hiển thị storefront)</option>
                <option value="inactive" <?= $category['status'] === 'inactive' ? 'selected' : '' ?>>Tạm ngưng (Ẩn khỏi storefront)</option>
            </select>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 25px;">
            <button type="submit" class="btn"><i class="fa-solid fa-floppy-disk"></i> Cập nhật</button>
            <a href="<?= url('admin/categories') ?>" class="btn btn--secondary">Quay lại</a>
        </div>
    </form>
</div>

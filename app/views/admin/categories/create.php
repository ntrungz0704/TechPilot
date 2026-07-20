<div class="card" style="max-width: 600px; margin: 0 auto 30px;">
    <h3 class="card-title">Thêm danh mục mới</h3>
    
    <form method="post" action="<?= url('admin/categories/store') ?>">
        <?= csrf_field() ?>
        
        <div class="form-group">
            <label for="name">Tên danh mục <span style="color: red;">*</span></label>
            <input type="text" name="name" id="name" class="form-control" placeholder="Ví dụ: Laptop Gaming" required>
        </div>

        <div class="form-group">
            <label for="slug">Slug (Đường dẫn tĩnh - Tự sinh nếu để trống)</label>
            <input type="text" name="slug" id="slug" class="form-control" placeholder="Ví dụ: laptop-gaming">
        </div>

        <div class="form-group">
            <label for="description">Mô tả danh mục</label>
            <textarea name="description" id="description" class="form-control" rows="4" placeholder="Nhập một vài dòng mô tả ngắn về danh mục này..."></textarea>
        </div>

        <div class="form-group">
            <label for="parent_id">Danh mục cha</label>
            <select name="parent_id" id="parent_id" class="form-control">
                <option value="">-- Là danh mục gốc (Không có cha) --</option>
                <?php foreach ($categories ?? [] as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="icon">Icon class (VD: fa-solid fa-laptop)</label>
            <input type="text" name="icon" id="icon" class="form-control" placeholder="fa-solid fa-tag">
        </div>

        <div class="form-group">
            <label for="image">Ảnh danh mục (đường dẫn hoặc tên file)</label>
            <input type="text" name="image" id="image" class="form-control" placeholder="assets/images/categories/laptop.png">
        </div>

        <div class="form-group">
            <label for="sort_order">Thứ tự sắp xếp hiển thị</label>
            <input type="number" name="sort_order" id="sort_order" class="form-control" value="0" min="0">
        </div>

        <div class="form-group">
            <label for="status">Trạng thái hoạt động</label>
            <select name="status" id="status" class="form-control">
                <option value="active">Kích hoạt (Hiển thị storefront)</option>
                <option value="inactive">Tạm ngưng (Ẩn khỏi storefront)</option>
            </select>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 25px;">
            <button type="submit" class="btn"><i class="fa-solid fa-floppy-disk"></i> Lưu lại</button>
            <a href="<?= url('admin/categories') ?>" class="btn btn--secondary">Quay lại</a>
        </div>
    </form>
</div>

<div class="card" style="margin-bottom: 30px;">
    <h3 class="card-title">Chỉnh sửa thương hiệu</h3>
    
    <form method="post" action="<?= url('admin/brands/update/' . $brand['id']) ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="current_logo" value="<?= e($brand['logo']) ?>">
        
        <div class="form-group">
            <label for="name">Tên thương hiệu <span style="color: red;">*</span></label>
            <input type="text" name="name" id="name" class="form-control" value="<?= e($brand['name']) ?>" required>
        </div>

        <div class="form-group">
            <label for="slug">Slug (Tự sinh nếu để trống)</label>
            <input type="text" name="slug" id="slug" class="form-control" value="<?= e($brand['slug']) ?>">
        </div>

        <div class="form-group" style="border: 1px dashed var(--border); padding: 15px; border-radius: 8px; background-color: #F9FAFB;">
            <label for="logo">Thay đổi hình ảnh Logo</label>
            
            <?php if (!empty($brand['logo'])): ?>
                <div style="margin-bottom: 15px;">
                    <span style="font-size: 12px; color: var(--text-secondary); display: block; margin-bottom: 5px;">Logo hiện tại:</span>
                    <img src="<?= url('assets/images/brands/' . e($brand['logo'])) ?>" alt="<?= e($brand['name']) ?>" style="height: 40px; object-fit: contain; border: 1px solid var(--border); padding: 4px; border-radius: 4px; background: #FFF;">
                </div>
            <?php endif; ?>

            <input type="file" name="logo" id="logo" class="form-control" style="border: none; padding: 5px 0;">
            <small style="color: var(--text-secondary); display: block; margin-top: 5px;">Tải lên ảnh mới sẽ thay thế logo cũ.</small>
        </div>

        <div class="form-group">
            <label for="description">Mô tả thương hiệu</label>
            <textarea name="description" id="description" class="form-control" rows="4"><?= e($brand['description']) ?></textarea>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 25px;">
            <button type="submit" class="btn"><i class="fa-solid fa-floppy-disk"></i> Cập nhật</button>
            <a href="<?= url('admin/brands') ?>" class="btn btn--secondary">Quay lại</a>
        </div>
    </form>
</div>

<div class="card" style="margin-bottom: 30px;">
    <h3 class="card-title">Thêm thương hiệu mới</h3>
    
    <form method="post" action="<?= url('admin/brands/store') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        
        <div class="form-group">
            <label for="name">Tên thương hiệu <span style="color: red;">*</span></label>
            <input type="text" name="name" id="name" class="form-control" placeholder="Ví dụ: Apple" required>
        </div>

        <div class="form-group">
            <label for="slug">Slug (Tự sinh nếu để trống)</label>
            <input type="text" name="slug" id="slug" class="form-control" placeholder="Ví dụ: apple">
        </div>

        <div class="form-group" style="border: 1px dashed var(--border); padding: 15px; border-radius: 8px; background-color: #F9FAFB;">
            <label for="logo">Hình ảnh Logo thương hiệu</label>
            <input type="file" name="logo" id="logo" class="form-control" style="border: none; padding: 5px 0;">
            <small style="color: var(--text-secondary); display: block; margin-top: 5px;">Chọn ảnh PNG, JPG hoặc WebP. Dung lượng tối đa 5MB.</small>
            
            <div style="margin-top: 15px; border-top: 1px solid var(--border); padding-top: 10px;">
                <label for="logo_text">Hoặc nhập thủ công đường dẫn ảnh (Nếu không tải tệp)</label>
                <input type="text" name="logo_text" id="logo_text" class="form-control" placeholder="Ví dụ: brands/apple.svg">
            </div>
        </div>

        <div class="form-group">
            <label for="description">Mô tả thương hiệu</label>
            <textarea name="description" id="description" class="form-control" rows="4" placeholder="Nhập một vài dòng mô tả ngắn về thương hiệu này..."></textarea>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 25px;">
            <button type="submit" class="btn"><i class="fa-solid fa-floppy-disk"></i> Lưu lại</button>
            <a href="<?= url('admin/brands') ?>" class="btn btn--secondary">Quay lại</a>
        </div>
    </form>
</div>

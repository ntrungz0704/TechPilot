<div class="card" style="margin-bottom: 30px;">
    <h3 class="card-title">Chỉnh sửa Banner quảng cáo</h3>
    
    <form method="post" action="<?= url('admin/banners/update/' . $banner['id']) ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="current_image" value="<?= e($banner['image']) ?>">
        
        <div class="form-group">
            <label for="title">Tiêu đề Banner <span style="color: red;">*</span></label>
            <input type="text" name="title" id="title" class="form-control" value="<?= e($banner['title']) ?>" required>
        </div>

        <div class="form-group">
            <label for="link">Đường dẫn Link liên kết (Khi nhấp vào banner)</label>
            <input type="text" name="link" id="link" class="form-control" value="<?= e($banner['link']) ?>">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="type">Vị trí phân loại hiển thị</label>
                <select name="type" id="type" class="form-control">
                    <option value="hero" <?= $banner['type'] === 'hero' ? 'selected' : '' ?>>Hero Slider (Slide trang chủ chính)</option>
                    <option value="hero_sidebar" <?= $banner['type'] === 'hero_sidebar' ? 'selected' : '' ?>>Hero Sidebar (Ảnh nhỏ bên cạnh slide)</option>
                    <option value="mid_banner" <?= $banner['type'] === 'mid_banner' ? 'selected' : '' ?>>Mid Banner (Banner quảng cáo giữa trang)</option>
                    <option value="long_banner" <?= $banner['type'] === 'long_banner' ? 'selected' : '' ?>>Long Banner (Banner dài chân trang)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="position">Thứ tự sắp xếp hiển thị</label>
                <input type="number" name="position" id="position" class="form-control" value="<?= (int)$banner['position'] ?>" min="1">
            </div>
        </div>

        <div class="form-group" style="border: 1px dashed var(--border); padding: 15px; border-radius: 8px; background-color: #F9FAFB;">
            <label for="image">Hình ảnh Banner</label>
            
            <?php if (!empty($banner['image'])): ?>
                <div style="margin-bottom: 15px;">
                    <span style="font-size: 12px; color: var(--text-secondary); display: block; margin-bottom: 5px;">Ảnh hiện tại:</span>
                    <img src="<?= url('assets/images/' . e($banner['image'])) ?>" alt="<?= e($banner['title']) ?>" style="height: 60px; max-width: 100%; object-fit: contain; border: 1px solid var(--border); padding: 4px; border-radius: 4px; background: #FFF;">
                </div>
            <?php endif; ?>

            <input type="file" name="image" id="image" class="form-control" style="border: none; padding: 5px 0;">
            <small style="color: var(--text-secondary); display: block; margin-top: 5px;">Chọn ảnh để thay thế ảnh cũ.</small>
        </div>

        <div class="form-group">
            <label for="status">Trạng thái kích hoạt</label>
            <select name="status" id="status" class="form-control">
                <option value="active" <?= $banner['status'] === 'active' ? 'selected' : '' ?>>Hiển thị (Active)</option>
                <option value="inactive" <?= $banner['status'] === 'inactive' ? 'selected' : '' ?>>Tạm ẩn (Inactive)</option>
            </select>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 25px;">
            <button type="submit" class="btn"><i class="fa-solid fa-floppy-disk"></i> Cập nhật</button>
            <a href="<?= url('admin/banners') ?>" class="btn btn--secondary">Quay lại</a>
        </div>
    </form>
</div>

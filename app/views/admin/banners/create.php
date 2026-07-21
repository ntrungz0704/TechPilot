<div class="card" style="margin-bottom: 30px;">
    <h3 class="card-title">Thêm banner quảng cáo mới</h3>
    
    <form method="post" action="<?= url('admin/banners/store') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        
        <div class="form-group">
            <label for="title">Tiêu đề Banner <span style="color: red;">*</span></label>
            <input type="text" name="title" id="title" class="form-control" placeholder="Ví dụ: Siêu Sale Laptop Gaming Asus ROG" required>
        </div>

        <div class="form-group">
            <label for="link">Đường dẫn Link liên kết (Khi nhấp vào banner)</label>
            <input type="text" name="link" id="link" class="form-control" value="#">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="type">Vị trí phân loại hiển thị</label>
                <select name="type" id="type" class="form-control">
                    <option value="hero">Hero Slider (Slide trang chủ chính)</option>
                    <option value="hero_sidebar">Hero Sidebar (Ảnh nhỏ bên cạnh slide)</option>
                    <option value="mid_banner">Mid Banner (Banner quảng cáo giữa trang)</option>
                    <option value="long_banner">Long Banner (Banner dài chân trang)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="position">Thứ tự sắp xếp hiển thị</label>
                <input type="number" name="position" id="position" class="form-control" value="1" min="1">
            </div>
        </div>

        <div class="form-group" style="border: 1px dashed var(--border); padding: 15px; border-radius: 8px; background-color: #F9FAFB;">
            <label for="image">Hình ảnh Banner <span style="color: red;">*</span></label>
            <input type="file" name="image" id="image" class="form-control" style="border: none; padding: 5px 0;">
            <small style="color: var(--text-secondary); display: block; margin-top: 5px;">Chọn ảnh JPG, PNG hoặc WebP. Dung lượng tối đa 5MB.</small>
            
            <div style="margin-top: 15px; border-top: 1px solid var(--border); padding-top: 10px;">
                <label for="image_text">Hoặc nhập thủ công đường dẫn ảnh (Nếu không tải tệp)</label>
                <input type="text" name="image_text" id="image_text" class="form-control" placeholder="Ví dụ: banners/banner_hero_1.png">
            </div>
        </div>

        <div class="form-group">
            <label for="status">Trạng thái kích hoạt</label>
            <select name="status" id="status" class="form-control">
                <option value="active">Hiển thị (Active)</option>
                <option value="inactive">Tạm ẩn (Inactive)</option>
            </select>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 25px;">
            <button type="submit" class="btn"><i class="fa-solid fa-floppy-disk"></i> Lưu banner</button>
            <a href="<?= url('admin/banners') ?>" class="btn btn--secondary">Quay lại</a>
        </div>
    </form>
</div>

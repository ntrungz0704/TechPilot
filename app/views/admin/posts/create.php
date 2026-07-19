<div class="card" style="max-width: 800px; margin: 0 auto 30px;">
    <h3 class="card-title">Viết bài viết mới</h3>
    
    <form method="post" action="<?= url('admin/posts/store') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        
        <div class="form-group">
            <label for="title">Tiêu đề bài viết <span style="color: red;">*</span></label>
            <input type="text" name="title" id="title" class="form-control" placeholder="Ví dụ: Top 5 laptop văn phòng đáng mua nhất năm 2026" required>
        </div>

        <div class="form-group" style="border: 1px dashed var(--border); padding: 15px; border-radius: 8px; background-color: #F9FAFB;">
            <label for="image">Hình ảnh đại diện bài viết</label>
            <input type="file" name="image" id="image" class="form-control" style="border: none; padding: 5px 0;">
            <small style="color: var(--text-secondary); display: block; margin-top: 5px;">Chọn ảnh JPG, PNG hoặc WebP. Dung lượng tối đa 5MB.</small>
            
            <div style="margin-top: 15px; border-top: 1px solid var(--border); padding-top: 10px;">
                <label for="image_text">Hoặc nhập thủ công đường dẫn ảnh (Nếu không tải tệp)</label>
                <input type="text" name="image_text" id="image_text" class="form-control" placeholder="Ví dụ: posts/post_cover_1.png">
            </div>
        </div>

        <div class="form-group">
            <label for="summary">Tóm tắt ngắn bài viết</label>
            <textarea name="summary" id="summary" class="form-control" rows="3" placeholder="Nhập tóm tắt ngắn khoảng 1-2 dòng hiển thị ở danh sách bài viết..."></textarea>
        </div>

        <div class="form-group">
            <label for="content">Nội dung chi tiết bài viết</label>
            <textarea name="content" id="content" class="form-control" rows="12" placeholder="Nhập nội dung bài viết bằng HTML hoặc văn bản thường..."></textarea>
        </div>

        <div class="form-group">
            <label for="status">Trạng thái xuất bản</label>
            <select name="status" id="status" class="form-control">
                <option value="published">Đăng ngay (Published)</option>
                <option value="draft">Lưu nháp (Draft)</option>
                <option value="hidden">Tạm ẩn (Hidden)</option>
            </select>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 25px;">
            <button type="submit" class="btn"><i class="fa-solid fa-floppy-disk"></i> Lưu bài viết</button>
            <a href="<?= url('admin/posts') ?>" class="btn btn--secondary">Quay lại</a>
        </div>
    </form>
</div>

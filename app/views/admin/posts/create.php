<div class="card" style="margin-bottom: 30px;">
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
            <label for="content">Nội dung chi tiết bài viết (Hỗ trợ Markdown)</label>
            <textarea name="content" id="content" class="form-control" rows="14" placeholder="Nhập nội dung bài viết bằng Markdown...&#10;Ví dụ:&#10;## 1. Tổng quan sản phẩm&#10;Nội dung mô tả...&#10;&#10;:::info&#10;Thông tin lưu ý quan trọng&#10;:::"></textarea>
            <small style="color: var(--text-secondary); display: block; margin-top: 5px;">Hỗ trợ: ## Heading 2, ### Heading 3, - Danh sách, > Blockquote, | Table |, :::info Callout :::</small>
        </div>

            <div class="form-group mb-3">
                <label>Chuyên mục (Thiết bị)</label>
                <select name="category_slug" class="form-control">
                    <option value="laptop">Laptop</option>
                    <option value="pc-gaming">PC Gaming</option>
                    <option value="pc-linh-kien">PC & Linh kiện</option>
                    <option value="man-hinh">Màn hình</option>
                    <option value="gaming-gear">Gaming Gear</option>
                    <option value="office-gear">Thiết Bị Văn Phòng</option>
                    <option value="networking">Thiết Bị Mạng</option>
                    <option value="ai-cong-nghe-moi">AI & Công nghệ mới</option>
                    <option value="cong-nghe">Công nghệ chung</option>
                </select>
            </div>

            <div class="form-group mb-3">
                <label>Loại nội dung</label>
                <select name="post_type" class="form-control">
                    <option value="news">Ra mắt & Xu hướng</option>
                    <option value="review">Đánh giá & Review</option>
                    <option value="guide">Tư vấn chọn mua</option>
                    <option value="howto">Mẹo hay & Thủ thuật</option>
                    <option value="comparison">So sánh sản phẩm</option>
                </select>
            </div>

            <div class="form-group mb-3">
                <label>Thời gian đọc (phút)</label>
                <input type="number" name="reading_minutes" class="form-control" min="1" max="60" placeholder="VD: 5">
            </div>

            <div class="form-group mb-3">
                <div class="form-check">
                    <input type="checkbox" name="is_featured" value="1" class="form-check-input" id="is_featured">
                    <label class="form-check-label" for="is_featured">Bài viết nổi bật</label>
                </div>
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

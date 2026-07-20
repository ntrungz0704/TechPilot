<div class="card" style="margin-bottom: 30px;">
    <h3 class="card-title">Chỉnh sửa bài viết</h3>
    
    <form method="post" action="<?= url('admin/posts/update/' . $post['id']) ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="current_image" value="<?= e($post['image']) ?>">
        
        <div class="form-group">
            <label for="title">Tiêu đề bài viết <span style="color: red;">*</span></label>
            <input type="text" name="title" id="title" class="form-control" value="<?= e($post['title']) ?>" required>
        </div>

        <div class="form-group" style="border: 1px dashed var(--border); padding: 15px; border-radius: 8px; background-color: #F9FAFB;">
            <label for="image">Hình ảnh đại diện bài viết</label>
            
            <?php if (!empty($post['image'])): ?>
                <div style="margin-bottom: 15px;">
                    <span style="font-size: 12px; color: var(--text-secondary); display: block; margin-bottom: 5px;">Ảnh hiện tại:</span>
                    <img src="<?= postImageUrl($post['image']) ?>" alt="<?= e($post['title']) ?>" style="height: 80px; width: 150px; object-fit: cover; border: 1px solid var(--border); padding: 4px; border-radius: 4px; background: #FFF;">
                </div>
            <?php endif; ?>

            <input type="file" name="image" id="image" class="form-control" style="border: none; padding: 5px 0;">
            <small style="color: var(--text-secondary); display: block; margin-top: 5px;">Chọn ảnh để thay thế ảnh đại diện cũ.</small>
        </div>

        <div class="form-group">
            <label for="summary">Tóm tắt ngắn bài viết</label>
            <textarea name="summary" id="summary" class="form-control" rows="3"><?= e($post['summary']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="content">Nội dung chi tiết bài viết</label>
            <textarea name="content" id="content" class="form-control" rows="12"><?= e($post['content']) ?></textarea>
        </div>

        <div class="form-group mb-3">
            <label>Chuyên mục</label>
            <select name="category_slug" class="form-control">
                <option value="cong-nghe" <?= ($post['category_slug'] ?? '') == 'cong-nghe' ? 'selected' : '' ?>>Công nghệ</option>
                <option value="laptop" <?= ($post['category_slug'] ?? '') == 'laptop' ? 'selected' : '' ?>>Laptop</option>
                <option value="gaming" <?= ($post['category_slug'] ?? '') == 'gaming' ? 'selected' : '' ?>>PC Gaming</option>
                <option value="pc-linh-kien" <?= ($post['category_slug'] ?? '') == 'pc-linh-kien' ? 'selected' : '' ?>>Linh kiện PC</option>
            </select>
        </div>

        <div class="form-group mb-3">
            <label>Loại bài viết</label>
            <select name="post_type" class="form-control">
                <option value="news" <?= ($post['post_type'] ?? '') == 'news' ? 'selected' : '' ?>>Tin tức</option>
                <option value="review" <?= ($post['post_type'] ?? '') == 'review' ? 'selected' : '' ?>>Đánh giá</option>
                <option value="guide" <?= ($post['post_type'] ?? '') == 'guide' ? 'selected' : '' ?>>Thủ thuật / Tư vấn</option>
                <option value="comparison" <?= ($post['post_type'] ?? '') == 'comparison' ? 'selected' : '' ?>>So sánh</option>
            </select>
        </div>

        <div class="form-group mb-3">
            <label>Thời gian đọc (phút)</label>
            <input type="number" name="reading_minutes" class="form-control" min="1" max="60" value="<?= htmlspecialchars($post['reading_minutes'] ?? '') ?>">
        </div>

        <div class="form-group mb-3">
            <div class="form-check">
                <input type="checkbox" name="is_featured" value="1" class="form-check-input" id="is_featured" <?= (!empty($post['is_featured']) && $post['is_featured']) ? 'checked' : '' ?>>
                <label class="form-check-label" for="is_featured">Bài viết nổi bật</label>
            </div>
        </div>

        <div class="form-group">
            <label for="status">Trạng thái xuất bản</label>
            <select name="status" id="status" class="form-control">
                <option value="published" <?= $post['status'] === 'published' ? 'selected' : '' ?>>Đang hiển thị (Published)</option>
                <option value="draft" <?= $post['status'] === 'draft' ? 'selected' : '' ?>>Lưu nháp (Draft)</option>
                <option value="hidden" <?= $post['status'] === 'hidden' ? 'selected' : '' ?>>Tạm ẩn (Hidden)</option>
            </select>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 25px;">
            <button type="submit" class="btn"><i class="fa-solid fa-floppy-disk"></i> Cập nhật</button>
            <a href="<?= url('admin/posts') ?>" class="btn btn--secondary">Quay lại</a>
        </div>
    </form>
</div>

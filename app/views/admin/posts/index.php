<div class="card">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
        <h3 class="card-title" style="margin-bottom: 0;">Danh sách tin tức & bài viết</h3>
        <a href="<?= url('admin/posts/create') ?>" class="btn"><i class="fa-solid fa-pen-nib"></i> Viết bài viết mới</a>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 60px; text-align: center;">STT</th>
                    <th style="width: 120px;">Ảnh đại diện</th>
                    <th>Tiêu đề bài viết</th>
                    <th>Tác giả</th>
                    <th>Lượt xem</th>
                    <th>Trạng thái</th>
                    <th>Ngày xuất bản</th>
                    <th style="width: 200px; text-align: center;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($posts)): ?>
                    <?php foreach ($posts as $index => $pst): ?>
                        <tr>
                            <td style="text-align: center; font-weight: 600; color: var(--text-secondary);"><?= $index + 1 ?></td>
                            <td>
                                <?php if (!empty($pst['image'])): ?>
                                    <img src="<?= postImageUrl($pst['image']) ?>" alt="<?= e($pst['title']) ?>" style="width: 100px; height: 50px; object-fit: cover; border: 1px solid var(--border); border-radius: 4px;">
                                <?php else: ?>
                                    <div style="width: 100px; height: 50px; background: #F3F4F6; display: flex; align-items: center; justify-content: center; font-size: 11px; color: #9CA3AF;">No image</div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= e($pst['title']) ?></strong></td>
                            <td><?= e($pst['author_name'] ?? 'Hệ thống') ?></td>
                            <td><?= number_format($pst['views']) ?> views</td>
                            <td style="text-align: center;">
                                <?php $isPub = (($pst['status'] ?? 'published') === 'published'); ?>
                                <label class="toggle-switch" title="Bật/Tắt xuất bản bài viết">
                                    <input type="checkbox" 
                                           class="post-status-toggle" 
                                           data-post-id="<?= (int)$pst['id'] ?>" 
                                           <?= $isPub ? 'checked' : '' ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <div style="font-size: 11px; margin-top: 4px; font-weight: 600; color: <?= $isPub ? '#10B981' : '#6B7280' ?>;" id="statusText_<?= (int)$pst['id'] ?>">
                                    <?= $isPub ? 'Đã đăng' : 'Ẩn' ?>
                                </div>
                            </td>
                            <?php $pubDate = !empty($pst['published_at']) ? $pst['published_at'] : (($pst['status'] === 'published') ? ($pst['created_at'] ?? null) : null); ?>
                            <td><?= !empty($pubDate) ? date('d/m/Y H:i', strtotime((string)$pubDate)) : 'Chưa xuất bản' ?></td>
                            <td style="text-align: center;">
                                <a href="<?= url('admin/posts/edit/' . $pst['id']) ?>" class="btn btn--outline btn--sm" style="padding: 6px 12px; font-size: 12px; white-space: nowrap;"><i class="fa-solid fa-pen-to-square"></i> Sửa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-secondary); padding: 30px;">Chưa có bài viết nào được tạo.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggles = document.querySelectorAll('.post-status-toggle');
    const csrfToken = '<?= csrf_token() ?>';

    toggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const postId = this.dataset.postId;
            const isChecked = this.checked;
            const statusTextEl = document.getElementById('statusText_' + postId);
            
            const formData = new FormData();
            formData.append('_csrf', csrfToken);

            fetch('<?= url("admin/posts/toggle-status/") ?>' + postId, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': csrfToken
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (statusTextEl) {
                        statusTextEl.textContent = data.status_label;
                        statusTextEl.style.color = (data.new_status === 'published') ? '#10B981' : '#6B7280';
                    }
                } else {
                    alert(data.message || 'Có lỗi xảy ra.');
                    this.checked = !isChecked;
                }
            })
            .catch(err => {
                console.error(err);
                alert('Không thể kết nối máy chủ.');
                this.checked = !isChecked;
            });
        });
    });
});
</script>

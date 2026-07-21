<div class="card">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
        <h3 class="card-title" style="margin-bottom: 0;">Danh sách tin tức & bài viết</h3>
        <a href="<?= url('admin/posts/create') ?>" class="btn"><i class="fa-solid fa-pen-nib"></i> Viết bài viết mới</a>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 80px;">ID</th>
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
                    <?php foreach ($posts as $pst): ?>
                        <tr>
                            <td><?= (int)$pst['id'] ?></td>
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
                            <td>
                                <?php
                                $statusClass = 'badge--warning';
                                $statusLabel = 'Nháp';
                                if ($pst['status'] === 'published') { $statusClass = 'badge--success'; $statusLabel = 'Đã đăng'; }
                                if ($pst['status'] === 'hidden') { $statusClass = 'badge--danger'; $statusLabel = 'Ẩn'; }
                                ?>
                                <span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                            </td>
                            <td><?= !empty($pst['published_at']) ? date('d/m/Y H:i', strtotime((string)$pst['published_at'])) : 'Chưa xuất bản' ?></td>
                            <td style="text-align: center;">
                                <div style="display: flex; gap: 8px; justify-content: center; align-items: center; min-height: 38px; flex-wrap: wrap;">
                                    <a href="<?= url('admin/posts/edit/' . $pst['id']) ?>" class="btn btn--outline btn--sm" style="padding: 6px 12px; font-size: 12px; white-space: nowrap;"><i class="fa-solid fa-pen-to-square"></i> Sửa</a>
                                    
                                    <form method="post" action="<?= url('admin/posts/delete/' . $pst['id']) ?>" onsubmit="return confirm('Bạn có chắc chắn muốn xoá bài viết này không?');" style="margin: 0;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn--danger btn--sm" style="padding: 6px 12px; font-size: 12px; white-space: nowrap;"><i class="fa-solid fa-trash-can"></i> Xoá</button>
                                    </form>
                                </div>
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

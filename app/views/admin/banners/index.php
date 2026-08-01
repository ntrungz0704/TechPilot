<div class="card">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
        <h3 class="card-title" style="margin-bottom: 0;">Quản lý Banner quảng cáo & Marketing</h3>
        <a href="<?= url('admin/banners/create') ?>" class="btn"><i class="fa-solid fa-plus"></i> Thêm banner mới</a>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 60px; text-align: center;">STT</th>
                    <th style="width: 160px;">Hình ảnh</th>
                    <th>Tiêu đề banner</th>
                    <th>Vị trí hiển thị (Type)</th>
                    <th>Thứ tự sắp xếp</th>
                    <th>Đường dẫn Link</th>
                    <th>Trạng thái</th>
                    <th style="width: 200px; text-align: center;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($banners)): ?>
                    <?php foreach ($banners as $index => $bn): ?>
                        <tr>
                            <td style="text-align: center; font-weight: 600; color: var(--text-secondary);"><?= $index + 1 ?></td>
                            <td>
                                <img src="<?= bannerImageUrl($bn['image'] ?? '') ?>" alt="<?= e($bn['title']) ?>" style="width: 140px; height: 55px; object-fit: cover; border: 1px solid var(--border); border-radius: 4px; padding: 2px; background: #FFF;">
                            </td>
                            <td><strong><?= e($bn['title']) ?></strong></td>
                            <td>
                                <span class="badge" style="background-color: #E0F2FE; color: #0369A1; font-weight: 700;">
                                    <?= e($bn['type']) ?>
                                </span>
                            </td>
                            <td><?= (int)$bn['position'] ?></td>
                            <td><code><?= e($bn['link']) ?></code></td>
                            <td>
                                <span class="badge <?= $bn['status'] === 'active' ? 'badge--success' : 'badge--danger' ?>">
                                    <?= $bn['status'] === 'active' ? 'Hiển thị' : 'Ẩn' ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: flex; gap: 8px; justify-content: center; align-items: center; min-height: 38px; flex-wrap: wrap;">
                                    <a href="<?= url('admin/banners/edit/' . $bn['id']) ?>" class="btn btn--outline btn--sm" style="padding: 6px 12px; font-size: 12px; white-space: nowrap;"><i class="fa-solid fa-pen-to-square"></i> Sửa</a>
                                    
                                    <form method="post" action="<?= url('admin/banners/delete/' . $bn['id']) ?>" onsubmit="return confirm('Bạn có chắc chắn muốn xoá banner này không?');" style="margin: 0;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn--danger btn--sm" style="padding: 6px 12px; font-size: 12px; white-space: nowrap;"><i class="fa-solid fa-trash-can"></i> Xoá</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-secondary); padding: 30px;">Chưa có banner nào được tạo.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

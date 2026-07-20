<div class="card">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
        <h3 class="card-title" style="margin-bottom: 0;">Danh sách thương hiệu</h3>
        <a href="<?= url('admin/brands/create') ?>" class="btn"><i class="fa-solid fa-plus"></i> Thêm thương hiệu mới</a>
    </div>

    <!-- Search Form -->
    <form method="get" action="<?= url('admin/brands') ?>" style="margin-bottom: 20px; display: flex; gap: 10px;">
        <input type="text" name="search" class="form-control" placeholder="Tìm thương hiệu theo tên..." value="<?= e($search) ?>" style="max-width: 300px;">
        <button type="submit" class="btn btn--outline"><i class="fa-solid fa-magnifying-glass"></i> Tìm kiếm</button>
        <?php if ($search !== ''): ?>
            <a href="<?= url('admin/brands') ?>" class="btn btn--secondary">Xoá lọc</a>
        <?php endif; ?>
    </form>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 80px;">ID</th>
                    <th style="width: 120px;">Logo</th>
                    <th>Tên thương hiệu</th>
                    <th>Slug</th>
                    <th>Mô tả</th>
                    <th>Số sản phẩm</th>
                    <th style="width: 200px; text-align: center;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($brands)): ?>
                    <?php foreach ($brands as $b): ?>
                        <tr>
                            <td><?= (int)$b['id'] ?></td>
                            <td>
                                <?php if (!empty($b['logo'])): ?>
                                    <img src="<?= url('assets/images/brands/' . e($b['logo'])) ?>" alt="<?= e($b['name']) ?>" style="height: 36px; max-width: 100px; object-fit: contain; display: block;">
                                <?php else: ?>
                                    <span style="color: #9CA3AF; font-size: 12px; font-style: italic;">Không có logo</span>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= e($b['name']) ?></strong></td>
                            <td><code><?= e($b['slug']) ?></code></td>
                            <td><span style="font-size: 13px; color: var(--text-secondary); display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden;"><?= e($b['description']) ?></span></td>
                            <td><span class="badge badge--success" style="background-color: #E0F2FE; color: #0369A1;"><?= (int)$b['product_count'] ?> sản phẩm</span></td>
                            <td style="text-align: center; display: flex; gap: 8px; justify-content: center; align-items: center;">
                                <a href="<?= url('admin/brands/edit/' . $b['id']) ?>" class="btn btn--outline btn--sm" style="padding: 6px 12px; font-size: 12px;"><i class="fa-solid fa-pen-to-square"></i> Sửa</a>
                                
                                <form method="post" action="<?= url('admin/brands/delete/' . $b['id']) ?>" onsubmit="return confirm('Bạn có chắc chắn muốn xoá thương hiệu này không?');" style="margin: 0;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn--danger btn--sm" style="padding: 6px 12px; font-size: 12px;"><i class="fa-solid fa-trash-can"></i> Xoá</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-secondary); padding: 30px;">Không tìm thấy thương hiệu nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

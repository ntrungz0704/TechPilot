<div class="card">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
        <h3 class="card-title" style="margin-bottom: 0;">Danh sách danh mục sản phẩm</h3>
        <a href="<?= url('admin/categories/create') ?>" class="btn"><i class="fa-solid fa-plus"></i> Thêm danh mục mới</a>
    </div>

    <!-- Search Form -->
    <form method="get" action="<?= url('admin/categories') ?>" style="margin-bottom: 20px; display: flex; gap: 10px;">
        <input type="text" name="search" class="form-control" placeholder="Tìm danh mục theo tên..." value="<?= e($search) ?>" style="max-width: 300px;">
        <button type="submit" class="btn btn--outline"><i class="fa-solid fa-magnifying-glass"></i> Tìm kiếm</button>
        <?php if ($search !== ''): ?>
            <a href="<?= url('admin/categories') ?>" class="btn btn--secondary">Xoá lọc</a>
        <?php endif; ?>
    </form>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 60px; text-align: center;">STT</th>
                    <th>Tên danh mục</th>
                    <th>Slug</th>
                    <th>Thứ tự</th>
                    <th>Số mẫu</th>
                    <th>Tổng tồn kho</th>
                    <th>Trạng thái</th>
                    <th style="width: 180px; text-align: center;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $index => $cat): ?>
                        <tr>
                            <td style="text-align: center; font-weight: 600; color: var(--text-secondary);"><?= $index + 1 ?></td>
                            <td><strong><?= e($cat['name']) ?></strong></td>
                            <td><code><?= e($cat['slug']) ?></code></td>
                            <td><?= (int)$cat['sort_order'] ?></td>
                            <td><span class="badge" style="background-color: #F3E8FF; color: #6B21A8; font-weight: 700;"><?= (int)($cat['product_models'] ?? $cat['product_count'] ?? 0) ?> mẫu</span></td>
                            <td><span class="badge" style="background-color: #E0F2FE; color: #0369A1; font-weight: 700;"><?= number_format((int)($cat['inventory_units'] ?? 0)) ?> đơn vị</span></td>
                            <td>
                                <span class="badge <?= $cat['status'] === 'active' ? 'badge--success' : 'badge--danger' ?>">
                                    <?= $cat['status'] === 'active' ? 'Đang hoạt động' : 'Tạm khoá' ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: flex; gap: 8px; justify-content: center; align-items: center; min-height: 38px; flex-wrap: wrap;">
                                    <a href="<?= url('admin/categories/edit/' . $cat['id']) ?>" class="btn btn--outline btn--sm" style="padding: 6px 12px; font-size: 12px; white-space: nowrap;"><i class="fa-solid fa-pen-to-square"></i> Sửa</a>
                                    
                                    <form method="post" action="<?= url('admin/categories/delete/' . $cat['id']) ?>" onsubmit="return confirm('Bạn có chắc chắn muốn xoá danh mục này không?');" style="margin: 0;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn--danger btn--sm" style="padding: 6px 12px; font-size: 12px; white-space: nowrap;"><i class="fa-solid fa-trash-can"></i> Xoá</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-secondary); padding: 30px;">Không tìm thấy danh mục nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

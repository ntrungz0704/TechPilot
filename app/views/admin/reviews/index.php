<div class="card">
    <h3 class="card-title">Quản lý và kiểm duyệt đánh giá khách hàng</h3>

    <!-- Filters & Search Form -->
    <form method="get" action="<?= url('admin/reviews') ?>" style="margin-bottom: 25px; display: flex; gap: 15px; flex-wrap: wrap;">
        <input type="text" name="search" class="form-control" placeholder="Tìm theo tên khách, nội dung..." value="<?= e($search) ?>" style="max-width: 300px;">
        
        <select name="rating" class="form-control" style="max-width: 150px;">
            <option value="">Tất cả số sao</option>
            <option value="5" <?= $rating === '5' ? 'selected' : '' ?>>⭐⭐⭐⭐⭐ 5 Sao</option>
            <option value="4" <?= $rating === '4' ? 'selected' : '' ?>>⭐⭐⭐⭐ 4 Sao</option>
            <option value="3" <?= $rating === '3' ? 'selected' : '' ?>>⭐⭐⭐ 3 Sao</option>
            <option value="2" <?= $rating === '2' ? 'selected' : '' ?>>⭐⭐ 2 Sao</option>
            <option value="1" <?= $rating === '1' ? 'selected' : '' ?>>⭐ 1 Sao</option>
        </select>

        <select name="status" class="form-control" style="max-width: 180px;">
            <option value="">Tất cả trạng thái</option>
            <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Được hiển thị (Approved)</option>
            <option value="hidden" <?= $status === 'hidden' ? 'selected' : '' ?>>Đã ẩn (Hidden)</option>
        </select>

        <button type="submit" class="btn btn--outline"><i class="fa-solid fa-filter"></i> Lọc đánh giá</button>
        
        <?php if ($search !== '' || $rating !== '' || $status !== ''): ?>
            <a href="<?= url('admin/reviews') ?>" class="btn btn--secondary">Xoá lọc</a>
        <?php endif; ?>
    </form>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 80px;">ID</th>
                    <th>Sản phẩm</th>
                    <th>Người đánh giá</th>
                    <th style="width: 120px;">Điểm số</th>
                    <th>Nội dung bình luận</th>
                    <th>Trạng thái</th>
                    <th>Ngày đăng</th>
                    <th style="width: 220px; text-align: center;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($reviews)): ?>
                    <?php foreach ($reviews as $rev): ?>
                        <tr>
                            <td><?= (int)$rev['id'] ?></td>
                            <td>
                                <strong><a href="<?= url('product/detail/' . e($rev['product_slug'])) ?>" target="_blank" style="color: var(--primary); text-decoration: none; display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; font-size: 13.5px;"><?= e($rev['product_name']) ?></a></strong>
                            </td>
                            <td><?= e($rev['reviewer_name']) ?></td>
                            <td>
                                <div style="color: #F59E0B; font-weight: 700; font-size: 13px;">
                                    <?= str_repeat('★', (int)$rev['rating']) . str_repeat('☆', 5 - (int)$rev['rating']) ?>
                                </div>
                            </td>
                            <td>
                                <p style="font-size: 13.5px; line-height: 1.5; color: var(--text-primary); margin: 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; max-width: 350px;">
                                    <?= e($rev['comment']) ?>
                                </p>
                            </td>
                            <td>
                                <span class="badge <?= $rev['status'] === 'approved' ? 'badge--success' : 'badge--danger' ?>">
                                    <?= $rev['status'] === 'approved' ? 'Đã duyệt' : 'Đang ẩn' ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($rev['created_at'])) ?></td>
                            <td style="text-align: center; display: flex; gap: 8px; justify-content: center; align-items: center; height: 50px;">
                                <?php if ($rev['status'] !== 'approved'): ?>
                                    <form method="post" action="<?= url('admin/reviews/approve/' . $rev['id']) ?>" style="margin: 0;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn--sm" style="padding: 6px 12px; font-size: 12px; background-color: #10B981;"><i class="fa-solid fa-check"></i> Phê duyệt</button>
                                    </form>
                                <?php else: ?>
                                    <form method="post" action="<?= url('admin/reviews/hide/' . $rev['id']) ?>" style="margin: 0;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn--danger btn--sm" style="padding: 6px 12px; font-size: 12px;"><i class="fa-solid fa-eye-slash"></i> Ẩn đánh giá</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-secondary); padding: 30px;">Không tìm thấy đánh giá nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div style="display: flex; justify-content: center; gap: 8px; margin-top: 25px;">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php
                $query = $_GET;
                $query['page'] = $i;
                $pageUrl = url('admin/reviews?' . http_build_query($query));
                ?>
                <a href="<?= $pageUrl ?>" class="btn <?= $page === $i ? '' : 'btn--outline' ?>" style="padding: 6px 12px; font-size: 13px;"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

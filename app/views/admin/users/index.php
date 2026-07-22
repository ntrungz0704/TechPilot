<div class="card">
    <h3 class="card-title">Quản lý người dùng & phân quyền</h3>

    <!-- Filters & Search Form -->
    <form method="get" action="<?= url('admin/users') ?>" style="margin-bottom: 25px; display: flex; gap: 15px; flex-wrap: wrap;">
        <input type="text" name="search" class="form-control" placeholder="Tìm theo tên, email, sđt..." value="<?= e($search) ?>" style="max-width: 300px;">
        
        <select name="role" class="form-control" style="max-width: 180px;">
            <option value="">Tất cả chức vụ</option>
            <option value="admin" <?= ($roleFilter ?? '') === 'admin' ? 'selected' : '' ?>>Quản trị viên (Admin)</option>
            <option value="customer" <?= ($roleFilter ?? '') === 'customer' ? 'selected' : '' ?>>Khách hàng (Customer)</option>
        </select>

        <button type="submit" class="btn btn--outline"><i class="fa-solid fa-filter"></i> Lọc tài khoản</button>
        
        <?php if ($search !== '' || ($roleFilter ?? '') !== ''): ?>
            <a href="<?= url('admin/users') ?>" class="btn btn--secondary">Xoá lọc</a>
        <?php endif; ?>
    </form>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 80px;">ID</th>
                    <th>Họ và tên</th>
                    <th>Email</th>
                    <th>Số điện thoại</th>
                    <th>Địa chỉ</th>
                    <th>Vai trò</th>
                    <th>Trạng thái</th>
                    <th>Ngày đăng ký</th>
                    <th style="width: 250px; text-align: center;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= (int)$u['id'] ?></td>
                            <td><strong><?= e($u['full_name']) ?></strong></td>
                            <td><?= e($u['email']) ?></td>
                            <td><?= e(formatPhone($u['phone'])) ?></td>
                            <td><span style="font-size: 13px; display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden;"><?= e($u['address'] ?? '') ?></span></td>
                            <td>
                                <span class="badge" style="background-color: <?= $u['role'] === 'admin' ? '#FEE2E2; color: #991B1B;' : '#E0F2FE; color: #0369A1;' ?>">
                                    <?= $u['role'] === 'admin' ? 'Admin' : 'Customer' ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= $u['status'] === 'active' ? 'badge--success' : 'badge--danger' ?>">
                                    <?= $u['status'] === 'active' ? 'Hoạt động' : 'Đang khoá' ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($u['created_at'])) ?></td>
                            <td style="text-align: center;">
                                <div style="display: flex; gap: 8px; justify-content: center; align-items: center; min-height: 38px; flex-wrap: wrap;">
                                    <!-- Toggle status button -->
                                    <form method="post" action="<?= url('admin/users/toggle_status/' . $u['id']) ?>" style="margin: 0;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn--sm <?= $u['status'] === 'active' ? 'btn--danger' : 'btn--outline' ?>" style="padding: 6px 12px; font-size: 12px; white-space: nowrap;">
                                            <i class="fa-solid <?= $u['status'] === 'active' ? 'fa-user-slash' : 'fa-user-check' ?>"></i> 
                                            <?= $u['status'] === 'active' ? 'Khoá' : 'Mở khoá' ?>
                                        </button>
                                    </form>

                                    <!-- Change role button -->
                                    <form method="post" action="<?= url('admin/users/change_role/' . $u['id']) ?>" style="margin: 0; display: flex; gap: 4px; align-items: center;">
                                        <?= csrf_field() ?>
                                        <select name="role" style="padding: 6px; border: 1px solid var(--border); border-radius: 4px; font-size: 12px; font-weight: 600; background-color: var(--bg-card); color: var(--text-primary); outline: none;">
                                            <option value="customer" <?= $u['role'] === 'customer' ? 'selected' : '' ?>>Customer</option>
                                            <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                        </select>
                                        <button type="submit" class="btn btn--outline btn--sm" style="padding: 6px 8px; font-size: 11px;" title="Cập nhật quyền"><i class="fa-solid fa-check"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align: center; color: var(--text-secondary); padding: 30px;">Không tìm thấy người dùng nào.</td>
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
                $pageUrl = url('admin/users?' . http_build_query($query));
                ?>
                <a href="<?= $pageUrl ?>" class="btn <?= $page === $i ? '' : 'btn--outline' ?>" style="padding: 6px 12px; font-size: 13px;"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

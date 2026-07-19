<?php

class AdminUserController extends Controller
{
    public function index(): void
    {
        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        $search = trim($_GET['search'] ?? '');
        $roleFilter = trim($_GET['role'] ?? '');

        $users = [];
        $limit = 10;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * $limit;
        $totalUsers = 0;

        if ($db) {
            $sql = 'SELECT * FROM users WHERE 1=1';
            $countSql = 'SELECT COUNT(*) FROM users WHERE 1=1';
            $params = [];

            if ($search !== '') {
                $sql .= ' AND (full_name LIKE :search OR email LIKE :search OR phone LIKE :search)';
                $countSql .= ' AND (full_name LIKE :search OR email LIKE :search OR phone LIKE :search)';
                $params[':search'] = '%' . $search . '%';
            }

            if ($roleFilter !== '') {
                $sql .= ' AND role = :role';
                $countSql .= ' AND role = :role';
                $params[':role'] = $roleFilter;
            }

            $countStmt = $db->prepare($countSql);
            $countStmt->execute($params);
            $totalUsers = (int)$countStmt->fetchColumn();

            $sql .= ' ORDER BY id DESC LIMIT :limit OFFSET :offset';
            $stmt = $db->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $totalPages = ceil($totalUsers / $limit);

        $this->renderAdmin('admin/users/index', [
            'pageTitle'  => 'Quản lý khách hàng & tài khoản',
            'activeMenu' => 'users',
            'users'      => $users,
            'search'     => $search,
            'roleFilter' => $roleFilter,
            'page'       => $page,
            'totalPages' => $totalPages,
            'totalUsers' => $totalUsers
        ]);
    }

    public function toggleStatus(string $id = ''): void
    {
        $id = (int)$id;
        if (!$this->isPost()) {
            $this->redirect('admin/users');
        }

        $currentAdminId = (int)($_SESSION['user']['id'] ?? 0);
        if ($id === $currentAdminId) {
            flash('error', 'Bạn không thể tự khoá tài khoản của chính mình.');
            $this->redirect('admin/users');
            return;
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        if ($db) {
            $stmt = $db->prepare('SELECT status FROM users WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $userStatus = $stmt->fetchColumn();

            if ($userStatus === false) {
                flash('error', 'Tài khoản không tồn tại.');
                $this->redirect('admin/users');
                return;
            }

            $newStatus = $userStatus === 'active' ? 'inactive' : 'active';
            $stmt = $db->prepare('UPDATE users SET status = :status WHERE id = :id');
            if ($stmt->execute([':status' => $newStatus, ':id' => $id])) {
                flash('success', 'Đã thay đổi trạng thái tài khoản thành công!');
            } else {
                flash('error', 'Không thể cập nhật trạng thái tài khoản.');
            }
        }

        $this->redirect('admin/users');
    }

    public function changeRole(string $id = ''): void
    {
        $id = (int)$id;
        if (!$this->isPost()) {
            $this->redirect('admin/users');
        }

        $currentAdminId = (int)($_SESSION['user']['id'] ?? 0);
        if ($id === $currentAdminId) {
            flash('error', 'Bạn không thể tự hạ quyền của chính mình.');
            $this->redirect('admin/users');
            return;
        }

        // Nhận role dạng chuỗi (admin/customer), không dùng role_id
        $newRole = trim($_POST['role'] ?? 'customer');
        if (!in_array($newRole, ['admin', 'customer'])) {
            $newRole = 'customer';
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        if ($db) {
            $stmt = $db->prepare('UPDATE users SET role = :role WHERE id = :id');
            if ($stmt->execute([':role' => $newRole, ':id' => $id])) {
                flash('success', 'Đã cập nhật phân quyền tài khoản thành công!');
            } else {
                flash('error', 'Không thể thay đổi quyền.');
            }
        }

        $this->redirect('admin/users');
    }
}

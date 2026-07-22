<?php
/**
 * Controller cơ sở - các Controller khác sẽ kế thừa lớp này
 */

class Controller
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Auto-login if session is empty but remember_me cookie exists
        if (empty($_SESSION['user']) && !empty($_COOKIE['remember_techpilot'])) {
            $userModel = $this->model('User');
            $user = $userModel->findByRememberToken($_COOKIE['remember_techpilot']);
            if ($user && ($user['status'] ?? 'active') === 'active') {
                session_regenerate_id(true);
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'full_name' => $user['full_name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ];
            }
        }
    }
    /**
     * Render 1 view, có thể bọc trong layout chung (header/footer)
     */
    protected function render(string $view, array $data = [], bool $useLayout = true): void
    {
        extract($data);
        $viewFile = ROOT_PATH . '/app/views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            die('Không tìm thấy view: ' . htmlspecialchars($view));
        }

        if ($useLayout) {
            // Lấy danh mục chung cho header (MVC Standard)
            require_once ROOT_PATH . '/app/services/CategoryMenuService.php';
            try {
                $globalCategoryMenu = CategoryMenuService::getActiveMenuTree();
            } catch (Exception $e) {
                $globalCategoryMenu = []; // Graceful fallback
            }
            
            require_once ROOT_PATH . '/config/database.php';
            $db = Database::getConnection();
            $globalCategories = [];
            if ($db) {
                $globalCategories = $db->query('SELECT * FROM categories WHERE status = "active" ORDER BY sort_order ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);
            }
            
            require ROOT_PATH . '/app/views/layouts/header.php';
            require $viewFile;
            require ROOT_PATH . '/app/views/layouts/footer.php';
        } else {
            require $viewFile;
        }
    }

    protected function model(string $modelName)
    {
        $modelFile = ROOT_PATH . '/app/models/' . $modelName . '.php';
        if (file_exists($modelFile)) {
            require_once $modelFile;
            return new $modelName();
        }
        die('Không tìm thấy Model: ' . htmlspecialchars($modelName));
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
        exit;
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /** Yêu cầu đã đăng nhập (Customer/Admin) */
    protected function requireAuth(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['user'])) {
            $this->redirect('auth/login');
        }
        return $_SESSION['user'];
    }

    /** Yêu cầu quyền Admin (role_id = 1) */
    protected function requireAdmin(): array
    {
        $user = $this->requireAuth();
        if (($user['role'] ?? '') !== 'admin') {
            http_response_code(403);
            die('<h1>403 Forbidden</h1><p>Bạn không có quyền truy cập trang này.</p>');
        }
        return $user;
    }

    /** Render view quản trị bằng cách bọc vào layout admin */
    protected function renderAdmin(string $view, array $data = []): void
    {
        $this->requireAdmin();

        extract($data);
        $viewFile = ROOT_PATH . '/app/views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            die('Không tìm thấy view Admin: ' . htmlspecialchars($view));
        }

        ob_start();
        require $viewFile;
        $adminContent = ob_get_clean();

        require ROOT_PATH . '/app/views/admin/layout.php';
    }
}

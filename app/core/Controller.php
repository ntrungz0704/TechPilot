<?php
/**
 * Controller cơ sở - các Controller khác sẽ kế thừa lớp này
 */

class Controller
{
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
}

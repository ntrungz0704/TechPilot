<?php
/**
 * Router - phân tích URL dạng ?route=controller/action/param1/param2
 * và gọi controller/action tương ứng theo mô hình MVC.
 */

class Router
{
    private array $routes = [];

    /** Đăng ký route GET */
    public function get(string $path, string $handler): void
    {
        $this->routes['GET'][$this->normalizePath($path)] = $handler;
    }

    /** Đăng ký route POST */
    public function post(string $path, string $handler): void
    {
        $this->routes['POST'][$this->normalizePath($path)] = $handler;
    }

    private function normalizePath(string $path): string
    {
        return '/' . trim($path, '/');
    }

    public function dispatch(string $url): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        // Loại bỏ query string nếu có
        $path = '/' . trim(parse_url($url, PHP_URL_PATH), '/');

        // 1. Kiểm tra trong danh sách route đã đăng ký (GET/POST với regex)
        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $routePath => $handler) {
                // Chuyển {id} hoặc {slug} thành regex ([^/]+)
                $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([^/]+)', $routePath);
                $pattern = '#^' . $pattern . '$#';

                if (preg_match($pattern, $path, $matches)) {
                    $params = array_slice($matches, 1);
                    $this->executeHandler($handler, $params);
                    return;
                }
            }
        }

        // Route không khớp -> trả về HTTP 404 qua 404 handler
        $this->trigger404();
    }

    private function executeHandler(string $handler, array $params): void
    {
        list($controllerName, $action) = explode('@', $handler);
        $controllerFile = ROOT_PATH . '/app/controllers/' . $controllerName . '.php';

        if (!file_exists($controllerFile)) {
            $this->trigger404();
            return;
        }

        require_once $controllerFile;
        if (!class_exists($controllerName)) {
            $this->trigger404();
            return;
        }

        $controller = new $controllerName();
        if (!method_exists($controller, $action)) {
            $this->trigger404();
            return;
        }

        call_user_func_array([$controller, $action], $params);
    }

    private function trigger404(): void
    {
        require_once ROOT_PATH . '/app/core/ErrorHandler.php';
        ErrorHandler::renderErrorView(404);
    }
}

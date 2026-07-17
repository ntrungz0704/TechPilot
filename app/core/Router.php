<?php
/**
 * Router - phân tích URL dạng ?route=controller/action/param1/param2
 * và gọi controller/action tương ứng theo mô hình MVC.
 */

class Router
{
    public function dispatch(string $url): void
    {
        $url = trim($url, '/');
        $url = filter_var($url, FILTER_SANITIZE_URL);
        $parts = $url === '' ? [] : explode('/', $url);

        // Mặc định: HomeController@index
        $controllerName = !empty($parts[0]) ? ucfirst($parts[0]) . 'Controller' : 'HomeController';
        $action          = $parts[1] ?? 'index';
        $params          = array_slice($parts, 2);

        $controllerFile = ROOT_PATH . '/app/controllers/' . $controllerName . '.php';

        if (!file_exists($controllerFile)) {
            $controllerName = 'HomeController';
            $action         = 'notFound';
            $controllerFile = ROOT_PATH . '/app/controllers/HomeController.php';
        }

        require_once $controllerFile;

        if (!class_exists($controllerName)) {
            die('Không tìm thấy Controller: ' . htmlspecialchars($controllerName));
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $action)) {
            $action = 'notFound';
        }

        call_user_func_array([$controller, $action], $params);
    }
}

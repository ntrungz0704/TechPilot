<?php

/**
 * Xử lý lỗi và ngoại lệ tập trung (Centralized Error Handler)
 */
class ErrorHandler
{
    public static function register(): void
    {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
    }

    public static function handleError(int $severity, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        // Bỏ qua các cảnh báo deprecated để tránh gián đoạn ứng dụng
        if ($severity === E_DEPRECATED || $severity === E_USER_DEPRECATED) {
            return true;
        }

        throw new ErrorException($message, 0, $severity, $file, $line);
    }

    public static function handleException(Throwable $exception): void
    {
        $code = $exception->getCode();
        $statusCode = ($code >= 400 && $code <= 599) ? (int)$code : 500;

        error_log(sprintf(
            "[%s] Exception (%d): %s in %s on line %d\nStack trace:\n%s",
            date('Y-m-d H:i:s'),
            $statusCode,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        ));

        self::renderErrorView($statusCode, $exception);
    }

    public static function renderErrorView(int $statusCode = 500, ?Throwable $exception = null): void
    {
        if (!headers_sent()) {
            http_response_code($statusCode);
        }

        $viewFile = ROOT_PATH . "/app/views/errors/{$statusCode}.php";
        if (!file_exists($viewFile)) {
            $viewFile = ROOT_PATH . "/app/views/errors/500.php";
        }

        $isDev = (getenv('APP_ENV') ?: 'development') === 'development';
        $errorMessage = $exception ? $exception->getMessage() : 'Đã có lỗi xảy ra.';

        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "<h1>Mã lỗi {$statusCode}</h1><p>Hệ thống tạm thời chưa thể xử lý yêu cầu.</p>";
        }
        exit;
    }
}

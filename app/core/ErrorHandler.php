<?php

/**
 * Xử lý lỗi và ngoại lệ tập trung (Centralized Error Handler)
 */
class ErrorHandler
{
    private static bool $handling = false;

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
        // Recursive guard chống lặp vòng vô tận khi xử lý lỗi
        if (self::$handling) {
            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: text/plain; charset=utf-8');
            }
            echo "HTTP 500 Internal Server Error (Critical Failure)";
            exit;
        }
        self::$handling = true;

        $code = $exception->getCode();
        $statusCode = ($code >= 400 && $code <= 599) ? (int)$code : 500;

        $errorId = substr(md5(uniqid((string)mt_rand(), true)), 0, 8);
        $sanitizedLog = self::sanitizeLogMessage(sprintf(
            "[%s] [ErrorID: %s] Exception (%d): %s in %s on line %d\nStack trace:\n%s",
            date('Y-m-d H:i:s'),
            $errorId,
            $statusCode,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        ));

        @error_log($sanitizedLog);

        self::renderErrorView($statusCode, $exception, $errorId);
    }

    public static function renderErrorView(int $statusCode = 500, ?Throwable $exception = null, string $errorId = ''): void
    {
        if (!headers_sent()) {
            http_response_code($statusCode);
        }

        $appEnv = defined('APP_ENV') ? APP_ENV : 'production';
        $isDev = ($appEnv === 'development');

        $errorMessage = ($isDev && $exception) ? $exception->getMessage() : 'Hệ thống đang gặp sự cố tạm thời.';

        $viewFile = defined('ROOT_PATH') ? ROOT_PATH . "/app/views/errors/{$statusCode}.php" : __DIR__ . "/../views/errors/{$statusCode}.php";

        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            if ($isDev && $exception) {
                echo "<h1>Mã lỗi {$statusCode}</h1><p>" . htmlspecialchars($errorMessage) . "</p>";
            } else {
                echo "<h1>Mã lỗi {$statusCode}</h1><p>Hệ thống tạm thời chưa thể xử lý yêu cầu.</p>";
            }
        }
        exit;
    }

    /**
     * Khử dữ liệu nhạy cảm trước khi ghi log
     */
    private static function sanitizeLogMessage(string $log): string
    {
        $patterns = [
            '/(?:GEMINI_API_KEY|GROQ_API_KEY|QWEN_API_KEY|DB_PASS|VNPAY_HASH_SECRET|api_key|password|secret|token)\s*=\s*[^\s&;]+/i' => '$0=***REDACTED***',
            '/(?:Bearer|sk-[a-zA-Z0-9]+|gsk_[a-zA-Z0-9]+)\s+[^\s&;]+/i' => 'Bearer ***REDACTED***'
        ];
        return (string)preg_replace(array_keys($patterns), array_values($patterns), $log);
    }
}

<?php

class UploadService
{
    private static array $allowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif'
    ];

    private static int $maxSizeBytes = 5242880; // 5MB

    /**
     * Upload hình ảnh an toàn.
     * Trả về tên file đã được ngẫu nhiên hoá lưu trong thư mục hoặc ném ngoại lệ khi có lỗi.
     */
    public static function uploadImage(array $file, string $targetSubDir = ''): string
    {
        // 1. Kiểm tra mã lỗi upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Lỗi tải tệp lên máy chủ (Mã lỗi: ' . $file['error'] . ').');
        }

        // 2. Kiểm tra dung lượng tệp
        if ($file['size'] > self::$maxSizeBytes) {
            throw new Exception('Kích thước tệp vượt quá giới hạn cho phép (Tối đa 5MB).');
        }

        // 3. Kiểm tra MIME type bằng Magic Bytes (finfo)
        $tmpPath = $file['tmp_name'];
        if (!file_exists($tmpPath)) {
            throw new Exception('Tệp tải lên không tồn tại.');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $realMime = finfo_file($finfo, $tmpPath);
        finfo_close($finfo);

        if (!isset(self::$allowedMimeTypes[$realMime])) {
            throw new Exception('Định dạng tệp không hợp lệ. Chỉ chấp nhận JPG, PNG, WEBP, GIF.');
        }

        $extension = self::$allowedMimeTypes[$realMime];

        // 4. Ngẫu nhiên hoá tên file để chặn Path Traversal và ghi đè file cũ
        $randomName = md5(uniqid((string)microtime(true), true)) . '.' . $extension;

        // 5. Xác định thư mục đích an toàn
        $baseUploadDir = ROOT_PATH . '/public/assets/images/';
        if ($targetSubDir !== '') {
            // Chặn path traversal ở tên thư mục con
            $targetSubDir = str_replace(['..', '/', '\\'], '', $targetSubDir);
            $uploadDir = $baseUploadDir . $targetSubDir . '/';
        } else {
            $uploadDir = $baseUploadDir;
        }

        // Tạo thư mục nếu chưa tồn tại
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $destPath = $uploadDir . $randomName;

        // 6. Thực hiện chuyển tệp an toàn
        if (!move_uploaded_file($tmpPath, $destPath)) {
            throw new Exception('Không thể lưu tệp vào thư mục đích.');
        }

        // Trả về đường dẫn tương đối để lưu database (ví dụ: "products/image.png" hoặc "image.png")
        return $targetSubDir !== '' ? $targetSubDir . '/' . $randomName : $randomName;
    }

    /**
     * Delete an uploaded image safely and idempotently.
     * Only deletes files located strictly within public/assets/images/.
     *
     * @param string|null $storedPath Stored image path (e.g. "posts/abc.jpg" or "abc.jpg")
     * @param string $fallbackSubDir Optional sub-directory fallback (e.g. "posts")
     * @return bool True if deleted or already missing, false if path traversal / invalid
     */
    public static function deleteImage(?string $storedPath, string $fallbackSubDir = ''): bool
    {
        if (empty($storedPath)) {
            return true;
        }

        // Standardize slashes and trim
        $storedPath = trim(str_replace('\\', '/', $storedPath));

        // Security check: reject path traversal and absolute paths
        if (str_contains($storedPath, '..') || str_starts_with($storedPath, '/') || preg_match('/^[a-zA-Z]:/', $storedPath)) {
            return false;
        }

        $baseDir = ROOT_PATH . '/public/assets/images';
        $realBaseDir = realpath($baseDir);
        if (!$realBaseDir) {
            return false;
        }
        $realBaseDirNormalized = str_replace('\\', '/', $realBaseDir);

        // Try direct path first
        $candidates = [$baseDir . '/' . $storedPath];

        // If no subdirectory in storedPath and fallbackSubDir is provided, also try fallback candidate
        if (!str_contains($storedPath, '/') && !empty($fallbackSubDir)) {
            $cleanSubDir = str_replace(['..', '/', '\\'], '', $fallbackSubDir);
            $candidates[] = $baseDir . '/' . $cleanSubDir . '/' . $storedPath;
        }

        foreach ($candidates as $candidatePath) {
            $realCandidate = realpath($candidatePath);
            if ($realCandidate) {
                $realCandidateNormalized = str_replace('\\', '/', $realCandidate);
                // Ensure candidate is strictly within base directory
                if (str_starts_with($realCandidateNormalized, $realBaseDirNormalized . '/')) {
                    try {
                        return @unlink($realCandidate);
                    } catch (Throwable $e) {
                        return false;
                    }
                } else {
                    return false; // Traversal or outside base directory
                }
            }
        }

        // File does not exist -> Idempotent success
        return true;
    }
}

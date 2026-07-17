<?php
require_once ROOT_PATH . '/config/database.php';

class Notification
{
    private ?PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** Lấy tất cả thông báo của user */
    public function getByUserId(int $userId): array
    {
        if ($this->db === null) return [];

        $stmt = $this->db->prepare('SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC');
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /** Đếm thông báo chưa đọc */
    public function getUnreadCount(int $userId): int
    {
        if ($this->db === null) return 0;

        $stmt = $this->db->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0');
        $stmt->execute([':user_id' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    /** Đánh dấu tất cả đã đọc */
    public function markAllAsRead(int $userId): bool
    {
        if ($this->db === null) return false;

        $stmt = $this->db->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = :user_id AND is_read = 0');
        return $stmt->execute([':user_id' => $userId]);
    }
}

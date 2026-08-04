<?php

require_once ROOT_PATH . '/app/services/NotificationRepositoryInterface.php';

class PdoNotificationRepository implements NotificationRepositoryInterface
{
    public function getLatest($db, int $adminUserId, int $limit = 10): array
    {
        $stmt = $db->prepare('SELECT id, title, content, is_read, created_at, user_id FROM notifications WHERE user_id = :uid OR user_id = 1 ORDER BY id DESC LIMIT :limit');
        $stmt->bindValue(':uid', $adminUserId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countUnread($db, int $adminUserId): int
    {
        $stmt = $db->prepare('SELECT COUNT(*) FROM notifications WHERE (user_id = :uid OR user_id = 1) AND is_read = 0');
        $stmt->bindValue(':uid', $adminUserId, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function getById($db, int $id, int $adminUserId): ?array
    {
        $stmt = $db->prepare('SELECT * FROM notifications WHERE id = :id AND (user_id = :uid OR user_id = 1)');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':uid', $adminUserId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function markRead($db, int $id, int $adminUserId): bool
    {
        $stmt = $db->prepare('UPDATE notifications SET is_read = 1 WHERE id = :id AND (user_id = :uid OR user_id = 1)');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':uid', $adminUserId, PDO::PARAM_INT);
        if ($stmt->execute()) {
            return $stmt->rowCount() === 1;
        }
        return false;
    }

    public function markAllRead($db, int $adminUserId): bool
    {
        $stmt = $db->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = :uid OR user_id = 1');
        $stmt->bindValue(':uid', $adminUserId, PDO::PARAM_INT);
        return $stmt->execute();
    }
}

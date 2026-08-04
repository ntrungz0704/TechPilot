<?php

interface NotificationRepositoryInterface
{
    public function getLatest($db, int $adminUserId, int $limit = 10): array;
    public function countUnread($db, int $adminUserId): int;
    public function getById($db, int $id, int $adminUserId): ?array;
    public function markRead($db, int $id, int $adminUserId): bool;
    public function markAllRead($db, int $adminUserId): bool;
}

<?php

require_once ROOT_PATH . '/app/services/NotificationRepositoryInterface.php';

class InMemoryNotificationRepository implements NotificationRepositoryInterface
{
    private array $state = [];
    private bool $simulateFailure = false;

    public function __construct(array $initialState = [])
    {
        $this->state = $initialState;
    }

    public function setSimulateFailure(bool $simulateFailure): void
    {
        $this->simulateFailure = $simulateFailure;
    }

    private function checkFailure(): void
    {
        if ($this->simulateFailure) {
            throw new PDOException("Simulated database failure");
        }
    }

    public function getLatest($db, int $adminUserId, int $limit = 10): array
    {
        $this->checkFailure();
        $filtered = array_filter($this->state, function ($item) use ($adminUserId) {
            return $item['user_id'] == $adminUserId || $item['user_id'] == 1;
        });
        usort($filtered, function($a, $b) {
            return $b['id'] <=> $a['id'];
        });
        return array_slice($filtered, 0, $limit);
    }

    public function countUnread($db, int $adminUserId): int
    {
        $this->checkFailure();
        $count = 0;
        foreach ($this->state as $item) {
            if (($item['user_id'] == $adminUserId || $item['user_id'] == 1) && $item['is_read'] == 0) {
                $count++;
            }
        }
        return $count;
    }

    public function getById($db, int $id, int $adminUserId): ?array
    {
        $this->checkFailure();
        foreach ($this->state as $item) {
            if ($item['id'] == $id && ($item['user_id'] == $adminUserId || $item['user_id'] == 1)) {
                return $item;
            }
        }
        return null;
    }

    public function markRead($db, int $id): bool
    {
        $this->checkFailure();
        foreach ($this->state as &$item) {
            if ($item['id'] == $id) {
                $item['is_read'] = 1;
                return true;
            }
        }
        return false;
    }

    public function markAllRead($db, int $adminUserId): bool
    {
        $this->checkFailure();
        $updated = false;
        foreach ($this->state as &$item) {
            if ($item['user_id'] == $adminUserId || $item['user_id'] == 1) {
                $item['is_read'] = 1;
                $updated = true;
            }
        }
        return $updated;
    }
}

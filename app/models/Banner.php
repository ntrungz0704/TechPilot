<?php
require_once ROOT_PATH . '/config/database.php';

class Banner
{
    private ?PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** Lấy danh sách banner theo loại */
    public function getByType(string $type): array
    {
        if ($this->db === null) {
            if ($type === 'hero') {
                return [['id' => 1, 'title' => 'ROG Zephyrus G16', 'image' => 'hero-rog-zephyrus.jpg', 'link' => 'product/detail/asus-rog-zephyrus-g16']];
            }
            if ($type === 'hero_sidebar') {
                return [
                    ['id' => 1, 'title' => 'Build PC theo yêu cầu', 'image' => '#', 'link' => '#'],
                    ['id' => 2, 'title' => 'Trả góp 0%', 'image' => '#', 'link' => '#'],
                    ['id' => 3, 'title' => 'Thu cũ đổi mới', 'image' => '#', 'link' => '#'],
                ];
            }
            return [];
        }

        $stmt = $this->db->prepare('SELECT * FROM banners WHERE type = :type ORDER BY position ASC');
        $stmt->execute([':type' => $type]);
        return $stmt->fetchAll();
    }
}

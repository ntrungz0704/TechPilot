<?php
require_once ROOT_PATH . '/config/database.php';

class Compare
{
    private ?PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** Lấy danh sách sản phẩm cần so sánh từ list IDs */
    public function getProductsByIds(array $ids): array
    {
        if ($this->db === null || empty($ids)) return [];

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare(
            "SELECT p.*, b.name as brand_name, c.name as category_name 
             FROM products p
             LEFT JOIN brands b ON p.brand_id = b.id
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE p.id IN ($placeholders) AND p.status = 'active'"
        );
        $stmt->execute(array_map('intval', $ids));
        return $stmt->fetchAll();
    }
}

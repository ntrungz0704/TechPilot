<?php
require_once ROOT_PATH . '/config/database.php';

class Brand
{
    private ?PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** Lấy toàn bộ thương hiệu */
    public function getAll(): array
    {
        if ($this->db === null) {
            return [
                ['id' => 1, 'name' => 'ASUS', 'slug' => 'asus', 'logo' => 'asus-logo.svg'],
                ['id' => 2, 'name' => 'MSI', 'slug' => 'msi', 'logo' => 'msi-logo.svg'],
                ['id' => 3, 'name' => 'GIGABYTE', 'slug' => 'gigabyte', 'logo' => 'gigabyte-logo.svg'],
                ['id' => 4, 'name' => 'DELL', 'slug' => 'dell', 'logo' => 'dell-logo.svg'],
                ['id' => 5, 'name' => 'HP', 'slug' => 'hp', 'logo' => 'hp-logo.svg'],
                ['id' => 6, 'name' => 'Lenovo', 'slug' => 'lenovo', 'logo' => 'lenovo-logo.svg'],
                ['id' => 7, 'name' => 'Razer', 'slug' => 'razer', 'logo' => 'razer-logo.svg'],
                ['id' => 8, 'name' => 'Corsair', 'slug' => 'corsair', 'logo' => 'corsair-logo.svg'],
                ['id' => 9, 'name' => 'Intel', 'slug' => 'intel', 'logo' => 'intel-logo.svg'],
                ['id' => 10, 'name' => 'AMD', 'slug' => 'amd', 'logo' => 'amd-logo.svg'],
                ['id' => 11, 'name' => 'Samsung', 'slug' => 'samsung', 'logo' => 'samsung-logo.svg'],
            ];
        }

        $stmt = $this->db->query('SELECT * FROM brands ORDER BY id ASC');
        return $stmt->fetchAll();
    }
}

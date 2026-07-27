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
        if ($this->db !== null) {
            try {
                $stmt = $this->db->query('SELECT * FROM brands WHERE status = \'active\' ORDER BY id ASC');
                $res = $stmt->fetchAll();
                if (!empty($res)) {
                    // Chuẩn hóa tên file logo từ DB nếu chứa -logo.png hoặc .png
                    return array_map(function($b) {
                        $slug = $b['slug'] ?? '';
                        if (!empty($slug)) {
                            $b['logo'] = $slug . '.png';
                        }
                        return $b;
                    }, $res);
                }
            } catch (Exception $e) {}
        }

        return [
            ['id' => 1, 'name' => 'ASUS', 'slug' => 'asus', 'logo' => 'asus.png'],
            ['id' => 2, 'name' => 'MSI', 'slug' => 'msi', 'logo' => 'msi.png'],
            ['id' => 3, 'name' => 'GIGABYTE', 'slug' => 'gigabyte', 'logo' => 'gigabyte.png'],
            ['id' => 4, 'name' => 'DELL', 'slug' => 'dell', 'logo' => 'dell.png'],
            ['id' => 5, 'name' => 'HP', 'slug' => 'hp', 'logo' => 'hp.png'],
            ['id' => 6, 'name' => 'Lenovo', 'slug' => 'lenovo', 'logo' => 'lenovo.png'],
            ['id' => 7, 'name' => 'Razer', 'slug' => 'razer', 'logo' => 'razer.png'],
            ['id' => 8, 'name' => 'Corsair', 'slug' => 'corsair', 'logo' => 'corsair.png'],
            ['id' => 9, 'name' => 'Intel', 'slug' => 'intel', 'logo' => 'intel.png'],
            ['id' => 10, 'name' => 'AMD', 'slug' => 'amd', 'logo' => 'amd.png'],
            ['id' => 11, 'name' => 'Samsung', 'slug' => 'samsung', 'logo' => 'samsung.png'],
            ['id' => 12, 'name' => 'Apple', 'slug' => 'apple', 'logo' => 'apple.png'],
            ['id' => 13, 'name' => 'Logitech', 'slug' => 'logitech', 'logo' => 'logitech.png'],
            ['id' => 14, 'name' => 'LG', 'slug' => 'lg', 'logo' => 'lg.png'],
            ['id' => 15, 'name' => 'Acer', 'slug' => 'acer', 'logo' => 'acer.png'],
            ['id' => 16, 'name' => 'Kingston', 'slug' => 'kingston', 'logo' => 'kingston.png'],
        ];
    }
}

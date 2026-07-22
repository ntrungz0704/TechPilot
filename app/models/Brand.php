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
                    // Chuẩn hóa tên file logo từ DB nếu chứa -logo.svg hoặc .png
                    return array_map(function($b) {
                        $slug = $b['slug'] ?? '';
                        if (!empty($slug)) {
                            $b['logo'] = $slug . '.svg';
                        }
                        return $b;
                    }, $res);
                }
            } catch (Exception $e) {}
        }

        return [
            ['id' => 1, 'name' => 'ASUS', 'slug' => 'asus', 'logo' => 'asus.svg'],
            ['id' => 2, 'name' => 'MSI', 'slug' => 'msi', 'logo' => 'msi.svg'],
            ['id' => 3, 'name' => 'GIGABYTE', 'slug' => 'gigabyte', 'logo' => 'gigabyte.svg'],
            ['id' => 4, 'name' => 'DELL', 'slug' => 'dell', 'logo' => 'dell.svg'],
            ['id' => 5, 'name' => 'HP', 'slug' => 'hp', 'logo' => 'hp.svg'],
            ['id' => 6, 'name' => 'Lenovo', 'slug' => 'lenovo', 'logo' => 'lenovo.svg'],
            ['id' => 7, 'name' => 'Razer', 'slug' => 'razer', 'logo' => 'razer.svg'],
            ['id' => 8, 'name' => 'Corsair', 'slug' => 'corsair', 'logo' => 'corsair.svg'],
            ['id' => 9, 'name' => 'Intel', 'slug' => 'intel', 'logo' => 'intel.svg'],
            ['id' => 10, 'name' => 'AMD', 'slug' => 'amd', 'logo' => 'amd.svg'],
            ['id' => 11, 'name' => 'Samsung', 'slug' => 'samsung', 'logo' => 'samsung.svg'],
            ['id' => 12, 'name' => 'Apple', 'slug' => 'apple', 'logo' => 'apple.svg'],
            ['id' => 13, 'name' => 'Logitech', 'slug' => 'logitech', 'logo' => 'logitech.svg'],
            ['id' => 14, 'name' => 'LG', 'slug' => 'lg', 'logo' => 'lg.svg'],
            ['id' => 15, 'name' => 'Acer', 'slug' => 'acer', 'logo' => 'acer.svg'],
            ['id' => 16, 'name' => 'Kingston', 'slug' => 'kingston', 'logo' => 'kingston.svg'],
        ];
    }
}

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
                $stmt = $this->db->query("SELECT * FROM brands WHERE status = 'active' ORDER BY id ASC");
                $res = $stmt->fetchAll();
                if (!empty($res)) {
                    return $res;
                }
            } catch (Exception $e) {}
        }

        $sourcesPath = ROOT_PATH . '/config/brand-logo-sources.php';
        if (file_exists($sourcesPath)) {
            $sources = require $sourcesPath;
            $fallback = [];
            $idCounter = 1;
            foreach ($sources as $slug => $info) {
                $fallback[] = [
                    'id'     => $idCounter++,
                    'name'   => $info['brand_name'] ?? ucfirst($slug),
                    'slug'   => $slug,
                    'logo'   => $info['file'] ?? null,
                    'status' => 'active'
                ];
            }
            return $fallback;
        }

        return [];
    }
}

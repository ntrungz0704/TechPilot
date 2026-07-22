<?php
require_once ROOT_PATH . '/config/database.php';

class Address
{
    private ?PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function allForUser(int $userId): array
    {
        if (!$this->db) return [];
        try {
            $stmt = $this->db->prepare('SELECT * FROM user_addresses WHERE user_id = :user_id ORDER BY is_default DESC, id DESC');
            $stmt->execute([':user_id' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }

    public function findForUser(int $id, int $userId): array|false
    {
        if (!$this->db) return false;
        try {
            $stmt = $this->db->prepare('SELECT * FROM user_addresses WHERE id = :id AND user_id = :user_id LIMIT 1');
            $stmt->execute([':id' => $id, ':user_id' => $userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return false; }
    }

    public function create(int $userId, array $data): bool { return $this->save($userId, null, $data); }

    public function exists(int $userId, string $recipientName, string $phone, string $addressLine): bool
    {
        if (!$this->db) return false;
        try {
            $stmt = $this->db->prepare('SELECT id FROM user_addresses WHERE user_id = :user_id AND recipient_name = :recipient_name AND phone = :phone AND address_line = :address_line LIMIT 1');
            $stmt->execute([':user_id'=>$userId, ':recipient_name'=>$recipientName, ':phone'=>$phone, ':address_line'=>$addressLine]);
            return (bool)$stmt->fetchColumn();
        } catch (PDOException $e) { return false; }
    }

    public function update(int $id, int $userId, array $data): bool
    {
        return $this->findForUser($id, $userId) ? $this->save($userId, $id, $data) : false;
    }

    private function save(int $userId, ?int $id, array $data): bool
    {
        if (!$this->db) return false;
        $this->db->beginTransaction();
        try {
            if (!empty($data['is_default'])) {
                $this->db->prepare('UPDATE user_addresses SET is_default = 0 WHERE user_id = :user_id')->execute([':user_id' => $userId]);
            }
            $params = [':user_id'=>$userId, ':recipient_name'=>$data['recipient_name'], ':phone'=>$data['phone'], ':address_line'=>$data['address_line'], ':ward'=>($data['ward'] ?? '') ?: null, ':district'=>($data['district'] ?? '') ?: null, ':province'=>$data['province'] ?? '', ':is_default'=>!empty($data['is_default']) ? 1 : 0];
            if ($id === null) {
                $sql = 'INSERT INTO user_addresses (user_id, recipient_name, phone, address_line, ward, district, province, is_default) VALUES (:user_id, :recipient_name, :phone, :address_line, :ward, :district, :province, :is_default)';
            } else {
                $sql = 'UPDATE user_addresses SET recipient_name=:recipient_name, phone=:phone, address_line=:address_line, ward=:ward, district=:district, province=:province, is_default=:is_default WHERE id=:id AND user_id=:user_id';
                $params[':id'] = $id;
            }
            $ok = $this->db->prepare($sql)->execute($params);
            $this->db->commit();
            return $ok;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            return false;
        }
    }

    public function delete(int $id, int $userId): bool
    {
        if (!$this->db) return false;
        try {
            $stmt = $this->db->prepare('DELETE FROM user_addresses WHERE id = :id AND user_id = :user_id');
            $stmt->execute([':id'=>$id, ':user_id'=>$userId]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) { return false; }
    }

    public static function formatted(array $address): string
    {
        return implode(', ', array_filter([$address['address_line'] ?? '', $address['ward'] ?? '', $address['district'] ?? '', $address['province'] ?? '']));
    }
}

<?php
require_once ROOT_PATH . '/config/database.php';

class User
{
    private ?PDO $db;
    private bool $useFallback;
    private array $fallbackUsers;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->useFallback = $this->db === null;
        $this->fallbackUsers = [];
    }

    /** Kiểm tra email đã tồn tại chưa */
    public function findByEmail(string $email): array|false
    {
        if ($this->useFallback) {
            foreach ($this->fallbackUsers as $user) {
                if (($user['email'] ?? '') === $email) {
                    return $user;
                }
            }
            return false;
        }

        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        return $stmt->fetch();
    }

    /** Tạo tài khoản mới, trả về true/false */
    public function create(string $fullName, string $email, string $phone, string $password): bool
    {
        if ($this->useFallback) {
            $this->fallbackUsers[] = [
                'id' => count($this->fallbackUsers) + 1,
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'password' => password_hash($password, PASSWORD_DEFAULT),
            ];
            return true;
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare(
            'INSERT INTO users (full_name, email, phone, password) VALUES (:full_name, :email, :phone, :password)'
        );
        return $stmt->execute([
            ':full_name' => $fullName,
            ':email'     => $email,
            ':phone'     => $phone,
            ':password'  => $hashed,
        ]);
    }

    /** Xác thực đăng nhập, trả về mảng user (không có password) hoặc false */
    public function verify(string $email, string $password): array|false
    {
        if ($this->useFallback) {
            $user = $this->findByEmail($email);
            if ($user && password_verify($password, $user['password'])) {
                unset($user['password']);
                return $user;
            }
            return false;
        }

        $user = $this->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']);
            return $user;
        }

        return false;
    }
}

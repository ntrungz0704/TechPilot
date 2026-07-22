<?php
require_once ROOT_PATH . '/config/database.php';

class User
{
    private ?PDO $db;
    private bool $useFallback;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->useFallback = $this->db === null;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['_fallback_users'])) {
            $_SESSION['_fallback_users'] = [
                [
                    'id' => 1,
                    'full_name' => 'TechPilot Admin',
                    'email' => 'admin@techpilot.vn',
                    'phone' => '0901234567',
                    'role' => 'admin',
                    'status' => 'active',
                    'password' => password_hash('12345678', PASSWORD_DEFAULT),
                ],
                [
                    'id' => 2,
                    'full_name' => 'Nguyễn Văn Khách',
                    'email' => 'customer@techpilot.vn',
                    'phone' => '0987654321',
                    'role' => 'customer',
                    'status' => 'active',
                    'password' => password_hash('12345678', PASSWORD_DEFAULT),
                ]
            ];
        }
    }

    /** Kiểm tra email đã tồn tại chưa */
    public function findByEmail(string $email): array|false
    {
        if ($this->useFallback) {
            foreach ($_SESSION['_fallback_users'] as $user) {
                if (strcasecmp($user['email'] ?? '', $email) === 0) {
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
    public function create(string $fullName, string $email, string $phone, string $password, string $role = 'customer'): bool
    {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        if ($this->useFallback) {
            $_SESSION['_fallback_users'][] = [
                'id' => count($_SESSION['_fallback_users']) + 1,
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'role' => $role,
                'status' => 'active',
                'password' => $hashed,
            ];
            return true;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO users (full_name, email, phone, password, role) VALUES (:full_name, :email, :phone, :password, :role)'
        );
        return $stmt->execute([
            ':full_name' => $fullName,
            ':email'     => $email,
            ':phone'     => $phone,
            ':password'  => $hashed,
            ':role'      => $role,
        ]);
    }

    /** Xác thực đăng nhập, trả về mảng user (không có password) hoặc false */
    public function verify(string $email, string $password): array|false
    {
        $user = $this->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            if (($user['status'] ?? 'active') !== 'active') {
                return false;
            }
            unset($user['password']);
            return $user;
        }

        return false;
    }

    /** Lấy thông tin user theo ID */
    public function getById(int $id): array|false
    {
        if ($this->useFallback) {
            foreach ($_SESSION['_fallback_users'] as $user) {
                if ($user['id'] == $id) {
                    unset($user['password']);
                    return $user;
                }
            }
            return false;
        }

        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /** Cập nhật thông tin cơ bản của user */
    public function updateProfile(int $id, string $fullName, string $phone): bool
    {
        if ($this->useFallback) {
            foreach ($_SESSION['_fallback_users'] as &$user) {
                if ($user['id'] == $id) {
                    $user['full_name'] = $fullName;
                    $user['phone'] = $phone;
                    return true;
                }
            }
            return false;
        }

        $stmt = $this->db->prepare('UPDATE users SET full_name = :full_name, phone = :phone WHERE id = :id');
        return $stmt->execute([
            ':full_name' => $fullName,
            ':phone'     => $phone,
            ':id'        => $id
        ]);
    }

    /** Cập nhật mật khẩu mới của user */
    public function updatePassword(int $id, string $newPassword): bool
    {
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);

        if ($this->useFallback) {
            foreach ($_SESSION['_fallback_users'] as &$user) {
                if ($user['id'] == $id) {
                    $user['password'] = $hashed;
                    return true;
                }
            }
            return false;
        }

        $stmt = $this->db->prepare('UPDATE users SET password = :password WHERE id = :id');
        return $stmt->execute([
            ':password' => $hashed,
            ':id'        => $id
        ]);
    }

    /** Cập nhật remember_token */
    public function updateRememberToken(int $id, ?string $token): bool
    {
        if ($this->useFallback) return true;
        $stmt = $this->db->prepare('UPDATE users SET remember_token = :token WHERE id = :id');
        return $stmt->execute([':token' => $token, ':id' => $id]);
    }

    /** Tìm user qua remember_token */
    public function findByRememberToken(string $token): array|false
    {
        if ($this->useFallback) return false;
        $stmt = $this->db->prepare('SELECT * FROM users WHERE remember_token = :token LIMIT 1');
        $stmt->bindValue(':token', $token);
        $stmt->execute();
        return $stmt->fetch();
    }

    /** Lưu reset_token cho email */
    public function setResetToken(string $email, string $token, string $expiry): bool
    {
        if ($this->useFallback) return true;
        $stmt = $this->db->prepare('UPDATE users SET reset_token = :token, reset_token_expiry = :expiry WHERE email = :email');
        return $stmt->execute([':token' => $token, ':expiry' => $expiry, ':email' => $email]);
    }

    /** Tìm user qua reset_token hợp lệ */
    public function findByResetToken(string $token): array|false
    {
        if ($this->useFallback) return false;
        $stmt = $this->db->prepare('SELECT * FROM users WHERE reset_token = :token AND reset_token_expiry > NOW() LIMIT 1');
        $stmt->bindValue(':token', $token);
        $stmt->execute();
        return $stmt->fetch();
    }
}

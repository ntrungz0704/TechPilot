<?php

class AuthController extends Controller
{
    /** Trang đăng nhập: /auth/login */
    public function login(): void
    {
        $errors = [];
        $old = ['email' => ''];

        if ($this->isPost()) {
            $email    = strtolower(trim($_POST['email'] ?? ''));
            $password = $_POST['password'] ?? '';
            $old['email'] = $email;

            if ($email === '' || $password === '') {
                $errors[] = 'Vui lòng nhập đầy đủ Email và Mật khẩu.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email không hợp lệ.';
            }

            if (empty($errors)) {
                $userModel = $this->model('User');
                $user = $userModel->verify($email, $password);

                if ($user) {
                    session_regenerate_id(true);
                    
                    // Lưu session thông tin tối giản an toàn
                    $_SESSION['user'] = [
                        'id' => $user['id'],
                        'full_name' => $user['full_name'],
                        'email' => $user['email'],
                        'role' => $user['role']
                    ];

                    // Xử lý ghi nhớ đăng nhập
                    if (!empty($_POST['remember'])) {
                        $token = bin2hex(random_bytes(32));
                        $userModel->updateRememberToken($user['id'], $token);
                        setcookie('remember_techpilot', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true); // 30 days
                    }

                    // Xử lý redirect an toàn sau đăng nhập
                    $redirect = trim($_GET['redirect'] ?? '');
                    if (!empty($redirect) && str_starts_with($redirect, '/') && !str_contains($redirect, '//')) {
                        $this->redirect($redirect);
                    } else {
                        $this->redirect('/');
                    }
                    return;
                }

                $errors[] = 'Email hoặc mật khẩu không chính xác.';
            }
        }

        $this->render('auth/login', [
            'pageTitle' => 'Đăng nhập',
            'errors'    => $errors,
            'old'       => $old,
        ]);
    }

    /** Trang đăng ký: /auth/register */
    public function register(): void
    {
        $errors = [];
        $old = ['full_name' => '', 'email' => '', 'phone' => ''];

        if ($this->isPost()) {
            $fullName = trim($_POST['full_name'] ?? '');
            $email    = strtolower(trim($_POST['email'] ?? ''));
            $phone    = trim($_POST['phone'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm  = $_POST['confirm_password'] ?? '';

            // Sanitize phone: chỉ giữ số, dấu +, khoảng trắng và gạch nối; reject nếu chứa @ hoặc dài quá 15 ký tự
            if ($phone !== '') {
                if (str_contains($phone, '@') || strlen($phone) > 15 || !preg_match('/^[\d\s\+\-\(\)]+$/', $phone)) {
                    $phone = ''; // xóa giá trị sai, không báo lỗi (field không bắt buộc)
                } else {
                    // Chỉ giữ số và dấu + ở đầu
                    $phone = preg_replace('/[^\d\+]/', '', $phone);
                }
            }

            $old = ['full_name' => $fullName, 'email' => $email, 'phone' => ''];

            if ($fullName === '' || $email === '' || $password === '' || $confirm === '') {
                $errors[] = 'Vui lòng điền đầy đủ các trường bắt buộc.';
            }
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email không hợp lệ.';
            }
            if (strlen($password) > 0 && strlen($password) < 8) {
                $errors[] = 'Mật khẩu phải có ít nhất 8 ký tự.';
            }
            if ($password !== $confirm) {
                $errors[] = 'Mật khẩu nhập lại không khớp.';
            }

            if (empty($errors)) {
                $userModel = $this->model('User');

                if ($userModel->findByEmail($email)) {
                    $errors[] = 'Email này đã được đăng ký. Vui lòng đăng nhập.';
                } else {
                    if ($userModel->create($fullName, $email, $phone, $password)) {
                        flash('success', 'Đăng ký tài khoản thành công! Vui lòng đăng nhập.');
                        $this->redirect('auth/login');
                        return;
                    } else {
                        $errors[] = 'Đăng ký thất bại. Vui lòng liên hệ quản trị viên.';
                    }
                }
            }
        }

        $this->render('auth/register', [
            'pageTitle' => 'Đăng ký',
            'errors'    => $errors,
            'old'       => $old,
        ]);
    }

    /** Đăng xuất */
    public function logout(): void
    {
        if (!empty($_SESSION['user']['id'])) {
            $userModel = $this->model('User');
            $userModel->updateRememberToken($_SESSION['user']['id'], null);
        }
        setcookie('remember_techpilot', '', time() - 3600, '/');
        unset($_SESSION['user']);
        session_destroy();
        session_start(); // regenerate a fresh empty session
        $this->redirect('/');
    }

    /** Quên mật khẩu */
    public function forgot(): void
    {
        $errors = [];
        $message = '';
        if ($this->isPost()) {
            $email = strtolower(trim($_POST['email'] ?? ''));
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Vui lòng nhập một địa chỉ email hợp lệ.';
            } else {
                $userModel = $this->model('User');
                $user = $userModel->findByEmail($email);
                if ($user) {
                    $token = bin2hex(random_bytes(32));
                    $expiry = date('Y-m-d H:i:s', time() + 3600); // 1 hour
                    $userModel->setResetToken($email, $token, $expiry);
                    $resetLink = BASE_URL . '/auth/reset?token=' . $token;
                    // Trong thực tế sẽ gửi email. Ở đây ta flash link trực tiếp để test.
                    $message = 'Một email khôi phục đã được gửi (Giả lập). Vui lòng click vào link này để đặt lại mật khẩu: <br><a href="'.$resetLink.'" style="word-break:break-all;color:var(--primary);">'.$resetLink.'</a>';
                } else {
                    $errors[] = 'Không tìm thấy tài khoản với email này.';
                }
            }
        }

        $this->render('auth/forgot', [
            'pageTitle' => 'Quên mật khẩu',
            'errors' => $errors,
            'message' => $message
        ]);
    }

    /** Đặt lại mật khẩu */
    public function reset(): void
    {
        $token = $_GET['token'] ?? '';
        if (empty($token)) {
            $this->redirect('auth/login');
        }

        $userModel = $this->model('User');
        $user = $userModel->findByResetToken($token);

        if (!$user) {
            flash('error', 'Link khôi phục mật khẩu không hợp lệ hoặc đã hết hạn.');
            $this->redirect('auth/login');
        }

        $errors = [];
        if ($this->isPost()) {
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';
            
            if (empty($password) || strlen($password) < 8) {
                $errors[] = 'Mật khẩu phải có ít nhất 8 ký tự.';
            } elseif ($password !== $confirm) {
                $errors[] = 'Mật khẩu xác nhận không khớp.';
            } else {
                $userModel->updatePassword($user['id'], $password);
                $userModel->setResetToken($user['email'], null, null); // Clear token
                flash('success', 'Đặt lại mật khẩu thành công! Bạn có thể đăng nhập ngay.');
                $this->redirect('auth/login');
            }
        }

        $this->render('auth/reset', [
            'pageTitle' => 'Đặt lại mật khẩu',
            'errors' => $errors,
            'token' => $token
        ]);
    }
}

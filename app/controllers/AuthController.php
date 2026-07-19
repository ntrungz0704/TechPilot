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

            $old = ['full_name' => $fullName, 'email' => $email, 'phone' => $phone];

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
        unset($_SESSION['user']);
        session_destroy();
        session_start(); // regenerate a fresh empty session
        $this->redirect('/');
    }
}

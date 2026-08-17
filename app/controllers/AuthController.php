<?php

class AuthController extends Controller
{
    // =========================================================================
    // ===== Chức năng Đăng nhập Tài khoản (UC02: Đăng nhập / Đăng xuất tài khoản) =====
    // =========================================================================
    /** Trang đăng nhập: /auth/login */
    public function login(): void
    {
        $errors = [];
        $old = ['email' => ''];

        if ($this->isPost()) {
            if (!verifyCsrf()) {
                $errors[] = 'Phiên làm việc hết hạn. Vui lòng tải lại trang.';
            }

            if (empty($errors)) {
                $email    = trim($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';
                $old['email'] = $email;

                if ($email === '' || $password === '') {
                    $errors[] = 'Vui lòng nhập đầy đủ Email/Số điện thoại và Mật khẩu.';
                } else {
                    $isEmail    = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
                    $cleanPhone = preg_replace('/\D/', '', $email);
                    $isPhone    = (strlen($cleanPhone) >= 9 && strlen($cleanPhone) <= 12);

                    if (!$isEmail && !$isPhone) {
                        $errors[] = 'Email hoặc số điện thoại không hợp lệ.';
                    }
                }

                if (empty($errors)) {
                    require_once ROOT_PATH . '/config/database.php';
                    if (Database::getConnection() === null) {
                        throw new RuntimeException('Không thể kết nối cơ sở dữ liệu. Vui lòng kiểm tra cấu hình MySQL và thử lại.', 500);
                    } else {
                        $userModel = $this->model('User');
                        $user = $userModel->verify($email, $password);

                        if ($user) {
                            session_regenerate_id(true);
                        
                            // Lưu session thông tin tối giản an toàn
                            $_SESSION['user'] = [
                                'id' => $user['id'],
                                'full_name' => $user['full_name'],
                                'email' => $user['email'],
                                'phone' => $user['phone'] ?? '',
                                'role' => $user['role']
                            ];

                            // Xử lý ghi nhớ đăng nhập
                            if (!empty($_POST['remember'])) {
                                $token = bin2hex(random_bytes(32));
                                $userModel->updateRememberToken($user['id'], $token);
                                setcookie('remember_techpilot', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true); // 30 days
                            }

                            // Hợp nhất giỏ hàng guest (nếu có)
                            if (($user['role'] ?? '') !== 'admin' && !empty($_SESSION['guest_cart'])) {
                                require_once ROOT_PATH . '/app/services/CartService.php';
                                require_once ROOT_PATH . '/config/database.php';
                                $db = Database::getConnection();
                                if ($db) {
                                    try {
                                        $cartService = new CartService();
                                        $mergeResult = $cartService->mergeGuestCartIntoUser((int)$user['id'], $db);
                                        if ($mergeResult['merged'] > 0) {
                                            flash('success', 'Đã thêm ' . $mergeResult['merged'] . ' sản phẩm từ giỏ hàng tạm.');
                                        }
                                        if ($mergeResult['skipped'] > 0) {
                                            flash('warning', 'Đã bỏ qua ' . $mergeResult['skipped'] . ' sản phẩm không còn hợp lệ hoặc hết hàng.');
                                        }
                                    } catch (Throwable $e) {
                                        error_log('[Cart Merge Error] ' . $e->getMessage());
                                        flash('error', 'Có lỗi xảy ra khi đồng bộ giỏ hàng, vui lòng thử lại sau.');
                                    }
                                }
                            }

                            // Xử lý redirect an toàn sau đăng nhập
                            $redirect = trim($_GET['redirect'] ?? $_POST['redirect'] ?? '');
                            if (!empty($redirect) && str_starts_with($redirect, '/') && !str_contains($redirect, '//')) {
                                $this->redirect(ltrim($redirect, '/'));
                            } elseif (($user['role'] ?? '') === 'admin') {
                                $this->redirect('admin');
                            } else {
                                $this->redirect('');
                            }
                            return;
                        }

                        $errors[] = 'Email hoặc mật khẩu không chính xác.';
                    }
                }
            }
        }

        $this->render('auth/login', [
            'pageTitle' => 'Đăng nhập',
            'errors'    => $errors,
            'old'       => $old,
        ]);
    }
    // =========================================================================
    // ===== Hoàn thành chức năng Đăng nhập Tài khoản (UC02) =====
    // =========================================================================

    // =========================================================================
    // ===== Chức năng Đăng ký Tài khoản mới (UC01: Đăng ký tài khoản mới (Guest -> Customer)) =====
    // =========================================================================
    /** Trang đăng ký: /auth/register */
    public function register(): void
    {
        header('Content-Type: text/html; charset=utf-8');
        $errors = [];
        $old = ['full_name' => '', 'email' => '', 'phone' => ''];

        if ($this->isPost()) {
            if (!verifyCsrf()) {
                $errors[] = 'Phiên làm việc hết hạn. Vui lòng tải lại trang.';
            }

            if (empty($errors)) {
                $fullName = trim($_POST['full_name'] ?? '');
                $email    = strtolower(trim($_POST['email'] ?? ''));
                $phone    = trim($_POST['phone'] ?? '');
                $password = $_POST['password'] ?? '';
                $confirm  = $_POST['confirm_password'] ?? '';

                // Giữ lại đầy đủ dữ liệu người dùng nhập để hiển thị lại trên form
                $old = ['full_name' => $fullName, 'email' => $email, 'phone' => $phone];

                require_once ROOT_PATH . '/config/database.php';
                if (Database::getConnection() === null) {
                    throw new RuntimeException('Không thể kết nối cơ sở dữ liệu. Vui lòng kiểm tra cấu hình MySQL và thử lại.', 500);
                }
                $userModel = $this->model('User');

                // 1. Kiểm tra Họ và tên (Trường 1)
                if ($fullName === '') {
                    $errors[] = 'Vui lòng nhập Họ và tên.';
                }

                // 2. Kiểm tra Email (Trường 2)
                if ($email === '') {
                    $errors[] = 'Vui lòng nhập Địa chỉ Email.';
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = 'Địa chỉ Email không hợp lệ.';
                } else {
                    if ($userModel->findByEmail($email)) {
                        $errors[] = 'Email này đã được sử dụng. Vui lòng sử dụng Email khác hoặc Đăng nhập.';
                    }
                }

                // 3. Kiểm tra Số điện thoại (Trường 3)
                if ($phone !== '') {
                    $digitsOnly = preg_replace('/\D/', '', $phone);
                    if (str_contains($phone, '@') || strlen($digitsOnly) < 9 || strlen($digitsOnly) > 12) {
                        $errors[] = 'Số điện thoại không hợp lệ (phải có từ 9 đến 11 chữ số).';
                    } else {
                        if ($userModel->findByPhone($phone)) {
                            $errors[] = 'Số điện thoại này đã được đăng ký bởi một tài khoản khác.';
                        }
                    }
                }

                // 4. Kiểm tra Mật khẩu (Trường 4)
                if ($password === '') {
                    $errors[] = 'Vui lòng nhập Mật khẩu.';
                } elseif (strlen($password) < 8) {
                    $errors[] = 'Mật khẩu phải có ít nhất 8 ký tự.';
                }

                // 5. Kiểm tra Xác nhận mật khẩu (Trường 5)
                if ($confirm === '') {
                    $errors[] = 'Vui lòng nhập lại Mật khẩu xác nhận.';
                } elseif ($password !== '' && $password !== $confirm) {
                    $errors[] = 'Mật khẩu nhập lại không khớp.';
                }

                if (empty($errors)) {
                    if ($userModel->create($fullName, $email, $phone, $password)) {
                        // Tự động đăng nhập tài khoản vừa tạo
                        $user = $userModel->verify($email, $password);
                        if ($user) {
                            session_regenerate_id(true);
                            $_SESSION['user'] = [
                                'id' => $user['id'],
                                'full_name' => $user['full_name'],
                                'email' => $user['email'],
                                'phone' => $user['phone'] ?? '',
                                'role' => $user['role']
                            ];

                            // Hợp nhất giỏ hàng tạm thời của Guest vào tài khoản mới
                            if (($user['role'] ?? '') !== 'admin' && !empty($_SESSION['guest_cart'])) {
                                require_once ROOT_PATH . '/app/services/CartService.php';
                                $db = Database::getConnection();
                                if ($db) {
                                    try {
                                        $cartService = new CartService();
                                        $mergeResult = $cartService->mergeGuestCartIntoUser((int)$user['id'], $db);
                                        if ($mergeResult['merged'] > 0) {
                                            flash('success', 'Đăng ký thành công! Đã tự động lưu sản phẩm vào giỏ hàng của bạn.');
                                        }
                                    } catch (Throwable $e) {
                                        error_log('[Cart Merge Error] ' . $e->getMessage());
                                    }
                                }
                            } else {
                                flash('success', 'Đăng ký tài khoản thành công!');
                            }

                            $redirect = trim($_GET['redirect'] ?? $_POST['redirect'] ?? '');
                            if (!empty($redirect) && str_starts_with($redirect, '/') && !str_contains($redirect, '//')) {
                                $this->redirect(ltrim($redirect, '/'));
                            } else {
                                $this->redirect('checkout');
                            }
                            return;
                        }

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
    // =========================================================================
    // ===== Hoàn thành chức năng Đăng ký Tài khoản mới (UC01) =====
    // =========================================================================

    // =========================================================================
    // ===== Chức năng Đăng xuất Tài khoản (UC02: Đăng nhập / Đăng xuất tài khoản) =====
    // =========================================================================
    /** Đăng xuất (Chỉ xử lý mutation khi POST) */
    public function logout(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/');
            return;
        }

        if (!empty($_SESSION['user']['id'])) {
            $userModel = $this->model('User');
            $userModel->updateRememberToken($_SESSION['user']['id'], null);
        }
        setcookie('remember_techpilot', '', time() - 3600, '/');
        unset($_SESSION['user']);
        session_destroy();
        session_start(); // regenerate a fresh empty session
        $this->redirect('/');
        return;
    }
    // =========================================================================
    // ===== Hoàn thành chức năng Đăng xuất Tài khoản (UC02) =====
    // =========================================================================

    // =========================================================================
    // ===== Chức năng Quên mật khẩu (UC03: Quên mật khẩu & Đặt lại qua Reset Token) =====
    // =========================================================================
    /** Quên mật khẩu */
    public function forgot(): void
    {
        $errors = [];
        $message = '';
        if ($this->isPost()) {
            if (!verifyCsrf()) {
                $errors[] = 'Phiên làm việc hết hạn. Vui lòng tải lại trang.';
            }

            if (empty($errors)) {
                $email = strtolower(trim($_POST['email'] ?? ''));
                if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = 'Vui lòng nhập một địa chỉ email hợp lệ.';
                } else {
                    try {
                        require_once ROOT_PATH . '/config/database.php';
                        if (Database::getConnection() === null) {
                            http_response_code(503);
                            $errors[] = 'Hệ thống đang tạm thời không thể xử lý yêu cầu. Vui lòng thử lại sau.';
                        } else {
                            $userModel = $this->model('User');
                            $user = $userModel->findByEmail($email);
                            if ($user) {
                                $token = bin2hex(random_bytes(32));
                                $expiry = date('Y-m-d H:i:s', time() + 3600);
                                $userModel->setResetToken($email, $token, $expiry);
                            }
                            $message = 'Nếu địa chỉ email tồn tại trong hệ thống, chúng tôi sẽ gửi hướng dẫn đặt lại mật khẩu.';
                        }
                    } catch (Throwable $e) {
                        error_log('Forgot password error: ' . $e->getMessage());
                        $message = 'Nếu địa chỉ email tồn tại trong hệ thống, chúng tôi sẽ gửi hướng dẫn đặt lại mật khẩu.';
                    }
                }
            }
        }

        $this->render('auth/forgot', [
            'pageTitle' => 'Quên mật khẩu',
            'errors' => $errors,
            'message' => $message
        ]);
    }
    // =========================================================================
    // ===== Hoàn thành chức năng Quên mật khẩu (UC03) =====
    // =========================================================================

    // =========================================================================
    // ===== Chức năng Đặt lại mật khẩu (UC03: Quên mật khẩu & Đặt lại qua Reset Token) =====
    // =========================================================================
    /** Đặt lại mật khẩu */
    public function reset(): void
    {
        $token = $_GET['token'] ?? '';
        if (empty($token)) {
            $this->redirect('auth/login');
            return;
        }

        require_once ROOT_PATH . '/config/database.php';
        if (Database::getConnection() === null) {
            throw new RuntimeException('Không thể kết nối cơ sở dữ liệu. Vui lòng kiểm tra cấu hình MySQL và thử lại.', 500);
        }

        $userModel = $this->model('User');
        $user = $userModel->findByResetToken($token);

        if (!$user) {
            flash('error', 'Link khôi phục mật khẩu không hợp lệ hoặc đã hết hạn.');
            $this->redirect('auth/login');
            return;
        }

        $errors = [];
        if ($this->isPost()) {
            if (!verifyCsrf()) {
                $errors[] = 'Phiên làm việc hết hạn. Vui lòng tải lại trang.';
            }

            if (empty($errors)) {
                $password = $_POST['password'] ?? '';
                $confirm = $_POST['confirm_password'] ?? '';
            
                if (empty($password) || strlen($password) < 8) {
                    $errors[] = 'Mật khẩu phải có ít nhất 8 ký tự.';
                } elseif (!preg_match('/[0-9]/', $password) || !preg_match('/[a-zA-Z]/', $password)) {
                    $errors[] = 'Mật khẩu mới phải bao gồm cả chữ cái và chữ số để đảm bảo an toàn.';
                } elseif ($password !== $confirm) {
                    $errors[] = 'Mật khẩu xác nhận không khớp với mật khẩu mới.';
                } else {
                    $userModel->updatePassword($user['id'], $password);
                    $userModel->setResetToken($user['email'], '', '1970-01-01 00:00:00'); // Invalidate token permanently
                    $userModel->updateRememberToken($user['id'], null); // Revoke old remember tokens
                    flash('success', 'Đặt lại mật khẩu thành công! Bạn có thể đăng nhập ngay bằng mật khẩu mới.');
                    $this->redirect('auth/login');
                    return;
                }
            }
        }

        $this->render('auth/reset', [
            'pageTitle' => 'Đặt lại mật khẩu',
            'errors' => $errors,
            'token' => $token
        ]);
    }
    // =========================================================================
    // ===== Hoàn thành chức năng Đặt lại mật khẩu (UC03) =====
    // =========================================================================
}


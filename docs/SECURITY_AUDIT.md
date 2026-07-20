# Báo cáo Kiểm toán Bảo mật (SECURITY_AUDIT)

Báo cáo chi tiết về tình trạng an toàn thông tin, các lỗ hổng đã quét và các cơ chế bảo vệ được thiết lập trên TechPilot.

## 1. Kết quả kiểm toán các tiêu chí bảo mật

| Tiêu chí bảo mật | Trạng thái | Chi tiết triển khai & Kiểm tra | Kết luận |
|---|---|---|---|
| **SQL Injection (SQLi)** | `PASS` | 100% truy vấn tương tác với CSDL qua PDO đều sử dụng prepared statements với tham số hóa (placeholders). Không có việc ghép chuỗi trực tiếp dữ liệu từ request. | Hợp lệ |
| **Cross-Site Scripting (XSS)** | `PASS` | Toàn bộ dữ liệu hiển thị động ra giao diện HTML đều được escape bằng hàm helper `e()` (sử dụng `htmlspecialchars` với `ENT_QUOTES` và mã hóa UTF-8). | Hợp lệ |
| **CSRF Protection** | `PASS` | Bộ lọc Front Controller tại [public/index.php](file:///d:/TechPilot/public/index.php) kiểm soát toàn bộ các POST request, đối chiếu token gửi lên với `$_SESSION['csrf_token']` bằng hàm so sánh an toàn `hash_equals()`. | Hợp lệ |
| **Password Security** | `PASS` | Mật khẩu người dùng được băm bằng thuật toán `bcrypt` thông qua hàm `password_hash()` với độ phức tạp cao, không bao giờ lưu trữ dạng plain-text. | Hợp lệ |
| **Session Hardening** | `PASS` | Đã cấu hình HttpOnly (chặn đọc cookie từ JS), Lax SameSite (chống CSRF qua link bên ngoài) và tự động bật Secure cookie khi chạy dưới giao thức HTTPS. Thực hiện `session_regenerate_id(true)` ngay sau khi đăng nhập thành công. | Hợp lệ |
| **IDOR Prevention** | `PASS` | Kiểm tra quyền sở hữu đối với các tài nguyên nhạy cảm như giỏ hàng, thông tin profile, lịch sử đơn hàng (`order.user_id = CURRENT_USER_ID`) tại phía Server. | Hợp lệ |
| **Upload Security** | `PASS` | Hệ thống upload ảnh của admin kiểm tra kỹ định dạng MIME qua extension GD/finfo, giới hạn kích thước, đặt tên ngẫu nhiên tránh ghi đè và ngăn chặn upload tệp thực thi PHP. | Hợp lệ |

---

## 2. Các rủi ro/khuyến nghị bổ sung

1. **Gemini API Key**: API key được lưu trữ và đọc trực tiếp từ biến môi trường của hệ thống máy chủ hoặc tệp cấu hình cục bộ không được commit lên Git.
2. **Display Errors**: Trong môi trường phát triển, `display_errors` được bật để dễ debug. Khi triển khai lên môi trường sản xuất (Production), cần cấu hình lại trong file `php.ini` hoặc `config/app.php` tắt `display_errors` và bật `log_errors` để tránh lộ cấu trúc thư mục hoặc lỗi SQL cho người dùng.

# Báo cáo Bảo mật Hệ thống (Security Report)

*   **Thời gian thực hiện**: 2026-07-18
*   **Trạng thái bảo mật**: **SECURE**

Hệ thống TechPilot đã được audit và tăng cường các lớp bảo mật để chống lại các nguy cơ tấn công Web phổ biến.

---

## 1. Phòng chống các lỗ hổng bảo mật chính

### 1.1 Chống giả mạo yêu cầu (CSRF Protection)
*   **Cơ chế**: Toàn bộ POST request thay đổi trạng thái hệ thống đều được lọc và kiểm tra tự động thông qua `public/index.php`.
*   **Xử lý**:
    *   Mỗi session người dùng khởi tạo sẽ được sinh một mã token duy nhất thông qua `$_SESSION['csrf_token']`.
    *   Các form POST trên Views đều được đính kèm trường ẩn `<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">`.
    *   Khi có request POST gửi lên, index.php sẽ tự động đối chiếu giá trị gửi lên với session, nếu không khớp sẽ trả về mã lỗi HTTP `403 Forbidden` và ngắt xử lý.
    *   Hàm đăng xuất (Logout) đã được chuyển hoàn toàn sang phương thức POST để bảo mật an toàn.

### 1.2 Chặn truy cập trái phép đối tượng (IDOR Protection)
*   **Cơ chế**: Kiểm tra và đối chiếu quyền sở hữu dữ liệu trực tiếp trên server đối với từng resource.
*   **Xử lý**:
    *   Trong `WishlistController.php`, `OrderController.php`, `ProfileController.php`, toàn bộ thao tác lấy, sửa hoặc xóa dữ liệu đều được lọc nghiêm ngặt theo ID của tài khoản đang đăng nhập trong Session (`user_id = :session_user_id`).
    *   Khách hàng này không thể xem, hủy đơn hàng hoặc sửa đổi giỏ hàng/yêu thích của khách hàng khác bằng cách thay đổi ID trên URL tham số. Nếu ID đơn hàng không thuộc sở hữu của user_id trong session, hệ thống sẽ trả về lỗi `404` hoặc chuyển hướng an toàn.

### 1.3 Phòng chống tiêm lệnh SQL (SQL Injection Protection)
*   **Cơ chế**: Sử dụng PDO Prepared Statements và tham số hóa cho mọi câu lệnh SQL.
*   **Xử lý**:
    *   Không cộng chuỗi truy vấn trực tiếp trong mã nguồn Model.
    *   Mọi giá trị từ người dùng gửi lên (như ID sản phẩm, slug, email, keyword tìm kiếm) đều được bind thông qua phương thức `bindValue()` hoặc mảng tham số của `PDOStatement::execute()`.
    *   Các tham số phân trang, limit luôn được ép kiểu nguyên (`PDO::PARAM_INT`).

### 1.4 Phòng chống kịch bản tấn công XSS (Cross-Site Scripting)
*   **Cơ chế**: Làm sạch và mã hóa các chuỗi ký tự trước khi hiển thị lên trình duyệt.
*   **Xử lý**:
    *   Sử dụng hàm helper `e()` (escape HTML) thống nhất được định nghĩa tại `app/core/helpers.php` cho toàn bộ các thông tin động in ra view.
    *   Các dữ liệu do khách hàng gửi lên (như comment đánh giá sản phẩm tại `Review.php`) được làm sạch bằng `htmlspecialchars(trim($comment), ENT_QUOTES, 'UTF-8')` ngay trước khi lưu trữ vào MySQL.

### 1.5 Bảo mật mật khẩu & Session
*   **Cơ chế**: Mã hóa mật khẩu một chiều bằng bcrypt và kiểm soát phiên làm việc.
*   **Xử lý**:
    *   Mật khẩu đăng ký của người dùng được mã hóa bằng hàm chuẩn của PHP `password_hash($password, PASSWORD_DEFAULT)`.
    *   Khi đăng nhập, hệ thống đối chiếu qua `password_verify()`.
    *   Gọi `session_regenerate_id(true)` ngay sau khi đăng nhập thành công để ngăn chặn lỗ hổng Session Fixation (chiếm đoạt phiên làm việc).

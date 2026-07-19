# PHASE 1 SECURITY REPORT — BÁO CÁO BẢO MẬT TECHPILOT

Tài liệu này tóm tắt các giải pháp bảo mật cốt lõi đã được áp dụng trong quá trình hoàn thiện mã nguồn TechPilot.

---

## 1. Phòng chống Tấn công Giả mạo Yêu cầu (CSRF Protection)
- **Cơ chế**: Sinh mã token ngẫu nhiên bằng bộ tạo số ngẫu nhiên an toàn mật mã học (`bin2hex(random_bytes(32))`) và lưu trữ trong session người dùng.
- **Triển khai**:
  - Tự động kiểm duyệt mọi request dạng `POST` tại Front Controller ([public/index.php](file:///d:/TechPilot/public/index.php)). Nếu thiếu hoặc sai token, yêu cầu bị huỷ ngay lập tức với mã `HTTP 403`.
  - Tích hợp helper `csrf_field()` để chèn thẻ ẩn `<input type="hidden" name="csrf_token" value="...">` vào toàn bộ các biểu mẫu `POST` trên storefront và Admin panel.
- **Mục tiêu đạt được**: Đảm bảo mọi thay đổi dữ liệu (đặt hàng, viết đánh giá, cập nhật cấu hình Admin) đều bắt nguồn từ chính giao diện TechPilot thật.

---

## 2. Phòng chống XSS (Cross-Site Scripting)
- **Cơ chế**: Thoát (escape) toàn bộ dữ liệu người dùng nhập trước khi lưu trữ hoặc hiển thị ra màn hình.
- **Triển khai**:
  - Tại storefront, helper `e()` (bí danh của `htmlspecialchars`) được dùng cho toàn bộ dữ liệu in ra trình duyệt.
  - Bình luận đánh giá sản phẩm được làm sạch bằng `htmlspecialchars($comment, ENT_QUOTES, 'UTF-8')` ngay tại model [Review.php](file:///d:/TechPilot/app/models/Review.php) trước khi chèn vào MySQL để loại bỏ script độc hại.
- **Mục tiêu đạt được**: Ngăn chặn tin tặc chèn mã Javascript độc vào bình luận để đánh cắp session cookie của khách hàng hoặc Admin.

---

## 3. Bảo mật Upload & Thực thi Tệp tin (Safe File Upload)
- **Cơ chế**: Kết hợp kiểm duyệt loại tệp ở mức nhị phân, thay đổi tên tệp ngẫu nhiên và vô hiệu hoá máy chủ thực thi script.
- **Triển khai**:
  - Sử dụng class `UploadService.php` gọi hàm `finfo_file` để kiểm tra Magic Bytes (MIME type thực tế), chỉ chấp nhận các MIME type ảnh chuẩn (`image/jpeg`, `image/png`, `image/webp`, `image/gif`).
  - Chặn Path Traversal bằng cách loại bỏ các ký tự đặc biệt (`..`, `/`, `\`) trong thư mục con đích và dùng hàm băm MD5 kết hợp microtime sinh tên tệp tin hoàn toàn ngẫu nhiên.
  - Tạo tệp tin cấu hình máy chủ Apache `.htaccess` tại thư mục chứa ảnh upload (`public/assets/images/.htaccess`) để chặn đứng quyền thực thi của các tệp mở rộng script (`.php`, `.phtml`, `.cgi`, `.sh`, v.v.) và tắt flag engine của PHP.
- **Mục tiêu đạt được**: Chặn đứng lỗ hổng RCE (Remote Code Execution) - hacker tải tệp tin PHP shell lên máy chủ rồi truy cập trực tiếp để chiếm quyền kiểm soát hệ thống.

---

## 4. Phòng chống SQL Injection (SQLi)
- **Cơ chế**: Tuyệt đối không cộng chuỗi SQL trực tiếp với biến đầu vào.
- **Triển khai**:
  - Toàn bộ các thao tác truy vấn, cập nhật, xoá trong database đều sử dụng cơ chế Prepared Statements của PDO.
  - Dữ liệu đầu vào từ người dùng được gán gián tiếp thông qua mảng parameters hoặc bind tham số rõ ràng với kiểu dữ liệu an toàn (`PDO::PARAM_INT` cho các biến ID, LIMIT, OFFSET).
- **Mục tiêu đạt được**: Vô hiệu hoá hoàn toàn các cuộc tấn công thay đổi cấu trúc câu truy vấn SQL để lấy trộm hoặc phá huỷ cơ sở dữ liệu.

---

## 5. Chặn lỗi IDOR (Insecure Direct Object Reference)
- **Cơ chế**: Ràng buộc quyền sở hữu thực tế đối với các yêu cầu truy vấn tài nguyên nhạy cảm.
- **Triển khai**:
  - Khi người dùng xem chi tiết hoặc thay đổi đơn hàng tại storefront ([Order.php](file:///d:/TechPilot/app/models/Order.php)), câu truy vấn SELECT lọc đồng thời theo `id` đơn hàng và `user_id` của tài khoản đang đăng nhập.
  - Trực tiếp kiểm duyệt quyền hạn Admin qua helper `requireAdmin()` trước khi thực hiện bất kỳ thao tác kết xuất view hoặc lưu trữ trên Admin CRUD.
- **Mục tiêu đạt được**: Đảm bảo khách hàng thường không thể đọc trộm thông tin đơn hàng hoặc thông tin cá nhân của người khác bằng cách thay đổi ID trên URL.

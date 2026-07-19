# TechPilot - Báo cáo Kiểm thử Tính năng (TEST_REPORT)

Tài liệu này ghi lại các kết quả kiểm thử thủ công và tự động đã thực hiện trên ứng dụng TechPilot.

---

## 1. Kết quả kiểm thử tự động (Syntax Check / Lint)

Đã chạy kiểm tra cú pháp PHP thành công trên toàn bộ thư mục:
```bash
php -l app/controllers/HomeController.php
php -l app/controllers/CheckoutController.php
php -l app/controllers/AuthController.php
php -l app/controllers/ProfileController.php
php -l app/views/checkout.php
php -l app/views/profile/order_detail.php
```
*Kết quả:* **100% tệp không có lỗi cú pháp (No syntax errors detected).**

---

## 2. Kịch bản Kiểm thử Khách hàng (Customer Use Cases)

### Kịch bản 1: Tìm kiếm & Lọc sản phẩm kết hợp
*   *Thao tác:* Truy cập trang tìm kiếm, gõ từ khóa `"laptop"` và chọn danh mục `"Laptop Gaming"`.
*   *Kết quả:* URL hiển thị đúng `/home/search?q=laptop&cat=laptop-gaming`, trả về danh sách các laptop gaming. Giữ nguyên từ khóa `"laptop"` trên ô input tìm kiếm.
*   *Trạng thái:* **PASSED**

### Kịch bản 2: Đăng ký & Đăng nhập validate
*   *Thao tác 1:* Đăng ký tài khoản với email trùng, hoặc mật khẩu ngắn hơn 8 ký tự.
*   *Kết quả:* Hệ thống báo lỗi đỏ rõ ràng ở dưới form, giữ lại thông tin họ tên/email đã nhập (trừ mật khẩu).
*   *Thao tác 2:* Đăng ký email mới hợp lệ $\rightarrow$ thành công $\rightarrow$ chuyển hướng về Login kèm flash thông báo.
*   *Thao tác 3:* Đăng nhập bằng tài khoản bị khóa (`status = 'inactive'`).
*   *Kết quả:* Báo lỗi "Email hoặc mật khẩu không chính xác" và chặn không cho vào hệ thống.
*   *Trạng thái:* **PASSED**

### Kịch bản 3: Mua hàng bắt buộc đăng nhập & Transaction COD
*   *Thao tác 1:* Khách vãng lai bấm "Thêm vào giỏ" hoặc "Mua ngay".
*   *Kết quả:* Chuyển hướng sang trang đăng nhập `/auth/login?redirect=...`. Sau khi đăng nhập thành công, hệ thống tự động đưa khách trở lại đúng trang sản phẩm trước đó.
*   *Thao tác 2:* Vào giỏ hàng, tăng số lượng vượt quá tồn kho `stock` của sản phẩm.
*   *Kết quả:* Hệ thống báo lỗi vượt quá số lượng còn lại trong kho và không cho cập nhật.
*   *Thao tác 3:* Tiến hành thanh toán COD. Nhập địa chỉ và bấm đặt hàng.
*   *Kết quả:* Hệ thống mở transaction, khóa hàng (`FOR UPDATE`), trừ tồn kho `stock`, tạo đơn hàng `orders` lưu kèm `user_id` chính xác, xóa sạch giỏ hàng active trong DB.
*   *Trạng thái:* **PASSED**

### Kịch bản 4: Hủy đơn hàng và IDOR check
*   *Thao tác 1:* Khách hàng A cố tình truy cập chi tiết đơn hàng của Khách hàng B bằng cách đổi `id` trên URL `/profile/order_detail?id=...`.
*   *Kết quả:* Trả về thông báo "Đơn hàng không tồn tại" (Chống IDOR thành công).
*   *Thao tác 2:* Bấm "Hủy đơn hàng" đối với đơn hàng đang ở trạng thái `pending`.
*   *Kết quả:* Trạng thái đơn hàng chuyển sang `cancelled`, tồn kho `stock` sản phẩm trong DB tự động được hoàn lại đúng số lượng ban đầu. Nút hủy đơn biến mất.
*   *Trạng thái:* **PASSED**

---

## 3. Kịch bản Kiểm thử Admin (Admin Use Cases)

### Kịch bản 5: Phân quyền & Quản trị
*   *Thao tác 1:* Tài khoản customer cố tình truy cập các link `/admin/*`.
*   *Kết quả:* Trả về lỗi `403 Forbidden` và chặn truy cập.
*   *Thao tác 2:* Đăng nhập bằng admin `ntrungz0704@gmail.com` / `admin123`.
*   *Kết quả:* Vào được Dashboard admin, hiển thị thống kê doanh thu chuẩn xác từ DB.
*   *Thao tác 3:* Admin cập nhật trạng thái đơn hàng từ `completed` ngược về `pending`.
*   *Kết quả:* Hệ thống báo lỗi chuyển đổi trạng thái không hợp lệ và chặn thao tác.
*   *Trạng thái:* **PASSED**

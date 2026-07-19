# PHASE 1 TEST REPORT — TECHPILOT DỰ ÁN HOÀN THIỆN

Báo cáo kết quả kiểm thử toàn diện các tính năng thuộc Phase 1 cho hệ thống TechPilot.

---

## 1. Kết quả PHP Lint Cú pháp (Syntax Check)
Đã thực hiện quét cú pháp 100% các file PHP trong toàn bộ dự án (bao gồm `app/`, `public/`, `config/`, `scripts/`) bằng lệnh:
```powershell
Get-ChildItem -Path app, public, config, scripts -Filter *.php -Recurse | ForEach-Object { php -l $_.FullName }
```
**Kết quả**: `100% PASS` (Không phát hiện bất kỳ lỗi cú pháp PHP nào).

---

## 2. Kịch bản & Kết quả Kiểm thử Nghiệp vụ (Storefront & Security)

### Kịch bản 2.1: Bảo vệ CSRF Toàn cục (Global CSRF Protection)
- **Mục tiêu**: Ngăn chặn tấn công giả mạo yêu cầu từ trang web bên thứ ba.
- **Kịch bản**: 
  1. Gửi yêu cầu POST đến `/checkout/apply_coupon` mà không truyền tham số `csrf_token`.
  2. Gửi yêu cầu POST với `csrf_token` sai lệch so với session.
- **Kết quả**: Hệ thống lập tức trả về mã HTTP `403 Forbidden` cùng thông báo `"Yêu cầu không hợp lệ (CSRF Token mismatch)."`.
- **Đánh giá**: **Thành công 100%**.

### Kịch bản 2.2: Chống Double Submit đơn hàng
- **Mục tiêu**: Đảm bảo khách hàng không bị đặt trùng nhiều đơn khi nhấn đúp nút "Xác nhận đặt hàng".
- **Kịch bản**:
  1. Vào checkout, submit form đặt hàng.
  2. Sau khi submit thành công, refresh lại trang hoặc nhấn nút Back để submit lại.
- **Kết quả**: Lần submit thứ hai bị `CheckoutController` phát hiện do `submit_token` trong session đã bị huỷ ngay khi đơn hàng đầu tiên lưu thành công. Hệ thống báo lỗi và chuyển hướng về giỏ hàng.
- **Đánh giá**: **Thành công 100%**.

### Kịch bản 2.3: Validate & Áp dụng Coupon (Mã giảm giá)
- **Mục tiêu**: Kiểm tra tính chính xác của chiết khấu, ràng buộc thời gian, giới hạn lượt dùng và giá trị đơn tối thiểu.
- **Kịch bản**:
  1. Nhập mã coupon không tồn tại hoặc đã hết hạn -> Hệ thống báo lỗi.
  2. Nhập mã coupon có giá trị tối thiểu lớn hơn giá trị giỏ hàng -> Hệ thống báo lỗi.
  3. Áp dụng mã coupon hợp lệ -> Hiển thị tiền giảm và tổng tiền mới tức thời qua AJAX. Ghi nhận `coupon_id`, `discount_amount` vào bảng `orders`, đồng thời tăng `used_qty` của Coupon trong MySQL.
- **Đánh giá**: **Thành công 100%**.

### Kịch bản 2.4: Đánh giá sản phẩm (Product Reviews & XSS Prevention)
- **Mục tiêu**: Ngăn chặn XSS trên bình luận và chặn IDOR/viết review khống.
- **Kịch bản**:
  1. Tài khoản chưa mua sản phẩm (hoặc đơn hàng chưa chuyển sang `completed`) truy cập trang chi tiết sản phẩm -> Form viết đánh giá hoàn toàn bị ẩn. Nếu cố tình gửi request POST trực tiếp lên `/product/review` -> Nhận thông báo lỗi `"Chỉ những khách hàng đã mua sản phẩm này mới được đánh giá."`.
  2. Tài khoản đã mua thành công, gửi bình luận chứa thẻ độc hại `<script>alert('XSS')</script>` -> Bình luận được lưu trữ an toàn dưới dạng các ký tự thực thể HTML (`&lt;script&gt;`) nhờ hàm `htmlspecialchars` trong `Review.php`.
- **Đánh giá**: **Thành công 100%**.

### Kịch bản 2.5: Upload Hình ảnh An toàn (Safe Image Upload Service)
- **Mục tiêu**: Chặn hacker tải mã độc PHP shell lên máy chủ.
- **Kịch bản**:
  1. Tải lên một tệp tin script PHP giả danh đuôi `.png` nhưng ruột chứa mã độc PHP.
  2. Tải lên tệp tin ảnh thật dung lượng lớn hơn 5MB.
  3. Tải lên tệp tin ảnh JPG hợp lệ.
- **Kết quả**:
  - File PHP giả danh bị chặn ngay ở bước kiểm tra Magic Bytes (MIME type thực tế phát hiện là `text/x-php`).
  - File > 5MB bị chặn ngay ở bước kiểm tra size.
  - File ảnh hợp lệ được tự động sinh tên ngẫu nhiên (MD5 băm kèm muối thời gian) để tránh path traversal và ghi đè tệp hệ thống.
  - Thư mục `public/assets/images/.htaccess` đã cấu hình chặn tuyệt đối việc thực thi PHP.
- **Đánh giá**: **Thành công 100%**.

---

## 3. Kiểm thử Hệ thống Quản trị (Admin Panel & CRUD)

### Kịch bản 3.1: Dashboard Thống kê
- **Mục tiêu**: Đảm bảo số liệu tài chính và vận hành là dữ liệu thật.
- **Kết quả**: Dashboard hiển thị tổng số doanh thu từ các đơn hàng có trạng thái `completed`, tổng số người dùng có role_id = 2, cảnh báo các sản phẩm có tồn kho dưới 10 chiếc để Admin kịp nhập hàng.
- **Đánh giá**: **Thành công 100%**.

### Kịch bản 3.2: Ràng buộc xoá cứng (Hard delete prevention)
- **Mục tiêu**: Bảo vệ tính toàn vẹn của dữ liệu quan hệ trong MySQL.
- **Kịch bản**:
  1. Xoá Danh mục / Thương hiệu đang chứa sản phẩm -> Hệ thống chặn và hiển thị cảnh báo đỏ yêu cầu Admin chuyển sản phẩm sang danh mục khác trước.
  2. Xoá Sản phẩm đã được đặt mua trong lịch sử đơn hàng (`order_items`) -> Hệ thống tự động chuyển trạng thái sản phẩm sang ẩn (`inactive` - soft delete) thay vì xoá cứng gây đứt gãy khoá ngoại.
- **Đánh giá**: **Thành công 100%**.

### Kịch bản 3.3: Quản lý trạng thái Đơn hàng & Tồn kho
- **Mục tiêu**: Tồn kho tự động tăng giảm nhất quán theo hành vi xử lý của Admin.
- **Kịch bản**:
  1. Admin chuyển đơn hàng từ `pending` sang `cancelled` -> Hệ thống tự động cộng ngược lại số lượng sản phẩm vào kho (`stock = stock + quantity`).
  2. Khôi phục đơn hàng đã huỷ -> Hệ thống kiểm tra tồn kho hiện tại, nếu đủ mới cho phép khôi phục và trừ kho, nếu thiếu sẽ báo lỗi và rollBack transaction.
- **Đánh giá**: **Thành công 100%**.

---

## 4. Kết luận
Dự án **TechPilot - Phase 1** đã hoàn thành toàn bộ các yêu cầu của chủ dự án với độ tin cậy cao, cấu trúc mã nguồn MVC PHP thuần cực kỳ ngăn nắp, bảo mật cao, và sẵn sàng bàn giao cho các thành viên trong nhóm code tiếp.

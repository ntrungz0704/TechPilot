# Checklist chức năng Khách vãng lai và Khách hàng (Customer Feature Checklist)

Dưới đây là danh sách toàn bộ các tính năng dành cho khách vãng lai và khách hàng đã đăng nhập, tích hợp cùng thiết kế ERD 15 bảng và tối ưu hóa Responsive di động.

## 1. Hệ thống Cơ sở dữ liệu (ERD15 Persist)
- [ ] Chạy Audit và phân tích 19 bảng dư thừa (`docs/ERD15_REDUCTION_AUDIT.md`).
- [ ] Di trú dữ liệu:
  - [ ] Di trú `wishlist_items` cũ sang bảng `wishlists` mới liên kết trực tiếp.
  - [ ] Di trú giá giảm `discount_price` trong `flash_sale_items` về trường `products.sale_price` và cờ `is_flash_sale`.
  - [ ] Đồng bộ tồn kho `on_hand` của `inventory_balances` về `products.stock`.
  - [ ] Chuyển vai trò phân quyền `roles` sang `users.role` (string/enum: admin, customer).
  - [ ] Tích hợp thông tin ship/payment của `shipments`, `payments` trực tiếp vào `orders`.
- [ ] Chạy file sql `database/schema.sql` sau khi rút gọn để thiết lập đúng 15 bảng chuẩn và các ràng buộc khóa ngoại, index.
- [ ] Chạy script import data mẫu để có baseline kiểm thử thật.

## 2. Chuẩn hóa MVC & Core Router
- [ ] Kiểm tra bootstrap, router phân biệt GET/POST, mapping các endpoint khách hàng an toàn.
- [ ] Tận dụng helper escape HTML đồng nhất (`e()`) chống XSS cho mọi đầu ra.
- [ ] Đăng ký đầy đủ route cho giỏ hàng, checkout, profile, wishlist, orders, reviews, posts.

## 3. Chức năng Khách vãng lai (Guest)
- [ ] **Trang chủ `/`**: hiển thị động toàn bộ dữ liệu từ 15 bảng (Banner, Flash Sale countdown thật, Best Sellers, Danh mục, Tin tức, Đối tác, Review của khách hàng).
- [ ] **Xem danh sách sản phẩm**: tìm kiếm, lọc (theo danh mục, thương hiệu, khoảng giá, còn hàng), sắp xếp (mới nhất, giá tăng/giảm, nổi bật), phân trang phía server.
- [ ] **Chi tiết sản phẩm**: hiển thị thông tin, thư viện ảnh `product_images`, rating trung bình, nút chọn số lượng, thêm giỏ hàng, danh sách đánh giá đã duyệt, sản phẩm liên quan.
- [ ] **So sánh sản phẩm**: lưu trữ và hiển thị tối đa 4 sản phẩm bằng `$_SESSION['compare']`.
- [ ] **Sản phẩm xem gần đây**: lưu trữ danh sách ID sản phẩm vừa xem bằng session/cookie.
- [ ] **Xem bài viết**: danh sách tin tức và xem chi tiết theo slug, tăng view đếm lượt xem an toàn.

## 4. persist Đăng ký & Đăng nhập (Auth)
- [ ] **Đăng ký tài khoản**: validate email trùng, số điện thoại, mật khẩu băm `password_hash()`, role mặc định `customer`.
- [ ] **Đăng nhập**: xác thực bằng `password_verify()`, chặn tài khoản `inactive`, thực thi `session_regenerate_id(true)` tránh Session Fixation.
- [ ] **Đăng xuất**: thực hiện bằng phương thức POST + bảo mật CSRF.

## 5. Chức năng Khách hàng đã đăng nhập (Customer)
- [ ] **Hồ sơ cá nhân**: xem thông tin, cập nhật họ tên, số điện thoại, email (UNIQUE), đổi mật khẩu (yêu cầu mật khẩu hiện tại).
- [ ] **Yêu thích (Wishlist)**: thêm, xóa, xem danh sách sản phẩm yêu thích (lưu trực tiếp bảng `wishlists`).
- [ ] **Giỏ hàng (Cart)**: lấy/tạo giỏ hàng, thêm sản phẩm, cập nhật số lượng (validate <= stock), xóa item. Tính lại giá toàn bộ ở server.
- [ ] **Mã giảm giá (Coupon)**: áp dụng mã giảm giá, kiểm tra điều kiện thật (hạn dùng, lượt dùng, giá trị tối thiểu) trực tiếp trên server ở cả bước check coupon lẫn checkout.
- [ ] **Thanh toán COD (Checkout)**: thực hiện trong 1 database transaction có khóa bản ghi (`FOR UPDATE`), tính lại tiền, tạo `orders` và `order_items` snapshot, trừ tồn kho, cập nhật lượt dùng coupon, xóa giỏ hàng, rollback nếu có bất kỳ lỗi nào.
- [ ] **Quản lý đơn hàng**: xem danh sách đơn, xem chi tiết, hủy đơn (hoàn lại tồn kho cho sản phẩm).
- [ ] **Viết đánh giá (Review)**: gửi rating, comment cho sản phẩm đã mua (kiểm tra trạng thái đơn completed), escape XSS, chỉ hiển thị review sau khi admin duyệt.

## 6. Giao diện Responsive & Viewport 440×956
- [ ] Tối ưu hóa toàn bộ các trang Storefront để không bị tràn ngang (no horizontal overflow-x).
- [ ] Ẩn các tính năng dư thừa không được hỗ trợ trong ERD15 (chatbot AI, nhiều địa chỉ nhận hàng, đổi trả đơn hàng).
- [ ] Tinh chỉnh font chữ, padding, grid 2 cột sản phẩm trên di động độ rộng 440px.

## 7. Bảo mật & Kiểm thử
- [ ] Bảo mật CSRF cho toàn bộ POST request.
- [ ] Auth guard cho các route cần đăng nhập, ownership check chống IDOR.
- [ ] PHP syntax check (Lint) xanh 100%.
- [ ] Chạy kiểm thử End-to-End toàn bộ quy trình mua hàng COD.

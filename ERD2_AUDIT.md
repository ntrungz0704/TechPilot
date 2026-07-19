# TechPilot - Tài liệu Kiểm toán ERD (ERD_AUDIT)

Tài liệu này xác minh sự đồng bộ giữa cấu trúc các bảng trong Cơ sở dữ liệu thực tế (`database/schema.sql`) và các nghiệp vụ đã triển khai trong mã nguồn TechPilot.

---

## 1. Ánh xạ 15 Bảng Hệ thống Cốt lõi

Hệ thống đã triển khai đầy đủ và chính xác 15 bảng theo đúng thiết kế ERD đã được phê duyệt:

1.  **`users`**: Lưu trữ thông tin tài khoản thành viên (Admin và Customer).
    *   *Tên cột schema:* `id`, `full_name`, `email`, `phone`, `password`, `role` (enum `'admin'`, `'customer'`), `address`, `status` (enum `'active'`, `'inactive'`).
2.  **`categories`**: Lưu trữ danh mục sản phẩm (ví dụ: Laptop Gaming, PC Build Sẵn).
3.  **`brands`**: Lưu trữ thương hiệu sản phẩm (ví dụ: ASUS, Lenovo, Gigabyte).
4.  **`products`**: Thông tin chi tiết sản phẩm.
    *   *Tồn kho:* Quản lý qua cột `stock` (giá trị mặc định = 100).
    *   *Giá bán:* Lưu qua cột `price` (giá bán) và `sale_price` (giá khuyến mãi).
5.  **`product_images`**: Thư viện ảnh chi tiết cho từng sản phẩm.
6.  **`carts`**: Đầu giỏ hàng gắn với `user_id` và có `status` (enum `'active'`, `'converted'`, `'abandoned'`).
7.  **`cart_items`**: Chi tiết sản phẩm trong giỏ hàng và số lượng `quantity`.
8.  **`coupons`**: Mã giảm giá, hỗ trợ loại giảm giá cố định (`fixed`) và phần trăm (`percent`).
9.  **`orders`**: Thông tin đơn hàng (mã đơn, người nhận, số điện thoại, địa chỉ, tổng tiền, trạng thái đơn...).
    *   *Cột trạng thái đơn hàng:* `status` (enum `'pending'`, `'confirmed'`, `'processing'`, `'shipping'`, `'completed'`, `'cancelled'`).
10. **`order_items`**: Lưu trữ chi tiết sản phẩm thuộc đơn hàng, bao gồm giá bán `price` tại thời điểm mua để không bị thay đổi lịch sử khi giá sản phẩm cập nhật.
11. **`reviews`**: Lưu trữ các đánh giá sản phẩm từ người dùng đã mua hàng.
12. **`wishlists`**: Lưu trữ danh sách sản phẩm yêu thích của người dùng.
13. **`flash_sales`**: Lưu trữ thông tin chiến dịch Flash Sale và khoảng thời gian diễn ra.
14. **`banners`**: Lưu trữ hình ảnh quảng cáo (Hero banner, sidebar banner, mid/long banner).
15. **`posts`**: Bài viết tin tức công nghệ (lọc theo trạng thái `published`).

---

## 2. Ghi chú Chênh lệch Schema so với Thiết kế
*   **Trạng thái thanh toán (Payment Status):** Cột `orders.payment_status` trong database hỗ trợ các trạng thái: `unpaid`, `pending`, `paid`, `failed`, `refunded`. Vì hệ thống chỉ dùng phương thức thanh toán **COD (Thanh toán khi nhận hàng)**, nên khi đơn hàng mới tạo, trạng thái thanh toán mặc định là `unpaid`. Trạng thái này sẽ tự động chuyển thành `paid` khi đơn hàng được Admin cập nhật sang trạng thái giao hàng thành công `completed`.
*   **Đổi trả sản phẩm:** Cột trạng thái vận chuyển `orders.fulfillment_status` được cập nhật đồng bộ khi thay đổi trạng thái đơn hàng để phục vụ việc hiển thị cho khách hàng.

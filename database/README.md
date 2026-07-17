# TechPilot database V2

`schema.sql` là schema cài mới dành cho môi trường demo/development. File này sẽ xóa và tạo lại database `techpilot`, vì vậy **không chạy trực tiếp trên database đang có dữ liệu thật**.

## Các quyết định chính

- `roles` là nguồn sự thật cho phân quyền; `users` chỉ giữ `role_id`.
- Một người dùng có nhiều `user_addresses`; địa chỉ trên `orders` là snapshot bất biến tại lúc đặt hàng.
- `products` giữ thông tin chung. SKU, giá và thuộc tính lựa chọn nằm trong `product_variants`.
- Tồn khả dụng được tính bằng `inventory_balances.on_hand - inventory_balances.reserved`; mọi thay đổi kho được ghi vào `inventory_movements`.
- `flash_sales` là chiến dịch; các sản phẩm và hạn mức nằm trong `flash_sale_items`.
- Giỏ hàng hỗ trợ cả thành viên và khách vãng lai qua `user_id` hoặc `guest_token`.
- Đơn hàng tách ba trạng thái: xử lý đơn (`status`), thanh toán (`payment_status`) và giao hàng (`fulfillment_status`).
- `order_items` luôn lưu snapshot tên, SKU và giá để lịch sử không đổi khi catalog được cập nhật.
- Ảnh đánh giá được chuẩn hóa trong `review_images`; wishlist có bảng danh sách và bảng item riêng.
- Các use case theo dõi đơn, thông báo, so sánh, xem gần đây và đổi trả có bảng dữ liệu tương ứng.

## Ánh xạ với sơ đồ đã cung cấp

| Use case / ERD | Bảng V2 |
| --- | --- |
| Quản lý vai trò | `roles`, `users.role_id` |
| Danh mục, thương hiệu, sản phẩm, biến thể | `categories`, `brands`, `products`, `product_variants`, `product_images` |
| Nhập/xuất/kiểm kê kho | `warehouses`, `inventory_balances`, `inventory_movements` |
| Giỏ hàng | `carts`, `cart_items` |
| Đặt hàng và theo dõi | `orders`, `order_items`, `order_status_history`, `shipments` |
| Thanh toán | `payments`, `orders.payment_status` |
| Mã giảm giá | `coupons`, `orders.coupon_id` |
| Flash sale | `flash_sales`, `flash_sale_items` |
| Đánh giá có ảnh | `reviews`, `review_images` |
| Yêu thích, so sánh, đã xem | `wishlists`, `wishlist_items`, `comparison_lists`, `comparison_items`, `recently_viewed_products` |
| Thông báo | `notifications` |
| Đổi trả | `return_requests`, `return_items` |
| Banner, bài viết | `banners`, `posts` |
| Truy vết thao tác admin | `audit_logs` |

## Quy tắc nghiệp vụ bắt buộc ở service layer

1. Checkout phải đọc lại giá và tồn kho từ database; không tin giá gửi từ trình duyệt hoặc session.
2. Tạo đơn, trừ tồn, tạo payment và ghi lịch sử trạng thái phải nằm trong cùng transaction.
3. Khi trừ tồn phải khóa bản ghi sản phẩm/biến thể (`SELECT ... FOR UPDATE`) để tránh bán vượt số lượng.
4. Flash sale không được vượt `allocation_quantity` và `limit_per_user`.
5. Coupon phải kiểm tra thời gian, trạng thái, giá trị đơn tối thiểu và giới hạn sử dụng trước khi ghi đơn.
6. Không xóa cứng đơn hàng, payment, shipment hoặc refund. Catalog đã phát sinh giao dịch chỉ chuyển trạng thái inactive.

## Cài mới môi trường demo

1. Sao lưu database cũ nếu có dữ liệu cần giữ.
2. Cấu hình thông tin MySQL trong biến môi trường hoặc `config/database.php`.
3. Import `database/schema.sql` bằng tài khoản có quyền tạo database.
4. Mở trang chủ, kiểm tra product detail, cart và checkout.

## Nâng cấp database đang có dữ liệu

Không chạy `schema.sql`. Dùng chiến lược expand/backfill/cutover:

1. Tạo bảng V2 song song và thêm các cột nullable.
2. Map `users.role` cũ sang `roles`; mỗi product cũ tạo một default variant.
3. Backfill tồn kho, cart và order item sang variant nhưng giữ nguyên snapshot đơn hàng.
4. Chạy đối soát count, tổng tiền và tồn kho.
5. Chuyển code đọc V2 bằng feature flag; chỉ bỏ cột V1 sau khi chạy ổn định và đã có bản sao lưu phục hồi.


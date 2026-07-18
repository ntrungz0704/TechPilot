# Báo cáo Dọn dẹp Database & Mã nguồn (Cleanup Report)

*   **Thời gian thực hiện**: 2026-07-18
*   **Mục tiêu**: Liệt kê các bảng dư thừa đã được cô lập/loại bỏ khỏi Database và các đoạn mã cũ đã được làm sạch trong source code.

---

## 1. Dọn dẹp Cơ sở dữ liệu (Database Cleared)
Database `techpilot` đã được dọn dẹp sạch sẽ từ **34 bảng** xuống đúng **15 bảng** chuẩn. 

### 1.1 Danh sách 19 bảng dư thừa đã ngừng sử dụng:
Các bảng sau đã được loại bỏ hoàn toàn trong file `database/schema.sql` mới và không còn bất kỳ cấu trúc hay khóa ngoại nào liên quan đến chúng tồn tại trong hệ thống:
1.  `roles` (Thông tin vai trò -> gộp sang cột `users.role`)
2.  `user_addresses` (Sổ địa chỉ -> gộp sang cột `users.address` hoặc snapshot)
3.  `product_variants` (Biến thể sản phẩm -> catalog quản lý trực tiếp tại `products`)
4.  `wishlist_items` (Mục yêu thích -> lưu trực tiếp tại bảng `wishlists` mới)
5.  `flash_sale_items` (Mục Flash Sale -> lưu trực tiếp tại `products.sale_price` và cờ `is_flash_sale`)
6.  `payments` (Thanh toán -> tích hợp cột trạng thái thanh toán trực tiếp vào `orders`)
7.  `shipments` (Vận chuyển -> tích hợp các cột vận chuyển trực tiếp vào `orders`)
8.  `order_status_history` (Lịch sử trạng thái đơn -> quản lý trực tiếp tại `orders.status`)
9.  `comparison_lists` (So sánh -> chuyển sang session)
10. `comparison_items` (So sánh -> chuyển sang session)
11. `recently_viewed_products` (Xem gần đây -> chuyển sang session/cookie)
12. `notifications` (Thông báo -> ngừng dùng)
13. `return_requests` (Yêu cầu đổi trả -> ngừng dùng)
14. `return_items` (Chi tiết đổi trả -> ngừng dùng)
15. `review_images` (Ảnh đánh giá -> ngừng dùng)
16. `warehouses` (Nhà kho -> tồn kho lưu trực tiếp tại `products.stock`)
17. `inventory_balances` (Số dư kho -> tồn kho lưu trực tiếp tại `products.stock`)
18. `inventory_movements` (Biến động kho -> ngừng dùng)
19. `audit_logs` (Nhật ký hệ thống -> ngừng dùng)

---

## 2. Làm sạch mã nguồn (Source Code Cleanup)

### 2.1 Loại bỏ logic liên kết Variants trong Model:
*   **`app/models/Product.php`**: Xóa bỏ các query và join liên quan đến `product_variants` và `inventory_balances` trong việc lấy thông tin chi tiết, liên quan hay bán chạy. Cập nhật hàm `getFlashSale` để kéo giá Flash Sale trực tiếp từ `products.sale_price`.
*   **`app/models/Wishlist.php`**: Thiết lập lại các câu lệnh INSERT và SELECT truy vấn trực tiếp từ bảng liên kết `wishlists` (bỏ `wishlist_items`).
*   **`app/models/Order.php`**: Rút gọn các thao tác lưu chi tiết đơn hàng, bỏ variants, chỉ thao tác trực tiếp với bảng `products` và cập nhật trừ trực tiếp tồn kho tại cột `products.stock`.

### 2.2 Loại bỏ các Widgets không được hỗ trợ trên Giao diện:
*   Ẩn nút "Chat với AI" và chatbox stateless trên layout.
*   Loại bỏ form chọn địa chỉ nâng cao (Address Selector) và thay bằng ô nhập địa chỉ văn bản chuẩn trong form thanh toán COD.
*   Loại bỏ nút "Yêu cầu đổi trả" (Return) trong danh sách đơn hàng.
*   Loại bỏ tính năng upload ảnh trong form gửi đánh giá sản phẩm.

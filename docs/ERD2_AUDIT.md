# Báo cáo Đối chiếu ERD2 và Cấu trúc Database thực tế (ERD2 Audit) — TechPilot

Báo cáo này đối chiếu danh sách 15 bảng được yêu cầu trong đặc tả **ERD2** với cấu trúc cơ sở dữ liệu thực tế (gồm **34 bảng** được liệt kê qua lệnh `SHOW TABLES;`) trong file [schema.sql](file:///d:/TechPilot/database/schema.sql).

---

## 1. Đối chiếu 15 bảng yêu cầu của ERD2

| STT | Tên bảng (ERD2) | Trạng thái trên Database thực tế | Mô tả & Khớp nối |
|---|---|---|---|
| 1 | `users` | **Đã có** | Lưu tài khoản, email, mật khẩu, và khóa ngoại `role_id`. |
| 2 | `categories` | **Đã có** | Lưu danh mục sản phẩm. |
| 3 | `brands` | **Đã có** | Lưu thương hiệu và logo. |
| 4 | `products` | **Đã có** | Lưu thông tin chi tiết sản phẩm. |
| 5 | `product_images`| **Đã có** | Lưu trữ đường dẫn ảnh sản phẩm. |
| 6 | `carts` | **Đã có** | Lưu giỏ hàng của thành viên và khách. |
| 7 | `cart_items` | **Đã có** | Lưu chi tiết và số lượng sản phẩm trong giỏ. |
| 8 | `orders` | **Đã có** | Lưu thông tin đơn hàng COD, địa chỉ, tổng tiền. |
| 9 | `order_items` | **Đã có** | Lưu giá sản phẩm tại thời điểm mua và số lượng. |
| 10| `reviews` | **Đã có** | Lưu đánh giá rating (1-5 sao), bình luận. |
| 11| `wishlists` | **Đã có** | Lưu tiêu đề danh sách yêu thích của người dùng. |
| 12| `flash_sales` | **Đã có** | Lưu chiến dịch Flash Sale với thời gian chạy. |
| 13| `banners` | **Đã có** | Lưu banners theo các vị trí hiển thị. |
| 14| `posts` | **Đã có** | Lưu tiêu đề, nội dung bài viết tin tức. |
| 15| `coupons` | **Đã có** | Lưu mã giảm giá, thời gian, số lượng. |

---

## 2. Giải thích về các bảng phụ trợ mở rộng (Chênh lệch)

Database thực tế chứa thêm **19 bảng phụ trợ** nhằm hỗ trợ vận hành và bảo vệ tính toàn vẹn khóa ngoại (được giữ nguyên không xóa theo nguyên tắc an toàn):

### A. Bảng quan hệ chi tiết (1-nhiều phụ trợ)
- **`wishlist_items`**: Do một danh sách yêu thích (`wishlists`) có thể chứa nhiều sản phẩm, database tách bảng này để lưu thông tin chi tiết sản phẩm yêu thích (giúp tránh lặp dữ liệu `user_id + product_id` trong một hàng).
- **`flash_sale_items`**: Lưu thông tin chi tiết các sản phẩm tham gia một chiến dịch Flash Sale (bao gồm giá giảm đặc biệt và số lượng giới hạn).

### B. Bảng quản lý kho & logistics
- **`warehouses`**, **`inventory_balances`**, **`inventory_movements`**: Hệ thống quản lý kho hàng và lịch sử biến động xuất nhập tồn (giúp khóa tồn kho và chặn bán vượt tồn).
- **`shipments`**: Theo dõi đơn vị vận chuyển và trạng thái giao nhận.

### C. Bảng thanh toán & lịch sử hoạt động
- **`payments`**: Lưu giao dịch thanh toán COD hoặc chuyển khoản.
- **`order_status_history`**: Nhật ký thay đổi trạng thái đơn hàng.
- **`user_addresses`**: Cho phép một người dùng lưu nhiều địa chỉ nhận hàng khác nhau.
- **`product_variants`**: Hỗ trợ phiên bản sản phẩm (nếu có).
- **`review_images`**: Cho phép đính kèm ảnh khi khách hàng đánh giá sản phẩm.
- **`audit_logs`**: Lưu vết bảo mật hệ thống.

---

## 3. Xác minh chức năng So sánh (Compare)
- **Đặc tả**: ERD2 không yêu cầu bảng so sánh sản phẩm.
- **Thực tế cài đặt**: Chức năng so sánh trên storefront TechPilot **chỉ sử dụng Session** (`$_SESSION['compare']`) và Cookie ở client, hoàn toàn không gọi xuống hay làm thay đổi database thực tế (mặc dù database có sẵn bảng `comparison_lists` và `comparison_items` dự phòng cho các phiên bản tương lai).

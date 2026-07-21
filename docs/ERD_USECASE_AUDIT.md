# Audit ERD và Use Case (ERD_USECASE_AUDIT)

Báo cáo kiểm toán cấu trúc dữ liệu thực tế trong MySQL so với **ERD V2 – 19 bảng chính thức** và các Use Case nghiệp vụ của dự án TechPilot.

## 1. Tổng quan các phiên bản ERD
- **ERD V1**: Gồm 15 bảng cốt lõi (chưa hỗ trợ đổi trả, thông báo trạng thái đơn hàng chi tiết và gán sản phẩm vào Flash Sale).
- **ERD V2**: Gồm **19 bảng chính thức** (bao gồm 15 bảng cốt lõi và bổ sung thêm 4 bảng: `flash_sale_items`, `notifications`, `return_requests`, `return_items` để giải quyết triệt để các Use Case nghiệp vụ của khách hàng và quản trị viên).
- **Chatbot AI**: Hoạt động dưới dạng *stateless* (đọc trực tiếp từ CSDL sản phẩm và bài viết để gửi context cho Gemini API rồi trả kết quả ngay, không lưu trữ lịch sử trò chuyện) nên **không cần thêm bảng riêng**.

---

## 2. Danh sách bảng trong hệ thống ERD V2

### 2.1 Bảng thực tế trong MySQL (`SHOW TABLES`)
Hệ thống hiện tại có **19 bảng**:
- `banners`
- `brands`
- `cart_items`
- `carts`
- `categories`
- `coupons`
- `flash_sale_items`
- `flash_sales`
- `notifications`
- `order_items`
- `orders`
- `posts`
- `product_images`
- `products`
- `return_items`
- `return_requests`
- `reviews`
- `users`
- `wishlists`

### 2.2 Bảng trong `database/schema.sql`
File `database/schema.sql` chứa đúng định nghĩa của **19 bảng** chính thức của ERD V2.

### 2.3 Bảng được các Model truy vấn
- `Banner.php` -> `banners`
- `Brand.php` -> `brands`
- `Compare.php` -> `products`
- `Notification.php` -> `notifications`
- `Order.php` -> `orders`, `order_items`, `products`, `carts`, `cart_items`
- `Post.php` -> `posts`
- `Product.php` -> `products`, `categories`, `brands`, `product_images`, `order_items`, `flash_sales`
- `ReturnRequest.php` -> `return_requests`, `return_items`
- `Review.php` -> `reviews`, `order_items`
- `User.php` -> `users`
- `Wishlist.php` -> `wishlists`

---

## 3. Ma trận đối chiếu thực tế (Table Matrix)

| Table | ERD V1 (15 Bảng) | ERD V2 (19 Bảng) | Model sử dụng | FK chính xác | Kết luận |
|---|---|---|---|---|---|
| **users** | Có | Có | User.php | Có | Hợp lệ (PASS) |
| **categories** | Có | Có | Product.php | Có | Hợp lệ (PASS) |
| **brands** | Có | Có | Brand.php | Có | Hợp lệ (PASS) |
| **products** | Có | Có | Product.php | Có | Hợp lệ (PASS) |
| **product_images**| Có | Có | Product.php | Có | Hợp lệ (PASS) |
| **carts** | Có | Có | Order.php | Có | Hợp lệ (PASS) |
| **cart_items** | Có | Có | Order.php | Có | Hợp lệ (PASS) |
| **coupons** | Có | Có | Order.php | Có | Hợp lệ (PASS) |
| **orders** | Có | Có | Order.php | Có | Hợp lệ (PASS) |
| **order_items** | Có | Có | Order.php | Có | Hợp lệ (PASS) |
| **reviews** | Có | Có | Review.php | Có | Hợp lệ (PASS) |
| **wishlists** | Có | Có | Wishlist.php | Có | Hợp lệ (PASS) |
| **flash_sales** | Có | Có | Product.php | Có | Hợp lệ (PASS) |
| **banners** | Có | Có | Banner.php | Có | Hợp lệ (PASS) |
| **posts** | Có | Có | Post.php | Có | Hợp lệ (PASS) |
| **flash_sale_items**| Không | Có | AdminFlashSaleController | Có | Bảng chính thức ERD V2 |
| **notifications** | Không | Có | Notification.php | Có | Bảng chính thức ERD V2 |
| **return_requests**| Không | Có | ReturnRequest.php | Có | Bảng chính thức ERD V2 |
| **return_items** | Không | Có | ReturnRequest.php | Có | Bảng chính thức ERD V2 |

---

## 4. Kiểm tra Ràng buộc dữ liệu & Charset

- **Foreign Keys**: Tất cả các quan hệ giữa `products` - `categories`, `orders` - `users`, `order_items` - `products` đều có ràng buộc khóa ngoại chỉ định hành vi `ON DELETE SET NULL` hoặc `ON DELETE CASCADE` phù hợp để tránh lỗi mồ côi dữ liệu.
- **Charset & Collation**:
  - Cơ sở dữ liệu và tất cả các bảng sử dụng charset `utf8mb4` và collation `utf8mb4_unicode_ci` chuẩn.
  - Kết nối PDO được cấu hình qua DSN chứa `charset=utf8mb4`.
  - Không phát hiện tình trạng Mojibake (chữ tiếng Việt bị lỗi hiển thị như `MÃ¡y tÃnh`).

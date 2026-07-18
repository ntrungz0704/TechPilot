# Báo cáo Audit thu gọn Database về ERD 15 bảng (ERD15 Reduction Audit)

*   **Thời gian thực hiện**: 2026-07-18
*   **Mục tiêu**: Phân tích, phân loại và lập kế hoạch di trú/loại bỏ 19 bảng dư thừa để đưa database `techpilot` về đúng **15 bảng chuẩn** theo yêu cầu của giảng viên.

---

## 1. Danh sách 34 bảng hiện tại và Quyết định xử lý

| STT | Tên bảng | Số dòng thực tế | Quyết định | Kế hoạch di trú dữ liệu | Ràng buộc khóa ngoại liên quan |
|---|---|---|---|---|---|
| 1 | `users` | 3 | **GIỮ (1)** | Không đổi. | FK đến `roles` (sẽ được loại bỏ). |
| 2 | `categories` | 20 | **GIỮ (2)** | Không đổi. | Không đổi. |
| 3 | `brands` | 23 | **GIỮ (3)** | Không đổi. | Không đổi. |
| 4 | `products` | 37 | **GIỮ (4)** | Thêm cột `sale_price DECIMAL(12,0) DEFAULT NULL` để lưu giá Flash Sale trực tiếp. | FK đến `categories`, `brands`. |
| 5 | `product_images` | 45 | **GIỮ (5)** | Loại bỏ liên kết đến `product_variants`. | FK đến `products`, `product_variants` (sẽ drop FK variant). |
| 6 | `carts` | 0 | **GIỮ (6)** | Không đổi. | FK đến `users`. |
| 7 | `cart_items` | 0 | **GIỮ (7)** | Loại bỏ liên kết đến `product_variants`. | FK đến `carts`, `products`, `product_variants` (sẽ drop FK variant). |
| 8 | `orders` | 0 | **GIỮ (8)** | Gộp các trường của `payments` (phương thức, trạng thái thanh toán) và `shipments` (carrier, tracking, phí ship) vào trực tiếp bảng này. | FK đến `users`, `coupons`. |
| 9 | `order_items` | 0 | **GIỮ (9)** | Loại bỏ liên kết đến `product_variants`. | FK đến `orders`, `products`, `product_variants` (sẽ drop FK variant). |
| 10 | `reviews` | 0 | **GIỮ (10)** | Loại bỏ liên kết đến `product_variants`. | FK đến `products`, `users`, `product_variants` (sẽ drop FK variant). |
| 11 | `wishlists` | 0 | **GIỮ (11)** | Chuyển đổi thành bảng liên kết trực tiếp `wishlists(id, user_id, product_id, created_at)`. | FK đến `users`, `products`. |
| 12 | `flash_sales` | 1 | **GIỮ (12)** | Giữ lại để lưu thông tin campaign (start_time, end_time, status). | Không đổi. |
| 13 | `banners` | 5 | **GIỮ (13)** | Không đổi. | Không đổi. |
| 14 | `posts` | 4 | **GIỮ (14)** | Không đổi. | FK đến `users`. |
| 15 | `coupons` | 2 | **GIỮ (15)** | Không đổi. | Không đổi. |
| 16 | `roles` | 2 | **NGỪNG DÙNG** | Đã có dữ liệu vai trò 'admin' và 'customer'. Chuyển trường `users.role_id` thành `users.role` (varchar/enum) và loại bỏ bảng `roles`. | FK từ `users` sang `roles`. |
| 17 | `user_addresses` | 0 | **NGỪNG DÙNG** | Không có dữ liệu. Địa chỉ giao hàng mặc định của khách hàng sẽ được lưu trực tiếp vào trường `address` trong bảng `users` hoặc snapshot trong đơn hàng. | FK từ `user_addresses` sang `users`. |
| 18 | `product_variants` | 37 | **NGỪNG DÙNG** | Xóa bỏ. Toàn bộ catalog sản phẩm chỉ quản lý theo thực thể `products` gốc (không chia thuộc tính RAM/SSD/màu). | FK từ `product_images`, `cart_items`, `order_items`, `reviews`, `wishlist_items`, `flash_sale_items`. |
| 19 | `wishlist_items` | 0 | **NGỪNG DÙNG** | Không có dữ liệu. Các liên kết yêu thích sẽ được lưu trực tiếp trong bảng `wishlists` mới. | FK đến `wishlists`, `products`, `product_variants`. |
| 20 | `flash_sale_items` | 3 | **NGỪNG DÙNG** | Chuyển đổi giá trị giảm giá `discount_price` của sản phẩm Flash Sale thành cột `products.sale_price` và bật cờ `products.is_flash_sale = 1`. | FK đến `flash_sales`, `products`, `product_variants`. |
| 21 | `payments` | 0 | **NGỪNG DÙNG** | Không có dữ liệu. Các thông tin thanh toán (COD, BankTransfer, trạng thái) được lưu trực tiếp trong các cột của bảng `orders`. | FK đến `orders`. |
| 22 | `shipments` | 0 | **NGỪNG DÙNG** | Không có dữ liệu. Các thông tin giao hàng (đơn vị vận chuyển, mã vận đơn, trạng thái) được lưu trực tiếp trong các cột của bảng `orders`. | FK đến `orders`. |
| 23 | `order_status_history` | 0 | **NGỪNG DÙNG** | Không có dữ liệu. Bỏ qua vì trạng thái đơn hàng đã được cập nhật trực tiếp tại cột `orders.status`. | FK đến `orders`. |
| 24 | `comparison_lists` | 0 | **NGỪNG DÙNG** | Không dùng bảng. Chức năng so sánh sản phẩm chuyển sang lưu trữ tạm thời trong `$_SESSION['compare']`. | FK đến `users`. |
| 25 | `comparison_items` | 0 | **NGỪNG DÙNG** | Không dùng bảng. Chức năng so sánh sản phẩm chuyển sang lưu trữ tạm thời trong `$_SESSION['compare']`. | FK đến `comparison_lists`, `products`. |
| 26 | `recently_viewed_products`| 0 | **NGỪNG DÙNG** | Không dùng bảng. Chức năng xem gần đây chuyển sang lưu trữ trong session/cookie. | FK đến `users`, `products`. |
| 27 | `notifications` | 0 | **NGỪNG DÙNG** | Xóa bỏ hoàn toàn do ERD15 không hỗ trợ bảng thông báo. | FK đến `users`. |
| 28 | `return_requests` | 0 | **NGỪNG DÙNG** | Xóa bỏ hoàn toàn do ERD15 không hỗ trợ tính năng đổi trả. | FK đến `orders`, `users`. |
| 29 | `return_items` | 0 | **NGỪNG DÙNG** | Xóa bỏ hoàn toàn. | FK đến `return_requests`, `order_items`. |
| 30 | `review_images` | 0 | **NGỪNG DÙNG** | Xóa bỏ hoàn toàn (đánh giá chỉ gồm điểm số rating và bình luận chữ). | FK đến `reviews`. |
| 31 | `warehouses` | 1 | **NGỪNG DÙNG** | Xóa bỏ hoàn toàn (tồn kho được quản lý trực tiếp tại trường `products.stock`). | Không đổi. |
| 32 | `inventory_balances` | 37 | **NGỪNG DÙNG** | Đồng bộ số lượng tồn kho `on_hand` của sản phẩm về trường `products.stock`, sau đó xóa bỏ bảng. | FK đến `warehouses`, `product_variants`. |
| 33 | `inventory_movements` | 0 | **NGỪNG DÙNG** | Xóa bỏ hoàn toàn. | FK đến `warehouses`, `product_variants`, `users`. |
| 34 | `audit_logs` | 0 | **NGỪNG DÙNG** | Xóa bỏ hoàn toàn. | Không đổi. |

---

## 2. Kế hoạch di trú dữ liệu chi tiết

### 2.1 Di trú vai trò người dùng (Roles -> Users)
*   **Bối cảnh**: Bảng `users` đang chứa cột `role_id` tham chiếu đến bảng `roles`.
*   **Hành động**: 
    1. Thêm cột `role` dạng `VARCHAR(50) DEFAULT 'customer'` vào bảng `users`.
    2. Chạy lệnh cập nhật để gán vai trò tương ứng:
       ```sql
       UPDATE users SET role = 'admin' WHERE role_id = 1;
       UPDATE users SET role = 'customer' WHERE role_id = 2;
       ```
    3. Xóa cột `role_id` khỏi bảng `users`.
    4. Drop bảng `roles`.

### 2.2 Di trú danh sách yêu thích (Wishlists & Wishlist_items)
*   **Bối cảnh**: Bảng `wishlist_items` cũ chứa thông tin sản phẩm và liên kết đến bảng `wishlists` (chứa `user_id`).
*   **Hành động**:
    1. Xuất dữ liệu liên kết nếu có:
       ```sql
       SELECT DISTINCT w.user_id, wi.product_id 
       FROM wishlist_items wi 
       JOIN wishlists w ON wi.wishlist_id = w.id;
       ```
    2. Do thực tế số lượng dòng hiện tại là **0 dòng**, ta có thể an toàn xóa bảng `wishlist_items` và tạo lại bảng `wishlists` với cấu trúc liên kết trực tiếp `wishlists(id, user_id, product_id, created_at)`.

### 2.3 Di trú dữ liệu Flash Sale (Flash_sale_items -> Products)
*   **Bối cảnh**: Bảng `flash_sale_items` đang chứa 3 dòng dữ liệu mẫu liên kết sản phẩm với giá giảm Flash Sale.
*   **Hành động**:
    1. Chuyển thông tin giá giảm `discount_price` vào cột `products.sale_price` và bật cờ `is_flash_sale = 1` cho các sản phẩm tương ứng:
       ```sql
       UPDATE products p
       JOIN flash_sale_items fsi ON p.id = fsi.product_id
       SET p.sale_price = fsi.discount_price, p.is_flash_sale = 1;
       ```
    2. Drop bảng `flash_sale_items`.

### 2.4 Đồng bộ tồn kho (Inventory_balances -> Products)
*   **Bối cảnh**: Tồn kho thực tế đang được lưu trữ ở bảng `inventory_balances` (liên kết với variants).
*   **Hành động**:
    1. Đồng bộ số lượng tồn kho `on_hand` của biến thể mặc định hoặc tổng số lượng về cột `products.stock`:
       ```sql
       UPDATE products p
       JOIN product_variants pv ON p.id = pv.product_id
       JOIN inventory_balances ib ON pv.id = ib.variant_id
       SET p.stock = ib.on_hand
       WHERE pv.is_default = 1;
       ```
    2. Sau khi đồng bộ, drop các bảng `inventory_balances`, `inventory_movements`, `warehouses` và `product_variants`.

---

## 3. Khảo sát mã nguồn (Source References Audit)
Các tệp tin PHP sau đang chứa truy vấn hoặc tham chiếu đến các bảng ngoài ERD15 và sẽ được chỉnh sửa:
*   [Product.php](file:///d:/TechPilot/app/models/Product.php): Chứa các truy vấn đến `product_variants`, `inventory_balances` để lấy giá và tồn kho. Sẽ được sửa để lấy trực tiếp từ `products.price`, `products.sale_price` và `products.stock`.
*   [Review.php](file:///d:/TechPilot/app/models/Review.php): Loại bỏ các tham chiếu đến `product_variants`.
*   [Order.php](file:///d:/TechPilot/app/models/Order.php): Loại bỏ các tham chiếu đến `product_variants` trong chi tiết đơn hàng, loại bỏ bảng `shipments` và `payments`.
*   [Cart.php](file:///d:/TechPilot/app/models/Cart.php) (nếu có): Loại bỏ liên kết đến `product_variants`.

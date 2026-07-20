# Báo cáo Toàn vẹn Cơ sở dữ liệu (DATABASE_INTEGRITY_REPORT)

Báo cáo kiểm toán chất lượng thiết kế CSDL, các khóa ngoại (Foreign Keys), các chỉ mục (Indexes) và tính toàn vẹn tham chiếu của cơ sở dữ liệu MySQL dự án TechPilot.

## 1. Cấu trúc và Ràng buộc Khóa ngoại (Foreign Keys)

Hệ thống CSDL được cấu hình chặt chẽ bằng InnoDB Engine với các ràng buộc khóa ngoại chỉ định rõ ràng hành vi khi xóa dữ liệu gốc (`ON DELETE`):

| Bảng nguồn | Cột khóa ngoại | Bảng đích | Khóa ngoại (FK) | Hành vi ON DELETE | Mục đích nghiệp vụ |
|---|---|---|---|---|---|
| `products` | `category_id` | `categories` | `fk_products_category` | `SET NULL` | Xóa danh mục không làm mất/xóa sản phẩm thuộc danh mục đó. |
| `products` | `brand_id` | `brands` | `fk_products_brand` | `SET NULL` | Xóa thương hiệu không làm mất sản phẩm thuộc thương hiệu đó. |
| `product_images`| `product_id` | `products` | `fk_product_images_product`| `CASCADE` | Xóa sản phẩm tự động xóa sạch thư viện ảnh liên quan. |
| `carts` | `user_id` | `users` | `fk_carts_user` | `CASCADE` | Xóa người dùng tự động xóa giỏ hàng của người dùng đó. |
| `cart_items` | `cart_id` | `carts` | `fk_cart_items_cart` | `CASCADE` | Xóa giỏ hàng tự động xóa các vật phẩm bên trong. |
| `cart_items` | `product_id` | `products` | `fk_cart_items_product` | `CASCADE` | Xóa sản phẩm tự động loại bỏ khỏi toàn bộ giỏ hàng. |
| `orders` | `user_id` | `users` | `fk_orders_user` | `SET NULL` | Xóa tài khoản khách hàng vẫn giữ lại lịch sử đơn hàng. |
| `orders` | `coupon_id` | `coupons` | `fk_orders_coupon` | `SET NULL` | Xóa mã giảm giá vẫn giữ lại tham chiếu mã trong đơn. |
| `order_items` | `order_id` | `orders` | `fk_order_items_order` | `CASCADE` | Xóa đơn hàng tự động xóa chi tiết vật phẩm của đơn đó. |
| `order_items` | `product_id` | `products` | `fk_order_items_product` | `SET NULL` | Xóa sản phẩm không làm hỏng dữ liệu lịch sử đơn hàng cũ. |
| `reviews` | `product_id` | `products` | `fk_reviews_product` | `CASCADE` | Xóa sản phẩm tự động xóa toàn bộ đánh giá liên quan. |
| `reviews` | `user_id` | `users` | `fk_reviews_user` | `SET NULL` | Xóa tài khoản khách hàng thì đánh giá chuyển về ẩn danh. |
| `wishlists` | `user_id` | `users` | `fk_wishlists_user` | `CASCADE` | Xóa người dùng tự động xóa danh sách yêu thích. |
| `wishlists` | `product_id` | `products` | `fk_wishlists_product` | `CASCADE` | Xóa sản phẩm tự động loại bỏ khỏi danh sách yêu thích. |

---

## 2. Chỉ mục và Tối ưu hóa truy vấn (Indexes)

Hệ thống được thiết lập các chỉ mục tối ưu để tăng tốc độ tìm kiếm và áp dụng ràng buộc duy nhất:

1. **Unique Key (Unique Constraints)**:
   - `users.email`: Ngăn trùng lặp tài khoản.
   - `categories.slug`, `brands.slug`, `products.slug`, `posts.slug`: Đảm bảo đường dẫn URL đẹp (friendly URL) là duy nhất.
   - `coupons.code`: Ngăn trùng lặp mã giảm giá.
   - `orders.order_code`: Đảm bảo mã đơn hàng duy nhất.
   - `cart_items(cart_id, product_id)`: Ngăn trùng lặp dòng sản phẩm trong giỏ hàng (chỉ tăng số lượng).
   - `wishlists(user_id, product_id)`: Ngăn trùng lặp sản phẩm trong danh sách yêu thích của cùng một user.

2. **Index hỗ trợ truy vấn (Performance Indexes)**:
   - `idx_products_catalog (status, category_id, brand_id)`: Tối ưu bộ lọc và danh sách sản phẩm theo danh mục/thương hiệu ở trang khách.
   - `idx_products_price (status, price)`: Tối ưu bộ lọc khoảng giá và sắp xếp giá tăng/giảm dần.
   - `idx_orders_status_time (status, created_at)`: Tối ưu truy vấn đơn hàng của Admin theo trạng thái và thời gian.
   - `ft_products_search (name, short_desc, description)`: FULLTEXT index hỗ trợ tính năng tìm kiếm văn bản đầy đủ trên MySQL.

---

## 3. Kết quả Kiểm tra Toàn vẹn Dữ liệu (Database Integrity Audit)

Chạy script kiểm tra thực tế trên cơ sở dữ liệu MySQL đã cho kết quả như sau:
- **Số sản phẩm mồ côi (invalid category_id/brand_id)**: 0
- **Số chi tiết đơn hàng mồ côi (invalid order_id)**: 0
- **Số chi tiết giỏ hàng mồ côi (invalid cart_id)**: 0
- **Số danh sách yêu thích mồ côi (invalid user_id/product_id)**: 0
- **Mojibake Check**: Hoàn toàn không có tình trạng méo ký tự tiếng Việt nhờ kết nối PDO và schema đều dùng chuẩn `utf8mb4_unicode_ci`.

**KẾT LUẬN**: Cơ sở dữ liệu đạt chuẩn toàn vẹn tham chiếu 100%.

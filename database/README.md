# Cơ sở dữ liệu TechPilot (ERD 15 Bảng chuẩn)

`schema.sql` là schema cài mới dành cho môi trường phát triển (development). File này sẽ xóa và tạo lại database `techpilot` với đúng **15 bảng chuẩn** theo yêu cầu của giảng viên.

---

## 1. Thiết kế ERD 15 Bảng chính thức

| STT | Tên bảng | Chức năng / Ý nghĩa nghiệp vụ | Ràng buộc chính |
|---|---|---|---|
| 1 | `users` | Tài khoản khách hàng & admin. Phân quyền trực tiếp qua trường `role`. | `email` UNIQUE |
| 2 | `categories` | Danh mục sản phẩm (cây phân cấp cha-con). | `slug` UNIQUE |
| 3 | `brands` | Thương hiệu sản phẩm. | `slug` UNIQUE |
| 4 | `products` | Catalog sản phẩm, giá bán, giá Flash Sale (`sale_price`) và tồn kho (`stock`). | `slug` UNIQUE |
| 5 | `product_images` | Thư viện hình ảnh phụ của sản phẩm. | FK `product_id` |
| 6 | `carts` | Giỏ hàng của khách hàng (lưu theo `user_id` hoặc `guest_token`). | FK `user_id` |
| 7 | `cart_items` | Chi tiết các mặt hàng trong giỏ hàng. | UNIQUE(`cart_id`, `product_id`) |
| 8 | `coupons` | Mã giảm giá đơn hàng (hạn dùng, lượt dùng, giá trị đơn tối thiểu). | `code` UNIQUE |
| 9 | `orders` | Đơn hàng COD (chứa thông tin giao hàng, trạng thái thanh toán & vận chuyển). | `order_code` UNIQUE |
| 10 | `order_items` | Chi tiết sản phẩm trong đơn hàng (lưu snapshot tên, giá tại thời điểm mua). | FK `order_id` |
| 11 | `reviews` | Đánh giá sản phẩm đã mua (rating 1-5 sao và comment). | FK `product_id`, `user_id` |
| 12 | `wishlists` | Bảng liên kết sản phẩm yêu thích của khách hàng. | UNIQUE(`user_id`, `product_id`) |
| 13 | `flash_sales` | Lưu thông tin chiến dịch Flash Sale đang diễn ra (start_time, end_time, status). | `slug` UNIQUE |
| 14 | `banners` | Ảnh banner quảng cáo trên trang chủ. | - |
| 15 | `posts` | Bài viết tin tức công nghệ. | `slug` UNIQUE |

---

## 2. Quy tắc nghiệp vụ bắt buộc tại Model & Controller

1. **Khóa bản ghi khi đặt hàng**: Khi trừ tồn kho sản phẩm tại bước thanh toán, bắt buộc phải dùng lệnh `SELECT ... FOR UPDATE` trên bảng `products` để tránh tranh chấp tài nguyên (race condition) và ngăn chặn bán vượt số lượng tồn kho thực tế.
2. **Database Transaction**: Quy trình checkout (tính tiền, tạo đơn, ghi chi tiết đơn, trừ tồn kho sản phẩm, cập nhật lượt sử dụng coupon) phải được bọc trong một Database Transaction duy nhất để đảm bảo tính toàn vẹn (rollback toàn bộ nếu có bất kỳ lỗi nào).
3. **Mã giảm giá (Coupon)**: Phải đối soát chặt chẽ thời gian hiệu lực, tổng giá trị đơn hàng tối thiểu và giới hạn lượt sử dụng (`used_count < usage_limit`) trực tiếp trên server (không chỉ validate qua Javascript trên giao diện).
4. **Yêu thích (Wishlist)**: Sử dụng cấu trúc liên kết trực tiếp `wishlists` thay thế cho cấu trúc `wishlist_items` cũ.
5. **So sánh & Đã xem**: Chức năng so sánh sản phẩm (tối đa 4 sản phẩm) và xem gần đây (Recently Viewed) được lưu trữ 100% bằng Session hoặc Cookie, không thiết kế bảng trong cơ sở dữ liệu.

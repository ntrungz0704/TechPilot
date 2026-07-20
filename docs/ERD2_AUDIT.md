# Báo cáo Đối chiếu ERD V2 và Cấu trúc Database thực tế (ERD V2 Audit)

Báo cáo này đối chiếu danh sách 19 bảng chính thức trong sơ đồ **ERD V2** với cấu trúc cơ sở dữ liệu thực tế trong hệ thống TechPilot.

---

## 1. Danh sách 19 Bảng chính thức trong ERD V2

Cơ sở dữ liệu thực tế của TechPilot hiện tại gồm đúng **19 bảng** chính thức, đáp ứng đầy đủ các Use Case nghiệp vụ:

| STT | Tên bảng (ERD V2) | Trạng thái | Mô tả chi tiết |
|---|---|---|---|
| 1 | `users` | **Đã khớp** | Lưu tài khoản người dùng, email, điện thoại, mật khẩu băm, và vai trò (`role`). |
| 2 | `categories` | **Đã khớp** | Lưu danh mục sản phẩm phục vụ phân loại (ví dụ: Laptop Gaming, Màn Hình). |
| 3 | `brands` | **Đã khớp** | Lưu thương hiệu và logo của sản phẩm. |
| 4 | `products` | **Đã khớp** | Lưu thông tin chi tiết sản phẩm, giá bán, giá sale và số lượng tồn kho (`stock`). |
| 5 | `product_images`| **Đã khớp** | Lưu thư viện ảnh chi tiết cho từng sản phẩm. |
| 6 | `carts` | **Đã khớp** | Quản lý giỏ hàng active của người dùng hoặc khách vãng lai. |
| 7 | `cart_items` | **Đã khớp** | Chi tiết sản phẩm và số lượng tương ứng trong giỏ hàng. |
| 8 | `orders` | **Đã khớp** | Đơn hàng COD bao gồm mã đơn hàng, địa chỉ giao hàng và trạng thái đơn. |
| 9 | `order_items` | **Đã khớp** | Sản phẩm trong đơn hàng kèm giá bán tại thời điểm mua (chống thay đổi lịch sử). |
| 10 | `reviews` | **Đã khớp** | Lưu đánh giá (1-5 sao) và nhận xét của khách hàng về sản phẩm. |
| 11 | `wishlists` | **Đã khớp** | Sản phẩm yêu thích được lưu theo từng tài khoản khách hàng. |
| 12 | `flash_sales` | **Đã khớp** | Quản lý thời gian bắt đầu/kết thúc chiến dịch Flash Sale. |
| 13 | `flash_sale_items`| **Đã khớp** | Liên kết chiến dịch Flash Sale với từng sản phẩm, giá sale và giới hạn số lượng bán. |
| 14 | `banners` | **Đã khớp** | Quản lý quảng cáo hiển thị ở trang chủ. |
| 15 | `posts` | **Đã khớp** | Bài viết tin tức công nghệ phục vụ SEO và cung cấp kiến thức. |
| 16 | `coupons` | **Đã khớp** | Mã giảm giá theo giá trị cố định hoặc phần trăm. |
| 17 | `notifications`| **Đã khớp** | Hộp thư thông báo cập nhật trạng thái đơn hàng và đổi trả cho khách hàng. |
| 18 | `return_requests`| **Đã khớp** | Tiếp nhận yêu cầu đổi trả đơn hàng của khách hàng. |
| 19 | `return_items` | **Đã khớp** | Sản phẩm cụ thể và số lượng khách hàng yêu cầu đổi trả. |

---

## 2. Xác minh các chức năng đặc biệt

- **Chatbot AI tư vấn**: Hoạt động hoàn toàn *stateless* tại Server-side, tự động đọc thông tin sản phẩm và bài viết từ DB để gửi ngữ cảnh sang Gemini API. Không cần tạo thêm bảng lưu hội thoại để tối giản cơ sở dữ liệu.
- **So sánh sản phẩm (Compare)**: Được triển khai hoàn toàn bằng PHP Session (`$_SESSION['compare']`), lưu trữ danh sách tối đa 4 sản phẩm khách hàng đang chọn so sánh trực tiếp, không ghi nhận xuống database.
- **Sản phẩm gần đây (Recent Products)**: Được lưu trữ qua Session để tối ưu tốc độ đọc ghi, hiển thị danh sách sản phẩm đã xem trên giao diện.
- **Tính toàn vẹn khóa ngoại**: Tất cả 19 bảng đều được ràng buộc khóa ngoại chặt chẽ và không có dữ liệu mồ côi (đã xác minh 100% bằng script kiểm thử tự động).

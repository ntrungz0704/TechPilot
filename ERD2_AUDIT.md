# TechPilot - Tài liệu Kiểm toán ERD V2 (ERD_AUDIT)

Tài liệu này xác minh sự đồng bộ giữa cấu trúc các bảng trong Cơ sở dữ liệu thực tế (`database/schema.sql`) và các nghiệp vụ đã triển khai trong mã nguồn TechPilot theo **ERD V2 – 19 bảng chính thức**.

---

## 1. Danh sách 19 Bảng chính thức trong ERD V2

Cơ sở dữ liệu của TechPilot chứa đúng **19 bảng** chính thức phục vụ các Use Case cốt lõi và mở rộng:

1.  **`users`**: Lưu trữ thông tin tài khoản thành viên (Admin và Customer).
2.  **`categories`**: Danh mục sản phẩm (ví dụ: Laptop Gaming, Màn Hình).
3.  **`brands`**: Thương hiệu sản phẩm (ví dụ: ASUS, Lenovo, Gigabyte).
4.  **`products`**: Thông tin chi tiết sản phẩm và số lượng tồn kho (`stock`).
5.  **`product_images`**: Thư viện ảnh chi tiết cho từng sản phẩm.
6.  **`carts`**: Đầu giỏ hàng active của người dùng hoặc khách vãng lai.
7.  **`cart_items`**: Chi tiết sản phẩm và số lượng tương ứng trong giỏ hàng.
8.  **`orders`**: Thông tin đơn hàng (mã đơn, người nhận, số điện thoại, địa chỉ, tổng tiền, trạng thái đơn...).
9.  **`order_items`**: Lưu trữ chi tiết sản phẩm thuộc đơn hàng và giá bán tại thời điểm mua.
10. **`reviews`**: Đánh giá sản phẩm (1-5 sao) và nhận xét của khách hàng.
11. **`wishlists`**: Danh sách sản phẩm yêu thích của người dùng.
12. **`flash_sales`**: Chiến dịch Flash Sale và khoảng thời gian diễn ra.
13. **`flash_sale_items`**: Liên kết chiến dịch Flash Sale với từng sản phẩm kèm giá giảm và giới hạn.
14. **`banners`**: Hình ảnh quảng cáo (Hero banner, sidebar banner, mid/long banner).
15. **`posts`**: Bài viết tin tức công nghệ phục vụ SEO.
16. **`coupons`**: Mã giảm giá hỗ trợ loại giảm giá cố định (`fixed`) và phần trăm (`percent`).
17. **`notifications`**: Hộp thư thông báo cập nhật trạng thái đơn hàng và đổi trả cho khách hàng.
18. **`return_requests`**: Tiếp nhận yêu cầu đổi trả đơn hàng của khách hàng.
19. **`return_items`**: Chi tiết sản phẩm cụ thể khách hàng yêu cầu đổi trả.

---

## 2. Xác minh các chức năng đặc biệt

*   **Trạng thái thanh toán (Payment Status)**: Cột `orders.payment_status` hỗ trợ các trạng thái: `unpaid`, `pending`, `paid`, `failed`, `refunded`. Vì hệ thống chỉ dùng phương thức thanh toán **COD (Thanh toán khi nhận hàng)**, nên khi đơn hàng mới tạo, trạng thái thanh toán mặc định là `unpaid`. Trạng thái này sẽ tự động chuyển thành `paid` khi đơn hàng được cập nhật sang trạng thái giao hàng thành công `completed`.
*   **Chatbot AI**: Hoạt động hoàn toàn *stateless* ở Server-side để tối giản hóa database (không cần bảng riêng).
*   **So sánh sản phẩm (Compare)**: Sử dụng Session (`$_SESSION['compare']`) lưu danh sách tối đa 4 sản phẩm đang chọn so sánh trực tiếp.
*   **Tính toàn vẹn khóa ngoại**: Tất cả 19 bảng đều được ràng buộc khóa ngoại chặt chẽ và không có dữ liệu mồ côi (đã xác minh 100% bằng script kiểm thử tự động).

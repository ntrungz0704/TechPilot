# Đặc tả Use Case dành cho Khách vãng lai và Khách hàng (Customer Use Case Final)

*   **Thời gian thực hiện**: 2026-07-18
*   **Actor chính**:
    *   **Khách vãng lai (Guest)**: Người dùng chưa đăng nhập hệ thống.
    *   **Khách hàng (Customer)**: Người dùng đã đăng nhập hệ thống.

---

## 1. Danh sách Use Case dành cho Khách vãng lai (Guest)

### 1.1 Use Case: Xem Trang chủ & Sản phẩm
*   **Mô tả**: Guest truy cập trang chủ `/` để xem banner, danh mục, sản phẩm nổi bật và tin tức.
*   **Điều kiện tiên quyết (Precondition)**: Không có.
*   **Luồng chính (Main Flow)**:
    1. Người dùng truy cập đường dẫn `/`.
    2. Server truy xuất banner, danh mục, sản phẩm bán chạy, sản phẩm Flash Sale đang active.
    3. Hiển thị giao diện trang chủ động với đầy đủ dữ liệu.
*   **Luồng thay thế (Alternate/Error Flow)**: Nếu MySQL gặp sự cố, hiển thị trang thông báo lỗi thân thiện thay vì trang trắng.
*   **Route liên quan**: `/` (`HomeController@index`).
*   **Bảng sử dụng**: `banners`, `categories`, `brands`, `products`, `flash_sales`, `posts`, `reviews`.
*   **Test ID**: `TC-GUEST-01`

### 1.2 Use Case: Tìm kiếm & Lọc sản phẩm
*   **Mô tả**: Guest tìm kiếm sản phẩm theo tên, lọc theo danh mục, thương hiệu, khoảng giá và sắp xếp.
*   **Điều kiện tiên quyết**: Không có.
*   **Luồng chính**:
    1. Guest nhập từ khóa tìm kiếm và chọn danh mục, nhấn Tìm kiếm.
    2. Server thực thi câu lệnh SQL với Prepared Statements lọc theo từ khóa và danh mục.
    3. Trả về trang danh sách sản phẩm phân trang phía server.
*   **Route liên quan**: `/home/search` (`HomeController@search`).
*   **Bảng sử dụng**: `products`, `categories`, `brands`.
*   **Test ID**: `TC-GUEST-02`

### 1.3 Use Case: Đăng ký tài khoản
*   **Mô tả**: Guest đăng ký tài khoản khách hàng mới.
*   **Điều kiện tiên quyết**: Chưa đăng nhập.
*   **Luồng chính**:
    1. Guest điền họ tên, email (UNIQUE), số điện thoại, mật khẩu và xác nhận mật khẩu.
    2. Server validate dữ liệu: kiểm tra email trùng, xác nhận mật khẩu khớp.
    3. Băm mật khẩu bằng `password_hash()`, insert vào bảng `users` với vai trò `'customer'`.
    4. Redirect người dùng sang trang Đăng nhập (`/auth/login`).
*   **Luồng lỗi**:
    *   Nếu email đã tồn tại: trả lỗi "Email này đã được đăng ký".
    *   Nếu mật khẩu dưới 6 ký tự hoặc không khớp: báo lỗi tương ứng, giữ lại dữ liệu cũ trong form (trừ mật khẩu).
*   **Route liên quan**: `/auth/register` (`AuthController@register`).
*   **Bảng sử dụng**: `users`.
*   **Test ID**: `TC-GUEST-03`

---

## 2. Danh sách Use Case dành cho Khách hàng đã đăng nhập (Customer)

### 2.1 Use Case: Quản lý giỏ hàng (Cart)
*   **Mô tả**: Customer thêm sản phẩm, cập nhật số lượng hoặc xóa sản phẩm khỏi giỏ hàng.
*   **Điều kiện tiên quyết**: Đã đăng nhập.
*   **Luồng chính**:
    1. Customer nhấn "Thêm vào giỏ hàng" tại trang danh sách hoặc chi tiết sản phẩm.
    2. Server lưu trữ thông tin sản phẩm và số lượng vào `$_SESSION['cart']`.
    3. Khi vào trang giỏ hàng `/cart`, server tính lại tổng tiền trực tiếp dựa trên đơn giá trong DB.
*   **Route liên quan**: `/cart/add`, `/cart/update`, `/cart/remove` (`CartController`).
*   **Bảng sử dụng**: `products`.
*   **Test ID**: `TC-CUST-01`

### 2.2 Use Case: Áp dụng mã giảm giá (Coupon)
*   **Mô tả**: Customer áp mã giảm giá để nhận chiết khấu trực tiếp trên đơn hàng.
*   **Điều kiện tiên quyết**: Giỏ hàng có sản phẩm, đã đăng nhập.
*   **Luồng chính**:
    1. Customer nhập mã giảm giá tại trang thanh toán và nhấn "Áp dụng".
    2. Server gửi AJAX request, kiểm tra trạng thái active, hạn sử dụng, tổng tiền tối thiểu và giới hạn lượt dùng (`used_count < usage_limit`).
    3. Trả về kết quả thành công kèm số tiền giảm giá và tổng tiền mới.
*   **Route liên quan**: `/checkout/apply_coupon` (`CheckoutController@apply_coupon`).
*   **Bảng sử dụng**: `coupons`.
*   **Test ID**: `TC-CUST-02`

### 2.3 Use Case: Đặt hàng COD (Checkout COD)
*   **Mô tả**: Customer tiến hành đặt hàng thanh toán khi nhận hàng.
*   **Điều kiện tiên quyết**: Đã đăng nhập, giỏ hàng không trống.
*   **Luồng chính**:
    1. Customer điền thông tin người nhận, địa chỉ giao hàng và nhấn "Đặt hàng".
    2. Server thực thi một database transaction:
        *   Khóa bảng `products` bằng `FOR UPDATE`.
        *   Kiểm tra số lượng tồn kho (`stock >= quantity`).
        *   Tạo bản ghi trong `orders` và `order_items` lưu trữ thông tin snapshot sản phẩm.
        *   Trừ tồn kho `products.stock`.
        *   Cập nhật tăng lượt sử dụng coupon `coupons.used_count` (nếu có).
        *   Xóa giỏ hàng.
        *   Commit transaction.
    3. Redirect sang trang thông báo đặt hàng thành công.
*   **Luồng lỗi**: Nếu có bất kỳ lỗi nào xảy ra (hết hàng, lỗi DB), hệ thống rollback toàn bộ và báo lỗi chi tiết.
*   **Route liên quan**: `/checkout/place_order` (`CheckoutController@submit`).
*   **Bảng sử dụng**: `orders`, `order_items`, `products`, `coupons`.
*   **Test ID**: `TC-CUST-03`

### 2.4 Use Case: Yêu thích sản phẩm (Wishlist)
*   **Mô tả**: Customer thêm sản phẩm vào danh sách yêu thích và xem lại.
*   **Điều kiện tiên quyết**: Đã đăng nhập.
*   **Luồng chính**:
    1. Customer nhấn nút Yêu thích trên card sản phẩm.
    2. Server insert dòng liên kết trực tiếp vào bảng `wishlists` (sử dụng `INSERT IGNORE`).
    3. Hiển thị thông báo thành công.
*   **Route liên quan**: `/wishlist/add`, `/wishlist/remove` (`WishlistController`).
*   **Bảng sử dụng**: `wishlists`.
*   **Test ID**: `TC-CUST-04`

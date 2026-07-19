# TechPilot - Bảng Kiểm nghiệm Tính năng (FEATURE_CHECKLIST)

Tài liệu này dùng để theo dõi tiến độ và trạng thái kiểm nghiệm của 24 màn hình chính và các chức năng cốt lõi của website TechPilot.

---

## 1. Màn hình Khách hàng - Desktop (12/12)
- `[x] [Tested]` **Trang chủ** (`home/index.php`): Banner hoạt động, Explore Tabs mượt mà, Flash Sale tự động tắt đúng giờ, không hiển thị sản phẩm Apple/Mac/Phone.
- `[x] [Tested]` **Danh sách sản phẩm** (`home/search.php`): Hiển thị sản phẩm theo danh mục và từ khóa chính xác.
- `[x] [Tested]` **Tìm kiếm, lọc, sắp xếp** (`home/search.php`): Tìm kiếm gộp từ khóa và danh mục, lọc khoảng giá, sắp xếp giá/mới nhất hoạt động thật.
- `[x] [Tested]` **Chi tiết sản phẩm** (`product/detail.php`): Gallery ảnh, Box ưu đãi, Tabs thông số kỹ thuật hoạt động, cấm chọn quá tồn kho.
- `[x] [Tested]` **Đăng ký** (`auth/register.php`): Validate server-side mật khẩu $\ge 8$ ký tự, mã hóa password, email duy nhất, role customer.
- `[x] [Tested]` **Đăng nhập** (`auth/login.php`): Verify hash password, regenerate session, xử lý redirect an toàn sau đăng nhập.
- `[x] [Tested]` **Giỏ hàng** (`cart.php`): Giỏ hàng lưu database thật, stepper tăng giảm số lượng kiểm tra stock sản phẩm, kiểm tra coupon.
- `[x] [Tested]` **Thanh toán COD** (`checkout.php`): Chỉ hỗ trợ phương thức COD duy nhất, transaction tạo đơn hàng, trừ stock, chuyển status giỏ hàng.
- `[x] [Tested]` **Đặt hàng thành công** (`checkout-success.php`): Chỉ hiển thị đơn của user hiện tại, hiển thị mã đơn và timeline "Chờ xác nhận".
- `[x] [Tested]` **Lịch sử đơn hàng** (`profile/orders.php`): Chỉ hiển thị đơn hàng thuộc sở hữu của user đăng nhập.
- `[x] [Tested]` **Chi tiết đơn hàng** (`profile/order_detail.php`): Chống IDOR, hiển thị timeline trạng thái thật, tích hợp form Hủy đơn hàng khi trạng thái `pending`.
- `[x] [Tested]` **Tin tức công nghệ** (`post/index.php` và `post/detail.php`): Danh sách tin DB thật, trang chi tiết tin tức tăng views và gợi ý tin liên quan.

---

## 2. Màn hình Quản trị Admin - Desktop (6/6)
- `[x] [Tested]` **Admin Dashboard** (`admin/dashboard.php`): Thống kê doanh thu thật từ các đơn completed, số lượng đơn hàng, khách hàng thực tế.
- `[x] [Tested]` **Admin Danh sách sản phẩm** (`admin/products/index.php`): Quản lý, tìm kiếm, phân trang và ẩn/hiện sản phẩm.
- `[x] [Tested]` **Admin Form thêm/sửa sản phẩm** (`admin/products/edit.php`): Validate logic giá bán, số lượng tồn kho không âm, upload file ảnh an toàn.
- `[x] [Tested]` **Admin Danh sách đơn hàng** (`admin/orders/index.php`): Bộ lọc trạng thái đơn, tìm kiếm mã đơn.
- `[x] [Tested]` **Admin Chi tiết đơn hàng** (`admin/orders/detail.php`): Xem thông tin khách hàng, sản phẩm, và cập nhật trạng thái đơn hàng.
- `[x] [Tested]` **Admin Quản lý khách hàng** (`admin/users/index.php`): Bảng danh sách user, tổng chi tiêu đơn completed, khóa/mở khóa tài khoản khách.

---

## 3. Màn hình Khách hàng - Mobile (6/6)
- `[x] [Tested]` **Trang chủ Mobile**: Explore Tabs cuộn ngang, lưới sản phẩm tối đa 2 cột rộng $\ge 168\text{px}$, ẩn quảng cáo thừa.
- `[x] [Tested]` **Danh sách sản phẩm + Bộ lọc Mobile**: Bottom Sheet lọc hoạt động trơn tru, khóa cuộn nền khi sheet mở.
- `[x] [Tested]` **Chi tiết sản phẩm Mobile**: Fixed Buy Bar chân trang hỗ trợ mua nhanh, accordion đóng mở tiện lợi.
- `[x] [Tested]` **Giỏ hàng Mobile**: Chuyển table thành card dọc dễ nhìn và thao tác.
- `[x] [Tested]` **Thanh toán COD Mobile**: Cột form dọc tinh gọn, nút thanh toán full-width dễ chạm.
- `[x] [Tested]` **Đăng nhập Mobile**: Form ngắn, keyboard-friendly.

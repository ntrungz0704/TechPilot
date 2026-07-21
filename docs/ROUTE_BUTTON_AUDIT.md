# Kiểm toán Tuyến đường và Nút bấm (ROUTE_BUTTON_AUDIT)

Báo cáo kiểm toán toàn bộ hệ thống định tuyến (Routing), liên kết (Links), nút bấm (Buttons) và form (Forms) trong ứng dụng TechPilot.

## 1. Danh sách định tuyến được đăng ký

Hệ thống sử dụng tệp [public/index.php](file:///d:/TechPilot/public/index.php) để đăng ký định tuyến tĩnh/regex, và cơ chế Auto-Dispatch làm fallback:

### 1.1 Định tuyến đăng ký tĩnh/regex
- **POST** `/checkout/apply_coupon` -> `CheckoutController@apply_coupon`
- **POST** `/product/review` -> `ProductController@review`
- **POST** `/profile/cancel_order` -> `ProfileController@cancel_order`
- **GET/POST** `/admin/categories` & CRUD actions -> `AdminCategoryController`
- **GET/POST** `/admin/brands` & CRUD actions -> `AdminBrandController`
- **GET/POST** `/admin/products` & CRUD actions -> `AdminProductController`
- **GET/POST** `/admin/orders` & CRUD actions -> `AdminOrderController`
- **GET/POST** `/admin/users` & status actions -> `AdminUserController`
- **GET/POST** `/admin/reviews` & review approval -> `AdminReviewController`
- **GET/POST** `/admin/flash-sales` & CRUD actions -> `AdminFlashSaleController`
- **GET/POST** `/admin/coupons` & CRUD actions -> `AdminCouponController`
- **GET/POST** `/admin/banners` & CRUD actions -> `AdminBannerController`
- **GET/POST** `/admin/posts` & CRUD actions -> `AdminPostController`
- **GET** `/build-pc`, `/pc-builder/products`, `/pc-builder/analysis` -> `PcBuilderController`
- **POST** `/pc-builder/add-to-cart` -> `PcBuilderController@addToCart`

### 1.2 Định tuyến Auto-Dispatch
- `/` -> `HomeController@index`
- `/home/search` -> `HomeController@search`
- `/product/detail/{slug}` -> `ProductController@detail`
- `/auth/register` -> `AuthController@register`
- `/auth/login` -> `AuthController@login`
- `/auth/logout` -> `AuthController@logout`
- `/cart` -> `CartController@index`
- `/checkout` -> `CheckoutController@index`
- `/profile` -> `ProfileController@index`
- `/post` -> `PostController@index`

---

## 2. Kết quả kiểm toán Liên kết & Nút bấm trống (Dead Links / Empty Buttons)

| Dòng | File | Nhãn nút / Liên kết | Mô tả / Hành vi kiểm toán | Trạng thái | Giải pháp |
|---|---|---|---|---|---|
| L-12 | `admin/dashboard.php` | Nút PC Builder Widget | Trỏ tới `#`, dùng làm nút trang trí widget | `PASS` | Không cần thay đổi |
| L-8 | `layouts/partials/topbar.php` | Ticker thông báo | Trỏ tới `#`, dùng hiển thị tin tức chạy chữ | `PASS` | Không cần thay đổi |
| L-40 | `post/detail.php` | Nút "Sao chép link" | Dùng `href="#"` kèm `onclick="event.preventDefault(); ..."` để copy | `PASS` | Đã ngăn mặc định, thực hiện JS |
| L-34 | `layouts/footer.php` | Logo chân trang | Trỏ tới trang chủ thông qua helper `url()` | `PASS` | Hợp lệ |

---

## 3. Kiểm toán Form Actions & Bảo mật POST

Tất cả các form thực hiện thay đổi dữ liệu (POST request) đều được kiểm tra:
1. **CSRF Protection**: Có chứa `<input type="hidden" name="csrf_token" value="...">` (hoặc tên tương thích `_csrf`).
2. **Method**: Sử dụng duy nhất `POST` cho các thao tác tác động dữ liệu (đặt hàng, đăng ký, cập nhật trạng thái đơn, hủy đơn, áp mã giảm giá). Không sử dụng `GET` để xóa hoặc thay đổi trạng thái.

**Kết quả kiểm tra Form Actions**:
- Form đăng ký (`auth/register`): Trỏ đúng action rỗng (tự submit lên chính nó). Có validate CSRF. -> `PASS`
- Form đăng nhập (`auth/login`): Trỏ đúng action rỗng. Có validate CSRF. -> `PASS`
- Form đặt hàng (`checkout/order`): Có nút đặt hàng submit form POST an toàn. Có validate CSRF. -> `PASS`
- Form áp mã giảm giá (`checkout/apply_coupon`): Submit qua AJAX POST đến `/checkout/apply_coupon` kèm token CSRF. -> `PASS`
- Form admin cập nhật trạng thái đơn hàng (`admin/orders/update_status/{id}`): Dùng POST, không dùng GET. Có validate CSRF. -> `PASS`

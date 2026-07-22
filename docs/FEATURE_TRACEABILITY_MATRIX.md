# Ma trận giám sát tính năng (FEATURE_TRACEABILITY_MATRIX)

Bảng ma trận ánh xạ các Use Case nghiệp vụ từ tài liệu thiết kế sang mã nguồn và trạng thái kiểm thử thực tế trên hệ thống TechPilot dưới sơ đồ **ERD V2 – 19 bảng chính thức**.

## Ma trận giám sát

| ID | Actor | Use Case | Route | Controller | Model | View | Table | Trạng thái | Bằng chứng |
|---|---|---|---|---|---|---|---|---|---|
| **F01** | Khách vãng lai | Đăng ký tài khoản | `auth/register` | AuthController | User | auth/register | users | `PASS` | Kiểm thử thành công qua Form đăng ký |
| **F02** | Khách vãng lai | Đăng nhập | `auth/login` | AuthController | User | auth/login | users | `PASS` | Đăng nhập thành công, chuyển hướng đúng role |
| **F03** | Khách hàng | Đăng xuất | `auth/logout` | AuthController | - | - | - | `PASS` | Hủy session và chuyển hướng về trang chủ |
| **F04** | Khách vãng lai | Xem danh sách sản phẩm | `product` hoặc `home` | ProductController | Product | product/index | products | `PASS` | Hiển thị đầy đủ sản phẩm active kèm phân trang |
| **F05** | Khách vãng lai | Xem chi tiết sản phẩm | `product/detail/{slug}` | ProductController | Product | product/detail | products, product_images, reviews | `PASS` | Hiển thị chi tiết, specs và thư viện ảnh |
| **F06** | Khách vãng lai | Xem theo danh mục | `category/{slug}` | ProductController | Product | product/category | categories, products | `PASS` | Lọc sản phẩm chính xác theo category_id |
| **F07** | Khách vãng lai | Xem theo thương hiệu | `brand/{slug}` | ProductController | Product | product/brand | brands, products | `PASS` | Lọc sản phẩm chính xác theo brand_id |
| **F08** | Khách vãng lai | Tìm kiếm sản phẩm | `home/search` | HomeController | Product | home/search | products, categories | `PASS` | Tìm kiếm chính xác theo products.name và alias |
| **F09** | Khách vãng lai | Lọc & Sắp xếp | `home/search` | HomeController | Product | home/search | products | `PASS` | Kết hợp AND các bộ lọc giá, thương hiệu, tồn kho |
| **F10** | Khách vãng lai | Xem chiến dịch Flash Sale | `home/search?promo=1` | HomeController | Product | home/search | flash_sales, products | `PASS` | Hiển thị sản phẩm giảm giá trong đợt sale |
| **F11** | Khách vãng lai | Đọc bài viết tin tức | `post`, `post/detail/{slug}` | PostController | Post | post/index, post/detail | posts | `PASS` | Đọc tin tức, tăng lượt xem views an toàn |
| **F12** | Khách vãng lai | Chatbot AI hỗ trợ | `chatbot` | ChatbotController | Product, Post | - | products, posts | `PASS` | Chatbot stateless đọc dữ liệu thật từ DB gợi ý |
| **F13** | Khách hàng | Quản lý giỏ hàng | `cart`, `cart/add`, `cart/update` | CartController | - | cart | carts, cart_items | `PASS` | Thêm/sửa/xóa giỏ hàng đồng bộ MySQL thật |
| **F14** | Khách hàng | Đặt hàng COD | `checkout` | CheckoutController | Order | checkout | orders, order_items, products | `PASS` | Thực hiện transaction an toàn, trừ stock |
| **F15** | Khách hàng | Theo dõi đơn hàng | `profile/orders`, `profile/order_detail` | ProfileController | Order | profile/orders, profile/order_detail | orders, order_items | `PASS` | Xem lịch sử mua hàng, chặn IDOR |
| **F16** | Khách hàng | Hủy đơn hàng | `profile/cancel_order` | ProfileController | Order | - | orders, products | `PASS` | Hủy đơn pending/confirmed, hoàn trả stock |
| **F17** | Khách hàng | Yêu thích sản phẩm | `wishlist` | WishlistController | Wishlist | profile/wishlist | wishlists | `PASS` | Thêm/xóa sản phẩm yêu thích của riêng user |
| **F18** | Khách hàng | Đánh giá & bình luận | `product/review` | ProductController | Review | product/detail | reviews | `PASS` | Gửi đánh giá 1-5 sao, hiển thị điểm TB |
| **F19** | Khách hàng | Quản lý hồ sơ cá nhân | `profile` | ProfileController | User | profile/index | users | `PASS` | Thay đổi tên, sđt, địa chỉ, đổi mật khẩu |
| **F20** | Khách hàng | Xem thông báo | `profile/notifications` | ProfileController | Notification | profile/notifications | notifications | `PASS` | Đọc và ghi nhận trạng thái thông báo từ DB thật |
| **F21** | Khách hàng | Yêu cầu đổi trả | `profile/return` | ProfileController | ReturnRequest | profile/return | return_requests, return_items | `PASS` | Gửi và quản lý trạng thái yêu cầu đổi trả trên DB thật |
| **F22** | Khách vãng lai | So sánh sản phẩm | `compare` | CompareController | - | compare | - | `PASS` | So sánh tối đa 4 sản phẩm qua Session |
| **F23** | Khách vãng lai | Sản phẩm gần đây | `product/detail` | ProductController | - | - | - | `PASS` | Lưu vết sản phẩm đã xem qua Session |
| **F24** | Quản trị viên | Dashboard thống kê | `admin`, `admin/dashboard` | AdminController | - | admin/dashboard | orders, users, products | `PASS` | Thống kê doanh thu completed, đơn hàng, biểu đồ |
| **F25** | Quản trị viên | CRUD Sản phẩm | `admin/products` | AdminProductController | Product | admin/products | products, product_images | `PASS` | Quản lý sản phẩm, upload ảnh WebP |
| **F26** | Quản trị viên | CRUD Danh mục | `admin/categories` | AdminCategoryController | - | admin/categories | categories | `PASS` | Quản lý danh mục, chặn xóa danh mục có SP |
| **F27** | Quản trị viên | CRUD Thương hiệu | `admin/brands` | AdminBrandController | - | admin/brands | brands | `PASS` | Quản lý thương hiệu |
| **F28** | Quản trị viên | CRUD Flash Sale | `admin/flash-sales` | AdminFlashSaleController | - | admin/flash-sales | flash_sales, flash_sale_items | `PASS` | Cấu hình chiến dịch và gán sản phẩm giảm giá thật |
| **F29** | Quản trị viên | CRUD Banner | `admin/banners` | AdminBannerController | - | admin/banners | banners | `PASS` | Quản lý banner quảng cáo |
| **F30** | Quản trị viên | CRUD Bài viết | `admin/posts` | AdminPostController | - | admin/posts | posts | `PASS` | Quản lý tin tức |
| **F31** | Quản trị viên | CRUD Đơn hàng | `admin/orders` | AdminOrderController | - | admin/orders | orders, order_items | `PASS` | Cập nhật trạng thái đơn hàng theo luồng |
| **F32** | Quản trị viên | CRUD Khách hàng | `admin/users` | AdminUserController | - | admin/users | users | `PASS` | Khóa/mở khóa tài khoản khách hàng |
| **F33** | Quản trị viên | CRUD Mã giảm giá | `admin/coupons` | AdminCouponController | - | admin/coupons | coupons | `PASS` | Quản lý mã giảm giá |
| **F34** | Quản trị viên | Duyệt đánh giá | `admin/reviews` | AdminReviewController | - | admin/reviews | reviews | `PASS` | Duyệt/ẩn đánh giá từ khách hàng |

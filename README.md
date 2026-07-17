# TechPilot – Website bán đồ công nghệ

Ứng dụng thương mại điện tử xây dựng bằng PHP MVC thuần, MySQL/MariaDB, HTML, CSS và JavaScript. Giao diện dùng bộ màu thương hiệu TechPilot, hỗ trợ responsive từ mobile đến desktop.

## Chức năng hiện có

- Trang chủ, danh mục, tìm kiếm, lọc và sắp xếp sản phẩm.
- Trang chi tiết sản phẩm, sản phẩm liên quan và trạng thái tồn kho.
- Đăng ký, đăng nhập, đăng xuất an toàn.
- Giỏ hàng phía máy chủ, cập nhật số lượng và xóa sản phẩm.
- Checkout COD, kiểm tra lại giá/tồn kho và tạo đơn trong transaction.
- Schema V2 cho vai trò, biến thể, kho, đơn hàng, thanh toán, vận chuyển, đánh giá, wishlist, flash sale, thông báo, so sánh và đổi trả.

## Cấu trúc chính

```text
techpilot/
├── app/
│   ├── controllers/
│   ├── models/
│   ├── services/
│   ├── views/
│   └── core/
├── config/
├── database/
│   ├── schema.sql
│   └── README.md
└── public/
    ├── index.php
    ├── router.php
    └── assets/
```

## Cài đặt

### 1. Khởi tạo database

`database/schema.sql` là bộ cài mới có dữ liệu mẫu và sẽ xóa database `techpilot` cũ trước khi tạo lại. Không chạy trực tiếp trên database production đang có dữ liệu.

```bash
mysql -u root -p < database/schema.sql
```

Quyết định thiết kế và chiến lược migrate an toàn được ghi tại `database/README.md`.

### 2. Cấu hình kết nối

Trên máy local, sao chép `config/database.local.example.php` thành
`config/database.local.php`, sau đó điền tài khoản MySQL. File local này đã được
Git bỏ qua nên không làm lộ mật khẩu.

Ứng dụng cũng hỗ trợ các biến môi trường sau; biến môi trường có độ ưu tiên cao
hơn file cấu hình local:

```text
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=techpilot
DB_USERNAME=root
DB_PASSWORD=
DB_CHARSET=utf8mb4
```

Không commit mật khẩu database vào repository.

### 3. Chạy bằng Laragon

Document root của virtual host phải trỏ thẳng vào thư mục `techpilot/public`.
Không duy trì một bản sao riêng trong `D:\laragon\www`, vì bản sao sẽ nhanh chóng
lệch với mã nguồn đang chỉnh sửa.

Sau khi Laragon reload Apache, mở domain virtual host của dự án. File
`public/.htaccess` sẽ chuyển các URL như `/home/search` và `/cart` vào router.

### 4. Chạy bằng PHP built-in server

Từ thư mục `public`:

```bash
php -S 127.0.0.1:8000 router.php
```

Sau đó mở `http://127.0.0.1:8000`.

Với Apache/Nginx, đặt document root vào thư mục `public` và bật rewrite URL.

## Các route chính

| Chức năng | URL |
|---|---|
| Trang chủ | `/` |
| Tìm kiếm và lọc | `/home/search` |
| Chi tiết sản phẩm | `/product/detail/{slug}` |
| Giỏ hàng | `/cart` |
| Thanh toán | `/checkout` |
| Đăng nhập | `/auth/login` |
| Đăng ký | `/auth/register` |

## Nguyên tắc an toàn

- PDO prepared statements cho truy vấn có dữ liệu người dùng.
- Mật khẩu dùng `password_hash()` và `password_verify()`.
- Mọi form POST dùng CSRF token.
- Giá, tồn kho và tổng đơn được tính lại ở server khi checkout.
- Tạo đơn, trừ tồn và ghi lịch sử diễn ra trong một transaction có khóa bản ghi.
- Khi chưa kết nối được database, catalog chỉ hiển thị dữ liệu xem trước; hệ thống không tạo giỏ hàng hay đơn hàng giả.

## Kiểm tra nhanh

```bash
php -l app/models/Product.php
php -l app/models/Order.php
```

Trước khi bàn giao môi trường thật, cần kiểm thử lại toàn bộ luồng đăng ký → đăng nhập → thêm giỏ → checkout trên database đã import.

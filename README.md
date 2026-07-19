# TechPilot - Website Thương mại Điện tử Thiết bị Công nghệ

TechPilot là website thương mại điện tử chuyên bán các sản phẩm máy tính, laptop Windows, PC lắp ráp, màn hình, linh kiện PC, thiết bị mạng, gaming gear và phụ kiện máy tính chính hãng. Dự án được triển khai bằng ngôn ngữ PHP thuần theo mô hình kiến trúc MVC (Model-View-Controller) kết hợp cơ sở dữ liệu MySQL/MariaDB.

---

## 1. Yêu cầu Hệ thống
*   **PHP:** Phiên bản 8.0 trở lên.
*   **MySQL / MariaDB:** Phiên bản 10.4 trở lên.
*   **Web Server:** Apache (có bật mod_rewrite) hoặc PHP Built-in Web Server.

---

## 2. Hướng dẫn Cài đặt & Chạy ứng dụng

### Bước 1: Khởi tạo Cơ sở Dữ liệu
1. Mở MySQL client (phpMyAdmin, DBeaver, Heidisql hoặc dòng lệnh CLI).
2. Tạo cơ sở dữ liệu mới và import file schema tại:
   `database/schema.sql`
   *(Lưu ý: Schema này đã được cập nhật sạch bóng 100% các sản phẩm Mac/iPhone theo đúng phạm vi kinh doanh của cửa hàng).*

### Bước 2: Cấu hình Kết nối CSDL
Mở file `config/database.php` và cập nhật thông số kết nối MySQL của bạn:
```php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'techpilot');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### Bước 3: Chạy ứng dụng cục bộ
Sử dụng PHP Built-in Web Server từ thư mục gốc của dự án:
```bash
php -S 127.0.0.1:8000 router.php
```
Mở trình duyệt và truy cập: [http://127.0.0.1:8000](http://127.0.0.1:8000).

---

## 3. Tài khoản Thử nghiệm (Development Credentials)

Dự án cung cấp sẵn hai tài khoản thử nghiệm đã được seed sẵn trong CSDL:

### 💼 Tài khoản Quản trị (Admin)
*   **Email:** `ntrungz0704@gmail.com` (hoặc `admin@techpilot.vn`)
*   **Mật khẩu:** `admin123`
*   **Vai trò:** Quản trị viên (Xem thống kê doanh thu, quản lý sản phẩm, đơn hàng và khách hàng).

### 🛒 Tài khoản Khách hàng (Customer)
*   **Email:** `customer@gmail.com`
*   **Mật khẩu:** `admin123`
*   **Vai trò:** Khách mua hàng.

---

## 4. Cấu trúc Thư mục chính
```text
TechPilot/
├── app/
│   ├── controllers/      # Bộ điều hướng (Controllers)
│   ├── core/             # Nhân hệ thống (Router, Controller, Helpers)
│   ├── models/           # Lớp dữ liệu (Models)
│   └── views/            # Giao diện hiển thị (Views)
├── config/               # Tệp cấu hình ứng dụng
├── database/             # File schema.sql và dữ liệu mẫu
├── public/               # Tài nguyên tĩnh (CSS, JS, Images) và index.php
└── router.php            # File định tuyến cho server local
```

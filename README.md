# TechPilot – Website bán đồ công nghệ

Ứng dụng thương mại điện tử xây dựng bằng PHP MVC thuần, MySQL/MariaDB, HTML, CSS và JavaScript. Giao diện dùng bộ màu thương hiệu TechPilot, hỗ trợ responsive từ mobile đến desktop.

## Chức năng hiện có

- Trang chủ, danh mục, tìm kiếm, lọc và sắp xếp sản phẩm.
- Trang chi tiết sản phẩm, sản phẩm liên quan và trạng thái tồn kho.
- Đăng ký, đăng nhập, đăng xuất an toàn.
- Giỏ hàng phía máy chủ, cập nhật số lượng và xóa sản phẩm.
- Checkout COD, kiểm tra lại giá/tồn kho và tạo đơn trong transaction.
- Chuẩn hóa 15 bảng cơ sở dữ liệu chính thức theo ERD của giảng viên, loại bỏ hoàn toàn các thực thể dư thừa như variants, warehouses, notifications, return requests.
- Hỗ trợ tối ưu hóa Responsive toàn diện cho mọi thiết bị di động, đặc biệt là khung hiển thị di động chuẩn 440×956.

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

---

## 👨‍💻 Hướng dẫn sử dụng Git & GitHub (Dành cho thành viên mới bắt đầu)

Dưới đây là hướng dẫn từng bước cực kỳ đơn giản để 4 thành viên trong nhóm (**trung**, **dinh**, **kim**, **hieu**) làm việc chung mà không lo bị ghi đè, mất code hoặc lỗi Git.

### 1. Lần đầu tiên lấy code về máy (Clone)
Mở Terminal/PowerShell tại thư mục bạn muốn lưu code (ví dụ `D:\`) và chạy:
```bash
git clone https://github.com/ntrungz0704/TechPilot.git
cd TechPilot
```

### 2. Luồng làm việc hàng ngày của mỗi thành viên (Quy trình 5 bước)

#### Bước 2.1: Chuyển sang nhánh cá nhân của bạn
Trước khi gõ bất kỳ dòng code nào, hãy chắc chắn bạn đang đứng ở nhánh của chính mình để không đè vào code người khác:
* **Bạn Trung**: `git checkout trung`
* **Bạn Dinh**: `git checkout dinh`
* **Bạn Kim**: `git checkout kim`
* **Bạn Hieu**: `git checkout hieu`

*(Nếu muốn kiểm tra xem mình đang đứng ở nhánh nào, gõ lệnh: `git branch`)*

#### Bước 2.2: Cập nhật code mới nhất từ nhóm về máy
Mỗi ngày trước khi code, hãy lấy những phần code mới nhất mà các bạn khác đã gộp vào nhánh chung `develop` về máy mình:
```bash
git pull origin develop
```

#### Bước 2.3: Viết code và kiểm tra các file đã sửa
Sau khi bạn code xong hoặc sửa lỗi xong, gõ lệnh này để xem danh sách các file bạn đã chỉnh sửa:
```bash
git status
```

#### Bước 2.4: Đóng gói code trên máy của bạn (Commit)
Để lưu lại những gì bạn vừa làm vào lịch sử máy của bạn, chạy 2 lệnh sau:
```bash
# Đưa các file đã sửa vào trạng thái chờ đóng gói (Lưu ý dấu chấm .)
git add .

# Đóng gói và viết ghi chú ngắn gọn bạn đã làm gì
git commit -m "Ghi chu ngan gon viec ban da lam (vi du: sua giao dien header)"
```

#### Bước 2.5: Đẩy code lên GitHub (Push)
Để gửi gói code từ máy của bạn lên trên GitHub, hãy chạy lệnh tương ứng với tên của bạn:
* **Bạn Trung**: `git push origin trung`
* **Bạn Dinh**: `git push origin dinh`
* **Bạn Kim**: `git push origin kim`
* **Bạn Hieu**: `git push origin hieu`

---

### 3. Cách gộp code của bạn vào nhánh chung (Tạo Pull Request - PR)
Khi bạn đã hoàn thành một tính năng ở nhánh cá nhân và muốn gộp vào nhánh chung `develop`:
1. Mở trang web GitHub của dự án: [GitHub TechPilot](https://github.com/ntrungz0704/TechPilot).
2. Bạn sẽ thấy một nút màu vàng hiện lên có chữ **"Compare & pull request"**. Hãy nhấp vào đó.
3. Chọn gộp code:
   * Ô bên trái (base): Chọn `develop` (nhánh chung của nhóm).
   * Ô bên phải (compare): Chọn nhánh của bạn (ví dụ: `dinh`, `kim`...).
4. Nhập tiêu đề mô tả bạn đã làm gì rồi nhấn **"Create pull request"**.
5. Nhờ trưởng nhóm (Trung) kiểm tra code và bấm nút **"Merge pull request"** để gộp code của bạn vào nhánh chung `develop` an toàn!


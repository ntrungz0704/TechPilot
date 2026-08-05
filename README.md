# TechPilot — Hệ thống thương mại điện tử công nghệ & Trợ lý AI 4.0

Ứng dụng web thương mại điện tử chuyên thiết bị công nghệ: Laptop, PC lắp sẵn, Linh kiện, Màn hình & Gaming Gear. Xây dựng bằng **PHP MVC thuần** (không dùng framework nặng), **MySQL/MariaDB**, **HTML/Vanilla CSS/JavaScript**.

Website tích hợp hệ thống **AI Multi-Provider Engine 4.0** (Gemini + Groq + Qwen Cloud), **Trợ lý AI tư vấn 5 bước động** và **Động cơ So sánh sản phẩm chuyên sâu theo Persona**.

**Tính năng chính:**

- **Khách hàng (Customer):** Duyệt catalog 650+ sản phẩm, tìm kiếm thông minh, lọc nâng cao, So sánh sản phẩm theo Persona (`/compare`), Trợ lý AI tư vấn 5 bước động (`/ai-assistant`), Giỏ hàng, Đặt hàng COD/VNPay Sandbox, PC Builder (tính công suất PSU 30% headroom), Tin tức, Chatbot nổi Q&A, Danh sách yêu thích (Wishlist).
- **Quản trị viên (Admin):** Dashboard thống kê doanh thu, quản lý Sản phẩm, Danh mục, Thương hiệu, Tồn kho (Inventory Audit Logs), Đơn hàng, Người dùng, Flash Sale, Mã giảm giá (Coupon), Banner, Bài viết, Đánh giá.
- **Hệ thống AI Multi-Provider:** Tự động Failover 3 cấp: Gemini API $\rightarrow$ Groq Cloud API $\rightarrow$ Qwen Cloud API.

**Repository chính thức:** <https://github.com/ntrungz0704/TechPilot>

---

## 1. Yêu cầu hệ thống

| Công cụ          | Phiên bản tối thiểu      | Ghi chú                          |
| ---------------- | ------------------------- | -------------------------------- |
| PHP              | 8.0+ *(Khuyên dùng 8.1+)*  | Đã thử nghiệm thành công trên PHP 8.5.5 |
| MySQL / MariaDB  | 5.7+ / 10.4+              | Port mặc định **3306**          |
| Git              | 2.x                       | Quản lý mã nguồn                 |
| Node.js          | 18+ *(Tùy chọn)*          | Chỉ cần nếu chạy browser test  |

**PHP extensions — phân loại theo module:**

| Extension    | Module         | Mức độ    | Ghi chú                             |
| ------------ | -------------- | --------- | ------------------------------------ |
| PDO          | Core           | Bắt buộc  | Kết nối database                     |
| pdo_mysql    | Core           | Bắt buộc  | Driver MySQL cho PDO                 |
| json         | Core           | Bắt buộc  | Mặc định có từ PHP 8.0              |
| mbstring     | Core           | Bắt buộc  | Xử lý chuỗi tiếng Việt               |
| fileinfo     | Admin Upload   | Bắt buộc  | Upload ảnh sản phẩm & banner        |
| curl         | AI / VNPay     | Bắt buộc  | Gọi API AI (Gemini/Groq/Qwen) & VNPay|
| openssl      | AI / VNPay     | Bắt buộc  | Mã hóa TLS/HTTPS                     |

**Bật extension trong `php.ini` CLI:**

Tìm file `php.ini` đang dùng:

```powershell
php --ini
```

Mở file (thường `C:\php\php.ini` hoặc `C:\xampp\php\php.ini`), bỏ dấu `;` ở đầu các dòng:

```ini
extension=curl
extension=fileinfo
extension=mbstring
extension=openssl
extension=pdo_mysql
```

Xác nhận danh sách module đã bật:

```powershell
php -m | findstr /I "curl fileinfo mbstring openssl PDO pdo_mysql"
```

---

## 2. Quy trình cài đặt chi tiết từ đầu (Step-by-step Clone & Setup)

Bất kỳ thành viên mới nào hoặc khi tải dự án về máy mới đều thực hiện lần lượt theo đúng các bước bên dưới:

### Bước 1: Clone Repository

```powershell
git clone https://github.com/ntrungz0704/TechPilot.git
cd TechPilot
```

Kiểm tra trạng thái git:

```powershell
git branch -a
git status
```

---

### Bước 2: Cấu hình Database Local

Sao chép file cấu hình database mẫu:

```powershell
Copy-Item config/database.local.example.php config/database.local.php
```

Mở file `config/database.local.php` và cập nhật thông số kết nối MySQL của máy bạn:

```php
<?php
return [
    'host'     => '127.0.0.1',
    'port'     => '3306',       // Port MySQL local
    'database' => 'techpilot',
    'username' => 'root',
    'password' => '',           // Mật khẩu MySQL local của bạn
    'charset'  => 'utf8mb4',
];
```

> ⚠️ **Lưu ý an toàn:** File `config/database.local.php` nằm trong `.gitignore` — **tuyệt đối không commit file này**.

---

### Bước 3: Cấu hình File Môi Trường `.env`

Sao chép file `.env.example`:

```powershell
Copy-Item .env.example .env
```

Mở `.env` và điền các cấu hình cần thiết:

```env
# === Ứng dụng ===
APP_ENV=development
APP_URL=http://127.0.0.1:8000

# === Cơ sở dữ liệu ===
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=techpilot
DB_USER=root
DB_PASS=

# === AI Multi-Provider API Keys (Tùy chọn cho tính năng AI) ===
GEMINI_API_KEY=
GROQ_API_KEY=
QWEN_API_KEY=

# === VNPay Sandbox Credential (Tùy chọn cho thanh toán online) ===
VNPAY_TMN_CODE=
VNPAY_HASH_SECRET=
VNPAY_RETURN_URL=http://127.0.0.1:8000/payment/vnpay-return
VNPAY_IPN_URL=
```

Quy tắc môi trường VNPay:

- Local development: đặt `APP_ENV=development` và để trống cả
  `VNPAY_TMN_CODE`, `VNPAY_HASH_SECRET` để dùng simulator tích hợp.
- Development có đủ credential: ứng dụng dùng VNPay Sandbox thật và route
  simulator local trả về `404`.
- Production: đặt `APP_ENV=production` và cung cấp đủ hai credential. Nếu thiếu,
  VNPay bị vô hiệu hóa an toàn; COD vẫn hoạt động và route simulator luôn trả
  về `404`.
- Không dùng credential production trong simulator hoặc commit secret vào Git.

---

### Bước 4: Khởi Tạo Cơ Sở Dữ Liệu & Import Data Mẫu

> ⚠️ **Lưu ý bảo mật:** File `database/seed_dev.sql` chứa tài khoản dev test với mật khẩu mẫu. **CHỈ DÙNG CHO LOCAL DEVELOPMENT**, không deploy lên production.

Mở MySQL CLI hoặc phpMyAdmin để tạo database `techpilot`:

```powershell
mysql -h 127.0.0.1 -P 3306 -u root -p --default-character-set=utf8mb4
```

Trong prompt MySQL:

```sql
CREATE DATABASE IF NOT EXISTS techpilot CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE techpilot;
SOURCE database/seed_dev.sql;
```

Hoặc chạy lệnh 1 dòng trên PowerShell / CMD:

```cmd
mysql -h 127.0.0.1 -P 3306 -u root -p --default-character-set=utf8mb4 techpilot < database\seed_dev.sql
```

Sau khi import thành công, database sẽ có **28 bảng vật lý**, 651 sản phẩm công nghệ thực tế và 30 logo thương hiệu chính hãng.

**Tài khoản dev mặc định** (chỉ dùng local dev):

| Role | Email | Mật khẩu |
|------|-------|----------|
| Admin | `admin@techpilot.vn` | `TechPilot@Dev2026!` |
| Customer | `dev@techpilot.vn` | `TechPilot@Dev2026!` |

> 💡 **Ghi chú:** Nếu chỉ cần tạo cấu trúc bảng (không có dữ liệu mẫu), dùng `database/schema.sql` thay vì `seed_dev.sql`.

---

### Bước 5: Chạy Web Server Local & Kiểm Tra Trạng Thái Cài Đặt

Khởi chạy PHP Built-in Server tại thư mục gốc dự án qua file `router.php`:

```powershell
php -S 127.0.0.1:8000 router.php
```

Mở một tab Terminal mới và chạy tập lệnh tự động kiểm tra cài đặt:

```powershell
php scripts/verify-install.php
```

Nếu kết quả xuất hiện:
```
══════════════════════════════════
  PASS: 121
  WARN: 1
  FAIL: 0
══════════════════════════════════
✅ Cài đặt hợp lệ. Website sẵn sàng chạy.
```
Website đã sẵn sàng để truy cập tại: **`http://127.0.0.1:8000`**

---

### Bước 6: Chạy PHP Migration an toàn

Kiểm tra migration nào đã chạy và migration nào còn pending:

```powershell
php scripts/database/migrate.php --status
```

Chỉ chạy những migration chưa có trong bảng `migrations`:

```powershell
php scripts/database/migrate.php
```

Runner ghi ledger sau khi `up()` trả về thành công, tự bỏ qua migration đã ghi,
khóa chống hai runner chạy đồng thời và dừng ngay tại migration đầu tiên bị lỗi.

`database/seed_dev.sql` đã baseline toàn bộ migration có trong snapshot. Với database
legacy được tạo trước khi runner có ledger, chỉ sau khi đã backup và xác minh
schema/data khớp seed hiện tại mới chạy đúng một lần:

```powershell
php scripts/database/migrate.php --baseline-existing
```

Lệnh baseline **không gọi `up()`**; nó chỉ ghi tên các migration hiện có vào
ledger để ngăn dữ liệu cũ bị chạy lại.

---

## 3. Kiến trúc 3 Chức Năng AI Tách Riêng (Decoupled AI Architecture)

Hệ thống TechPilot phân tách độc lập 3 chức năng AI để đảm bảo đúng trải nghiệm người dùng và logic nghiệp vụ:

### A. Chatbot Nổi (Floating Chatbot ở góc màn hình)
- **Mục đích:** Hỏi đáp tự nhiên Q&A, trả lời chính sách bảo hành/giao hàng, giải thích thông số kỹ thuật (RAM, CPU, VGA) và đưa ra nút bấm điều hướng (CTA) sang trang `/ai-assistant` hoặc `/compare`.
- **Tuyệt đối không chứa:** Khảo sát 5 bước, bảng gợi ý sản phẩm 3 card hay bảng so sánh kéo thả trong khung chat nhỏ.

### B. AI Tư Vấn Chọn Máy Khảo Sát 5 Bước (`/ai-assistant`)
- **Khảo sát động 5 bước thay đổi theo danh mục:**
  - **Laptop:** Tiêu chuẩn mỏng nhẹ, pin, hiệu năng, màn hình.
  - **PC Lắp Sẵn:** Tiêu chí CPU, GPU, tản nhiệt, khả năng nâng cấp. (**KHÔNG HIỂN THỊ Pin lâu, Mỏng nhẹ di động, Trọng lượng dưới 1.5kg**).
  - **Màn hình & Gaming Gear:** Tiêu chí tần số quét (144Hz-240Hz+), độ chuẩn màu (sRGB/DCI-P3), chuẩn ngàm VESA, loại gear (chuột, bàn phím, tai nghe).
- **Thuật toán chấm điểm định hướng (Deterministic 100-Point Scoring):** Lọc cứng ngân sách min/max (6 mã budget: `under_10m`, `10_15m`, `15_20m`, `20_25m`, `25_35m`, `over_35m`), tính điểm khớp mục đích (35đ), ưu tiên (25đ), phần mềm (15đ), giá trị P/P (15đ), thương hiệu (5đ), độ đầy đủ dữ liệu (5đ). Không dùng hàm ngẫu nhiên.
- **Trả về 3 vai trò chiến thắng rõ ràng:** `PHÙ HỢP NHẤT`, `ĐÁNG TIỀN NHẤT`, `HIỆU NĂNG CAO NHẤT`.

### C. Động Cơ So Sánh Sản Phẩm Chuyên Sâu Theo Persona (`/compare`)
- **Khóa danh mục trước khi so sánh (Category-First Workflow):** Người dùng chọn loại sản phẩm (`[Laptop] [PC Lắp Sẵn] [Màn hình] [CPU] [VGA] [PSU] [RAM] [SSD] [Gear]`). Ô tìm kiếm chỉ trả về các sản phẩm thuộc danh mục đã chọn (Gõ `PC` khi chọn `PC Lắp Sẵn` chỉ trả PC Gaming, **tuyệt đối không trả về Nguồn PSU**).
- **Khóa danh mục cứng:** Ngay khi thêm 1 sản phẩm vào bảng so sánh, danh mục được khóa lại để đảm bảo so sánh 100% cùng loại.
- **Ma trận Persona & Tiêu chí ngạch cứng (Hard Requirements):** Cho phép nhập hạn mức giá tối đa, RAM tối thiểu, SSD tối thiểu, tần số quét tối thiểu. Sản phẩm vi phạm bị đánh dấu `NOT_ELIGIBLE` và tô đỏ lý do.
- **Bảng thông số sạch (Zero Metadata Leakage):** Loại bỏ hoàn toàn các key nội bộ (`compatibility`, `use_cases`, `schema_version`...). Highlight tự động ô có giá trị tốt nhất màu xanh lá (`#10B981`).
- **Báo cáo AI Multi-Provider:** AI viết nhận xét lý do khuyên dùng theo Persona, đối tượng nên mua và các điểm cần đánh đổi khi chọn giữa các mẫu.

---

## 4. Tài Khoản Quản Trị & Đăng Nhập Mẫu

Trang quản trị Admin: **`http://127.0.0.1:8000/admin`** hoặc click nút "Quản trị" trên Navbar khi đăng nhập tài khoản Admin.

| Tài khoản | Email | Mật khẩu | Quyền hạn |
|---|---|---|---|
| Admin | `admin@techpilot.vn` | `Admin@123` | Quản trị toàn bộ hệ thống |
| Customer | `user@techpilot.vn` | `User@123` | Khách hàng mua hàng mẫu |

---

## 5. Quy Trình Làm Việc Với Git & Quy Định Nhánh (Branch Policy)

### Danh sách nhánh cho phép (Allowlist):
- **`main`**: Nhánh sản phẩm ổn định chính thức.
- **`trung`**: Nhánh cá nhân phụ trách chính.

### Các lệnh làm việc với Git:
Lấy mã nguồn mới nhất:
```powershell
git fetch --all --prune
git pull origin main
```

Kiểm tra branch hiện tại và trạng thái làm việc:
```powershell
git branch --show-current
git status --short
```

Đẩy mã nguồn lên remote GitHub:
```powershell
git add .
git commit -m "feat/fix: mô tả ngắn gọn nội dung thay đổi"
git push origin trung
git checkout main
git merge trung
git push origin main
git checkout trung
```

---

## 6. Xử Lý Lỗi Thường Gặp (Troubleshooting)

| Sự cố | Nguyên nhân | Cách khắc phục |
|---|---|---|
| Lỗi `PDOException: Connection refused` | MySQL chưa bật hoặc sai port/password trong `config/database.local.php` | Kiểm tra dịch vụ MySQL đang chạy tại port 3306 và cập nhật `config/database.local.php`. |
| Lỗi `404 Not Found` các route | PHP Built-in server không chạy qua router.php | Chạy web server bằng lệnh: `php -S 127.0.0.1:8000 router.php`. |
| Lỗi `CSRF Token Invalid (403)` khi gọi AJAX | Thiếu header `X-CSRF-Token` hoặc `_csrf` body | Kiểm tra script JS gửi header `X-CSRF-Token: csrfToken`. |
| Lỗi ảnh sản phẩm không hiển thị | Thư mục `public/assets/images/products/` thiếu ảnh | Đảm bảo tập tin ảnh tồn tại và không bị xóa bớt trong public assets. |
| AI Chatbot trả về thông báo lỗi API | Thiếu hoặc sai API Key trong `.env` | Điền `GEMINI_API_KEY` hoặc `GROQ_API_KEY` hợp lệ vào file `.env`. |

---

**TechPilot Team © 2026 — Advanced AI-Powered Tech E-Commerce Platform.**

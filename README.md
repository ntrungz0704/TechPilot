# TechPilot — Hệ thống bán hàng công nghệ

Ứng dụng web thương mại điện tử chuyên thiết bị công nghệ: laptop, PC, linh kiện,
phụ kiện. Xây dựng bằng **PHP MVC thuần** (không framework), **MySQL/MariaDB**,
**HTML/CSS/JavaScript**.

**Tính năng chính:**

- Customer: duyệt sản phẩm, tìm kiếm, lọc, so sánh, giỏ hàng, đặt hàng COD/VNPay,
  PC Builder, tin tức, chatbot Gemini AI, wishlist.
- Admin: quản lý sản phẩm, danh mục, thương hiệu, tồn kho, đơn hàng, người dùng,
  flash sale, coupon, banner, bài viết, đánh giá.
- Hệ thống tồn kho với inventory logs.

**Repository chính thức:** <https://github.com/ntrungz0704/TechPilot>

---

## 1. Yêu cầu hệ thống

| Công cụ          | Phiên bản tối thiểu      | Ghi chú                          |
| ---------------- | ------------------------- | -------------------------------- |
| PHP              | 8.0+                      | Khuyến nghị 8.1+                |
| MySQL / MariaDB  | 5.7+ / 10.4+              | Port mặc định **3306**          |
| Git              | 2.x                       |                                  |
| Node.js          | 18+ *(tùy chọn)*          | Chỉ cần nếu chạy browser test  |

**PHP extensions — phân loại theo module:**

| Extension    | Module         | Mức độ    | Ghi chú                             |
| ------------ | -------------- | --------- | ------------------------------------ |
| PDO          | Core           | Bắt buộc  | Kết nối database                     |
| pdo_mysql    | Core           | Bắt buộc  | Driver MySQL cho PDO                 |
| json         | Core           | Bắt buộc  | Mặc định có từ PHP 8.0              |
| mbstring     | Core           | Bắt buộc  | Source dùng mb_* cho tiếng Việt      |
| fileinfo     | Admin Upload   | Bắt buộc  | Upload ảnh sản phẩm                  |
| curl         | Gemini/VNPay   | Tùy chọn  | Cần cho API ngoài                    |
| openssl      | Gemini/VNPay   | Tùy chọn  | Cần cho TLS/HTTPS                    |

**Bật extension trong php.ini CLI:**

Tìm file php.ini CLI đang dùng:

```powershell
php --ini
```

Mở file (thường `C:\php\php.ini` hoặc `C:\xampp\php\php.ini`), bỏ dấu `;` trước:

```ini
extension=curl
extension=fileinfo
extension=mbstring
extension=openssl
extension=pdo_mysql
```

Xác nhận:

```powershell
php -m | findstr /I "curl fileinfo mbstring openssl PDO pdo_mysql"
```

**Kiểm tra trên Windows PowerShell:**

```powershell
git --version
php -v
mysql --version
php -m
```

---

## 2. Clone repository

```powershell
git clone https://github.com/ntrungz0704/TechPilot.git
cd TechPilot
```

Xác nhận:

```powershell
git remote -v
git fetch --all --prune
git branch -a
git status
```

> **Không tải ZIP** khi làm việc nhóm — mất lịch sử commit và không thể
> pull/push.

---

## 3. Kiểm tra nhánh main

```powershell
git switch main
git pull --ff-only origin main
git status --short
git rev-parse --short HEAD
```

- `main` chỉ để kiểm tra bản ổn định.
- **Không code trực tiếp trên main.**
- Sau khi cài đặt và kiểm tra xong, chuyển sang nhánh cá nhân.

---

## 4. Cấu hình database local

Sao chép file cấu hình mẫu:

```powershell
Copy-Item config/database.local.example.php config/database.local.php
```

Mở `config/database.local.php` và điền thông tin MySQL trên máy:

```php
return [
    'host'     => '127.0.0.1',
    'port'     => '3306',       // Port MySQL chuẩn
    'database' => 'techpilot',
    'username' => 'root',
    'password' => '',           // Mật khẩu MySQL local
    'charset'  => 'utf8mb4',
];
```

> ⚠️ **`config/database.local.php`:**
> - Nằm trong `.gitignore` — **không được commit**.
> - Không được `git add -f`.
> - Không sửa password trực tiếp trong `config/database.php`.

---

## 5. Cấu hình .env

```powershell
Copy-Item .env.example .env
```

Mở `.env` và điền các giá trị:

```env
# === Ứng dụng ===
APP_URL=http://127.0.0.1:8000

# === Cơ sở dữ liệu ===
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=techpilot
DB_USER=root
DB_PASS=

# === Gemini AI (tùy chọn) ===
GEMINI_API_KEY=

# === VNPay (tùy chọn) ===
VNPAY_TMN_CODE=
VNPAY_HASH_SECRET=
VNPAY_RETURN_URL=http://127.0.0.1:8000/payment/vnpay-return
VNPAY_IPN_URL=
```

**Phân biệt:**

- Website core chạy **không cần** Gemini key.
- Thanh toán COD chạy **không cần** VNPay credential.
- Gemini chatbot và VNPay chỉ hoạt động đầy đủ khi có credential hợp lệ.
- **Không commit `.env`.**

---

## 6. Database import

### A. MySQL CLI

```powershell
mysql -h 127.0.0.1 -P 3306 -u root -p --default-character-set=utf8mb4
```

Trong MySQL prompt:

```sql
CREATE DATABASE IF NOT EXISTS techpilot
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE techpilot;
SOURCE database/seed.sql;
```

Hoặc một dòng (cmd):

```cmd
mysql -h 127.0.0.1 -P 3306 -u root -p --default-character-set=utf8mb4 techpilot < database\seed.sql
```

### B. phpMyAdmin

1. Mở phpMyAdmin → tạo database `techpilot` (utf8mb4_unicode_ci).
2. Chọn database `techpilot` → tab **Import**.
3. Chọn file `database/seed.sql` → Execute.

> ⚠️ **Cảnh báo:**
> - Seed có `DROP TABLE` — sẽ xóa bảng cùng tên nếu đã tồn tại.
> - Chỉ chạy trên local/test — **không import vào production**.
> - Backup trước nếu database có dữ liệu cần giữ.
> - Không dùng lệnh có password trực tiếp (dùng `-p` không có giá trị).

---

## 7. Migration

Sau khi import seed, chạy migration để đưa database lên schema mới nhất:

```powershell
php scripts/database/migrate.php
```

- Import seed tạo baseline dữ liệu.
- Migration bổ sung các bảng/cột mới chưa có trong seed.
- **Không được bỏ qua migration.**
- Chạy lại migration an toàn (idempotent).

---

## 8. Verify installation

Kiểm tra toàn bộ cài đặt:

```powershell
php scripts/verify-install.php
```

Verifier v2 kiểm tra theo capability matrix:

| Module          | Kiểm tra                                           |
| --------------- | -------------------------------------------------- |
| CORE            | PHP, extensions, files, config, DB, tables, data   |
| CORE            | Primary image sản phẩm active phải tồn tại (FAIL)  |
| ADMIN UPLOAD    | fileinfo extension, upload directory writable       |
| GEMINI          | curl, openssl, GEMINI_API_KEY                      |
| VNPAY           | hash/HMAC, TMN_CODE, HASH_SECRET, APP_URL          |

**Database:** 19 bảng business/audit + 1 bảng technical (`migrations`) = **20 bảng vật lý**.

**Quy tắc:**

- Core requirement thiếu → **FAIL** toàn hệ thống.
- Ảnh chính sản phẩm active thiếu → **FAIL**.
- Ảnh gallery phụ thiếu → WARN.
- Gemini/VNPay chưa cấu hình → NOT_CONFIGURED (không phải FAIL).

**Chỉ khi CORE APPLICATION = 0 FAIL** mới xác nhận cài đặt hợp lệ.

---

## 9. Chạy website

```powershell
php -S 127.0.0.1:8000 router.php
```

Mở trình duyệt: **<http://127.0.0.1:8000>**

> ⚠️ **Không được bỏ `router.php`** — nếu chạy `php -S 127.0.0.1:8000` không
> có router, mọi route sẽ 404.

---

## 10. Tài khoản development

Seed tạo sẵn các tài khoản sau:

| Email                    | Role     | Mật khẩu    | Đã xác minh       |
| ------------------------ | -------- | ----------- | ------------------ |
| admin@techpilot.vn       | admin    | `admin123`  | ✅ password_verify |
| ntrungz0704@gmail.com    | customer | *(chưa xác minh)* | ❌            |

> - Admin: đã xác minh bằng `password_verify()` — đăng nhập thành công.
> - Customer: mật khẩu chưa được xác minh — nếu cần, tạo tài khoản mới qua
>   trang đăng ký hoặc cập nhật password hash trong database.

> ⚠️ **Không dùng tài khoản development trên production.**

---

## 11. Checklist Customer

Sau khi chạy website, kiểm tra tuần tự:

- [ ] Trang chủ (Home) hiển thị đúng
- [ ] Duyệt danh mục (category)
- [ ] Tìm kiếm sản phẩm (search)
- [ ] Lọc sản phẩm (filter)
- [ ] Sắp xếp sản phẩm (sort)
- [ ] Phân trang (pagination)
- [ ] Chi tiết sản phẩm (product detail)
- [ ] Gallery ảnh sản phẩm
- [ ] Thông số kỹ thuật (specs)
- [ ] Đăng ký / Đăng nhập / Đăng xuất
- [ ] Wishlist (yêu thích)
- [ ] So sánh sản phẩm (compare)
- [ ] Giỏ hàng (cart)
- [ ] Đặt hàng COD
- [ ] Đặt hàng VNPay *(khi có cấu hình)*
- [ ] Lịch sử đơn hàng
- [ ] PC Builder
- [ ] Tin tức (news)
- [ ] Gemini chatbot *(khi có API key)*
- [ ] Responsive 440 × 956

---

## 12. Checklist Admin

Đăng nhập tài khoản admin, kiểm tra:

- [ ] Dashboard
- [ ] Quản lý Products
- [ ] Quản lý Categories
- [ ] Quản lý Brands
- [ ] Quản lý Inventory (tồn kho)
- [ ] Inventory history (logs)
- [ ] Quản lý Orders
- [ ] Quản lý Users / Customers
- [ ] Quản lý Flash Sale
- [ ] Quản lý Banners
- [ ] Quản lý Posts (bài viết)
- [ ] Quản lý Reviews (đánh giá)
- [ ] Quản lý Coupons (mã giảm giá)
- [ ] Authorization (phân quyền admin/customer)

---

## 13. Troubleshooting

| Vấn đề | Giải pháp |
| ------ | --------- |
| `php` không nhận trong terminal | Thêm PHP vào PATH: `$env:Path += ";C:\xampp\php"` |
| Lỗi kết nối MySQL | Kiểm tra MySQL đang chạy, port **3306**, user/password đúng |
| `could not find driver` | Bật `extension=pdo_mysql` trong `php.ini` |
| 404 mọi trang | Thiếu `router.php` trong lệnh chạy server |
| Lỗi utf8mb4 | Database phải dùng charset `utf8mb4`, collation `utf8mb4_unicode_ci` |
| `HY093: Invalid parameter number` | Kiểm tra số placeholder `?` khớp số tham số bind |
| Ảnh/asset không hiển thị | Kiểm tra file tồn tại trong `public/assets/`, URL không dùng đường dẫn tuyệt đối Windows |
| Database chưa migrate | Chạy `php scripts/database/migrate.php` |
| Gemini chatbot không hoạt động | Cấu hình `GEMINI_API_KEY` trong `.env` |
| VNPay return URL sai | Kiểm tra `VNPAY_RETURN_URL` trong `.env` trỏ đúng host:port |
| Trang hiển thị cũ | Xóa cache browser: Ctrl+Shift+R |
| Đang ở branch sai | Chạy `git branch --show-current` để kiểm tra |
| File local bị staged | Chạy `git restore --staged .env config/database.local.php` |

---

## 14. Cấu trúc ảnh sản phẩm

```
public/assets/images/
├── categories/           ← 20 file: category-{slug}.png
├── placeholders/         ← Ảnh placeholder dùng chung
├── products/             ← Ảnh sản phẩm theo category
│   ├── accessories/
│   ├── case/
│   ├── cpu/
│   ├── laptop/
│   └── ... (20 thư mục)
├── brands/               ← Logo thương hiệu
├── news/                 ← Ảnh tin tức
└── posts/                ← Ảnh bài viết
```

- Database lưu relative path: `assets/images/products/{category}/{filename}`.
- Ảnh chính sản phẩm active **phải tồn tại** và **được Git theo dõi**.
- Ảnh category dùng format `category-{slug}.png`.
- Placeholder chỉ bảo vệ UI — không thay thế ảnh thật.

---

## 15. Tiêu chí cài đặt hợp lệ

Bản cài đặt được xác nhận hợp lệ khi:

1. `php scripts/verify-install.php` trả về **CORE APPLICATION: 0 FAIL**.
2. Checklist smoke test (mục 11, 12) hoàn thành.

**Phân biệt:**

- **Core application**: chạy độc lập không cần external service.
- **Gemini AI**: cần `GEMINI_API_KEY` hợp lệ — chatbot mới hoạt động.
- **VNPay**: cần `VNPAY_TMN_CODE` + `VNPAY_HASH_SECRET` hợp lệ — thanh toán
  online mới hoạt động. COD không cần VNPay.
- Gemini/VNPay = NOT_CONFIGURED **không phải FAIL** của core.

---

## 16. Branch policy

### Danh sách nhánh cho phép (allowlist)

| Nhánh     | Mục đích                       |
| --------- | ------------------------------ |
| `main`    | Bản ổn định — chỉ merge qua PR |
| `develop` | Tích hợp test — chỉ merge qua PR |
| `trung`   | Nhánh cá nhân — Trung          |
| `kim`     | Nhánh cá nhân — Kim            |
| `hieu`    | Nhánh cá nhân — Hiếu           |
| `dinh`    | Nhánh cá nhân — Định           |

> **Ngoài sáu nhánh trên, thành viên và AI không được tự ý tạo thêm nhánh.**

### Các lệnh bị cấm

```bash
git branch ten-nhanh-moi          # ❌ Tạo nhánh mới
git switch -c ten-nhanh-moi       # ❌ Tạo và chuyển nhánh mới
git checkout -b ten-nhanh-moi     # ❌ Tạo và chuyển nhánh mới
git worktree add -b ten-nhanh-moi # ❌ Tạo worktree kèm nhánh
```

### Quy trình Pull Request

1. Nhánh cá nhân (`trung`/`kim`/`hieu`/`dinh`) → **PR vào `main`**.
2. Chỉ chủ repository (Trung) được review và merge PR.
3. **Không push trực tiếp lên `main`.**
4. **Không force-push.**
5. `develop` dùng khi cần tích hợp test nhiều nhánh cùng lúc.

### Cài đặt Git Guards

Sau khi clone, mỗi thành viên chạy một lần:

```powershell
powershell -ExecutionPolicy Bypass -File scripts/install-git-guards.ps1
```

Local hook sẽ:

- Chặn commit trên `main`/`develop` và branch ngoài allowlist.
- Chặn staged credential (`.env`, `database.local.php`...).
- Chặn push lên `main`/`develop` và branch ngoài allowlist.

> ⚠️ **Local hook không thay thế GitHub Ruleset.** Xem mục 16.

### Cấu hình GitHub Ruleset (chỉ chủ repository)

Vào **Repository → Settings → Rules → Rulesets**:

**Ruleset 1: Chặn tạo branch không được phép**

- Name: `deny-unapproved-branch-creation`
- Target: `*`
- Rule: Restrict creations
- Bypass: chỉ chủ repository (Trung)
- Enforcement: Active

**Ruleset 2: Bảo vệ `main` và `develop`**

- Target: `main`, `develop`
- Bật:
  - Require pull request
  - Require approval
  - Require status checks
  - Block force pushes
  - Restrict deletions
  - Require conversation resolution

> README và local hooks không thể ngăn hoàn toàn branch creation trên remote.
> GitHub Ruleset mới là lớp cưỡng chế cuối cùng.

---

## 17. Node.js và browser test

Node.js **chỉ dùng cho Puppeteer browser test** — runtime website không cần
`npm install`.

Nếu muốn chạy browser test:

```powershell
npm ci
```

> Dùng `npm ci` thay vì `npm install` khi có `package-lock.json` để đảm bảo
> dependency đồng nhất giữa các máy.

---

## BRANCH LOCK — BẮT BUỘC

> **Dành cho thành viên và AI agent:**

Bạn chỉ được làm việc trên nhánh Git hiện tại đã do chủ repository tạo sẵn.

Trước khi sửa code, chạy:

```bash
git branch --show-current
git status --short
```

**Bạn bị cấm:**

- Tạo branch mới.
- Chạy `git switch -c`, `git checkout -b`, `git branch <tên>`.
- Đổi tên branch.
- Xóa branch.
- Tạo worktree kèm branch.
- Tự chuyển sang `main`/`develop` hoặc nhánh người khác.
- Commit, push, merge, rebase hoặc force-push nếu chưa được yêu cầu rõ.

**Nếu nhánh hiện tại không đúng nhánh được giao:** DỪNG và báo lại.
Không được tự chọn giải pháp bằng cách tạo nhánh khác.

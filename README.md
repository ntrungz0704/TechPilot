# TechPilot — Website thương mại điện tử thiết bị công nghệ

TechPilot là website thương mại điện tử chuyên bán laptop Windows, PC lắp ráp, màn hình, linh kiện PC, thiết bị mạng, gaming gear và phụ kiện máy tính. Dự án sử dụng **PHP thuần theo mô hình MVC**, MySQL/MariaDB, HTML5, CSS3 và JavaScript thuần.

Tài liệu này dành cho cả thành viên mới chưa từng dùng Git/GitHub. Hãy làm lần lượt từ trên xuống và không bỏ qua phần **Quy trình Git hằng ngày**.

---

## 1. Công nghệ và yêu cầu hệ thống

### Phần mềm bắt buộc

- PHP 8.0 trở lên.
- MySQL hoặc MariaDB 10.4 trở lên.
- Git.
- Một trình soạn thảo, khuyến nghị Visual Studio Code.
- Web server:
  - Apache có `mod_rewrite`; hoặc
  - PHP Built-in Web Server.

### Khuyến nghị trên Windows

Có thể dùng một trong các bộ môi trường:

- Laragon.
- XAMPP.
- PHP và MySQL cài riêng.

Kiểm tra các công cụ đã cài:

```bash
git --version
php -v
mysql --version
```

Nếu một lệnh không chạy, cần cài phần mềm tương ứng hoặc thêm nó vào biến môi trường `PATH` trước khi tiếp tục.

---

## 2. Cấu trúc thư mục

```text
TechPilot/
├── app/
│   ├── controllers/      # Nhận request và điều phối xử lý
│   ├── core/             # Router, Controller nền và Helpers
│   ├── models/           # Truy vấn và xử lý dữ liệu
│   └── views/            # Giao diện PHP
├── config/               # Cấu hình ứng dụng và database
├── database/             # schema.sql và dữ liệu mẫu
├── public/               # CSS, JavaScript, hình ảnh và public entry point
├── docs/                 # Tài liệu dự án nếu có
├── index.php             # Entry point ở thư mục gốc nếu dự án sử dụng
└── router.php            # Router cho PHP Built-in Server
```

Quy tắc MVC:

- Không viết SQL trực tiếp trong View.
- Controller không được chứa HTML lớn.
- Model dùng PDO prepared statements.
- CSS, JavaScript và hình ảnh đặt trong `public/assets` theo cấu trúc dự án hiện có.

---

## 3. Các nhánh Git của dự án

| Nhánh | Mục đích | Ai được làm việc |
|---|---|---|
| `main` | Bản ổn định để nghiệm thu/phát hành | Chỉ merge qua Pull Request đã kiểm tra |
| `develop` | Nhánh tích hợp chung | Nhận code từ các nhánh cá nhân |
| `trung` | Nhánh làm việc cá nhân của Trung | Trung |
| `kim` | Nhánh làm việc cá nhân của Kim | Kim |
| `hieu` | Nhánh làm việc cá nhân của Hiếu | Hiếu |
| `dinh` | Nhánh làm việc cá nhân của Định | Định |

Trung là chủ repository và reviewer. Quản lý `develop` và `main`. Không thành viên nào được tự ý push thẳng lên `main` hoặc ghi đè lịch sử nhánh chung.

Luồng code:

```text
trung ─┐
kim   ─┼── Pull Request ──> develop ── kiểm thử ──> main
hieu  ─┤
dinh  ─┘
```

---

## 4. Cài Git và đăng nhập GitHub lần đầu

### Bước 1 — Cài Git

Tải Git tại: <https://git-scm.com/downloads>

Sau khi cài, mở PowerShell, Git Bash hoặc Terminal trong VS Code:

```bash
git --version
```

### Bước 2 — Khai báo tên và email

Mỗi thành viên chỉ làm một lần trên máy của mình:

```bash
git config --global user.name "Tên của bạn"
git config --global user.email "email-github-cua-ban@example.com"
```

Kiểm tra:

```bash
git config --global --list
```

Email nên trùng với email đã dùng trên GitHub để commit được nhận diện đúng.

### Bước 3 — Có quyền repository

Chủ repository phải mời Kim, Hiếu và Định làm Collaborator. Thành viên mở lời mời GitHub và bấm **Accept invitation** trước khi push.

Khi Git yêu cầu đăng nhập GitHub qua HTTPS, không dùng mật khẩu GitHub thông thường. Hãy đăng nhập bằng trình duyệt/Git Credential Manager hoặc Personal Access Token theo hướng dẫn của GitHub.

---

## 5. Tải dự án về máy lần đầu

Mở Terminal tại thư mục muốn chứa dự án:

```bash
git clone https://github.com/ntrungz0704/TechPilot.git
cd TechPilot
```

Kiểm tra remote và danh sách nhánh:

```bash
git remote -v
git fetch origin
git branch -a
```

Không tải file ZIP để làm việc nhóm lâu dài vì bản ZIP không có lịch sử Git và rất khó đồng bộ.

---

## 6. Chọn đúng nhánh cá nhân

Mỗi người chỉ chạy nhóm lệnh ứng với mình.

### Trung

```bash
git switch trung
git pull origin trung
```

### Kim

```bash
git switch kim
git pull origin kim
```

### Hiếu

```bash
git switch hieu
git pull origin hieu
```

### Định

```bash
git switch dinh
git pull origin dinh
```

Nếu Git báo nhánh chưa có ở máy:

```bash
git fetch origin
git switch --track origin/kim
```

Thay `kim` bằng `trung`, `hieu` hoặc `dinh` tương ứng.

Kiểm tra đang đứng đúng nhánh:

```bash
git branch --show-current
git status
```

Không bắt đầu sửa code nếu tên nhánh đang là `main`, `develop` hoặc nhánh của người khác.

---

## 7. Khởi tạo cơ sở dữ liệu

> **Cảnh báo:** Đọc đầu file `database/schema.sql` trước khi chạy. Nếu file có `DROP DATABASE`, nó có thể xóa toàn bộ database cũ. Chỉ import vào database local/test, không chạy trên database production đang có đơn hàng.

### Cách 1 — phpMyAdmin

1. Mở phpMyAdmin.
2. Tạo database tên `techpilot` với charset `utf8mb4`.
3. Chọn database `techpilot`.
4. Chọn tab **Import**.
5. Chọn file `database/schema.sql`.
6. Bấm **Import/Go**.

### Cách 2 — MySQL CLI

Nếu `schema.sql` tự tạo database:

```bash
mysql -u root -p < database/schema.sql
```

Nếu đã tạo database `techpilot` trước:

```bash
mysql -u root -p techpilot < database/schema.sql
```

Sau khi import, kiểm tra các bảng cốt lõi như `users`, `categories`, `brands`, `products`, `carts`, `orders` và `posts` đã tồn tại.

---

## 8. Cấu hình kết nối database

Mở `config/database.php` và kiểm tra cấu hình theo môi trường local:

```php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'techpilot');
define('DB_USER', 'root');
define('DB_PASS', '');
```

Nếu repository có file `config/database.local.example.php`, nên sao chép thành `config/database.local.php` và chỉ sửa file local:

```powershell
Copy-Item config/database.local.example.php config/database.local.php
```

File chứa mật khẩu local phải nằm trong `.gitignore`. Không commit mật khẩu database thật, API key hoặc credential production.

Ứng dụng và database phải dùng UTF-8/`utf8mb4` để tránh lỗi chữ như `MÃ¡y tÃnh`.

---

## 9. Chạy ứng dụng

### Cách 1 — PHP Built-in Server

Từ thư mục gốc `TechPilot`:

```bash
php -S 127.0.0.1:8000 router.php
```

Mở:

```text
http://127.0.0.1:8000
```

Dừng server bằng `Ctrl + C` trong Terminal.

### Cách 2 — Laragon/Apache

Khuyến nghị document root trỏ vào thư mục `public` nếu cấu trúc router của dự án yêu cầu. Bật Apache rewrite và kiểm tra `.htaccess`.

Không duy trì hai bản code riêng, ví dụ một bản ở ổ D và một bản trong `laragon/www`, vì chúng sẽ nhanh chóng lệch nhau.

---

## 10. Tài khoản thử nghiệm local

Các tài khoản sau chỉ dùng trong môi trường development sau khi import đúng seed:

### Admin

```text
Email: admin@techpilot.vn
Mật khẩu seed: admin123
```

### Customer

```text
Email: customer@gmail.com
Mật khẩu seed: admin123
```

> `admin123` là mật khẩu yếu. Không được dùng những tài khoản/mật khẩu này khi triển khai public. Trước khi deploy phải đổi mật khẩu, tắt hoặc xóa tài khoản seed và không ghi credential production trong README.

Nếu đăng nhập thất bại, kiểm tra:

- Database đã import đúng chưa.
- Email có tồn tại trong bảng `users` không.
- Password trong DB có được tạo bằng `password_hash()` không.
- Role/status của tài khoản có đúng không.
- Ứng dụng đang kết nối đúng database không.

---

## 11. Quy trình Git hằng ngày để tránh conflict

Mỗi lần bắt đầu làm việc, thực hiện đúng thứ tự dưới đây.

### Bước 1 — Kiểm tra nhánh và file đang sửa

```bash
git branch --show-current
git status
```

Nếu có file chưa commit từ hôm trước, hãy hoàn tất hoặc commit chúng trước khi đồng bộ. Không dùng `git reset --hard` để xóa thay đổi.

### Bước 2 — Lấy thông tin mới nhất

```bash
git fetch origin
```

### Bước 3 — Đồng bộ `develop` vào nhánh cá nhân

Ví dụ Kim:

```bash
git switch kim
git merge origin/develop
```

Hiếu, Định và Trung thay `kim` bằng nhánh của mình.

Nếu không có conflict, tiếp tục làm việc. Nếu có conflict, đọc phần **Xử lý conflict an toàn** bên dưới.

### Bước 4 — Chỉ sửa đúng task được giao

Trước khi code, nhóm thống nhất:

- Người phụ trách.
- File/module dự kiến sửa.
- Tiêu chí hoàn thành.

Không để hai người cùng lúc sửa các file layout lớn như `header.php`, `footer.php`, router, schema hoặc CSS chung mà chưa trao đổi.

### Bước 5 — Kiểm tra thay đổi

```bash
git status
git diff
```

Không commit file cấu hình local, log, cache, file tạm hoặc credential.

### Bước 6 — Chạy kiểm thử

Lint tất cả PHP bằng PowerShell:

```powershell
Get-ChildItem -Path app,config,public -Filter *.php -Recurse | ForEach-Object { php -l $_.FullName }
```

Hoặc kiểm tra từng file:

```bash
php -l app/models/Product.php
php -l app/models/Order.php
```

Sau đó test thủ công route/module vừa sửa.

### Bước 7 — Commit nhỏ, rõ nghĩa

```bash
git add duong-dan-file-da-sua
git commit -m "fix(search): correct product filtering and relevance"
```

Không dùng `git add .` một cách máy móc nếu trong thư mục có file không liên quan.

Mẫu commit:

```text
feat(cart): add server-side quantity validation
fix(auth): redirect user after successful login
fix(search): preserve filters across pagination
feat(admin): add order status update
style(mobile): simplify product grid at 440px
docs(readme): add beginner setup guide
```

### Bước 8 — Push nhánh cá nhân

Kim:

```bash
git push origin kim
```

Hiếu, Định và Trung dùng tên nhánh tương ứng.

### Bước 9 — Tạo Pull Request

Trên GitHub:

1. Mở repository TechPilot.
2. Chọn **Pull requests**.
3. Chọn **New pull request**.
4. `base` chọn `develop`.
5. `compare` chọn `kim`, `hieu`, `dinh` hoặc `trung`.
6. Ghi rõ đã sửa gì, file nào và test gì.
7. Gửi Trung hoặc một thành viên khác review.
8. Chỉ merge khi code đã được review và test.

Không mở Pull Request từ nhánh cá nhân thẳng vào `main` trong công việc hằng ngày.

---

## 12. Mẫu Pull Request

```markdown
## Công việc đã làm
- ...

## File chính đã thay đổi
- ...

## Cách kiểm thử
1. ...
2. ...

## Kết quả
- [ ] PHP lint pass
- [ ] Test desktop
- [ ] Test mobile 440px
- [ ] Không commit credential/log
- [ ] Không còn nút chết trong phạm vi task

## Ảnh trước/sau
Đính kèm nếu thay đổi giao diện.

## Lưu ý hoặc rủi ro
- ...
```

---

## 13. Cách phòng tránh conflict

1. Đồng bộ `origin/develop` trước khi code.
2. Không sửa trực tiếp `main` hoặc `develop`.
3. Mỗi task chỉ có một người chính phụ trách.
4. Chia task theo module/file, không chỉ theo tên trang chung chung.
5. Commit nhỏ và push thường xuyên.
6. Báo nhóm trước khi sửa router, schema, layout hoặc CSS toàn cục.
7. Không format lại toàn bộ file nếu chỉ sửa một đoạn nhỏ.
8. Không đổi tên/xóa file người khác đang dùng mà chưa trao đổi.
9. Pull Request nhỏ dễ review hơn một PR chứa toàn bộ dự án.
10. Sau khi PR khác được merge vào `develop`, mọi người cần fetch và merge lại `origin/develop`.

---

## 14. Xử lý conflict an toàn

Sau lệnh:

```bash
git merge origin/develop
```

Nếu Git báo conflict:

### Bước 1 — Xem file conflict

```bash
git status
```

File conflict có thể chứa:

```text
<<<<<<< HEAD
Code trên nhánh cá nhân
=======
Code từ develop
>>>>>>> origin/develop
```

### Bước 2 — Trao đổi với người viết đoạn code liên quan

Không bấm **Accept All Current** hoặc **Accept All Incoming** nếu chưa hiểu hai phần code. Cần kết hợp thủ công để giữ đúng logic của cả hai bên.

### Bước 3 — Xóa marker và kiểm tra lại file

Sau khi sửa, file không được còn các dòng:

```text
<<<<<<<
=======
>>>>>>>
```

Tìm toàn dự án:

```bash
git grep -n "<<<<<<<\|=======\|>>>>>>>"
```

### Bước 4 — Đánh dấu đã giải quyết và hoàn tất merge

```bash
git add duong-dan-file-da-sua
git commit -m "merge: resolve conflicts with develop"
```

### Bước 5 — Chạy lại ứng dụng và test

Ít nhất phải chạy PHP lint và test lại cả chức năng của mình lẫn phần code vừa conflict.

Nếu đang merge nhưng nhận ra làm sai và chưa commit merge, có thể dừng merge bằng:

```bash
git merge --abort
```

Lệnh này chỉ dùng để hủy lần merge đang diễn ra, không dùng `git reset --hard`.

---

## 15. Các lệnh Git bị cấm hoặc cần tránh

Không sử dụng nếu chưa được chủ repository đồng ý:

```bash
git push --force
git push -f
git reset --hard
git clean -fdx
```

Không:

- Xóa thư mục `.git`.
- Copy đè toàn bộ source của người khác.
- Commit file chứa mật khẩu/API key.
- Commit database dump chứa dữ liệu cá nhân.
- Tự merge PR chưa được review.
- Sửa lịch sử `main`/`develop`.

---

## 16. Khi Pull Request đã được merge

Sau khi code cá nhân được merge vào `develop`, cập nhật nhánh cá nhân:

```bash
git fetch origin
git switch kim
git merge origin/develop
git push origin kim
```

Thay `kim` bằng `trung`, `hieu` hoặc `dinh`.

Khi `develop` đã kiểm thử ổn định, Trung tạo Pull Request:

```text
develop → main
```

`main` chỉ chứa bản được nghiệm thu hoặc chuẩn bị phát hành.

---

## 17. Checklist trước khi bàn giao task

- [ ] Đang làm đúng nhánh cá nhân.
- [ ] Đã merge phiên bản mới nhất của `origin/develop`.
- [ ] Chỉ sửa file thuộc task.
- [ ] Không còn conflict marker.
- [ ] PHP lint pass.
- [ ] Route và nút bấm trong phạm vi task hoạt động thật.
- [ ] Đã test dữ liệu MySQL sau thao tác.
- [ ] Đã test tài khoản guest/customer/admin nếu liên quan.
- [ ] Đã test desktop và mobile 440px nếu sửa UI.
- [ ] Không có `href="#"` hoặc nút giả trong phạm vi task.
- [ ] Không commit password, log, cache hoặc config local.
- [ ] Commit message rõ ràng.
- [ ] Đã push nhánh cá nhân.
- [ ] Pull Request có mô tả và cách test.

---

## 18. Kiểm tra nhanh sau khi cài đặt

Sau khi chạy ứng dụng, kiểm tra lần lượt:

1. Trang chủ mở được.
2. Tìm kiếm sản phẩm hoạt động.
3. Category/filter/sort/pagination hoạt động.
4. Đăng ký tạo user thật trong database.
5. Đăng nhập/đăng xuất hoạt động.
6. Guest bị yêu cầu đăng nhập trước khi mua.
7. Thêm giỏ và cập nhật số lượng hoạt động.
8. Checkout chỉ có COD.
9. Tạo order, xem lịch sử và chi tiết đơn.
10. Admin truy cập dashboard và quản lý đúng quyền.
11. Tin tức mở được danh sách và bài chi tiết.
12. Mobile 440px không có horizontal scroll.

Trang hiển thị HTTP 200 chưa đủ để kết luận hoạt động; cần kiểm tra cả request, database và giao diện sau refresh.

---

## 19. Lỗi thường gặp

### `php` không được nhận diện

PHP chưa được thêm vào `PATH`. Nếu dùng Laragon, mở Terminal từ Laragon hoặc thêm thư mục PHP của Laragon vào PATH.

### Không kết nối được database

Kiểm tra MySQL đã chạy, tên database, username, password, port và file config local.

### Trang con trả 404

Kiểm tra đang chạy đúng lệnh:

```bash
php -S 127.0.0.1:8000 router.php
```

Nếu dùng Apache, kiểm tra `mod_rewrite`, `.htaccess` và document root.

### Chữ tiếng Việt bị lỗi

Kiểm tra database/table/connection đều dùng `utf8mb4`, file PHP lưu UTF-8 và HTML có `<meta charset="UTF-8">`.

### HY093: Invalid parameter number (PDO search bug)

Lỗi này xảy ra khi một named placeholder như `:keyword` được dùng nhiều lần trong cùng một SQL string, hoặc khi `$params` truyền vào `execute()` chứa key không tồn tại trong SQL.

Nguyên tắc bắt buộc với PDO:

- Mỗi named placeholder (`:ten`) chỉ xuất hiện **một lần** trong SQL.
- Nếu cần lọc theo cùng giá trị ở nhiều cột, dùng tên khác nhau: `:filterName`, `:filterDesc`.
- Không nhúng cùng một đoạn SQL có placeholder vào nhiều vị trí khác nhau trong query.
- Mỗi key trong `$params` phải có placeholder tương ứng trong SQL và ngược lại.

Kiểm tra nhanh trước `execute()`:

```php
// Tạm thời thêm để debug, xóa sau khi fix
var_dump(array_keys($params));
echo $query;
exit;
```

### Push bị từ chối

Chạy:

```bash
git fetch origin
git merge origin/develop
```

Giải quyết conflict, test, commit rồi push lại. Không dùng `--force`.

### File local bị đưa vào commit

Không commit ngay. Kiểm tra:

```bash
git status
git restore --staged duong-dan-file
```

Sau đó bổ sung file phù hợp vào `.gitignore` nếu cần.

---

## 20. Nguyên tắc bảo mật

- Mật khẩu dùng `password_hash()` và `password_verify()`.
- Truy vấn có input dùng PDO prepared statements.
- Mọi form POST dùng CSRF token.
- Giá, tồn kho, coupon và tổng đơn phải tính lại ở server.
- Checkout COD dùng transaction và khóa tồn kho phù hợp.
- User chỉ xem được dữ liệu của chính mình.
- Route admin bắt buộc `role = admin`.
- Không commit credential thật.

---

## 21. Liên kết repository

Repository:

<https://github.com/ntrungz0704/TechPilot>

Khi cần hỗ trợ, hãy gửi kèm:

- Tên nhánh hiện tại.
- Lệnh vừa chạy.
- Toàn bộ thông báo lỗi.
- File/module đang sửa.
- Các bước để tái hiện lỗi.

Không chỉ gửi ảnh trắng trang mà thiếu log hoặc URL.

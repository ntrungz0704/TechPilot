# Governance Baseline Evidence

| Thuộc tính | Giá trị |
|---|---|
| Evidence status | `PARTIAL — STATUS/STAT MATCH, NO PRE-EDIT BYTE HASH` |
| Audit date | `2026-07-16` (`Asia/Bangkok`) |
| Canonical baseline | `main@1ae679461e1f709488155ebf275ef070b54d723a` |
| Scope | Product tree `techpilot/**` |
| Current tracked-diff hash | `314114a5fde22c8071712679a2e46258633a3325` |

## Mục đích

Ghi lại bằng chứng về dirty product baseline trước governance và lần kiểm tra lại
sau khi governance docs được dựng. File này không hợp thức hóa, approve hoặc review
product delta.

## Quan sát ban đầu trước documentation setup

Các command audit ban đầu ghi nhận:

```text
git status --short --untracked-files=all -- techpilot
git diff --stat -- techpilot
```

Tracked product diff:

```text
25 files changed, 2522 insertions(+), 919 deletions(-)
```

25 tracked paths thuộc các nhóm:

- `techpilot/README.md`
- 4 controllers: Auth, Cart, Checkout, Home.
- `app/core/helpers.php`.
- 4 models: Brand, Order, Product và User; tổng tracked path được Git đếm là 25.
- Auth/cart/checkout/home/layout/product views.
- `config/database.php`, `database/schema.sql`.
- `public/assets/css/style.css`, `public/assets/js/main.js`, `public/index.php`.

Untracked product categories/paths:

- `techpilot/app/services/` (`CartService.php`).
- `techpilot/config/database.local.example.php`.
- `techpilot/database/README.md`.
- `techpilot/design/`, gồm UX/UI spec và `phase-0/` planning package.
- `techpilot/php-server.err.log` và `techpilot/php-server.out.log`.
- `techpilot/public/.htaccess`.
- `techpilot/public/design-v2/`, gồm prototype HTML/CSS/SVG và `.gitkeep`.

## Xác minh sau documentation setup

Sau khi các file governance ngoài `techpilot/**` được tạo, cùng các command scoped
trên được chạy lại. Kết quả vẫn là:

```text
25 files changed, 2522 insertions(+), 919 deletions(-)
```

Danh sách tracked modified paths và các untracked categories ở trên vẫn xuất hiện
trong product-scoped status.

Tracked product diff hiện tại được fingerprint bằng:

```text
git diff --binary -- techpilot | git hash-object --stdin
```

Kết quả post-setup:

```text
314114a5fde22c8071712679a2e46258633a3325
```

Hash này chỉ fingerprint tracked diff tại lần kiểm tra sau setup; không bao gồm
byte content của untracked files.

## Limitation bắt buộc

Không có pre-edit byte hash được chụp trước khi documentation setup bắt đầu. Vì
vậy không được tuyên bố đã chứng minh byte-for-byte rằng product tree bất biến.

Việc initial và post-setup status/stat cùng là 25 files, `+2522/-919`, cùng các
path/category là bằng chứng scoped hữu ích, nhưng không loại trừ tuyệt đối khả
năng content đổi mà tổng line stat không đổi. Post-setup hash không thể dùng hồi
tố như một pre-edit hash.

Kết luận được phép:

```text
Product-scoped Git status/stat matched the initial audit after governance setup.
No pre-edit byte hash exists, so immutable byte-level proof is unavailable.
```

Không được nâng kết luận này thành “product source chắc chắn không đổi” hoặc xem
dirty delta là approved candidate.

## Next action

- Human Project Owner quyết định cách giữ/tách/review product delta.
- Khi tạo candidate hợp lệ, Human materialize commit và ghi full SHA canonical.
- Các lần kiểm tra tiếp theo có thể so với post-setup hash trên, nhưng vẫn phải xử
  lý untracked files bằng manifest/hash riêng nếu cần byte-level proof.

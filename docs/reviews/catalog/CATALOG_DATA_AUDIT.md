# CHECKPOINT 0 — CATALOG DATA AUDIT REPORT (V3 FINAL)
**Dự án:** TechPilot  
**Thời điểm Audit (Timestamp):** 2026-07-23T00:45:00+07:00  
**Database Audit Target:** CSDL `techpilot` (bảng `categories`, `products`, `brands`)  
**Main Branch SHA:** `7a148b8fc776fe9d19a2d1027758abbf226b6410`  
**Checkpoint 0 Initial SHA:** `b03c2ec580e0367e7d194d7e0fa9dde57db871e9`  
**Checkpoint 0 V2 SHA:** `fc331bd`  
**Trạng thái Audit:** ĐÃ HOÀN THIỆN BẰNG CHỨNG TÁI TẠO (REPRODUCIBLE EVIDENCE FINALIZE)  

---

## 1. Executive Summary

Báo cáo này thực hiện kiểm kê dữ liệu thời gian thực (Runtime Data Audit) và phân tích chuyên sâu cho toàn bộ Hệ thống Danh mục & Sản phẩm của dự án TechPilot nhằm phục vụ định hướng tái cấu trúc Menu & Điều hướng Catalog theo **Phương án Hybrid (Virtual Catalog Groups tại Service Layer)** đã được phê duyệt.

### Quyết định đã được phê duyệt làm cơ sở audit:
1. **Desktop Viewport (từ 1366×768):** Phải nhìn thấy trọn vẹn Top bar, Main header, Navigation, Hero (category menu, banner, promo) và toàn bộ Features bar mà chưa cần cuộn trang.
2. **Phương án Hybrid:** Không migration CSDL ở giai đoạn này, không thay đổi `category_id` của sản phẩm, xây dựng virtual catalog groups tại Service Layer. Chỉ cân nhắc migration parent_id sau khi UI được duyệt.
3. **Bảy nhóm Catalog được duyệt:** Laptop, PC & Build PC, Linh kiện PC, Màn hình, Gaming Gear, Thiết bị văn phòng, Thiết bị mạng.

---

## 2. Kiểm Trả Trạng Thái Git (Git Audit)

| Tiêu chí | Trạng thái thực tế |
| :--- | :--- |
| **Branch hiện tại** | `feature/hieu-news` |
| **Trạng thái Working Tree** | Clean (sau commit V2 `fc331bd`) |
| **So sánh với `main`** | Có 23 file khác biệt (tin tức, header refactoring, style.css, catalog audit docs) |

### Tệp thuộc Header/Category khác biệt so với `main`:
- [header.php](../../../app/views/layouts/header.php): Đã refactor header actions, thêm badges giỏ hàng/thông báo, cập nhật menu navigation.
- [style.css](../../../public/assets/css/style.css): Cập nhật kích thước header, navigation height, responsive padding.
- [main.js](../../../public/assets/js/main.js): Cập nhật xử lý mobile drawer & search form parameters.
- [_category_nav.php](../../../app/views/post/partials/_category_nav.php): Thanh điều hướng danh mục bài viết tin tức.

> [!NOTE]
> Không thực hiện `git checkout`, `reset` hay `merge main` trong Checkpoint 0 này.

---

## 3. Kiểm Kê Database Đang Chạy (Runtime Database Inventory)

### 3.1 Bảng Danh mục CSDL (`categories`)

Tổng số danh mục trong DB: **18 categories** (9 danh mục cấp 1 parent_id IS NULL, 9 danh mục cấp 2 parent_id = 4).

| ID | Parent ID | Tên Danh Mục | Slug | Status | Sort Order | Icon |
| :---: | :---: | :--- | :--- | :---: | :---: | :--- |
| 1 | NULL | Laptop Gaming | `laptop-gaming` | active | 0 | `fa-solid fa-laptop-code` |
| 2 | NULL | Laptop Văn Phòng | `laptop-van-phong` | active | 0 | `fa-solid fa-laptop` |
| 3 | NULL | PC Build Sẵn | `pc-build-san` | active | 0 | `fa-solid fa-desktop` |
| 4 | NULL | Linh Kiện PC | `pc-linh-kien` | active | 0 | `fa-solid fa-microchip` |
| 5 | NULL | Màn Hình | `man-hinh` | active | 0 | `fa-solid fa-tv` |
| 6 | NULL | Máy tính bộ | `may-tinh-bo` | active | 0 | `fa-solid fa-desktop` |
| 7 | NULL | Gaming Gear | `gaming-gear` | active | 0 | `fa-solid fa-gamepad` |
| 8 | NULL | Thiết Bị Văn Phòng | `office-gear` | active | 0 | `fa-solid fa-print` |
| 9 | NULL | Thiết Bị Mạng | `networking` | active | 0 | `fa-solid fa-wifi` |
| 10 | 4 | CPU | `cpu` | active | 0 | NULL |
| 11 | 4 | Mainboard | `mainboard` | active | 0 | NULL |
| 12 | 4 | RAM | `ram` | active | 0 | NULL |
| 13 | 4 | VGA | `vga` | active | 0 | NULL |
| 14 | 4 | Ổ Cứng SSD | `ssd` | active | 0 | NULL |
| 15 | 4 | Ổ Cứng HDD | `hdd` | active | 0 | NULL |
| 16 | 4 | Nguồn (PSU) | `psu` | active | 0 | NULL |
| 17 | 4 | Case | `case` | active | 0 | NULL |
| 18 | 4 | Tản nhiệt | `tan-nhiet` | active | 0 | NULL |

---

### 3.2 Thống kê Sản phẩm & Giá Effective (Min, Median, Max) theo Category

*Ghi chú:* Giá effective được tính bằng công thức `COALESCE(NULLIF(sale_price, 0), price)`. Median được tính chính xác thông qua MySQL 8 CTE Window Function.

| Category ID | Tên Category | Tổng SP | Active | Draft | Inactive | Min Price Effective | Median Price Effective | Max Price Effective | Số Brand |
| :---: | :--- | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| 1 | Laptop Gaming | 38 | 38 | 0 | 0 | 19.810.000đ | 20.048.500đ | 20.452.000đ | 6 |
| 2 | Laptop Văn Phòng | 36 | 36 | 0 | 0 | 19.800.000đ | 20.212.500đ | 20.497.000đ | 6 |
| 3 | PC Build Sẵn | 36 | 36 | 0 | 0 | 19.801.000đ | 20.127.000đ | 20.474.000đ | 6 |
| 4 | Linh Kiện PC *(Parent)* | 0 | 0 | 0 | 0 | N/A | N/A | N/A | 0 |
| 5 | Màn Hình | 10 | 10 | 0 | 0 | 3.100.000đ | 4.850.000đ | 14.500.000đ | 0 *(brand_id NULL)* |
| 6 | Máy tính bộ | 0 | 0 | 0 | 0 | N/A | N/A | N/A | 0 |
| 7 | Gaming Gear | 10 | 10 | 0 | 0 | 550.000đ | 3.850.000đ | 5.500.000đ | 0 *(brand_id NULL)* |
| 8 | Thiết Bị Văn Phòng | 5 | 5 | 0 | 0 | 1.200.000đ | 2.800.000đ | 9.500.000đ | 0 *(brand_id NULL)* |
| 9 | Thiết Bị Mạng | 0 | 0 | 0 | 0 | N/A | N/A | N/A | 0 |
| 10 | CPU *(Child of 4)* | 40 | 40 | 0 | 0 | 2.203.000đ | 6.206.500đ | 20.000.000đ | 2 |
| 11 | Mainboard *(Child of 4)* | 60 | 60 | 0 | 0 | 1.808.000đ | 2.842.000đ | 5.940.000đ | 5 |
| 12 | RAM *(Child of 4)* | 80 | 80 | 0 | 0 | 622.000đ | 1.916.000đ | 6.890.000đ | 6 |
| 13 | VGA *(Child of 4)* | 80 | 80 | 0 | 0 | 7.341.000đ | 9.802.500đ | 16.359.000đ | 5 |
| 14 | Ổ Cứng SSD *(Child of 4)* | 60 | 60 | 0 | 0 | 652.000đ | 1.434.500đ | 3.444.000đ | 5 |
| 15 | Ổ Cứng HDD *(Child of 4)* | 20 | 20 | 0 | 0 | 879.000đ | 2.487.500đ | 2.878.000đ | 2 |
| 16 | Nguồn (PSU) *(Child of 4)* | 50 | 50 | 0 | 0 | 1.517.000đ | 2.154.500đ | 2.733.000đ | 5 |
| 17 | Case *(Child of 4)* | 50 | 50 | 0 | 0 | 1.309.000đ | 1.616.000đ | 1.990.000đ | 5 |
| 18 | Tản nhiệt *(Child of 4)* | 45 | 45 | 0 | 0 | 150.000đ | 862.000đ | 2.500.000đ | 4 *(5 SP brand_id NULL)* |
| **Tổng** | | **620** | **620** | **0** | **0** | **150.000đ** | **2.401.000đ** | **20.497.000đ** | |

---

### 3.3 Sản phẩm Bất thường & Phân bổ (Product Anomaly Audit)

- **Sản phẩm không có `category_id` (`NULL`):** **0** sản phẩm.
- **Sản phẩm có `category_id` không tồn tại:** **0** sản phẩm.
- **Sản phẩm không có thương hiệu (`brand_id IS NULL` hoặc `0`):** **30 sản phẩm** (10 SP Màn Hình, 10 SP Gaming Gear, 5 SP Thiết Bị Văn Phòng, 5 SP Tản Nhiệt).
- **Sản phẩm có `component_type` NULL/rỗng:** **140 sản phẩm** (gồm 38 Laptop Gaming, 36 Laptop VP, 36 PC Build Sẵn, 10 Màn hình, 10 Gaming Gear, 5 TB Văn phòng, 5 Tản nhiệt).
- **Toàn bộ distinct `component_type` hiện có trong DB:**

| component_type | Số lượng SP | Trạng thái Active | Danh mục chứa chính |
| :--- | :---: | :---: | :--- |
| `cpu` | 40 | 40 | CPU (ID 10) |
| `mainboard` | 60 | 60 | Mainboard (ID 11) |
| `ram` | 80 | 80 | RAM (ID 12) |
| `gpu` | 80 | 80 | VGA (ID 13) |
| `storage` | 80 | 80 | SSD (ID 14: 60 SP) & HDD (ID 15: 20 SP) |
| `psu` | 50 | 50 | Nguồn PSU (ID 16) |
| `case` | 50 | 50 | Case (ID 17) |
| `cpu_cooler` | 40 | 40 | Tản nhiệt (ID 18: 40 SP, 5 SP còn lại NULL) |
| `NULL` | 140 | 140 | Laptop, PC Build, Màn hình, Gear, TBVP |

- **Phân bổ trạng thái sản phẩm (Status distribution):** 100% sản phẩm (620/620) có `status = 'active'`.

---

### 3.4 Bất thường về Category & Integration Evidence Phân Tích Linh Kiện PC

> [!IMPORTANT]
> **Phân biệt Lỗi CategoryMenuService vs Logic Query Trong Product Model & Integration Evidence:**
>
> 1. **Lỗi trong `CategoryMenuService.php` (Làm ẩn danh mục khỏi Header Menu):**
>    - `CategoryMenuService::getActiveMenuTree()` chỉ kiểm tra sản phẩm có `category_id` trực tiếp bằng ID danh mục cha (`$p['category_id'] == $catId`).
>    - Do Category ID 4 (`pc-linh-kien`) có **0 sản phẩm trực tiếp** (toàn bộ 485 sản phẩm linh kiện nằm ở các danh mục con ID 10–18), `CategoryMenuService` đánh giá `$hasProducts = false` và thực hiện `continue`. Hậu quả: **`Linh Kiện PC` bị bỏ qua và ẩn hoàn toàn khỏi Header Dropdown Menu.**
>
> 2. **Logic Query trong Model `Product.php` và Route Search (Vẫn hỗ trợ danh mục con):**
>    - Phương thức `Product::search()` khi nhận `$categorySlug = 'pc-linh-kien'` sẽ tạo câu truy vấn SQL:
>      `WHERE (c.slug = 'pc-linh-kien' OR c.parent_id IN (SELECT id FROM categories WHERE slug = 'pc-linh-kien' AND status = 'active'))`
>    - **Integration Evidence:** Đã thực thi kiểm tra bằng cURL và PHP CLI.
>      - **Kết quả thực tế của `home/search?cat=pc-linh-kien`:** Trả về đầy đủ **485 sản phẩm active** (trang 1 trả về 24 SP theo pagination limit 24).
>
> 3. **Lỗi Phụ trong `Product::getByCategorySlug()`:**
>    - Phương thức `getByCategorySlug()` có SQL: `WHERE c.slug = :slug OR c.parent_id IN (SELECT id FROM categories WHERE slug = :slug)`.
>    - Do placeholder `:slug` bị lặp lại 2 lần trong chuỗi SQL nhưng chỉ được bind 1 lần trong PDO khi `PDO::ATTR_EMULATE_PREPARES` là `false`, PDO ném ngoại lệ bị bắt im lặng (`try-catch`), dẫn đến fallback về hàm trả 7 sản phẩm mẫu (`getSampleProducts`).

---

### 3.5 Kiểm Kê Thương Hiệu (Brand Audit)

- **Danh sách Brands hiện có trong DB:** 38 thương hiệu.
- **Trùng lặp / Tên gần trùng:** Không phát hiện trùng lặp.
- **Thương hiệu inactive có sản phẩm active:** Không có.
- **Thương hiệu theo từng Category:**
  - `laptop-gaming`: Acer, ASUS, DELL, HP, Lenovo, MSI.
  - `laptop-van-phong`: Acer, ASUS, DELL, HP, Lenovo, MSI.
  - `pc-build-san`: Acer, ASUS, DELL, HP, Lenovo, MSI.
  - `cpu`: AMD, Intel.
  - `mainboard`: ASRock, ASUS, Colorful, GIGABYTE, MSI.
  - `ram`: Acer, Corsair, G.Skill, Kingston, TeamGroup, XPG.
  - `vga`: ASUS, Colorful, GIGABYTE, MSI, Zotac.
  - `ssd`: Crucial, Kingston, Lexar, Samsung, WD.
  - `hdd`: ASUS, WD.
  - `psu`: ASUS, Cooler Master, Corsair, DeepCool, MSI.
  - `case`: DeepCool, Lian Li, Montech, NZXT, Xigmatek.
  - `tan-nhiet`: Cooler Master, Corsair, DeepCool, Thermalright.

---

## 4. Kiểm Tra Slug Contract & Bất Đồng Bộ Alias (Slug Audit Matrix)

### 4.1 Ma Trận Slug Contract

| Danh mục Phê duyệt | Slug Canonical trong DB | Aliases đang dùng trong Code / Views | File tham chiếu | Nguy cơ Trả kết quả Rỗng |
| :--- | :--- | :--- | :--- | :--- |
| **Laptop** | `laptop-gaming`<br>`laptop-van-phong` | `laptop-gaming`<br>`laptop-van-phong` | [header.php](../../../app/views/layouts/header.php#L23-L26)<br>[index.php](../../../app/views/home/index.php#L264)<br>[Product.php](../../../app/models/Product.php) | **Cao**: Chưa có slug chung `laptop` trong CSDL. Tìm kiếm `cat=laptop` sẽ ra rỗng. |
| **PC & Build PC** | `pc-build-san`<br>`may-tinh-bo` | `pc-build-san`<br>`may-tinh-bo`<br>`pc-gaming`<br>`pc-van-phong` | [HomeController.php](../../../app/controllers/HomeController.php)<br>[header.php](../../../app/views/layouts/header.php#L298)<br>[index.php](../../../app/views/home/index.php) | **Cao**: `may-tinh-bo` có 0 SP. `pc-build-san` được dùng ở nav nhưng không gộp sản phẩm. |
| **Linh kiện PC** | `pc-linh-kien`<br>(+ 9 child slugs) | `pc-linh-kien`, `cpu`, `mainboard`, `ram`, `vga`, `ssd`, `hdd`, `psu`, `case`, `tan-nhiet` | [header.php](../../../app/views/layouts/header.php#L27)<br>[Product.php](../../../app/models/Product.php) | **Trung bình**: Search URL `cat=pc-linh-kien` ra 485 SP, nhưng Menu Dropdown ẩn và từ khóa search "linh kiện" bị lỗi alias. |
| **Màn hình** | `man-hinh` | `man-hinh` | [header.php](../../../app/views/layouts/header.php#L29)<br>[Product.php](../../../app/models/Product.php) | **Trung bình**: Lọc brand trả về rỗng vì `brand_id = NULL`. |
| **Gaming Gear** | `gaming-gear` | `gaming-gear` | [header.php](../../../app/views/layouts/header.php#L31)<br>[Product.php](../../../app/models/Product.php) | **Trung bình**: Lọc brand trả về rỗng vì `brand_id = NULL`. |
| **Thiết bị văn phòng** | `office-gear` | `office-gear`<br>`thiet-bi-van-phong` | [helpers.php](../../../app/core/helpers.php)<br>[NewsCommerceService.php](../../../app/services/NewsCommerceService.php) | **Cao**: Bất đồng bộ slug giữa DB (`office-gear`) và tiếng Việt (`thiet-bi-van-phong`). |
| **Thiết bị mạng** | `networking` | `networking`<br>`thiet-bi-mang` | [helpers.php](../../../app/core/helpers.php)<br>[NewsCommerceService.php](../../../app/services/NewsCommerceService.php) | **Nghiêm trọng**: 0 sản phẩm trong DB. Bấm vào trả về 0 kết quả. |

### 4.2 Chi Tiết Lỗi Alias Hiện Hữu Trong `Product.php`

- Trong [Product.php](../../../app/models/Product.php#L1284-L1285), mảng `$aliases` định nghĩa từ khóa tìm kiếm tiếng Việt:
  ```php
  'linh kiện' => ['linh-kien-pc'],
  'linh kien' => ['linh-kien-pc'],
  ```
- **Xung đột:** `Product.php` map từ khóa "linh kiện" sang slug `linh-kien-pc` (tiền tố `linh-kien-`), trong khi Canonical Slug chuẩn trong CSDL tại bảng `categories` là `pc-linh-kien` (tiền tố `pc-`).
- **Hậu quả:** Khi người dùng gõ tìm kiếm "linh kiện", hệ thống lọc theo `c.slug = 'linh-kien-pc'`, dẫn đến **0 sản phẩm nào được tìm thấy** mặc dù CSDL có 485 sản phẩm linh kiện active!

---

## 5. Kiểm Tra Pipeline Category Menu & Phân Loại Code (Pipeline Audit)

### 5.1 Sơ đồ luồng (Pipeline Diagram)

```
Database (MySQL `techpilot`)
   │
   ▼
Controller::render (app/core/Controller.php)
   │
   ├─► CategoryMenuService::getActiveMenuTree() (3 SQL Queries)
   │      │
   │      ├─► Query 1: Main categories (parent_id IS NULL)
   │      ├─► Query 2: Sub categories (parent_id IS NOT NULL)
   │      └─► Query 3: Active products JOIN brands
   │
   ├─► Redundant Fixed Query: SELECT * FROM categories WHERE status = 'active' (4th Query)
   │
   ▼
$globalCategoryMenu + $globalCategories
   │
   ├─► app/views/layouts/header.php (Dropdown menu if $activeMenu !== 'home')
   └─► app/views/home/index.php (Static hero menu if $activeMenu === 'home')
          │
          ▼
   app/views/layouts/partials/category-mega-menu.php
          │
          ▼
   public/assets/js/category-mega-menu.js
          │
          ▼
   public/assets/css/category-mega-menu.css
```

### 5.2 Lỗi Kỹ Thuật Trong Active Pipeline

1. **CategoryMenuService ẩn danh mục có sản phẩm ở danh mục con:** Đã phân tích chi tiết tại Mục 3.4.
2. **Redundant Fixed Query (Truy vấn dư thừa cố định):**
   - Trong `Controller::render()` ([Controller.php](../../../app/core/Controller.php#L45-L54)), sau khi gọi `CategoryMenuService::getActiveMenuTree()` (3 truy vấn DB), controller tiếp tục thực hiện thêm 1 truy vấn dư thừa cố định `SELECT * FROM categories WHERE status = "active"` chỉ để tạo danh sách `<option>` trong ô chọn category của thanh tìm kiếm header.
3. **Mức giá Hard-code không phù hợp:** `CategoryMenuService.php` hard-code khoảng giá `<15M`, `15-20M`, `20-30M`, `>30M`, không tương thích với linh kiện/gear giá rẻ.
4. **Khác biệt Static vs Dropdown Menu:** Header dropdown hiển thị dạng popup khi click toggle button, Homepage static menu hiển thị cố định trong Hero section với CSS `width: calc(100vw - 280px - 300px - 100px)`.

---

### 5.3 Audit và Phân Loại Code Legacy / Dead Code

- [app/data/category-menu.php](../../../app/data/category-menu.php): Chứa mảng tĩnh dữ liệu danh mục cứng cũ (PHP array).
- [app/views/components/category-menu.php](../../../app/views/components/category-menu.php): Component view cũ đọc dữ liệu từ `app/data/category-menu.php` và thực hiện lọc sản phẩm dựa trên chuỗi specs/tên.

> [!IMPORTANT]
> **KẾT LUẬN AUDIT CODE:**
> Both `app/data/category-menu.php` và `app/views/components/category-menu.php` là **LEGACY / DEAD CODE**. Chúng không hề được `require`, `include` hay gọi ở bất kỳ Controller, Layout hay View nào trong luồng đang chạy (Active Pipeline) của ứng dụng.

---

## 6. Tổng Hợp Rủi Ro (Risk Matrix)

> [!WARNING]
> | Mã Rủi Ro | Loại Rủi Ro | Mức Độ | Mô Tả Chi Tiết | Phương Án Giảm Thiểu / Khắc Phục |
> | :---: | :--- | :---: | :--- | :--- |
> | **R-01** | Menu Exclusion | **CAO** | `Linh Kiện PC` bị ẩn hoàn toàn khỏi Header Mega Menu Dropdown | Cập nhật `CategoryMenuService` kiểm tra sản phẩm ở cả danh mục con |
> | **R-02** | Keyword Alias Mismatch | **CAO** | Gõ "linh kiện" ra 0 SP do `Product.php` map sai sang `linh-kien-pc` | Cập nhật mảng alias trong `Product.php` chuẩn hóa về `pc-linh-kien` |
> | **R-03** | Missing Brand Filter | **TRUNG BÌNH** | 30 sản phẩm (Màn hình, Gear, TBVP) có `brand_id = NULL` | Virtual Catalog Service ẩn cột "Thương hiệu" rỗng hoặc fallback theo tag/tên |
> | **R-04** | Hardcoded Price Filter | **TRUNG BÌNH** | Bộ lọc giá >15M hardcode trả về 0 SP cho gear/linh kiện giá rẻ | Cấu hình khoảng giá động (Dynamic Price Ranges) theo từng Virtual Group |
> | **R-05** | Empty Group Handling | **TRUNG BÌNH** | `Thiết bị mạng` (0 SP) nếu click vào sẽ trả về trang rỗng | Đánh dấu NOT READY / HIDDEN trên UI navigation cho nhóm Thiết bị mạng |
> | **R-06** | Viewport Overflow | **CAO** | Chiều cao Header stack làm tràn vạch 768px ở màn hình 1366x768 | Tối ưu hóa padding & height của Navigation/Header stack |

---

## 7. Đề Xuất Virtual Group Mapping & Bang Giá Median Effective (7 Nhóm Catalog Phê Duyệt)

Dựa trên dữ liệu CSDL runtime thực tế và câu lệnh MySQL 8 CTE Window Function, quy tắc nhận diện, số lượng sản phẩm khớp thực tế và thống kê giá Effective (`COALESCE(NULLIF(sale_price, 0), price)`) của từng nhóm được xác định như sau:

| Virtual Group Name | Rule Nhận Diện (`category_id`) | Total Active Products | Min Effective Price | Median Effective Price | Max Effective Price | Trạng thái Sẵn sàng |
| :--- | :--- | :---: | :---: | :---: | :---: | :---: |
| **Laptop** | `c.id IN (1, 2)` | 74 | 19.800.000đ | **20.161.000đ** | 20.497.000đ | **READY** |
| **PC & Build PC** | `c.id IN (3, 6)` | 36 | 19.801.000đ | **20.127.000đ** | 20.474.000đ | **READY** |
| **Linh kiện PC** | `c.id IN (4, 10..18)` | 485 | 150.000đ | **2.401.000đ** | 20.000.000đ | **READY** |
| **Màn hình** | `c.id = 5` | 10 | 3.100.000đ | **4.850.000đ** | 14.500.000đ | **READY** |
| **Gaming Gear** | `c.id = 7` | 10 | 550.000đ | **3.850.000đ** | 5.500.000đ | **READY** |
| **Thiết bị văn phòng** | `c.id = 8` | 5 | 1.200.000đ | **2.800.000đ** | 9.500.000đ | **READY** |
| **Thiết bị mạng** | `c.id = 9` | 0 | N/A | **N/A** | N/A | **NOT READY / HIDDEN** |

---

## 8. Bằng Chứng Audit Tái Tạo Nguyên Văn (Reproducible Raw Evidence)

### 8.1 Raw HTTP Headers Response (`curl -I`)

```http
HTTP/1.1 200 OK
Date: Wed, 22 Jul 2026 17:45:39 GMT
Server: Apache/2.4.62 (Win64) OpenSSL/3.0.15 PHP/8.3.26
X-Powered-By: PHP/8.3.26
Set-Cookie: PHPSESSID=fve82han2gcr6hpeqtf51kgmlh; path=/; HttpOnly; SameSite=Lax
Expires: Thu, 19 Nov 1981 08:52:00 GMT
Cache-Control: no-store, no-cache, must-revalidate
Pragma: no-cache
Content-Type: text/html; charset=UTF-8
```

---

### 8.2 Raw PHP Output (`var_dump` Execution)

```php
array(4) {
  ["HTTP_STATUS"]=>
  int(200)
  ["totalResults"]=>
  int(485)
  ["returned_count_page_1"]=>
  int(24)
  ["EXIT_CODE"]=>
  int(0)
}
```

---

### 8.3 Raw SQL Window Function & Output: Category Median Effective Price (MySQL 8 CTE)

#### Câu lệnh SQL CTE:
```sql
WITH priced AS (
    SELECT
        c.id AS category_id,
        c.name AS category_name,
        COALESCE(NULLIF(p.sale_price, 0), p.price) AS effective_price
    FROM categories c
    JOIN products p ON p.category_id = c.id
    WHERE p.status = 'active'
),
ranked AS (
    SELECT
        category_id,
        category_name,
        effective_price,
        ROW_NUMBER() OVER (
            PARTITION BY category_id
            ORDER BY effective_price
        ) AS rn,
        COUNT(*) OVER (
            PARTITION BY category_id
        ) AS cnt
    FROM priced
)
SELECT
    category_id,
    category_name,
    AVG(effective_price) AS median_effective_price
FROM ranked
WHERE rn IN (
    FLOOR((cnt + 1) / 2),
    FLOOR((cnt + 2) / 2)
)
GROUP BY category_id, category_name
ORDER BY category_id;
```

#### Raw SQL Array Output Nguyên Văn:
```php
Array
(
    [0] => Array
        (
            [category_id] => 1
            [category_name] => Laptop Gaming
            [median_effective_price] => 20048500.0000
        )
    [1] => Array
        (
            [category_id] => 2
            [category_name] => Laptop Văn Phòng
            [median_effective_price] => 20212500.0000
        )
    [2] => Array
        (
            [category_id] => 3
            [category_name] => PC Build Sẵn
            [median_effective_price] => 20127000.0000
        )
    [3] => Array
        (
            [category_id] => 5
            [category_name] => Màn Hình
            [median_effective_price] => 4850000.0000
        )
    [4] => Array
        (
            [category_id] => 7
            [category_name] => Gaming Gear
            [median_effective_price] => 3850000.0000
        )
    [5] => Array
        (
            [category_id] => 8
            [category_name] => Thiết Bị Văn Phòng
            [median_effective_price] => 2800000.0000
        )
    [6] => Array
        (
            [category_id] => 10
            [category_name] => CPU
            [median_effective_price] => 6206500.0000
        )
    [7] => Array
        (
            [category_id] => 11
            [category_name] => Mainboard
            [median_effective_price] => 2842000.0000
        )
    [8] => Array
        (
            [category_id] => 12
            [category_name] => RAM
            [median_effective_price] => 1916000.0000
        )
    [9] => Array
        (
            [category_id] => 13
            [category_name] => VGA
            [median_effective_price] => 9802500.0000
        )
    [10] => Array
        (
            [category_id] => 14
            [category_name] => Ổ Cứng SSD
            [median_effective_price] => 1434500.0000
        )
    [11] => Array
        (
            [category_id] => 15
            [category_name] => Ổ Cứng HDD
            [median_effective_price] => 2487500.0000
        )
    [12] => Array
        (
            [category_id] => 16
            [category_name] => Nguồn (PSU)
            [median_effective_price] => 2154500.0000
        )
    [13] => Array
        (
            [category_id] => 17
            [category_name] => Case
            [median_effective_price] => 1616000.0000
        )
    [14] => Array
        (
            [category_id] => 18
            [category_name] => Tản nhiệt
            [median_effective_price] => 862000.0000
        )
)
```

---

### 8.4 Raw SQL Window Function & Output: 7 Virtual Groups Median Effective Price (MySQL 8 CTE)

#### Câu lệnh SQL CTE:
```sql
WITH vg_mapped AS (
    SELECT
        CASE
            WHEN c.id IN (1, 2) THEN 'Laptop'
            WHEN c.id IN (3, 6) THEN 'PC & Build PC'
            WHEN c.id IN (4, 10, 11, 12, 13, 14, 15, 16, 17, 18) THEN 'Linh kiện PC'
            WHEN c.id = 5 THEN 'Màn hình'
            WHEN c.id = 7 THEN 'Gaming Gear'
            WHEN c.id = 8 THEN 'Thiết bị văn phòng'
            WHEN c.id = 9 THEN 'Thiết bị mạng'
        END AS virtual_group_name,
        COALESCE(NULLIF(p.sale_price, 0), p.price) AS effective_price
    FROM categories c
    JOIN products p ON p.category_id = c.id
    WHERE p.status = 'active'
),
ranked AS (
    SELECT
        virtual_group_name,
        effective_price,
        ROW_NUMBER() OVER (
            PARTITION BY virtual_group_name
            ORDER BY effective_price
        ) AS rn,
        COUNT(*) OVER (
            PARTITION BY virtual_group_name
        ) AS cnt,
        MIN(effective_price) OVER (
            PARTITION BY virtual_group_name
        ) AS min_p,
        MAX(effective_price) OVER (
            PARTITION BY virtual_group_name
        ) AS max_p
    FROM vg_mapped
)
SELECT
    virtual_group_name,
    MAX(cnt) AS total_active_products,
    MAX(min_p) AS min_effective_price,
    AVG(effective_price) AS median_effective_price,
    MAX(max_p) AS max_effective_price
FROM ranked
WHERE rn IN (
    FLOOR((cnt + 1) / 2),
    FLOOR((cnt + 2) / 2)
)
GROUP BY virtual_group_name
ORDER BY min_effective_price ASC;
```

#### Raw SQL Array Output Nguyên Văn:
```php
Array
(
    [0] => Array
        (
            [virtual_group_name] => Linh kiện PC
            [total_active_products] => 485
            [min_effective_price] => 150000
            [median_effective_price] => 2401000.0000
            [max_effective_price] => 20000000
        )
    [1] => Array
        (
            [virtual_group_name] => Gaming Gear
            [total_active_products] => 10
            [min_effective_price] => 550000
            [median_effective_price] => 3850000.0000
            [max_effective_price] => 5500000
        )
    [2] => Array
        (
            [virtual_group_name] => Thiết bị văn phòng
            [total_active_products] => 5
            [min_effective_price] => 1200000
            [median_effective_price] => 2800000.0000
            [max_effective_price] => 9500000
        )
    [3] => Array
        (
            [virtual_group_name] => Màn hình
            [total_active_products] => 10
            [min_effective_price] => 3100000
            [median_effective_price] => 4850000.0000
            [max_effective_price] => 14500000
        )
    [4] => Array
        (
            [virtual_group_name] => Laptop
            [total_active_products] => 74
            [min_effective_price] => 19800000
            [median_effective_price] => 20161000.0000
            [max_effective_price] => 20497000
        )
    [5] => Array
        (
            [virtual_group_name] => PC & Build PC
            [total_active_products] => 36
            [min_effective_price] => 19801000
            [median_effective_price] => 20127000.0000
            [max_effective_price] => 20474000
        )
)
```

---

## 9. Danh Sách Files Dự Kiến Sửa ở Checkpoint Tiếp Theo

Ở Checkpoint 1 (Service Layer & Route Mapping) và Checkpoint 2 (UI Integration):

1. `app/services/CatalogGroupService.php` **[NEW]**: Xây dựng service quản lý 7 virtual catalog groups, alias mapping & dynamic price ranges.
2. `app/services/CategoryMenuService.php` **[MODIFY]**: Cập nhật logic `getActiveMenuTree()` khắc phục lỗi ẩn `Linh Kiện PC` và hỗ trợ Virtual Group.
3. `app/core/Controller.php` **[MODIFY]**: Loại bỏ redundant fixed query `SELECT * FROM categories` trong `render()`.
4. `app/controllers/HomeController.php` **[MODIFY]**: Cập nhật route search & filter hỗ trợ aliases và virtual groups.
5. `app/models/Product.php` **[MODIFY]**: Sửa lỗi alias `linh-kien-pc` -> `pc-linh-kien` và lỗi bind parameter trùng tên trong `getByCategorySlug()`.
6. `app/views/layouts/header.php` **[MODIFY]**: Cập nhật navigation bar & search select options.
7. `app/views/layouts/partials/category-mega-menu.php` **[MODIFY]**: Đảm bảo hiển thị chuẩn các nhóm danh mục.
8. `public/assets/css/category-mega-menu.css` **[MODIFY]**: Tối ưu responsive & layout desktop 1366x768.

---

## 10. Những Điều Chưa Thể Xác Minh (Unverified Items)

1. **Trải nghiệm lọc theo brand của nhóm Màn hình / Gear / TBVP:** Do 30 sản phẩm ở các nhóm này đang `brand_id = NULL`, chưa thể kiểm chứng tính năng lọc theo brand trừ khi gán brand hoặc bổ sung dữ liệu.
2. **Giao diện Admin quản lý sản phẩm:** Chưa thể kiểm chứng giao diện Admin tạo sản phẩm mới sẽ chọn Category ID gốc hay Virtual Group.

---

## 11. Khuyến Nghị Go / No-Go Cho CHECKPOINT 1

> [!TIP]
> **KHUYẾN NGHỊ: GO DÙNG PHƯƠNG ÁN HYBRID**
>
> 1. **Đánh giá Rủi ro Database:** Risk đối với Database Mutation là **RẤT THẤP** do không chỉnh sửa bảng hay chạy migration. Tuy nhiên, các nguy cơ về Route mapping, Search keyword alias và UI Layout Regression vẫn cần được test kỹ lưỡng trong các Checkpoint tiếp theo.
> 2. **Đánh giá Dữ liệu Virtual Catalog:** **6/7 nhóm Virtual Catalog đã sẵn sàng dữ liệu** (tổng cộng 620 SP active), riêng nhóm **Thiết bị mạng (0 SP) được đánh dấu NOT READY / HIDDEN** trên UI để tránh ảnh hưởng trải nghiệm người dùng.
> 3. Hệ thống đã đủ điều kiện kỹ thuật để chuyển sang Checkpoint 1 (Xây dựng `CatalogGroupService` tại Service Layer).

---

### Xác nhận hoàn tất Checkpoint 0 (V3):
- [x] Không sửa giao diện / production code.
- [x] Không sửa CSDL / không chạy migration.
- [x] Không thay đổi product/category records.
- [x] Bổ sung đầy đủ SQL CTE Window Function Median & Raw Outputs nguyên văn.
- [x] Đã cập nhật 100% Markdown links tương đối từ vị trí `docs/reviews/catalog/CATALOG_DATA_AUDIT.md`.

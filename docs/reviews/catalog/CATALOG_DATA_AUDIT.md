# CHECKPOINT 0 — CATALOG DATA AUDIT REPORT
**Dự án:** TechPilot  
**Ngày thực hiện:** 22/07/2026  
**Trạng thái Audit:** HOÀN THÀNH — CHO PHÉP XEM XÉT GO/NO-GO  

---

## 1. Executive Summary

Báo cáo này thực hiện kiểm kê dữ liệu thời gian thực (Runtime Data Audit) cho toàn bộ Hệ thống Danh mục & Sản phẩm của dự án TechPilot nhằm phục vụ định hướng tái cấu trúc Menu & Điều hướng Catalog theo **Phương án Hybrid (Virtual Catalog Groups tại Service Layer)** đã được phê duyệt.

### Quyết định đã được phê duyệt làm cơ sở audit:
1. **Desktop Viewport (từ 1366×768):** Phải hiển thị trọn vẹn Top bar, Main header, Navigation, Hero (category menu, banner, promo) và toàn bộ Features bar mà không cần cuộn trang.
2. **Phương án Hybrid:** Không migration CSDL ở giai đoạn này, giữ nguyên `category_id` sản phẩm hiện tại, xây dựng virtual catalog groups tại Service Layer.
3. **Bảy nhóm Catalog được duyệt:** Laptop, PC & Build PC, Linh kiện PC, Màn hình, Gaming Gear, Thiết bị văn phòng, Thiết bị mạng.

---

## 2. Kiểm Trả Trạng Thái Git (Git Audit)

| Tiêu chí | Trạng thái thực tế |
| :--- | :--- |
| **Branch hiện tại** | `feature/hieu-news` |
| **Trạng thái Working Tree** | Clean (nothing to commit, working tree clean) |
| **So sánh với `main`** | Có 22 file khác biệt (tin tức, header refactoring, style.css, v.v.) |

### Tệp thuộc Header/Category khác biệt so với `main`:
- [header.php](file:///d:/laragon/www/TechPilot/app/views/layouts/header.php): Đã refactor header actions, thêm badges giỏ hàng/thông báo, cập nhật menu navigation.
- [style.css](file:///d:/laragon/www/TechPilot/public/assets/css/style.css): Cập nhật kích thước header, navigation height, responsive padding.
- [main.js](file:///d:/laragon/www/TechPilot/public/assets/js/main.js): Cập nhật xử lý mobile drawer & search form parameters.
- [_category_nav.php](file:///d:/laragon/www/TechPilot/app/views/post/partials/_category_nav.php): Thanh điều hướng danh mục bài viết tin tức.

> [!NOTE]
> Không thực hiện `git checkout`, `reset` hay `merge main` trong Checkpoint 0 này để đảm bảo an toàn tuyệt đối cho code hiện tại.

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

### 3.2 Thống kê Sản phẩm & Khoảng giá theo Category

| Category ID | Tên Category | Tổng SP | SP Active | SP Draft | SP Inactive | Min Price | Max Price | Số Brand khác nhau |
| :---: | :--- | :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| 1 | Laptop Gaming | 38 | 38 | 0 | 0 | 19.810.000đ | 20.452.000đ | 6 |
| 2 | Laptop Văn Phòng | 36 | 36 | 0 | 0 | 19.800.000đ | 20.497.000đ | 6 |
| 3 | PC Build Sẵn | 36 | 36 | 0 | 0 | 19.801.000đ | 20.474.000đ | 6 |
| 4 | Linh Kiện PC *(Parent)* | 0 | 0 | 0 | 0 | N/A | N/A | 0 |
| 5 | Màn Hình | 10 | 10 | 0 | 0 | 3.100.000đ | 14.500.000đ | 0 *(brand_id NULL)* |
| 6 | Máy tính bộ | 0 | 0 | 0 | 0 | N/A | N/A | 0 |
| 7 | Gaming Gear | 10 | 10 | 0 | 0 | 550.000đ | 5.500.000đ | 0 *(brand_id NULL)* |
| 8 | Thiết Bị Văn Phòng | 5 | 5 | 0 | 0 | 1.200.000đ | 9.500.000đ | 0 *(brand_id NULL)* |
| 9 | Thiết Bị Mạng | 0 | 0 | 0 | 0 | N/A | N/A | 0 |
| 10 | CPU *(Child of 4)* | 40 | 40 | 0 | 0 | 2.203.000đ | 20.000.000đ | 2 |
| 11 | Mainboard *(Child of 4)* | 60 | 60 | 0 | 0 | 1.808.000đ | 5.940.000đ | 5 |
| 12 | RAM *(Child of 4)* | 80 | 80 | 0 | 0 | 622.000đ | 6.890.000đ | 6 |
| 13 | VGA *(Child of 4)* | 80 | 80 | 0 | 0 | 7.341.000đ | 16.359.000đ | 5 |
| 14 | Ổ Cứng SSD *(Child of 4)* | 60 | 60 | 0 | 0 | 652.000đ | 3.444.000đ | 5 |
| 15 | Ổ Cứng HDD *(Child of 4)* | 20 | 20 | 0 | 0 | 879.000đ | 2.878.000đ | 2 |
| 16 | Nguồn (PSU) *(Child of 4)* | 50 | 50 | 0 | 0 | 1.517.000đ | 2.733.000đ | 5 |
| 17 | Case *(Child of 4)* | 50 | 50 | 0 | 0 | 1.309.000đ | 1.990.000đ | 5 |
| 18 | Tản nhiệt *(Child of 4)* | 45 | 45 | 0 | 0 | 150.000đ | 2.500.000đ | 4 *(5 SP brand_id NULL)* |
| **Tổng** | | **620** | **620** | **0** | **0** | **150.000đ** | **20.497.000đ** | |

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

### 3.4 Bất thường về Category (Category Anomaly Audit)

1. **Category không có sản phẩm trực tiếp (`parent_direct_products = 0`):**
   - Category ID 4 (`pc-linh-kien`): 0 sản phẩm trực tiếp. **Tuy nhiên có 9 category con chứa tổng cộng 485 sản phẩm active.**
   - Category ID 6 (`may-tinh-bo`): 0 sản phẩm trong CSDL.
   - Category ID 9 (`networking` - Thiết Bị Mạng): 0 sản phẩm trong CSDL.
2. **Category chỉ có sản phẩm draft/inactive:** Không có (tất cả 620 SP đều active).
3. **Category có child categories:** Duy nhất Category ID 4 (`Linh Kiện PC`) có 9 child categories (ID 10 đến ID 18).
4. **Mâu thuẫn Parent vs Child Product Count:** Category ID 4 (`Linh Kiện PC`) có 0 SP trực tiếp nhưng các con có 485 SP. Nếu query danh mục theo `WHERE category_id = 4` sẽ trả về 0 sản phẩm.

---

### 3.5 Kiểm Kê Thương Hiệu (Brand Audit)

- **Danh sách Brands hiện có trong DB:** 38 thương hiệu (Acer, ASUS, DELL, HP, Lenovo, MSI, Intel, AMD, GIGABYTE, ASRock, Zotac, Colorful, Corsair, Kingston, WD, Lexar, DeepCool, Thermalright, Montech, NZXT, Lian Li, G.Skill, TeamGroup, XPG, Apacer, Sapphire, Crucial, Cooler Master, Xigmatek, Thermaltake, be quiet!, PowerColor, v.v.).
- **Trùng lặp / Tên gần trùng:** Không phát hiện tên thương hiệu trùng lặp trong bảng `brands`.
- **Thương hiệu inactive có sản phẩm active:** Không có (`inactive_brands_with_active_products = []`).
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

## 4. Kiểm Tra Slug Contract (Slug Audit Matrix)

| Danh mục Phê duyệt | Slug Canonical trong DB | Aliases đang dùng trong Code / Views | File tham chiếu | Nguy cơ Trả kết quả Rỗng |
| :--- | :--- | :--- | :--- | :--- |
| **Laptop** | `laptop-gaming`<br>`laptop-van-phong` | `laptop-gaming`<br>`laptop-van-phong` | [header.php](file:///d:/laragon/www/TechPilot/app/views/layouts/header.php#L23-L26)<br>[index.php](file:///d:/laragon/www/TechPilot/app/views/home/index.php#L264)<br>[Product.php](file:///d:/laragon/www/TechPilot/app/models/Product.php) | **Cao**: Chưa có slug chung `laptop` trong CSDL. Tìm kiếm `cat=laptop` sẽ ra rỗng. |
| **PC & Build PC** | `pc-build-san`<br>`may-tinh-bo` | `pc-build-san`<br>`may-tinh-bo`<br>`pc-gaming`<br>`pc-van-phong` | [HomeController.php](file:///d:/laragon/www/TechPilot/app/controllers/HomeController.php)<br>[header.php](file:///d:/laragon/www/TechPilot/app/views/layouts/header.php#L298)<br>[index.php](file:///d:/laragon/www/TechPilot/app/views/home/index.php) | **Cao**: `may-tinh-bo` có 0 SP. `pc-build-san` được dùng ở nav nhưng không gộp sản phẩm. |
| **Linh kiện PC** | `pc-linh-kien`<br>(+ 9 child slugs) | `pc-linh-kien`, `cpu`, `mainboard`, `ram`, `vga`, `ssd`, `hdd`, `psu`, `case`, `tan-nhiet` | [header.php](file:///d:/laragon/www/TechPilot/app/views/layouts/header.php#L27)<br>[Product.php](file:///d:/laragon/www/TechPilot/app/models/Product.php)<br>[category-menu.php](file:///d:/laragon/www/TechPilot/app/data/category-menu.php) | **Nghiêm trọng**: Bấm vào `cat=pc-linh-kien` trả về **0 sản phẩm** do SP nằm trong 9 category con. |
| **Màn hình** | `man-hinh` | `man-hinh` | [header.php](file:///d:/laragon/www/TechPilot/app/views/layouts/header.php#L29)<br>[Product.php](file:///d:/laragon/www/TechPilot/app/models/Product.php) | **Trung bình**: Lọc brand trả về rỗng vì `brand_id = NULL`. |
| **Gaming Gear** | `gaming-gear` | `gaming-gear` | [header.php](file:///d:/laragon/www/TechPilot/app/views/layouts/header.php#L31)<br>[Product.php](file:///d:/laragon/www/TechPilot/app/models/Product.php) | **Trung bình**: Lọc brand trả về rỗng vì `brand_id = NULL`. |
| **Thiết bị văn phòng** | `office-gear` | `office-gear`<br>`thiet-bi-van-phong` | [helpers.php](file:///d:/laragon/www/TechPilot/app/core/helpers.php)<br>[NewsCommerceService.php](file:///d:/laragon/www/TechPilot/app/services/NewsCommerceService.php) | **Cao**: Bất đồng bộ slug giữa DB (`office-gear`) và tiếng Việt (`thiet-bi-van-phong`). |
| **Thiết bị mạng** | `networking` | `networking`<br>`thiet-bi-mang` | [helpers.php](file:///d:/laragon/www/TechPilot/app/core/helpers.php)<br>[NewsCommerceService.php](file:///d:/laragon/www/TechPilot/app/services/NewsCommerceService.php) | **Nghiêm trọng**: 0 sản phẩm trong DB. Bấm vào trả về 0 kết quả. |

> [!IMPORTANT]
> **Kết luận Slug Audit:** Không sửa slug DB hay migration DB ở Checkpoint này. Toàn bộ alias và gộp danh mục con sẽ được xử lý bằng Virtual Mapping trong `CatalogGroupService` ở Checkpoint tiếp theo.

---

## 5. Kiểm Tra Luồng Category Menu Hiện Tại (Current Pipeline Audit)

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
   ├─► Duplicate Query: SELECT * FROM categories WHERE status = 'active' (4th Query)
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

### 5.2 Các lỗi & Giới hạn kỹ thuật trong Luồng hiện tại

1. **Loại bỏ nhầm danh mục cha có sản phẩm ở danh mục con:**
   - Trong `CategoryMenuService.php` (dòng 36-47): Service kiểm tra `$p['category_id'] == $catId` cho danh mục cha.
   - Vì `Linh Kiện PC` (ID 4) có `hasProducts = false` (sản phẩm nằm ở ID 10-18), Service **bỏ qua hoàn toàn Linh Kiện PC (`continue`)**. `Linh Kiện PC` bị biến mất khỏi Menu Dropdown chính!
2. **Duplicate Query / N+1 Query:**
   - `Controller::render()` gọi `CategoryMenuService::getActiveMenuTree()` (3 truy vấn DB).
   - Ngay sau đó, `Controller::render()` lại thực hiện thêm 1 truy vấn dư thừa `SELECT * FROM categories` chỉ để làm dropdown trong thanh tìm kiếm header.
3. **Mức giá Hard-code không phù hợp cho phụ kiện/linh kiện:**
   - `CategoryMenuService.php` hard-code mức giá: `<15M`, `15-20M`, `20-30M`, `>30M`.
   - Màn hình (3.1M - 14.5M), Gaming Gear (550k - 5.5M), RAM (622k - 6.8M), Tản nhiệt (150k - 2.5M) nếu bấm vào bộ lọc giá >15M sẽ trả về rỗng 100%.
4. **Khác biệt giữa Static Homepage Menu và Header Dropdown:**
   - Homepage static menu (`#categoryStaticMenu`): nằm trong `.hero-section__left`, hiển thị liên tục.
   - Header dropdown menu (`#categoryMegaDropdown`): chỉ bật khi người dùng click nút "Danh mục" ở header (trên các trang ngoài trang chủ).
   - CSS hard-code kích thước mega panel static: `width: calc(100vw - 280px - 300px - 100px); min-width: 700px;`.
5. **Inline Styles trong Views:**
   - Inline style tại `category-mega-menu.php`: `style="padding: 24px;"`.
   - Inline styles tại `header.php` (logo link, badge text, admin role link).

---

## 6. Tổng Hợp Rủi Ro (Risk Matrix)

> [!WARNING]
> | Mã Rủi Ro | Loại Rủi Ro | Mức Độ | Mô Tả Chi Tiết | Phương Án Giảm Thiểu / Khắc Phục |
> | :---: | :--- | :---: | :--- | :--- |
> | **R-01** | Empty Catalog Results | **CAO** | Click `Linh Kiện PC` hoặc `Thiết Bị Mạng` trả ra 0 SP | Virtual Grouping gộp 9 child categories cho Linh Kiện PC; hiển thị empty state mượt cho TB Mạng |
> | **R-02** | Menu Exclusion | **CAO** | `Linh Kiện PC` bị ẩn hoàn toàn khỏi menu dropdown | Sửa logic `CategoryMenuService` kiểm tra cả SP trực tiếp lẫn SP thuộc child categories |
> | **R-03** | Missing Brand Filter | **TRUNG BÌNH** | 30 sản phẩm (Màn hình, Gear, TBVP) có `brand_id = NULL` nên không lọc được brand | Virtual Catalog Service ẩn cột "Thương hiệu" rỗng hoặc fallback theo tag/tên |
> | **R-04** | Hardcoded Price Filter | **TRUNG BÌNH** | Giá linh kiện/gear < 15M bị trả về 0 sản phẩm nếu chọn bộ lọc 15M+ | Cấu hình mức giá động (Dynamic Price Ranges) theo đặc thù từng nhóm virtual group |
> | **R-05** | Viewport Overflow | **CAO** | Top bar + Header + Nav quá cao gây mất Features Bar ở 1366×768 | Rút gọn chiều cao header stack & hero section theo Quyết định đã phê duyệt |

---

## 7. Đề Xuất Virtual Group Mapping (7 Nhóm Catalog Phê Duyệt)

Dựa trên dữ liệu CSDL runtime, đề xuất cấu hình mapping tại Service Layer (`CatalogGroupService` / `CategoryMenuService` mở rộng):

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                               7 VIRTUAL CATALOG GROUPS                                 │
├─────────────┬──────────────┬──────────────┬───────────┬──────────────┬─────────────────┬──────────────┤
│ 1. Laptop   │ 2. PC & Build│ 3. Linh Kiện │4. Màn Hình│5. Gaming Gear│ 6. Thiết Bị VP  │ 7. Thiết Bị  │
│             │    PC        │    PC        │           │              │                 │    Mạng      │
└─────────────┴──────────────┴──────────────┴───────────┴──────────────┴─────────────────┴──────────────┘
```

### Detail Breakdown:

#### 1. Nhóm Laptop
- **Slugs nguồn:** `laptop-gaming`, `laptop-van-phong`
- **Số SP active:** 74 sản phẩm (38 Gaming + 36 Văn phòng)
- **Thương hiệu (6):** Acer, ASUS, DELL, HP, Lenovo, MSI
- **Khoảng giá:** 19.800.000đ – 20.497.000đ
- **`component_type` khả dụng:** `NULL` (Sản phẩm nguyên chiếc)
- **Nhóm con hiển thị:** Laptop Gaming, Laptop Văn Phòng
- **Dữ liệu thiếu:** Chưa có sub-group cho Macbook / Laptop Creator
- **Aliases hỗ trợ:** `laptop`, `laptop-gaming`, `laptop-van-phong`

#### 2. Nhóm PC & Build PC
- **Slugs nguồn:** `pc-build-san`, `may-tinh-bo`
- **Số SP active:** 36 sản phẩm (36 PC Build sẵn, 0 Máy tính bộ)
- **Thương hiệu (6):** Acer, ASUS, DELL, HP, Lenovo, MSI
- **Khoảng giá:** 19.801.000đ – 20.474.000đ
- **`component_type` khả dụng:** `NULL`
- **Nhóm con hiển thị:** PC Build Sẵn, Máy Tính Bộ
- **Dữ liệu thiếu:** Danh mục `may-tinh-bo` hiện có 0 sản phẩm
- **Aliases hỗ trợ:** `pc-build-san`, `may-tinh-bo`, `pc-gaming`, `pc-van-phong`

#### 3. Nhóm Linh kiện PC
- **Slugs nguồn:** `pc-linh-kien` (ID 4) + 9 child slugs (`cpu`, `mainboard`, `ram`, `vga`, `ssd`, `hdd`, `psu`, `case`, `tan-nhiet`)
- **Số SP active:** 485 sản phẩm
- **Thương hiệu (25):** AMD, Intel, ASRock, ASUS, Colorful, GIGABYTE, MSI, Corsair, G.Skill, Kingston, TeamGroup, XPG, Acer, Zotac, Crucial, Lexar, Samsung, WD, Cooler Master, DeepCool, Lian Li, Montech, NZXT, Xigmatek, Thermalright
- **Khoảng giá:** 150.000đ – 20.000.000đ
- **`component_type` khả dụng:** `cpu`, `mainboard`, `ram`, `gpu`, `storage`, `psu`, `case`, `cpu_cooler`
- **Nhóm con hiển thị:** CPU, Mainboard, RAM, Card màn hình (VGA), Ổ cứng SSD, Ổ cứng HDD, Nguồn (PSU), Vỏ Case, Tản nhiệt
- **Dữ liệu thiếu:** Không thiếu (Dữ liệu 485 SP rất phong phú)
- **Aliases hỗ trợ:** `pc-linh-kien`, `linh-kien-pc`, `cpu`, `mainboard`, `ram`, `vga`, `ssd`, `hdd`, `psu`, `case`, `tan-nhiet`

#### 4. Nhóm Màn hình
- **Slugs nguồn:** `man-hinh` (ID 5)
- **Số SP active:** 10 sản phẩm
- **Thương hiệu:** Chưa gán (`brand_id = NULL`)
- **Khoảng giá:** 3.100.000đ – 14.500.000đ
- **`component_type` khả dụng:** `NULL`
- **Nhóm con hiển thị (Virtual Subgroups):** Màn hình Gaming, Màn hình Văn phòng, Màn hình Đồ họa
- **Dữ liệu thiếu:** `brand_id` rỗng cho cả 10 sản phẩm màn hình
- **Aliases hỗ trợ:** `man-hinh`, `monitor`

#### 5. Nhóm Gaming Gear
- **Slugs nguồn:** `gaming-gear` (ID 7)
- **Số SP active:** 10 sản phẩm
- **Thương hiệu:** Chưa gán (`brand_id = NULL`)
- **Khoảng giá:** 550.000đ – 5.500.000đ
- **`component_type` khả dụng:** `NULL`
- **Nhóm con hiển thị (Virtual Subgroups):** Bàn phím cơ, Chuột Gaming, Tai nghe Gaming, Bàn ghế Gaming
- **Dữ liệu thiếu:** `brand_id` rỗng cho cả 10 sản phẩm gear
- **Aliases hỗ trợ:** `gaming-gear`, `gear`

#### 6. Nhóm Thiết bị văn phòng
- **Slugs nguồn:** `office-gear` (ID 8)
- **Số SP active:** 5 sản phẩm
- **Thương hiệu:** Chưa gán (`brand_id = NULL`)
- **Khoảng giá:** 1.200.000đ – 9.500.000đ
- **`component_type` khả dụng:** `NULL`
- **Nhóm con hiển thị (Virtual Subgroups):** Máy in / Scanners, Máy chiếu, Phụ kiện văn phòng
- **Dữ liệu thiếu:** `brand_id` rỗng cho cả 5 sản phẩm
- **Aliases hỗ trợ:** `office-gear`, `thiet-bi-van-phong`

#### 7. Nhóm Thiết bị mạng
- **Slugs nguồn:** `networking` (ID 9)
- **Số SP active:** 0 sản phẩm (Danh mục chờ)
- **Thương hiệu:** N/A
- **Khoảng giá:** N/A
- **`component_type` khả dụng:** N/A
- **Nhóm con hiển thị (Virtual Subgroups):** Router Wi-Fi, Switch chia mạng, Bộ thu Wi-Fi
- **Dữ liệu thiếu:** Hiện chưa có sản phẩm mẫu trong CSDL
- **Aliases hỗ trợ:** `networking`, `thiet-bi-mang`

---

## 8. Danh Sách Files Dự Kiến Sửa ở Checkpoint Tiếp Theo

Ở Checkpoint 1 (Service Layer & Route Mapping) và Checkpoint 2 (UI Integration):

1. `app/services/CatalogGroupService.php` **[NEW]**: Xây dựng service quản lý 7 virtual catalog groups, alias mapping & price ranges phù hợp.
2. `app/services/CategoryMenuService.php` **[MODIFY]**: Cập nhật logic `getActiveMenuTree()` sử dụng `CatalogGroupService`, khắc phục lỗi ẩn `Linh Kiện PC`.
3. `app/core/Controller.php` **[MODIFY]**: Loại bỏ truy vấn dư thừa `SELECT * FROM categories` trong `render()`.
4. `app/controllers/HomeController.php` **[MODIFY]**: Cập nhật route search & filter hỗ trợ aliases và virtual groups.
5. `app/models/Product.php` **[MODIFY]**: Thêm phương thức hỗ trợ query theo virtual group category IDs / slugs.
6. `app/views/layouts/header.php` **[MODIFY]**: Cập nhật 7 danh mục chính trên navigation bar & search select options.
7. `app/views/layouts/partials/category-mega-menu.php` **[MODIFY]**: Đảm bảo hiển thị chuẩn 7 nhóm danh mục.
8. `public/assets/css/category-mega-menu.css` **[MODIFY]**: Tối ưu responsive & layout desktop 1366x768.

---

## 9. Những Điều Chưa Thể Xác Minh (Unverified Items)

1. **Trải nghiệm thực tế của người dùng khi lọc theo thuộc tính màn hình/gear:** Do `brand_id` của Màn hình, Gaming Gear và TB Văn phòng đang `NULL`, chưa thể xác minh hiệu quả lọc thương hiệu trừ khi gán brand hoặc lọc theo từ khóa tên sản phẩm.
2. **Kịch bản nhập sản phẩm mới trong tương lai:** Chưa thể xác minh giao diện Admin thêm sản phẩm mới có cho phép chọn Virtual Group hay bắt buộc chọn category ID gốc.

---

## 10. Khuyến Nghị Go / No-Go Cho CHECKPOINT 1

> [!TIP]
> **KHUYẾN NGHỊ: GO DÙNG PHƯƠNG ÁN HYBRID**
>
> 1. Dữ liệu runtime CSDL hiện tại có **620 sản phẩm active**, trong đó **485 sản phẩm nằm ở các danh mục linh kiện con**. Việc triển khai Virtual Grouping tại Service Layer là hoàn toàn khả thi, đúng hướng và an toàn tuyệt đối cho CSDL.
> 2. Không cần chạy bất kỳ lệnh migration hay sửa bảng `categories` / `products` nào.
> 3. Hệ thống 7 nhóm Virtual Catalog đã sẵn sàng dữ liệu để ánh xạ mượt mà ở Checkpoint 1.

---

### Xác nhận hoàn tất Checkpoint 0:
- [x] Không sửa giao diện.
- [x] Không sửa CSDL / chạy migration.
- [x] Không thay đổi product/category records.
- [x] Đã xuất báo cáo tại `docs/reviews/catalog/CATALOG_DATA_AUDIT.md`.

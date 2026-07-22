# CHECKPOINT 1 — VIRTUAL CATALOG SERVICE & CATEGORY CONTRACT REPORT (V2 FINAL)
**Dự án:** TechPilot  
**Thời điểm thực hiện:** 23/07/2026  
**Trạng thái Checkpoint:** HOÀN THÀNH — DỪNG TẠI `STOPPED_WAITING_FOR_REVIEW_GATE_VIRTUAL_CATALOG_V2`  

---

## 1. Executive Summary

Báo cáo V2 này trình bày toàn bộ kết quả nâng cấp **Service Layer & Virtual Routing Contract** cho hệ thống Catalog của TechPilot nhằm giải quyết triệt để các yêu cầu từ **REVIEW_GATE_VIRTUAL_CATALOG**:

1. **Virtual Route Resolution:** Chuẩn hóa menu group `Laptop` sử dụng virtual slug `laptop` (trả về đúng toàn bộ 74 sản phẩm Laptop: `laptop-gaming` + `laptop-van-phong`) và menu group `PC & Build PC` sử dụng virtual slug `pc` (trả về đúng 36 sản phẩm PC: `pc-build-san` + `may-tinh-bo`). Page title hiển thị chuẩn Tên Virtual Group (`Laptop`, `PC & Build PC`, `Linh kiện PC`) thay vì slug thô.
2. **Single Source of Truth cho Catalog Mapping:** Loại bỏ mảng `$aliases` lặp lại trong `Product.php`. Toàn bộ mapping category, virtual slug, source slugs và alias từ khóa tiếng Việt được quản lý tập trung 100% tại `CatalogGroupService.php` thông qua các API resolvers:
   - `CatalogGroupService::resolveGroupKey(string $slugOrAlias): ?string`
   - `CatalogGroupService::resolveSourceSlugs(string $slugOrAlias): array`
   - `CatalogGroupService::getStaticGroupDefinition(string $key): ?array`
   - `CatalogGroupService::getDisplayName(string $slugOrAlias): string`
   - `CatalogGroupService::getKeywordAliasMap(): array`
3. **Động hóa Runtime Hydration (Không phụ thuộc cứng vào Category ID):** Group definitions chỉ lưu `source_slugs` làm contract chính. Khi hydrate runtime, `source_category_ids` được resolve động từ CSDL. Chi tính các sản phẩm và category có trạng thái `status = 'active'`.
4. **Chuẩn hóa Fallback Contract:** Khi CSDL unavailable (`$db === null` hoặc ngắt kết nối), không có bất kỳ group nào có `status = 'ready'` (toàn bộ ở trạng thái `status = 'unavailable'`). `CategoryMenuService::getActiveMenuTree()` trả về mảng rỗng `[]` an toàn.
5. **Chuẩn hóa `Product::getByCategorySlug()`:** Sử dụng hai parameter riêng `:slug_direct` và `:slug_parent`, bổ sung điều kiện `p.status = 'active' AND c.status = 'active'`, và không bao giờ trả sample products khi query thất bại.
6. **Tích hợp CI Workflow:** Đã tạo file workflow GitHub Actions tại [.github/workflows/catalog-ci.yml](../../../.github/workflows/catalog-ci.yml) thực hiện `php -l` lint check và chạy suite `php tests/CatalogGroupTest.php`.

---

## 2. Approved New Output Contract (Cấu trúc Output Contract Chuẩn)

Mỗi Virtual Catalog Group xuất ra contract chuẩn dạng mảng như sau:

```php
[
    'key'                     => 'laptop',
    'name'                    => 'Laptop',
    'canonical_slug'           => 'laptop',
    'virtual_slug'            => 'laptop',
    'icon'                    => 'fa-solid fa-laptop',
    'source_category_ids'     => [1, 2], // Resolved dynamically at runtime
    'source_slugs'            => ['laptop-gaming', 'laptop-van-phong'],
    'aliases'                 => ['laptop', 'laptop-gaming', 'laptop-van-phong', 'may-tinh-xach-tay', 'lap'],
    'status'                  => 'ready', // 'ready', 'not_ready' hoặc 'unavailable'
    'product_count'           => 74,     // Computed live from active DB
    'subgroups'               => [
        [
            'id'            => 1,
            'name'          => 'Laptop Gaming',
            'slug'          => 'laptop-gaming',
            'product_count' => 38,
        ],
        [
            'id'            => 2,
            'name'          => 'Laptop Văn Phòng',
            'slug'          => 'laptop-van-phong',
            'product_count' => 36,
        ]
    ],
    'brands'                  => [
        ['name' => 'Acer', 'slug' => 'acer', 'query' => 'brand=acer'],
        ['name' => 'ASUS', 'slug' => 'asus', 'query' => 'brand=asus'],
        ['name' => 'DELL', 'slug' => 'dell', 'query' => 'brand=dell'],
        ['name' => 'HP', 'slug' => 'hp', 'query' => 'brand=hp'],
        ['name' => 'Lenovo', 'slug' => 'lenovo', 'query' => 'brand=lenovo'],
        ['name' => 'MSI', 'slug' => 'msi', 'query' => 'brand=msi'],
    ],
    'price_ranges'            => [
        ['name' => 'Dưới 15 triệu', 'query' => 'min_price=0&max_price=15000000'],
        ['name' => 'Từ 15 - 20 triệu', 'query' => 'min_price=15000000&max_price=20000000'],
        ['name' => 'Từ 20 - 30 triệu', 'query' => 'min_price=20000000&max_price=30000000'],
        ['name' => 'Trên 30 triệu', 'query' => 'min_price=30000000'],
    ],
    'min_effective_price'    => 19800000,
    'median_effective_price' => 20161000,
    'max_effective_price'    => 20497000,
]
```

### Contract khi Database Unavailable:
```php
[
    'key'                     => 'laptop',
    'name'                    => 'Laptop',
    'canonical_slug'           => 'laptop',
    'virtual_slug'            => 'laptop',
    'icon'                    => 'fa-solid fa-laptop',
    'source_category_ids'     => [],
    'source_slugs'            => ['laptop-gaming', 'laptop-van-phong'],
    'aliases'                 => ['laptop', 'laptop-gaming', 'laptop-van-phong', 'may-tinh-xach-tay', 'lap'],
    'status'                  => 'unavailable', // Không bao giờ có status = 'ready'
    'product_count'           => 0,
    'subgroups'               => [],
    'brands'                  => [],
    'price_ranges'            => [],
    'min_effective_price'    => null,
    'median_effective_price' => null,
    'max_effective_price'    => null,
]
```

---

## 3. Files Changed (Danh sách file đã thay đổi)

1. [CatalogGroupService.php](../../../app/services/CatalogGroupService.php) **[MODIFY]**: Cung cấp Single Source of Truth cho catalog mapping, resolvers API, dynamic category ID resolution, connection provider seam cho testing và safe fallback contract.
2. [CategoryMenuService.php](../../../app/services/CategoryMenuService.php) **[MODIFY]**: Xuất mảng menu item với virtual slug chuẩn (`laptop`, `pc`, `pc-linh-kien`), trả về mảng rỗng an toàn khi DB ngắt kết nối.
3. [Product.php](../../../app/models/Product.php) **[MODIFY]**: Bỏ mảng `$aliases` trùng lặp, dùng `CatalogGroupService::getKeywordAliasMap()` & `resolveSourceSlugs()`, cập nhật `getByCategorySlug()` với `:slug_direct` & `:slug_parent` cùng điều kiện active status.
4. [HomeController.php](../../../app/controllers/HomeController.php) **[MODIFY]**: Cập nhật action `search()` sử dụng `CatalogGroupService::getDisplayName($categorySlug)` cho virtual page titles.
5. [CatalogGroupTest.php](../../../tests/CatalogGroupTest.php) **[MODIFY]**: Bổ sung 13 integration test cases V2 (Virtual URL resolution, DB offline simulation seam, page title, product status checks).
6. [.github/workflows/catalog-ci.yml](../../../.github/workflows/catalog-ci.yml) **[NEW]**: File cấu hình GitHub Actions CI chạy `php -l` và `php tests/CatalogGroupTest.php`.
7. [CHECKPOINT_1_VIRTUAL_CATALOG.md](CHECKPOINT_1_VIRTUAL_CATALOG.md) **[MODIFY]**: Báo cáo tổng kết Checkpoint 1 V2 Final.

---

## 4. Bảng Mapping Thực Tế và Runtime Product Counts

| STT | Virtual Group Name | Virtual Slug | Source Category Slugs | Active Products (Runtime) | Status | Menu Link Output |
| :---: | :--- | :--- | :--- | :---: | :---: | :--- |
| 1 | **Laptop** | `laptop` | `laptop-gaming`, `laptop-van-phong` | **74** | `ready` | `home/search?cat=laptop` |
| 2 | **PC & Build PC** | `pc` | `pc-build-san`, `may-tinh-bo` | **36** | `ready` | `home/search?cat=pc` |
| 3 | **Linh kiện PC** | `pc-linh-kien` | `pc-linh-kien`, `cpu`, `mainboard`, `ram`, `vga`, `ssd`, `hdd`, `psu`, `case`, `tan-nhiet` | **485** | `ready` | `home/search?cat=pc-linh-kien` |
| 4 | **Màn hình** | `man-hinh` | `man-hinh` | **10** | `ready` | `home/search?cat=man-hinh` |
| 5 | **Gaming Gear** | `gaming-gear` | `gaming-gear` | **10** | `ready` | `home/search?cat=gaming-gear` |
| 6 | **Thiết bị văn phòng** | `office-gear` | `office-gear` | **5** | `ready` | `home/search?cat=office-gear` |
| 7 | **Thiết bị mạng** | `networking` | `networking` | **0** | `not_ready` | *(Bị ẩn hoàn toàn trên menu)* |

---

## 5. Bằng Chứng Thực Thi Local Test Suite V2 (Local Execution Evidence)

### Command chạy local test suite V2:
```bash
php tests/CatalogGroupTest.php
```

### Raw Test Execution Log Output:
```text
==================================================
RUNNING CHECKPOINT 1 V2 — CATALOG GROUP INTEGRATION TESTS
==================================================

1. countSearch('', 'laptop') == 74                                [PASS]
2. search('', 'laptop', 24) returns 24 products on page 1         [PASS]
3. Products returned for 'laptop' belong ONLY to laptop-gaming or laptop-van-phong [PASS]
4. countSearch('', 'pc') == 36                                    [PASS]
5. Storefront Menu item for Laptop has slug 'laptop' (NOT laptop-gaming) [PASS]
6. Storefront Menu item for PC has slug 'pc' (NOT pc-build-san)   [PASS]
7. home/search?cat=laptop request context has totalResults = 74   [PASS]
8. Virtual page titles resolve correctly ('Laptop', 'PC & Build PC', 'Linh kiện PC') [PASS]
9. getByCategorySlug returns ONLY active status products & categories [PASS]
10. No duplicate category mapping array in Product.php            [PASS]
11. DB unavailable simulated via connection provider SEAM returns empty menu tree [PASS]
12. Fallback groups with count 0 MUST NOT have status 'ready'     [PASS]
13. Zero DB mutations during and after tests                      [PASS]

--------------------------------------------------
ALL 13 INTEGRATION TESTS PASSED SUCCESSFULLY!
--------------------------------------------------
```

### Command kiểm tra cú pháp PHP (Lint Audit):
```bash
php -l app/services/CatalogGroupService.php
php -l app/services/CategoryMenuService.php
php -l app/models/Product.php
php -l app/controllers/HomeController.php
php -l tests/CatalogGroupTest.php
```

### Raw PHP Lint Output:
```text
No syntax errors detected in app/services/CatalogGroupService.php
No syntax errors detected in app/services/CategoryMenuService.php
No syntax errors detected in app/models/Product.php
No syntax errors detected in app/controllers/HomeController.php
No syntax errors detected in tests/CatalogGroupTest.php
```

---

## 6. Cấu Hình CI Workflow (GitHub Actions)

File workflow [.github/workflows/catalog-ci.yml](../../../.github/workflows/catalog-ci.yml) đã được cấu hình với các bước:
1. `Checkout Repository`
2. `Setup PHP 8.3` (với extensions pdo, pdo_mysql)
3. `PHP Lint Check` (chạy `php -l` cho toàn bộ các tệp PHP thay đổi)
4. `Import Database Schema & Seed Data` (khởi tạo MySQL 8 service container)
5. `Run Catalog Group Integration Tests` (chạy `php tests/CatalogGroupTest.php`)

---

## 7. Đánh Giá Rủi Ro Còn Lại Cho Checkpoint Tiếp Theo (UI Integration)

| Mã Rủi Ro | Nội dung rủi ro | Mức độ | Phương án kiểm soát ở Checkpoint 2 |
| :---: | :--- | :---: | :--- |
| **R-UI-01** | Viewport Vertical Overflow | **CAO** | Tối ưu kích thước padding & height của Navigation/Header stack để vừa vặn trong chiều cao 768px màn hình 1366x768. |
| **R-UI-02** | Search Form Select Sync | **TRUNG BÌNH** | Đồng bộ các thẻ `<option>` trong dropdown danh mục của Header Search Bar theo 7 Virtual Groups. |
| **R-UI-03** | Subgroup Mega Panel Layout | **TRUNG BÌNH** | Đảm bảo Mega Menu Panel của nhóm Linh kiện PC (9 subgroups) cân đối và dễ nhìn trên màn hình desktop. |

---

### Xác nhận hoàn tất Checkpoint 1 V2:
- [x] Không sửa giao diện, CSS, hero, features bar hoặc CSDL.
- [x] Menu group Laptop dùng virtual slug `laptop`, PC dùng `pc`.
- [x] Single source of truth tại `CatalogGroupService.php`.
- [x] Dynamic category ID resolution từ `source_slugs`.
- [x] Fallback contract an toàn khi DB ngắt kết nối (`status = 'unavailable'`).
- [x] `getByCategorySlug()` chỉ trả sản phẩm active thuộc category active.
- [x] 13/13 test cases V2 pass trên local runtime.
- [x] Đã tạo GitHub Actions CI workflow tại `.github/workflows/catalog-ci.yml`.

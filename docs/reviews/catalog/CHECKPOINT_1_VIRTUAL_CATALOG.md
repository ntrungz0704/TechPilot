# CHECKPOINT 1 — VIRTUAL CATALOG SERVICE & CATEGORY CONTRACT REPORT
**Dự án:** TechPilot  
**Thời điểm thực hiện:** 23/07/2026  
**Trạng thái Checkpoint:** HOÀN THÀNH — CHO PHÉP XEM XÉT REVIEW GATE VIRTUAL CATALOG  

---

## 1. Executive Summary

Checkpoint 1 đã xây dựng thành công **Service Layer thống nhất dữ liệu Catalog** thành **7 Virtual Catalog Groups** mà không thực hiện bất kỳ migration CSDL nào, không thay đổi `category_id` sản phẩm, không sửa giao diện storefront và giữ an toàn 100% cho CSDL hiện tại.

### Các kết quả nổi bật:
- Thêm mới `CatalogGroupService.php` làm trung tâm quản lý 7 virtual groups, tính toán số lượng sản phẩm, thương hiệu, khoảng giá động và median effective price từ runtime CSDL.
- Refactor `CategoryMenuService.php` kết nối với `CatalogGroupService`. Khắc phục hoàn toàn lỗi ẩn danh mục `Linh Kiện PC` (danh mục có 0 sản phẩm trực tiếp nhưng có 485 sản phẩm ở các danh mục con).
- Chuẩn hóa contract trong `Product.php`: sửa lỗi alias tìm kiếm tiếng Việt `"linh kiện"` map về `pc-linh-kien` (trả về đúng 485 sản phẩm) và khắc phục lỗi trùng named placeholder `:slug` trong `getByCategorySlug()`.
- Xây dựng bộ test tự động [CatalogGroupTest.php](../../../tests/CatalogGroupTest.php) kiểm thử 13 kịch bản tích hợp CSDL thật — **100% PASS**.

---

## 2. Files Changed (Danh sách các file thay đổi)

1. [CatalogGroupService.php](../../../app/services/CatalogGroupService.php) **[NEW]**: Service layer mới định nghĩa 7 virtual groups, hydrate dữ liệu CSDL runtime, loại bỏ brand trùng, lọc subgroup count > 0 và hỗ trợ tra cứu slug/alias.
2. [CategoryMenuService.php](../../../app/services/CategoryMenuService.php) **[MODIFY]**: Refactor sử dụng `CatalogGroupService`, ẩn danh mục rỗng (`Thiết bị mạng`), hiển thị `Linh Kiện PC`.
3. [Product.php](../../../app/models/Product.php) **[MODIFY]**: Sửa alias `"linh kiện"` -> `pc-linh-kien`, thêm alias tiếng Việt cho `office-gear` và `networking`, sửa lỗi trùng named parameter trong `getByCategorySlug()`.
4. [CatalogGroupTest.php](../../../tests/CatalogGroupTest.php) **[NEW]**: Bộ test tự động kiểm thử 13 kịch bản tích hợp dữ liệu CSDL thật.
5. [CHECKPOINT_1_VIRTUAL_CATALOG.md](CHECKPOINT_1_VIRTUAL_CATALOG.md) **[NEW]**: Báo cáo tổng kết Checkpoint 1.

---

## 3. Output Contract Structure (Cấu trúc Output Contract Ổn định)

Mỗi Virtual Catalog Group xuất ra contract chuẩn dạng mảng như sau:

```php
[
    'key'                     => 'laptop',
    'name'                    => 'Laptop',
    'canonical_slug'           => 'laptop-gaming',
    'virtual_slug'            => 'laptop',
    'icon'                    => 'fa-solid fa-laptop',
    'source_category_ids'     => [1, 2],
    'source_slugs'            => ['laptop-gaming', 'laptop-van-phong'],
    'aliases'                 => ['laptop', 'laptop-gaming', 'laptop-van-phong', 'may-tinh-xach-tay', 'lap'],
    'status'                  => 'ready', // 'ready' hoặc 'not_ready' (nếu count == 0)
    'product_count'           => 74,     // Tính toán runtime từ CSDL
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

---

## 4. Bảng Mapping Thực Tế và Runtime Product Counts

| STT | Virtual Group Name | Key / Canonical Slug | Source Category IDs | Active Products (Runtime) | Status | Subgroups Rendered |
| :---: | :--- | :--- | :---: | :---: | :---: | :--- |
| 1 | **Laptop** | `laptop-gaming` / `laptop` | `[1, 2]` | **74** | `ready` | Laptop Gaming (38 SP), Laptop Văn Phòng (36 SP) |
| 2 | **PC & Build PC** | `pc-build-san` / `pc` | `[3, 6]` | **36** | `ready` | PC Build Sẵn (36 SP) *(bỏ qua Máy tính bộ 0 SP)* |
| 3 | **Linh kiện PC** | `pc-linh-kien` | `[4, 10..18]` | **485** | `ready` | CPU (40 SP), Mainboard (60 SP), RAM (80 SP), VGA (80 SP), SSD (60 SP), HDD (20 SP), PSU (50 SP), Case (50 SP), Tản nhiệt (45 SP) |
| 4 | **Màn hình** | `man-hinh` | `[5]` | **10** | `ready` | Màn hình (10 SP) |
| 5 | **Gaming Gear** | `gaming-gear` | `[7]` | **10** | `ready` | Gaming Gear (10 SP) |
| 6 | **Thiết bị văn phòng** | `office-gear` | `[8]` | **5** | `ready` | Thiết bị văn phòng (5 SP) |
| 7 | **Thiết bị mạng** | `networking` | `[9]` | **0** | `not_ready` | *(Bị ẩn hoàn toàn trên storefront, count = 0)* |
| **TỔNG** | | | | **620** | | |

---

## 5. Kết Quả Kiểm Thử Tích Hợp (Test Output)

### Command chạy test:
```bash
php tests/CatalogGroupTest.php
```

### Raw Test Output Execution Log:
```text
==================================================
RUNNING CHECKPOINT 1 — CATALOG GROUP INTEGRATION TESTS
==================================================

1. Laptop active runtime count == 74                         [PASS]
2. PC & Build PC active runtime count == 36                  [PASS]
3. Linh kiện PC active runtime count == 485                [PASS]
4. Màn hình active runtime count == 10                     [PASS]
5. Gaming Gear active runtime count == 10                    [PASS]
6. Thiết bị văn phòng active runtime count == 5        [PASS]
7. Thiết bị mạng has 0 products and is NOT_READY/HIDDEN [PASS]
8. Parent 'pc-linh-kien' with 0 direct products IS displayed via child products [PASS]
9. No duplicate brands within any virtual group              [PASS]
10. No subgroup rendered with count == 0                     [PASS]
11. Keyword 'linh kiện' resolves to pc-linh-kien (485 items) [PASS]
12. Safe fallback when DB unavailable returns 7 defined fallback groups [PASS]
13. Zero DB mutations during and after tests                 [PASS]

--------------------------------------------------
ALL 13 INTEGRATION TESTS PASSED SUCCESSFULLY!
--------------------------------------------------
```

---

## 6. Những Nội Dung Chưa Triển Khai (Deferred Scope)

Theo đúng quy định Checkpoint 1, các hạng mục sau chưa được thực hiện và được hoãn lại cho Checkpoint 2 (UI Integration & Layout Hardening):
1. **Chưa chỉnh sửa HTML/CSS Header:** Chiều cao header stack, navigation links trên storefront header views.
2. **Chưa điều chỉnh Hero Section:** Kích thước hero category sidebar và carousel trên trang chủ.
3. **Chưa thay đổi JavaScript mega menu:** Xử lý hover/dropdown mega panel.

---

## 7. Đánh Giá Rủi Ro Cho Checkpoint Tiếp Theo (UI Integration Risk)

| Mã Rủi Ro | Nội dung rủi ro | Mức độ | Phương án kiểm soát ở Checkpoint 2 |
| :---: | :--- | :---: | :--- |
| **R-UI-01** | Viewport Vertical Overflow | **CAO** | Cần giảm padding và font-size của topbar/header/nav để cả 5 thành phần (Topbar, Main Header, Navigation, Hero, Features Bar) nằm trọn trong vạch 768px chiều cao màn hình 1366x768. |
| **R-UI-02** | Search Form Select Sync | **TRUNG BÌNH** | Cập nhật ô `<select name="cat">` trong header search bar hiển thị chuẩn 7 nhóm Virtual Catalog thay vì 18 category gốc. |
| **R-UI-03** | Subgroup Hover Overflow | **TRUNG BÌNH** | Đảm bảo Mega Menu Panel của nhóm `Linh kiện PC` (có 9 subgroups) hiển thị cân đối và không bị tràn khỏi màn hình. |

---

### Xác nhận hoàn tất Checkpoint 1:
- [x] Không migration CSDL.
- [x] Không thay đổi `category_id` sản phẩm.
- [x] Không sửa giao diện lớn.
- [x] Đã tạo `CatalogGroupService.php` & refactor `CategoryMenuService.php`.
- [x] Đã vượt qua 100% test tích hợp tự động (`CatalogGroupTest.php`).
- [x] Đã tạo báo cáo tại [CHECKPOINT_1_VIRTUAL_CATALOG.md](CHECKPOINT_1_VIRTUAL_CATALOG.md).

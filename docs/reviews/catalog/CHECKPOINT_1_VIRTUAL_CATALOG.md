# CHECKPOINT 1 — VIRTUAL CATALOG SERVICE & CATEGORY CONTRACT REPORT (V3)
**Dự án:** TechPilot  
**Thời điểm thực hiện:** 23/07/2026  
**Trạng thái Checkpoint:** ĐÃ HOÀN THÀNH CODE & KỊCH BẢN TEST — DỪNG TẠI `STOPPED_WAITING_FOR_REVIEW_GATE_VIRTUAL_CATALOG_V3`  

---

## 1. Executive Summary

Báo cáo V3 này trình bày toàn bộ kết quả tái cấu trúc **Service Layer, Route Resolution & Keyword Contract** cho hệ thống Catalog của TechPilot nhằm giải quyết triệt để các yêu cầu từ **REVIEW_GATE_VIRTUAL_CATALOG_V2**:

1. **Phân tách Virtual Group Route và Exact Source Category Route:**
   - **Virtual Group Route:** Khi URL chứa virtual slug (`laptop`, `pc`, `pc-linh-kien`), resolver sẽ mở rộng thành toàn bộ source slugs thuộc nhóm đó (ví dụ: `cat=laptop` -> 74 sản phẩm, `cat=pc` -> 36 sản phẩm, `cat=pc-linh-kien` -> 485 sản phẩm).
   - **Exact Source Category Route:** Khi URL chứa slug danh mục cụ thể (`laptop-gaming`, `laptop-van-phong`, `pc-build-san`, `cpu`, `ram`, `vga`, v.v.), resolver **chỉ trả về đúng danh mục đó** mà không mở rộng sang cả nhóm (ví dụ: `cat=laptop-gaming` -> đúng 38 sản phẩm, `cat=cpu` -> đúng 40 sản phẩm, `cat=ram` -> đúng 80 sản phẩm).
2. **Thiết kế Keyword Alias có Target Riêng:**
   - Quản lý tập trung 100% tại `CatalogGroupService.php::$keywordAliasMap`. Không tự động map mọi từ khóa sang toàn bộ group.
   - Từ khóa `"laptop"` -> `['laptop-gaming', 'laptop-van-phong']` (74 SP). Từ khóa `"laptop gaming"` -> `['laptop-gaming']` (38 SP). Từ khóa `"cpu"` -> `['cpu']` (40 SP). Từ khóa `"card màn hình"` -> `['vga']` (80 SP).
3. **Độ phân giải Tên hiển thị (Display Name Resolution):**
   - Virtual root slug (`laptop`, `pc`, `pc-linh-kien`) -> Tên Virtual Group (`Laptop`, `PC & Build PC`, `Linh kiện PC`).
   - Exact source category slug (`laptop-gaming`, `cpu`, `ram`) -> Tên Category chuẩn (`Laptop Gaming`, `CPU`, `RAM`). Tiêu đề trang CPU giữ nguyên là `"CPU"`, không bị biến thành `"Linh kiện PC"`.
4. **Bảo tồn các Section Trang chủ (Homepage Categories Isolation):**
   - `getByCategorySlug('laptop-gaming', 100)` chỉ trả sản phẩm `laptop-gaming` (38 SP).
   - `getByCategorySlug('laptop-van-phong', 100)` chỉ trả sản phẩm `laptop-van-phong` (36 SP).
   - Hai danh mục trên trang chủ hoàn toàn độc lập và không bị lấn sang nhau.
5. **Contract Category Active & SQL Preparation:**
   - Thêm điều kiện `c.status = 'active'` vào `Product::search()` và `Product::countSearch()`.
   - `config/database.php` đọc biến môi trường (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`) an toàn.
6. **Tái tạo CI Workflow với SQL Fixture:**
   - Tạo tệp SQL fixture tái tạo dữ liệu [tests/fixtures/catalog_ci.sql](../../../tests/fixtures/catalog_ci.sql) (237 KB) chứa đủ 18 categories và 620 sản phẩm.
   - Cập nhật [.github/workflows/catalog-ci.yml](../../../.github/workflows/catalog-ci.yml) tự động nạp fixture này trên môi trường MySQL 8 của GitHub Actions.

---

## 2. Ma Trận Route Resolution & Count Results

| Input Slug / Alias (`cat=`) | Loại Route | Source Slugs Được Resolve | Product Count | Page Title Output | Storefront Menu Output |
| :--- | :--- | :--- | :---: | :--- | :--- |
| `laptop` | Virtual Group | `laptop-gaming`, `laptop-van-phong` | **74** | **Laptop** | Top-level Menu Link (`home/search?cat=laptop`) |
| `laptop-gaming` | Exact Category | `laptop-gaming` | **38** | **Laptop Gaming** | Subgroup Menu Link (`home/search?cat=laptop-gaming`) |
| `laptop-van-phong` | Exact Category | `laptop-van-phong` | **36** | **Laptop Văn Phòng** | Subgroup Menu Link (`home/search?cat=laptop-van-phong`) |
| `pc` | Virtual Group | `pc-build-san`, `may-tinh-bo` | **36** | **PC & Build PC** | Top-level Menu Link (`home/search?cat=pc`) |
| `pc-build-san` | Exact Category | `pc-build-san` | **36** | **PC Build Sẵn** | Subgroup Menu Link (`home/search?cat=pc-build-san`) |
| `pc-linh-kien` | Virtual Group / Parent | `pc-linh-kien`, `cpu`, `mainboard`, `ram`, `vga`, `ssd`, `hdd`, `psu`, `case`, `tan-nhiet` | **485** | **Linh kiện PC** | Top-level Menu Link (`home/search?cat=pc-linh-kien`) |
| `cpu` | Exact Category | `cpu` | **40** | **CPU** | Subgroup Menu Link (`home/search?cat=cpu`) |
| `ram` | Exact Category | `ram` | **80** | **RAM** | Subgroup Menu Link (`home/search?cat=ram`) |
| `vga` | Exact Category | `vga` | **80** | **VGA** | Subgroup Menu Link (`home/search?cat=vga`) |

---

## 3. Targeted Keyword Alias Mapping (Chi tiết từ khóa tìm kiếm)

Toàn bộ mapping được định nghĩa tập trung tại `CatalogGroupService.php::$keywordAliasMap`:

```php
'keyword_aliases' => [
    'laptop'            => ['laptop-gaming', 'laptop-van-phong'],
    'laptop gaming'     => ['laptop-gaming'],
    'laptop văn phòng'  => ['laptop-van-phong'],

    'pc'                => ['pc-build-san', 'may-tinh-bo'],
    'pc build sẵn'      => ['pc-build-san'],

    'linh kiện'         => ['pc-linh-kien', 'cpu', 'mainboard', 'ram', 'vga', 'ssd', 'hdd', 'psu', 'case', 'tan-nhiet'],
    'cpu'               => ['cpu'],
    'mainboard'         => ['mainboard'],
    'ram'               => ['ram'],
    'vga'               => ['vga'],
    'card màn hình'     => ['vga'],
    'ssd'               => ['ssd'],
    'hdd'               => ['hdd'],
    'nguồn'             => ['psu'],
    'case'              => ['case'],
    'tản nhiệt'         => ['tan-nhiet'],
]
```

---

## 4. Kết Quả Kiểm Thử Local Integration Suite V3 (21/21 PASS)

### Command thực thi local test suite V3:
```bash
php tests/CatalogGroupTest.php
```

### Raw Test Output Execution Log:
```text
==================================================
RUNNING CHECKPOINT 1 V3 — CATALOG ROUTING & CONTRACT INTEGRATION TESTS
==================================================

1. countSearch('', 'laptop') == 74                                   [PASS]
2. countSearch('', 'laptop-gaming') == 38                            [PASS]
3. countSearch('', 'laptop-van-phong') == 36                         [PASS]
4. countSearch('', 'pc') == 36                                       [PASS]
5. countSearch('', 'pc-build-san') == 36                             [PASS]
6. countSearch('', 'cpu') == 40                                      [PASS]
7. countSearch('', 'ram') == 80                                      [PASS]
8. countSearch('', 'vga') == 80                                      [PASS]
9. Keyword 'laptop' count == 74                                      [PASS]
10. Keyword 'laptop gaming' count == 38                              [PASS]
11. Keyword 'laptop văn phòng' count == 36                         [PASS]
12. Keyword 'linh kiện' count == 485                               [PASS]
13. Keyword 'cpu' count == 40                                        [PASS]
14. Keyword 'card màn hình' count == 80                            [PASS]
15. Menu subgroup CPU link is cat=cpu & cat=cpu does NOT expand to 485 [PASS]
16. Homepage sections: Laptop Gaming & Laptop VP are strictly isolated [PASS]
17. Page titles: Virtual root ('Laptop') vs Exact source ('CPU') resolve correctly [PASS]
18. Inactive category / empty category filtering works correctly in search [PASS]
19. HomeController::search() route execution renders Virtual Group page title [PASS]
20. DB unavailable SEAM returns empty storefront tree & unavailable status [PASS]
21. Zero DB mutations during and after tests                         [PASS]

--------------------------------------------------
ALL 21 INTEGRATION TESTS PASSED SUCCESSFULLY!
--------------------------------------------------
```

---

## 5. Danh Sách Files Thay Đổi Trong Checkpoint 1 V3

1. [CatalogGroupService.php](../../../app/services/CatalogGroupService.php) **[MODIFY]**: Phân tách virtual group route & exact source route, định nghĩa targeted keyword aliases, hỗ trợ display name động cho CSDL categories.
2. [Product.php](../../../app/models/Product.php) **[MODIFY]**: Bổ sung `c.status = 'active'`, tích hợp Single Source of Truth keyword alias map.
3. [config/database.php](../../../config/database.php) **[MODIFY]**: Thêm khả năng đọc môi trường `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` cho CI.
4. [CatalogGroupTest.php](../../../tests/CatalogGroupTest.php) **[MODIFY]**: Cập nhật 21 kịch bản kiểm thử (route resolution, homepage isolation, page title, controller execution).
5. [tests/fixtures/catalog_ci.sql](../../../tests/fixtures/catalog_ci.sql) **[NEW]**: SQL Fixture tái tạo 620 sản phẩm và 18 categories cho CI.
6. [.github/workflows/catalog-ci.yml](../../../.github/workflows/catalog-ci.yml) **[MODIFY]**: Tự động import `catalog_ci.sql` fixture trước khi chạy integration test.
7. [CHECKPOINT_1_VIRTUAL_CATALOG.md](CHECKPOINT_1_VIRTUAL_CATALOG.md) **[MODIFY]**: Báo cáo tổng kết V3.

---

### Trạng thái sẵn sàng review:
- [x] Không sửa CSS, hero, header layout hoặc database production.
- [x] Phân tách rõ ràng Virtual Group Route (74 Laptop) và Exact Category Route (38 Laptop Gaming, 40 CPU).
- [x] Keyword alias mapping có target riêng biệt trong `CatalogGroupService.php`.
- [x] Display Name giữ tên category thô cho exact source route và tên Virtual Group cho virtual root.
- [x] 21/21 integration tests pass local.
- [x] Fixture `catalog_ci.sql` & CI Workflow đã sẵn sàng.

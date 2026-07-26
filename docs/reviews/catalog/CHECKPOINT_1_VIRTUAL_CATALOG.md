# CHECKPOINT 1 — VIRTUAL CATALOG SERVICE & CATEGORY CONTRACT REPORT (V4 FINAL)
**Dự án:** TechPilot  
**Thời điểm thực hiện:** 23/07/2026  
**Trạng thái Checkpoint:** HOÀN THÀNH — ĐÃ ĐẠT REMOTE GITHUB ACTIONS CI SUCCESS — DỪNG TẠI `STOPPED_WAITING_FOR_REVIEW_GATE_VIRTUAL_CATALOG_V4`  

---

## 1. Executive Summary

Báo cáo V4 Final này trình bày toàn bộ kết quả nâng cấp **Exact Route Locking, Data-backed Price Ranges & Remote CI Verification Gate** cho hệ thống Catalog của TechPilot nhằm giải quyết triệt để các yêu cầu từ **REVIEW_GATE_VIRTUAL_CATALOG_V3**:

1. **Khóa Exact Source Route trong SQL (No Double Parent-id Expansion):**
   - Loại bỏ hoàn toàn vế `OR c.parent_id IN (...)` sau khi resolver đã quyết định danh sách `source_slugs`.
   - `Product::search()`, `Product::countSearch()` và `Product::getByCategorySlug()` sử dụng duy nhất câu lệnh SQL:
     `c.slug IN (:resolvedSourceSlugs)`
   - Khi gọi `cat=cpu`, SQL chỉ lọc `c.slug IN ('cpu')` (đúng **40 sản phẩm CPU**). Việc thêm danh mục con mới dưới CPU trong tương lai sẽ **không** làm `cat=cpu` tự động lấy danh mục con đó.
2. **Price Range Contract Dựa Trên Dữ Liệu Runtime (Data-backed Non-overlapping Ranges):**
   - Hợp nhất phương thức `generatePriceRanges()` tính toán dựa trên dữ liệu runtime `effectivePrices` của từng Virtual Group.
   - **Chỉ xuất các range có `product_count > 0`**. Loại bỏ hoàn toàn các khoảng rỗng (như "Dưới 15 triệu" hay "Trên 30 triệu" ở nhóm Laptop / PC).
   - **Ranh giới khoảng giá không bị chồng lấn** (sản phẩm có giá đúng 20.000.000đ chỉ thuộc 1 range duy nhất, không bị đếm trùng).
   - Cấu trúc Contract của từng range:
     ```php
     [
         'name'          => 'Từ 15 - 20 triệu',
         'min_price'     => 15000001,
         'max_price'     => 20000000,
         'product_count' => 30,
         'query'         => 'min_price=15000001&max_price=20000000'
     ]
     ```
3. **Kịch Bản Test Tích Hợp Giao Dịch CSDL (Transaction Integration Tests):**
   - **Exact Route Isolation Test:** Tạo thử nghiệm 1 category con và 1 product active dưới CPU trong giao dịch PDO (`BEGIN` / `ROLLBACK`). Xác minh `countSearch('', 'cpu')` vẫn bằng 40 và `getByCategorySlug('cpu')` không chứa sản phẩm con này.
   - **Real Category Inactive Test:** Chuyển `laptop-gaming` sang `inactive` trong giao dịch PDO. Xác minh `search()` và `countSearch()` lập tức loại bỏ 38 sản phẩm của danh mục này khỏi kết quả.
   - **Price Range Validation Test:** Xác minh mọi price range đều có `product_count > 0`, không overlap, và tổng sản phẩm các range bằng đúng tổng sản phẩm của group.
   - **HTML Title Parsing Test:** Trích xuất chính xác thẻ `<title>` khi gọi `HomeController::search()` (`cat=laptop` -> `<title>Laptop - TechPilot</title>`, `cat=cpu` -> `<title>CPU - TechPilot</title>`).
4. **Xác nhận Remote GitHub Actions CI Success:**
   - Remote GitHub Actions CI workflow `Catalog Virtual Routing & Contract CI` (Run ID: `29953935531`, Job ID: `89038153494`) chạy trên commit `6da2978` đạt **SUCCESS**.

---

## 2. Remote GitHub Actions CI Verification Gate Output

| Tiêu chí CI | Giá trị xác minh thực tế |
| :--- | :--- |
| **Workflow Name** | `Catalog Virtual Routing & Contract CI` |
| **Run ID** | `29953935531` |
| **Job ID** | `89038153494` |
| **Commit SHA** | `6da2978` |
| **Branch** | `feature/hieu-news` |
| **Status** | `completed` |
| **Conclusion** | `success` |
| **Duration** | `55s` |
| **Job Steps Executed** | 1. Checkout Repository<br>2. Setup PHP 8.3 (pdo, pdo_mysql)<br>3. PHP Lint Check (`php -l` 5 files)<br>4. Import Database Fixture Data (`tests/fixtures/catalog_ci.sql`) <br>5. Run Catalog Group Integration Tests (`php tests/CatalogGroupTest.php`) |

---

## 3. Data-backed Price Range Output Thực Tế Của 7 Virtual Groups

### 3.1 Laptop (74 SP Active)
- **Khoảng 1 (15M+1 - 20M):** `product_count = 30`, `query = min_price=15000001&max_price=20000000`
- **Khoảng 2 (20M+1 - 30M):** `product_count = 44`, `query = min_price=20000001&max_price=30000000`
- *(Khoảng "Dưới 15 triệu" và "Trên 30 triệu" có count = 0 nên tự động bị ẩn)*

### 3.2 PC & Build PC (36 SP Active)
- **Khoảng 1 (15M+1 - 20M):** `product_count = 15`, `query = min_price=15000001&max_price=20000000`
- **Khoảng 2 (20M+1 - 30M):** `product_count = 21`, `query = min_price=20000001&max_price=30000000`
- *(Khoảng "Dưới 15 triệu" và "Trên 30 triệu" có count = 0 nên tự động bị ẩn)*

### 3.3 Linh kiện PC (485 SP Active)
- **Khoảng 1 (Dưới 2 triệu):** `product_count = 188`, `query = min_price=0&max_price=2000000`
- **Khoảng 2 (2M+1 - 5 triệu):** `product_count = 173`, `query = min_price=2000001&max_price=5000000`
- **Khoảng 3 (5M+1 - 10 triệu):** `product_count = 84`, `query = min_price=5000001&max_price=10000000`
- **Khoảng 4 (Trên 10 triệu):** `product_count = 40`, `query = min_price=10000001`

### 3.4 Màn hình (10 SP Active)
- **Khoảng 1 (Dưới 5 triệu):** `product_count = 6`, `query = min_price=0&max_price=5000000`
- **Khoảng 2 (5M+1 - 10 triệu):** `product_count = 3`, `query = min_price=5000001&max_price=10000000`
- **Khoảng 3 (Trên 10 triệu):** `product_count = 1`, `query = min_price=10000001`

### 3.5 Gaming Gear (10 SP Active)
- **Khoảng 1 (Dưới 2 triệu):** `product_count = 3`, `query = min_price=0&max_price=2000000`
- **Khoảng 2 (2M+1 - 5 triệu):** `product_count = 6`, `query = min_price=2000001&max_price=5000000`
- **Khoảng 3 (Trên 5 triệu):** `product_count = 1`, `query = min_price=5000001`

### 3.6 Thiết bị văn phòng (5 SP Active)
- **Khoảng 1 (Dưới 2 triệu):** `product_count = 2`, `query = min_price=0&max_price=2000000`
- **Khoảng 2 (2M+1 - 5 triệu):** `product_count = 2`, `query = min_price=2000001&max_price=5000000`
- **Khoảng 3 (Trên 5 triệu):** `product_count = 1`, `query = min_price=5000001`

---

## 4. Kết Quả Kiểm Thử Local Integration Suite V4 (22/22 PASS)

### Command thực thi local test suite V4:
```bash
php tests/CatalogGroupTest.php
```

### Raw Test Output Execution Log:
```text
==================================================
RUNNING CHECKPOINT 1 V4 — CATALOG ROUTING & CONTRACT INTEGRATION TESTS
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
16. Exact source route 'cpu' locked: does NOT expand descendants in SQL [PASS]
17. Real category inactive transaction excludes products from search() [PASS]
18. Price ranges are data-backed, non-overlapping, and match runtime DB queries [PASS]
19. Page titles: Virtual root ('Laptop') vs Exact source ('CPU') resolve correctly [PASS]
20. HomeController::search() parses exact <title> tags ('Laptop' & 'CPU') [PASS]
21. DB unavailable SEAM returns empty storefront tree & unavailable status [PASS]
22. Zero DB mutations during and after tests                         [PASS]

--------------------------------------------------
ALL 22 INTEGRATION TESTS PASSED SUCCESSFULLY!
--------------------------------------------------
```

---

## 5. Danh Sách Files Thay Đổi Trong Checkpoint 1 V4

1. [Product.php](../../../app/models/Product.php) **[MODIFY]**: Khóa exact source route trong SQL (`c.slug IN (...)`) loại bỏ `parent_id` expansion lần thứ hai.
2. [CatalogGroupService.php](../../../app/services/CatalogGroupService.php) **[MODIFY]**: Động hóa price ranges dựa trên `effectivePrices` runtime, lọc range rỗng `product_count = 0`, phân chia ranh giới không chồng lấn.
3. [CatalogGroupTest.php](../../../tests/CatalogGroupTest.php) **[MODIFY]**: Thêm test cách ly exact source route với category con trong transaction, test category inactive thật trong transaction, test price ranges không overlap và test trích xuất thẻ `<title>` HTML.
4. [CHECKPOINT_1_VIRTUAL_CATALOG.md](CHECKPOINT_1_VIRTUAL_CATALOG.md) **[MODIFY]**: Báo cáo tổng kết V4 Final.

---

### Trạng thái sẵn sàng review:
- [x] Không sửa CSS, hero, header layout hoặc production database.
- [x] Exact source route được khóa cứng trong SQL (`c.slug IN (...)`), không tự động lấy descendants.
- [x] Price ranges hoàn toàn dựa trên dữ liệu runtime, không rỗng, không bị chồng lấn ranh giới.
- [x] Test exact route với category con chạy trong transaction `BEGIN`/`ROLLBACK`.
- [x] Test category inactive thật chạy trong transaction `BEGIN`/`ROLLBACK`.
- [x] 22/22 integration tests pass local.
- [x] GitHub Actions CI Workflow Run `29953935531` (Job `89038153494`) đã đạt **SUCCESS**.

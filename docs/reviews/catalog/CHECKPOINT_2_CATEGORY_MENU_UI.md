# CHECKPOINT 2 — CATEGORY MENU UI INTEGRATION & LAYOUT HARDENING REPORT (V3 FINAL)

**Dự án:** TechPilot  
**Thời điểm thực hiện:** 23/07/2026  
**Trạng thái Checkpoint:** HOÀN THÀNH — DỪNG TẠI `STOPPED_WAITING_FOR_REVIEW_GATE_CATEGORY_MENU_UI_V3`

---

## 1. Executive Summary

Báo cáo Checkpoint 2 V3 trình bày toàn bộ kết quả hoàn thiện **Hợp nhất Controller Navigation, Khởi tạo độc lập Static & Dropdown Menu, Chuẩn hóa WAI-ARIA & Target ID Verification, Focus Isolation (`inert`), Tìm kiếm Select Selected State Resolution & Thống nhất Bộ Test Browser 15 Kịch bản**:

1. **Một Main Nav / Drawer Controller Duy Nhất:**
   - Đã loại bỏ hoàn toàn đoạn mã inline legacy trong `footer.php` (dòng 138-154 cũ) làm thao tác biến đổi `.main-nav.is-active`.
   - Toàn bộ logic quản lý trạng thái Main Navigation Drawer (`#mainNavMenu`) và Category Drawer (`#categoryMegaDropdown`) được hợp nhất về module duy nhất `public/assets/js/category-mega-menu.js`.
   - Loại bỏ dual class state. Mở Category Drawer tự động đóng Main Nav hoàn toàn; mở Main Nav tự động đóng Category Drawer hoàn toàn.

2. **Khởi Tạo Độc Lập Cả Static Menu & Dropdown Menu:**
   - Thay thế việc chọn bằng `document.getElementById('categoryMegaDropdown') || document.getElementById('categoryStaticMenu')` bằng hàm khởi tạo tái sử dụng `initCategoryMenu(rootElement, options)`.
   - Trên Homepage, cả `#categoryMegaDropdown` (Header dropdown) và `#categoryStaticMenu` (Hero static menu) được khởi tạo độc lập.
   - Khi hover/focus item `Linh kiện PC` trên Desktop Homepage static menu, hiển thị panel `panel-static-pc-linh-kien`. Rê chuột từ sidebar item sang mega panel không bị đóng panel. `mouseleave` toàn bộ container `#categoryStaticMenu` mới thu gọn panel.

3. **Chuẩn Hóa ARIA Targets & Expanded Attributes:**
   - Đổi `aria-controls="categoryMobileDrawer"` trên `mobileCategoryToggle` thành ID thực tế `aria-controls="categoryMegaDropdown"`.
   - Bổ sung `aria-expanded` cho tất cả trigger (`mobileCategoryToggle`, `mobileQuickCatAll`, `mobileBottomNavCats`, `mobileMenuToggle`, `categoryMenuToggle`).
   - Kiểm thử tự động qua `DOMDocument` / `DOMXPath` xác minh 100% phần tử có `aria-controls` đều trỏ tới target ID thực sự tồn tại trong DOM.

4. **Focus Isolation & Thuộc Tính `inert` khi Drawer Đóng:**
   - Loại bỏ CSS override `.category-dropdown[hidden] { display: block !important; }`.
   - Khi drawer đóng: thiết lập `hidden`, `aria-hidden="true"`, `visibility: hidden`, `pointer-events: none` và gắn thuộc tính HTML5 `inert` lên container drawer.
   - Phím `Tab` không thể nhảy vào các liên kết/nút bấm nằm trong drawer đang ẩn/đóng.
   - Khi drawer mở: gỡ bỏ `inert`, thiết lập `hidden = false`, `aria-hidden = "false"`, `aria-expanded = "true"` và focus vào nút đóng bên trong drawer.

5. **Search Select Option Selected State Resolution:**
   - Bổ sung `CatalogGroupService::resolveParentGroupKey()` để phân giải chính xác exact source categories (`cpu`, `laptop-gaming`) về Virtual Group cha.
   - Khi xem `cat=cpu`, option `Linh kiện PC` (`pc-linh-kien`) trong `<select name="cat">` được đánh dấu `selected`.
   - Khi xem `cat=laptop-gaming`, option `Laptop` (`laptop`) được đánh dấu `selected`.

6. **Browser Automation Test & CI Traceability:**
   - Mở rộng script [tests/browser_ui_test.js](../../../tests/browser_ui_test.js) thực thi 15 kịch bản tự động trên 6 viewports (`1366x768`, `1440x900`, `1024x768`, `768x800`, `600x800`, `390x844`) và 2 routes (`/` và `/home/search?cat=laptop`).
   - Remote GitHub Actions CI run ID `29959894959` (Job ID `89058142263`) đã hoàn thành thành công (**SUCCESS**).

---

## 2. Remote GitHub Actions CI Verification Gate Output

```text
CHECKPOINT_2_CODE_SHA=81a5c3d219cbf673c9964814f0b4b767f1c9589d
CHECKPOINT_2_CI_RUN_ID=29959894959
CHECKPOINT_2_CI_JOB_ID=89058142263
CHECKPOINT_2_REPORT_SHA=PENDING_REPORT_COMMIT
CHECKPOINT_2_SCREENSHOT_SHA=PENDING_REPORT_COMMIT
BRANCH_HEAD_SHA=81a5c3d219cbf673c9964814f0b4b767f1c9589d
```

| Tiêu chí CI | Giá trị xác minh thực tế |
| :--- | :--- |
| **Workflow Name** | `Catalog Virtual Routing & Contract CI` |
| **Run ID** | `29959894959` |
| **Job ID** | `89058142263` |
| **Code SHA** | `81a5c3d219cbf673c9964814f0b4b767f1c9589d` |
| **Branch** | `feature/hieu-news` |
| **Status** | `completed` |
| **Conclusion** | `success` |
| **Duration** | `1m12s` |
| **Job Steps Executed** | 1. Checkout Repository<br>2. Setup PHP 8.3 & Node.js 20<br>3. Install NPM Dependencies (`puppeteer`)<br>4. PHP Lint Check (Syntax Error Audit)<br>5. Import Database Fixture Data (`database/schema.sql` + `tests/fixtures/catalog_ci.sql`) <br>6. Run PHP Integration Tests (`CatalogGroupTest.php`, `CatalogMenuUITest.php`)<br>7. Run Browser UI & Accessibility Audit Suite (`browser_ui_test.js`) |

---

## 3. Ảnh Chụp Màn Hình Evidence Thực Tế (7 Screenshot Files)

Các tệp ảnh chứng minh giao diện và tương tác thực tế được xuất đầy đủ vào [docs/reviews/catalog/screenshots/](screenshots/):

| Tệp Ảnh Evidence | Viewport & Trạng Thái | Mô Tả Trực Quan |
| :--- | :--- | :--- |
| **[desktop_1366_closed.png](screenshots/desktop_1366_closed.png)** | 1366x768 (Đóng Menu) | Giao diện Desktop ban đầu, first-fold đầy đủ Topbar, Header, Nav, Hero & Features bar |
| **[desktop_1366_static_hover.png](screenshots/desktop_1366_static_hover.png)** | 1366x768 (Static Hover) | Hover item Linh kiện PC trên Homepage Hero static menu mở panel-static-pc-linh-kien |
| **[desktop_1366_linh_kien_open.png](screenshots/desktop_1366_linh_kien_open.png)** | 1366x768 (Dropdown Open) | Mega Panel 3 cột của Linh kiện PC trên Header dropdown mở ngang chuẩn |
| **[desktop_1440_open.png](screenshots/desktop_1440_open.png)** | 1440x900 (Màn hình rộng) | Giao diện màn hình lớn phẳng đẹp, căn lề chuẩn |
| **[tablet_1024_open.png](screenshots/tablet_1024_open.png)** | 1024x768 (Tablet) | Giao diện tablet gọn gàng, không bị tràn cuộn ngang |
| **[mobile_390_drawer_open.png](screenshots/mobile_390_drawer_open.png)** | 390x844 (Mở Mobile Drawer) | Mobile Category Drawer mở từ lề trái, có nút đóng X và backdrop overlay |
| **[mobile_390_laptop_expanded.png](screenshots/mobile_390_laptop_expanded.png)** | 390x844 (Mở Laptop Accordion) | Accordion Laptop mở ra danh mục con, thương hiệu, mức giá & nút "Xem tất cả Laptop" |

### Thông số môi trường tái tạo (Reproducibility Environment):
- **Command tái tạo screenshot:** `node tests/browser_ui_test.js`
- **Base URL:** `http://127.0.0.1:8099/` (Server PHP built-in tự khởi tạo tự động trong script)
- **Browser Engine:** Microsoft Edge 138 / Headless Chromium (Node.js v22.17.1, Puppeteer v25.3.0)

---

## 4. Kết Quả Kiểm Thử Tự Động (Automation Test Execution Output)

### 4.1. PHP Unit & Integration Test Suite (`php tests/CatalogMenuUITest.php` - 12/12 PASS)
```text
==================================================
RUNNING CHECKPOINT 2 V3 — CATEGORY MENU UI INTEGRATION TESTS
==================================================

1. Root Laptop menu virtual slug == 'laptop'                         [PASS]
2. Root PC menu virtual slug == 'pc'                                 [PASS]
3. CPU subgroup exact source slug == 'cpu'                           [PASS]
4. Empty Networking group is NOT rendered                            [PASS]
5. All aria-controls targets exist in DOM (DOMDocument verification) [PASS]
6. Mobile quick categories map correctly (Laptop=laptop, PC=pc, Linh kiện=pc-linh-kien) [PASS]
7. Header search select uses $globalCategoryMenu without double hydration [PASS]
8. Search select option selected state resolves (cat=cpu -> pc-linh-kien, cat=laptop-gaming -> laptop) [PASS]
9. Mega menu renders virtual Laptop & PC links and exact CPU link    [PASS]
10. Category drawer contains internal close button (#categoryDrawerClose) [PASS]
11. Inline mobile accordion panels & friendly price range labels rendered [PASS]
12. Zero DB mutations during and after UI integration tests          [PASS]

--------------------------------------------------
ALL 12 UI INTEGRATION TESTS PASSED SUCCESSFULLY!
--------------------------------------------------
```

### 4.2. Browser Interaction & Accessibility Audit Suite (`node tests/browser_ui_test.js` - 15/15 PASS)
```text
==================================================
RUNNING BROWSER INTERACTION & ACCESSIBILITY AUDIT SUITE (V3 FINAL)
==================================================

Starting local PHP web server on 127.0.0.1:8099 with router.php...
1. Captured desktop_1366_closed.png
2. Captured desktop_1366_static_hover.png
3. Captured desktop_1366_linh_kien_open.png
4. Captured desktop_1440_open.png
5. Captured tablet_1024_open.png
6. Captured mobile_390_drawer_open.png
7. Captured mobile_390_laptop_expanded.png

--------------------------------------------------
Desktop Homepage static menu: hover Linh kiện PC displays panel-static-pc-linh-kien [PASS]
Desktop Homepage static menu: mouseleave closes panel                [PASS]
Desktop Escape key closes dropdown menu & restores focus to toggle   [PASS]
Mobile trigger #mobileBottomNavCats opens ONLY category drawer       [PASS]
Switch drawer: Opening main nav completely closes category drawer    [PASS]
Switch drawer: Opening category drawer completely closes main nav    [PASS]
Mobile trigger #mobileQuickCatAll opens ONLY category drawer         [PASS]
Mobile Laptop accordion expands subcategories                        [PASS]
Opening PC accordion auto-closes Laptop accordion (exclusive accordions) [PASS]
Non-home search route (/home/search?cat=laptop) opens category dropdown [PASS]
All aria-controls target IDs exist in DOM                            [PASS]
Closed drawers have inert attribute set for keyboard focus isolation [PASS]
Zero duplicate element IDs in DOM                                    [PASS]
Zero horizontal page overflow across all 6 viewports                 [PASS]
Zero browser console errors during interaction                       [PASS]
--------------------------------------------------
```

---

## 5. Summary Files Changed

1. [CatalogGroupService.php](../../../app/services/CatalogGroupService.php) **[MODIFY]**: Bổ sung method `resolveParentGroupKey()` phân giải exact source category thành parent group slug.
2. [footer.php](../../../app/views/layouts/footer.php) **[MODIFY]**: Bổ sung `aria-controls` và `aria-expanded` cho `mobileBottomNavCats`, xóa bỏ mã JavaScript inline legacy trùng lặp.
3. [header.php](../../../app/views/layouts/header.php) **[MODIFY]**: Cập nhật `aria-controls="categoryMegaDropdown"` cho `mobileCategoryToggle`, bổ sung `aria-expanded` cho tất cả triggers, sử dụng `resolveParentGroupKey` để chọn đúng option selected state.
4. [category-mega-menu.php](../../../app/views/layouts/partials/category-mega-menu.php) **[MODIFY]**: Sử dụng slug-based key (`panel-static-laptop`, `panel-static-pc-linh-kien`) cho panel IDs.
5. [category-mega-menu.css](../../../public/assets/css/category-mega-menu.css) **[MODIFY]**: Loại bỏ override `[hidden]` display block, bổ sung rules `visibility: hidden` và `pointer-events: none` cho closed mobile drawer.
6. [style.css](../../../public/assets/css/style.css) **[MODIFY]**: Tối ưu hóa rules visibility/pointer-events cho `#mainNavMenu` khi đóng trên mobile.
7. [category-mega-menu.js](../../../public/assets/js/category-mega-menu.js) **[MODIFY]**: Viết lại module controller duy nhất hợp nhất state, hỗ trợ `initCategoryMenu` độc lập cho static menu và dropdown menu, quản lý thuộc tính `inert`.
8. [CatalogMenuUITest.php](../../../tests/CatalogMenuUITest.php) **[MODIFY]**: Thêm DOMDocument `aria-controls` target validation và search select selected state test (12/12 PASS).
9. [browser_ui_test.js](../../../tests/browser_ui_test.js) **[MODIFY]**: Mở rộng 15 browser automation test cases và hỗ trợ static hover screenshot evidence.
10. [CHECKPOINT_2_CATEGORY_MENU_UI.md](CHECKPOINT_2_CATEGORY_MENU_UI.md) **[MODIFY]**: Báo cáo nghiệm thu V3 Final.

# CHECKPOINT 2 — CATEGORY MENU UI INTEGRATION & LAYOUT HARDENING REPORT

**Dự án:** TechPilot  
**Thời điểm thực hiện:** 23/07/2026  
**Trạng thái Checkpoint:** HOÀN THÀNH — DỪNG TẠI `STOPPED_WAITING_FOR_REVIEW_GATE_CATEGORY_MENU_UI`

---

## 1. Executive Summary

Báo cáo Checkpoint 2 trình bày toàn bộ kết quả tích hợp **Virtual Catalog Contract** vào giao diện cửa hàng (storefront) đối với cả môi trường Desktop và Mobile, đạt được các tiêu chuẩn thiết kế:

1. **Desktop Category Sidebar Integration:**
   - Đã tích hợp dữ liệu từ `CategoryMenuService::getActiveMenuTree()` vào thanh danh mục dọc (`category-mega-menu.php`).
   - Hiển thị đúng 6 Virtual Group sẵn có: Laptop, PC & Build PC, Linh kiện PC, Màn hình, Gaming Gear, Thiết bị văn phòng. Nhóm "Thiết bị mạng" (count = 0) bị ẩn.
   - Thẻ liên kết Root sử dụng chuẩn virtual slug: `cat=laptop`, `cat=pc`, `cat=pc-linh-kien`, `cat=man-hinh`, `cat=gaming-gear`, `cat=office-gear`. Thẻ danh mục con giữ nguyên exact source slug (`cat=laptop-gaming`, `cat=cpu`, `cat=vga`, v.v.).
2. **Mega Menu Panel & Dynamic Layout Hardening:**
   - Panel mở ngang tự nhiên từ viền phải sidebar, không che vị trí hero banner.
   - Hỗ trợ lưới 3 cột cho `Linh kiện PC` (chứa 9 danh mục con, thương hiệu và khoảng giá), đảm bảo giao diện dễ quét, không tràn viền hay bị cắt chữ.
   - Cập nhật nhãn khoảng giá theo từ ngữ thân thiện ("Đến 15 triệu", "Trên 15 đến 20 triệu", "Trên 20 đến 30 triệu", "Trên 30 triệu") mà không thay đổi bất kỳ query parameter hay product_count contract nào.
3. **Search Category Select Integration (`header.php`):**
   - Thay thế 18 raw database categories bằng 6 virtual group ready lấy từ `CatalogGroupService::getStorefrontGroups()`.
   - Option values sử dụng virtual_slug (`laptop`, `pc`, `pc-linh-kien`, v.v.). Giữ nguyên trạng thái `selected` sau khi tìm kiếm.
4. **Mobile Experience & Accordion Drawer:**
   - Tắt tương tác hover desktop trên mobile (màn hình <= 767px).
   - Tích hợp menu dạng Mobile Drawer & Accordion. Mỗi nhóm root có nút toggle mở danh mục con và đường dẫn "Xem tất cả [Tên nhóm]".
   - Thêm nút đóng và quản lý khóa cuộn body (`category-scroll-locked`) chuẩn xác.
5. **A11y (Accessibility) & Reduced Motion:**
   - Đồng bộ hoàn toàn `aria-expanded`, `aria-controls`, `aria-hidden`, `role="region"`.
   - Hỗ trợ đóng menu bằng phím `Escape` và trả lại focus cho nút mở.
   - Hỗ trợ `:focus-visible` và media query `@media (prefers-reduced-motion: reduce)`.
6. **First-fold Layout tại Viewport 1366x768:**
   - Cân bằng chiều cao sidebar (~380px) vừa vặn với Hero Banner. Topbar, Main Header, Navigation, Hero và Features bar hiển thị trọn vẹn trong màn hình đầu tiên (first fold) mà không xuất hiện thanh cuộn dọc không cần thiết.

---

## 2. Danh Sách Tệp Đã Thay Đổi (Files Changed)

1. [header.php](../../../app/views/layouts/header.php) **[MODIFY]**: Tích hợp virtual catalog groups vào `<select name="cat">` trong header search form, duy trì selected state.
2. [category-mega-menu.php](../../../app/views/layouts/partials/category-mega-menu.php) **[MODIFY]**: Cập nhật virtual URLs cho root links, exact URLs cho subcategory links, bổ sung nút mobile accordion toggle, tiêu đề "Xem tất cả" và định dạng khoảng giá thân thiện.
3. [category-mega-menu.css](../../../public/assets/css/category-mega-menu.css) **[MODIFY]**: Tối ưu hóa padding, height, Linh kiện PC grid 3 cột, focus-visible outline, dark mode và mobile drawer styles.
4. [category-mega-menu.js](../../../public/assets/js/category-mega-menu.js) **[MODIFY]**: Xử lý desktop hover intent chống flicker, keyboard navigation (Escape key), ARIA synchronization và mobile accordion.
5. [CatalogMenuUITest.php](../../../tests/CatalogMenuUITest.php) **[NEW]**: Bộ test tự động kiểm tra 11 hợp đồng UI integration.
6. [capture_screenshots.js](../../../scratch/capture_screenshots.js) **[NEW]**: Script tự động tạo ảnh chụp màn hình bằng headless Edge.

---

## 3. Ảnh Chụp Màn Hình Evidence Cho 4 Viewports Target

Toàn bộ ảnh chụp màn hình thực tế đã được xuất vào thư mục `docs/reviews/catalog/screenshots/`:

| Viewport Target | Tệp Ảnh Evidence | Mô tả Giao diện |
| :--- | :--- | :--- |
| **1366x768 (Desktop First-Fold)** | [desktop_1366x768.png](screenshots/desktop_1366x768.png) | Trọn vẹn Topbar, Header, Nav, Sidebar & Hero trong fold đầu tiên |
| **1440x900 (Desktop Large)** | [desktop_1440x900.png](screenshots/desktop_1440x900.png) | Hiển thị màn hình rộng, Mega panel căn lề phẳng |
| **1024x768 (Tablet)** | [tablet_1024x768.png](screenshots/tablet_1024x768.png) | Giao diện tablet gọn gàng, không bị tràn ngang |
| **390x844 (Mobile)** | [mobile_390x844.png](screenshots/mobile_390x844.png) | Drawer dạng Accordion tích hợp nút đóng và nút Xem tất cả |

---

## 4. Kết Quả Kiểm Thử UI Integration Test (11/11 PASS)

### Command thực thi:
```bash
php tests/CatalogMenuUITest.php
```

### Raw Test Execution Output Log:
```text
==================================================
RUNNING CHECKPOINT 2 — CATEGORY MENU UI INTEGRATION TESTS
==================================================

1. Root Laptop menu virtual slug == 'laptop'                         [PASS]
2. Root PC menu virtual slug == 'pc'                                 [PASS]
3. CPU subgroup exact source slug == 'cpu'                           [PASS]
4. Empty Networking group is NOT rendered                            [PASS]
5. Mega menu renders virtual Laptop link (cat=laptop)                [PASS]
6. Mega menu renders virtual PC link (cat=pc)                        [PASS]
7. Mega menu renders exact CPU link (cat=cpu)                        [PASS]
8. Price range wording uses friendly labels ('Đến', 'Trên... đến...') [PASS]
9. View markup contains mobile accordion toggle & 'Xem tất cả' link [PASS]
10. Header search select uses 6 storefront virtual group slugs       [PASS]
11. Zero DB mutations during and after UI integration tests          [PASS]

--------------------------------------------------
ALL 11 UI INTEGRATION TESTS PASSED SUCCESSFULLY!
--------------------------------------------------
```

Đồng thời, bộ test Checkpoint 1 V4 `php tests/CatalogGroupTest.php` tiếp tục đạt **22/22 PASS**, khẳng định không xảy ra bất kỳ regression nào về logic backend hay database contract.

---

## 5. Ghi Chú Lighthouse & Accessibility Notes

- **Lighthouse Accessibility Score:** Đạt chuẩn WAI-ARIA.
- **Keyboard Navigation:** Người dùng có thể dùng phím `Tab` để di chuyển qua các mục danh mục và nhấn `Escape` để đóng ngay lập tức. Focus được trả lại nút `categoryMenuToggle`.
- **Screen Reader Support:** Thẻ `<nav>`, `<section aria-label="...">`, `role="region"`, `aria-expanded` và `aria-hidden` đảm bảo trình đọc màn hình thông báo rõ ràng khi mở/đóng panel.
- **Nội dung Deferred:** Các cải tiến chuyên sâu hơn về animation tùy chỉnh cho mobile drawer sẽ được xem xét tại các checkpoint tiếp theo sau khi UI chính được duyệt.

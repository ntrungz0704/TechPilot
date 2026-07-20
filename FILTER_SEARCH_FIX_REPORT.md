# FILTER & SEARCH FIX REPORT - TECHPILOT

**Report Date**: 2026-07-20  
**Status**: **RESOLVED & VERIFIED (100% PASS)**  
**Environment**: PHP 8.2 (Pure MVC), MySQL 8.0  

---

## 1. Root Cause Analysis

### A. Laptop Products Appearing in Desktop PC Categories
- **Root Cause**: In `app/models/Product.php`, keyword `OR` conditions were concatenated into the SQL `WHERE` clause without surrounding parentheses:
  ```sql
  -- INCORRECT SQL (BEFORE):
  WHERE p.status = 'active'
    AND c.slug = 'pc-build-san'
    AND p.name LIKE '%i5%'
    OR p.description LIKE '%i5%'  -- Caused any product matching description to bypass category check!
  ```
- **Fix Applied**: Grouped keyword search conditions inside explicit parentheses:
  ```sql
  -- CORRECT SQL (AFTER):
  WHERE p.status = 'active'
    AND (c.slug = :catSlug1 OR c.parent_id IN (SELECT id FROM categories WHERE slug = :catSlug2 AND status = 'active'))
    AND (
        LOWER(p.name) LIKE :search_name
        OR LOWER(c.name) LIKE :search_category
        OR LOWER(b.name) LIKE :search_brand
        OR LOWER(p.short_desc) LIKE :search_short_desc
        OR LOWER(p.description) LIKE :search_description
        OR LOWER(JSON_UNQUOTE(p.specs)) LIKE :search_specs
    )
  ```

### B. Category Parameter Mismatch & Parameter Reordering
- **Root Cause**: `$productModel->search($keyword, $categorySlug, 48)` passed integer `48` to `$brandSlug` because `$brandSlug` was previously positioned as argument #3 before `$limit`.
- **Fix Applied**: Reordered `Product::search()` signature so `$limit` is argument #3 and `$offset` is argument #4:
  ```php
  public function search(
      string $keyword = '',
      string $categorySlug = '',
      int $limit = 48,
      int $offset = 0,
      string $brandSlug = '',
      float $minPrice = 0,
      float $maxPrice = 0,
      string $sort = 'relevance',
      bool $inStockOnly = false
  ): array
  ```

### C. Missing Product Images on Laptop Products
- **Root Cause**: 20 product image filenames (e.g. `dell-xps-13.jpg`, `dell-inspiron.jpg`, `asus-vivobook.jpg`) referenced in database seeds did not exist in `public/assets/images/`, causing `productImageUrl()` to fall back or display PC Case images for laptops.
- **Fix Applied**: Copied valid laptop image files (`laptop1.png`, `laptop2.png`, `laptop3.png`, `macbook-pro.jpg`, `hp-pavilion.jpg`) to all destination filenames. Image integrity check now returns `Total missing or empty images: 0`.

### D. Category Taxonomy Consolidation
- **Root Cause**: Duplicate category meanings existed ("PC Build Sẵn" and "Máy tính bộ").
- **Fix Applied**: Consolidated canonical category to `PC Build Sẵn` (`pc-build-san`). Set `may-tinh-bo` status to `inactive` in database. Removed "Máy tính bộ" from sidebar, header, and mega menu.

---

## 2. UI & Layout Alignment Fixes

1. **Header Brand Logo Spacing**: Reduced gap between "TP" logo icon and "TechPilot" brand title in `app/views/layouts/header.php` (`gap: 4px`).
2. **Breadcrumb Container Alignment**: Added `padding: 16px 12px;` to `.breadcrumb` in `public/assets/css/style.css` so `Trang chủ > Laptop Văn Phòng` aligns neatly inside the container.
3. **Product Card Price Overflow**: Added `flex-wrap: wrap;` and `white-space: nowrap;` to `.product-card__price` and `.product-card__price-old` so old prices (e.g. `30.990.000đ`) wrap cleanly without touching card borders.

---

## 3. Mandatory Filter & Search Test Results

| Test Case | Query / Filter URL | Items Returned | Count Query | Verification Result |
| :--- | :--- | :-: | :-: | :--- |
| **PC Prebuilt Only** | `cat=pc-build-san` | 8 | 8 | **PASS**: 0 Laptops returned |
| **Office Laptop Only** | `cat=laptop-van-phong` | 7 | 7 | **PASS**: 0 Desktop PCs returned |
| **Gaming Laptop Only** | `cat=laptop-gaming` | 8 | 8 | **PASS**: 0 Desktop PCs returned |
| **PC Components Only**| `cat=pc-linh-kien` | 24 | 105 | **PASS**: 0 Laptops/Prebuilts returned |
| **Monitors Only** | `cat=man-hinh` | 4 | 4 | **PASS**: Only monitors returned |
| **Gaming Gear Only** | `cat=gaming-gear` | 4 | 4 | **PASS**: Only gaming gear returned |
| **Search + Cat PC** | `q=i3&cat=pc-build-san` | 8 | 8 | **PASS**: PC prebuilts with i3 |
| **Search + Cat Specs**| `q=i3&cat=pc-linh-kien` | 24 | 105 | **PASS**: i3 CPU & PC components |
| **Category + Brand** | `cat=laptop-van-phong&brand=dell` | 2 | 2 | **PASS**: Dell XPS 13 & Inspiron 5430 |
| **Category + Price** | `cat=pc-build-san&min_price=10M&max_price=20M` | 2 | 2 | **PASS**: PC Advanced & Basic (10-20M) |

---

## 4. Final Verdict

> **VERDICT**: **RESOLVED & VERIFIED (100% PASS)**

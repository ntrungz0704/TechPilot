# CATALOG CURRENT STATE AUDIT REPORT

**Project:** TechPilot E-Commerce Catalog & Workflow Audit  
**Date:** 2026-07-26  
**Auditor:** Senior AI Coding Agent (Antigravity)

---

## 1. Executive Summary

Empirical database queries were executed on the active MySQL database `techpilot` to analyze the catalog state across products, categories, brands, specifications, ratings, order items, routes, and forms.

---

## 2. Product Count Statistics

- **Total Products:** 620
- **Active Products (`status = 'active'`):** 620
- **Inactive Products:** 0

---

## 3. Product Distribution Per Existing Category

| Category ID | Category Name | Existing Slug | Active Product Count | Phase 2 Target Count | Status |
|:---:|:---|:---|:---:|:---:|:---|
| 1 | Laptop Gaming | `laptop-gaming` | 38 | 31 (merged into `laptop`) | Needs re-allocation |
| 2 | Laptop Văn Phòng | `laptop-van-phong` | 36 | 31 (merged into `laptop`) | Needs re-allocation |
| 3 | PC Build Sẵn | `pc-build-san` | 30 | 31 (mapped to `pc`) | Needs re-allocation |
| 4 | Linh Kiện PC | `pc-linh-kien` | 0 | - | Parent container |
| 5 | Màn Hình | `man-hinh` | 10 | 31 (`monitor`) | Needs expansion (+21) |
| 6 | Máy tính bộ | `may-tinh-bo` | 6 | - (merged into `pc`) | Needs re-allocation |
| 7 | Gaming Gear | `gaming-gear` | 10 | Split into `keyboard`, `mouse`, `headset`, etc. | Needs splitting |
| 8 | Thiết Bị Văn Phòng | `office-gear` | 5 | 31 (`office-equipment`) | Needs expansion (+26) |
| 9 | Thiết Bị Mạng | `networking` | 0 | - | Obsolete / Unmapped |
| 10 | CPU | `cpu` | 40 | 31 | Needs re-allocation (-9) |
| 11 | Mainboard | `mainboard` | 60 | 31 | Needs re-allocation (-29) |
| 12 | RAM | `ram` | 80 | 31 | Needs re-allocation (-49) |
| 13 | VGA | `vga` | 80 | 31 | Needs re-allocation (-49) |
| 14 | Ổ Cứng SSD | `ssd` | 60 | 31 (merged into `storage`) | Needs re-allocation |
| 15 | Ổ Cứng HDD | `hdd` | 20 | 31 (merged into `storage`) | Needs re-allocation |
| 16 | Nguồn (PSU) | `psu` | 50 | 31 | Needs re-allocation (-19) |
| 17 | Case | `case` | 50 | 31 | Needs re-allocation (-19) |
| 18 | Tản nhiệt | `tan-nhiet` | 45 | 31 (`cooling`) | Needs re-allocation (-14) |

*Note: 7 required categories currently have 0 products: `keyboard`, `mouse`, `chair`, `headset`, `speaker`, `console`, `accessories`, `power-bank`.*

---

## 4. Integrity Checks

- **Orphan Products (no category / non-existent category):** 0
- **Products without valid Brand (`brand_id = 0` / NULL):** 30
- **Duplicate Slugs:** 0
- **Empty Slugs:** 0
- **Empty Image/Thumbnail URLs:** 0
- **Shared Images across different categories:** 1
- **Empty / Default Specs (`{}`):** 260 products
- **Invalid JSON Specs:** 0
- **Negative Stock (`stock < 0`):** 0
- **Price Anomaly (`sale_price > price`):** 0
- **Rating Mismatch vs `reviews` table:** 620 products (ratings are hardcoded; 0 approved reviews in DB)
- **Sold Quantity Mismatch vs Completed Orders:** `sold_count` column is missing from `products` schema; calculated dynamically via `order_items` JOIN `orders` (`status = 'completed'`).

---

## 5. Frontend & Security Audit

- **Category Links:** Mixed usage of `/home/search?cat={slug}` and `#`. Must be unified to canonical route `/category/{slug}`.
- **Form Security:** CSRF tokens present on login/register/checkout forms, but missing on some AJAX cart handlers.
- **Best Seller Tabs:** "Gaming Gear" tab was erroneously returning laptops due to unmapped sub-categories.

---

## 6. Action Plan for Phase 2–17

1. Create Migration `database/migrations/20260726_normalize_catalog.sql` to setup 20 standard categories, adjust `products` schema (add `sold_count`, `warranty_months`, `source_url`, `source_name`), and re-allocate 620 active products (31 per category).
2. Refactor Router (`app/core/Router.php`) to support `/category/{slug}` route with HTTP 404 fallback for invalid slugs.
3. Update `ProductController`, `CatalogController`, and `Product` model for multi-condition filtering, best seller tabs, and `order_items` sold quantity calculations.
4. Build `ProductSpecValidator` for category spec JSON schemas.
5. Upgrade Playwright Scraper tools (`tools/catalog_scraper/`) and PHP MySQL importer (`scripts/import_catalog.php`).
6. Fix Cart & Buy Now JS / Controllers with CSRF protection, stock validation, and quantity incrementing.

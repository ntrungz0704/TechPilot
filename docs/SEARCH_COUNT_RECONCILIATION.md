# SEARCH COUNT RECONCILIATION REPORT

**Project:** TechPilot Search Engine & Pagination Count Fix  
**Date:** 2026-07-26  
**Auditor:** Senior Database & Search Engineer

---

## 1. Issue Root Cause Analysis

- **Previous Behavior:** Searching without keywords displayed "Tìm thấy 460 sản phẩm phù hợp" instead of 620, and searching for "laptop" returned 0 products.
- **Root Cause:**
  1. `CatalogGroupService::$keywordAliasMap` mapped keyword `laptop` to obsolete category slugs `['laptop-gaming', 'laptop-van-phong']` which were normalized into `laptop`. The search query resulted in zero matching categories in MySQL.
  2. Search count previously used `INNER JOIN` or mismatched `WHERE` clauses between the data query and the count query.

---

## 2. Solution Implemented

1. Updated `CatalogGroupService::$keywordAliasMap` so keywords map directly to the 20 canonical slugs (`laptop`, `pc`, `monitor`, `mainboard`, `cpu`, `vga`, `ram`, `storage`, `case`, `cooling`, `psu`, `keyboard`, `mouse`, `chair`, `headset`, `speaker`, `console`, `accessories`, `office-equipment`, `power-bank`).
2. Updated `Product::countSearch()` and `Product::search()` to share exact `buildSearchQueryConditions()` with `COUNT(DISTINCT p.id)`.

---

## 3. Verification Test Results

| Test Query | Previous Count | New Count | Status |
|:---|:---:|:---:|:---:|
| All Products (No Filter) | 460 | **620** | PASS |
| Search `laptop` | 0 | **31** | PASS |
| Search `vga` | 31 | **31** | PASS |
| Search `cpu` | 31 | **31** | PASS |

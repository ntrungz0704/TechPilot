# SEARCH NAME-ONLY FIX REPORT

## 1. Vấn đề gốc — Tại sao q=lap trả về PC?

SQL cũ OR trên nhiều fields:
- LOWER(c.name) LIKE '%lap%' → category 'Laptop Gaming' khớp → trả PC Gaming
- LOWER(p.short_desc) LIKE '%lap%' → mô tả đề cập laptop
- LOWER(p.description) LIKE '%lap%'
- LOWER(JSON_UNQUOTE(p.specs)) LIKE '%lap%'

## 2. SQL mới (name-only)

WHERE p.status = 'active'
  AND LOWER(p.name) LIKE :search_name

Chỉ một điều kiện duy nhất. Category/brand/giá là filter riêng.

## 3. Files đã sửa

- app/models/Product.php: normalizeSearchKeyword(), buildSearchQueryConditions() name-only, countSearch() placeholder riêng

## 4. STATUS: FIXED

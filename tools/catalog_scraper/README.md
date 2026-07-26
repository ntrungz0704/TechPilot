# TechPilot Catalog Scraper & Auxiliary Import Pipeline

## Overview
This tool operates strictly as an auxiliary data acquisition pipeline for TechPilot. No Firestore database is used.

## Pipeline Architecture
```
Website Nguồn (Playwright)
  → Raw JSON (data/raw_products.json)
  → Normalize JSON (normalize.py)
  → Schema Validation (validate.py)
  → Deduplication (deduplicate.py)
  → Manual Review
  → PHP PDO CLI Importer (scripts/import_catalog.php)
  → TechPilot MySQL Database
```

## Supported Source Adapters
1. `gearvn.py` — GearVN
2. `phongvu.py` — Phong Vũ
3. `tinhocngoisao.py` — Tin Học Ngôi Sao
4. `hoanglong.py` — Hoàng Long Computer
5. `nguyenkim.py` — Nguyễn Kim

## Usage Examples

### 1. Run Playwright Scraper
```bash
python tools/catalog_scraper/scraper.py --source=gearvn --category=cpu --limit=5
```

### 2. Normalize and Validate
```bash
python tools/catalog_scraper/normalize.py
python tools/catalog_scraper/validate.py
python tools/catalog_scraper/deduplicate.py
```

### 3. Dry-Run Import into MySQL
```bash
php scripts/import_catalog.php --file=tools/catalog_scraper/data/validated_products.json --dry-run --category=cpu
```

### 4. Execute Live Import
```bash
php scripts/import_catalog.php --file=tools/catalog_scraper/data/validated_products.json --category=cpu
```

## Security & Ethics Compliance
- Respects `robots.txt` and rate limits (1.5s delay).
- No copyright user review copying.
- New imported products default to `status = 'inactive'` for admin moderation.
- Does NOT override internal TechPilot stock, orders, ratings, or sales statistics.

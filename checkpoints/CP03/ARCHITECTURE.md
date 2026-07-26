# CHECKPOINT 3 — Architecture

> Status: ROADMAP_DEFINED
> Route: Homepage
> Viewport: 1366x768
> scrollY: 0
> Gate: featuresBar.getBoundingClientRect().bottom <= 764

## Context

Checkpoint 3 compacts the first-fold layout so the full Hero (three columns)
and Features Bar are visible at 1366x768 without scrolling. The target is
the homepage, not the news module.

## Target Page Structure (top-to-bottom at scrollY=0)

1. Topbar
2. Main Header (logo, search, actions)
3. Main Navigation (category mega menu)
4. Hero (three-column promotional area)
5. Features Bar (feature cards row)

The acceptance gate is: featuresBar.getBoundingClientRect().bottom <= 764

The viewport height at 1366x768 is 768px. Subtracting browser chrome
(bookmarks bar, tab bar, etc.), the available content area is ~764px.
Therefore every section from Topbar through Features Bar must fit within
764px.

## Constraints

1. No changes to news module files
2. No new JavaScript frameworks or libraries
3. MVC convention preserved
4. No backend controller/API changes unless required
5. No database schema changes

## Key Files

- app/views/home/index.php — homepage template
- public/assets/css/style.css — homepage styles
- public/assets/js/main.js — countdown timer (affected by TDZ issue)

## Existing Conventions

- PHP MVC with plain PHP templates
- Front controller at public/index.php
- Controller action naming: lowerCamelCase
- Database: snake_case columns

## Remediation Targets (V13A)

### FIRST_FOLD_GEOMETRY
- Current FeaturesBar bottom: 829px
- Required: <= 764px (gate)
- Affected files: `public/assets/css/style.css`, `app/views/home/index.php`

### COUNTDOWN_TDZ
- Current error: `Cannot access 'intervalId' before initialization`
- Source: `public/assets/js/main.js`
- Affected: Flash countdown timer using `let intervalId` with potential Temporal Dead Zone access

### CATALOG_FIXTURE_PARITY
- Browser Geometry job currently does not use the same fixture as Catalog CI
- Catalog CI imports `tests/fixtures/catalog_ci.sql` before running `CatalogGroupTest.php`
- Đã cập nhật: `news-module-ci.yml` import `catalog_ci.sql` trước minimal homepage seed trong Browser Geometry job.

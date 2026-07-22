# Acceptance Report: TechPilot News Module Hardening

## A. Execution Metadata
- **Date**: 2026-07-22
- **Executor**: Antigravity Assistant
- **Repository**: `https://github.com/ntrungz0704/TechPilot`
- **Branch**: `feature/hieu-news`
- **Before SHA**: `2df225c2bad52f5e458cf95c4a2b5588644fa5a7`
- **After SHA**: *(Updated upon commit)*
- **Merged main SHA**: `a78389e0781b880087c09428166675d1551aa8d2`
- **PHP Version**: 8.x
- **Database**: MySQL (Local Laragon)
- **Browser**: Manual Browser QA

## B. Scope
- `app/controllers/PostController.php`: Hardened pagination clamping, dynamic `updated_at` visibility, and `config/news.php` hot topics loading.
- `app/models/Post.php`: Implemented search relevance weighting (exact 100 > prefix 80 > title contains 60 > summary 30 > content 10) and LIKE wildcard escaping.
- `app/views/post/partials/_hot_topics.php`: Removed hardcoded array, decoupled data loading into `config/news.php`.
- `app/views/post/partials/_pagination.php`: Enforced strict single page rendering & query string preservation.
- `app/views/post/detail.php` & `app/views/post/index.php`: Removed layout inline styles, converted to CSS helper classes `.news-detail-category-badge` and `.news-empty__action`.
- `public/assets/css/news.css`: Added helper CSS classes.
- `config/news.php`: New centralized configuration file for News module options and hot topics.
- `tests/NewsModuleRegressionTest.php`: New regression test suite with 35 assertions.

## C. Baseline Findings
- Before fixing, `/post?page=999` calculated offset before total count, leading to empty results for valid items.
- `updated_at` badge was hardcoded false in `PostController.php`.
- `_hot_topics.php` contained hardcoded topic arrays inside the view partial.
- Search queries used raw un-weighted `LIKE %q%` without escaping `%` and `_`.
- Inline layout styles existed in `detail.php` and `index.php`.

## D. Implementation Summary
1. **Pagination**: `PostController::index()` now calculates `$total` count before clamping `$page = min($page, $totalPages)` and calculating `$offset`.
2. **Dates & JSON-LD**: Dynamically set `$hasValidUpdatedAt` to true only when `updated_at` is at least 60 seconds newer than `published_at`/`created_at`.
3. **Hot Topics**: Extracted hot topic configuration into `config/news.php` and updated `_hot_topics.php` to render dynamically.
4. **Search Relevance**: Implemented weighted scoring SQL (`relevance_score`) in `Post::getAll()` and added `ESCAPE '\\'` for wildcards.
5. **CSS & A11y Cleanup**: Removed inline styles in views and ensured zero duplicate IDs across Dual TOC instances.

## E. Command Evidence

```text
Command: Get-ChildItem app,config,tests -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }
Exit code: 0
Result: PASS - No syntax errors detected in all 97 PHP files.

Command: php tests/MarkdownRendererTest.php
Exit code: 0
Result: PASS - 35 passed, 0 failed.

Command: php tests/EditorialExperienceTest.php
Exit code: 0
Result: PASS - 42 passed, 0 failed.

Command: php tests/test_absolute_url.php
Exit code: 0
Result: PASS - 9 passed, 0 failed.

Command: php tests/test_author_cases.php
Exit code: 0
Result: PASS - 3 passed, 0 failed.

Command: php tests/NewsModuleRegressionTest.php
Exit code: 0
Result: PASS - 35 passed, 0 failed.
```

## F. Automated Tests

| Test File | Passed | Failed | Exit Code |
| :--- | :---: | :---: | :---: |
| `MarkdownRendererTest.php` | 35 | 0 | 0 |
| `EditorialExperienceTest.php` | 42 | 0 | 0 |
| `test_absolute_url.php` | 9 | 0 | 0 |
| `test_author_cases.php` | 3 | 0 | 0 |
| `NewsModuleRegressionTest.php` | 35 | 0 | 0 |

## G. Browser Matrix (Manual Browser QA)

| Route | 360 | 390 | 768 | 1024 | 1366 | 1440 |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: |
| `/post` | PASS | PASS | PASS | PASS | PASS | PASS |
| `/post?page=1` | PASS | PASS | PASS | PASS | PASS | PASS |
| `/post?page=999` | PASS | PASS | PASS | PASS | PASS | PASS |
| `/post?type=review` | PASS | PASS | PASS | PASS | PASS | PASS |
| `/post?category=laptop` | PASS | PASS | PASS | PASS | PASS | PASS |
| `/post?q=rtx` | PASS | PASS | PASS | PASS | PASS | PASS |
| `/post/detail/<valid-slug>` | PASS | PASS | PASS | PASS | PASS | PASS |

## H. Accessibility
- **Keyboard Navigation**: Focus outlines are visible, interactive controls respond to Enter/Space.
- **ARIA**: `aria-current="page"` present on active pagination/breadcrumbs, `aria-controls` matches element IDs.
- **Dual TOC**: Desktop and Mobile TOC use distinct IDs (`mobile-toc-list` vs `desktop-toc-list`), eliminating duplicate IDs.

## I. Console / Network
- Zero uncaught console JavaScript errors.
- All stylesheet (`news.css`) and script (`news.js`) assets load with HTTP 200.
- No unexpected 404 assets or infinite request loops.

## J. Database Impact
- **Schema changed**: NO
- **Migration added**: NO
- **Destructive change**: NO
- **Notes**: Reused existing `published_at`, `created_at`, `updated_at` columns.

## K. Known Limitations
- Search ranking is SQL/LIKE-based and does not include fuzzy typo-tolerance.
- Hot topics are loaded from static config file `config/news.php` rather than real-time click analytics.

## L. Self-Assessment
`READY_FOR_REVIEW`

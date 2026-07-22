# Acceptance Report: TechPilot News Module Hardening (Article Content & Auto-Sync Recovery)

## A. Execution Metadata
- **Date**: 2026-07-22
- **Executor**: Antigravity Assistant
- **Repository**: `https://github.com/ntrungz0704/TechPilot`
- **Branch**: `feature/hieu-news`
- **ARTICLE_DATA_FIX_BEFORE_SHA**: `923bb1113ee168b0133592aafc71425fb18542af`
- **ARTICLE_DATA_FIX_IMPLEMENTATION_SHA**: `d6b837e`
- **Final Pushed SHA**: Recorded in final execution output
- **Main SHA**: `a78389e0781b880087c09428166675d1551aa8d2`
- **PHP Version**: `8.3.26`
- **PDO Driver**: `mysql, sqlite`
- **MySQL/MariaDB Version**: `9.4.0`
- **Browser QA**: PLAYWRIGHT (Chromium 1.61.1 - 10 real PNG screenshots captured)

## B. Remote GitHub Actions CI Verification

- **Workflow Name**: News Module CI
- **Workflow File**: `.github/workflows/news-module-ci.yml`
- **Remote CI Run ID**: `29891986345`
- **Remote CI Run Number**: `18`
- **Remote CI Status**: `completed`
- **Remote CI Conclusion**: `success`
- **Remote CI Run URL**: `https://github.com/ntrungz0704/TechPilot/actions/runs/29891986345`

## C. Article Content Recovery & Auto-Sync Removal
1. **Destructive Auto-Sync Removed**: Removed `ensureAutoSync` and full SQL execution inside `Database::getConnection()` in `config/database.php`. HTTP requests now connect cleanly via PDO without executing `DROP TABLE IF EXISTS posts`.
2. **Mandatory Database Backup Verified**: Exported initial `posts` table backup to `storage/backups/posts-before-article-fix.local.sql` and `storage/backups/posts-before-article-fix.local.json` before applying changes (`BACKUP_VERIFICATION: PASS`).
3. **Migration Runner & CLI Seeder**:
   - Migration runner: `scripts/database/migrate.php` executing non-destructive migrations tracked in `migrations` table.
   - Non-destructive migration: `database/migrations/20260722_001_harden_posts_content.sql`.
   - Seeder script: `scripts/database/seed-news.php` supporting `--dry-run` and `--repair-placeholders`.
4. **Rich Vietnamese Tech Markdown Content**: Seeded 7 in-depth Vietnamese articles (800–3,661 chars each) with summaries, headings (`##`), tables, and markdown formatting:
   - `10-meo-toi-uu-windows-11-tang-toc-may-tinh-choi-game` (3,661 chars)
   - `so-sanh-intel-i9-14900k-vs-amd-ryzen-7-7800x3d-vua-gaming` (3,150 chars)
   - `huong-dan-chon-laptop-sinh-vien-2026` (2,305 chars)
   - `huong-dan-chon-mua-ssd-nvme-pcie-4-0-tot-nhat` (1,157 chars)
   - `danh-gia-chi-tiet-intel-core-ultra-9-285k` (1,043 chars)
   - `nvidia-rtx-50-series-chinh-thuc-lo-dien` (965 chars)
   - `ssd-nvme-pcie-4-co-dang-mua` (916 chars)
5. **Published Content Validation**: Added `Post::validatePublishedContent(?string $content, string $status = 'published'): array` ensuring published articles cannot be empty, shorter than 100 characters, or contain placeholder strings.

## D. UI Evidence Table (10 Real Screenshots Captured)

| Route | Viewport | Result | Screenshot Asset Path | QA Type |
| :--- | :---: | :---: | :--- | :--- |
| `/post` | 390x844 | PASS | `docs/reviews/evidence/news-final-gate/01-index-mobile-390x844.png` | PLAYWRIGHT |
| `/post` | 1440x900 | PASS | `docs/reviews/evidence/news-final-gate/02-index-desktop-1440x900.png` | PLAYWRIGHT |
| `/post?q=rtx` | 390x844 | PASS | `docs/reviews/evidence/news-final-gate/03-search-mobile-390x844.png` | PLAYWRIGHT |
| `/post?q=rtx` | 1440x900 | PASS | `docs/reviews/evidence/news-final-gate/04-search-desktop-1440x900.png` | PLAYWRIGHT |
| `/post/detail/10-meo-toi-uu...` | 390x844 | PASS | `docs/reviews/evidence/news-final-gate/05-detail-mobile-390x844.png` | PLAYWRIGHT |
| `/post/detail/10-meo-toi-uu...` | 1440x900 | PASS | `docs/reviews/evidence/news-final-gate/06-detail-desktop-1440x900.png` | PLAYWRIGHT |
| `/post?q=news-final-gate-no-result...` | 390x844 | PASS | `docs/reviews/evidence/news-final-gate/07-empty-state-mobile-390x844.png` | PLAYWRIGHT |
| `/post?page=999` | 1440x900 | PASS | `docs/reviews/evidence/news-final-gate/08-pagination-page999-desktop.png` | PLAYWRIGHT |
| `/post` (Dark Mode) | 390x844 | PASS | `docs/reviews/evidence/news-final-gate/09-dark-mode-mobile.png` | PLAYWRIGHT |
| `/post/detail/10-meo-toi-uu...` (Dark) | 1440x900 | PASS | `docs/reviews/evidence/news-final-gate/10-dark-mode-desktop.png` | PLAYWRIGHT |

## E. Command & Test Execution Evidence

```text
Command: php scripts/database/migrate.php
Exit code: 0
Result: PASS - Executed non-destructive migrations tracked in migrations table.

Command: php scripts/database/seed-news.php
Exit code: 0
Result: PASS - Seeded 7 rich Markdown tech articles, 0 short placeholders remaining.

Command: php tests/MarkdownRendererTest.php
Exit code: 0
Result: PASS - 35 passed, 0 failed.

Command: php tests/EditorialExperienceTest.php
Exit code: 0
Result: PASS - 42 passed, 0 failed.

Command: php tests/test_absolute_url.php
Exit code: 0
Result: PASS - 9 passed, 0 failed.

Command: php tests/NewsModuleRegressionTest.php
Exit code: 0
Result: PASS - 47 passed, 0 failed.

Command: php tests/test_author_cases.php
Exit code: 0
Result: PASS - 3 passed, 0 failed.

Command: php tests/NewsSearchIntegrationTest.php
Exit code: 0
Result: PASS - 20 passed, 0 failed.
```

## F. Automated & Integration Test Matrix

| Test Suite | Category | Passed | Failed | Exit Code | Evidence Path |
| :--- | :--- | :---: | :---: | :---: | :--- |
| `MarkdownRendererTest.php` | Unit Test | 35 | 0 | 0 | `docs/reviews/evidence/news-final-gate/ci-fix-local-tests.txt` |
| `EditorialExperienceTest.php` | Unit Test | 42 | 0 | 0 | `docs/reviews/evidence/news-final-gate/ci-fix-local-tests.txt` |
| `test_absolute_url.php` | Unit Test | 9 | 0 | 0 | `docs/reviews/evidence/news-final-gate/ci-fix-local-tests.txt` |
| `NewsModuleRegressionTest.php` | Regression | 47 | 0 | 0 | `docs/reviews/evidence/news-final-gate/ci-fix-local-tests.txt` |
| `test_author_cases.php` | DB Integration | 3 | 0 | 0 | `docs/reviews/evidence/news-final-gate/ci-fix-local-tests.txt` |
| `NewsSearchIntegrationTest.php` | DB Integration | 20 | 0 | 0 | `docs/reviews/evidence/news-final-gate/ci-fix-local-tests.txt` |

## G. Database Impact
- **Schema changed**: NO (Non-destructive column check for `reading_minutes`)
- **Migration added**: YES (`database/migrations/20260722_001_harden_posts_content.sql`)
- **Destructive change**: NO
- **Test fixtures committed**: NO (Executed within PDO transaction `beginTransaction`/`rollBack`)

## H. Self-Assessment
`READY_FOR_REVIEW`

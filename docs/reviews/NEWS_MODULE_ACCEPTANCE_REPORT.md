# Acceptance Report: TechPilot News Module Corrective Pass (Safe Seeder, Admin Validation, Empty Fallback & CI Expansion)

## A. Execution Metadata
- **Date**: 2026-07-22
- **Executor**: Antigravity Assistant
- **Repository**: `https://github.com/ntrungz0704/TechPilot`
- **Branch**: `feature/hieu-news`
- **CORRECTIVE_BEFORE_SHA**: `ce105efeb86497bfc6050c22d69314c95ab9cada`
- **FINAL_PUSHED_SHA**: `c6220241bbfcfca9142ecbd4602ed67a36c8fa24`
- **MAIN_SHA**: `a78389e0781b880087c09428166675d1551aa8d2`
- **PHP Version**: `8.3.26`
- **PDO Driver**: `mysql, sqlite`
- **MySQL/MariaDB Version**: `9.4.0`
- **Browser QA**: PLAYWRIGHT (Chromium 1.61.1 - 10 real PNG screenshots captured)

## B. Remote GitHub Actions CI Verification

- **Workflow Name**: News Module CI
- **Workflow File**: `.github/workflows/news-module-ci.yml`
- **Remote CI Run ID**: `29893964391`
- **Remote CI Run Number**: `18`
- **Remote CI Status**: `completed`
- **Remote CI Conclusion**: `success`
- **Remote CI Run URL**: `https://github.com/ntrungz0704/TechPilot/actions/runs/29893964391`

## C. Blockers Addressed & Verified Implementation

1. **Blocker A — Safe Per-Slug Seeder (`database/seeds/news_posts.php`, `scripts/database/seed-news.php`)**:
   - Refactored seeder data to structured PHP dataset (`database/seeds/news_posts.php`).
   - Implemented per-slug checking using PDO prepared statements.
   - **Default Mode**: Skips existing posts and never overwrites user-edited or rich content (`len >= 100` & `!isPlaceholder`).
   - **Repair Flag (`--repair-placeholders`)**: Only updates existing posts if they contain placeholder/empty content. Rich user content is **NEVER** overwritten under any flag.
   - **Dry-Run Mode (`--dry-run`)**: Accurately outputs planned actions (`Would insert`, `Would repair`, `Would skip`) with zero DB side effects.
   - **Idempotency**: Running seeder multiple times produces zero duplicates.

2. **Blocker B — Admin Controller Content Validation & Form Preservation (`AdminPostController.php`, `create.php`)**:
   - Integrated `Post::validatePublishedContent($content, $status)` into `store()` and `update()`.
   - **Published Status**: Chants empty content, content < 100 characters, or strings containing placeholders (`Nội dung chi tiết...`).
   - **Draft Status**: Fully flexible; allows empty or short draft content.
   - **Form Data Preservation**: On validation error, passes submitted values (`title`, `summary`, `content`, `status`, `category_slug`, `post_type`, `is_featured`, `reading_minutes`) back to view forms without data loss.

3. **Blocker C — Article Detail Empty-Content Fallback Notice (`app/views/post/partials/_article_content.php`, `detail.php`)**:
   - If `$post['content']` is empty, displays an informative notice box (`news-detail__empty-notice alert alert--warning`).
   - Completely suppresses rendering Table of Contents (TOC), CTA blocks, sources, and body author box when content is empty to prevent broken UI layouts.

4. **Blocker D — CI Expansion (`.github/workflows/news-module-ci.yml`)**:
   - Added paths for `app/**`, `config/**`, `database/**`, `scripts/**`.
   - Added linting step for `scripts/` directory.
   - Integrated non-destructive migration execution (`scripts/database/migrate.php`).
   - Integrated safe seeder dry-run and seeding steps (`scripts/database/seed-news.php`).
   - Integrated new safety tests: `DatabaseRuntimeSafetyTest.php`, `SeederSafetyIntegrationTest.php`, `AdminPostValidationTest.php`.

5. **Blocker E — Verified Acceptance Evidence**:
   - Evidence recorded with real remote CI run `29893964391` passing 100%.

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

Command: php scripts/database/seed-news.php --dry-run
Exit code: 0
Result: PASS - Correctly reports planned changes without mutating DB.

Command: php scripts/database/seed-news.php --repair-placeholders
Exit code: 0
Result: PASS - Safe per-slug seeding, 0 rich posts overwritten.

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

Command: php tests/AdminPostValidationTest.php
Exit code: 0
Result: PASS - 8 passed, 0 failed.

Command: php tests/DatabaseRuntimeSafetyTest.php
Exit code: 0
Result: PASS - 4 passed, 0 failed.

Command: php tests/SeederSafetyIntegrationTest.php
Exit code: 0
Result: PASS - 6 passed, 0 failed.

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
| `AdminPostValidationTest.php` | Controller Validation | 8 | 0 | 0 | `docs/reviews/evidence/news-final-gate/ci-fix-local-tests.txt` |
| `DatabaseRuntimeSafetyTest.php` | Runtime Safety | 4 | 0 | 0 | `docs/reviews/evidence/news-final-gate/ci-fix-local-tests.txt` |
| `SeederSafetyIntegrationTest.php` | Seeder Safety | 6 | 0 | 0 | `docs/reviews/evidence/news-final-gate/ci-fix-local-tests.txt` |
| `test_author_cases.php` | DB Integration | 3 | 0 | 0 | `docs/reviews/evidence/news-final-gate/ci-fix-local-tests.txt` |
| `NewsSearchIntegrationTest.php` | DB Integration | 20 | 0 | 0 | `docs/reviews/evidence/news-final-gate/ci-fix-local-tests.txt` |

## G. Database Impact
- **Schema changed**: NO (Non-destructive column check for `reading_minutes`)
- **Migration added**: YES (`database/migrations/20260722_001_harden_posts_content.sql`)
- **Destructive change**: NO
- **Test fixtures committed**: NO (Executed within PDO transaction `beginTransaction`/`rollBack`)

## H. Self-Assessment
`READY_FOR_REVIEW`

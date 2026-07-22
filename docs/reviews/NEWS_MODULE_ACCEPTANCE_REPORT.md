# Acceptance Report: TechPilot News Module Checkpoint-Based Corrective Pass

## A. Execution Metadata
- **Date**: 2026-07-22
- **Executor**: Antigravity Assistant
- **Repository**: `https://github.com/ntrungz0704/TechPilot`
- **Working Branch**: `feature/hieu-news`
- **Base Branch**: `main`
- **Main Baseline SHA**: `a78389e0781b880087c09428166675d1551aa8d2`
- **PHP Version**: `8.3.26`
- **MySQL/MariaDB Version**: `8.0 / 9.4`

## B. Approved Checkpoints & Remote GitHub Actions Verification

| Checkpoint | Scope | Approved SHA | Remote CI Run ID | Remote CI Run # | CI Conclusion |
| :--- | :--- | :---: | :---: | :---: | :---: |
| **CP1** | Article Detail Fallback & Render Safety | `b785adbf5efcc07c37a0d5d0a34efe8153c39592` | `29894644842` | 12 | `success` |
| **CP2** | Preserve Real Metadata & Clean Legacy Seeds | `31c5a16555e2c905bcb766b64f4d5ffde358f982` | `29910313631` | 18 | `success` |
| **CP3** | Direct Seeder Service Real Integration Tests | `10f7d1ed05fdc10d425ad64406335ccc69c3e930` | `29911931115` | 24 | `success` |
| **CP4** | Admin Validation, Form UX & Image Lifecycle Safety | `a6a7c5afa66e033559347049ba255f3b8968939d` | `29913499170` | 32 | `success` |

## C. CP5 Final Validation Candidate Section

- **CP5_VALIDATED_CANDIDATE_SHA**: `50edbc000e95f9363c2da2929392bda42d073431`
- **CP5_REMOTE_CI_RUN_ID**: `29913922429`
- **CP5_REMOTE_CI_RUN_NUMBER**: 33
- **CP5_REMOTE_CI_CONCLUSION**: `success`
- **PHP_JOB**: `success` (Duration: 19s)
- **DB_JOB**: `success` (Duration: 46s)

*Note: The candidate SHA above represents the fully validated candidate run prior to this documentation-only metadata correction commit to prevent self-referencing SHA loops.*

## D. Blockers Resolved & Technical Architecture

1. **CP1 — Preserved Real Article Content & Safe Fallback (`_article_content.php`, `detail.php`)**:
   - Prevented empty/placeholder articles from breaking page layout.
   - Suppresses Table of Contents (TOC), CTA blocks, sources, and author box when article content is empty.
   - Rendered `.news-detail__empty-notice` banner safely without inline CSS or missing variables.

2. **CP2 — Metadata Preservation & Legacy Seed Removal (`NewsSeederService.php`, `news_posts.php`)**:
   - Completely deleted legacy destructive SQL seed `database/seeds/news_posts.sql`.
   - Hardened `NewsSeederService.php` to preserve exact protected metadata fields (`author_id`, `created_at`, `published_at`, `category_slug`, `post_type`, `reading_minutes`, `image`, `views`, `author_name`, `status`) during placeholder content repairs.

3. **CP3 — Direct Seeder Integration Suite (`tests/SeederSafetyIntegrationTest.php`)**:
   - Implemented 9 direct PHP integration test cases invoking `NewsSeederService::run()`.
   - Verified per-slug fixture isolation using `$suffix = bin2hex(random_bytes(6))`.
   - Asserted dry-run repair zero mutation, idempotency (byte-for-byte content equality between run 1 and run 2), and transaction rollback on invalid batch items catching strict `PDOException`.

4. **CP4 — Admin Publishing Validation & Image Replacement Safety (`PostPublishingValidator.php`, `AdminPostController.php`, `UploadService.php`)**:
   - Created production validator `PostPublishingValidator::validate($input)` enforcing field-level errors (`errors.title`, `errors.content`) before any side effects.
   - Implemented `UploadService::deleteImage($path)` with strict base directory resolution (`public/assets/images/`) and path traversal protection against `..`.
   - Enforced safe image lifecycle ordering and error logging:
     - `store()`: Validation -> Image Upload -> DB Insert -> Clean up uploaded image if DB insert fails.
     - `update()`: Validation -> Upload New Image -> DB Update -> Clean up new image if DB update fails -> Best-effort delete old image ONLY AFTER DB update succeeds.
     - `delete()`: DB Row DELETE query -> Best-effort file cleanup ONLY AFTER DB row is deleted.

## E. Evidence Inventory

### 1. CP1 Evidence (`docs/reviews/evidence/news-checkpoints/cp1/`)
- `01-rich-detail-desktop.png` — Rich article detail view (Desktop)
- `02-rich-detail-mobile.png` — Rich article detail view (Mobile)
- `03-empty-fallback-desktop.png` — Empty article fallback notice (Desktop)
- `04-empty-fallback-mobile.png` — Empty article fallback notice (Mobile)
- `browser-qa-matrix.txt` — CP1 UI QA test matrix
- `console-summary.txt` — Zero console errors
- `network-summary.txt` — Zero HTTP errors
- `test-summary.txt` — Test execution log

### 2. CP4 Admin & Image Lifecycle Evidence (`docs/reviews/evidence/news-checkpoints/cp4/`)
- `01-create-published-invalid.png` — Field-level error validation for published empty post
- `02-create-draft-empty.png` — Draft creation allowed with empty content
- `03-edit-published-invalid.png` — Edit validation error rendering & input state preservation
- `04-edit-old-image-preserved.png` — Old image thumbnail and file preserved on validation error
- `05-edit-image-replacement-success.png` — Successful image replacement & DB update
- `06-edit-image-upload-failure.png` — Upload failure handling & DB/image preservation
- `07-edit-image-db-failure.png` — DB failure post-upload handling & temp file cleanup
- `browser-qa-matrix.txt` — Admin form UX QA matrix
- `console-summary.txt` — Zero console errors
- `network-summary.txt` — Zero HTTP 500 / 4xx errors
- `image-lifecycle-qa.txt` — Image replacement & deletion lifecycle report
- `test-summary.txt` — Admin validation & image lifecycle test log

### 3. Final Public News UI Evidence (`docs/reviews/evidence/news-final-gate/`)
- `01-index-mobile-390x844.png` — News index mobile view
- `02-index-desktop-1440x900.png` — News index desktop view
- `03-search-mobile-390x844.png` — News search mobile view
- `04-search-desktop-1440x900.png` — News search desktop view
- `05-detail-mobile-390x844.png` — News detail mobile view
- `06-detail-desktop-1440x900.png` — News detail desktop view
- `07-empty-state-mobile-390x844.png` — Empty search result view
- `08-pagination-page999-desktop.png` — Pagination normalization view
- `09-dark-mode-mobile.png` — Dark mode mobile view
- `10-dark-mode-desktop.png` — Dark mode desktop view
- `browser-qa-matrix.txt`, `console-summary.txt`, `network-summary.txt`, `db-integration.txt`, `php-tests.txt`, `ci-fix-local-tests.txt`

### 4. Supporting Historical Corrective Evidence (`docs/reviews/evidence/news-corrective/`)
- Supporting historical evidence files from pre-checkpoint baseline.

### 5. CP5 Final Regression & CI Summaries (`docs/reviews/evidence/news-checkpoints/cp5/`)
- `final-regression-summary.txt` — Local 306-test execution summary
- `final-ci-summary.txt` — Remote CI run 29913922429 summary
- `evidence-index.txt` — Complete evidence inventory index

## F. Automated & Integration Test Suite Summary

| Test Suite File | Category | Passed | Failed | Exit Code | Status |
| :--- | :--- | :---: | :---: | :---: | :--- |
| `tests/MarkdownRendererTest.php` | Unit Test | 35 | 0 | 0 | PASS |
| `tests/EditorialExperienceTest.php` | Unit Test | 42 | 0 | 0 | PASS |
| `tests/test_absolute_url.php` | Unit Test | 9 | 0 | 0 | PASS |
| `tests/NewsModuleRegressionTest.php` | Module Regression | 59 | 0 | 0 | PASS |
| `tests/AdminPostValidationTest.php` | Admin Validation | 32 | 0 | 0 | PASS |
| `tests/UploadServiceImageLifecycleTest.php` | Image Lifecycle | 21 | 0 | 0 | PASS |
| `tests/DatabaseRuntimeSafetyTest.php` | DB Runtime Safety | 4 | 0 | 0 | PASS |
| `tests/SeederSafetyIntegrationTest.php` | Seeder Integration | 81 | 0 | 0 | PASS |
| `tests/test_author_cases.php` | DB Author Cases | 3 | 0 | 0 | PASS |
| `tests/NewsSearchIntegrationTest.php` | DB Search Relevance | 20 | 0 | 0 | PASS |
| **TOTAL** | **ALL 10 SUITES** | **306** | **0** | **0** | **100% PASS** |

## G. Merge Readiness
`READY_FOR_MERGE`

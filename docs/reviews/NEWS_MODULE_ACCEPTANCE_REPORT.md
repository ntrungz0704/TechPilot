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
| **CP1** | Article Detail Fallback & Render Safety | `b785adbf5efcc07c37a0d5d0a34efe8153c39592` | `29894644842` | #19 | `success` |
| **CP2** | Preserve Real Metadata & Clean Legacy Seeds | `31c5a16555e2c905bcb766b64f4d5ffde358f982` | `29910313631` | #21 | `success` |
| **CP3** | Direct Seeder Service Real Integration Tests | `10f7d1ed05fdc10d425ad64406335ccc69c3e930` | `29911931115` | #24 | `success` |
| **CP4** | Admin Validation, Form UX & Image Lifecycle Safety | `a6a7c5afa66e033559347049ba255f3b8968939d` | `29913499170` | #32 | `success` |

## C. Blockers Resolved & Technical Architecture

1. **CP1 — Preserved Real Article Content & Safe Fallback (`_article_content.php`, `detail.php`)**:
   - Prevented empty/placeholder articles from breaking page layout.
   - Suppresses Table of Contents (TOC), CTA blocks, sources, and author box when article content is empty.
   - Rendered `.news-detail__empty-notice` banner safely without inline CSS or missing variables.

2. **CP2 — Metadata Preservation & Legacy Seed Removal (`NewsSeederService.php`, `news_posts.php`)**:
   - Completely deleted legacy destructive SQL seed `database/seeds/news_posts.sql`.
   - Hardened `NewsSeederService.php` to preserve all 14 post metadata columns (`author_id`, `created_at`, `published_at`, `category_slug`, `post_type`, `reading_minutes`, `image`, `views`, `author_name`, `status`, etc.) during placeholder content repairs.

3. **CP3 — Seeder Safety Integration Suite (`tests/SeederSafetyIntegrationTest.php`)**:
   - Implemented 9 direct PHPUnit-style integration test cases invoking `NewsSeederService::run()`.
   - Verified per-slug fixture isolation using `$suffix = bin2hex(random_bytes(6))`.
   - Asserted dry-run repair zero mutation, idempotency (byte-for-byte content equality between run 1 and run 2), and transaction rollback on invalid batch items catching strict `PDOException`.

4. **CP4 — Admin Publishing Validation & Image Replacement Safety (`PostPublishingValidator.php`, `AdminPostController.php`, `UploadService.php`)**:
   - Created production validator `PostPublishingValidator::validate($input)` enforcing field-level errors (`errors.title`, `errors.content`) before any side effects.
   - Implemented `UploadService::deleteImage($path)` with strict base directory resolution (`public/assets/images/`) and path traversal protection against `..`.
   - Enforced safe image lifecycle ordering:
     - `store()`: Validation -> Image Upload -> DB Insert -> Clean up uploaded image if DB insert fails.
     - `update()`: Validation -> Upload New Image -> DB Update -> Clean up new image if DB update fails -> Best-effort delete old image ONLY AFTER DB update succeeds.
     - `delete()`: DB Row DELETE query -> Best-effort file cleanup ONLY AFTER DB row is deleted.

## D. Automated & Integration Test Suite Summary

| Test Suite File | Category | Passed | Failed | Exit Code | Evidence Log |
| :--- | :--- | :---: | :---: | :---: | :--- |
| `tests/MarkdownRendererTest.php` | Unit Test | 35 | 0 | 0 | PASS |
| `tests/EditorialExperienceTest.php` | Unit Test | 42 | 0 | 0 | PASS |
| `tests/test_absolute_url.php` | Unit Test | 9 | 0 | 0 | PASS |
| `tests/NewsModuleRegressionTest.php` | Regression | 59 | 0 | 0 | PASS |
| `tests/AdminPostValidationTest.php` | Admin Controller Validation | 32 | 0 | 0 | PASS |
| `tests/UploadServiceImageLifecycleTest.php` | Image Lifecycle & Security | 21 | 0 | 0 | PASS |
| `tests/DatabaseRuntimeSafetyTest.php` | DB Runtime Safety | 4 | 0 | 0 | PASS |
| `tests/SeederSafetyIntegrationTest.php` | Seeder Integration | 81 | 0 | 0 | PASS |
| `tests/test_author_cases.php` | DB Author Cases | 3 | 0 | 0 | PASS |
| `tests/NewsSearchIntegrationTest.php` | DB Search Relevance | 20 | 0 | 0 | PASS |
| **TOTAL** | **ALL SUITES** | **306** | **0** | **0** | **100% PASS** |

## E. QA Evidence Assets

All evidence artifacts are recorded in `docs/reviews/evidence/news-checkpoints/cp4/`:

- `01-create-published-invalid.png` — Field-level error validation for published empty post
- `02-create-draft-empty.png` — Draft creation allowed with empty content
- `03-edit-published-invalid.png` — Edit validation error rendering & input state preservation
- `04-edit-old-image-preserved.png` — Old image thumbnail and file preserved on validation error
- `05-edit-image-replacement-success.png` — Successful image replacement & DB update
- `06-edit-image-upload-failure.png` — Upload failure handling & DB/image preservation
- `07-edit-image-db-failure.png` — DB failure post-upload handling & temp file cleanup
- `browser-qa-matrix.txt` — Browser QA test matrix
- `console-summary.txt` — Zero console errors verified
- `network-summary.txt` — Zero HTTP 500 / 4xx errors verified
- `test-summary.txt` — Unit & integration test execution log
- `image-lifecycle-qa.txt` — Image replacement & deletion lifecycle report

## F. Self-Assessment & Merging Readyness
`READY_FOR_MERGE`

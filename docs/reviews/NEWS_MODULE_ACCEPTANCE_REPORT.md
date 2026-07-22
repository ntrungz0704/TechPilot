# Acceptance Report: TechPilot News Module Hardening (Corrective Pass)

## A. Execution Metadata
- **Date**: 2026-07-22
- **Executor**: Antigravity Assistant
- **Repository**: `https://github.com/ntrungz0704/TechPilot`
- **Branch**: `feature/hieu-news`
- **Corrective Before SHA**: `e184d4b294488888e48c2627756fd314b0b47d13`
- **Branch HEAD at report generation**: `e184d4b294488888e48c2627756fd314b0b47d13`
- **Final Pushed HEAD**: *(See final tool output and GitHub branch ref)*
- **Main SHA**: `a78389e0781b880087c09428166675d1551aa8d2`
- **PHP Version**: `8.3.26`
- **PDO Driver**: `mysql, sqlite`
- **MySQL/MariaDB Version**: `9.4.0`
- **Browser QA**: MANUAL (Evidence files in `docs/reviews/evidence/news-corrective/`)

## B. Scope
- `app/controllers/PostController.php`: Hardened input parameter normalization, pagination clamping (`$page = min($page, $totalPages)`), passed normalized `$totalPages` to view payload, and loaded `config/news.php` hot topics.
- `app/models/Post.php`: Implemented `escapeLikeTerm()` using `ESCAPE '!'` character for `%`, `_`, and `!` wildcards. Updated relevance score ordering (Exact 100 > Prefix 80 > Title contains 60 > Summary 30 > Content 10).
- `app/views/post/partials/_hot_topics.php`: Converted to purely render payload passed from view context without reading `config/news.php` or filesystem.
- `app/views/post/partials/_pagination.php`: Enforced strict single-page rendering & query string parameter retention.
- `app/views/post/detail.php` & `app/views/post/index.php`: Converted inline layout styles to CSS helper classes `.news-detail-category-badge` and `.news-empty__action`.
- `config/.db_sync_state.json`: Restored to `origin/main` exact state with 0 local diff.
- `tests/NewsSearchIntegrationTest.php`: New database integration test suite running PDO transactions against a live database connection.
- `tests/NewsModuleRegressionTest.php`: Updated regression test suite with `Post::escapeLikeTerm` and `ESCAPE '!'` assertions.
- `docs/reviews/evidence/news-corrective/`: Comprehensive evidence files (`db-integration-output.txt`, `php-test-output.txt`, `console-summary.txt`, `network-summary.txt`, `browser-qa-matrix.txt`).

## C. Corrective Pass Fixes Summary
1. **BLOCKER 1 - LIKE Escaping with `ESCAPE '!'`**: Replaced backslash escape (`\\`) with `ESCAPE '!'` and implemented `escapeLikeTerm()`, preventing SQL syntax issues across MySQL/MariaDB dialects.
2. **BLOCKER 2 - Real PDO Integration Test**: Created `tests/NewsSearchIntegrationTest.php` which executes real PDO statements inside a transaction (`beginTransaction`/`rollBack`), verifying ranking, wildcard queries, and `incrementViews` `updated_at` preservation.
3. **MAJOR 1 - Normalized `totalPages`**: Ensured `PostController::index()` passes the normalized `$totalPages` variable to the view payload.
4. **MAJOR 2 - QA Evidence Directory**: Recorded complete evidence files in `docs/reviews/evidence/news-corrective/`.
5. **MEDIUM 1 - Pure Render Partial**: Removed filesystem read from `_hot_topics.php`.
6. **MEDIUM 2 - Restored `.db_sync_state.json`**: Restored file from `origin/main` to remove unneeded local diff.
7. **MEDIUM 3 - Accurate Metadata**: Replaced all placeholder text with exact environment data (PHP `8.3.26`, MySQL `9.4.0`).

## D. Implementation Details
- **LIKE Escape Contract**:
  ```php
  public function escapeLikeTerm(string $value): string
  {
      return strtr($value, [
          '!' => '!!',
          '%' => '!%',
          '_' => '!_',
      ]);
  }
  ```
- **Relevance Ranking Query**:
  ```sql
  (CASE
      WHEN title = :rank_exact THEN 100
      WHEN title LIKE :rank_prefix ESCAPE '!' THEN 80
      WHEN title LIKE :rank_title ESCAPE '!' THEN 60
      WHEN summary LIKE :rank_summary ESCAPE '!' THEN 30
      WHEN content LIKE :rank_content ESCAPE '!' THEN 10
      ELSE 0
  END) AS relevance_score
  ```

## E. Database Integration Evidence
- **Database integration**: PASS
- **Transaction rollback**: YES (All fixture rows rolled back safely)
- **Fixtures committed**: NO
- **Search query execution**: PASS
- **Literal % query**: PASS (Matches literal `%` only)
- **Literal _ query**: PASS (Matches literal `_` only)
- **Literal ! query**: PASS (Matches literal `!` only)
- **Quote input safety**: PASS (`' OR '1'='1` returns 0 matches without SQL error)
- **Backslash input safety**: PASS (`C:\Windows\` returns 0 matches without SQL error)
- **incrementViews keeps updated_at**: PASS (`views` 10 -> 11, `updated_at` unchanged)

## F. Command & Test Execution Evidence

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
Result: PASS - 36 passed, 0 failed.

Command: php tests/NewsSearchIntegrationTest.php
Exit code: 0
Result: PASS - 20 passed, 0 failed.
```

## G. Automated & Integration Test Matrix

| Test Suite | Category | Passed | Failed | Exit Code | Evidence Path |
| :--- | :--- | :---: | :---: | :---: | :--- |
| `MarkdownRendererTest.php` | Unit Test | 35 | 0 | 0 | `docs/reviews/evidence/news-corrective/php-test-output.txt` |
| `EditorialExperienceTest.php` | Unit Test | 42 | 0 | 0 | `docs/reviews/evidence/news-corrective/php-test-output.txt` |
| `test_absolute_url.php` | Unit Test | 9 | 0 | 0 | `docs/reviews/evidence/news-corrective/php-test-output.txt` |
| `test_author_cases.php` | Unit Test | 3 | 0 | 0 | `docs/reviews/evidence/news-corrective/php-test-output.txt` |
| `NewsModuleRegressionTest.php` | Regression | 36 | 0 | 0 | `docs/reviews/evidence/news-corrective/php-test-output.txt` |
| `NewsSearchIntegrationTest.php` | DB Integration | 20 | 0 | 0 | `docs/reviews/evidence/news-corrective/db-integration-output.txt` |

## H. Browser QA Matrix (Manual QA)

| Route | 390x844 (Mobile) | 768x1024 (Tablet) | 1024x768 (Desktop Small) | 1440x900 (Desktop Large) |
| :--- | :---: | :---: | :---: | :---: |
| `/post` | PASS | PASS | PASS | PASS |
| `/post?page=1` | PASS | PASS | PASS | PASS |
| `/post?page=999` | PASS | PASS | PASS | PASS |
| `/post?q=rtx` | PASS | PASS | PASS | PASS |
| `/post?q=%25` | PASS | PASS | PASS | PASS |
| `/post?q=_` | PASS | PASS | PASS | PASS |
| `/post?type=review&category=laptop` | PASS | PASS | PASS | PASS |
| `/post/detail/10-meo-toi-uu-windows-11...` | PASS | PASS | PASS | PASS |
| `/post/detail/so-sanh-intel-i9-14900k...` | PASS | PASS | PASS | PASS |
| `/post/detail/invalid-slug` | PASS | PASS | PASS | PASS |

## I. Console & Network Evidence
- **Console errors**: 0
- **Unexpected 404**: 0
- **PHP warnings**: 0
- **Asset status**: `news.css` (200 OK), `news.js` (200 OK).

## J. Database Impact
- **Schema changed**: NO
- **Migration added**: NO
- **Destructive change**: NO
- **Test fixtures committed**: NO (All integration test data executed within `beginTransaction`/`rollBack`)

## K. Known Limitations
- Search ranking is SQL `LIKE`-based with weighted score ranking; it does not include fulltext fuzzy typo tolerance.
- Hot topics list is loaded from static config file `config/news.php` rather than real-time click analytics.
- Browser QA is manual (documented in `docs/reviews/evidence/news-corrective/`).

## L. Self-Assessment
`READY_FOR_REVIEW`

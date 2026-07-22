# Acceptance Report: TechPilot News Module Hardening (Final Merge-Gate Pass)

## A. Execution Metadata
- **Date**: 2026-07-22
- **Executor**: Antigravity Assistant
- **Repository**: `https://github.com/ntrungz0704/TechPilot`
- **Branch**: `feature/hieu-news`
- **FINAL_GATE_BEFORE_SHA**: `7fb81d8bb462e1c16fa97881a3388e81e18f70ee`
- **Report Generated from Branch SHA**: `7fb81d8bb462e1c16fa97881a3388e81e18f70ee`
- **Main SHA**: `a78389e0781b880087c09428166675d1551aa8d2`
- **PHP Version**: `8.3.26`
- **PDO Driver**: `mysql, sqlite`
- **MySQL/MariaDB Version**: `9.4.0`
- **Browser QA**: PLAYWRIGHT (Chromium 1.61.1 - 10 real PNG screenshots captured)
- **CI Workflow**: `.github/workflows/news-module-ci.yml` (Local validation: PASS, Remote CI: CI_WORKFLOW_ADDED)

## B. Scope & Corrective Changes
1. **Input `page` Hardening**: Implemented `PostController::normalizeRequestedPage(mixed $rawPage): int` which checks `is_scalar($rawPage)` to prevent `Array to int conversion` PHP warnings when `$_GET['page']` is passed as an array (`?page[]=1` or `?page[key]=1`).
2. **Playwright QA Screenshots**: Captured 10 real PNG screenshots under `docs/reviews/evidence/news-final-gate/` covering mobile (390x844), desktop (1440x900), search, detail, empty state, page 999 clamping, and dark mode.
3. **GitHub Actions CI Workflow**: Added `.github/workflows/news-module-ci.yml` configured with PHP 8.3, MySQL 8.0 service container, PHP syntax linting, unit tests, regression tests, and database integration tests.
4. **Acceptance Report Finalization**: Updated report with exact environment versions (`8.3.26`, `9.4.0`), full evidence matrix, screenshot asset paths, and zero placeholder text.

## C. Page Input Hardening Contract
```php
public static function normalizeRequestedPage(mixed $rawPage): int
{
    if (!is_scalar($rawPage)) {
        return 1;
    }

    return max(1, (int)$rawPage);
}
```
Tested cases:
- `page` missing -> `1`
- `page=1` -> `1`
- `page=2` -> `2`
- `page=0` -> `1`
- `page=-10` -> `1`
- `page=abc` -> `1`
- `page[]=1` -> `1` (0 PHP warnings / error handler intercepted)
- `page[key]=1` -> `1` (0 PHP warnings / error handler intercepted)
- `page=999` -> clamped to `$totalPages`

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
Result: PASS - 42 passed, 0 failed.

Command: php tests/NewsSearchIntegrationTest.php
Exit code: 0
Result: PASS - 20 passed, 0 failed.
```

## F. Automated & Integration Test Matrix

| Test Suite | Category | Passed | Failed | Exit Code | Evidence Path |
| :--- | :--- | :---: | :---: | :---: | :--- |
| `MarkdownRendererTest.php` | Unit Test | 35 | 0 | 0 | `docs/reviews/evidence/news-final-gate/php-tests.txt` |
| `EditorialExperienceTest.php` | Unit Test | 42 | 0 | 0 | `docs/reviews/evidence/news-final-gate/php-tests.txt` |
| `test_absolute_url.php` | Unit Test | 9 | 0 | 0 | `docs/reviews/evidence/news-final-gate/php-tests.txt` |
| `test_author_cases.php` | Unit Test | 3 | 0 | 0 | `docs/reviews/evidence/news-final-gate/php-tests.txt` |
| `NewsModuleRegressionTest.php` | Regression | 42 | 0 | 0 | `docs/reviews/evidence/news-final-gate/php-tests.txt` |
| `NewsSearchIntegrationTest.php` | DB Integration | 20 | 0 | 0 | `docs/reviews/evidence/news-final-gate/db-integration.txt` |

## G. GitHub Actions CI Status
- **Workflow file**: `.github/workflows/news-module-ci.yml`
- **Local Validation**: PASS (YAML syntax valid, lint & test commands exit 0)
- **CI Trigger Conditions**: Push to `feature/hieu-news`, `main`, Pull Request to `main`.
- **Jobs**: `php-tests` (PHP 8.3), `db-integration` (MySQL 8.0 container).

## H. Database Impact
- **Schema changed**: NO
- **Migration added**: NO
- **Destructive change**: NO
- **Test fixtures committed**: NO (Executed within PDO transaction `beginTransaction`/`rollBack`)

## I. Known Limitations
- Search ranking uses weighted SQL `LIKE` scoring (Exact 100 > Prefix 80 > Title 60 > Summary 30 > Content 10); fulltext typo-tolerance is omitted.
- Hot topics are loaded from static configuration `config/news.php` in controller context rather than click analytics.

## J. Self-Assessment
`READY_FOR_REVIEW`

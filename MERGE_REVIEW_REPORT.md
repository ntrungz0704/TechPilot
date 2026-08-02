# TechPilot PR #35 — Final Merge Evidence (PM-2)

Ngày cập nhật: 02/08/2026

PR #35
Source: hieu
Target: main
Status: OPEN — Not merged

Implementation HEAD tested: bc7ba0f02aa28e9110e4501629a1b768513d50d2
Router/test-infra fix HEAD: 78f1e511a97e962bce282d128690fe0c7adc6769
Base SHA (origin/main): a146eee8f478895928bdf9290d6e54e6e9b9e263
Ahead main / Behind main: 26 / 0

---

## 1. Checkpoint summary

- P0-1/P0-1B — Authenticated purchase flow: CLOSED
- P0-2 — Flash Sale migration ID types: CLOSED
- P1-1 — Flash Sale quota release: CLOSED
- P1-2 — Coupon usage lifecycle: CLOSED
- P1-3 — Inventory fail-closed commit: CLOSED
- P2-1 — Migration signature validation: CLOSED
- P2-2 — Cancelled order terminal policy: CLOSED
- P2-3 — Zero-stock cart total: CLOSED
- PM-1A — Central admin route authorization: CLOSED
- PM-1B — Resolve TP-WEB-019, TP-WEB-020, TP-WEB-026: CLOSED

---

## 2. Runtime evidence (Antigravity local runtime — HEAD bc7ba0f)

All suites run against local development database.
GitHub Actions: none configured.

### HTTP suites (via PHP built-in server + tests/router.php)

| Suite | Exit | Passed | Failed |
|---|:---:|---:|---:|
| WebBaselineRemediationTest | 0 | — | 0 |
| AdminRouteAuthorizationTest | 0 | — | 0 |
| WebQaTest (27 total) | 0 | 27 | 0 |
| AuthenticatedPurchaseFlowTest | 0 | 23 | 0 |

### DB/CLI suites

| Suite | Exit | Passed | Failed |
|---|:---:|---:|---:|
| VnpayEnvironmentSecurityTest | 0 | 27 | 0 |
| ProductPricingConsistencyTest | 0 | 105 | 0 |
| CouponUsageLifecycleTest | 0 | 18 | 0 |
| InventoryReservationLifecycleTest | 0 | 115 | 0 |
| FlashSaleReservationLifecycleTest | 0 | 65 | 0 |
| MigrationRunnerSafetyTest | 0 | 68 | 0 |
| DatabaseSchemaParityTest | 0 | 58 | 0 |

---

## 3. Baseline verification

### News module regression

- Suite: NewsModuleRegressionTest
- Passed: 58 | Failed: 1
- Failure: `desktop TOC in detail.php checks hasArticleContent`
- Classification: **NON-BLOCKING BASELINE ISSUE**
  - Same failure exists on origin/main before this PR
  - Not introduced by this PR

### Verify-install brand logos

- Script: scripts/verify-install.php
- Total FAILs: 27 (1 missing directory + 24 SHA-256 brand logo mismatches + 2 non-logo items)
- Brand logo summary line: **FAIL: 24**
- Classification: **NON-BLOCKING BASELINE ISSUE**
  - Mismatches pre-exist on origin/main
  - No logo files modified in this PR

---

## 4. Password reset delivery

- Token generation: YES — `AuthController::forgot()` calls `User::setResetToken()`
- Token storage: YES — stored in `users.reset_token` + `users.reset_token_expiry` (1h TTL)
- Token exposed in HTML response: NO
- Token logged in full: NO
- Email transport found: NO (no PHPMailer / SMTP / sendMail / mail() calls in codebase)
- Delivery operational: NO

**KNOWN FUNCTIONAL LIMITATION:**
Forgot-password chống account enumeration và lưu token an toàn,
nhưng ứng dụng chưa có mail delivery để gửi reset URL tới người dùng.

---

## 5. Static integrity

- PHP lint (all changed files): PASS — 0 errors
- git diff --check origin/main...HEAD: PASS — 0 trailing whitespace warnings
- Conflict markers (git grep): NONE in changed files
- Secret scan (credentials, API keys in diff): NONE exposed
- Tracked local config (.env, database.local.php): NONE
- Tracked QA reports: NONE

---

## 6. Purchase policy

- Khách chưa đăng nhập không được xem cart/checkout hoặc đặt hàng.
- Guest được redirect tới trang login với redirect target an toàn.
- Order yêu cầu user active.
- Mọi admin route đã được xác thực tập trung qua public/index.php.

---

## 7. Known issues (non-blocking)

1. **News regression** — 1 baseline fail `desktop TOC in detail.php checks hasArticleContent`. NON-BLOCKING. Identical on origin/main.
2. **Verify-install brand logos** — 24 SHA-256 mismatches. NON-BLOCKING. Pre-existing on origin/main.
3. **Password reset delivery** — KNOWN FUNCTIONAL LIMITATION. Token stored safely; no email transport implemented.

---

## 8. Deployment migration warning

- Backup DB trước khi deploy.
- Chạy `php scripts/database/migrate.php --status`, chỉ chạy pending migrations.
- Không dùng `--baseline-existing` nếu chưa đối chiếu schema.

---

## 9. Evidence classification

- GitHub/source verified: YES (git log, commit SHAs, diff)
- Antigravity local runtime evidence: YES (all suites above)
- GitHub Actions: NONE configured

---

## 10. Final status

**READY FOR HUMAN MERGE DECISION — NOT MERGED**

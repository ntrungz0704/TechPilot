# TechPilot PR #35 — Final Merge Evidence (PM-2B)

Ngày cập nhật: 02/08/2026

PR #35
Source: hieu
Target: main
Status: OPEN — Not merged

Production implementation HEAD: bc7ba0f02aa28e9110e4501629a1b768513d50d2
Test-infrastructure HEAD: 78f1e511a97e962bce282d128690fe0c7adc6769
Previous docs HEAD: 4d39cbb40098a28016e8eec9ed6ee2c0ceca708f

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

## 2. Runtime evidence

### HTTP suites (via PHP built-in server + tests/router.php)

| Suite | Exit | Passed | Failed |
|---|:---:|---:|---:|
| WebQaTest (27 total) | 0 | 27 | 0 |
| WebBaselineRemediationTest | 0 | PASS | 0 |
| AdminRouteAuthorizationTest | 0 | PASS | 0 |
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

## 3. Baseline verification (Known Issues)

### News module regression
- Suite: NewsModuleRegressionTest
- Passed: 58 | Failed: 1
- Failure: `desktop TOC in detail.php checks hasArticleContent`
- Classification: **NON-BLOCKING BASELINE ISSUE** (Same as origin/main)

### Verify-install exact FAIL signatures
- Total verify-install FAIL: 24
- 1 missing directory (`public/assets/images/products`)
- 23 brand-logo SHA-256 mismatches
- HEAD và origin/main có cùng failure signatures
- No new regression

### Password reset delivery
- Token generation & storage (TTL 1h): YES
- Token exposed in HTML response: NO
- Email transport found: NO
- **KNOWN FUNCTIONAL LIMITATION:** Forgot-password lưu token an toàn nhưng ứng dụng chưa có email transport để gửi reset URL tới người dùng.

---

## 4. Static Integrity

- PHP lint: PASS
- git diff --check: PASS
- Conflict markers: NONE
- Secret scan: NONE
- Tracked local config: NONE
- Unexpected tracked files: NONE
- GitHub Actions: NONE

---

## 5. Deployment Requirements

- Backup database trước deploy.
- Chạy `php scripts/database/migrate.php --status`.
- Chỉ chạy pending migrations.
- Không dùng `--baseline-existing` nếu chưa đối chiếu schema.
- **Tạo trước thư mục:** `public/assets/images/products`
  - *Ghi chú:* UploadService có thể tự tạo thư mục khi upload, nhưng việc tạo trước giúp verify/deployment ổn định.

Lệnh PowerShell hỗ trợ tạo thư mục trước deploy:
```powershell
New-Item -ItemType Directory -Force public\assets\images\products
```

---

## 6. Final status

**READY FOR HUMAN MERGE DECISION — NOT MERGED**

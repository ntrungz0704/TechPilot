# Báo cáo sửa lỗi và đề nghị review merge (PM-1 Pre-Merge QA)

Ngày cập nhật: 02/08/2026

PR #35
Source: hieu
Target: main
Implementation HEAD tested: 8b3f6bf1f331674c8811e92775a84dc576897098
Base SHA: a146eee8f478895928bdf9290d6e54e6e9b9e263
Ahead/behind trước docs commit: 21/0

## 1. Purchase policy (Authenticated Purchase Flow)

- Khách chưa đăng nhập không được xem cart/checkout hoặc đặt hàng.
- Guest được redirect tới trang login với redirect target an toàn.
- Order yêu cầu user active.

## 2. Checkpoint summary

- P0-1/P0-1B — authenticated purchase: CLOSED
- P0-2 — Flash Sale migration ID types: CLOSED
- P1-1 — Flash Sale quota release: CLOSED
- P1-2 — Coupon usage lifecycle: CLOSED
- P1-3 — Inventory fail-closed commit: CLOSED
- P2-1 — Migration signature validation: CLOSED
- P2-2 — Cancelled order terminal policy: CLOSED
- P2-3 — Zero-stock cart total: CLOSED

## 3. Test evidence (Antigravity local runtime evidence)

| Kiểm tra (Antigravity local runtime) | Kết quả |
|---|---|
| PHP lint | 72 passed, 0 failed |
| AuthenticatedPurchaseFlowTest | 23 passed, 0 failed |
| ProductPricingConsistencyTest | 105 passed, 0 failed |
| CouponUsageLifecycleTest | 18 passed, 0 failed |
| InventoryReservationLifecycleTest | 115 passed, 0 failed |
| FlashSaleReservationLifecycleTest | 65 passed, 0 failed |
| MigrationRunnerSafetyTest | 68 passed, 0 failed |
| DatabaseSchemaParityTest | 58 passed, 0 failed |
| VnpayEnvironmentSecurityTest | 27 passed, 0 failed |
| Product status | 25 passed, 0 failed |
| Facet/filter suites | 180 passed, 0 failed |
| WebQaTest | 19 passed, 8 failed |
| Search QA audit | 20 passed, 0 failed |
| PC Builder audit | 24 passed, 0 failed |
| Benchmark | p95 cao nhất <25ms/query |
| News regression | 58 passed, 1 failed |
| git diff --check | Pass (0 errors) |
| Secret scan | 0 exposed secrets |

GitHub Actions: không có.

## 4. Known issues

- News regression được đánh giá là NON-BLOCKING BASELINE ISSUE vì base và head có cùng một lỗi giống hệt nhau ở `desktop TOC in detail.php checks hasArticleContent`.
- Bảo vệ scraper: Hoãn theo quyết định dự án. Không mở rộng phạm vi trong đợt này.
- WebQaTest: Lỗi phân quyền POST khu vực admin. Cần sửa route/admin post authorization ở tương lai (hiện tại fail các test: TP-WEB-021 đến TP-WEB-025).

## 5. Deployment migration warning

- Backup DB, chạy `php scripts/database/migrate.php --status`, chỉ chạy pending; không dùng `--baseline-existing` nếu chưa đối chiếu schema.

## 6. Kết luận bàn giao

BLOCKED — WebQaTest failed on mandatory tests TP-WEB-021 to TP-WEB-025, and STALE AUTH POLICY DOCUMENTATION OUTSIDE ALLOWED SCOPE was detected in WebQaTest.php.

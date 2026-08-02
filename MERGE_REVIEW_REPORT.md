# TechPilot PR #35 — Final Merge Evidence (PM-2A)

Ngày cập nhật: 02/08/2026

PR #35
Source: hieu
Target: main
Status: OPEN — Not merged

Production implementation HEAD: bc7ba0f02aa28e9110e4501629a1b768513d50d2
Test-infrastructure HEAD: 78f1e511a97e962bce282d128690fe0c7adc6769
Previous docs HEAD: 2c65c6c2d8aee408f3ed09490c626213ead88d03
Ahead/behind before correction docs commit: 27/0

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

## 3. Baseline verification

### News module regression

- Suite: NewsModuleRegressionTest
- Passed: 58 | Failed: 1
- Failure: `desktop TOC in detail.php checks hasArticleContent`
- Classification: **NON-BLOCKING BASELINE ISSUE** (Same as origin/main)

### Verify-install exact FAIL signatures

- Total exact FAIL count: 24
- Note on previous count: A previous report incorrectly claimed "27 FAILs (24 logos, 1 directory, 2 others)". This was caused by a grep filter that matched the word "fail" inside `[PASS] AI Priority Failover`, the `FAIL: 24` summary, and the final completion warning. There are exactly 24 `[FAIL]` lines, consisting of 23 logos and 1 directory.

**1. Exact 1 directory failure:**
- `[FAIL] Directory thiếu: public/assets/images/products`
- HEAD result: FAIL
- origin/main result: FAIL
- Same signature: YES
- Impact: Requires local directory creation.
- Classification: NON-BLOCKING BASELINE ISSUE.

**2. Exact 23 brand-logo failures:**
- `[FAIL] SHA-256 mismatch for asus: registry=4a0e411da254ecdb5183085c1303e7763272b104e630e2d06489af0eb63aef30, actual=1a2a9f72d65b973d97e1627b523446626a1f092940d2e3c16c1c73613659c742`
- `[FAIL] SHA-256 mismatch for msi: registry=2bbad3479db7ef737cd65c3e8cdf919def2fb33efc64152db2eb4d59199bf4da, actual=6047671eafee6af50deab67d9befb2990bdf34b507d952edaa78881a790616b4`
- `[FAIL] SHA-256 mismatch for gigabyte: registry=f8b164c800dbfdc0fb41ab7a71ba05d6daad39ce8855c8ff67d49d7360d22d74, actual=aec80b94f6ccaf2ea4bd73d57bd809aae738c91bcbb33fb12c93858b6f962944`
- `[FAIL] SHA-256 mismatch for dell: registry=dbd92638d90e69beaba44bc9170a26f44b9c9c59d0e58d1b77d67ccf89b191d4, actual=324b431567f984f6bddbad9ff56aaeed84b36d2d321df7e896fc8c0b00779dc0`
- `[FAIL] SHA-256 mismatch for hp: registry=aa9cf43f62ccab17653047029cdfbe257311628eb1d66a26ae7b9770f360b7d2, actual=4ace3aae4575c0c0b360a8c2c566b2f1b9e491d75d728d48b680f5674ee7b5f5`
- `[FAIL] SHA-256 mismatch for lenovo: registry=20aac68668fe3f48ff525d28a3cb252298eadf31f42dffa5b8e405e87dd18410, actual=8e12063764b29ad719617be4fafc60843a9b2036bec72dde0f46a2fbbf31709c`
- `[FAIL] SHA-256 mismatch for razer: registry=d393d95d1f43b77092af117a27d2e117abbfc9d4c77b864c743a66b5c091dd44, actual=2e605539fece1fd6bfe6c5af03c6e08bedbf929f60c27b16f4486039ebf3be62`
- `[FAIL] SHA-256 mismatch for corsair: registry=174c797d09f69af4ee7df20af48479aa3bbb18c2b10f1c29afda9f9d6c613ffe, actual=99ad835c51a129cd9a97f2c5a241372ce7f51740ae625452b59c85760656c0a2`
- `[FAIL] SHA-256 mismatch for intel: registry=8d4b6b05eaf67b219def9ba2d869743e89e8a4d5a6fd0a091f9e67d6258092b7, actual=4ebdb7fc48216c734a363ff3be1f3f07e08be1b2a400d2faaded7e1fd88498bb`
- `[FAIL] SHA-256 mismatch for amd: registry=32daec090168f92be6b1b9451a0955920937a4f574e5b4eef7d22ccf61466f82, actual=3aa64dc22507c8e3ceab6caa7541495bd545fe0b7bdd5424566c5e2975fdd8dc`
- `[FAIL] SHA-256 mismatch for samsung: registry=b9ef17cef49fd46ca9a6521a801dfca2bc899971e097e8b26c610fcc5d933a12, actual=b9010e1d62293b4a7e77c22138bf369a6b33935fbb08ba94437725663c964a16`
- `[FAIL] SHA-256 mismatch for logitech: registry=1e2c67d1ddd7407d493d60f57ade543d60f056112b4360ac1babf0b5eb277966, actual=9e0dfb47cafbab09133da20b48d8d63e18bf89967801a94219aafb41cd67cc64`
- `[FAIL] SHA-256 mismatch for acer: registry=d9da33be27b07e22ab969dc93b766a4f7597b7183e453b7360ffd4932944b544, actual=4d2a860851b3c7493a20d1e859f2f100b2fd84ef2f5a54a24fa5087e93a73f00`
- `[FAIL] SHA-256 mismatch for lg: registry=fc9c08b3d9526b03df5b5dfd7c27962c95306421552335675dcae08aa510547b, actual=ac693b1d21df107c7baff91cc210a480f791176f47e5c09f3adf406612991c51`
- `[FAIL] SHA-256 mismatch for kingston: registry=a78cead8777e0ada6363fff763f7ac4424666cd331f12bcc359eb4a434f04085, actual=3fa96dc8f4d49928989d2cebba8763f1e14292fa27910c006caf9e9e9514a057`
- `[FAIL] SHA-256 mismatch for wd: registry=dd4e427c2d905fb04eb8514fb497198b7bb89e00e5b571c1134e81c00d3e1cd0, actual=2e677ca82fc96c4550ec3488689cc2691f29b34fb067640f8f6ca91c7b08b71a`
- `[FAIL] SHA-256 mismatch for deepcool: registry=b1ad59c94cdf6f5c713f6896d16d3072df1fd17d75edbe4a0990bdd87a1212bc, actual=ba88a036da6dcfff431b2635b9be4fada490678eb453f9b5b7fb07951079d5e6`
- `[FAIL] SHA-256 mismatch for asrock: registry=e97fa93eb916882b151d8ee500fbed63e6b2eced54eb66a39fc4dcb4cbf38297, actual=62449d409822d21120aa7bb720feee78b5c5ca84b367f2dc5700b57554261a74`
- `[FAIL] SHA-256 mismatch for zotac: registry=bc529d7eeb6619cb57d3152dc77d930c0d1170e927807563585e509facc9c40f, actual=e99f700b5cb56b6ed86b6cc9023b3db49a2154abe44c57dfac7eca810ffdece9`
- `[FAIL] SHA-256 mismatch for colorful: registry=90267238f021d0d98663409abe7b1b2913d82fdeeeca631d400bdacc1a1b2534, actual=1948e009162089dcc684065fd770c332a50421a37687277a55bb924910d89f7a`
- `[FAIL] SHA-256 mismatch for crucial: registry=29b75488bdba83dfe58608da94863d11cc07467d709c494c05f1a01f3f6546da, actual=055de8c923c80eab80a5a7be68269c5e97cf531f7e4b27e769757389ce070805`
- `[FAIL] SHA-256 mismatch for cooler-master: registry=9aed405a45ce2e26eed1870f0712a9d0fd2e843c27a33ea5ad6c14446dcec98f, actual=f0e4cb46631e8a16660e3afdf0832875cc96844282f41345e4b0e44efdc7ccc8`
- `[FAIL] SHA-256 mismatch for xigmatek: registry=715813ee7927dcf647cba554fa144fd9704e32a4895258494e96d3c3dcbf62fa, actual=ff5924fee75a16edf46bfdcce60b36f1cacfa505d741905ac0f1ea50c8ccdf5d`
- HEAD result: FAIL
- origin/main result: FAIL
- Same signature: YES
- Impact: Hashes do not match expected registry.
- Classification: NON-BLOCKING BASELINE ISSUE.

**Conclusion:** All 24 FAIL lines are identical to origin/main. No regression detected.

---

## 4. Password reset delivery

- Token generation: YES
- Token storage: YES (TTL 1h)
- Token exposed in HTML response: NO
- Email transport found: NO
- Delivery operational: NO

**KNOWN FUNCTIONAL LIMITATION:**
Forgot-password chống account enumeration và lưu token an toàn,
nhưng ứng dụng chưa có mail delivery để gửi reset URL tới người dùng.

---

## 5. Evidence classification

- GitHub/source verified: YES
- Antigravity local runtime evidence: YES
- GitHub Actions: NONE configured

---

## 6. Final status

**READY FOR HUMAN MERGE DECISION — NOT MERGED**

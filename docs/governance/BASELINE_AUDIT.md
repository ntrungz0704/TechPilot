# Baseline Audit — TechPilot

| Thuộc tính | Giá trị |
|---|---|
| Audit date | `2026-07-16` (`Asia/Bangkok`) |
| Audit mode | Read-only trước khi tạo governance |
| Governance decision | `PARTIAL` |
| Product execution decision | `BLOCKED` |
| Canonical branch | `main` |
| Canonical HEAD | `1ae679461e1f709488155ebf275ef070b54d723a` |
| Remote state | Local `main` khớp `origin/main` tại thời điểm audit |
| Active phase | `UNRESOLVED` |
| Active checkpoint | `NO_ACTIVE_CHECKPOINT` |

`PARTIAL` nghĩa là governance có thể được dựng ở trạng thái draft, nhưng chưa đủ
Human decision để mở product execution.

## 1. Lệnh audit đã chạy

```text
git status --short
git branch --show-current
git rev-parse HEAD
git log -5 --oneline
git remote -v
git tag --list
git branch -a -vv
git diff --stat
git diff --name-only
git ls-files
```

Ngoài ra đã kiểm tra README, `.agents`, design/phase documents, rule files,
roadmap/checkpoint/ADR/handoff/review/evidence, `.github`, CODEOWNERS, scripts,
package/test files và dấu hiệu tool trong repository.

## 2. Repository state trước governance

### Git

- Chỉ phát hiện branch `main` và remote tracking `origin/main`.
- Không phát hiện tag.
- Có 4 commit. Một commit dùng prefix `feat:`, hai commit dùng “Add files via
  upload”; chưa đủ bằng chứng cho commit convention ổn định.
- Remote repository là `XuanDinh2702/Techpilot`, nhưng remote owner không chứng
  minh ai là Human Project Owner hoặc CODEOWNER.

### Working tree

Tại thời điểm audit:

- 25 tracked product files đã thay đổi.
- Diff tracked khoảng 2.522 dòng thêm và 919 dòng xóa.
- Có untracked source/config/schema/design/prototype và `php-server*.log`.
- Các thay đổi trải qua controller, model, view, helper, config, schema, CSS, JS và
  public entry point.

Không có checkpoint contract, Writer, Reviewer, handoff, evidence hoặc review để
giải thích/approve delta này. Audit không xác định tác giả hay trạng thái hoàn tất
của delta.

Quyết định: bảo toàn nguyên trạng, không reset/delete/stage và không hợp thức hóa
bằng governance mới.

Evidence scoped và giới hạn chứng minh được ghi tại
[`docs/evidence/GOVERNANCE-BASELINE.md`](../evidence/GOVERNANCE-BASELINE.md). Initial
và post-setup product status/stat cùng là 25 tracked files, `+2522/-919`; tuy nhiên
không có pre-edit byte hash, nên không được overclaim byte-for-byte immutability.

## 3. Framework và MVC

- Nested README và code cho thấy PHP MVC thuần, MySQL/MariaDB, HTML/CSS và
  JavaScript; không có framework/package manifest được phát hiện.
- Cấu trúc có controller, model, view, core và một `CartService` untracked trong
  dirty working tree.
- Database README draft yêu cầu business rule ở service layer, nhưng implementation
  đang là mô hình hỗn hợp và nhiều order rule nằm trong Model.

Governance phải mô tả convention được code thực sự dùng. Không tự thêm DDD, Clean,
Hexagonal, Repository/Use Case layer hoặc buộc chuyển toàn bộ logic sang Service.

## 4. Source of truth trước governance

- Root `README.md` tại HEAD chỉ có tiêu đề.
- Committed nested README mô tả bốn route và cart demo.
- Working-tree nested README mô tả cart/checkout/schema V2 rộng hơn.
- Không có file tuyên bố authority, active state hay lifecycle canonical.

Kết luận: trước governance, Git HEAD là baseline implementation duy nhất có thể
truy vết; không có canonical planning state. Working-tree content chưa được xem là
approved state.

## 5. Planning/design documents hiện có

Gói `techpilot/design/` là untracked tại thời điểm audit. Gói có nhiều input hữu
ích: product direction, personas, P0 scope, matrix, business/data contracts,
decision log và prototype.

Tuy nhiên:

- `PHASE-0-SCOPE-LOCK.md` tự ghi `LOCKED`, “Project owner đã yêu cầu”, Phase 0 đã
  đóng và Phase 1 có thể bắt đầu.
- Không có tên người duyệt, approval record, document commit, Writer, Reviewer,
  handoff hoặc gate evidence.
- Vì file untracked, claim không nằm trong canonical Git history.

Phân loại: `UNVERIFIED PLANNING INPUT`; không phải `PLAN_APPROVED`.

## 6. Mâu thuẫn và tài liệu stale

### Design/layout

- `TECHPILOT-UXUI-V2.md` mô tả PDP desktop `7/5` columns.
- `phase-0/SCREEN-STATE-MATRIX.md` mô tả `6/6`.

### P0 scope

UX/UI spec đưa AI Advisor/PC Builder, compare tray, wishlist/Q&A và coupon/payment
cards vào core screens. Phase-0 scope và decision log lại chuyển nhiều mục đó sang
P1/P2 hoặc out-of-scope.

### Canonical design source

Decision D-018 gọi Figma semantic token là source. Governance yêu cầu repository
là canonical project state. Cần sửa nghĩa: Figma là authoring/reference; mapping
đã duyệt và lưu trong repository mới là official state.

### Phase và priority

Tài liệu dùng “Phase 0/1/5” cho delivery sequence và “P0/P1/P2” cho product scope,
nhưng không có roadmap giải thích hai trục. Không thể suy ra active phase.

### Backend blocker register

Dirty source/schema đã có dấu hiệu SKU variant, email/payment status/idempotency,
CSRF, transaction lock và search/pagination mới. Vì vậy một số BE finding cũ có
thể đã thay đổi. Structured address, warranty source, float-money và architecture
gap có thể còn.

Mỗi finding phải được audit lại trên candidate SHA; không bulk mark open/closed.

### Test claim

Committed nested README nói đã kiểm thử toàn bộ nhưng không có test suite/evidence.
Working README chỉ nêu hai lệnh `php -l`. Không được tuyên bố coverage hoặc tự phát
minh PHPUnit/composer command.

## 7. Governance maturity trước setup

Không phát hiện:

- `AGENTS.md`, `START_HERE.md`, `CONTRIBUTING.md`, `ROADMAP.md`.
- Canonical vision/phase/checkpoint/`ACTIVE.md`.
- Architecture/ADR, handoff, review hoặc evidence convention.
- Governance scripts hoặc repo doctor.
- `.github` workflow, PR template hoặc CODEOWNERS.
- Branch/release convention và branch-protection evidence.
- Test suite, `composer.json`, `package.json` hoặc `phpunit.xml`.

`.agents/` tồn tại nhưng rỗng.

## 8. Team/tool evidence

- Figma-first docs và prototypes tồn tại trong dirty tree.
- Phiên thiết lập governance hiện tại dùng Codex.
- Không có repository evidence xác nhận Antigravity, Claude Code, Gemini CLI hoặc
  Cursor đang được team dùng.

Chỉ tạo Codex adapter và adapter template ở baseline; thêm tool khác khi có bằng
chứng thực tế.

## 9. Current canonical planning state

```yaml
active_phase: UNRESOLVED
active_checkpoint: NO_ACTIVE_CHECKPOINT
lifecycle: DRAFT
human_project_owner: UNRESOLVED
plan_approved_by: UNRESOLVED
writer: UNASSIGNED
writer_tool: UNRESOLVED
reviewer: UNASSIGNED
reviewer_tool: UNRESOLVED
base_commit: 1ae679461e1f709488155ebf275ef070b54d723a
candidate_commit: UNRESOLVED
reviewed_commit: UNRESOLVED
required_next_action: HUMAN_PLAN_APPROVAL_REQUIRED
```

`DRAFT` là lifecycle của `NO_ACTIVE_CHECKPOINT` trong `ACTIVE.md`. Contract hỗ trợ
`CP-00.0` có thể ở `PLAN_REVIEW`, nhưng không phải active checkpoint và không mở
source execution.

## 10. Human Actions Required

1. Xác nhận Human Project Owner và GitHub handle.
2. Quyết định giữ, tách, review hay loại bỏ dirty product delta tồn tại sẵn.
3. Review/approve hoặc sửa `PRODUCT_VISION.md` và `ROADMAP.md`.
4. Giải quyết các conflict design/scope/layout và terminology phase/P-tier.
5. Audit lại backend blockers trên candidate SHA được chọn.
6. Chọn active phase/checkpoint và ghi contract canonical.
7. Gán Writer và Reviewer độc lập cùng tool metadata.
8. Xác nhận PHP/runtime/test commands; không dựa vào claim chưa có evidence.
9. Điền CODEOWNERS thật và bật branch protection/required CI.
10. Chỉ Human thực hiện Git/release actions.

## 11. Phạm vi không được phép thay đổi và mức evidence

- Governance setup không được phép chủ động sửa file dưới `techpilot/`.
- Không sửa business logic, schema, API, authentication, CSS/JS sản phẩm hoặc
  prototype.
- Không thêm runtime dependency.
- Không reset, stage, commit, merge, push, deploy hoặc release.

Product-scoped Git status/stat sau setup khớp observation ban đầu. Do audit ban đầu
không chụp byte hash, kết quả chỉ là partial evidence; xem
[`GOVERNANCE-BASELINE.md`](../evidence/GOVERNANCE-BASELINE.md) để biết giới hạn.

## 12. Decision

- **Governance:** `PARTIAL` cho đến khi Owner/approval/assignment được điền.
- **Source execution:** `BLOCKED` vì `NO_ACTIVE_CHECKPOINT` và dirty delta chưa
  được xử lý.
- **Phase-0 claim:** `UNVERIFIED`, không được chuyển thành `PLAN_APPROVED`.

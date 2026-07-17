# Vòng đời checkpoint

## Trạng thái hợp lệ

| Trạng thái | Ý nghĩa | Ai có thể ghi quyết định |
|---|---|---|
| `DRAFT` | Contract đang được soạn | Planning Authority |
| `PLAN_REVIEW` | Chờ Human review | Planning Authority |
| `PLAN_APPROVED` | Human duyệt đúng version | Human Project Owner |
| `EXECUTING` | Writer đang thực hiện approved scope | Writer được gán, sau startup gate |
| `READY_FOR_REVIEW` | Writer hoàn tất handoff/evidence | Writer được gán |
| `REWORK_REQUIRED` | Reviewer yêu cầu sửa trong contract | Independent Reviewer |
| `GATE_PASS` | Reviewed commit đạt contract | Independent Reviewer |
| `REVIEW_INVALIDATED` | Source đổi sau review | Người phát hiện phải ghi ngay |
| `MERGED` | Exact reviewed commit đã được merge | Human Project Owner |
| `ROLLBACK_REQUIRED` | Sau merge, evidence cho thấy release cần rollback decision | Human Project Owner; mọi thành viên có thể báo incident |
| `CLOSED` | Checkpoint và canonical docs hoàn tất | Human Project Owner |
| `BLOCKED` | Không thể tiếp tục an toàn | Bất kỳ role nào phát hiện blocker |

## Luồng chuẩn

```text
DRAFT
→ PLAN_REVIEW
→ PLAN_APPROVED
→ EXECUTING
→ READY_FOR_REVIEW
→ GATE_PASS
→ MERGED
→ CLOSED
```

Không bỏ qua bước vì task nhỏ.

## Nhánh rework

```text
READY_FOR_REVIEW
→ REWORK_REQUIRED
→ EXECUTING
→ READY_FOR_REVIEW
→ GATE_PASS
```

Mỗi candidate mới cần handoff/evidence cập nhật và review mới.

## Review invalidation

```text
GATE_PASS
→ source changed
→ REVIEW_INVALIDATED
→ READY_FOR_REVIEW
→ independent review again
```

Không merge candidate có state `REVIEW_INVALIDATED`.

## Candidate materialization trước gate

`READY_FOR_REVIEW` cho biết Writer đã bàn giao, không chứng minh candidate commit
đã tồn tại. Nếu SHA còn pending:

```text
READY_FOR_REVIEW
→ Human materializes candidate commit
→ Human records full candidate SHA in canonical state
→ Independent Reviewer starts
→ Gate decision
```

Review trên patch/working tree trước bước Human commit chỉ là advisory. Nó không
được tạo `GATE_PASS`, reviewed SHA hoặc merge permission.

## Blocked

`BLOCKED` cần mã lý do, evidence, người có thể giải quyết và next action. Ví dụ:

- `BLOCKED — GOVERNANCE_CONFLICT`
- `BLOCKED — ROLE_UNRESOLVED`
- `BLOCKED — OUT_OF_SCOPE_CHANGE_REQUIRED`
- `BLOCKED — GOVERNANCE_CHANGE_REQUIRED`
- `BLOCKED — REVIEW_SCOPE_CONFLICT`
- `BLOCKED — CREDENTIAL_REQUIRED`

Chỉ bỏ `BLOCKED` khi nguyên nhân được giải quyết và canonical state được cập nhật.
Không dùng `BLOCKED` để che một test fail có thể sửa trong approved scope.

## `MERGED` → `ROLLBACK_REQUIRED`

Transition này dùng khi exact reviewed commit đã merge nhưng runtime/security/data
evidence cho thấy release không an toàn hoặc không còn đáp ứng contract.

```text
MERGED
→ incident evidence
→ ROLLBACK_REQUIRED
→ Human rollback hoặc mở forward-fix checkpoint
→ Human ghi resolution
→ CLOSED khi incident và canonical state đã được xử lý
```

### Authority

- Bất kỳ thành viên/agent nào cũng phải báo incident ngay.
- Chỉ Human Project Owner được ghi canonical `ROLLBACK_REQUIRED`, chọn rollback
  hoặc forward fix, thực hiện release action và quyết định khi nào `CLOSED`.
- Writer/Reviewer không tự rollback hoặc sửa nóng ngoài checkpoint.

### Evidence bắt buộc

- Checkpoint ID, merged SHA, deployed SHA/version và môi trường.
- Thời điểm phát hiện, triệu chứng, impact/severity và reproduction nếu có.
- Test/log/runtime evidence không chứa secret hoặc PII.
- Rollback target và lý do target an toàn.
- Database/migration/backward-compatibility impact.
- Người báo, Human decision, action result và follow-up checkpoint nếu có.

### Required next action

Đóng băng release action liên quan, báo Human, bảo toàn evidence và chờ decision.
Nếu Human rollback, ghi kết quả và canonical deployed state. Nếu forward fix, mở
checkpoint mới; không tái sử dụng review cũ.

## Điều kiện chuyển trạng thái

### `PLAN_REVIEW` → `PLAN_APPROVED`

- Contract đầy đủ scope/out-of-scope/allowlist/acceptance/tests/evidence.
- Base SHA, Writer và Reviewer xác định.
- Human approval record hợp lệ.

### `PLAN_APPROVED` → `EXECUTING`

- Writer đúng assignment.
- Repo doctor không có blocking issue.
- Branch/HEAD/working tree tương thích contract.
- Startup report đã xuất.

### `EXECUTING` → `READY_FOR_REVIEW`

- Implementation trong allowlist.
- Required tests đã chạy thực tế.
- Evidence và handoff tồn tại.
- Changed files khớp handoff.
- Candidate SHA được ghi nếu đã có.

### `READY_FOR_REVIEW` → Gate decision

- Human đã materialize candidate commit và canonical state có full candidate SHA.
- Reviewer độc lập xác minh đúng candidate.
- Diff, tests, evidence, MVC, scope và dependency được kiểm tra.
- Chỉ một trong `GATE_PASS`, `REWORK_REQUIRED`, `BLOCKED`.

### `GATE_PASS` → `MERGED` → `CLOSED`

- Human xác nhận exact reviewed SHA và CI.
- Human thực hiện merge/release action.
- Canonical state, roadmap/phase và evidence được cập nhật.

## Baseline hiện tại

`NO_ACTIVE_CHECKPOINT`; không được suy ra lifecycle từ dirty working tree hay tài
liệu untracked.

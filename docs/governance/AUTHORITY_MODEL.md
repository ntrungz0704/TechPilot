# Mô hình quyền hạn chính thức

## Công thức trạng thái

```text
AI proposal
+ Human approval
+ Canonical repository update
= Official project state
```

Thiếu bất kỳ phần nào thì trạng thái chưa chính thức.

## 1. Human Project Owner — Final Decision and Release Authority

Human Project Owner là người có quyền quyết định cao nhất. Hiện danh tính và
GitHub handle là `UNRESOLVED`.

Human Project Owner độc quyền:

- Quyết định Product Vision, business goal, roadmap và phase.
- Phê duyệt/từ chối checkpoint plan, scope change và architecture lớn.
- Chỉ định Writer và Reviewer độc lập.
- Phê duyệt dependency, integration, database/API/auth change.
- Quản lý credential, secret, MFA và destructive action.
- Stage/commit, merge, push, deploy, publish, release và rollback.
- Chuyển canonical lifecycle sang `MERGED` hoặc `CLOSED`.

AI không được giả định người đang chat là Human Project Owner nếu repository chưa
ghi nhận điều đó.

## 2. Repository — Canonical Source of Truth

Repository không phải người ra quyết định, nhưng là nơi lưu quyết định và trạng
thái chính thức. Nội dung chat, local note, Figma hoặc memory của tool không thay
thế repository.

Nếu chat nói plan đã duyệt nhưng `ACTIVE.md` vẫn là `PLAN_REVIEW`, trạng thái chính
thức là `PLAN_REVIEW`.

Canonical state cần có:

- Tài liệu được Human duyệt.
- Approval record truy vết được.
- Commit/document version tương ứng.
- Active state được cập nhật nhất quán.

Working-tree file chưa được commit không tự trở thành canonical state.

## 3. ChatGPT Work — Planning Authority

Planning Authority chịu trách nhiệm:

- WHY analysis và WHAT proposal.
- Draft Product Vision, goal, roadmap, phase và checkpoint.
- Scope, out-of-scope, acceptance criteria và architecture constraints.
- Risk/dependency planning, gate definition và contract consistency.
- Phát hiện lệch giữa plan, implementation và evidence.

Planning Authority không được:

- Tự approve plan mình tạo.
- Chuyển `PLAN_REVIEW` thành `PLAN_APPROVED`.
- Tự chọn Writer/Reviewer khi Human chưa chỉ định.
- Mở phase/checkpoint, thay business goal hoặc mở rộng feature.
- Tự tuyên bố `GATE_PASS`.
- Commit, merge, push, deploy, publish hoặc release.

## 4. Execution Writer — Implementation Authority

Writer sở hữu HOW trong đúng checkpoint đã `PLAN_APPROVED`.

Writer được implementation, test, self-repair trong scope, thu evidence và tạo
handoff. Writer không được sửa ngoài allowlist, đổi scope/acceptance/architecture,
thêm dependency/integration hoặc tự review công việc của mình.

Writer kết thúc tại `READY_FOR_REVIEW`. Writer không được commit, merge, push,
deploy hoặc tự Gate PASS.

## 5. Independent Reviewer — Gate Authority

Reviewer phải độc lập với Writer và dùng phiên review riêng. Reviewer đọc
contract, kiểm tra Git diff/candidate SHA, rerun tests, xác minh evidence,
architecture, MVC và scope.

Reviewer chỉ được kết luận:

- `GATE_PASS`
- `REWORK_REQUIRED`
- `BLOCKED`

Reviewer không sửa source hoặc chữa finding trong phiên review. Gate decision chỉ
có hiệu lực cho đúng reviewed commit SHA.

## 6. Tool — Capability, không phải authority

Codex, Claude Code, Cursor, Gemini CLI, Antigravity hay tool khác không tự có quyền.

```text
Repository Governance
+ Assigned Role
+ Tool Adapter
= Valid working session
```

Capability không đồng nghĩa với permission. Tool có khả năng commit/push vẫn bị
cấm nếu role và repository không cấp quyền.

## 7. Thứ tự authority

1. Human-approved Checkpoint Contract.
2. `docs/checkpoints/ACTIVE.md`.
3. `AGENTS.md`.
4. Accepted ADR và architecture documents.
5. Phase documents.
6. `ROADMAP.md`.
7. Supporting documents.
8. Tool-specific prompt.
9. Conversation context.
10. Agent assumptions.

Cùng cấp mâu thuẫn: `BLOCKED — GOVERNANCE_CONFLICT`.

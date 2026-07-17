---
schema_version: 1
checkpoint_id: CP-XX.X
phase_id: PHASE-XX
title: UNRESOLVED
lifecycle_status: DRAFT
planning_authority: "ChatGPT Work"
plan_approved_by: HUMAN_PLAN_APPROVAL_REQUIRED
plan_approval_ref: UNRESOLVED
assigned_writer:
  member: UNASSIGNED
  role: "Execution Writer"
  tool: TOOL_ADAPTER_REQUIRED
  assigned_by: UNASSIGNED
  assigned_at: UNRESOLVED
  approval_record: UNRESOLVED
  role_assignment_ref: UNRESOLVED
assigned_reviewer:
  member: UNASSIGNED
  role: "Independent Reviewer"
  tool: TOOL_ADAPTER_REQUIRED
  assigned_by: UNASSIGNED
  assigned_at: UNRESOLVED
  approval_record: UNRESOLVED
  role_assignment_ref: UNRESOLVED
base_commit: UNRESOLVED
candidate_commit: UNRESOLVED
contract_path: "docs/checkpoints/CP-XX.X-NAME.md"
allowed_paths: []
forbidden_paths: []
forbidden_changes: []
writer_permission: NONE
reviewer_permission: READ_ONLY
required_tests: []
required_evidence: []
required_next_action: HUMAN_PLAN_APPROVAL_REQUIRED
last_updated: UNRESOLVED
---

# Checkpoint Contract

Khi nội dung đã sẵn sàng để Human review, chuyển contract sang
**PLAN_REVIEW** và commit document candidate. Sau khi Human tạo approval record
gắn với commit đó, chỉ ACTIVE chuyển sang **PLAN_APPROVED**; contract tiếp tục giữ
**PLAN_REVIEW** để approval luôn gắn với đúng byte content.

## Objective

- Objective: UNRESOLVED

## Business outcome

- Business outcome: UNRESOLVED

## Dependencies and entry conditions

- Dependencies: UNRESOLVED
- Checkpoint đã được Human Project Owner chuyển thành **PLAN_APPROVED** trong repository.
- Base commit đã xác minh.
- Writer và Reviewer là hai phiên độc lập, được Human chỉ định.
- Working tree không có thay đổi không rõ nguồn.

## Scope

## In scope

- UNRESOLVED

## Out of scope

- UNRESOLVED

### Allowed paths

- UNRESOLVED

### Forbidden paths

- UNRESOLVED

### Forbidden changes

- Feature, dependency, integration, architecture, database, API hoặc authentication ngoài contract.
- Sửa governance để nới quyền cho checkpoint.
- Commit, merge, push, deploy, publish hoặc destructive action bởi AI.

## Technical contract

- Request/response hoặc UI contract: UNRESOLVED
- Persistence contract: UNRESOLVED
- Security/authorization contract: UNRESOLVED
- Compatibility contract: UNRESOLVED

## MVC constraints

- Giữ PHP MVC thuần và convention thực tế được mô tả trong docs/architecture/SYSTEM_ARCHITECTURE.md.
- Không tự thêm Domain, Application, Repository, Use Case, Port, Adapter hoặc Service layer.
- Chỉ dùng optional layer nếu repository đã dùng cho đúng luồng hoặc contract được Human duyệt rõ.
- Controller điều phối request; Model giữ semantics dữ liệu/persistence hiện có; View chỉ presentation; route giữ public contract đã duyệt.

## Existing conventions to preserve

- Class/file PascalCase; Controller có hậu tố Controller.
- Action lowerCamelCase; dữ liệu database dùng snake_case.
- Front controller tại techpilot/public/index.php và document root tại techpilot/public.
- Convention bổ sung: UNRESOLVED

## Acceptance criteria

1. UNRESOLVED — mô tả kết quả đo được.
2. Git diff chỉ chứa file khớp allowed paths.
3. Không có thay đổi thuộc forbidden paths hoặc forbidden changes.
4. Required tests hoàn tất với kết quả và exit code được ghi lại.
5. Handoff khớp đầy đủ với Git diff thực tế.

Không dùng tiêu chí mơ hồ như “hoạt động tốt”, “code sạch”, “UI đẹp”, “tối ưu” hoặc “không lỗi”.

## Required tests and evidence

- Required tests: UNRESOLVED
- Required evidence: UNRESOLVED
- Required handoff: docs/handoffs/CP-XX.X-HANDOFF.md

## Exit conditions

- Writer chỉ được kết thúc tại **READY_FOR_REVIEW**.
- Reviewer độc lập chỉ được kết luận **GATE_PASS**, **REWORK_REQUIRED** hoặc **BLOCKED**.
- GATE_PASS phải gắn với đúng reviewed commit SHA.
- Human Project Owner giữ quyền MERGED, CLOSED và release.

## Stop conditions

- Contract chưa PLAN_APPROVED, role chưa được chỉ định hoặc Writer trùng Reviewer.
- Base/candidate SHA không khớp, working tree không rõ nguồn hoặc có conflict.
- Cần sửa ngoài allowlist, thêm dependency/integration, đổi architecture hoặc dùng credentials.
- Required test không chạy được hoặc cần destructive action.

## Known risks

- UNRESOLVED

## Human approval

- Decision type: HUMAN_PLAN_APPROVAL_REQUIRED
- Checkpoint ID: CP-XX.X
- Contract path: docs/checkpoints/CP-XX.X-NAME.md
- Base commit: UNRESOLVED
- Approved by: UNASSIGNED
- Approval record: UNRESOLVED
- Allowed next action: NONE
- Limitations: Approval không bao gồm commit, merge, push, deploy hoặc release.

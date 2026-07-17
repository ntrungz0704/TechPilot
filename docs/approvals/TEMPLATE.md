---
schema_version: 1
decision_type: UNRESOLVED
scope_type: UNRESOLVED
scope_id: UNRESOLVED
checkpoint_id: UNRESOLVED
phase_id: UNRESOLVED
decided_object: UNRESOLVED
approved_by: UNASSIGNED
authority_role: "Human Project Owner"
authority_tool: NOT_APPLICABLE
approved_at: UNRESOLVED
approved_document_path: UNRESOLVED
approved_document_commit: UNRESOLVED
contract_path: UNRESOLVED
base_commit: UNRESOLVED
candidate_commit: NOT_APPLICABLE
reviewed_commit: NOT_APPLICABLE
covers_assignments: false
assignments:
  writer:
    member: UNASSIGNED
    role: "Execution Writer"
    tool: TOOL_ADAPTER_REQUIRED
    assigned_by: UNASSIGNED
    assigned_at: UNRESOLVED
  reviewer:
    member: UNASSIGNED
    role: "Independent Reviewer"
    tool: TOOL_ADAPTER_REQUIRED
    assigned_by: UNASSIGNED
    assigned_at: UNRESOLVED
assignment:
  role_kind: UNRESOLVED
  member: UNASSIGNED
  role: UNRESOLVED
  tool: TOOL_ADAPTER_REQUIRED
  assigned_by: UNASSIGNED
  assigned_at: UNRESOLVED
approved_paths: []
allowed_next_action: NONE
forbidden_actions:
  - "Commit, merge, push, deploy, publish, release, rollback, and destructive actions remain Human-only"
conditions:
  - "Replace every UNRESOLVED or UNASSIGNED field required by the selected decision type"
limitations:
  - "This record authorizes only its exact scope and approved document commit"
---

# Canonical Human Decision Record

Sao chép template này thành một file mới trong `docs/approvals/`; không dùng trực tiếp `TEMPLATE.md` làm approval reference.

## Cách dùng theo decision type

- `PLAN_APPROVED`: dùng `scope_type: CHECKPOINT`; document được duyệt là checkpoint contract. Nếu record duyệt luôn role assignments, đặt `covers_assignments: true` và điền hai snapshot dưới `assignments` khớp tuyệt đối với ACTIVE/contract.
- `ROLE_ASSIGNMENT_APPROVED`: dùng `scope_type: CHECKPOINT`; điền `assignment.role_kind` là `WRITER` hoặc `REVIEWER` cùng toàn bộ metadata assignment.
- `GOVERNANCE_CHANGE_APPROVED`: dùng `scope_type: GOVERNANCE`; `approved_paths` phải bao phủ chính xác các protected governance paths được phép đổi.

## Quy tắc bất biến

`approved_document_commit` phải là full commit SHA đã chứa đúng nội dung document được Human duyệt và là ancestor của validation head. Sửa document sau commit đó làm record không còn hợp lệ; phải quay về review và tạo decision record mới.

Human điền identity, timestamp ISO-8601 có timezone, scope, conditions, forbidden actions và allowed next action cụ thể. AI không tự tạo giá trị approval.

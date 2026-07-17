# PHASE-XX — Tên phase

> Sao chép template này. Không sửa template thành một phase đang hoạt động.

## Metadata

```yaml
phase_id: PHASE-XX
title: UNRESOLVED
plan_status: DRAFT
execution_state: NOT_STARTED
roadmap_path: ROADMAP.md
planning_authority: UNRESOLVED
human_project_owner: UNRESOLVED
approved_by: UNRESOLVED
approval_date: UNRESOLVED
approval_record: UNRESOLVED
target_branch: UNRESOLVED
base_commit: UNRESOLVED
```

`plan_status` chỉ dùng `DRAFT`, `PLAN_REVIEW` hoặc `PLAN_APPROVED`.
`execution_state` chỉ dùng `NOT_STARTED`, `ACTIVE`, `BLOCKED` hoặc `CLOSED`.
Chỉ Human Project Owner được ghi approval, mở phase hoặc đóng phase.

## 1. WHY

- Vấn đề:
- Bằng chứng:
- Người dùng/business outcome bị ảnh hưởng:
- Vì sao cần làm trong phase này:

## 2. Mục tiêu

- Mục tiêu đo được:
- Kết quả người dùng:
- Kết quả kỹ thuật:

## 3. In scope

- `UNRESOLVED`

## 4. Out of scope

- `UNRESOLVED`

## 5. Architecture constraints

- Framework/MVC variant đã audit:
- Convention phải bảo toàn:
- Layer hiện có được phép dùng:
- Forbidden architecture changes:
- Accepted ADR liên quan:

Không thêm abstraction, dependency, integration, database, API hoặc auth ngoài
scope đã được Human duyệt.

## 6. Entry conditions

- [ ] Product Vision và roadmap liên quan đã được Human duyệt.
- [ ] Phase scope/out-of-scope đã được Human duyệt.
- [ ] Baseline commit tồn tại và working tree được xử lý rõ.
- [ ] Architecture constraints và dependency đã được xác nhận.
- [ ] Risk, test strategy và evidence strategy đã được xác nhận.
- [ ] Checkpoint plan đã được chia đủ nhỏ.
- [ ] Mỗi checkpoint có Writer và Reviewer độc lập dự kiến.

Không đạt entry condition thì phase không được chuyển sang `ACTIVE`.

## 7. Checkpoint plan

| Checkpoint | Mục tiêu | Contract | Dependency | Status |
|---|---|---|---|---|
| `CP-XX.1` | `UNRESOLVED` | `UNRESOLVED` | `UNRESOLVED` | `DRAFT` |

Mỗi checkpoint có contract riêng. Phase approval không thay thế checkpoint
approval.

## 8. Acceptance và validation strategy

- Acceptance criteria cấp phase:
- Automated tests:
- Manual/runtime checks:
- Security/accessibility/performance checks:
- Evidence bắt buộc:

Không ghi test là PASS nếu chưa chạy thực tế.

## 9. Risk và dependency

| ID | Risk/dependency | Tác động | Mitigation | Owner | Status |
|---|---|---|---|---|---|
| `R-XX-01` | `UNRESOLVED` | `UNRESOLVED` | `UNRESOLVED` | `UNASSIGNED` | `OPEN` |

## 10. Exit conditions

- [ ] Tất cả checkpoint bắt buộc là `CLOSED` hoặc có quyết định Human rõ ràng.
- [ ] Gate review áp dụng đúng reviewed commit của từng checkpoint.
- [ ] Không còn blocker/critical finding chưa xử lý.
- [ ] Test/evidence bắt buộc tồn tại và có thể truy vết.
- [ ] Architecture, MVC, dependency và scope được xác minh.
- [ ] Tài liệu canonical, roadmap và `ACTIVE.md` được cập nhật.
- [ ] Release/rollback decision được Human ghi nếu phase tạo release.
- [ ] Human Project Owner ghi quyết định đóng phase.

## 11. Change control

Mỗi scope change phải ghi:

- Change request ID.
- Vấn đề và bằng chứng.
- Scope/acceptance bị ảnh hưởng.
- Effort, risk, timeline và dependency impact.
- Người đề xuất.
- Human decision: Accepted / Rejected / Deferred.
- Phiên bản phase document mới.

Trao đổi trong chat không tự thay đổi phase.

## 12. Human approval record

```yaml
decision: HUMAN_PLAN_APPROVAL_REQUIRED
approved_by: UNRESOLVED
approved_at: UNRESOLVED
approved_document_commit: UNRESOLVED
notes: UNRESOLVED
```

Không AI nào được tự điền approval record.

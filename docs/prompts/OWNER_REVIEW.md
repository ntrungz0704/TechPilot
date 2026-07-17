# Human Project Owner Review

File này là checklist hỗ trợ Human; nó không tự tạo approval. Quyết định chỉ chính thức sau khi Human điền đủ dữ liệu và cập nhật canonical repository.

## Baseline decisions required

- [ ] Xác nhận tên/định danh Human Project Owner.
- [ ] Xác nhận GitHub handle thay cho @REPLACE_WITH_PROJECT_OWNER.
- [ ] Chọn cách xử lý product working-tree delta đang tồn tại.
- [ ] Duyệt, yêu cầu sửa hoặc từ chối PRODUCT_VISION và ROADMAP.
- [ ] Xác minh hoặc bác bỏ các tuyên bố LOCKED/Phase 0 closed trong techpilot/design/phase-0.
- [ ] Chọn active phase và checkpoint; không dùng P0/P1 backlog thay cho Phase ID.
- [ ] Chỉ định Writer và Reviewer khác nhau.
- [ ] Ghi `assigned_by`, `assigned_at`, `approval_record` và
      `role_assignment_ref` cho từng role; hoặc dùng plan approval ghi rõ
      `covers_assignments: true` với snapshot khớp tuyệt đối.
- [ ] Xác nhận tool adapter và required tests.
- [ ] Bật branch protection theo docs/governance/BRANCH_AND_RELEASE_RULES.md.

## Canonical decision record

Tạo record từ `docs/approvals/TEMPLATE.md`, lưu thành file mới dưới
`docs/approvals/`, commit document được duyệt trước, rồi ghi full SHA đó vào
`approved_document_commit`. Không trỏ ACTIVE tới template hoặc approval chỉ có
trong chat.

- Decision type: UNRESOLVED
- Checkpoint ID: UNRESOLVED
- Decided object: UNRESOLVED
- Contract path: UNRESOLVED
- Base commit: UNRESOLVED
- Candidate commit: UNRESOLVED
- Reviewed commit: UNRESOLVED
- Authority name: UNASSIGNED
- Authority role: Human Project Owner
- Date: UNRESOLVED
- Evidence/reference: UNRESOLVED
- Allowed next action: NONE
- Forbidden next actions: commit, merge, push, deploy and release unless separately authorized
- Limitations: UNRESOLVED

Không ghi “Approved” đơn lẻ. Dùng đúng decision type như PLAN_APPROVED, MERGE_APPROVED hoặc RELEASE_APPROVED và gắn với checkpoint/commit phù hợp.

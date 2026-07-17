# Mô hình vai trò

Vai trò quyết định quyền hạn; tên tool không quyết định quyền hạn.

## Trạng thái assignment hiện tại

| Role | Assignment |
|---|---|
| Human Project Owner | `UNRESOLVED` |
| Planning Authority | `ChatGPT Work` |
| Execution Writer | `UNASSIGNED` |
| Independent Reviewer | `UNASSIGNED` |
| Current tool evidence | Codex |

Không có role assignment đồng nghĩa không có write permission cho source.

## Ma trận quyền

| Hành động | Human Owner | Planning Authority | Writer | Reviewer | Tool tự thân |
|---|---:|---:|---:|---:|---:|
| Draft vision/roadmap/contract | Có | Có | Đề xuất finding | Đề xuất finding | Không |
| Approve plan/scope | Có | Không | Không | Không | Không |
| Gán Writer/Reviewer | Có | Không | Không | Không | Không |
| Sửa source trong allowlist | Theo quyết định | Không | Có, khi approved | Không | Không |
| Self-repair trong scope | Theo quyết định | Không | Có | Không | Không |
| Independent review | Theo quyết định | Hỗ trợ | Không | Có | Không |
| Gate decision | Theo quyết định cuối | Không | Không | Có | Không |
| Commit/merge/push | Có | Không | Không | Không | Không |
| Deploy/release/rollback | Có | Không | Không | Không | Không |
| Đổi `MERGED`/`CLOSED` | Có | Không | Không | Không | Không |

## Execution Writer

Điều kiện hợp lệ:

- Checkpoint là `PLAN_APPROVED`.
- Human đã ghi Writer cụ thể trong contract/ACTIVE.
- Writer đọc governance, contract và tool adapter.
- Working tree/base SHA khớp contract.
- Allowlist, forbidden paths và tests rõ ràng.

Writer chịu trách nhiệm implementation, test, evidence và handoff; không chịu
trách nhiệm gate hay release.

## Independent Reviewer

Điều kiện hợp lệ:

- Reviewer khác Writer.
- Phiên review độc lập, không kế thừa giả định của Writer.
- Candidate commit xác định được.
- Contract, handoff và evidence tồn tại.
- Reviewer không có source write permission trong phiên review.

Reviewer xác minh, không sửa. Nếu finding cần thay đổi source, kết luận
`REWORK_REQUIRED` và trả về Writer.

## Planning Authority

Planning Authority có thể tạo proposal và sửa draft theo feedback. Planning
Authority không được dùng quyền lập kế hoạch để tự cấp quyền execution hoặc gate.

## Role conflict

- Một người/phiên không được vừa Writer vừa Reviewer của cùng candidate.
- Tool transition không thay đổi role.
- Reviewer chuyển sang sửa code phải kết thúc review session; Human gán lại role
  và cần reviewer độc lập mới.
- Khi metadata role thiếu hoặc mâu thuẫn: `BLOCKED — ROLE_UNRESOLVED`.

## Ghi role assignment

Một assignment hợp lệ cần:

```yaml
checkpoint_id: CP-XX.X
member: <identity>
role: WRITER_OR_REVIEWER
tool: <tool>
assigned_by: <human owner>
assigned_at: <timestamp>
approval_record: <repository path or commit>
role_assignment_ref: <docs/approvals/CP-XX.X-ROLE-ASSIGNMENT.md or UNRESOLVED>
```

Ở lifecycle actionable, `assigned_by` phải là Human Project Owner cụ thể,
`assigned_at` phải là timestamp ISO-8601 có timezone và approval phải parse được
từ `docs/approvals/`. `approval_record` bằng `role_assignment_ref`, trừ khi một
record `PLAN_APPROVED` đặt `covers_assignments: true` và chứa snapshot assignment
khớp tuyệt đối; khi đó `approval_record` trỏ tới plan approval và
`role_assignment_ref` có thể là `UNRESOLVED`.

Không dùng `AI`, `team`, `developer` hoặc “người đang làm” như identity mơ hồ.

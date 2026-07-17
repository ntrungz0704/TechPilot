# ADR-NNN: Tên quyết định

> Sao chép template này thành ADR-NNN-short-title.md. Không chỉnh template thành một quyết định cụ thể.

## Quy tắc thẩm quyền

- ADR mới bắt đầu ở trạng thái Proposed.
- Chỉ một người phê duyệt có thẩm quyền, được nêu tên trong ADR, mới được đổi trạng thái sang Accepted hoặc Rejected.
- AI, automation, agent, tác nhân triển khai hoặc người chỉ viết proposal không được tự phê duyệt ADR.
- Code đã được viết, test đã pass, commit đã tồn tại hoặc working tree đã chứa thay đổi không phải là bằng chứng phê duyệt.
- Không để Owner, Approver, approval evidence hoặc commit ở dạng “TBD” khi chuyển sang Accepted.

## Metadata

| Trường | Giá trị |
| --- | --- |
| ADR | ADR-NNN |
| Tiêu đề | Tên quyết định |
| Status | Proposed |
| Owner | Tên người chịu trách nhiệm |
| Approver(s) | Tên người có thẩm quyền phê duyệt |
| Approval required from | Product / Engineering / Security / Operations / DBA / khác |
| Approval evidence | Link issue/PR/biên bản hoặc chữ ký có thể kiểm chứng; để trống khi Proposed |
| Approval date | YYYY-MM-DD; để trống khi Proposed |
| Decision date | YYYY-MM-DD; để trống khi Proposed |
| Baseline commit reviewed | Full commit SHA |
| Target implementation commit | Dự kiến hoặc để trống khi Proposed |
| Actual implementation commit | Full commit SHA sau triển khai; không phải approval evidence |
| Scope | Repository/module/runtime/data/deployment bị ảnh hưởng |
| Related issue/PR | Link hoặc ID |
| Supersedes | ADR cũ hoặc None |
| Superseded by | ADR mới hoặc None |

Status hợp lệ:

- Proposed: đang lấy ý kiến, chưa được phép coi là architecture baseline;
- Accepted: có human approval evidence và được phép triển khai trong scope;
- Rejected: người có thẩm quyền từ chối;
- Superseded: đã được ADR khác thay thế;
- Deprecated: quyết định không còn được khuyến nghị nhưng cần kế hoạch chuyển tiếp.

## 1. Tóm tắt quyết định đề xuất

Mô tả ngắn gọn điều đang được đề xuất. Khi Status còn là Proposed, dùng ngôn ngữ “đề xuất”, không viết như thể quyết định đã được chấp nhận.

## 2. Bối cảnh và bằng chứng

### 2.1 Vấn đề cần giải quyết

Mô tả vấn đề, người dùng/hệ thống bị ảnh hưởng và lý do cần quyết định ở cấp kiến trúc.

### 2.2 Baseline đã commit

- Commit được kiểm tra:
- File/line hoặc behavior làm bằng chứng:
- Public/data/session contracts hiện tại:

Chỉ ghi fact có thể kiểm chứng trong commit baseline.

### 2.3 Working-tree candidate, nếu có

- File modified/untracked:
- Hành vi candidate:
- Điểm chưa được kiểm chứng:

Candidate không được mô tả là architecture đã accepted.

### 2.4 Giả định và điều chưa biết

- Giả định:
- Câu hỏi mở:
- Bằng chứng còn thiếu:

## 3. Decision drivers

- Driver 1:
- Driver 2:
- Security/reliability/data constraints:
- Compatibility constraints:
- Time/cost/operational constraints:

## 4. Các phương án được xem xét

Luôn bao gồm phương án giữ nguyên hiện trạng.

### Option A: Giữ nguyên

- Mô tả:
- Lợi ích:
- Bất lợi:
- Rủi ro:
- Khi nào phù hợp:

### Option B: Phương án đề xuất

- Mô tả:
- Lợi ích:
- Bất lợi:
- Rủi ro:
- Khi nào phù hợp:

### Option C: Phương án khác

- Mô tả:
- Lợi ích:
- Bất lợi:
- Rủi ro:
- Khi nào phù hợp:

## 5. Quyết định

Status hiện tại: Proposed

Phương án đề xuất:

Lý do:

Điều kiện phải thỏa trước khi Accepted:

- [ ] Owner đã được nêu tên.
- [ ] Approver có thẩm quyền đã được nêu tên.
- [ ] Scope và out-of-scope rõ ràng.
- [ ] Ảnh hưởng MVC và public/data contracts đã được đánh giá.
- [ ] Security, migration, testing và rollback đã được đánh giá theo rủi ro.
- [ ] Approval evidence do con người cung cấp đã được ghi nhận.

Không đổi câu “Status hiện tại” hoặc Metadata sang Accepted nếu thiếu bất kỳ điều kiện bắt buộc nào.

## 6. Scope

### In scope

- Module/file/contract:
- Data/deployment:
- Người dùng hoặc use case:

### Out of scope

- Không thay đổi:
- Đề xuất cần ADR khác:

## 7. Ảnh hưởng tới custom PHP MVC

Không mặc định rằng quyết định phải tạo Service, Repository, Clean Architecture hoặc DDD layer. Nếu thêm abstraction, phải giải thích responsibility cụ thể và vì sao convention hiện tại không đủ.

| Khu vực | Không đổi / Thay đổi | Ảnh hưởng và compatibility |
| --- | --- | --- |
| public/ web root |  |  |
| public/index.php / front controller |  |  |
| Router và public URL |  |  |
| Controller/action và HTTP method |  |  |
| Models/PDO/schema |  |  |
| Views/layouts/helpers |  |  |
| Session/auth/CSRF/RBAC |  |  |
| Validation/output encoding |  |  |
| Optional coordination helper/service |  |  |
| Static assets/client JavaScript |  |  |
| Error/config/operations |  |  |
| Tests/verification |  |  |

### Dependency impact

- Dependency direction trước:
- Dependency direction sau:
- Coupling mới:
- Abstraction mới, nếu có:
- Lý do abstraction là tùy chọn hay bắt buộc trong scope này:

### Public contract impact

- Routes thêm/xóa/đổi:
- HTTP method thay đổi:
- Redirect/canonical URL:
- View-data/session keys:
- Backward compatibility:

## 8. Data và migration impact

- Bảng/cột/index/constraint bị ảnh hưởng:
- Source of truth trước/sau:
- Existing-data migration:
- Backfill/reconciliation:
- Transaction/concurrency behavior:
- Backup prerequisite:
- Rollback hoặc roll-forward:
- Destructive operation:

Nếu không ảnh hưởng data, ghi “Không”.

## 9. Security và privacy impact

- Authentication:
- Authorization:
- Session/cookies:
- CSRF:
- Input validation:
- Output encoding:
- Secrets/config:
- Personal data/logging:
- Threats còn lại:

Nếu không có security review riêng, nêu rõ lý do và người chịu trách nhiệm chấp nhận rủi ro.

## 10. Consequences

### Positive

- UNRESOLVED

### Negative / trade-offs

- UNRESOLVED

### Risks accepted

- Rủi ro:
- Người chấp nhận:
- Ngày và approval evidence:

### Follow-up work

- UNRESOLVED

## 11. Verification plan

### Automated checks

- Unit:
- Integration:
- Static/lint:
- Migration/data assertions:

### Manual checks

- Route/use case:
- Environment:
- Expected evidence:

### Acceptance criteria

- [ ] Tiêu chí 1
- [ ] Tiêu chí 2
- [ ] Không làm thay đổi ngoài scope

## 12. Rollout và rollback

- Rollout sequence:
- Feature flag/gating, nếu có:
- Monitoring:
- Abort conditions:
- Rollback steps:
- Data recovery:
- Người có quyền quyết định rollback:

## 13. Implementation plan

1. Bước:
2. Bước:
3. Bước:

Implementation chỉ bắt đầu trước Accepted khi owner cho phép rõ đây là prototype không deploy; prototype vẫn không phải approval.

## 14. Human approval record

| Approver | Vai trò/thẩm quyền | Decision | Ngày | Approval evidence |
| --- | --- | --- | --- | --- |
|  |  | Pending |  |  |

Quy tắc:

- Ít nhất một dòng phải có tên người thật/có trách nhiệm, Decision là Approve hoặc Reject và evidence có thể kiểm chứng.
- Tác nhân AI/automation không được điền tên mình làm approver.
- Không suy diễn approval từ silence, commit, merge, test result hoặc việc giao task triển khai.

## 15. Post-implementation verification

Chỉ điền sau triển khai:

- Actual implementation commit:
- Verification run:
- Evidence:
- Sai lệch so với ADR:
- Follow-up ADR cần tạo:
- Người xác nhận implementation:
- Ngày:

## 16. Review và supersession

- Review date:
- Review owner:
- Trigger cần review sớm:
- ADR thay thế quyết định này:

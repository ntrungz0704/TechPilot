# AGENTS.md — Quy tắc bắt buộc của repository

File này áp dụng cho mọi Human contributor và mọi AI coding tool trong toàn bộ
repository. Tool-specific prompt không được ghi đè file này.

## 1. Snapshot trạng thái

Danh sách dưới là snapshot tiện tra cứu ngày `2026-07-16`. Bảng trạng thái live
canonical duy nhất là `docs/checkpoints/ACTIVE.md`. Nếu snapshot và `ACTIVE.md`
khác nhau, ưu tiên `ACTIVE.md` và báo `BLOCKED — GOVERNANCE_CONFLICT`.

- Canonical baseline: `main@1ae679461e1f709488155ebf275ef070b54d723a`.
- Active phase: `UNRESOLVED`.
- Active checkpoint: `NO_ACTIVE_CHECKPOINT`.
- Human Project Owner: `UNRESOLVED`.
- Writer: `UNASSIGNED`.
- Reviewer: `UNASSIGNED`.
- Approval: `HUMAN_PLAN_APPROVAL_REQUIRED`.
- Có thay đổi source tồn tại sẵn trong working tree nhưng chưa có contract hoặc
  review xác minh.

Hệ quả: không agent nào có quyền sửa source sản phẩm cho đến khi repository ghi
một checkpoint `PLAN_APPROVED`, role assignment và allowlist hợp lệ.

## 2. Thứ tự authority

Đối với plan và scope, ưu tiên:

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

Nếu hai tài liệu cùng cấp mâu thuẫn, báo `BLOCKED — GOVERNANCE_CONFLICT`.

Đối với implementation, source, test, runtime evidence, Git diff và reviewed SHA
là bằng chứng kỹ thuật. Bằng chứng không tự thay đổi approved scope. Nếu code và
contract mâu thuẫn, ghi finding; không mặc định code đúng hơn contract.

## 3. Mô hình quyền hạn

- **Human Project Owner:** quyết định cuối cùng, phê duyệt plan/scope/architecture,
  gán Writer/Reviewer và độc quyền commit, merge, push, deploy, publish, release,
  rollback, `MERGED`, `CLOSED`, credential, secret và destructive action.
- **Repository:** nơi lưu trạng thái chính thức; chat không phải trạng thái.
- **ChatGPT Work / Planning Authority:** phân tích WHY, đề xuất WHAT, soạn vision,
  roadmap, phase, checkpoint và gate; không tự approve.
- **Execution Writer:** thực hiện HOW trong checkpoint được duyệt; chỉ sửa
  `allowed_paths`; dừng tại `READY_FOR_REVIEW`.
- **Independent Reviewer:** phiên độc lập, chỉ đọc source khi review, tự rerun
  validation và đưa ra gate decision gắn với đúng commit SHA.
- **Tool:** chỉ là phương tiện. Capability không đồng nghĩa với permission.

Chi tiết: `docs/governance/AUTHORITY_MODEL.md` và
`docs/governance/ROLE_MODEL.md`.

## 4. Session bắt buộc

Trước khi sửa bất kỳ file nào:

1. Chạy `git status --short`.
2. Đọc branch và `git rev-parse HEAD`.
3. Đọc file này, `ACTIVE.md`, contract và tài liệu architecture liên quan.
4. Xác nhận member, role, tool, write permission, allowlist và forbidden paths.
5. Kiểm tra handoff/review/evidence và candidate/reviewed SHA.
6. Xuất startup report theo `docs/governance/SESSION_PROTOCOL.md`.

Không có startup report thì không được sửa file.

## 5. Quy tắc Writer

Writer phải:

- Xác minh checkpoint là `PLAN_APPROVED` và chính mình được gán làm Writer.
- Nêu scope, file dự kiến sửa và required tests trước khi sửa.
- Chỉ sửa `allowed_paths`; không sửa unrelated code.
- Giữ kiến trúc PHP MVC thuần đang có. Architecture phải được khám phá, không
  được giả định.
- Không tự thêm Domain/Application/Port/Adapter/Repository/Use Case layer.
- Chỉ dùng Service, Repository, Validator hoặc DTO nếu repo đã dùng hoặc checkpoint
  phê duyệt rõ.
- Không thêm dependency, route, schema, API, auth hay feature ngoài scope.
- Chạy test, lưu evidence, tạo handoff và đối chiếu handoff với Git diff.
- Kết thúc ở `READY_FOR_REVIEW`; không tự `GATE_PASS`.

Nếu handoff chưa có candidate SHA, Writer ghi rõ `CANDIDATE_COMMIT_PENDING_HUMAN_COMMIT`.
Human Project Owner materialize candidate commit và cập nhật full SHA trong
canonical state. Writer không tự tạo commit để lấp trường này.

Nếu cần sửa ngoài scope: `BLOCKED — OUT_OF_SCOPE_CHANGE_REQUIRED`.

## 6. Quy tắc Reviewer

Reviewer phải:

- Khác Writer và dùng phiên độc lập.
- Không sửa source, test hoặc finding trong phiên review.
- Đọc contract trước khi đọc báo cáo Writer.
- Xác minh branch, candidate SHA, diff, allowlist, forbidden paths, architecture,
  MVC, dependency, tests, evidence và handoff.
- Không thêm acceptance criteria mới.
- Chỉ kết luận `GATE_PASS`, `REWORK_REQUIRED` hoặc `BLOCKED`.
- Ghi rõ reviewed SHA. Source đổi sau review làm review mất hiệu lực.

Reviewer chỉ bắt đầu gate review sau khi Human đã materialize candidate commit và
canonical state có full candidate SHA. Review sơ bộ trên working-tree patch chỉ là
advisory review; không được `GATE_PASS`.

## 7. Bảo vệ MVC hiện tại

Repository dùng một biến thể MVC PHP thuần, không có framework được khai báo.

- Controller nhận request và điều phối theo convention hiện có.
- Model giữ persistence/data behavior theo convention hiện có.
- View chịu trách nhiệm presentation, không tự quyết định authorization hoặc truy
  cập database mới nếu convention không cho phép.
- Route giữ URL/HTTP contract hiện có; không đổi ngoài checkpoint.
- Layer tùy chọn chỉ được dùng khi đã tồn tại hoặc được Human phê duyệt.

Không tự chuyển sang DDD, Clean, Hexagonal, Onion, CQRS, Event Sourcing,
microservices hoặc Ports and Adapters.

## 8. Git, release và dữ liệu nhạy cảm

- AI không được stage, commit, merge, push, deploy, publish, release hoặc rollback.
- Không direct push vào `main`; mọi source change cần PR, independent review và CI.
- Không tạo, đọc ra màn hình hoặc commit credential/secret.
- Không chạy migration phá hủy, import schema xóa dữ liệu hoặc action production
  khi chưa có Human approval rõ ràng.
- Không dùng `git reset --hard`, force push hoặc xóa branch để xử lý conflict.
- Nếu lỗi nghiêm trọng được phát hiện sau `MERGED`, báo
  `ROLLBACK_REQUIRED`, đóng băng release action tiếp theo và chuyển evidence cho
  Human Project Owner. AI không tự rollback.

## 9. Không được làm yếu governance

Không agent nào được sửa `AGENTS.md`, `ACTIVE.md`, contract, review, script hoặc CI
để làm nhiệm vụ dễ hơn, hợp thức hóa diff cũ hay bỏ qua gate. Thay đổi governance
cần checkpoint riêng, Human approval và review độc lập.

Nếu quy tắc này chặn công việc, dừng và báo Human; không tự sửa quy tắc.

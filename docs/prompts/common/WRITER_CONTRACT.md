# Execution Writer Contract

Writer sở hữu HOW trong checkpoint đã **PLAN_APPROVED**. Writer không sở hữu scope, gate hoặc release.

## Trước khi viết

1. Chạy Common Session Start.
2. Xác nhận mình là assigned Writer trong ACTIVE.
3. Xác nhận Reviewer là người/phiên độc lập.
4. Xác nhận checkpoint PLAN_APPROVED, base SHA và contract path hợp lệ.
5. Tóm tắt scope bằng ngôn ngữ dễ hiểu.
6. Liệt kê file dự kiến sửa và đối chiếu allowed paths.
7. Liệt kê required tests/evidence.
8. Đưa execution plan khớp contract; chỉ sau đó mới sửa file.

## Trong khi thực thi

- Chỉ sửa allowed paths và thay đổi thuộc acceptance criteria.
- Giữ kiến trúc/convention MVC được phát hiện trong repository.
- Không tự thêm feature, dependency, integration, database/API/auth change hoặc abstraction.
- Không tự tạo Service, Repository, Domain, Use Case, Port hoặc Adapter vì “best practice”.
- Không sửa unrelated code, governance hoặc acceptance criteria để làm task dễ hơn.
- Không sửa test để che lỗi production.
- Không dùng credential, MFA hoặc destructive action.
- Chạy validation, self-repair trong scope và thu thập evidence.
- Khi đổi tool: pull canonical repository, chạy doctor, đọc lại contract, giữ nguyên role và ghi transition.

Nếu cần file ngoài allowlist:

    BLOCKED — OUT_OF_SCOPE_CHANGE_REQUIRED

Nếu cần sửa governance:

    BLOCKED — GOVERNANCE_CHANGE_REQUIRED

## Trước khi bàn giao

1. Chạy toàn bộ required tests và ghi command/result/exit code.
2. Lập full changed-file list từ Git, không dựa vào trí nhớ.
3. So sánh changed files với allowlist/forbidden paths.
4. Tạo evidence và docs/handoffs theo template.
5. So sánh handoff với Git diff.
6. Ghi candidate SHA; nếu chưa có commit, dùng diff/patch hash hoặc CANDIDATE_COMMIT_PENDING_HUMAN_COMMIT.
7. Kết thúc tại **READY_FOR_REVIEW**.

Writer không tự GATE_PASS, commit, merge, push, deploy, publish hoặc release.


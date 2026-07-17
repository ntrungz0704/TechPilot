# Codex Tool Adapter

## Identity

- Tool name: Codex
- Adapter status: DRAFT — Human confirmation required
- Evidence of use: repository governance setup session on 2026-07-16
- Role source: docs/checkpoints/ACTIVE.md, never the tool itself

## Capability matrix

    tool:
      capabilities:
        read_repository: true
        edit_files: true
        run_commands: true
        run_tests: true
        create_commits: true
        push_remote: environment_dependent
        parallel_agents: true

    repository_permissions:
      read_repository: true
      edit_allowed_paths: only_when_assigned_writer_and_plan_approved
      run_tests: when_non_destructive_and_in_contract
      create_commits: false
      push_remote: false
      merge: false
      deploy: false

Capability không đồng nghĩa với permission.

## Start a Codex session

1. Yêu cầu Codex đọc AGENTS.md, ACTIVE, contract và adapter này.
2. Yêu cầu chạy Common Session Start và xuất đầy đủ startup report.
3. Không cho phép chỉnh file cho tới khi report xác nhận role, lifecycle và allowlist.
4. Nếu là Writer, nạp Writer Contract. Nếu là Reviewer, dùng phiên Codex độc lập và nạp Reviewer Contract.
5. Yêu cầu Codex dùng Git diff làm căn cứ cho changed-file list.

## Reading, terminal and write limits

- Codex có thể đọc repository và chạy command không phá hủy để xác minh trạng thái.
- Mọi write phải nằm trong allowed paths của contract PLAN_APPROVED.
- Không cho Codex tự reset, clean, stash, rebase, xóa hoặc ghi đè local change không rõ nguồn.
- Không cho auto-commit/auto-push; nhắc rõ “do not commit, merge, push or deploy”.
- Credential, MFA, destructive migration và production action luôn chuyển về Human.
- Parallel agent chỉ được dùng cho subtask có scope tách biệt; không tạo thêm Writer hoặc cho Reviewer sửa source.

## Output

- Writer xuất handoff theo docs/handoffs/TEMPLATE.md và dừng ở READY_FOR_REVIEW.
- Reviewer xuất review theo docs/reviews/TEMPLATE.md và không sửa finding.
- Nếu đổi khỏi Codex, ghi tool transition, pull mới nhất, chạy doctor và đọc lại repository; role không đổi.


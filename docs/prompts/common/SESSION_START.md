# Common Session Start

Áp dụng cho mọi Human contributor và AI coding tool trước khi đọc theo ký ức cũ hoặc sửa file.

## Read order

1. Chạy Git status, đọc branch và HEAD.
2. Đọc AGENTS.md.
3. Đọc docs/checkpoints/ACTIVE.md.
4. Đọc active checkpoint contract; nếu không có, dừng.
5. Đọc handoff, review và evidence hiện có.
6. Đọc ADR và architecture documents liên quan.
7. Đọc source và tests thuộc scope.
8. Xác định role và tool adapter; tool không tự tạo role.
9. So sánh base, candidate, reviewed commit và local HEAD.

## Commands

    git status --short
    git branch --show-current
    git rev-parse HEAD
    python scripts/repo_doctor.py

Không sửa file trước khi repo doctor và startup report hoàn tất.

## Mandatory startup report

    Repository Status:
    Current Branch:
    Current HEAD:
    Working Tree:
    Active Phase:
    Active Checkpoint:
    Lifecycle Status:
    Assigned Writer:
    Assigned Reviewer:
    Current Member:
    Current Role:
    Current Tool:
    Write Permission:
    Allowed Paths:
    Forbidden Paths:
    Forbidden Changes:
    Required Tests:
    Required Evidence:
    Base Commit:
    Candidate Commit:
    Reviewed Commit:
    Required Next Action:
    Blocking Issues:

## Required comparisons

- Base commit phải tồn tại và đúng với contract.
- Candidate commit phải tồn tại trước independent review; nếu Human chưa commit thì Writer chỉ ghi CANDIDATE_COMMIT_PENDING_HUMAN_COMMIT.
- GATE_PASS chỉ hợp lệ khi reviewed commit bằng candidate commit đã kiểm tra.
- Nếu source đổi sau review, báo **REVIEW_INVALIDATED**.
- Nếu hai tài liệu cùng authority mâu thuẫn, báo **BLOCKED — GOVERNANCE_CONFLICT**.
- Nếu tool prompt mâu thuẫn contract, contract thắng.
- Nếu branch/HEAD/working tree stale hoặc không rõ nguồn, báo BLOCKED; không tự dọn, stash hoặc reset.

Conversation memory is not canonical repository state.


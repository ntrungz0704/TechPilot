# Independent Reviewer Contract

Reviewer là Gate Authority cho đúng checkpoint và đúng reviewed commit, không phải implementation hoặc release authority.

## Điều kiện độc lập

- Dùng phiên độc lập với Writer.
- Được Human chỉ định trong ACTIVE.
- Không sửa source, test hoặc governance trong phiên review.
- Không tự chữa finding; trả finding về Writer.

## Quy trình

1. Chạy Common Session Start và xác nhận role.
2. Đọc contract trước khi đọc handoff hoặc tin lời Writer.
3. Xác minh branch, candidate SHA và Git diff.
4. Kiểm tra tất cả changed files thuộc allowlist và không thuộc forbidden paths.
5. Kiểm tra handoff/evidence khớp Git thực tế.
6. Chạy lại required tests độc lập và ghi result/exit code.
7. Kiểm tra từng acceptance criterion, scope, architecture và MVC convention.
8. Kiểm tra dependency, database, API, auth, route/public contract và architecture change.
9. Phân loại blocking/non-blocking findings.
10. Ghi review record, reviewed commit và decision limitations.

Nếu Reviewer bị yêu cầu ngoài contract:

    BLOCKED — REVIEW_SCOPE_CONFLICT

## Gate decision

Chỉ dùng:

- **GATE_PASS**
- **REWORK_REQUIRED**
- **BLOCKED**

GATE_PASS phải ghi:

    This Gate PASS applies only to reviewed commit: <SHA>.
    Any subsequent source change invalidates this review.

Reviewer không thêm acceptance criterion mới, không approve commit khác, không commit, merge, push, deploy hoặc release.


# Common Session Protocol

Protocol này áp dụng cho mọi Human contributor và AI tool.

## 1. Startup gate

Trước khi sửa file, thực hiện:

1. `git status --short`
2. `git branch --show-current`
3. `git rev-parse HEAD`
4. Đọc `AGENTS.md`.
5. Đọc `docs/checkpoints/ACTIVE.md`.
6. Đọc checkpoint contract được trỏ tới.
7. Đọc handoff/review/evidence hiện có.
8. Đọc architecture documents và accepted ADR liên quan.
9. Xác định member, role và tool.
10. Xác định write permission, allowed/forbidden paths.
11. So sánh base, candidate và reviewed commit.
12. Báo stale state, dirty tree và governance conflict.

Không sửa file trước khi xuất startup report.

## 2. Startup report bắt buộc

```text
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
Required Next Action:
Blocking Issues:
```

Không dùng “same as last session”. Mỗi trường phải được đọc lại từ repository.

## 3. Quyết định write permission

Write permission chỉ là `YES` khi đồng thời:

- Checkpoint là `PLAN_APPROVED`.
- Current member trùng assigned Writer.
- Contract tồn tại và parse được.
- Base/HEAD/working tree tương thích contract.
- Allowlist không rỗng và file dự kiến sửa nằm trong allowlist.
- Không có governance conflict hoặc review-only role.

Thiếu một điều kiện thì `Write Permission: NO`.

## 4. Trong session Writer

- Tóm tắt contract bằng ngôn ngữ đơn giản.
- Liệt kê file dự kiến sửa và test bắt buộc.
- Kiểm tra diff thường xuyên để phát hiện file ngoài scope.
- Không hấp thụ thay đổi tồn tại sẵn vào handoff của mình.
- Khi cần ngoài scope, dừng và báo đúng blocker.
- Ghi tool transition nếu đổi công cụ.
- Trước kết thúc, chạy test, lưu evidence, tạo handoff và so sánh với Git diff.

## 5. Trong session Reviewer

- Xác minh review-only, không source write.
- Xác minh Human đã materialize candidate commit và canonical state có full SHA.
  Candidate `PENDING`/`UNRESOLVED` không đủ điều kiện gate review.
- Đọc contract trước handoff.
- Kiểm tra exact candidate SHA và independent diff.
- Rerun test thay vì chỉ chép kết quả Writer.
- Ghi blocking/non-blocking findings và gate decision.
- Không sửa source để “tiện hoàn tất”.

Review sơ bộ trên working-tree patch có thể cung cấp advisory findings, nhưng
không được đưa `GATE_PASS`, không tạo reviewed SHA và không mở merge/release.

## 6. Candidate materialization bắt buộc

Sau Writer handoff:

1. Writer dừng ở `READY_FOR_REVIEW`; nếu chưa có SHA, ghi
   `CANDIDATE_COMMIT_PENDING_HUMAN_COMMIT`.
2. Human Project Owner kiểm tra handoff/diff và stage đúng phạm vi.
3. Human tạo candidate commit; AI không thực hiện bước này.
4. Human ghi full candidate SHA vào canonical repository state. Vì SHA chỉ có sau
   commit, metadata có thể nằm trong một canonical follow-up record nhưng phải trỏ
   chính xác candidate source commit.
5. Reviewer checkout/đọc đúng candidate SHA rồi mới bắt đầu independent gate.

Thiếu bước này: `BLOCKED — CANDIDATE_COMMIT_REQUIRED`.

## 7. Working tree bẩn hoặc stale

Khi có thay đổi không thuộc checkpoint:

1. Không reset, stash, move, delete hoặc stage nếu Human chưa chỉ dẫn.
2. Ghi danh sách file và nguồn gốc nếu biết.
3. Xác định có thể audit/tách phạm vi an toàn không.
4. Nếu không thể chứng minh ownership hoặc candidate sạch, dừng `BLOCKED`.

Baseline hiện tại đã biết có dirty source tồn tại trước governance. Điều này phải
hiện trong mọi startup report cho đến khi Human giải quyết.

## 8. Kết thúc session

### Writer report

- Lifecycle đề xuất: `READY_FOR_REVIEW` hoặc `BLOCKED`.
- File thực sự thay đổi.
- Test command/result/exit code.
- Evidence và handoff path.
- Candidate SHA hoặc `CANDIDATE_COMMIT_PENDING_HUMAN_COMMIT`.
- Remaining risk/blocker.
- Không tự Gate PASS.

### Reviewer report

- Reviewed branch/commit.
- Verification đã chạy.
- Findings.
- `GATE_PASS`, `REWORK_REQUIRED` hoặc `BLOCKED`.
- Giới hạn: decision chỉ áp dụng reviewed SHA.

### Sau merge

- Nếu phát hiện release không an toàn, báo `ROLLBACK_REQUIRED` cùng merged/deployed
  SHA, triệu chứng, evidence và rollback target đề xuất.
- Chỉ Human Project Owner được ghi quyết định và thực hiện rollback.

### Planning session

- Tài liệu vẫn `DRAFT`/`PLAN_REVIEW` nếu chưa có Human approval.
- Liệt kê quyết định Human cần đưa ra.
- Không mở execution.

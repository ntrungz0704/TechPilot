# Đóng góp cho TechPilot

Mọi đóng góp, dù nhỏ, đều phải thuộc một checkpoint rõ ràng. Task nhỏ dùng
checkpoint nhỏ; không dùng scope mơ hồ.

## Điều kiện trước khi bắt đầu

Hiện repository chưa có active checkpoint hoặc role assignment. Vì vậy chưa ai
được phép bắt đầu source work. Human Project Owner cần xử lý các mục trong
`docs/governance/BASELINE_AUDIT.md` trước.

Khi đã có checkpoint hợp lệ, dùng luồng sau:

```text
Pull latest repository
→ Run repo doctor
→ Read ACTIVE
→ Confirm member, role and tool
→ Create checkpoint branch
→ Run common session protocol
→ Apply role contract
→ Apply tool adapter
→ Writer executes and runs validation
→ Writer produces handoff (`candidate SHA` có thể pending)
→ Human materializes candidate commit
→ Human records full candidate SHA in canonical state
→ Independent Reviewer begins review
→ Reviewer produces gate decision
→ Open Pull Request
→ Human merge and close
```

## 1. Chuẩn bị session

1. Không bắt đầu trên working tree bẩn chưa rõ nguồn gốc.
2. Chạy:

   ```bash
   python scripts/repo_doctor.py
   ```

3. Đọc `AGENTS.md`, `docs/checkpoints/ACTIVE.md`, contract, architecture và prompt
   theo role.
4. Xác nhận Writer và Reviewer là hai người/phiên khác nhau.
5. Xuất startup report trước khi sửa.

Working tree baseline đang chứa source changes tồn tại từ trước. Không stage,
reset, ghi đè hoặc nhận chúng làm thay đổi của checkpoint mới.

## 2. Branch

Repository chưa có convention được Human phê duyệt. Convention đề xuất trong lúc
chờ duyệt:

```text
cp/CP-XX.X-short-name
review/CP-XX.X-gate-review
fix/CP-XX.X-review-findings
docs/governance-update
```

Tên branch không tạo quyền thực thi. Branch phải khớp checkpoint và được tạo theo
chỉ dẫn của Human Project Owner. Không làm trực tiếp trên `main`.

## 3. Writer

- Chỉ bắt đầu khi checkpoint là `PLAN_APPROVED`.
- Chỉ sửa `allowed_paths`; không đụng `forbidden_paths`.
- Không thay đổi scope, architecture, dependency, database, API hoặc auth ngoài
  contract.
- Chạy chính xác required tests; không sửa test để che lỗi.
- Lưu evidence theo đường dẫn trong contract.
- Tạo handoff và liệt kê đầy đủ file thực sự thay đổi.
- Dừng tại `READY_FOR_REVIEW`.
- Không stage, commit, push hoặc tự Gate PASS.

Nếu chưa có commit, handoff phải ghi
`CANDIDATE_COMMIT_PENDING_HUMAN_COMMIT`. Writer không tự tạo hoặc giả mạo SHA.

## 4. Candidate materialization gate

Sau Writer handoff và trước independent review:

1. Human Project Owner đối chiếu handoff với diff.
2. Human stage đúng checkpoint files và tạo candidate commit.
3. Human ghi full candidate SHA vào canonical state/handoff reference.
4. Xác nhận candidate có thể checkout và review độc lập.
5. Chỉ sau đó Reviewer mới bắt đầu gate review.

Một review sơ bộ trên patch hoặc working tree chưa materialize chỉ là advisory
feedback. Nó không được dùng để kết luận `GATE_PASS`, không được merge và không
thay thế review đúng commit SHA.

## 5. Reviewer

- Dùng phiên độc lập và không sửa source.
- Chỉ bắt đầu khi candidate commit đã được Human tạo và canonical state có full
  SHA; giá trị `PENDING`/`UNRESOLVED` không hợp lệ cho gate review.
- Review đúng candidate SHA, không chỉ tin handoff.
- Kiểm tra contract, diff, allowed/forbidden paths, MVC, dependency và evidence.
- Tự chạy lại required tests.
- Ghi review theo `docs/reviews/TEMPLATE.md`.
- Chỉ kết luận `GATE_PASS`, `REWORK_REQUIRED` hoặc `BLOCKED`.

`GATE_PASS` chỉ áp dụng cho đúng reviewed SHA. Source thay đổi sau đó phải review
lại.

## 6. Pull Request

PR phải dùng `.github/pull_request_template.md` và có tối thiểu:

- Checkpoint/phase/role/tool.
- Contract, base SHA và candidate SHA.
- Scope summary và changed files.
- Required tests, kết quả và evidence.
- Handoff hoặc independent review.
- Dependency/architecture/MVC impact.
- Tool transition nếu có.
- Human actions required.

PR không được tự coi là approved vì CI xanh. Human Project Owner vẫn giữ release
authority.

## 7. Commit, merge và release

AI agent không được commit, merge, push, deploy, publish, release hoặc rollback.
Human Project Owner kiểm tra đúng reviewed SHA rồi mới thực hiện các hành động đó.

Sau merge, Human cập nhật canonical state sang `MERGED`, rồi `CLOSED` khi mọi exit
condition đã đạt. Nếu exact merged release cần rollback, Human ghi
`ROLLBACK_REQUIRED`, thu evidence và quyết định rollback/follow-up; AI không tự
rollback. Không đóng checkpoint chỉ dựa vào chat.

## 8. Đổi công cụ

Đổi tool không đổi role hoặc scope. Pull mới nhất, chạy repo doctor, đọc lại
governance, kiểm tra Git state và ghi transition trong handoff. Context của tool
cũ không phải bằng chứng canonical.

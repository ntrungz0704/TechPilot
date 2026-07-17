# START HERE — Bắt đầu làm việc với TechPilot

Tài liệu này là lối vào duy nhất cho thành viên mới, vibe coder và AI agent.

## Snapshot trạng thái

Bảng dưới là snapshot tiện tra cứu ngày `2026-07-16`. Bảng canonical live duy
nhất là `docs/checkpoints/ACTIVE.md`; luôn đọc file đó trước khi hành động. Nếu
snapshot này khác `ACTIVE.md`, ưu tiên `ACTIVE.md` và báo conflict.

| Câu hỏi | Câu trả lời chính thức |
|---|---|
| Dự án đang ở phase nào? | `UNRESOLVED` |
| Checkpoint active là gì? | `NO_ACTIVE_CHECKPOINT` |
| Tôi là Writer hay Reviewer? | Chưa xác định; cả hai đang `UNASSIGNED` |
| Plan đã được duyệt chưa? | Chưa; `HUMAN_PLAN_APPROVAL_REQUIRED` |
| Tôi được sửa file nào? | Không có quyền sửa source khi chưa có checkpoint `PLAN_APPROVED` |
| Tôi phải chạy test nào? | Chưa có product tests vì chưa có product checkpoint active; governance checks vẫn bắt buộc theo `ACTIVE.md` |
| Canonical baseline là gì? | `main@1ae679461e1f709488155ebf275ef070b54d723a` |

Working tree hiện có thay đổi source tồn tại từ trước. Không tự tiếp tục, xóa,
khôi phục, stage hoặc gộp các thay đổi đó vào công việc mới.

## Luồng bắt buộc

```text
Clone hoặc pull repository
→ Chạy repo doctor
→ Đọc START_HERE.md
→ Đọc docs/checkpoints/ACTIVE.md
→ Đọc checkpoint contract
→ Xác nhận member và role
→ Chọn tool adapter
→ Dùng đúng role contract
→ Writer thực thi và tạo handoff
→ Human tạo candidate commit và cập nhật candidate SHA canonical
→ Reviewer độc lập mới bắt đầu gate review
→ Human thực hiện release action
```

Lệnh kiểm tra ban đầu:

```bash
python scripts/repo_doctor.py
```

Ở trạng thái hiện tại, repo doctor có thể trả `BLOCKED` vì chưa có approval và
assignment. Đây là hành vi đúng; không sửa governance để làm cho kết quả thành
`PASS`.

Không có product tests bắt buộc vì chưa có product checkpoint. Tuy nhiên snapshot
`ACTIVE.md` hiện yêu cầu chạy các governance checks sau khi sửa governance:

```bash
python scripts/repo_doctor.py
python scripts/governance_check.py
```

Luôn lấy danh sách command live từ `ACTIVE.md` và checkpoint contract; bảng trên
không thay thế chúng.

## Tôi phải đọc gì?

Theo thứ tự:

1. [AGENTS.md](AGENTS.md).
2. `docs/checkpoints/ACTIVE.md`.
3. Checkpoint contract được `ACTIVE.md` trỏ tới.
4. `docs/architecture/SYSTEM_ARCHITECTURE.md` và ADR liên quan.
5. [docs/governance/SESSION_PROTOCOL.md](docs/governance/SESSION_PROTOCOL.md).
6. `docs/prompts/common/SESSION_START.md`.
7. Contract của role: Writer hoặc Reviewer.
8. Adapter của công cụ đang dùng. Bằng chứng hiện tại chỉ xác nhận Codex.
9. Handoff, review và evidence liên quan nếu chúng tồn tại.

Nếu `ACTIVE.md` hoặc contract không tồn tại, không khớp hoặc cùng cấp mâu thuẫn,
dừng với `BLOCKED — GOVERNANCE_CONFLICT`.

## Tôi được làm gì?

- Chỉ Writer được chỉ định mới được sửa source, và chỉ khi checkpoint đã
  `PLAN_APPROVED`.
- Writer chỉ sửa `allowed_paths`, tránh `forbidden_paths`, chạy required tests,
  tạo evidence và dừng tại `READY_FOR_REVIEW`.
- Reviewer phải dùng phiên độc lập, không sửa source, tự kiểm tra diff/test và chỉ
  kết luận `GATE_PASS`, `REWORK_REQUIRED` hoặc `BLOCKED`.
- Thành viên chưa được gán role chỉ được đọc, audit và báo trạng thái.

Sau handoff, candidate SHA có thể còn pending vì AI/Writer không được commit.
Human Project Owner phải materialize candidate commit và ghi full SHA vào canonical
state trước khi Reviewer bắt đầu. Review sơ bộ trên patch chưa có commit chỉ mang
tính tư vấn và không được kết luận `GATE_PASS`.

Không có checkpoint active đồng nghĩa không có quyền sửa source.

## Tôi không được làm gì?

- Không tự mở phase hoặc checkpoint.
- Không tự mở rộng scope, thêm dependency, đổi schema, API, auth hoặc architecture.
- Không sửa governance để bỏ qua gate.
- Không stage, commit, merge, push, deploy, publish, release hoặc rollback.
- Không dùng secret, credential hoặc dữ liệu thật khi chưa có quyền của Human.
- Không dựa vào ký ức của phiên AI trước.

## Khi nào phải báo Human Project Owner?

Báo và dừng khi:

- Thiếu hoặc mơ hồ về Owner, Writer, Reviewer hay approval.
- Cần sửa ngoài `allowed_paths` hoặc đổi acceptance criteria.
- Cần dependency, integration, schema, API, auth hoặc architecture mới.
- Working tree có thay đổi lạ hoặc không thể tách khỏi checkpoint.
- Contract, `ACTIVE.md`, ADR hoặc code mâu thuẫn.
- Test cần credential, destructive action hoặc môi trường chưa được cấp.
- Candidate commit khác reviewed commit.
- Cần commit, merge, push, deploy, release hay rollback.
- Sau `MERGED`, phát hiện release không an toàn và cần chuyển
  `ROLLBACK_REQUIRED`; chỉ Human được quyết định và thực hiện rollback.

Conversation memory is not canonical repository state.

Nội dung trong cuộc trò chuyện trước không phải trạng thái chính thức nếu chưa
được Human phê duyệt và ghi vào repository. Khi không chắc chắn, dừng lại tốt hơn
tự đoán.

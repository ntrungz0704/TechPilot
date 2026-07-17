# Vibe Coder Playbook

Khi không chắc chắn, dừng lại tốt hơn tự đoán.

## Năm câu hỏi trước khi làm

### 1. Tôi đang làm nhiệm vụ nào?

Đọc `docs/checkpoints/ACTIVE.md` và checkpoint contract. Hiện tại là
`NO_ACTIVE_CHECKPOINT`, nên chưa có source task được phép làm.

### 2. Tôi có vai trò gì?

Bạn phải được ghi rõ là Writer hoặc Reviewer. Hiện cả hai là `UNASSIGNED`.

### 3. Tôi được sửa file nào?

Writer chỉ sửa `allowed_paths`. Reviewer không sửa source. Không có checkpoint
approved thì không ai được sửa source.

### 4. Làm sao biết đã hoàn thành?

Đọc acceptance criteria, required tests và required evidence trong contract.
“Nhìn có vẻ chạy” hoặc agent nói “xong rồi” không phải evidence.

### 5. Khi nào phải dừng?

Dừng khi scope mơ hồ, cần file ngoài allowlist, test cần secret, architecture đổi,
working tree lạ, tài liệu mâu thuẫn hoặc cần commit/merge/push/deploy.

## Bảng trạng thái

| Trạng thái | Ý nghĩa đơn giản |
|---|---|
| `DRAFT` | Nhiệm vụ đang được soạn |
| `PLAN_REVIEW` | Đang chờ Owner duyệt |
| `PLAN_APPROVED` | Owner đã duyệt đúng version |
| `EXECUTING` | Writer được gán đang làm |
| `READY_FOR_REVIEW` | Writer xong, chờ kiểm tra độc lập |
| `REWORK_REQUIRED` | Reviewer yêu cầu sửa lại |
| `GATE_PASS` | Reviewer xác nhận đúng reviewed SHA đạt |
| `MERGED` | Human đã nhập exact reviewed code vào nhánh chính |
| `ROLLBACK_REQUIRED` | Sau merge có evidence nguy hiểm; dừng và chờ Human quyết định rollback |
| `CLOSED` | Human xác nhận nhiệm vụ hoàn tất |
| `BLOCKED` | Phải dừng và giải quyết blocker |
| `REVIEW_INVALIDATED` | Code đổi sau review; phải review lại |

## Checklist trước khi làm

- [ ] Tôi đã lấy canonical repository mới nhất theo quy trình chưa?
- [ ] Repo doctor có cho phép tiếp tục không?
- [ ] Tôi biết active checkpoint chưa?
- [ ] Tôi biết mình là Writer hay Reviewer chưa?
- [ ] Tôi biết tool đang dùng và đã đọc adapter chưa?
- [ ] Tôi biết allowed/forbidden paths chưa?
- [ ] Tôi biết required tests/evidence chưa?
- [ ] Agent đã xuất startup report chưa?

## Checklist trong khi làm

- [ ] Agent có muốn sửa ngoài scope không?
- [ ] Có thêm dependency/integration không?
- [ ] Có thay architecture, database, API hoặc auth không?
- [ ] Có file lạ thay đổi không?
- [ ] Có định sửa governance để tiếp tục không?
- [ ] Có định stage/commit/push không?

Nếu có, dừng và báo Human.

## Kết thúc Writer

- [ ] Required tests đã chạy thực tế.
- [ ] Evidence tồn tại.
- [ ] Handoff khớp Git diff.
- [ ] Tool transition đã ghi.
- [ ] Chỉ báo `READY_FOR_REVIEW` hoặc `BLOCKED`.
- [ ] Không tự Gate PASS, commit hoặc push.

Sau handoff, nếu chưa có commit, ghi
`CANDIDATE_COMMIT_PENDING_HUMAN_COMMIT`. Human tạo candidate commit và cập nhật
full SHA canonical. Đừng yêu cầu Reviewer Gate PASS trên patch chưa có commit.

## Kết thúc Reviewer

- [ ] Human đã materialize candidate commit; full SHA không phải `PENDING`.
- [ ] Đúng candidate SHA.
- [ ] Tự rerun tests.
- [ ] Kiểm tra allowlist/forbidden paths, MVC, scope và dependency.
- [ ] So sánh handoff với Git diff.
- [ ] Không sửa source.
- [ ] Chỉ đưa `GATE_PASS`, `REWORK_REQUIRED` hoặc `BLOCKED`.

Preliminary patch review chỉ được góp ý; không được `GATE_PASS`.

## Khi thấy lỗi sau merge

1. Không tự rollback hoặc sửa nóng.
2. Báo `ROLLBACK_REQUIRED` kèm merged/deployed SHA, triệu chứng và evidence.
3. Đề xuất rollback target nếu biết, nhưng không tự quyết định.
4. Chờ Human Project Owner chọn rollback hoặc mở forward-fix checkpoint.
5. Review cũ không được tái sử dụng cho source mới.

## Nhớ ba điều

1. Conversation memory is not canonical repository state.
2. Capability của tool không phải quyền của bạn.
3. Human Project Owner là người duy nhất merge và release.

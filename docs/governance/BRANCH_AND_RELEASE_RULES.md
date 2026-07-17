# Quy tắc branch và release

## Trạng thái Git đã xác minh

- Canonical branch: `main`.
- Baseline: `1ae679461e1f709488155ebf275ef070b54d723a`.
- Local `main` khớp `origin/main` tại thời điểm audit.
- Không có tag hoặc branch khác được phát hiện.
- Commit history chưa đủ để chứng minh convention ổn định.
- Working tree có source changes tồn tại trước governance.

## Branch convention đề xuất

Chờ Human Project Owner duyệt:

```text
cp/CP-XX.X-short-name
review/CP-XX.X-gate-review
fix/CP-XX.X-review-findings
docs/governance-update
```

- Một branch gắn với một checkpoint.
- Không làm source work trực tiếp trên `main`.
- Branch name không thay thế contract hoặc approval.
- Human hoặc người được Human chỉ dẫn tạo/switch branch sau khi dirty tree đã được
  xử lý an toàn.

## Quy tắc Git bắt buộc

AI agent không được:

- Stage hoặc commit.
- Merge hoặc rebase để phát hành.
- Push hoặc force push.
- Tạo/xóa release tag.
- Reset/xóa thay đổi không thuộc mình.

Human Project Owner độc quyền các hành động trên. Writer/Reviewer chỉ chuẩn bị
diff, evidence và báo cáo.

## Pull Request gate

Mỗi source PR cần:

- Checkpoint `PLAN_APPROVED`.
- Branch và base/candidate SHA rõ.
- Diff nằm trong allowlist, không có forbidden change.
- Handoff của Writer.
- Independent review đúng candidate SHA.
- Required CI/tests/evidence đạt yêu cầu.
- Không có source change sau `GATE_PASS`.
- Human xác nhận merge.

CI PASS không tự cấp merge authority.

## Candidate materialization gate

Writer chỉ tạo diff, validation, evidence và handoff. Sau đó:

1. Human Project Owner stage đúng checkpoint diff.
2. Human tạo candidate commit.
3. Human cập nhật canonical state bằng full candidate SHA.
4. Independent Reviewer review exact SHA đó.

Reviewer không được Gate PASS một patch chưa có commit. Preliminary patch review
chỉ là advisory và phải được lặp lại trên materialized candidate.

## Branch protection Human cần bật

- Không direct push vào `main`.
- Require Pull Request.
- Require independent review.
- Require governance CI và các test bắt buộc.
- Dismiss stale approval khi có source change.
- Require approval sau latest source change.
- Require conversation resolution nếu phù hợp.
- Block force push và branch deletion.

Các setting này không thể được coi là đã bật chỉ vì tài liệu yêu cầu.

## Release

Trước release, Human xác minh:

1. Candidate SHA đúng reviewed SHA.
2. Checkpoint `GATE_PASS` chưa bị invalidated.
3. CI/test/evidence và release checklist đạt.
4. Credential, migration, destructive action và rollback đã được kiểm soát.
5. Version/tag/changelog được quyết định rõ.

Human thực hiện commit/merge/push/deploy/publish/release/rollback. Sau hành động,
Human cập nhật repository sang `MERGED` và `CLOSED` khi exit condition đạt.

## `ROLLBACK_REQUIRED` và rollback

Sau `MERGED`, nếu evidence cho thấy release không an toàn hoặc vi phạm contract,
Human Project Owner chuyển state sang `ROLLBACK_REQUIRED`. Bất kỳ thành viên nào
có thể báo incident; chỉ Human được quyết định canonical transition và rollback.

Evidence tối thiểu:

- Checkpoint, merged/deployed SHA và môi trường bị ảnh hưởng.
- Thời điểm, triệu chứng, mức độ ảnh hưởng và cách tái hiện.
- Test/log/runtime evidence; không đưa secret/PII.
- Rollback target SHA/version.
- Data migration/compatibility risk.
- Người báo, Human decision và next action.

Next action: đóng băng release action tiếp theo; Human chọn rollback, forward-fix
checkpoint hoặc chấp nhận risk có ghi nhận. Nếu rollback, Human thực hiện và ghi
kết quả/evidence; AI không chạy action.

Rollback là release action, không phải self-repair thông thường. Cần:

- Trigger và impact rõ.
- Target version/SHA rõ.
- Data migration/backward compatibility được đánh giá.
- Evidence và người thực hiện.
- Human approval trước khi chạy, trừ emergency procedure đã được Human duyệt sẵn.

## Dirty baseline hiện tại

Không tạo release, stage hay branch-switch rủi ro cho đến khi Human quyết định giữ,
tách, review hoặc loại bỏ source delta tồn tại sẵn. Governance docs không hợp thức
hóa delta đó.

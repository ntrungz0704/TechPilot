# Tool Independence

Governance, role và contract không đổi khi đổi công cụ.

```text
Governance
+ Assigned Role
+ Tool Adapter
= Valid working session
```

## Capability không phải permission

Tool có thể đọc file, sửa file, chạy command, tạo commit hoặc dùng agent song song.
Repository chỉ cấp quyền theo role và checkpoint. Khả năng kỹ thuật không mở rộng
quyền hạn.

Ví dụ: Codex có thể chỉnh file nhưng Reviewer dùng Codex vẫn không được sửa source;
Writer dùng Codex vẫn không được commit hoặc push.

## Adapter được phép làm gì?

Adapter chỉ mô tả:

- Cách tool đọc repository và chạy command/test.
- Cách giới hạn write vào allowlist.
- Cách áp dụng common session/Writer/Reviewer contract.
- Capability và limitation của tool.
- Cách ngăn auto-commit/auto-push.
- Cách xuất handoff hoặc review.

Adapter không được:

- Đổi lifecycle hoặc authority.
- Mở rộng scope/allowlist.
- Làm yếu gate, approval hoặc architecture constraints.
- Cho tool tự commit, merge, push hoặc deploy.
- Biến conversation memory thành canonical state.

## Đổi tool giữa checkpoint

1. Pull canonical repository mới nhất theo quy trình được Human cho phép.
2. Chạy repo doctor.
3. Đọc lại `AGENTS.md`, `ACTIVE.md`, contract và architecture.
4. Xác nhận role vẫn giữ nguyên.
5. Kiểm tra branch, HEAD và working tree.
6. Không dựa vào context của tool cũ.
7. Ghi tool cũ, tool mới, thời điểm và lý do trong handoff.
8. Không tạo checkpoint mới chỉ vì đổi tool.

Đổi tool không làm review mất hiệu lực. Source thay đổi sau review mới làm review
mất hiệu lực.

## Bằng chứng tool hiện tại

- Repository có tài liệu/prototype Figma-first, nhưng Figma là design authoring
  tool, không phải bằng chứng về coding-agent role.
- Phiên governance hiện tại dùng Codex.
- Không có repository evidence xác nhận Antigravity, Claude Code, Gemini CLI hoặc
  Cursor đang được team sử dụng.

Vì vậy adapter tối thiểu cần có là template và Codex. Tool khác chỉ thêm khi có sử
dụng thực tế; thêm adapter không tự cấp quyền.

## External tool và canonical state

Figma, issue tracker, email hoặc chat có thể chứa input. Quyết định chỉ chính thức
khi Human phê duyệt và repository được cập nhật. Nếu external tool khác repository,
báo conflict; không tự đồng bộ theo một chiều.

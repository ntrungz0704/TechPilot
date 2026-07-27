# Cleanup Deletion Manifest

| Path | Hành động | Trạng thái |
|---|---|---|
| `page.html` | Xoá file qua lệnh `git rm` | Đã xoá an toàn |
| `public/assets/images/products/*.svg` | Giữ lại / Xoá placeholder đã được thay thế bởi PNG | Đã thực hiện ở commit trước |
| `public/assets/images/products/*.webp` | Giữ lại / Xoá placeholder đã được thay thế bởi PNG | Đã thực hiện ở commit trước |

*Lưu ý: Không xoá bất kỳ file nào trong thư mục `checkpoints/`, `.opencode/`, `docs/governance/` hoặc `scripts/workflow/` theo đúng nguyên tắc AGENTS.md.*

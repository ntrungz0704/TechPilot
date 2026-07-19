# Mô tả Pull Request (Pull Request Template)

## 📌 Mục tiêu (Objective)
*Mô tả ngắn gọn về tính năng, sửa lỗi hoặc cải tiến được thực hiện trong PR này.*

## 📸 Ảnh chụp màn hình Before / After (nếu có thay đổi UI)
| Trước (Before) | Sau (After) |
|---|---|
| *Chèn ảnh hoặc mô tả* | *Chèn ảnh hoặc mô tả* |

## 🛠️ Danh sách file thay đổi (Files Changed)
- [ ] `đường/dẫn/file.php` - *Mô tả thay đổi*

## 🧪 Cách kiểm thử (How to Test)
1. *Mô tả các bước chạy thử và dữ liệu kiểm thử ở local.*
2. *Chạy lệnh server:* `php -S 127.0.0.1:8000 router.php`
3. *Đường dẫn test:* `http://127.0.0.1:8000/...`

## ⚠️ Rủi ro tiềm ẩn (Potential Risks)
- [ ] *Có ảnh hưởng đến database schema hoặc các trang khác không?*

## ✅ Checklist nghiệm thu (Acceptance Criteria)
- [ ] Mã nguồn đã được format sạch sẽ, không chứa code thừa/debug.
- [ ] Giao diện responsive tốt trên Desktop, Tablet và Mobile (360px).
- [ ] Chạy thử không gặp lỗi Fatal/Warning hay JavaScript console error.
- [ ] Đã tag ít nhất 1 thành viên review.

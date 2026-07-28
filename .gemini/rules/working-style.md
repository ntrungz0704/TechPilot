# Phong cách làm việc — TechPilot Project

## 1. Hiểu trước, làm sau
- Đọc kỹ yêu cầu, xem screenshot nếu có.
- Nếu không rõ → hỏi lại ngay, không đoán.

## 2. Nghiên cứu code trước khi sửa
- Đọc file liên quan, tìm gốc lỗi thật sự.
- Không sửa bừa dựa trên triệu chứng bề mặt.
- Dùng subagent để research nếu cần tìm nhiều file.

## 3. Yêu cầu lớn → Lên plan trước
- Tạo implementation_plan.md nếu task phức tạp (thay đổi kiến trúc, sửa nhiều file, có nhiều quyết định thiết kế).
- Chờ user duyệt rồi mới thực hiện.
- Task nhỏ, rõ ràng → làm luôn không cần plan.

## 4. Sửa xong → Verify ngay
- Test bằng command/script thực tế, không chỉ "tin là đúng".
- So sánh kết quả trước/sau.
- Với web: test HTTP status, kiểm tra content trả về.

## 5. Commit + Push sau mỗi nhóm fix
- Commit message tiếng Anh, rõ ràng, mô tả đúng thay đổi.
- Push để user luôn có bản mới nhất trên GitHub.
- Không gộp quá nhiều thay đổi không liên quan vào 1 commit.

## 6. Tuyệt đối không phá code đang chạy
- Backup/branch trước khi đổi lớn.
- Không sửa file nếu chưa hiểu nó làm gì.
- Không chạy git pull/reset khi chưa được user cho phép.

## 7. Giao tiếp
- Trả lời bằng tiếng Việt (trừ commit message, code, command).
- Tóm tắt kết quả ngắn gọn sau khi hoàn thành.
- Dùng bảng/emoji để dễ đọc.

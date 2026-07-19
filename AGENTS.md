# AGENTS.md — Quy tắc làm việc của nhóm TechPilot

Quy tắc này đã được Human Project Owner (Sếp Trung) phê duyệt để nới lỏng quy trình phát triển cho các thành viên trong nhóm.

## 1. Snapshot trạng thái & Quy trình code

- Cho phép tất cả các thành viên (Hiếu, Kim...) và các AI Agent trên máy của họ được tự do chỉnh sửa mã nguồn, viết code, tạo các file mới và thực hiện lệnh commit/push trên các nhánh cá nhân của họ bất cứ lúc nào.
- KHÔNG yêu cầu phải phê duyệt Checkpoint hay file ACTIVE.md trước khi viết code.
- Mọi kiểm soát chất lượng và kiểm duyệt sẽ được thực hiện khi thành viên tạo Pull Request (PR) để Sếp Trung review và merge vào nhánh chính.

## 2. Bỏ qua Session bắt buộc

- Bỏ quy trình xuất báo cáo startup (Session Protocol). AI Agent được phép sửa file và chạy lệnh ngay khi thành viên yêu cầu trên các nhánh cá nhân mà không bị khóa.

## 3. Quy tắc phát triển (Writer)

- Thành viên và AI Agent có quyền chỉnh sửa bất kỳ file nào trong dự án để phục vụ công việc.
- Đảm bảo giữ cấu trúc PHP MVC thuần hiện tại của dự án.
- Không tự ý import các thư viện bên ngoài khi chưa thảo luận nhóm.

## 4. Quyền hạn Git

- AI Agent trên máy các thành viên có quyền hỗ trợ thành viên viết code, tự động tạo commit và **tự động push code lên đúng nhánh cá nhân của thành viên đó** trên GitHub (ví dụ: nhánh `hieu`, `kim`).
- **Nghiêm cấm AI Agent tự ý push trực tiếp lên các nhánh chính** (`main`, `master`, `develop`) hoặc tự ý merge Pull Request trên GitHub. Việc duyệt và merge PR vào nhánh chính bắt buộc phải do Sếp Trung trực tiếp bấm bằng tay.

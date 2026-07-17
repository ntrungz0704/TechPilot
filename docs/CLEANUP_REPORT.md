# Báo cáo Dọn dẹp Tài nguyên Dự án TechPilot (Cleanup Report)

Báo cáo này liệt kê các tệp tin dư thừa được kiểm kê trong dự án, phân tích mức độ ảnh hưởng, đưa ra quyết định xóa hoặc giữ lại và phương án khôi phục.

---

## 1. Danh sách tệp tin kiểm kê

| Đường dẫn tệp tin | Kích thước | Trạng thái Git | Nơi tham chiếu | Bằng chứng & Lý do | Quyết định | Phương án khôi phục |
|---|---|---|---|---|---|---|
| `public/test_css.css` | 80,2 KB | Đã lưu baseline | Không có | File CSS rác từ quá trình test giao diện, hoàn toàn không được import hay nạp bởi bất kỳ view nào. | **ĐÃ XÓA AN TOÀN** | `git checkout <baseline_sha> -- public/test_css.css` |
| `php-server.err.log` | 331 KB | Untracked (Đã ignore) | Không có | File log lỗi phát sinh trong lúc chạy server cục bộ của PHP, không phục vụ vận hành. | **ĐÃ THÊM VÀO .GITIGNORE** | Không cần khôi phục |
| `php-server.out.log` | 0 B | Untracked (Đã ignore) | Không có | File log ghi nhận output server cục bộ. | **ĐÃ THÊM VÀO .GITIGNORE** | Không cần khôi phục |
| `config/database.local.php` | 182 B | Untracked (Đã ignore) | `app/models/Product.php`... | File chứa thông tin kết nối database local, đã được Git ignore để bảo vệ bảo mật thông tin nhạy cảm. | **GIỮ LẠI (KHÔNG ĐƯỢC XÓA)** | N/A |
| `scripts/router_techpilot.php` | 519 B | Đã lưu baseline | Không có | Script chạy server phụ trợ cũ của TechPilot, hiện tại đã có `router.php` ở root chạy chính. | **CẦN XÁC NHẬN** | Giữ nguyên để chờ ý kiến đội ngũ |

---

## 2. Quy trình kiểm tra an toàn trước khi xóa
1. **Kiểm tra tham chiếu:** Chạy tìm kiếm chuỗi (Grep/Select-String) trên toàn bộ mã nguồn `app/` và `public/` để chắc chắn không có file nào gọi/require/link tới tệp tin dự kiến xóa.
2. **Kiểm thử Baseline:** Chạy ứng dụng trước khi xóa và kiểm tra giao diện không lỗi.
3. **Xóa từng phần nhỏ:** Không dùng lệnh xóa hàng loạt, chỉ xóa chính xác file đã xác nhận dư thừa.
4. **Smoke test sau khi xóa:** Chạy lại PHP built-in server và duyệt qua các trang để xác minh không phát sinh lỗi PHP Fatal error hoặc lỗi 404 cho asset tĩnh.

---

## 3. Cập nhật .gitignore
Chúng tôi đã bổ sung các cấu hình sau vào tệp `.gitignore` để ngăn chặn các tệp tin log và rác phát sinh quay trở lại repository:
```text
/config/database.local.php
*.log
.DS_Store
Thumbs.db
```

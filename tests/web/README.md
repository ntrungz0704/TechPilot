# TechPilot Web QA

Bộ test này kiểm tra website đang chạy qua HTTP, thay vì gọi trực tiếp
controller/model. Mục tiêu là phát hiện lỗi mà tester và người dùng thật gặp ở
router, session, CSRF, phân quyền và tài nguyên giao diện.

## Phạm vi an toàn

- Smoke test trang chủ, tìm kiếm, sản phẩm, AI, so sánh, PC Builder, tin tức,
  đăng nhập và giỏ hàng.
- Kiểm tra route hồ sơ mà view/controller đang tạo link.
- Kiểm tra khách chưa đăng nhập không truy cập được endpoint admin.
- Kiểm tra POST không có CSRF bị từ chối.
- Kiểm tra chống dò email ở form quên mật khẩu.
- Kiểm tra tối đa 60 CSS, JavaScript và ảnh local được render trên trang chủ.
- Không đăng nhập bằng tài khoản thật.
- Không đặt hàng, thanh toán, upload file, tạo/sửa/xóa bản ghi.
- Các POST admin chỉ gửi payload rỗng để dừng ở validation trước thao tác DB.

## Cách chạy

Mở terminal thứ nhất tại thư mục gốc repo:

```powershell
php -S 127.0.0.1:8000 router.php
```

Mở terminal thứ hai:

```powershell
php tests/web/WebQaTest.php http://127.0.0.1:8000
```

Xuất báo cáo Markdown để gửi cho nhóm trưởng:

```powershell
php tests/web/WebQaTest.php http://127.0.0.1:8000 `
  --report=tests/web/reports/qa-report.md
```

Giới hạn số tài nguyên tĩnh cần kiểm tra:

```powershell
php tests/web/WebQaTest.php http://127.0.0.1:8000 --max-assets=30
```

Audit search trực tiếp trên database local, chỉ chạy truy vấn đọc:

```powershell
php tests/web/SearchQaAudit.php
```

Audit này in số kết quả và top 3 cho alias danh mục, thương hiệu, thông số,
cụm “máy in”, từ không dấu, synonym, typo, wildcard, SKU và tên chính xác.

Audit dữ liệu và rule của PC Builder, chỉ chạy truy vấn đọc:

```powershell
php tests/web/PcBuilderQaAudit.php
```

Audit này kiểm tra mapping category, eligibility, giá khuyến mãi, độ phủ trường
tương thích, ca tính nguồn CPU/VGA/PSU và heuristic iGPU/stock cooler.

## Diễn giải kết quả

- `PASS`: hành vi web khớp hợp đồng kiểm thử.
- `FAIL`: website có lỗi hoặc thiếu cơ chế bảo vệ mong đợi.
- Exit code `0`: tất cả test pass.
- Exit code `1`: phát hiện ít nhất một lỗi web.
- Exit code `2`: runner không chạy được, ví dụ server chưa bật hoặc thiếu cURL.

Mức độ ưu tiên:

- `P0`: lỗi phân quyền/bảo mật có thể ảnh hưởng dữ liệu hoặc khu vực admin.
- `P1`: chức năng chính hỏng, route 404, tài nguyên thiếu hoặc lộ thông tin.
- `P2`: hardening hoặc hành vi phụ cần sửa.

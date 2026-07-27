# Cleanup Candidates

| Path | Loại | Lý do | Kết quả tìm reference | File thay thế | Mức rủi ro | Hành động đề xuất |
|---|---|---|---|---|---|---|
| `page.html` | Temporary file | File HTML sinh ra trong lúc test (chứa mã HTML lỗi trang chủ hoặc rác) | 0 reference | Không có | Thấp | Xóa |
| `apply_image_fixes.php` | Maintenance tool | Script tự động fix đường dẫn ảnh bị sai | 0 reference | Không có | Thấp | Di chuyển vào `tools/maintenance/` |
| `check_products.php` | Audit tool | Script liệt kê và kiểm tra trạng thái sản phẩm | 0 reference | Không có | Thấp | Di chuyển vào `tools/audit/` |
| `check_schema.php` | Audit tool | Kiểm tra cấu trúc schema database | 0 reference | Không có | Thấp | Di chuyển vào `tools/audit/` |
| `verify_business_logic.php` | Audit tool / Test | Script mô phỏng các test case logic | Tham chiếu trong `FINAL_ACCEPTANCE_REPORT.md` & `TEST_REPORT.md` | `tests/` (unit tests) | Thấp | Di chuyển vào `tools/audit/` |
| `verify_images.php` | Audit tool / Test | Script kiểm tra ảnh | Tham chiếu trong các report markdown | Không có | Thấp | Di chuyển vào `tools/audit/` |

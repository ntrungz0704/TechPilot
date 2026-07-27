# CI Database Failure Audit

## Nguyên nhân gốc (Root Cause)
Lỗi xảy ra tại GitHub Actions Job `Database Search & Persistence Integration Test` trong step `Run Non-Destructive Migrations`.
Cụ thể, file migration `database/migrations/20260727_add_catalog_verification_fields.sql` bị lỗi tại dòng `ADD COLUMN model_number VARCHAR(100) NULL AFTER sku,` do cột `sku` không tồn tại trong schema của CI.
Sự bất đồng bộ giữa cấu trúc Database cục bộ (có thể đã có `sku` từ trước) và file dump `database/techpilot.sql` hoặc `database/schema.sql` (hoàn toàn không chứa trường `sku`) khiến lệnh `ALTER TABLE ... AFTER sku` bị crash với lỗi `Unknown column 'sku'`.

## Thứ tự migration
- `scripts/database/migrate.php` sử dụng `glob()` lấy tất cả file `.sql` trong `database/migrations` và chạy chúng theo thứ tự chữ cái.

## Schema hiện tại
- **Schema CI**: Lấy từ `database/techpilot.sql` (chứa cấu trúc các bảng). Sau khi phân tích bằng `grep_search`, bảng `products` trong `database/schema.sql` và `database/techpilot.sql` ĐỀU KHÔNG có cột `sku` hay `source_updated_at`.
- **Cột đang thiếu trong CI Schema**: `sku`, `source_updated_at`.
- **Cột đã tồn tại**: chưa có `model_number`, `canonical_model_key`, `verification_status`, `verification_score`, `verified_at`, `source_checked_at` (vì migration chưa chạy thành công).
- **Index**: Chưa có `idx_products_canonical_model_key` hoặc `idx_canonical_model_key`.

## Đề xuất sửa chữa
- Dùng cấu trúc `PREPARE stmt` (Idempotent) để kiểm tra từng cột bằng `information_schema` trước khi thêm.
- Loại bỏ hoàn toàn mệnh đề `AFTER sku` và `AFTER source_updated_at` để bảo đảm tính an toàn khi schema thiếu đồng bộ.

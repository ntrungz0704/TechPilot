# Báo cáo sửa lỗi và đề nghị review merge

Ngày cập nhật: 02/08/2026

Nhánh đích: `hieu`

Base dùng để đối chiếu: `origin/main`

> Local branch `hieu` đã được tạo trực tiếp từ `origin/main`; toàn bộ sửa lỗi
> local được giữ nguyên khi chuyển nhánh. Trước khi tạo các commit mới, `hieu`
> ở đúng cùng commit với `origin/main` (`0 ahead / 0 behind`). `origin/hieu`
> cũ đang chậm 78 commit và không có commit riêng, nên lần push này có thể
> fast-forward an toàn, không cần force push.

## 1. Tóm tắt thay đổi

| Hạng mục | Trạng thái | Đã thực hiện | Tác động dễ hiểu |
|---|---|---|---|
| 1. Khóa VNPay simulator trên production | ✅ Hoàn tất | Simulator chỉ được đăng ký ở `development`; production thiếu credential sẽ tắt VNPay theo kiểu fail-closed | Không thể vô tình dùng cổng thanh toán giả trên server thật; COD vẫn hoạt động |
| 2. Thống nhất cách tính giá | ✅ Hoàn tất | Cart, Checkout, Order, Product Detail, Search, PC Builder và Flash Sale dùng chung giá hiệu lực; backend tính lại giá/coupon/tổng tiền | Không còn mỗi màn hình tính một giá hoặc tin tổng tiền do trình duyệt gửi lên |
| 3. Product status và migration runner | ✅ Hoàn tất | Đồng bộ 7 trạng thái sản phẩm; runner có ledger, lock, fail-fast, `--status` và baseline có kiểm soát | Trạng thái không bị cắt sai; migration không bị chạy lặp toàn cục |
| 4. Đồng bộ schema | ✅ Hoàn tất | Seed và migration được đồng bộ cho 26 bảng, return tables, chatbot tables, inventory và Flash Sale reservation | Database mới và database nâng cấp có cùng cấu trúc mong đợi |
| 5. Login hay guest checkout | ✅ Chốt Guest Checkout | Khách không đăng nhập được đặt hàng; `user_id` có thể rỗng; địa chỉ chỉ lưu cho tài khoản đăng nhập | Giảm bước mua hàng nhưng guest không có đầy đủ lịch sử/sổ địa chỉ |
| 6. Bảo vệ scraper | ⏸ Hoãn theo quyết định dự án | Không mở rộng phạm vi trong đợt này | Phù hợp quy mô trường học; cần làm trước khi public rộng hoặc chạy tải lớn |
| 7. Bộ lọc và query khi dữ liệu tăng | ✅ Logic hoàn tất / ⏳ Index theo dõi | Chuẩn hóa nhiều dạng JSON specs; thêm facet theo danh mục; sửa URL, khoảng giá và alias Gaming/Văn phòng; thêm benchmark đọc-only | Bộ lọc trả đúng sản phẩm, không còn option rỗng hoặc hai alias trả cùng tập |
| Flash Sale và tồn kho | ✅ Hoàn tất trong phạm vi hiện tại | Dọn item sai, giữ hạn mức Flash Sale theo order item; reserve/release/commit tồn kho nguyên tử | Giảm oversell, double-submit và sai hạn mức Flash Sale |

## 2. Bằng chứng kiểm thử

| Kiểm tra | Kết quả |
|---|---:|
| VNPay environment security | 27/27 pass |
| Pricing consistency | 74/74 pass |
| Product status | 25/25 pass |
| Migration runner safety | 47/47 pass |
| Database schema parity | 58/58 pass |
| Inventory lifecycle | 65/65 pass |
| Flash Sale cleanup + reservation | 66/66 pass |
| Filter contract + normalizer + integration + UI | 603/603 pass |
| HTTP alias Laptop Gaming/Văn phòng | HTTP 200; 30 Gaming + 31 Văn phòng, không trùng |
| Benchmark 50 vòng, 650 sản phẩm | p95 cao nhất 24,060 ms/query; chưa cần thêm index JSON |
| `git diff --check` | Pass |

Kết quả regression toàn dự án: **19/21 test file pass**. Hai suite chưa xanh
được ghi riêng bên dưới, không được che giấu khi mở PR.

## 3. Việc còn lại / cần nhóm trưởng xác nhận

| Mức ưu tiên | Việc cần xử lý hoặc xác nhận | Đề xuất |
|---|---|---|
| Trước merge | Phạm vi thay đổi lớn: hơn 70 file code, migration, test và tài liệu | Chia thành các commit theo nhóm chức năng trong cùng PR để review dễ hơn |
| Phạm vi commit | Không đưa các báo cáo QA lịch sử trong `tests/web/reports/*` lên PR | Vẫn giữ local; chỉ stage code, migration, test/fixtures cần thiết và báo cáo merge này |
| Đồng bộ branch | Local `hieu` đã được tạo từ `origin/main`; thay đổi local vẫn nguyên vẹn | Sau khi test và commit, push fast-forward lên `origin/hieu`; không dùng force push |
| Trước deploy DB cũ | Có migration mới và seed đã baseline migration | Backup DB, chạy `php scripts/database/migrate.php --status`, chỉ chạy pending; không dùng `--baseline-existing` nếu chưa đối chiếu schema |
| Regression độc lập | `NewsModuleRegressionTest`: 58/59, lỗi desktop TOC kiểm tra `hasArticleContent` | Tạo task riêng hoặc nhóm trưởng chấp nhận là known issue trước merge |
| QA cần cập nhật | `WebQaTest`: 18/27; case Checkout vẫn kỳ vọng bắt đăng nhập, trái với quyết định Guest Checkout | Sửa expectation TP-WEB-015; audit riêng các route return/AI favorite, admin POST auth và forgot-password |
| Production VNPay | QR thật cần TMN Code, Hash Secret, return URL/IPN hợp lệ | Cấu hình secret ngoài Git và test trên VNPay Sandbox trước production |
| Khi catalog tăng | Facet JSON đang quét các sản phẩm trong category | Chạy lại `php tests/ProductFacetBenchmark.php 50`; chỉ thêm generated column/index khi p95 thực tế vượt ngưỡng thống nhất |
| Trước public rộng | Scraper chưa được harden trong đợt này | Bổ sung rate limit, timeout, allowlist domain và giới hạn tài nguyên ở task riêng |

## 4. Đề xuất cấu trúc commit

1. `fix(payment-pricing): harden vnpay and centralize effective price`
2. `fix(database-orders): add safe migrations, inventory and flash-sale lifecycle`
3. `fix(checkout): finalize guest checkout with server-side totals`
4. `fix(catalog): normalize specs and implement category facets`
5. `test: add regression contracts, QA evidence and query benchmark`

## 5. Kết luận bàn giao

- Sẵn sàng **commit và mở PR để review**: Có.
- Sẵn sàng **merge thẳng vào main mà không review**: Không.
- Nhánh đích của PR: `hieu`; branch đã được cập nhật từ `origin/main` trước khi tạo commit.
- Không đưa `tests/web/reports/*` vào commit/PR theo xác nhận của chủ nhánh.
- Không có migration/index mới dành riêng cho facet và benchmark không ghi dữ liệu.
- Nhóm trưởng cần xác nhận migration rollout, Guest Checkout và cách xử lý hai
  suite regression chưa xanh trước khi merge.

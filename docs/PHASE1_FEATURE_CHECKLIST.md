# Feature Checklist — Phase 1 (TechPilot)

| Phân hệ | Tính năng chi tiết | Trạng thái | Ghi chú |
|---|---|---|---|
| **1. Storefront** | Trang chủ hiển thị động 22 khu vực | `[/]` | Đang sử dụng database động, cần rà soát lại banners/countdown |
| | Tìm kiếm & Bộ lọc (danh mục, thương hiệu, khoảng giá) | `[x]` | Hoàn thành sửa bug parameter lặp |
| | Bộ lọc phân trang server-side & sắp xếp | `[/]` | Cần kiểm tra độ ổn định và responsive |
| | Chi tiết sản phẩm (gallery, specs, review list) | `[x]` | Hoàn thành |
| | Flash Sale campaign active, countdown Javascript | `[/]` | Cần đồng bộ múi giờ server-client |
| | Bài viết tin tức published | `[x]` | Hoàn thành |
| | Các trang tĩnh (Giới thiệu, Liên hệ, Chính sách) | `[/]` | Cần rà soát các tệp view tĩnh |
| **2. Auth & User** | Đăng ký tài khoản (họ tên, email, phone, mật khẩu hash) | `[x]` | Đã có logic password_hash & check email trùng |
| | Đăng nhập tài khoản (password_verify & session regenerate) | `[x]` | Đã bổ sung session_regenerate_id(true) |
| | Đăng xuất (xử lý session destroy an toàn) | `[x]` | Đã hoàn thành |
| | CLI Provisioning Admin (`scripts/create_admin.php`) | `[ ]` | Cần phát triển mới |
| | Chặn customer truy cập vùng admin, phân quyền | `[ ]` | Cần phát triển middleware/guard |
| **3. Account Area**| Sửa hồ sơ cá nhân, đổi mật khẩu (yêu cầu mật khẩu cũ) | `[/]` | Cần rà soát lại giao diện |
| | Wishlist & Compare (session so sánh) | `[x]` | Hoàn thành |
| | Lịch sử đơn hàng, chi tiết đơn hàng (IDOR check) | `[x]` | Đã bổ sung check user_id trong SQL |
| | Đổi trả sản phẩm & hủy đơn (ở trạng thái cho phép) | `[/]` | Cần rà soát logic hủy đơn |
| **4. Shopping Cart**| Thêm/sửa/xóa giỏ hàng động, đồng bộ session | `[x]` | Hoàn thành |
| | Kiểm tra tồn kho trước khi cho thêm vào giỏ | `[x]` | Hoàn thành |
| | Tính lại giá gốc từ DB, chặn client fake price | `[x]` | Hoàn thành |
| **5. Checkout COD**| Áp coupon giảm giá (ngày hết hạn, min order, loại giảm giá) | `[/]` | Cần tích hợp form coupon ở checkout |
| | Transaction đặt hàng: khóa dòng `FOR UPDATE`, trừ kho, rollback | `[x]` | Đã hoàn thành |
| | Success page, chặn double submit đơn hàng | `[/]` | Cần bổ sung token submit chống double-post |
| **6. Reviews** | Gửi review (rating 1-5, comment XSS escape) | `[/]` | Cần kiểm duyệt XSS và rating động |
| | Chỉ người mua đơn completed mới được review | `[ ]` | Cần bổ sung check status đơn |
| **7. Admin Panel** | Layout Admin responsive, sidebar đóng/mở | `[ ]` | Cần phát triển mới |
| | Dashboard thống kê thật (doanh thu, đơn hàng, tồn thấp) | `[ ]` | Cần phát triển mới |
| | CRUD Danh mục (slug unique, chặn delete khi có sản phẩm) | `[ ]` | Cần phát triển mới |
| | CRUD Thương hiệu (chặn delete khi đang được tham chiếu) | `[ ]` | Cần phát triển mới |
| | CRUD Sản phẩm (validate giá/kho, upload ảnh gallery) | `[ ]` | Cần phát triển mới |
| | CRUD Đơn hàng (cập nhật trạng thái state machine, hoàn kho khi hủy) | `[ ]` | Cần phát triển mới |
| | CRUD Khách hàng (khóa/mở khóa tài khoản, đổi role) | `[ ]` | Cần phát triển mới |
| | CRUD Đánh giá (duyệt/ẩn review) | `[ ]` | Cần phát triển mới |
| | CRUD Flash Sale & Coupon | `[ ]` | Cần phát triển mới |
| | CRUD Banners & Posts | `[ ]` | Cần phát triển mới |
| **8. Security** | CSRF protection cho toàn bộ POST requests | `[x]` | Đã bổ sung global filter ở public/index.php |
| | Ngăn chặn path traversal, upload file an toàn | `[/]` | Cần rà soát hàm upload ảnh sản phẩm |
| | PDO prepared statements 100% | `[x]` | Hoàn thành |

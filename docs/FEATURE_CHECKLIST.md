# Danh sách Kiểm kê Tính năng (Feature Checklist) — TechPilot

Danh sách này theo dõi tiến độ phát triển các tính năng của dự án TechPilot theo thứ tự ưu tiên từ cốt lõi (P0) đến nâng cao (P1, P2) nhằm phục vụ đợt nghiệm thu cuối cùng.

---

## 1. Mức P0 — Website phải chạy (Core Flow)

| STT | Tính năng | Trạng thái phát triển | Trạng thái kiểm thử | Ghi chú |
|---|---|---|---|---|
| 1 | Khởi tạo cấu hình và bộ định tuyến Router | **Hoàn thành** | **Đã test** | Sửa lỗi fallback action `notFound`. |
| 2 | Layout chung (Header, Footer, Navbar) | **Hoàn thành** | **Đã test** | Responsive chuẩn thiết kế V2 Blue Theme. |
| 3 | Trang chủ hiển thị động từ DB | **Hoàn thành** | **Đã test** | Nạp categories, sản phẩm nổi bật, banner. |
| 4 | Tìm kiếm, lọc và phân trang sản phẩm | **Hoàn thành** | **Đã test** | Sắp xếp theo giá, thương hiệu, danh mục. |
| 5 | Chi tiết sản phẩm (`/product/detail/{slug}`) | **Hoàn thành** | **Đã test** | Hiển thị thông số kỹ thuật, mô tả, tồn kho. |
| 6 | Đăng ký, đăng nhập, đăng xuất | **Hoàn thành** | **Đã test** | Mã hóa password bằng `password_hash`. |
| 7 | Giỏ hàng (Cart Session & Validation) | **Hoàn thành** | **Đã test** | Chặn số lượng âm, chặn mua quá số lượng tồn. |
| 8 | Thanh toán COD (Transaction & Lock Stock) | **Hoàn thành** | **Đã test** | Áp dụng rollback khi gặp lỗi, cập nhật tồn kho. |
| 9 | Lịch sử và chi tiết đơn hàng cá nhân | **Hoàn thành** | **Đã test** | Chặn IDOR (chỉ cho chính chủ xem đơn hàng). |

---

## 2. Mức P1 — Hoàn thiện theo ERD2

| STT | Tính năng | Trạng thái phát triển | Trạng thái kiểm thử | Ghi chú |
|---|---|---|---|---|
| 10 | Danh sách sản phẩm yêu thích (Wishlist) | **Hoàn thành** | **Đã test** | Yêu cầu đăng nhập, thêm/xóa nhanh. |
| 11 | Đánh giá & Bình luận sản phẩm (Reviews) | **Hoàn thành** | **Đã test** | Chỉ cho phép rating từ 1-5 sao, chống XSS. |
| 12 | Đồng hồ đếm ngược Flash Sale thời gian thực | **Hoàn thành** | **Đã test** | countdown thực tế dựa trên deadline MySQL. |
| 13 | Quản lý mã giảm giá (Coupon) | **Hoàn thành** | **Đã test** | Tính lại giá và kiểm tra điều kiện áp mã ở server. |
| 14 | Quản lý banner quảng cáo và Tin tức (Posts) | **Hoàn thành** | **Đã test** | Hiển thị tin tức và banners theo vị trí. |
| 15 | Trang quản trị Admin (Dashboard tối thiểu) | **Hoàn thành** | **Đã test** | Thống kê số lượng đơn, doanh thu và CRUD. |

---

## 3. Mức P2 — Nâng cao chất lượng

| STT | Tính năng | Trạng thái phát triển | Trạng thái kiểm thử | Ghi chú |
|---|---|---|---|---|
| 16 | Thiết kế Responsive trên 5 viewports | **Hoàn thành** | **Đã test** | Đã test 360px, 768px, 1024px, 1440px. |
| 17 | Tải lại trang/trạng thái trống (Empty States) | **Hoàn thành** | **Đã test** | Hiển thị thông báo khi giỏ hàng/yêu thích rỗng. |
| 18 | Bảo mật & QA Audit (CSRF, XSS, IDOR) | **Hoàn thành** | **Đã test** | Escape dữ liệu đầu ra và chặn xem trộm đơn hàng. |

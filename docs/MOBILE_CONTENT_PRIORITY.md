# Phân cấp Ưu tiên Nội dung Di động (Mobile Content Priority)

Tài liệu này xác định cách trình bày, số lượng hiển thị và hành vi của các thành phần giao diện trên Mobile Homepage (viewport <= 575px) so với Desktop Homepage (viewport >= 992px).

---

## 1. Bản đồ Phân cấp nội dung (Homepage Mobile)

| STT | Thành phần giao diện | Trạng thái trên Mobile | Giải pháp hiển thị trên Mobile |
|---|---|---|---|
| 1 | Top Announcement Bar | **ẨN** | Ẩn hoàn toàn để tránh dồn thông tin chữ nhỏ. |
| 2 | Header | **GỘP & TINH GIẢN** | Chỉ giữ 1 hàng: `[Menu] [Logo] [Cart] [Account]`. Touch target >= 44px. |
| 3 | Search Bar | **CHUYỂN DÒNG** | Nằm riêng biệt bên dưới Header, rộng 100%, placeholder "Tìm sản phẩm...". |
| 4 | Quick Categories | **TINH GIẢN** | Grid 3 cột x 2 hàng, hiển thị đúng 6 danh mục chính. |
| 5 | Hero Banner Slider | **TINH GIẢN** | Chỉ hiển thị: tên sản phẩm, mô tả ngắn, giá và nút CTA "Mua ngay". Ẩn specs chi tiết và banner phụ. |
| 6 | Cam kết dịch vụ đầu trang | **TINH GIẢN** | Chỉ hiển thị 2 cam kết chính: Miễn phí giao hàng, Bảo hành chính hãng. |
| 7 | Flash Sale | **TINH GIẢN** | Tiêu đề và nút "Xem tất cả" hàng 1, countdown hàng 2. Grid 2 cột, hiển thị tối đa 4 sản phẩm. |
| 8 | Sản phẩm bán chạy | **TINH GIẢN** | Grid 2 cột, tối đa 4 sản phẩm. Chỉ giữ 3 chip lọc: Tất cả, Laptop, Linh kiện. |
| 9 | Banner chiến dịch | **GIỮ 1 BANNER** | Chỉ giữ 1 banner NVIDIA RTX 50 Series full width, crop focal point phù hợp. |
| 10 | Các section sản phẩm riêng biệt | **ẨN & GỘP** | Ẩn 6 section dài. Gộp tất cả vào khối tab **"Khám phá theo danh mục"**. Mỗi tab hiển thị tối đa 4 sản phẩm. |
| 11 | Apple Zone | **TINH GIẢN** | 1 banner full width và 2 sản phẩm nổi bật xếp 2 cột. |
| 12 | Tin tức công nghệ | **TINH GIẢN** | 1 cột dọc gồm 3 bài viết mới nhất dưới dạng thẻ ngang (ảnh bên trái, tiêu đề bên phải). |
| 13 | Đối tác chiến lược | **ẨN** | Ẩn toàn bộ khỏi trang chủ di động để rút ngắn trang. |
| 14 | Khách hàng nói gì (Reviews) | **ẨN** | Ẩn toàn bộ khỏi trang chủ di động. |
| 15 | Cam kết dịch vụ cuối trang | **TINH GIẢN** | Grid 2x2 cho 4 cam kết chính. |
| 16 | Footer | **TINH GIẢN** | Accordion đóng/mở cho các nhóm liên kết, form nhận tin full width. |
| 17 | Bottom Navigation | **THÊM MỚI** | Fixed bottom 0, gồm 5 mục chính. Chiều cao 64px, safe area padding. |

---

## 2. Quy tắc giới hạn số lượng sản phẩm trên Mobile (PHP / CSS)

*   **Không dùng display:none bừa bãi trên lượng lớn dữ liệu**:
    *   Sử dụng CSS selector giới hạn số lượng hiển thị thực tế: `.product-grid > .product-card:nth-child(n+5) { display: none !important; }` để chỉ hiển thị đúng 4 sản phẩm trên mobile grid, tránh tải dư thừa nhưng vẫn giữ nguyên 6-8 sản phẩm trên desktop.
    *   Tương tự đối với tin tức: `.news-grid > .news-card:nth-child(n+4) { display: none !important; }` để hiển thị tối đa 3 bài viết.

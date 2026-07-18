# Kiểm tra và Đánh giá Mobile UX (Mobile UX Audit)

*   **Thời gian thực hiện**: 2026-07-18
*   **Viewport mục tiêu**: **440×956**

Tài liệu này ghi nhận các điểm chưa hợp lý của giao diện hiện tại trên thiết bị di động và các biện pháp cải tiến triệt để nhằm mang lại trải nghiệm mua sắm tối ưu, chuẩn Mobile-First.

---

## 1. Điểm chưa hợp lý & Biện pháp khắc phục

| STT | Vấn đề được phát hiện trên Mobile | Tác động UX | Biện pháp khắc phục |
|---|---|---|---|
| 1 | **Trang desktop bị co nhỏ** | Khách hàng phải zoom để đọc thông tin, chữ quá nhỏ dưới 11px. | Đặt lại font-size tối thiểu cho text nội dung là 12px. Chuyển các lưới nhiều cột thành grid tối đa 2 cột hoặc 1 cột dọc. |
| 2 | **Lưới sản phẩm 3-4 card trên hàng** | Card quá bé, hình ảnh và giá bị bóp méo, không thể đọc được thông tin. | Khống chế tối đa **2 cột** tại 390-440px, rộng mỗi card tối thiểu 168px. |
| 3 | **Touch target quá nhỏ** | Các nút CTA, giỏ hàng, menu hamburger khó chạm bằng ngón tay. | Đảm bảo chiều cao/chiều rộng các vùng bấm chính (nút, icon) đạt tối thiểu **44×44px**. |
| 4 | **Trang chủ quá dài** | Có quá nhiều section sản phẩm tương tự nhau làm người dùng mệt mỏi khi cuộn trang. | Gộp 6 section sản phẩm riêng biệt thành một khối tab duy nhất **"Khám phá theo danh mục"**. |
| 5 | **Banners lặp lại dày đặc** | Banners quảng cáo xuất hiện liên tục gây loãng thông tin sản phẩm. | Ẩn toàn bộ các banner phụ lặp lại giữa các danh mục. Chỉ giữ lại 1 banner chiến dịch lớn ở giữa trang chủ. |
| 6 | **Danh mục quick categories chật chội** | Hiển thị quá nhiều icon danh mục khiến grid bị dồn nén. | Tinh giản chỉ hiển thị đúng **6 danh mục chính**, trình bày dạng grid 3x2 ngay ngắn. |
| 7 | **Thiếu Bottom Navigation** | Người dùng di động phải cuộn ngược lên đầu để tìm giỏ hàng hoặc tìm kiếm. | Bổ sung thanh **Bottom Navigation cố định** dưới cùng màn hình (5 mục: Trang chủ, Danh mục, Tìm kiếm, Yêu thích, Giỏ hàng). |

---

## 2. Tiêu chí định lượng đo lường sự thành công

1.  **Không horizontal overflow**: 100% no scrollbar ngang ở body.
2.  **Độ cao trang chủ**: Giảm tối thiểu 50% so với bản gốc của Desktop.
3.  **Kích thước chữ**: Nội dung chính (tên sản phẩm, giá) luôn >= 13px.
4.  **Touch Target**: Các vùng tương tác chính >= 44px.

# Báo cáo Kiểm thử Mobile Responsive (Mobile Responsive Test)

*   **Thời gian thực hiện**: 2026-07-18
*   **Môi trường**: Giả lập Chrome DevTools / Cốc Cốc di động (360px, 390px, 412px, 440px).

Tài liệu này ghi nhận kết quả kiểm thử định lượng các thông số giao diện sau khi nâng cấp Mobile UX.

---

## 1. Kết quả kiểm thử định lượng (Quantitative Test)

| Chỉ số kiểm thử | Thiết bị (Width) | Trạng thái | Giá trị đo được | Tiêu chí nghiệm thu |
|---|---|---|---|---|
| **Độ rộng tràn màn hình** | 360px | **PASS** | 0px overflow | `= 0px` |
| | 440px | **PASS** | 0px overflow | `= 0px` |
| **Kích thước chữ nhỏ nhất** | 440px | **PASS** | 12px (Metadata) | `>= 11px` |
| **Touch Target lớn nhất** | 440px | **PASS** | 46px (Bottom Nav) | `>= 44px` |
| **Số card trên 1 hàng** | 440px | **PASS** | 2 cards | `<= 2 cards` |
| | 320px | **PASS** | 1 card | `<= 1 card` |

---

## 2. Kiểm thử hành vi tương tác trên Mobile

### 2.1 Bấm nút chuyển đổi Tab "Khám phá theo danh mục"
*   **Mô tả**: Bấm các tab (Laptop, PC, Linh kiện, Gaming Gear, Màn hình).
*   **Hành vi mong đợi**: Nội dung tab tương ứng hiển thị lập tức 4 sản phẩm đại diện mà không bị reload lại trang.
*   **Trạng thái**: **PASS**.

### 2.2 Đóng/mở Footer Accordion
*   **Mô tả**: Nhấn vào tiêu đề `h4` ở footer.
*   **Hành vi mong đợi**: Danh sách các liên kết bên dưới trượt xuống hiển thị, icon mũi tên đi xuống xoay ngược lên. Nhấn lần nữa sẽ đóng lại.
*   **Trạng thái**: **PASS**.

### 2.3 Bottom Navigation
*   **Mô tả**: Bấm các nút điều hướng nhanh ở chân màn hình.
*   **Hành vi mong đợi**: Chuyển trang mượt mà hoặc mở nhanh chức năng tương ứng. Giỏ hàng hiển thị badge số lượng chuẩn.
*   **Trạng thái**: **PASS**.

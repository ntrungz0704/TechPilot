# Báo cáo Kiểm thử Responsive UI/UX (Customer Responsive Report)

*   **Thời gian thực hiện**: 2026-07-18
*   **Thiết bị kiểm thử**: Trình duyệt Cốc Cốc & Chrome DevTools (giả lập kích thước viewport **440×956**).

---

## 1. Trạng thái kiểm thử các Viewport chính

| Viewport | Kích thước kiểm thử | Trạng thái | Hành vi kiểm tra | Ghi chú |
|---|---|---|---|---|
| **Mobile nhỏ** | `320px` đến `359px` | **PASS** | Không bị tràn ngang, lưới sản phẩm 2 cột đều đặn. | Chữ nút CTA tự thu nhỏ khít. |
| **Mobile chuẩn** | **440×956** | **PASS** | 100% no horizontal scrollbar. Mọi dòng chữ specs hiển thị đầy đủ. | Đã gỡ bỏ toàn bộ variants, layout Cart & Checkout xếp 1 cột. |
| **Tablet** | `768px` đến `991px` | **PASS** | Đọc rõ thông tin, menu drawer ẩn/hiện mượt mà. | Không bị méo ảnh banner. |
| **Desktop** | `1200px` trở lên | **PASS** | Hiển thị full width theo container, grid 4-6 cột. | Chuẩn giao diện TechPilot Blue Theme. |

---

## 2. Chi tiết tối ưu hóa Responsive tại Viewport 440×956

### 2.1 Header & Điều hướng (Navigation)
*   **Trạng thái**: **PASS**
*   **Cải tiến**: Logo TechPilot thu nhỏ font-size về 18px. Ẩn tagline phụ. Góc phải chỉ giữ lại 2 icon hành động chính là Giỏ hàng và Tài khoản (touch target đạt chuẩn >= 44px). Ẩn các nút Locator/Wishlist phụ.
*   **Hệ quả**: Header gọn gàng và không bị méo lề.

### 2.2 Banner & Specs Slide
*   **Trạng thái**: **PASS**
*   **Cải tiến**: Chữ tiêu đề `h2` trong slide được scale về 20px. Padding của slide đặt là `24px 20px` (Slide Left = 20px). Các specs hiển thị thẳng hàng và không bị cắt ký tự đầu tiên (đầu chữ `NVIDIA`, `Intel`, `ROG`... hiển thị đầy đủ).

### 2.3 Lưới sản phẩm (Products Grid)
*   **Trạng thái**: **PASS**
*   **Cải tiến**: Trên mobile 440px, lưới tự động đổi sang `grid-template-columns: repeat(2, 1fr)` với `gap: 10px` để tối ưu không gian. Tên sản phẩm được khống chế chiều cao tối đa 2 dòng (34px) để tránh méo card. Ảnh sản phẩm đặt `object-fit: contain` không méo.

### 2.4 Giỏ hàng & Checkout (Cart & Checkout)
*   **Trạng thái**: **PASS**
*   **Cải tiến**: Các layout chia cột 2 bên (sidebar tổng tiền) được chuyển hoàn toàn thành 1 cột xếp dọc (`flex-direction: column` hoặc `grid-template-columns: 1fr`). Các item trong giỏ hàng xếp dạng cột đứng, ảnh sản phẩm nằm phía trên, tên và số lượng nằm phía dưới để không bị tràn màn hình.

### 2.5 Widget không được hỗ trợ
*   **Trạng thái**: **PASS**
*   **Cải tiến**: Ẩn hoàn toàn nút chat AI stateless/chatbox, nút chọn địa chỉ nâng cao và nút đổi trả để khớp hoàn toàn với ERD 15 bảng của giảng viên.

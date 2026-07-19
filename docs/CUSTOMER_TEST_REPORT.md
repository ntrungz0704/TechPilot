# Báo cáo Kiểm thử chức năng Khách vãng lai và Khách hàng (Customer Test Report)

*   **Thời gian thực hiện**: 2026-07-18
*   **Người kiểm thử**: Antigravity (AI Coding Assistant)
*   **Trạng thái kiểm thử**: **PASS**

---

## 1. Kết quả kiểm thử các kịch bản chính (Test Results)

| Test ID | Tên kịch bản | Các bước thực hiện | Kết quả mong đợi | Kết quả thực tế | Trạng thái |
|---|---|---|---|---|---|
| **TC-GUEST-01** | Trang chủ hoạt động thật | Truy cập `/`, kiểm tra dữ liệu banner, Flash Sale, Best Seller. | Dữ liệu hiển thị từ 15 bảng database mẫu. | Dữ liệu hiển thị chính xác. | **PASS** |
| **TC-GUEST-02** | Tìm kiếm & Lọc | Tìm kiếm từ khóa "ASUS" và lọc giá trị. | Trả về đúng danh sách sản phẩm khớp. | Danh sách sản phẩm trả về chuẩn. | **PASS** |
| **TC-GUEST-03** | Đăng ký & Đăng nhập | Đăng ký email mới, kiểm tra password hash và trạng thái active. | Lưu mật khẩu băm, đăng nhập thành công. | Mật khẩu được bcrypt hóa, login mượt. | **PASS** |
| **TC-CUST-01** | Giỏ hàng & Đồng bộ | Thêm sản phẩm, cập nhật số lượng, xóa item. | Giá tính lại ở server, giỏ hàng chuẩn. | Giá tính chính xác, không lỗi logic. | **PASS** |
| **TC-CUST-02** | Mã giảm giá (Coupon) | Áp mã `TECHPILOT100` cho đơn hàng 28.590.000đ. | Giảm trực tiếp 100.000đ thành công. | Tổng tiền giảm chính xác, hiển thị đẹp. | **PASS** |
| **TC-CUST-03** | Đặt hàng COD | Điền thông tin giao hàng, đặt hàng COD. | Trừ tồn kho sản phẩm, tăng used_count. | Tồn kho giảm từ 25 xuống 24, used_count tăng 1. | **PASS** |
| **TC-CUST-04** | Yêu thích (Wishlist) | Thêm/xóa sản phẩm yêu thích của khách hàng. | Lưu trực tiếp vào bảng liên kết `wishlists`. | Insert & delete trực tiếp bảng wishlists. | **PASS** |

---

## 2. Chi tiết kết quả kiểm tra Transaction & Concurrency

### 2.1 Kiểm tra Rollback Transaction:
*   **Kịch bản**: Cố ý tạo lỗi đứt kết nối hoặc nhập số lượng không hợp lệ ở giữa bước tạo đơn hàng.
*   **Kết quả**: Database tự động rollback toàn bộ. Không có bất kỳ bản ghi mồ côi (orphan) nào xuất hiện trong `orders` hay `order_items`. Số lượng tồn kho sản phẩm được giữ nguyên vẹn.

### 2.2 Phòng chống bán vượt mức (Overselling Protection):
*   **Kịch bản**: Hai luồng đồng thời tiến hành checkout một sản phẩm có số lượng tồn kho chỉ còn 1.
*   **Kết quả**: Luồng thứ nhất thực hiện câu lệnh `SELECT ... FOR UPDATE` khóa dòng sản phẩm thành công và checkout trừ kho về 0. Luồng thứ hai bị chặn và trả về lỗi thông báo "Sản phẩm không đủ hàng tồn kho", tồn kho không bao giờ bị âm.

### 2.3 Chống gửi đúp đơn hàng (Double-Submit Protection):
*   **Kịch bản**: Khách hàng nhấn nút đặt hàng liên tiếp nhiều lần.
*   **Kết quả**: Ngay sau request đầu tiên gửi đi, mã `submit_token` trong Session bị unset lập tức. Các request gửi đúp sau đó không khớp token sẽ bị từ chối và redirect về giỏ hàng an toàn.

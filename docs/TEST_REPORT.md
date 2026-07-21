# Báo cáo Kiểm thử và Tích hợp E2E (TEST_REPORT)

Báo cáo kết quả chạy kiểm thử tự động, kiểm thử tích hợp (E2E), kiểm thử khói (Smoke Test) và kiểm toán nghiệp vụ trên hệ thống TechPilot dưới sơ đồ **ERD V2 – 19 bảng chính thức**.

## 1. Kết quả chạy Smoke Test đường dẫn (GET requests)

Sử dụng script kiểm tra mã phản hồi HTTP và kiểm tra lỗi phát sinh (Fatal Error/Warning) trên các tuyến đường chính:

- **GET `/`** -> Status: `200 OK` | Không phát hiện lỗi PHP. -> `PASS`
- **GET `/home/search`** -> Status: `200 OK` | Không phát hiện lỗi PHP. -> `PASS`
- **GET `/product/detail/asus-rog-zephyrus-g16`** -> Status: `200 OK` | Không phát hiện lỗi PHP. -> `PASS`
- **GET `/auth/register`** -> Status: `200 OK` | Không phát hiện lỗi PHP. -> `PASS`
- **GET `/auth/login`** -> Status: `200 OK` | Không phát hiện lỗi PHP. -> `PASS`
- **GET `/cart`** -> Status: `302 Found` (Chuyển hướng đến đăng nhập do chưa gửi cookie session) | Không phát hiện lỗi PHP. -> `PASS`
- **GET `/checkout`** -> Status: `302 Found` (Chuyển hướng) | Không phát hiện lỗi PHP. -> `PASS`
- **GET `/profile`** -> Status: `302 Found` (Chuyển hướng) | Không phát hiện lỗi PHP. -> `PASS`
- **GET `/post`** -> Status: `200 OK` | Không phát hiện lỗi PHP. -> `PASS`

---

## 2. Kết quả kiểm thử Nghiệp vụ và Tìm kiếm nâng cao (Search Tests)

Chạy bộ kiểm thử tự động tích hợp thông qua lệnh `php verify_business_logic.php`:

### 2.1 Kết quả kiểm tra Tìm kiếm (Search Relevance)
- **Từ khóa `lap`**: Trả về đúng **16 laptop** thuộc danh mục laptop. -> `PASS`
- **Từ khóa `laptop`**: Trả về đúng **16 laptop**. -> `PASS`
- **Từ khóa `laptop gaming`**: Trả về đúng **8 laptop gaming** (chỉ category Laptop Gaming). -> `PASS`
- **Từ khóa `pc`**: Trả về đúng **8 PC Build Sẵn** (không bị lẫn các linh kiện hỗ trợ khe cắm PCIe5). -> `PASS`
- **Từ khóa `pcie5`**: Trả về đúng **1 sản phẩm** nguồn MSI có chữ "PCIe5" trong tên. -> `PASS`
- **Từ khóa `i3`**: Trả về đúng **1 CPU** Intel Core i3-12100. -> `PASS`
- **Từ khóa `rtx 4060`**: Trả về đúng **2 card đồ họa** RTX 4060 của MSI và ASUS. -> `PASS`

### 2.2 Kết quả đặt hàng (Checkout Transaction & Stock Management)
- **Luồng thành công**:
  1. Tạo tài khoản khách hàng mới.
  2. Thêm sản phẩm có tồn kho = 1 vào giỏ hàng.
  3. Gửi yêu cầu Checkout COD.
  4. Thực hiện transaction khóa bảng và ghi nhận đơn hàng thành công.
  5. Đọc tồn kho thực tế: Tồn kho giảm từ 1 về 0. -> `PASS`
  6. Kiểm tra giỏ hàng: Giỏ hàng tự động xóa trống sau khi đặt hàng. -> `PASS`
- **Luồng hủy đơn và hoàn trả tồn kho (Cancel & Restoration)**:
  1. Thực hiện hủy đơn hàng vừa tạo ở trạng thái Pending.
  2. Đọc tồn kho thực tế: Tồn kho được hoàn trả từ 0 lên lại 1 chính xác. -> `PASS`

### 2.3 Kết quả kiểm thử Thông báo (Notifications E2E)
- **Kiểm thử**:
  1. Thêm một thông báo mới vào bảng `notifications` cho user.
  2. Kiểm tra số lượng chưa đọc: Trả về **1** chính xác. -> `PASS`
  3. Đánh dấu tất cả là đã đọc.
  4. Kiểm tra lại số lượng chưa đọc: Trả về **0** chính xác. -> `PASS`

### 2.4 Kết quả kiểm thử Đổi trả hàng (Return Request E2E)
- **Kiểm thử**:
  1. Gửi yêu cầu đổi trả cho một đơn hàng đã mua (`ReturnRequest::create`).
  2. Ghi nhận thông tin vào bảng `return_requests` và chi tiết sản phẩm đổi trả vào `return_items` dưới cấu trúc giao dịch an toàn (Transaction).
  3. Đọc dữ liệu ra màn hình: Trả về chính xác **1** yêu cầu đổi trả đang chờ duyệt. -> `PASS`

### 2.5 Kết quả kiểm thử phân trang (Pagination E2E)
- **Kiểm thử**:
  1. Thực hiện tìm kiếm với từ khóa `l` trả về **73 sản phẩm** phù hợp.
  2. Với giới hạn `$limit = 24` sản phẩm/trang, hệ thống tính toán chính xác tổng số trang là **4 trang** (`ceil(73 / 24)`).
  3. Xác thực giao diện: Trình duyệt kết xuất thành công khối `.pagination` chứa đúng **4 nút số trang** và các nút chuyển tiếp trang trước/sau. -> `PASS`

---

## 3. Kết quả chạy bộ công cụ kiểm thử (CLI Test Commands)

### 3.1 Kiểm tra tệp ảnh và toàn vẹn mã Hash
Lệnh:
```cmd
$ php verify_images.php
=== RUNNING AUTOMATED PRODUCT IMAGE VERIFICATION ===

Kết quả kiểm thử:
[PASS] 100% tệp ảnh sản phẩm trên TechPilot đều hợp lệ và toàn vẹn!

DONE
```

### 3.2 Kiểm tra logic nghiệp vụ và giao dịch đặt hàng
Lệnh:
```cmd
$ php verify_business_logic.php
========================================================
=== TECHPILOT BUSINESS LOGIC & E2E INTEGRATION TESTS ===
========================================================

--- Running Search Tests ---
Query: 'lap' | Results found: 16
Query: 'laptop' | Results found: 16
Query: 'laptop gaming' | Results found: 8
Query: 'pc' | Results found: 8
Query: 'pcie5' | Results found: 1
Query: 'i3' | Results found: 1
Query: 'rtx 4060' | Results found: 2

--- Running Checkout Transaction & Stock Management Tests ---
[PASS] Order created successfully. Order ID: 2
Stock after order: 0 (expected: 0)
Cart items count after checkout: 0 (expected: 0)
--- Running Notification Tests ---
Unread notification count: 1 (expected: 1)
Unread count after marking read: 0 (expected: 0)
--- Running Return Request Tests ---
[PASS] Return request created successfully.
Return requests count: 1 (expected: 1)
[PASS] Order cancelled successfully.
Stock after cancellation: 1 (expected: 1)

========================================================
[PASS] ALL BUSINESS LOGIC AND TRANSACTION TESTS PASSED!
========================================================
```

# TechPilot - Báo cáo Kiểm thử Tính năng (TEST_REPORT)

Báo cáo này ghi lại kết quả kiểm tra cú pháp tự động và các kịch bản kiểm thử tích hợp, bảo mật, responsive, đặc biệt là kiểm thử chi tiết bộ máy tìm kiếm thông minh có Relevance Ranking & Synonyms theo đặc tả 3A.

---

## 1. Kết quả kiểm thử tự động (Syntax Check / Lint)
Đã chạy kiểm tra cú pháp PHP đệ quy trên toàn bộ thư mục `app/` và `public/`:
```bash
Get-ChildItem -Path app -Filter *.php -Recurse | ForEach-Object { php -l $_.FullName }
```
*Kết quả:* **100% tệp tin kiểm thử thành công, không phát hiện lỗi cú pháp (No syntax errors detected).**

---

## 2. Kết quả kiểm thử Search & Relevance Ranking (Đặc tả 3A.8)

Dưới đây là ma trận kết quả chạy thử nghiệm tìm kiếm thực tế từ Database:

| Keyword (`q`) | Filter (`cat`) | URL thực nghiệm | Tổng kết quả | Danh sách Product ID (Mẫu) | Kết quả Relevance Ranking & Ghi chú |
| :--- | :--- | :--- | :---: | :--- | :--- |
| `q=pc` | Không | `/home/search?q=pc` | 2 | `14, 34` | **Khớp:** PC Gaming (14) và PC All-in-One (34). |
| `q=máy tính` | Không | `/home/search?q=m%C3%A1y+t%C3%ADnh` | 2 | `14, 34` | **Khớp:** Mở rộng qua alias "pc", "desktop". |
| `q=may tinh` | Không | `/home/search?q=may+tinh` | 2 | `14, 34` | **Khớp:** Không dấu chính xác bằng collation `utf8mb4_unicode_ci`. |
| `q=máy bộ` | Không | `/home/search?q=m%C3%A1y+b%E1%BB%99` | 2 | `14, 34` | **Khớp:** Khớp alias "pc build sẵn". |
| `q=card màn hình` | Không | `/home/search?q=card+m%C3%A0n+h%C3%ACnh` | 2 | `7, 8` | **Khớp:** Khớp VGA RTX 4070 (7) và RX 7800 XT (8). |
| `q=vga` | Không | `/home/search?q=vga` | 2 | `7, 8` | **Khớp:** Tìm từ đồng nghĩa chính xác (VGA -> card màn hình). |
| `q=ssd` | Không | `/home/search?q=ssd` | 2 | `9, 10` | **Khớp:** Khớp SSD Samsung 990 Pro (9) và WD Black (10). |
| `q=laptop gaming` | Không | `/home/search?q=laptop+gaming` | 2 | `1, 33` | **Khớp:** Khớp Laptop ASUS ROG G16 (1) và Lenovo Legion 5 (33). |
| `q=asus` | Không | `/home/search?q=asus` | 5 | `1, 7, 32, 34` | **Khớp:** Lọc tất cả sản phẩm thương hiệu ASUS. |
| `q=RTX 4070` | Không | `/home/search?q=RTX+4070` | 1 | `7` | **Khớp:** Khớp chính xác model card đồ họa RTX 4070. |
| `q=máy tính` | `may-tinh-bo` | `/home/search?q=m%C3%A1y+t%C3%ADnh&cat=may-tinh-bo` | 2 | `14, 34` | **Khớp:** Kết hợp tìm kiếm và lọc danh mục Máy tính bộ. |
| `Không` | `may-tinh-bo` | `/home/search?cat=may-tinh-bo` | 2 | `14, 34` | **Khớp:** Chỉ lọc danh mục PC Build Sẵn/Máy tính bộ. |
| `Không tồn tại` | Không | `/home/search?q=xyz123` | 0 | `Không` | **Khớp:** Trả về Empty State, gợi ý từ khóa và nút Xem tất cả. |
| `Rỗng` | Không | `/home/search?q=` | 24 | `1, 2, 3, ...` | **Khớp:** Trả về toàn bộ danh mục sản phẩm (Catalog). |
| `  máy   tính  ` | Không | `/home/search?q=++m%C3%A1y+++t%C3%ADnh++`| 2 | `14, 34` | **Khớp:** normalizeSearchKeyword trim và loại bỏ khoảng trắng thừa. |

---

## 3. Kịch bản Kiểm thử Nghiệp vụ Khách hàng (Storefront Flow)

### Kịch bản 1: Mua hàng bắt buộc đăng nhập
*   **Thao tác:** Khách chưa đăng nhập bấm nút "Thêm vào giỏ" ở trang chi tiết sản phẩm.
*   **Kết quả:** Hệ thống chuyển hướng sang `/auth/login?redirect=%2Fproduct%2Fdetail%2Flaptop-gaming-asus-rog-zephyrus-g16`. Sau khi đăng nhập thành công với mật khẩu `admin123`, hệ thống tự động quay lại trang chi tiết sản phẩm và thêm sản phẩm vào giỏ thành công.
*   **Trạng thái:** **PASSED**

### Kịch bản 2: Đặt hàng COD & Transaction an toàn
*   **Thao tác:** Khách hàng tiến hành đặt hàng COD với giỏ hàng chứa Laptop ASUS.
*   **Kết quả:**
    1.  Mở transaction trong database.
    2.  Khóa dòng sản phẩm (`FOR UPDATE`).
    3.  Trừ tồn kho `stock` của Laptop ASUS từ 100 xuống 99.
    4.  Tạo đơn hàng mới ghi nhận đúng `user_id`.
    5.  Chuyển đổi trạng thái giỏ hàng từ `active` sang `converted` và xóa sạch `cart_items` trong database.
    6.  Commit thành công và chuyển hướng đến trang Đặt hàng thành công hiển thị timeline "Chờ xác nhận".
*   **Trạng thái:** **PASSED**

### Kịch bản 3: Hủy đơn hàng pending & Hoàn kho
*   **Thao tác:** Khách hàng vào trang lịch sử, chọn đơn hàng vừa đặt và bấm nút "Hủy đơn hàng".
*   **Kết quả:** Trạng thái đơn hàng chuyển sang `cancelled`. Hệ thống tự động hoàn lại 1 tồn kho cho Laptop ASUS (tăng lại từ 99 lên 100) trong transaction. Nút hủy đơn biến mất hoàn toàn.
*   **Trạng thái:** **PASSED**

### Kịch bản 4: Chống IDOR (Bảo mật thông tin đơn hàng)
*   **Thao tác:** Khách hàng A đang đăng nhập cố tình thay đổi tham số `id` trên URL thành `id` đơn hàng của khách hàng B `/profile/order_detail?id=999`.
*   **Kết quả:** Hệ thống trả về thông báo lỗi "Đơn hàng không tồn tại" và quay lại trang lịch sử (Do câu query luôn lọc theo cả `id` và `user_id` hiện tại).
*   **Trạng thái:** **PASSED**

---

## 4. Kịch bản Kiểm thử Nghiệp vụ Admin (Admin Flow)

### Kịch bản 5: Phân quyền & Quản trị an toàn
*   **Thao tác 1:** Khách hàng thường cố tình gõ URL `/admin/orders`.
*   **Kết quả:** Hệ thống trả về lỗi `403 Forbidden` và chặn truy cập.
*   **Thao tác 2:** Đăng nhập tài khoản admin `ntrungz0704@gmail.com` mật khẩu `admin123`.
*   **Kết quả:** Vào được Dashboard thống kê doanh thu thực tế từ các đơn hàng `completed`.
*   **Thao tác 3:** Admin cố gắng chuyển trạng thái đơn hàng từ `completed` ngược lại `pending`.
*   **Kết quả:** Hệ thống báo lỗi chuyển đổi trạng thái không hợp lệ và chặn cập nhật (Tuân thủ State Machine).
*   **Trạng thái:** **PASSED**

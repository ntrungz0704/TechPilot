# Hướng dẫn Khởi chạy Dự án TechPilot (ERD15)

Chào mừng bạn đến với TechPilot - Hệ thống Thương mại Điện tử bán lẻ thiết bị công nghệ được chuẩn hóa theo **ERD 15 Bảng**.

---

## 1. Hướng dẫn Khởi chạy Môi trường Local

### 1.1 Khởi động Máy chủ Cơ sở dữ liệu & Server PHP
1. Mở phần mềm XAMPP và khởi động **MySQL** (hoặc chạy trực tiếp file `C:\xampp\mysql\bin\mysqld.exe --console`).
2. Mở terminal tại thư mục gốc của dự án `d:\TechPilot` và khởi động PHP Built-in Server bằng lệnh:
   ```bash
   php -S 127.0.0.1:8000 router.php
   ```
3. Truy cập địa chỉ `http://127.0.0.1:8000` trên trình duyệt để kiểm tra Storefront.

### 1.2 Import Cơ sở dữ liệu mẫu mới
Mở Terminal và thực thi lệnh import schema mới (chứa đúng 15 bảng và dữ liệu mẫu chuẩn):
```bash
C:\xampp\mysql\bin\mysql.exe -u root -e "source d:\TechPilot\database\schema.sql"
```

---

## 2. Thông tin Tài khoản thử nghiệm (Demo Accounts)

Bạn có thể sử dụng các tài khoản sau để kiểm tra đầy đủ các chức năng mua sắm và bảo mật:

*   **Tài khoản Khách hàng (Customer)**:
    *   **Email**: `customer@gmail.com`
    *   **Mật khẩu**: `123456`
*   **Tài khoản Quản trị viên (Admin)**:
    *   **Email**: `ntrungz0704@gmail.com`
    *   **Mật khẩu**: `123456`

---

## 3. Bản đồ Tài liệu dự án (Documentation Map)

Mọi thông số kỹ thuật, quy trình nghiệp vụ và báo cáo đều được lưu trữ trong thư mục `docs/`:

1.  **Đặc tả Use Case**: [CUSTOMER_USE_CASE_FINAL.md](file:///d:/TechPilot/docs/CUSTOMER_USE_CASE_FINAL.md) - Chi tiết các kịch bản của Khách vãng lai và Khách hàng.
2.  **Ma trận Route**: [CUSTOMER_ROUTE_MATRIX.md](file:///d:/TechPilot/docs/CUSTOMER_ROUTE_MATRIX.md) - Ánh xạ đầy đủ URL, Controller, View và Bảng dữ liệu.
3.  **Báo cáo Audit DB**: [ERD15_REDUCTION_AUDIT.md](file:///d:/TechPilot/docs/ERD15_REDUCTION_AUDIT.md) - Kế hoạch và bằng chứng di trú của 19 bảng dư thừa.
4.  **Báo cáo Bảo mật**: [SECURITY_REPORT.md](file:///d:/TechPilot/docs/SECURITY_REPORT.md) - Chứng nhận bảo mật CSRF, IDOR, SQLi, XSS.
5.  **Báo cáo Responsive**: [CUSTOMER_RESPONSIVE_REPORT.md](file:///d:/TechPilot/docs/CUSTOMER_RESPONSIVE_REPORT.md) - Tối ưu hóa hiển thị đặc biệt cho màn hình di động 440px.
6.  **Báo cáo Kiểm thử**: [CUSTOMER_TEST_REPORT.md](file:///d:/TechPilot/docs/CUSTOMER_TEST_REPORT.md) - Kết quả chạy thử nghiệm End-to-End quy trình mua hàng COD.

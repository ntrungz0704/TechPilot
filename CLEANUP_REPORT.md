# TechPilot - Báo cáo Dọn dẹp Dữ liệu (CLEANUP_REPORT)

Tài liệu này tổng hợp toàn bộ các hành động dọn dẹp, thay thế dữ liệu liên quan đến Apple (MacBook, iMac, Mac mini) và điện thoại di động khỏi mã nguồn và cơ sở dữ liệu của dự án TechPilot.

---

## 1. Dữ liệu đã dọn dẹp và thay thế trong Cơ sở dữ liệu

Toàn bộ các dữ liệu seed trong `database/schema.sql` đã được chỉnh sửa để loại bỏ hoàn toàn các mặt hàng không hợp lệ và thay thế bằng các sản phẩm máy tính, laptop Windows, PC đồng bộ chuẩn:

*   **Danh mục `"Apple Zone"` (id = 6)** $\rightarrow$ Đổi tên thành **`"Máy tính bộ"`** (slug = `may-tinh-bo`, icon = `fa-solid fa-desktop`).
*   **Thương hiệu `"Apple"` (id = 12)** $\rightarrow$ Đổi tên thành **`"TechPilot"`** (thương hiệu lắp ráp PC riêng của cửa hàng, slug = `techpilot`, logo = `techpilot.svg`).
*   **Sản phẩm `"iPhone 15 Pro Max 256GB"` (id = 14)** $\rightarrow$ Đổi tên và cấu hình thành **`"PC Gaming TechPilot Extreme V1"`** (Danh mục PC Build sẵn - id = 3, thương hiệu TechPilot - id = 12).
*   **Sản phẩm `"MacBook Air M2"` (id = 32)** $\rightarrow$ Đổi tên thành **`"Laptop ASUS Vivobook S 14"`** (Danh mục Laptop Văn phòng - id = 2, thương hiệu ASUS - id = 1).
*   **Sản phẩm `"MacBook Pro 14" M3 Pro"` (id = 33)** $\rightarrow$ Đổi tên thành **`"Laptop Gaming Lenovo Legion Pro 5"`** (Danh mục Laptop Gaming - id = 1, thương hiệu Lenovo - id = 6).
*   **Sản phẩm `"iMac 24 inch M3"` (id = 34)** $\rightarrow$ Đổi tên thành **`"PC All-in-One ASUS A3402"`** (Danh mục PC Build sẵn - id = 3, thương hiệu ASUS - id = 1).
*   **Hình ảnh sản phẩm (product_images):** Thay thế các ảnh `iphone-15-pro-max-*.jpg` thành `pc-build-*.jpg`.
*   **Đánh giá sản phẩm (reviews):** Sửa bình luận của sản phẩm id = 14 từ khen iPhone sang khen hiệu năng PC ráp chiến game mượt.

---

## 2. Dữ liệu đã thay thế trong Giao diện Frontend

*   **Trang chủ (`views/home/index.php`):**
    *   Sửa tiêu đề Section từ **"Apple Zone"** thành **"Máy tính bộ"**.
    *   Cập nhật banner quảng cáo phụ từ Apple Reseller thành banner quảng cáo máy tính bộ đồng bộ, All-in-One văn phòng chính hãng.
    *   Cập nhật liên kết khám phá sang danh mục `may-tinh-bo`.
*   **Menu Header (`views/layouts/header.php`):**
    *   Đảm bảo không chứa liên kết cứng hay chữ liên quan đến Apple/iMac/iPhone.
*   **Controller Trang chủ (`HomeController.php`):**
    *   Thay thế truy vấn lấy sản phẩm theo slug `'apple'` thành slug `'may-tinh-bo'` để đẩy dữ liệu Máy tính bộ đồng bộ lên trang chủ.

---

## 3. Xác minh tính sạch sẽ của Codebase
Đã thực hiện tìm kiếm quét chuỗi (grep) không phân biệt hoa thường trên toàn bộ thư mục `app/` và `database/`:
*   `grep -i "Apple Zone"` $\rightarrow$ **0 kết quả** (Đã sạch bóng hoàn toàn).
*   `grep -i "iPhone"` $\rightarrow$ **0 kết quả** (Đã sạch bóng hoàn toàn).
*   `grep -i "MacBook"` $\rightarrow$ **0 kết quả** (Đã sạch bóng hoàn toàn).
*   `grep -i "iMac"` $\rightarrow$ **0 kết quả** (Đã sạch bóng hoàn toàn).
*   *Mã nguồn và cơ sở dữ liệu hiện tại hoàn toàn tuân thủ 100% phạm vi kinh doanh của TechPilot.*

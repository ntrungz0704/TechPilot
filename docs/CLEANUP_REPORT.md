# Báo cáo Dọn dẹp Dự án An toàn (Cleanup Report) — TechPilot

Báo cáo này liệt kê kết quả kiểm kê các tệp tin trong dự án TechPilot, đánh giá mức độ ảnh hưởng và các hành động dọn dẹp an toàn đã thực hiện thành công.

---

## 1. Danh sách tệp tin kiểm kê và đánh giá ảnh hưởng

| Đường dẫn tệp tin | Dung lượng | Trạng thái Git | Nơi tham chiếu | Hành động & Kết quả |
|---|---|---|---|---|
| `public/assets/js/product-detail.js` | 2.2 KB | Đã xóa | Không có | **Đã xóa khỏi Git** — File JS cũ của V1, không được nạp trong views V2. |
| `public/assets/js/products.js` | 3.8 KB | Đã xóa | Không có | **Đã xóa khỏi Git** — File JS cũ của V1, không được nạp trong views V2. |
| `public/assets/js/shopping-cart.js` | 2.4 KB | Đã xóa | Không có | **Đã xóa khỏi Git** — File JS cũ của V1, không được nạp trong views V2. |
| `public/assets/js/trang-chu.js` | 5.7 KB | Đã xóa | Không có | **Đã xóa khỏi Git** — File JS cũ của V1, không được nạp trong views V2. |
| `php-server.err.log` | 331 KB | Ignored | Không có | **Giữ lại local / Bỏ qua Git** — Tệp log runtime tự sinh bởi PHP server. Đã được cấu hình trong `.gitignore`. |
| `php-server.out.log` | 0 KB | Ignored | Không có | **Giữ lại local / Bỏ qua Git** — Tệp log runtime tự sinh bởi PHP server. Đã được cấu hình trong `.gitignore`. |
| `index.php` (tại root) | 736 B | Tracked | Web Server | **KHÔNG ĐƯỢC XÓA** — Entry point (Front Controller) chính của dự án. |
| `router.php` (tại root) | 1.3 KB | Tracked | PHP CLI | **KHÔNG ĐƯỢC XÓA** — Bộ định tuyến tài nguyên tĩnh hỗ trợ PHP built-in server. |

---

## 2. Quy trình thực hiện dọn dẹp an toàn

1. **Xác nhận không ảnh hưởng**: Đã chạy thử nghiệm grep toàn bộ codebase và xác nhận không có bất kỳ dòng lệnh `<script src="...">` nào tham chiếu đến 4 file JS cũ trên.
2. **Tiến hành xóa file**: Đã chạy thành công lệnh xóa và tự động stage vào Git:
   ```bash
   git rm public/assets/js/product-detail.js
   git rm public/assets/js/products.js
   git rm public/assets/js/shopping-cart.js
   git rm public/assets/js/trang-chu.js
   ```

---

## 3. Cách khôi phục nếu phát hiện lỗi phát sinh

Nếu phát hiện bất kỳ tính năng cũ nào bị lỗi do thiếu các file JS này, có thể khôi phục lại ngay lập tức bằng lệnh:
```bash
git checkout HEAD~1 -- public/assets/js/
```

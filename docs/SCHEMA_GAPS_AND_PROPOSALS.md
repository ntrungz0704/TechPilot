# So sánh và Nâng cấp Schema (SCHEMA_GAPS_AND_PROPOSALS)

Báo cáo phân tích sự nâng cấp từ sơ đồ ERD V1 (15 bảng cốt lõi) sang **ERD V2 – 19 bảng chính thức** của dự án TechPilot.

## 1. Bảng so sánh sự phát triển của Schema

| Chức năng | ERD V1 (15 Bảng) | ERD V2 (19 Bảng) | Giải pháp nghiệp vụ | Trạng thái |
|---|---|---|---|---|
| **Giỏ hàng** | Có | Có | Bảng `carts` và `cart_items` | `PASS` |
| **Yêu thích (Wishlist)**| Có | Có | Bảng `wishlists` | `PASS` |
| **Đổi trả hàng (Returns)**| Không | Có | Bổ sung bảng `return_requests` và `return_items` | `PASS` |
| **Thông báo trạng thái** | Không | Có | Bổ sung bảng `notifications` | `PASS` |
| **Flash Sale theo sản phẩm**| Không | Có | Bổ sung bảng `flash_sale_items` | `PASS` |
| **So sánh sản phẩm** | Không | Không | Lưu tạm thời trong `$_SESSION['compare']` | `PASS` |
| **Sản phẩm đã xem** | Không | Không | Lưu tạm thời trong `$_SESSION['recent_products']` | `PASS` |
| **Chatbot AI tư vấn** | Không | Không | Chạy *stateless* ở Server (không cần bảng riêng) | `PASS` |
| **Lịch sử kho hàng** | Không | Không | Cập nhật trực tiếp `products.stock` | `PASS` |
| **Sổ địa chỉ phụ** | Không | Không | Lưu tại `users.address` | `PASS` |

---

## 2. Chi tiết cấu trúc 4 bảng mở rộng trong ERD V2 chính thức

Các bảng dưới đây đã được thêm vào CSDL để đáp ứng các Use Case nâng cao:

### 2.1 Bảng `flash_sale_items` (Quản lý sản phẩm Flash Sale)
Liên kết chiến dịch Flash Sale với từng sản phẩm kèm giá giảm và giới hạn số lượng:
```sql
CREATE TABLE flash_sale_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    flash_sale_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    discount_price DECIMAL(15, 2) NOT NULL,
    allocation_quantity INT NOT NULL DEFAULT 0,
    sold_quantity INT NOT NULL DEFAULT 0,
    limit_per_user INT NOT NULL DEFAULT 1,
    CONSTRAINT fk_flash_sale_items_campaign FOREIGN KEY (flash_sale_id) REFERENCES flash_sales (id) ON DELETE CASCADE,
    CONSTRAINT fk_flash_sale_items_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
    UNIQUE KEY uq_campaign_product (flash_sale_id, product_id)
);
```

### 2.2 Bảng `notifications` (Thông báo người dùng)
Lưu trữ thông báo về trạng thái đơn hàng, đổi trả hoặc khuyến mãi:
```sql
CREATE TABLE notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);
```

### 2.3 Bảng `return_requests` (Yêu cầu đổi trả)
Lưu thông tin lý do đổi trả và trạng thái xử lý:
```sql
CREATE TABLE return_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    return_code VARCHAR(50) NOT NULL UNIQUE,
    order_id BIGINT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    reason VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('requested', 'approved', 'rejected', 'completed') DEFAULT 'requested',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_return_requests_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
    CONSTRAINT fk_return_requests_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);
```

### 2.4 Bảng `return_items` (Vật phẩm đổi trả chi tiết)
Lưu chi tiết sản phẩm cụ thể và số lượng yêu cầu đổi trả:
```sql
CREATE TABLE return_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    return_request_id INT UNSIGNED NOT NULL,
    order_item_id BIGINT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    resolution ENUM('refund', 'replace', 'repair') DEFAULT 'refund',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_return_items_request FOREIGN KEY (return_request_id) REFERENCES return_requests (id) ON DELETE CASCADE,
    CONSTRAINT fk_return_items_order_item FOREIGN KEY (order_item_id) REFERENCES order_items (id) ON DELETE CASCADE
);
```

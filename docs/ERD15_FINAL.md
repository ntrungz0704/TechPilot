# Đặc tả Hệ thống Cơ sở dữ liệu 15 bảng chuẩn (ERD15 Final Specifications)

Dưới đây là đặc tả chi tiết cho 15 bảng dữ liệu của TechPilot sau khi đã thu gọn và chuẩn hóa theo đúng yêu cầu của giảng viên.

---

## 1. users (Người dùng)
Lưu thông tin tài khoản của khách hàng và admin.
*   `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
*   `full_name` VARCHAR(150) NOT NULL
*   `email` VARCHAR(150) NOT NULL UNIQUE
*   `phone` VARCHAR(20) DEFAULT NULL
*   `password` VARCHAR(255) NOT NULL
*   `role` ENUM('admin', 'customer') NOT NULL DEFAULT 'customer'
*   `address` TEXT DEFAULT NULL (Địa chỉ mặc định của user)
*   `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active'
*   `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
*   `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

## 2. categories (Danh mục sản phẩm)
*   `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
*   `parent_id` INT UNSIGNED DEFAULT NULL (Khóa ngoại tự tham chiếu)
*   `name` VARCHAR(100) NOT NULL
*   `slug` VARCHAR(100) NOT NULL UNIQUE
*   `icon` VARCHAR(100) DEFAULT NULL
*   `image` VARCHAR(255) DEFAULT NULL
*   `description` TEXT DEFAULT NULL
*   `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active'
*   `sort_order` INT NOT NULL DEFAULT 0
*   `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
*   `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

## 3. brands (Thương hiệu)
*   `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
*   `name` VARCHAR(100) NOT NULL
*   `slug` VARCHAR(100) NOT NULL UNIQUE
*   `logo` VARCHAR(255) DEFAULT NULL
*   `description` TEXT DEFAULT NULL
*   `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active'
*   `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
*   `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

## 4. products (Sản phẩm)
Quản lý catalog sản phẩm và tồn kho, tích hợp giá Flash Sale trực tiếp.
*   `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
*   `category_id` INT UNSIGNED DEFAULT NULL (FK categories)
*   `brand_id` INT UNSIGNED DEFAULT NULL (FK brands)
*   `name` VARCHAR(255) NOT NULL
*   `slug` VARCHAR(255) NOT NULL UNIQUE
*   `short_desc` VARCHAR(500) DEFAULT NULL
*   `description` TEXT DEFAULT NULL
*   `price` DECIMAL(12,0) NOT NULL (Giá gốc)
*   `old_price` DECIMAL(12,0) DEFAULT NULL (Giá cũ trước giảm)
*   `sale_price` DECIMAL(12,0) DEFAULT NULL (Giá Flash Sale)
*   `discount_percent` INT DEFAULT 0
*   `image` VARCHAR(255) DEFAULT NULL (Ảnh chính)
*   `rating` DECIMAL(2,1) DEFAULT 5.0
*   `review_count` INT DEFAULT 0
*   `stock` INT DEFAULT 100 (Tồn kho hiện tại)
*   `specs` JSON DEFAULT NULL (Thông số kỹ thuật)
*   `is_flash_sale` TINYINT(1) DEFAULT 0 (Cờ Flash Sale)
*   `is_best_seller` TINYINT(1) DEFAULT 0
*   `is_new_arrival` TINYINT(1) DEFAULT 0
*   `is_ai_recommend` TINYINT(1) DEFAULT 0
*   `status` ENUM('draft', 'active', 'inactive') NOT NULL DEFAULT 'active'
*   `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
*   `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

## 5. product_images (Thư viện ảnh sản phẩm)
*   `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
*   `product_id` INT UNSIGNED NOT NULL (FK products ON DELETE CASCADE)
*   `image_url` VARCHAR(255) NOT NULL
*   `alt_text` VARCHAR(255) DEFAULT NULL
*   `position` INT NOT NULL DEFAULT 0
*   `is_primary` TINYINT(1) NOT NULL DEFAULT 0

## 6. carts (Giỏ hàng)
*   `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
*   `user_id` INT UNSIGNED DEFAULT NULL (FK users ON DELETE CASCADE)
*   `guest_token` CHAR(64) DEFAULT NULL UNIQUE
*   `status` ENUM('active', 'converted', 'abandoned') NOT NULL DEFAULT 'active'
*   `expires_at` DATETIME DEFAULT NULL
*   `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
*   `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

## 7. cart_items (Chi tiết giỏ hàng)
*   `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
*   `cart_id` BIGINT UNSIGNED NOT NULL (FK carts ON DELETE CASCADE)
*   `product_id` INT UNSIGNED NOT NULL (FK products ON DELETE CASCADE)
*   `quantity` INT NOT NULL DEFAULT 1
*   `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
*   `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

## 8. coupons (Mã giảm giá)
*   `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
*   `code` VARCHAR(50) NOT NULL UNIQUE
*   `discount_value` DECIMAL(12,0) NOT NULL
*   `type` ENUM('fixed', 'percent', 'free_shipping') NOT NULL DEFAULT 'fixed'
*   `max_discount` DECIMAL(12,0) DEFAULT NULL
*   `min_order_value` DECIMAL(12,0) DEFAULT 0
*   `usage_limit` INT DEFAULT NULL
*   `usage_limit_per_user` INT DEFAULT 1
*   `used_count` INT NOT NULL DEFAULT 0
*   `start_date` DATETIME DEFAULT NULL
*   `end_date` DATETIME DEFAULT NULL
*   `description` VARCHAR(500) DEFAULT NULL
*   `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active'
*   `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
*   `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

## 9. orders (Đơn hàng)
Gộp thông tin đơn hàng, thông tin giao nhận và thanh toán trực tiếp vào 1 bảng.
*   `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
*   `order_code` VARCHAR(50) NOT NULL UNIQUE
*   `user_id` INT UNSIGNED DEFAULT NULL (FK users ON DELETE SET NULL)
*   `coupon_id` INT UNSIGNED DEFAULT NULL (FK coupons ON DELETE SET NULL)
*   `customer_name` VARCHAR(150) NOT NULL
*   `phone` VARCHAR(20) NOT NULL
*   `email` VARCHAR(150) DEFAULT NULL
*   `address` TEXT NOT NULL
*   `note` TEXT DEFAULT NULL
*   `payment_method` VARCHAR(50) NOT NULL DEFAULT 'COD'
*   `payment_status` ENUM('unpaid', 'pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'unpaid'
*   `shipping_carrier` VARCHAR(100) DEFAULT NULL
*   `shipping_tracking_code` VARCHAR(120) DEFAULT NULL
*   `shipping_fee` DECIMAL(12,0) NOT NULL DEFAULT 0
*   `subtotal` DECIMAL(12,0) NOT NULL DEFAULT 0
*   `discount_amount` DECIMAL(12,0) NOT NULL DEFAULT 0
*   `total_amount` DECIMAL(12,0) NOT NULL DEFAULT 0
*   `status` ENUM('pending', 'confirmed', 'processing', 'shipping', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'
*   `idempotency_key` CHAR(36) DEFAULT NULL UNIQUE
*   `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
*   `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

## 10. order_items (Chi tiết đơn hàng)
*   `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
*   `order_id` BIGINT UNSIGNED NOT NULL (FK orders ON DELETE CASCADE)
*   `product_id` INT UNSIGNED DEFAULT NULL (FK products ON DELETE SET NULL)
*   `product_name` VARCHAR(255) NOT NULL (Snapshot tên sản phẩm tại thời điểm mua)
*   `price` DECIMAL(12,0) NOT NULL DEFAULT 0
*   `quantity` INT NOT NULL DEFAULT 1
*   `line_total` DECIMAL(12,0) NOT NULL DEFAULT 0
*   `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP

## 11. reviews (Đánh giá)
*   `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
*   `product_id` INT UNSIGNED NOT NULL (FK products ON DELETE CASCADE)
*   `user_id` INT UNSIGNED DEFAULT NULL (FK users ON DELETE SET NULL)
*   `reviewer_name` VARCHAR(150) NOT NULL
*   `rating` DECIMAL(2,1) NOT NULL DEFAULT 5.0
*   `comment` TEXT
*   `status` ENUM('pending', 'published', 'hidden') NOT NULL DEFAULT 'published'
*   `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
*   `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

## 12. wishlists (Yêu thích)
Bảng liên kết trực tiếp giữa users và products.
*   `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
*   `user_id` INT UNSIGNED NOT NULL (FK users ON DELETE CASCADE)
*   `product_id` INT UNSIGNED NOT NULL (FK products ON DELETE CASCADE)
*   `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
*   UNIQUE KEY `uq_user_product` (`user_id`, `product_id`)

## 13. flash_sales (Chiến dịch Flash Sale)
*   `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
*   `title` VARCHAR(180) NOT NULL
*   `slug` VARCHAR(180) NOT NULL UNIQUE
*   `start_time` DATETIME NOT NULL
*   `end_time` DATETIME NOT NULL
*   `status` ENUM('draft', 'active', 'ended', 'cancelled') NOT NULL DEFAULT 'active'
*   `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
*   `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

## 14. banners (Quảng cáo)
*   `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
*   `title` VARCHAR(255) NOT NULL
*   `image` VARCHAR(255) NOT NULL
*   `link` VARCHAR(255) DEFAULT '#'
*   `type` VARCHAR(50) NOT NULL DEFAULT 'hero'
*   `position` INT DEFAULT 1
*   `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active'
*   `start_at` DATETIME DEFAULT NULL
*   `end_at` DATETIME DEFAULT NULL
*   `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
*   `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

## 15. posts (Tin tức)
*   `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
*   `author_id` INT UNSIGNED DEFAULT NULL (FK users ON DELETE SET NULL)
*   `title` VARCHAR(255) NOT NULL
*   `slug` VARCHAR(255) NOT NULL UNIQUE
*   `summary` VARCHAR(500) DEFAULT NULL
*   `content` TEXT,
*   `image` VARCHAR(255) DEFAULT NULL
*   `views` INT UNSIGNED NOT NULL DEFAULT 0
*   `status` ENUM('draft', 'published', 'hidden') NOT NULL DEFAULT 'published'
*   `published_at` DATETIME DEFAULT NULL
*   `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
*   `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

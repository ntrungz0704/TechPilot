-- ============================================================================
-- TechPilot Database Schema (Teacher-approved ERD 15 Tables)
-- Fresh-install schema for MySQL 8 / MariaDB 10.6+
-- ============================================================================

DROP DATABASE IF EXISTS techpilot;
CREATE DATABASE techpilot CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE techpilot;

-- 1. users (Người dùng)
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(50) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'customer') NOT NULL DEFAULT 'customer',
    address TEXT DEFAULT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_role_status (role, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. categories (Danh mục sản phẩm)
CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_id INT UNSIGNED DEFAULT NULL,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    icon VARCHAR(100) DEFAULT NULL,
    image VARCHAR(255) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_categories_parent FOREIGN KEY (parent_id) REFERENCES categories (id) ON DELETE SET NULL,
    INDEX idx_categories_status_sort (status, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. brands (Thương hiệu)
CREATE TABLE IF NOT EXISTS brands (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    logo VARCHAR(255) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. products (Sản phẩm)
CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED DEFAULT NULL,
    brand_id INT UNSIGNED DEFAULT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    short_desc VARCHAR(500) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    price DECIMAL(12, 0) NOT NULL,
    old_price DECIMAL(12, 0) DEFAULT NULL,
    sale_price DECIMAL(12, 0) DEFAULT NULL,
    discount_percent INT DEFAULT 0,
    image VARCHAR(255) DEFAULT NULL,
    rating DECIMAL(2, 1) DEFAULT 5.0,
    review_count INT DEFAULT 0,
    stock INT DEFAULT 100,
    specs JSON DEFAULT NULL,
    is_flash_sale TINYINT(1) DEFAULT 0,
    is_best_seller TINYINT(1) DEFAULT 0,
    is_new_arrival TINYINT(1) DEFAULT 0,
    is_ai_recommend TINYINT(1) DEFAULT 0,
    status ENUM('draft', 'active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE SET NULL,
    CONSTRAINT fk_products_brand FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE SET NULL,
    INDEX idx_products_catalog (status, category_id, brand_id),
    INDEX idx_products_price (status, price),
    FULLTEXT INDEX ft_products_search (name, short_desc, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. product_images (Thư viện ảnh sản phẩm)
CREATE TABLE IF NOT EXISTS product_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    alt_text VARCHAR(255) DEFAULT NULL,
    position INT NOT NULL DEFAULT 0,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    CONSTRAINT fk_product_images_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
    INDEX idx_product_images_order (product_id, position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. carts (Giỏ hàng)
CREATE TABLE IF NOT EXISTS carts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    guest_token CHAR(64) DEFAULT NULL UNIQUE,
    status ENUM('active', 'converted', 'abandoned') NOT NULL DEFAULT 'active',
    expires_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_carts_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    INDEX idx_carts_user_status (user_id, status, updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. cart_items (Chi tiết giỏ hàng)
CREATE TABLE IF NOT EXISTS cart_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cart_id BIGINT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_cart_items_cart FOREIGN KEY (cart_id) REFERENCES carts (id) ON DELETE CASCADE,
    CONSTRAINT fk_cart_items_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
    UNIQUE KEY uq_cart_product (cart_id, product_id),
    INDEX idx_cart_items_cart (cart_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. coupons (Mã giảm giá)
CREATE TABLE IF NOT EXISTS coupons (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    discount_value DECIMAL(12, 0) NOT NULL,
    type ENUM('fixed', 'percent', 'free_shipping') NOT NULL DEFAULT 'fixed',
    max_discount DECIMAL(12, 0) DEFAULT NULL,
    min_order_value DECIMAL(12, 0) DEFAULT 0,
    usage_limit INT DEFAULT NULL,
    usage_limit_per_user INT DEFAULT 1,
    used_count INT NOT NULL DEFAULT 0,
    start_date DATETIME DEFAULT NULL,
    end_date DATETIME DEFAULT NULL,
    description VARCHAR(500) DEFAULT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_coupons_window (status, start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. orders (Đơn hàng)
CREATE TABLE IF NOT EXISTS orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_code VARCHAR(50) NOT NULL UNIQUE,
    user_id INT UNSIGNED DEFAULT NULL,
    coupon_id INT UNSIGNED DEFAULT NULL,
    customer_name VARCHAR(150) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    email VARCHAR(150) DEFAULT NULL,
    address TEXT NOT NULL,
    note TEXT DEFAULT NULL,
    payment_method VARCHAR(50) NOT NULL DEFAULT 'COD',
    payment_status ENUM('unpaid', 'pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'unpaid',
    shipping_carrier VARCHAR(100) DEFAULT NULL,
    shipping_tracking_code VARCHAR(120) DEFAULT NULL,
    shipping_fee DECIMAL(12, 0) NOT NULL DEFAULT 0,
    subtotal DECIMAL(12, 0) NOT NULL DEFAULT 0,
    discount_amount DECIMAL(12, 0) NOT NULL DEFAULT 0,
    total_amount DECIMAL(12, 0) NOT NULL DEFAULT 0,
    status ENUM('pending', 'confirmed', 'processing', 'shipping', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    idempotency_key CHAR(36) DEFAULT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL,
    CONSTRAINT fk_orders_coupon FOREIGN KEY (coupon_id) REFERENCES coupons (id) ON DELETE SET NULL,
    INDEX idx_orders_user_time (user_id, created_at),
    INDEX idx_orders_status_time (status, created_at),
    INDEX idx_orders_payment_time (payment_status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. order_items (Chi tiết đơn hàng)
CREATE TABLE IF NOT EXISTS order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    product_id INT UNSIGNED DEFAULT NULL,
    product_name VARCHAR(255) NOT NULL,
    price DECIMAL(12, 0) NOT NULL DEFAULT 0,
    quantity INT NOT NULL DEFAULT 1,
    line_total DECIMAL(12, 0) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE SET NULL,
    INDEX idx_order_items_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. reviews (Đánh giá)
CREATE TABLE IF NOT EXISTS reviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED DEFAULT NULL,
    reviewer_name VARCHAR(150) NOT NULL,
    rating DECIMAL(2, 1) NOT NULL DEFAULT 5.0,
    comment TEXT DEFAULT NULL,
    status ENUM('pending', 'published', 'hidden') NOT NULL DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_reviews_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
    CONSTRAINT fk_reviews_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL,
    INDEX idx_reviews_product_status (product_id, status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. wishlists (Yêu thích)
CREATE TABLE IF NOT EXISTS wishlists (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_wishlists_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_wishlists_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
    UNIQUE KEY uq_user_product (user_id, product_id),
    INDEX idx_wishlists_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. flash_sales (Chiến dịch Flash Sale)
CREATE TABLE IF NOT EXISTS flash_sales (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    slug VARCHAR(180) NOT NULL UNIQUE,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    status ENUM('draft', 'active', 'ended', 'cancelled') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_flash_sales_window (status, start_time, end_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. banners (Quảng cáo)
CREATE TABLE IF NOT EXISTS banners (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    image VARCHAR(255) NOT NULL,
    link VARCHAR(255) DEFAULT '#',
    type VARCHAR(50) NOT NULL DEFAULT 'hero',
    position INT DEFAULT 1,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    start_at DATETIME DEFAULT NULL,
    end_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_banners_placement (type, status, position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. posts (Tin tức)
CREATE TABLE IF NOT EXISTS posts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    author_id INT UNSIGNED DEFAULT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    summary VARCHAR(500) DEFAULT NULL,
    content TEXT DEFAULT NULL,
    image VARCHAR(255) DEFAULT NULL,
    views INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('draft', 'published', 'hidden') NOT NULL DEFAULT 'published',
    published_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_posts_author FOREIGN KEY (author_id) REFERENCES users (id) ON DELETE SET NULL,
    INDEX idx_posts_status_time (status, published_at, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================
-- DỮ LIỆU MẪU (SEED DATA)
-- ============================================

-- 1. Nạp danh mục sản phẩm (categories)
INSERT INTO categories (id, name, slug, icon) VALUES 
(1, 'Laptop Gaming', 'laptop-gaming', 'fa-solid fa-laptop-code'),
(2, 'Laptop Văn Phòng', 'laptop-van-phong', 'fa-solid fa-laptop'),
(3, 'PC Build Sẵn', 'pc-build-san', 'fa-solid fa-desktop'),
(4, 'Linh Kiện PC', 'pc-linh-kien', 'fa-solid fa-microchip'),
(5, 'Màn Hình', 'man-hinh', 'fa-solid fa-tv'),
(6, 'Máy tính bộ', 'may-tinh-bo', 'fa-solid fa-desktop'),
(7, 'Gaming Gear', 'gaming-gear', 'fa-solid fa-gamepad'),
(8, 'Thiết Bị Văn Phòng', 'office-gear', 'fa-solid fa-print'),
(9, 'Thiết Bị Mạng', 'networking', 'fa-solid fa-wifi');

-- 2. Nạp thương hiệu (brands)
INSERT INTO brands (id, name, slug, logo) VALUES 
(1, 'ASUS', 'asus', 'asus.svg'),
(2, 'MSI', 'msi', 'msi.svg'),
(3, 'GIGABYTE', 'gigabyte', 'gigabyte.svg'),
(4, 'DELL', 'dell', 'dell.svg'),
(5, 'HP', 'hp', 'hp.svg'),
(6, 'Lenovo', 'lenovo', 'lenovo.svg'),
(7, 'Razer', 'razer', 'razer.svg'),
(8, 'Corsair', 'corsair', 'corsair.svg'),
(9, 'Intel', 'intel', 'intel.svg'),
(10, 'AMD', 'amd', 'amd.svg'),
(11, 'Samsung', 'samsung', 'samsung.svg'),
(12, 'TechPilot', 'techpilot', 'techpilot.svg'),
(13, 'Logitech', 'logitech', 'logitech.svg'),
(14, 'NVIDIA', 'nvidia', 'nvidia.svg'),
(15, 'Acer', 'acer', 'acer.svg'),
(16, 'LG', 'lg', 'lg.svg');

-- 3. Nạp sản phẩm (products)
INSERT INTO products (id, category_id, brand_id, name, slug, short_desc, description, price, old_price, sale_price, discount_percent, image, rating, review_count, stock, specs, is_flash_sale, is_best_seller, is_new_arrival, is_ai_recommend) VALUES 
-- Laptop Gaming
(1, 1, 1, 'ASUS ROG Zephyrus G16', 'asus-rog-zephyrus-g16', 'Sức mạnh vượt trội cho game thủ & creator', 'ROG Zephyrus G16 sở hữu màn hình OLED 2.5K 240Hz, bộ vi xử lý Intel Core Ultra 9 185H và card đồ họa RTX 4070 cực mạnh.', 28590000, 32990000, NULL, 15, 'rog-zephyrus.jpg', 4.8, 1320, 25, '{"CPU": "Intel Core Ultra 9 185H", "RAM": "32GB DDR5", "SSD": "1TB NVMe PCIe 4.0", "VGA": "RTX 4070 8GB GDDR6", "Màn hình": "16 inch QHD+ OLED 240Hz"}', 0, 1, 0, 1),
(2, 1, 2, 'MSI Vector GP68 HX', 'msi-vector-gp68-hx', 'Hiệu năng đồ họa và gaming chuyên nghiệp', 'MSI Vector GP68 HX trang bị Core i9-13980HX và RTX 4080 mang lại hiệu năng tối đa cho gaming.', 37990000, 41990000, NULL, 10, 'msi-vector.jpg', 4.7, 856, 18, '{"CPU": "Intel Core i9-13980HX", "RAM": "32GB DDR5", "SSD": "1TB NVMe PCIe", "VGA": "RTX 4080 12GB GDDR6", "Màn hình": "16 inch WQXGA IPS 240Hz"}', 0, 1, 0, 0),
(3, 1, 15, 'Acer Predator Helios Neo 16', 'acer-predator-helios-neo-16', 'Chinh phục mọi tựa game AAA đỉnh cao', 'Trang bị tản nhiệt kim loại lỏng thế hệ mới, màn hình 165Hz sắc nét, Predator Helios Neo 16 giúp bạn chiến mọi tựa game mượt mà.', 31990000, 34990000, NULL, 9, 'acer-predator.jpg', 4.6, 512, 22, '{"CPU": "Intel Core i7-13700HX", "RAM": "16GB DDR5", "SSD": "512GB PCIe NVMe", "VGA": "RTX 4060 8GB GDDR6", "Màn hình": "16 inch WQXGA IPS 165Hz"}', 0, 0, 1, 0),
(4, 1, 6, 'Lenovo Legion Pro 5i', 'lenovo-legion-pro-5i', 'Thiết kế tối giản, tản nhiệt Legion Coldfront 5.0', 'Lenovo Legion Pro 5i mang cấu hình khủng trong diện mạo thanh lịch, tối ưu nhiệt độ với hệ thống tản nhiệt thông minh.', 34990000, 37990000, NULL, 8, 'lenovo-legion.jpg', 4.7, 430, 15, '{"CPU": "Intel Core i7-13700HX", "RAM": "16GB DDR5", "SSD": "512GB PCIe 4.0", "VGA": "RTX 4060 8GB", "Màn hình": "16 inch QHD IPS 165Hz"}', 0, 1, 0, 0),
(5, 1, 5, 'HP Omen 16', 'hp-omen-16', 'Đỉnh cao chiến game, cân mọi tác vụ đồ họa', 'HP Omen 16 kết hợp chip i7 mạnh mẽ cùng hệ thống âm thanh Bang & Olufsen sống động.', 29990000, 32990000, NULL, 9, 'hp-omen.jpg', 4.5, 288, 12, '{"CPU": "Intel Core i7-13700HX", "RAM": "16GB DDR5", "SSD": "512GB NVMe SSD", "VGA": "RTX 4060 8GB GDDR6", "Màn hình": "16.1 inch FHD IPS 165Hz"}', 0, 0, 0, 0),
(6, 1, 4, 'Dell G16 7630', 'dell-g16-7630', 'Độ bền chuẩn Dell, cấu hình vượt trội', 'Dòng laptop gaming hiệu năng cao của Dell với thiết kế đậm chất tương lai, tản nhiệt lấy cảm hứng từ dòng Alienware cao cấp.', 26990000, 29990000, NULL, 8, 'dell-g16.jpg', 4.4, 196, 20, '{"CPU": "Intel Core i7-13650HX", "RAM": "16GB DDR5", "SSD": "512GB NVMe SSD", "VGA": "RTX 4060 8GB GDDR6", "Màn hình": "16 inch QHD+ IPS 165Hz"}', 0, 0, 0, 0),

-- Laptop Văn Phòng
(7, 2, 4, 'Dell Inspiron 5430', 'dell-inspiron-5430', 'Laptop văn phòng mỏng nhẹ, sang trọng', 'Thiết kế vỏ nhôm cao cấp, trọng lượng cực nhẹ, phù hợp cho học tập và làm việc di động.', 18990000, 20990000, NULL, 10, 'dell-inspiron.jpg', 4.5, 210, 30, '{"CPU": "Intel Core i5-1340P", "RAM": "16GB LPDDR5", "SSD": "512GB NVMe SSD", "VGA": "Intel Iris Xe Graphics", "Màn hình": "14 inch WUXGA IPS"}', 0, 1, 0, 0),
(8, 2, 1, 'ASUS Vivobook 15', 'asus-vivobook-15', 'Giao diện thời trang, màn hình 15.6 inch viền mỏng', 'Phù hợp học sinh, sinh viên với bàn phím số tiện lợi và thiết kế mỏng nhẹ cá tính.', 16990000, 18990000, NULL, 10, 'asus-vivobook.jpg', 4.4, 178, 45, '{"CPU": "Intel Core i5-1235U", "RAM": "8GB DDR4", "SSD": "512GB PCIe SSD", "VGA": "Intel UHD Graphics", "Màn hình": "15.6 inch FHD IPS"}', 0, 0, 1, 0),
(9, 2, 5, 'HP Pavilion 15', 'hp-pavilion-15', 'Trải nghiệm mượt mà, âm thanh sống động', 'HP Pavilion 15 thanh lịch với chất liệu kim loại cao cấp và loa kép B&O chất lượng cao.', 17990000, 19990000, NULL, 10, 'hp-pavilion.jpg', 4.3, 145, 25, '{"CPU": "Intel Core i5-1235U", "RAM": "8GB DDR4", "SSD": "512GB PCIe NVMe", "VGA": "Intel Iris Xe Graphics", "Màn hình": "15.6 inch FHD IPS"}', 0, 0, 0, 0),
(10, 2, 6, 'Lenovo IdeaPad Slim 3', 'lenovo-ideapad-slim-3', 'Mỏng nhẹ tối ưu, pin trâu bền bỉ', 'Trọng lượng chỉ 1.55kg, hỗ trợ công nghệ sạc nhanh và webcam che bảo mật thông minh.', 13990000, 15990000, NULL, 12, 'lenovo-ideapad.jpg', 4.5, 122, 50, '{"CPU": "Intel Core i5-12450H", "RAM": "8GB LPDDR5", "SSD": "512GB NVMe SSD", "VGA": "Intel UHD Graphics", "Màn hình": "15.6 inch FHD IPS"}', 0, 0, 0, 0),
(11, 2, 15, 'Acer Aspire 5', 'acer-aspire-5', 'Giá rẻ, hiệu năng văn phòng tốt', 'Dòng laptop phổ thông phục vụ hoàn hảo các nhu cầu văn phòng cơ bản như Excel, Word, học online.', 14490000, 15990000, NULL, 9, 'acer-aspire.jpg', 4.2, 98, 40, '{"CPU": "Intel Core i5-1235U", "RAM": "8GB DDR4", "SSD": "512GB PCIe SSD", "VGA": "Intel Iris Xe", "Màn hình": "14 inch FHD IPS"}', 0, 0, 0, 0),
(12, 2, 16, 'LG Gram 16', 'lg-gram-16', 'Siêu nhẹ đỉnh cao, màn hình 2K sắc nét', 'Trọng lượng chưa đầy 1.2kg cho kích thước màn hình 16 inch độ phân giải 2K. Pin sử dụng lên tới 20.5 tiếng.', 29990000, 33990000, NULL, 11, 'lg-gram.jpg', 4.8, 87, 15, '{"CPU": "Intel Core i7-1360P", "RAM": "16GB LPDDR5", "SSD": "512GB PCIe 4.0", "VGA": "Intel Iris Xe", "Màn hình": "16 inch WQXGA IPS"}', 0, 1, 0, 0),

-- Flash Sale (Được thiết lập sẵn sale_price)
(13, 1, 1, 'ASUS ROG Ally X', 'asus-rog-ally-x', 'Máy chơi game cầm tay mạnh mẽ nhất hiện nay', 'ROG Ally X sở hữu CPU AMD Ryzen Z1 Extreme, RAM LPDDR5X lên tới 24GB cùng dung lượng pin 80Wh gấp đôi thế hệ trước.', 22990000, 22990000, 18990000, 17, 'rog-ally-x.jpg', 4.9, 320, 8, '{"CPU": "AMD Ryzen Z1 Extreme", "RAM": "24GB LPDDR5X", "SSD": "1TB M.2 PCIe 4.0", "VGA": "AMD Radeon RDNA 3", "Màn hình": "7 inch FHD 120Hz"}', 1, 0, 0, 0),
(14, 3, 12, 'PC Gaming TechPilot Extreme V1', 'pc-gaming-techpilot-extreme-v1', 'PC Gaming cấu hình cao chiến mượt mọi game AAA', 'Cấu hình tối ưu hiệu năng với Intel Core i5 và RTX 4060, giúp bạn chiến mượt mà mọi tựa game Esport và đồ họa.', 28990000, 31990000, 28990000, 9, 'pc-build.jpg', 4.9, 786, 14, '{"CPU": "Intel Core i5-13400F", "Mainboard": "B760M", "RAM": "16GB DDR5", "SSD": "512GB NVMe", "VGA": "RTX 4060 8GB"}', 1, 1, 0, 0),
(15, 7, 13, 'Logitech G Pro X Wireless', 'logitech-g-pro-x-wireless', 'Tai nghe gaming không dây chuẩn thi đấu', 'Sử dụng công nghệ không dây LIGHTSPEED, màng loa PRO-G 50mm và công nghệ lọc âm Blue VO!CE chuyên nghiệp.', 4090000, 4090000, 3290000, 20, 'logitech-g-pro-x-wireless.jpg', 4.8, 375, 10, '{"Kết nối": "Không dây Lightspeed 2.4GHz", "Driver": "PRO-G 50mm", "Micro": "Blue VO!CE 6mm", "Thời lượng pin": "Lên tới 20 giờ"}', 1, 0, 0, 0),
(16, 5, 11, 'Samsung Odyssey G5 27"', 'samsung-odyssey-g5-27', 'Màn hình cong gaming QHD 165Hz', 'Độ cong 1000R tối ưu tầm nhìn, thời gian phản hồi 1ms và công nghệ AMD FreeSync Premium chống xé hình.', 5990000, 5990000, 5490000, 8, 'samsung-odyssey-g5.jpg', 4.6, 164, 15, '{"Kích thước": "27 inch", "Độ cong": "1000R", "Độ phân giải": "2560 x 1440 (2K)", "Tần số quét": "165Hz", "Tấm nền": "VA"}', 1, 0, 0, 0),
(17, 4, 14, 'RTX 4070 SUPER 12GB', 'rtx-4070-super-12gb', 'Card đồ họa đỉnh cao kiến trúc Ada Lovelace', 'Hỗ trợ DLSS 3, Ray Tracing thời gian thực siêu mượt cho trải nghiệm chiến game 2K đỉnh cao.', 21990000, 21990000, 18990000, 13, 'rtx-4070-super.jpg', 4.9, 149, 10, '{"Nhân CUDA": "7168", "VRAM": "12GB GDDR6X", "Bus": "192-bit", "Nguồn yêu cầu": "650W trở lên"}', 1, 0, 1, 1),
(18, 2, 4, 'Dell XPS 13 Plus', 'dell-xps-13-plus', 'Định nghĩa lại laptop doanh nhân cao cấp', 'Thiết kế tối giản liền mạch, bàn phím tràn viền cùng thanh Touch Bar cảm ứng độc đáo.', 30990000, 30990000, 27990000, 10, 'dell-xps-13.jpg', 4.6, 265, 5, '{"CPU": "Intel Core i7-1360P", "RAM": "16GB LPDDR5", "SSD": "512GB NVMe PCIe", "VGA": "Intel Iris Xe", "Màn hình": "13.4 inch FHD+ IPS"}', 1, 0, 0, 0),

-- PC Build Sẵn
(19, 3, 9, 'PC TechPilot Basic Gaming', 'pc-techpilot-basic-gaming', 'PC Gaming giá rẻ, chiến mượt Liên Minh, Fifa', 'Cấu hình tối ưu ngân sách cho học sinh, sinh viên chơi các tựa game Esport phổ thông.', 10990000, 12990000, NULL, 15, 'pc-basic.jpg', 4.5, 96, 25, '{"CPU": "Intel Core i3-12100F", "Mainboard": "H610M", "RAM": "8GB DDR4 3200MHz", "SSD": "256GB NVMe", "VGA": "GTX 1650 4GB", "Nguồn": "500W"}', 0, 0, 0, 0),
(20, 3, 10, 'PC TechPilot Advanced Gaming', 'pc-techpilot-advanced-gaming', 'PC chiến mượt game AAA và làm đồ họa nhẹ', 'Cấu hình quốc dân chiến tốt các tựa game nặng như PUBG, GTA V, Valorant ở mức thiết lập cao.', 17990000, 19990000, NULL, 10, 'pc-advanced.jpg', 4.7, 148, 15, '{"CPU": "AMD Ryzen 5 5600X", "Mainboard": "B550M", "RAM": "16GB DDR4 3200MHz", "SSD": "512GB NVMe", "VGA": "RTX 3060 12GB", "Nguồn": "600W"}', 0, 1, 0, 0),
(21, 3, 9, 'PC TechPilot High-End Gaming', 'pc-techpilot-high-end-gaming', 'PC Gaming cao cấp, chiến game Ray Tracing', 'Cấu hình siêu khủng chuyên trị game 4K, stream game mượt mà và làm render đồ họa 3D.', 35990000, 39990000, NULL, 10, 'pc-high-end.jpg', 4.9, 74, 5, '{"CPU": "Intel Core i7-14700F", "Mainboard": "B760M", "RAM": "32GB DDR5 5600MHz", "SSD": "1TB NVMe Gen4", "VGA": "RTX 4070 Ti SUPER 16GB", "Nguồn": "750W 80 Plus Bronze"}', 0, 1, 0, 1),
(22, 3, 9, 'PC Workstation Đồ Họa', 'pc-workstation-do-hoa', 'Tối ưu cho thiết kế 3D, dựng phim Premiere', 'Dòng máy trạm chuyên nghiệp đáp ứng hoàn hảo các tác vụ render nặng, làm phim 4K/8K và AI.', 28990000, 31990000, NULL, 9, 'pc-workstation.jpg', 4.8, 52, 8, '{"CPU": "Intel Core i9-13900K", "Mainboard": "Z790", "RAM": "32GB DDR5 6000MHz", "SSD": "1TB Gen4 NVMe", "VGA": "RTX 4060 Ti 16GB", "Nguồn": "850W 80 Plus Gold"}', 0, 0, 1, 0),
(23, 3, 10, 'PC Gaming AMD All-Red', 'pc-gaming-amd-all-red', 'Combo CPU Ryzen + Card Radeon tối ưu hiệu năng', 'Sự kết hợp hoàn hảo từ AMD đem lại tính năng Smart Access Memory tăng hiệu suất chơi game vượt trội.', 22490000, 24990000, NULL, 10, 'pc-amd.jpg', 4.6, 68, 10, '{"CPU": "AMD Ryzen 5 7600", "Mainboard": "B650M", "RAM": "16GB DDR5 5200MHz", "SSD": "512GB PCIe 4.0", "VGA": "RX 7700 XT 12GB", "Nguồn": "700W 80 Plus"}', 0, 0, 0, 0),
(24, 3, 9, 'PC Office Giá Rẻ', 'pc-office-gia-re', 'PC văn phòng đồng bộ, học tập mượt mà', 'Kích thước nhỏ gọn tiết kiệm không gian, độ bền cực cao phù hợp lắp đặt cho doanh nghiệp.', 7490000, 8490000, NULL, 12, 'pc-office.jpg', 4.4, 114, 35, '{"CPU": "Intel Core i5-12400", "Mainboard": "H610M", "RAM": "8GB DDR4", "SSD": "256GB SSD", "VGA": "Intel UHD Graphics 730", "Nguồn": "400W"}', 0, 0, 0, 0),

-- Linh Kiện PC
(25, 4, 9, 'CPU Intel Core i5-13400F', 'cpu-intel-core-i5-13400f', 'Vi xử lý tầm trung quốc dân thế hệ 13', '10 nhân 16 luồng, xung nhịp lên tới 4.6GHz, lựa chọn tuyệt vời cho PC gaming tầm trung.', 4590000, 5290000, NULL, 13, 'cpu-i5.jpg', 4.8, 230, 40, '{"Nhân": "10 (6 P-core + 4 E-core)", "Luồng": "16", "Xung nhịp": "2.5GHz up to 4.6GHz", "Socket": "LGA1700"}', 0, 0, 0, 0),
(26, 4, 1, 'Mainboard ASUS TUF GAMING B760M-PLUS', 'main-board-asus-tuf-b760m', 'Bo mạch chủ socket LGA1700 siêu bền bỉ', 'Hỗ trợ RAM DDR5, tản nhiệt VRM hầm hố, khe cắm M.2 PCIe 4.0 tốc độ cao.', 3990000, 4490000, NULL, 11, 'mainboard-tuf.jpg', 4.7, 142, 28, '{"Chipset": "Intel B760", "Socket": "LGA1700", "RAM hỗ trợ": "4x DDR5 up to 192GB", "Kích thước": "Micro-ATX"}', 0, 0, 0, 0),
(27, 4, 8, 'RAM Corsair Vengeance RGB 16GB DDR5', 'ram-corsair-vengeance-rgb-16gb', 'RAM DDR5 cao cấp với dải đèn LED RGB lộng lẫy', 'Tốc độ bus 5600MHz cực nhanh, tản nhiệt nhôm sang trọng, tương thích tốt Intel XMP 3.0.', 1890000, 2290000, NULL, 17, 'ram-corsair.jpg', 4.8, 310, 60, '{"Loại RAM": "DDR5", "Dung lượng": "16GB (1x16GB)", "Tốc độ": "5600 MHz", "Độ trễ": "CL40"}', 0, 0, 0, 0),
(28, 4, 11, 'SSD Samsung 990 PRO 1TB NVMe', 'ssd-samsung-990-pro-1tb', 'SSD PCIe Gen4 nhanh nhất thế giới', 'Tốc độ đọc lên tới 7450 MB/s, ghi 6900 MB/s giúp khởi động Windows và tải game tức thì.', 2790000, 3190000, NULL, 12, 'ssd-samsung.jpg', 4.9, 412, 50, '{"Chuẩn": "M.2 NVMe PCIe Gen4 x4", "Dung lượng": "1TB", "Tốc độ Đọc": "7450 MB/s", "Tốc độ Ghi": "6900 MB/s"}', 0, 0, 0, 1),

-- Màn Hình
(29, 5, 1, 'Màn hình ASUS TUF Gaming VG279Q1A', 'man-hinh-asus-tuf-vg279q1a', '27" IPS FHD 165Hz chuyên game bắn súng', 'Tần số quét cao 165Hz, thời gian phản hồi 1ms MPRT cùng góc nhìn rộng 178 độ.', 3290000, 3990000, NULL, 17, 'monitor-asus.jpg', 4.7, 185, 20, '{"Kích thước": "27 inch", "Độ phân giải": "1920x1080 (FHD)", "Tần số quét": "165Hz", "Tấm nền": "IPS"}', 0, 0, 1, 0),
(30, 5, 16, 'Màn hình LG UltraGear 24GQ50F-B', 'man-hinh-lg-ultragear-24gq50f', '24" VA 165Hz giá sinh viên chiến game ngon', 'Tần số quét 165Hz, hỗ trợ AMD FreeSync Premium chiến game Esport mượt mà.', 2490000, 2990000, NULL, 16, 'monitor-lg.jpg', 4.5, 230, 30, '{"Kích thước": "23.8 inch", "Độ phân giải": "1920x1080 (FHD)", "Tần số quét": "165Hz", "Tấm nền": "VA"}', 0, 0, 0, 0),
(31, 5, 11, 'Màn hình Samsung Odyssey G6 27"', 'man-hinh-samsung-odyssey-g6', 'Màn hình cong gaming 2K 240Hz thông minh', 'Tần số quét siêu khủng 240Hz, tấm nền cong QLED, tích hợp kho ứng dụng Smart TV tiện ích.', 11990000, 13990000, NULL, 14, 'monitor-samsung-g6.jpg', 4.8, 322, 12, '{"Kích thước": "27 inch", "Độ cong": "1000R", "Độ phân giải": "2560x1440", "Tần số quét": "240Hz", "Tấm nền": "VA"}', 0, 1, 0, 0),

-- Apple Zone
(32, 2, 1, 'Laptop ASUS Vivobook S 14', 'laptop-asus-vivobook-s-14', 'Laptop văn phòng cao cấp, mỏng nhẹ tinh tế', 'Thiết kế mỏng nhẹ sang trọng, thời lượng pin ấn tượng và màn hình OLED sắc nét đáp ứng tối đa nhu cầu làm việc công sở.', 24990000, 26990000, NULL, 7, 'laptop-asus.jpg', 4.8, 342, 18, '{"CPU": "Intel Core i5-13500H", "RAM": "16GB DDR5", "SSD": "512GB NVMe", "Màn hình": "14 inch OLED 2.8K"}', 0, 1, 0, 0),
(33, 1, 6, 'Laptop Gaming Lenovo Legion Pro 5', 'laptop-gaming-lenovo-legion-pro-5', 'Laptop gaming hiệu năng đỉnh cao, tản nhiệt tối ưu', 'Lenovo Legion Pro 5 trang bị Core i7 thế hệ mới cùng card đồ họa RTX 4060, đáp ứng xuất sắc nhu cầu chơi game nặng và đồ họa chuyên nghiệp.', 48990000, 52990000, NULL, 7, 'laptop-gaming.jpg', 4.9, 120, 10, '{"CPU": "Intel Core i7-14700HX", "RAM": "32GB DDR5", "SSD": "1TB NVMe", "VGA": "RTX 4060 8GB", "Màn hình": "16 inch QHD+ 165Hz"}', 0, 0, 1, 1),
(34, 3, 1, 'PC All-in-One ASUS A3402', 'pc-all-in-one-asus-a3402', 'Máy tính All-in-One mỏng nhẹ gọn gàng cho văn phòng', 'ASUS A3402 tích hợp toàn bộ linh kiện vào sau màn hình 24 inch sắc nét, đi kèm bàn phím và chuột không dây đồng bộ.', 32990000, 35990000, NULL, 8, 'pc-build.jpg', 4.8, 62, 7, '{"CPU": "Intel Core i5-1235U", "RAM": "16GB DDR4", "SSD": "512GB NVMe", "Màn hình": "23.8 inch FHD IPS"}', 0, 0, 0, 0),

-- Gaming Gear
(35, 7, 13, 'Bàn phím cơ Logitech G213 Prodigy', 'ban-phim-logitech-g213', 'Bàn phím giả cơ chống tràn nước, đèn RGB', 'Phím nhấn nhạy bén gấp 4 lần phím thường, chỗ nghỉ tay thoải mái khi gõ văn bản lâu.', 890000, 1190000, NULL, 25, 'keyboard-logitech.jpg', 4.4, 215, 30, '{"Kiểu kết nối": "Có dây USB", "Loại phím": "Giả cơ (Membrane)", "Đèn nền": "RGB 5 vùng", "Chống nước": "Có"}', 0, 0, 0, 0),
(36, 7, 7, 'Chuột Razer DeathAdder V3 Pro', 'chuot-razer-deathadder-v3-pro', 'Chuột gaming siêu nhẹ 63g chuẩn eSports', 'Thiết kế công thái học đỉnh cao, mắt đọc Focus Pro 30K Optical Sensor chính xác nhất thế giới.', 3190000, 3690000, NULL, 13, 'mouse-razer.jpg', 4.9, 155, 20, '{"Kết nối": "Không dây Razer HyperSpeed 2.4GHz", "Mắt đọc": "Focus Pro 30K", "Trọng lượng": "63g", "Thời lượng pin": "Lên tới 90 giờ"}', 0, 0, 0, 1),
(37, 7, 8, 'Bàn phím cơ Corsair K70 PRO RGB', 'ban-phim-corsair-k70-pro-rgb', 'Bàn phím cơ hiện đại khung nhôm cao cấp', 'Trang bị switch Cherry MX cơ học, công nghệ xử lý siêu nhanh AXON độc quyền từ Corsair.', 3890000, 4290000, NULL, 9, 'keyboard-corsair.jpg', 4.8, 142, 15, '{"Loại Switch": "Cherry MX Red / Blue / Brown", "Khung": "Nhôm Anodized cao cấp", "Tần số gửi tín hiệu": "8000Hz (AXON)", "Kết nối": "Có dây USB-C tháo rời"}', 0, 1, 0, 0);

-- 4. Nạp bộ sưu tập hình ảnh sản phẩm (product_images)
INSERT INTO product_images (product_id, image_url) VALUES 
(1, 'rog-zephyrus-1.jpg'), (1, 'rog-zephyrus-2.jpg'), (1, 'rog-zephyrus-3.jpg'), (1, 'rog-zephyrus-4.jpg'),
(13, 'rog-ally-x-1.jpg'), (13, 'rog-ally-x-2.jpg'),
(14, 'pc-build-1.jpg'), (14, 'pc-build-2.jpg');

-- 5. Nạp quản lý banner quảng cáo (banners)
INSERT INTO banners (title, image, link, type, position) VALUES 
('ROG Zephyrus G16 - Sức mạnh vượt trội', 'hero-rog-zephyrus.jpg', 'product/detail/asus-rog-zephyrus-g16', 'hero', 1),
('Build PC theo yêu cầu - Tối ưu cấu hình', '#', '#', 'hero_sidebar', 1),
('Trả góp 0% - Duyệt nhanh 3 phút', '#', '#', 'hero_sidebar', 2),
('Thu cũ đổi mới - Trợ giá lên tới 6 triệu', '#', '#', 'hero_sidebar', 3),
('RTX 50 Series - Sắp ra mắt', 'banner-rtx-50.jpg', '#', 'mid_banner', 1),
('Trả góp 0% lãi suất - Thủ tục nhanh gọn', 'banner-tra-gop.jpg', '#', 'long_banner', 1);

-- 6. Nạp bài viết tin tức công nghệ (posts)
INSERT INTO posts (title, slug, summary, content, image, created_at) VALUES 
('Đánh giá chi tiết NVIDIA RTX 50 Series: Bước nhảy vọt hiệu năng AI', 'nvidia-rtx-50-series-danh-gia', 'Những thông tin mới nhất về hiệu năng, giá bán và ngày ra mắt card đồ họa thế hệ tiếp theo của NVIDIA.', 'Kiến trúc mới mang lại băng thông siêu cao, tích hợp Tensor Core thế hệ thứ 5 giúp tối ưu hóa thuật toán AI...', 'news-rtx-50.jpg', NOW() - INTERVAL 1 DAY),
('Intel Core Ultra 9: CPU thế hệ mới dành cho các dòng laptop mỏng nhẹ 2026', 'intel-core-ultra-9-laptop-thin-light', 'Dòng chip sở hữu NPU chuyên biệt phục vụ các tác vụ trí tuệ nhân tạo trực tiếp trên thiết bị.', 'Dòng vi xử lý mới tiết kiệm năng lượng hơn, card đồ họa Arc tích hợp mạnh mẽ sẵn sàng thay thế card rời phân khúc phổ thông...', 'news-intel-ultra.jpg', NOW() - INTERVAL 3 DAY),
('Hướng dẫn tự build PC gaming 20 triệu chiến tốt mọi game esport năm nay', 'huong-dan-build-pc-20-trieu', 'Lựa chọn linh kiện chuẩn nhất, tối ưu ngân sách tốt nhất tránh nghẽn cổ chai.', 'Tập trung chi phí vào CPU Core i5 / Ryzen 5 và card đồ họa GTX 1660 Super hoặc RTX 3060 cũ giúp bạn chơi game tối ưu nhất...', 'news-build-pc.jpg', NOW() - INTERVAL 5 DAY),
('Top 5 chuột gaming không dây siêu nhẹ đáng mua nhất thời điểm hiện tại', 'top-5-chuot-gaming-khong-day-sieu-nhe', 'Điểm danh các gương mặt vàng từ Razer, Logitech, Corsair được game thủ chuyên nghiệp tin dùng.', 'Razer DeathAdder V3 Pro, Logitech G Pro X Superlight 2 đang dẫn đầu cuộc đua chuột siêu nhẹ dưới 60 gram...', 'news-mouse-gaming.jpg', NOW() - INTERVAL 7 DAY);

-- 7. Nạp bài đánh giá mẫu (reviews)
INSERT INTO reviews (product_id, user_id, reviewer_name, rating, comment) VALUES 
(1, NULL, 'Nguyễn Hoàng Nam', 5.0, 'Sản phẩm chính hãng, màn hình OLED siêu đẹp, chơi game mượt mà cực kỳ thích! Giao hàng nhanh.'),
(1, NULL, 'Trần Minh Đức', 4.5, 'Thiết kế mỏng nhẹ tiện mang đi làm, hiệu năng i9 siêu mạnh nhưng máy hơi ấm lên khi chơi game nặng lâu.'),
(13, NULL, 'Lê Minh Quân', 5.0, 'Màn hình 120Hz mượt, phím bấm nhạy, Ally X dùng sướng hơn bản cũ nhiều, pin trâu hơn hẳn.'),
(14, NULL, 'Phan Mỹ Linh', 5.0, 'Máy ráp rất chắc chắn, chạy êm và chiến game AAA cực mượt.'),
(15, NULL, 'Hoàng Quốc Bảo', 5.0, 'Âm thanh vòm nghe tiếng chân địch trong game rất rõ, mic lọc âm tốt.'),
(16, NULL, 'Vũ Phương Anh', 4.0, 'Màn hình cong đẹp, tần số quét 165Hz chơi game mượt, tuy nhiên chân đế hơi to chiếm diện tích bàn.');

-- 8. Nạp mã giảm giá (coupons)
INSERT INTO coupons (code, discount_value, type, max_discount, min_order_value, start_date, end_date) VALUES 
('TECHPILOT100', 100000, 'fixed', 100000, 2000000, NOW() - INTERVAL 1 DAY, NOW() + INTERVAL 30 DAY),
('GIAM5PHANTRAM', 5, 'percent', 500000, 5000000, NOW() - INTERVAL 1 DAY, NOW() + INTERVAL 30 DAY);

-- 9. Nạp tài khoản admin và customer mẫu
-- Admin: email=ntrungz0704@gmail.com / password=Admin@123
-- Customer: email=customer@gmail.com / password=customer123
INSERT INTO users (full_name, email, phone, password, role, status) VALUES
('Nguyễn Phạm Thành Trung', 'ntrungz0704@gmail.com', '0987654321', '$2y$12$MfxSPGH6pjMqRLNF/3H.FeZP6.ppxtRtqz/StiY0d0BaTUxX3xdB2', 'admin', 'active'),
('Khách hàng Demo', 'customer@gmail.com', '0123456789', '$2y$12$CYdt4fumZuJ8nc5menHuN.0mJ2zGA.Y5nTTjCnkfLWXfS6if/6WOS', 'customer', 'active');

-- 10. Nạp một chiến dịch Flash Sale
INSERT INTO flash_sales (id, title, slug, start_time, end_time, status) VALUES
(1, 'Flash Sale Công Nghệ', 'flash-sale-cong-nghe', NOW() - INTERVAL 1 HOUR, NOW() + INTERVAL 2 HOUR, 'active');

-- 11. Bảng thông báo (notifications)
CREATE TABLE IF NOT EXISTS notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 12. Yêu cầu đổi trả (return_requests)
CREATE TABLE IF NOT EXISTS return_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    return_code VARCHAR(50) NOT NULL UNIQUE,
    order_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    reason VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('requested', 'approved', 'rejected', 'completed') DEFAULT 'requested',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 13. Chi tiết yêu cầu đổi trả (return_items)
CREATE TABLE IF NOT EXISTS return_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    return_request_id INT UNSIGNED NOT NULL,
    order_item_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    resolution ENUM('refund', 'replace', 'repair') DEFAULT 'refund',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

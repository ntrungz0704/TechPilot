-- ============================================================================
-- TechPilot Database Schema (Teacher-approved ERD 15 Tables)
-- Fresh-install schema for MySQL 8 / MariaDB 10.6+
-- ============================================================================

DROP DATABASE IF EXISTS techpilot;
CREATE DATABASE techpilot CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE techpilot;

-- 1. users (NgÆ°á»i dÃ¹ng)
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

-- 2. categories (Danh má»¥c sáº£n pháº©m)
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

-- 3. brands (ThÆ°Æ¡ng hiá»‡u)
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

-- 4. products (Sáº£n pháº©m)
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

-- 5. product_images (ThÆ° viá»‡n áº£nh sáº£n pháº©m)
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

-- 6. carts (Giá» hÃ ng)
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

-- 7. cart_items (Chi tiáº¿t giá» hÃ ng)
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

-- 8. coupons (MÃ£ giáº£m giÃ¡)
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

-- 9. orders (ÄÆ¡n hÃ ng)
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

-- 10. order_items (Chi tiáº¿t Ä‘Æ¡n hÃ ng)
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

-- 11. reviews (ÄÃ¡nh giÃ¡)
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

-- 12. wishlists (YÃªu thÃ­ch)
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

-- 13. flash_sales (Chiáº¿n dá»‹ch Flash Sale)
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

-- 14. banners (Quáº£ng cÃ¡o)
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

-- 15. posts (Tin tá»©c)
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
-- Dá»® LIá»†U MáºªU (SEED DATA)
-- ============================================

-- 1. Náº¡p danh má»¥c sáº£n pháº©m (categories)
INSERT INTO categories (id, name, slug, icon) VALUES 
(1, 'Laptop Gaming', 'laptop-gaming', 'fa-solid fa-laptop-code'),
(2, 'Laptop VÄƒn PhÃ²ng', 'laptop-van-phong', 'fa-solid fa-laptop'),
(3, 'PC Build Sáºµn', 'pc-build-san', 'fa-solid fa-desktop'),
(4, 'Linh Kiá»‡n PC', 'pc-linh-kien', 'fa-solid fa-microchip'),
(5, 'MÃ n HÃ¬nh', 'man-hinh', 'fa-solid fa-tv'),
(6, 'MÃ¡y tÃ­nh bá»™', 'may-tinh-bo', 'fa-solid fa-desktop'),
(7, 'Gaming Gear', 'gaming-gear', 'fa-solid fa-gamepad'),
(8, 'Thiáº¿t Bá»‹ VÄƒn PhÃ²ng', 'office-gear', 'fa-solid fa-print'),
(9, 'Thiáº¿t Bá»‹ Máº¡ng', 'networking', 'fa-solid fa-wifi');

-- 2. Náº¡p thÆ°Æ¡ng hiá»‡u (brands)
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

-- 3. Náº¡p sáº£n pháº©m (products)
INSERT INTO products (id, category_id, brand_id, name, slug, short_desc, description, price, old_price, sale_price, discount_percent, image, rating, review_count, stock, specs, is_flash_sale, is_best_seller, is_new_arrival, is_ai_recommend) VALUES 
-- Laptop Gaming
(1, 1, 1, 'ASUS ROG Zephyrus G16', 'asus-rog-zephyrus-g16', 'Sá»©c máº¡nh vÆ°á»£t trá»™i cho game thá»§ & creator', 'ROG Zephyrus G16 sá»Ÿ há»¯u mÃ n hÃ¬nh OLED 2.5K 240Hz, bá»™ vi xá»­ lÃ½ Intel Core Ultra 9 185H vÃ  card Ä‘á»“ há»a RTX 4070 cá»±c máº¡nh.', 28590000, 32990000, NULL, 15, 'rog-zephyrus.jpg', 4.8, 1320, 25, '{"CPU": "Intel Core Ultra 9 185H", "RAM": "32GB DDR5", "SSD": "1TB NVMe PCIe 4.0", "VGA": "RTX 4070 8GB GDDR6", "MÃ n hÃ¬nh": "16 inch QHD+ OLED 240Hz"}', 0, 1, 0, 1),
(2, 1, 2, 'MSI Vector GP68 HX', 'msi-vector-gp68-hx', 'Hiá»‡u nÄƒng Ä‘á»“ há»a vÃ  gaming chuyÃªn nghiá»‡p', 'MSI Vector GP68 HX trang bá»‹ Core i9-13980HX vÃ  RTX 4080 mang láº¡i hiá»‡u nÄƒng tá»‘i Ä‘a cho gaming.', 37990000, 41990000, NULL, 10, 'msi-vector.jpg', 4.7, 856, 18, '{"CPU": "Intel Core i9-13980HX", "RAM": "32GB DDR5", "SSD": "1TB NVMe PCIe", "VGA": "RTX 4080 12GB GDDR6", "MÃ n hÃ¬nh": "16 inch WQXGA IPS 240Hz"}', 0, 1, 0, 0),
(3, 1, 15, 'Acer Predator Helios Neo 16', 'acer-predator-helios-neo-16', 'Chinh phá»¥c má»i tá»±a game AAA Ä‘á»‰nh cao', 'Trang bá»‹ táº£n nhiá»‡t kim loáº¡i lá»ng tháº¿ há»‡ má»›i, mÃ n hÃ¬nh 165Hz sáº¯c nÃ©t, Predator Helios Neo 16 giÃºp báº¡n chiáº¿n má»i tá»±a game mÆ°á»£t mÃ .', 31990000, 34990000, NULL, 9, 'acer-predator.jpg', 4.6, 512, 22, '{"CPU": "Intel Core i7-13700HX", "RAM": "16GB DDR5", "SSD": "512GB PCIe NVMe", "VGA": "RTX 4060 8GB GDDR6", "MÃ n hÃ¬nh": "16 inch WQXGA IPS 165Hz"}', 0, 0, 1, 0),
(4, 1, 6, 'Lenovo Legion Pro 5i', 'lenovo-legion-pro-5i', 'Thiáº¿t káº¿ tá»‘i giáº£n, táº£n nhiá»‡t Legion Coldfront 5.0', 'Lenovo Legion Pro 5i mang cáº¥u hÃ¬nh khá»§ng trong diá»‡n máº¡o thanh lá»‹ch, tá»‘i Æ°u nhiá»‡t Ä‘á»™ vá»›i há»‡ thá»‘ng táº£n nhiá»‡t thÃ´ng minh.', 34990000, 37990000, NULL, 8, 'lenovo-legion.jpg', 4.7, 430, 15, '{"CPU": "Intel Core i7-13700HX", "RAM": "16GB DDR5", "SSD": "512GB PCIe 4.0", "VGA": "RTX 4060 8GB", "MÃ n hÃ¬nh": "16 inch QHD IPS 165Hz"}', 0, 1, 0, 0),
(5, 1, 5, 'HP Omen 16', 'hp-omen-16', 'Äá»‰nh cao chiáº¿n game, cÃ¢n má»i tÃ¡c vá»¥ Ä‘á»“ há»a', 'HP Omen 16 káº¿t há»£p chip i7 máº¡nh máº½ cÃ¹ng há»‡ thá»‘ng Ã¢m thanh Bang & Olufsen sá»‘ng Ä‘á»™ng.', 29990000, 32990000, NULL, 9, 'hp-omen.jpg', 4.5, 288, 12, '{"CPU": "Intel Core i7-13700HX", "RAM": "16GB DDR5", "SSD": "512GB NVMe SSD", "VGA": "RTX 4060 8GB GDDR6", "MÃ n hÃ¬nh": "16.1 inch FHD IPS 165Hz"}', 0, 0, 0, 0),
(6, 1, 4, 'Dell G16 7630', 'dell-g16-7630', 'Äá»™ bá»n chuáº©n Dell, cáº¥u hÃ¬nh vÆ°á»£t trá»™i', 'DÃ²ng laptop gaming hiá»‡u nÄƒng cao cá»§a Dell vá»›i thiáº¿t káº¿ Ä‘áº­m cháº¥t tÆ°Æ¡ng lai, táº£n nhiá»‡t láº¥y cáº£m há»©ng tá»« dÃ²ng Alienware cao cáº¥p.', 26990000, 29990000, NULL, 8, 'dell-g16.jpg', 4.4, 196, 20, '{"CPU": "Intel Core i7-13650HX", "RAM": "16GB DDR5", "SSD": "512GB NVMe SSD", "VGA": "RTX 4060 8GB GDDR6", "MÃ n hÃ¬nh": "16 inch QHD+ IPS 165Hz"}', 0, 0, 0, 0),

-- Laptop VÄƒn PhÃ²ng
(7, 2, 4, 'Dell Inspiron 5430', 'dell-inspiron-5430', 'Laptop vÄƒn phÃ²ng má»ng nháº¹, sang trá»ng', 'Thiáº¿t káº¿ vá» nhÃ´m cao cáº¥p, trá»ng lÆ°á»£ng cá»±c nháº¹, phÃ¹ há»£p cho há»c táº­p vÃ  lÃ m viá»‡c di Ä‘á»™ng.', 18990000, 20990000, NULL, 10, 'dell-inspiron.jpg', 4.5, 210, 30, '{"CPU": "Intel Core i5-1340P", "RAM": "16GB LPDDR5", "SSD": "512GB NVMe SSD", "VGA": "Intel Iris Xe Graphics", "MÃ n hÃ¬nh": "14 inch WUXGA IPS"}', 0, 1, 0, 0),
(8, 2, 1, 'ASUS Vivobook 15', 'asus-vivobook-15', 'Giao diá»‡n thá»i trang, mÃ n hÃ¬nh 15.6 inch viá»n má»ng', 'PhÃ¹ há»£p há»c sinh, sinh viÃªn vá»›i bÃ n phÃ­m sá»‘ tiá»‡n lá»£i vÃ  thiáº¿t káº¿ má»ng nháº¹ cÃ¡ tÃ­nh.', 16990000, 18990000, NULL, 10, 'asus-vivobook.jpg', 4.4, 178, 45, '{"CPU": "Intel Core i5-1235U", "RAM": "8GB DDR4", "SSD": "512GB PCIe SSD", "VGA": "Intel UHD Graphics", "MÃ n hÃ¬nh": "15.6 inch FHD IPS"}', 0, 0, 1, 0),
(9, 2, 5, 'HP Pavilion 15', 'hp-pavilion-15', 'Tráº£i nghiá»‡m mÆ°á»£t mÃ , Ã¢m thanh sá»‘ng Ä‘á»™ng', 'HP Pavilion 15 thanh lá»‹ch vá»›i cháº¥t liá»‡u kim loáº¡i cao cáº¥p vÃ  loa kÃ©p B&O cháº¥t lÆ°á»£ng cao.', 17990000, 19990000, NULL, 10, 'hp-pavilion.jpg', 4.3, 145, 25, '{"CPU": "Intel Core i5-1235U", "RAM": "8GB DDR4", "SSD": "512GB PCIe NVMe", "VGA": "Intel Iris Xe Graphics", "MÃ n hÃ¬nh": "15.6 inch FHD IPS"}', 0, 0, 0, 0),
(10, 2, 6, 'Lenovo IdeaPad Slim 3', 'lenovo-ideapad-slim-3', 'Má»ng nháº¹ tá»‘i Æ°u, pin trÃ¢u bá»n bá»‰', 'Trá»ng lÆ°á»£ng chá»‰ 1.55kg, há»— trá»£ cÃ´ng nghá»‡ sáº¡c nhanh vÃ  webcam che báº£o máº­t thÃ´ng minh.', 13990000, 15990000, NULL, 12, 'lenovo-ideapad.jpg', 4.5, 122, 50, '{"CPU": "Intel Core i5-12450H", "RAM": "8GB LPDDR5", "SSD": "512GB NVMe SSD", "VGA": "Intel UHD Graphics", "MÃ n hÃ¬nh": "15.6 inch FHD IPS"}', 0, 0, 0, 0),
(11, 2, 15, 'Acer Aspire 5', 'acer-aspire-5', 'GiÃ¡ ráº», hiá»‡u nÄƒng vÄƒn phÃ²ng tá»‘t', 'DÃ²ng laptop phá»• thÃ´ng phá»¥c vá»¥ hoÃ n háº£o cÃ¡c nhu cáº§u vÄƒn phÃ²ng cÆ¡ báº£n nhÆ° Excel, Word, há»c online.', 14490000, 15990000, NULL, 9, 'acer-aspire.jpg', 4.2, 98, 40, '{"CPU": "Intel Core i5-1235U", "RAM": "8GB DDR4", "SSD": "512GB PCIe SSD", "VGA": "Intel Iris Xe", "MÃ n hÃ¬nh": "14 inch FHD IPS"}', 0, 0, 0, 0),
(12, 2, 16, 'LG Gram 16', 'lg-gram-16', 'SiÃªu nháº¹ Ä‘á»‰nh cao, mÃ n hÃ¬nh 2K sáº¯c nÃ©t', 'Trá»ng lÆ°á»£ng chÆ°a Ä‘áº§y 1.2kg cho kÃ­ch thÆ°á»›c mÃ n hÃ¬nh 16 inch Ä‘á»™ phÃ¢n giáº£i 2K. Pin sá»­ dá»¥ng lÃªn tá»›i 20.5 tiáº¿ng.', 29990000, 33990000, NULL, 11, 'lg-gram.jpg', 4.8, 87, 15, '{"CPU": "Intel Core i7-1360P", "RAM": "16GB LPDDR5", "SSD": "512GB PCIe 4.0", "VGA": "Intel Iris Xe", "MÃ n hÃ¬nh": "16 inch WQXGA IPS"}', 0, 1, 0, 0),

-- Flash Sale (ÄÆ°á»£c thiáº¿t láº­p sáºµn sale_price)
(13, 1, 1, 'ASUS ROG Ally X', 'asus-rog-ally-x', 'MÃ¡y chÆ¡i game cáº§m tay máº¡nh máº½ nháº¥t hiá»‡n nay', 'ROG Ally X sá»Ÿ há»¯u CPU AMD Ryzen Z1 Extreme, RAM LPDDR5X lÃªn tá»›i 24GB cÃ¹ng dung lÆ°á»£ng pin 80Wh gáº¥p Ä‘Ã´i tháº¿ há»‡ trÆ°á»›c.', 22990000, 22990000, 18990000, 17, 'rog-ally-x.jpg', 4.9, 320, 8, '{"CPU": "AMD Ryzen Z1 Extreme", "RAM": "24GB LPDDR5X", "SSD": "1TB M.2 PCIe 4.0", "VGA": "AMD Radeon RDNA 3", "MÃ n hÃ¬nh": "7 inch FHD 120Hz"}', 1, 0, 0, 0),
(14, 3, 12, 'PC Gaming TechPilot Extreme V1', 'pc-gaming-techpilot-extreme-v1', 'PC Gaming cáº¥u hÃ¬nh cao chiáº¿n mÆ°á»£t má»i game AAA', 'Cáº¥u hÃ¬nh tá»‘i Æ°u hiá»‡u nÄƒng vá»›i Intel Core i5 vÃ  RTX 4060, giÃºp báº¡n chiáº¿n mÆ°á»£t mÃ  má»i tá»±a game Esport vÃ  Ä‘á»“ há»a.', 28990000, 31990000, 28990000, 9, 'pc-build.jpg', 4.9, 786, 14, '{"CPU": "Intel Core i5-13400F", "Mainboard": "B760M", "RAM": "16GB DDR5", "SSD": "512GB NVMe", "VGA": "RTX 4060 8GB"}', 1, 1, 0, 0),
(15, 7, 13, 'Logitech G Pro X Wireless', 'logitech-g-pro-x-wireless', 'Tai nghe gaming khÃ´ng dÃ¢y chuáº©n thi Ä‘áº¥u', 'Sá»­ dá»¥ng cÃ´ng nghá»‡ khÃ´ng dÃ¢y LIGHTSPEED, mÃ ng loa PRO-G 50mm vÃ  cÃ´ng nghá»‡ lá»c Ã¢m Blue VO!CE chuyÃªn nghiá»‡p.', 4090000, 4090000, 3290000, 20, 'logitech-g-pro-x-wireless.jpg', 4.8, 375, 10, '{"Káº¿t ná»‘i": "KhÃ´ng dÃ¢y Lightspeed 2.4GHz", "Driver": "PRO-G 50mm", "Micro": "Blue VO!CE 6mm", "Thá»i lÆ°á»£ng pin": "LÃªn tá»›i 20 giá»"}', 1, 0, 0, 0),
(16, 5, 11, 'Samsung Odyssey G5 27"', 'samsung-odyssey-g5-27', 'MÃ n hÃ¬nh cong gaming QHD 165Hz', 'Äá»™ cong 1000R tá»‘i Æ°u táº§m nhÃ¬n, thá»i gian pháº£n há»“i 1ms vÃ  cÃ´ng nghá»‡ AMD FreeSync Premium chá»‘ng xÃ© hÃ¬nh.', 5990000, 5990000, 5490000, 8, 'samsung-odyssey-g5.jpg', 4.6, 164, 15, '{"KÃ­ch thÆ°á»›c": "27 inch", "Äá»™ cong": "1000R", "Äá»™ phÃ¢n giáº£i": "2560 x 1440 (2K)", "Táº§n sá»‘ quÃ©t": "165Hz", "Táº¥m ná»n": "VA"}', 1, 0, 0, 0),
(17, 4, 14, 'RTX 4070 SUPER 12GB', 'rtx-4070-super-12gb', 'Card Ä‘á»“ há»a Ä‘á»‰nh cao kiáº¿n trÃºc Ada Lovelace', 'Há»— trá»£ DLSS 3, Ray Tracing thá»i gian thá»±c siÃªu mÆ°á»£t cho tráº£i nghiá»‡m chiáº¿n game 2K Ä‘á»‰nh cao.', 21990000, 21990000, 18990000, 13, 'rtx-4070-super.jpg', 4.9, 149, 10, '{"NhÃ¢n CUDA": "7168", "VRAM": "12GB GDDR6X", "Bus": "192-bit", "Nguá»“n yÃªu cáº§u": "650W trá»Ÿ lÃªn"}', 1, 0, 1, 1),
(18, 2, 4, 'Dell XPS 13 Plus', 'dell-xps-13-plus', 'Äá»‹nh nghÄ©a láº¡i laptop doanh nhÃ¢n cao cáº¥p', 'Thiáº¿t káº¿ tá»‘i giáº£n liá»n máº¡ch, bÃ n phÃ­m trÃ n viá»n cÃ¹ng thanh Touch Bar cáº£m á»©ng Ä‘á»™c Ä‘Ã¡o.', 30990000, 30990000, 27990000, 10, 'dell-xps-13.jpg', 4.6, 265, 5, '{"CPU": "Intel Core i7-1360P", "RAM": "16GB LPDDR5", "SSD": "512GB NVMe PCIe", "VGA": "Intel Iris Xe", "MÃ n hÃ¬nh": "13.4 inch FHD+ IPS"}', 1, 0, 0, 0),

-- PC Build Sáºµn
(19, 3, 9, 'PC TechPilot Basic Gaming', 'pc-techpilot-basic-gaming', 'PC Gaming giÃ¡ ráº», chiáº¿n mÆ°á»£t LiÃªn Minh, Fifa', 'Cáº¥u hÃ¬nh tá»‘i Æ°u ngÃ¢n sÃ¡ch cho há»c sinh, sinh viÃªn chÆ¡i cÃ¡c tá»±a game Esport phá»• thÃ´ng.', 10990000, 12990000, NULL, 15, 'pc-basic.jpg', 4.5, 96, 25, '{"CPU": "Intel Core i3-12100F", "Mainboard": "H610M", "RAM": "8GB DDR4 3200MHz", "SSD": "256GB NVMe", "VGA": "GTX 1650 4GB", "Nguá»“n": "500W"}', 0, 0, 0, 0),
(20, 3, 10, 'PC TechPilot Advanced Gaming', 'pc-techpilot-advanced-gaming', 'PC chiáº¿n mÆ°á»£t game AAA vÃ  lÃ m Ä‘á»“ há»a nháº¹', 'Cáº¥u hÃ¬nh quá»‘c dÃ¢n chiáº¿n tá»‘t cÃ¡c tá»±a game náº·ng nhÆ° PUBG, GTA V, Valorant á»Ÿ má»©c thiáº¿t láº­p cao.', 17990000, 19990000, NULL, 10, 'pc-advanced.jpg', 4.7, 148, 15, '{"CPU": "AMD Ryzen 5 5600X", "Mainboard": "B550M", "RAM": "16GB DDR4 3200MHz", "SSD": "512GB NVMe", "VGA": "RTX 3060 12GB", "Nguá»“n": "600W"}', 0, 1, 0, 0),
(21, 3, 9, 'PC TechPilot High-End Gaming', 'pc-techpilot-high-end-gaming', 'PC Gaming cao cáº¥p, chiáº¿n game Ray Tracing', 'Cáº¥u hÃ¬nh siÃªu khá»§ng chuyÃªn trá»‹ game 4K, stream game mÆ°á»£t mÃ  vÃ  lÃ m render Ä‘á»“ há»a 3D.', 35990000, 39990000, NULL, 10, 'pc-high-end.jpg', 4.9, 74, 5, '{"CPU": "Intel Core i7-14700F", "Mainboard": "B760M", "RAM": "32GB DDR5 5600MHz", "SSD": "1TB NVMe Gen4", "VGA": "RTX 4070 Ti SUPER 16GB", "Nguá»“n": "750W 80 Plus Bronze"}', 0, 1, 0, 1),
(22, 3, 9, 'PC Workstation Äá»“ Há»a', 'pc-workstation-do-hoa', 'Tá»‘i Æ°u cho thiáº¿t káº¿ 3D, dá»±ng phim Premiere', 'DÃ²ng mÃ¡y tráº¡m chuyÃªn nghiá»‡p Ä‘Ã¡p á»©ng hoÃ n háº£o cÃ¡c tÃ¡c vá»¥ render náº·ng, lÃ m phim 4K/8K vÃ  AI.', 28990000, 31990000, NULL, 9, 'pc-workstation.jpg', 4.8, 52, 8, '{"CPU": "Intel Core i9-13900K", "Mainboard": "Z790", "RAM": "32GB DDR5 6000MHz", "SSD": "1TB Gen4 NVMe", "VGA": "RTX 4060 Ti 16GB", "Nguá»“n": "850W 80 Plus Gold"}', 0, 0, 1, 0),
(23, 3, 10, 'PC Gaming AMD All-Red', 'pc-gaming-amd-all-red', 'Combo CPU Ryzen + Card Radeon tá»‘i Æ°u hiá»‡u nÄƒng', 'Sá»± káº¿t há»£p hoÃ n háº£o tá»« AMD Ä‘em láº¡i tÃ­nh nÄƒng Smart Access Memory tÄƒng hiá»‡u suáº¥t chÆ¡i game vÆ°á»£t trá»™i.', 22490000, 24990000, NULL, 10, 'pc-amd.jpg', 4.6, 68, 10, '{"CPU": "AMD Ryzen 5 7600", "Mainboard": "B650M", "RAM": "16GB DDR5 5200MHz", "SSD": "512GB PCIe 4.0", "VGA": "RX 7700 XT 12GB", "Nguá»“n": "700W 80 Plus"}', 0, 0, 0, 0),
(24, 3, 9, 'PC Office GiÃ¡ Ráº»', 'pc-office-gia-re', 'PC vÄƒn phÃ²ng Ä‘á»“ng bá»™, há»c táº­p mÆ°á»£t mÃ ', 'KÃ­ch thÆ°á»›c nhá» gá»n tiáº¿t kiá»‡m khÃ´ng gian, Ä‘á»™ bá»n cá»±c cao phÃ¹ há»£p láº¯p Ä‘áº·t cho doanh nghiá»‡p.', 7490000, 8490000, NULL, 12, 'pc-office.jpg', 4.4, 114, 35, '{"CPU": "Intel Core i5-12400", "Mainboard": "H610M", "RAM": "8GB DDR4", "SSD": "256GB SSD", "VGA": "Intel UHD Graphics 730", "Nguá»“n": "400W"}', 0, 0, 0, 0),

-- Linh Kiá»‡n PC
(25, 4, 9, 'CPU Intel Core i5-13400F', 'cpu-intel-core-i5-13400f', 'Vi xá»­ lÃ½ táº§m trung quá»‘c dÃ¢n tháº¿ há»‡ 13', '10 nhÃ¢n 16 luá»“ng, xung nhá»‹p lÃªn tá»›i 4.6GHz, lá»±a chá»n tuyá»‡t vá»i cho PC gaming táº§m trung.', 4590000, 5290000, NULL, 13, 'cpu-i5.jpg', 4.8, 230, 40, '{"NhÃ¢n": "10 (6 P-core + 4 E-core)", "Luá»“ng": "16", "Xung nhá»‹p": "2.5GHz up to 4.6GHz", "Socket": "LGA1700"}', 0, 0, 0, 0),
(26, 4, 1, 'Mainboard ASUS TUF GAMING B760M-PLUS', 'main-board-asus-tuf-b760m', 'Bo máº¡ch chá»§ socket LGA1700 siÃªu bá»n bá»‰', 'Há»— trá»£ RAM DDR5, táº£n nhiá»‡t VRM háº§m há»‘, khe cáº¯m M.2 PCIe 4.0 tá»‘c Ä‘á»™ cao.', 3990000, 4490000, NULL, 11, 'mainboard-tuf.jpg', 4.7, 142, 28, '{"Chipset": "Intel B760", "Socket": "LGA1700", "RAM há»— trá»£": "4x DDR5 up to 192GB", "KÃ­ch thÆ°á»›c": "Micro-ATX"}', 0, 0, 0, 0),
(27, 4, 8, 'RAM Corsair Vengeance RGB 16GB DDR5', 'ram-corsair-vengeance-rgb-16gb', 'RAM DDR5 cao cáº¥p vá»›i dáº£i Ä‘Ã¨n LED RGB lá»™ng láº«y', 'Tá»‘c Ä‘á»™ bus 5600MHz cá»±c nhanh, táº£n nhiá»‡t nhÃ´m sang trá»ng, tÆ°Æ¡ng thÃ­ch tá»‘t Intel XMP 3.0.', 1890000, 2290000, NULL, 17, 'ram-corsair.jpg', 4.8, 310, 60, '{"Loáº¡i RAM": "DDR5", "Dung lÆ°á»£ng": "16GB (1x16GB)", "Tá»‘c Ä‘á»™": "5600 MHz", "Äá»™ trá»…": "CL40"}', 0, 0, 0, 0),
(28, 4, 11, 'SSD Samsung 990 PRO 1TB NVMe', 'ssd-samsung-990-pro-1tb', 'SSD PCIe Gen4 nhanh nháº¥t tháº¿ giá»›i', 'Tá»‘c Ä‘á»™ Ä‘á»c lÃªn tá»›i 7450 MB/s, ghi 6900 MB/s giÃºp khá»Ÿi Ä‘á»™ng Windows vÃ  táº£i game tá»©c thÃ¬.', 2790000, 3190000, NULL, 12, 'ssd-samsung.jpg', 4.9, 412, 50, '{"Chuáº©n": "M.2 NVMe PCIe Gen4 x4", "Dung lÆ°á»£ng": "1TB", "Tá»‘c Ä‘á»™ Äá»c": "7450 MB/s", "Tá»‘c Ä‘á»™ Ghi": "6900 MB/s"}', 0, 0, 0, 1),

-- MÃ n HÃ¬nh
(29, 5, 1, 'MÃ n hÃ¬nh ASUS TUF Gaming VG279Q1A', 'man-hinh-asus-tuf-vg279q1a', '27" IPS FHD 165Hz chuyÃªn game báº¯n sÃºng', 'Táº§n sá»‘ quÃ©t cao 165Hz, thá»i gian pháº£n há»“i 1ms MPRT cÃ¹ng gÃ³c nhÃ¬n rá»™ng 178 Ä‘á»™.', 3290000, 3990000, NULL, 17, 'monitor-asus.jpg', 4.7, 185, 20, '{"KÃ­ch thÆ°á»›c": "27 inch", "Äá»™ phÃ¢n giáº£i": "1920x1080 (FHD)", "Táº§n sá»‘ quÃ©t": "165Hz", "Táº¥m ná»n": "IPS"}', 0, 0, 1, 0),
(30, 5, 16, 'MÃ n hÃ¬nh LG UltraGear 24GQ50F-B', 'man-hinh-lg-ultragear-24gq50f', '24" VA 165Hz giÃ¡ sinh viÃªn chiáº¿n game ngon', 'Táº§n sá»‘ quÃ©t 165Hz, há»— trá»£ AMD FreeSync Premium chiáº¿n game Esport mÆ°á»£t mÃ .', 2490000, 2990000, NULL, 16, 'monitor-lg.jpg', 4.5, 230, 30, '{"KÃ­ch thÆ°á»›c": "23.8 inch", "Äá»™ phÃ¢n giáº£i": "1920x1080 (FHD)", "Táº§n sá»‘ quÃ©t": "165Hz", "Táº¥m ná»n": "VA"}', 0, 0, 0, 0),
(31, 5, 11, 'MÃ n hÃ¬nh Samsung Odyssey G6 27"', 'man-hinh-samsung-odyssey-g6', 'MÃ n hÃ¬nh cong gaming 2K 240Hz thÃ´ng minh', 'Táº§n sá»‘ quÃ©t siÃªu khá»§ng 240Hz, táº¥m ná»n cong QLED, tÃ­ch há»£p kho á»©ng dá»¥ng Smart TV tiá»‡n Ã­ch.', 11990000, 13990000, NULL, 14, 'monitor-samsung-g6.jpg', 4.8, 322, 12, '{"KÃ­ch thÆ°á»›c": "27 inch", "Äá»™ cong": "1000R", "Äá»™ phÃ¢n giáº£i": "2560x1440", "Táº§n sá»‘ quÃ©t": "240Hz", "Táº¥m ná»n": "VA"}', 0, 1, 0, 0),

-- Apple Zone
(32, 2, 1, 'Laptop ASUS Vivobook S 14', 'laptop-asus-vivobook-s-14', 'Laptop vÄƒn phÃ²ng cao cáº¥p, má»ng nháº¹ tinh táº¿', 'Thiáº¿t káº¿ má»ng nháº¹ sang trá»ng, thá»i lÆ°á»£ng pin áº¥n tÆ°á»£ng vÃ  mÃ n hÃ¬nh OLED sáº¯c nÃ©t Ä‘Ã¡p á»©ng tá»‘i Ä‘a nhu cáº§u lÃ m viá»‡c cÃ´ng sá»Ÿ.', 24990000, 26990000, NULL, 7, 'laptop-asus.jpg', 4.8, 342, 18, '{"CPU": "Intel Core i5-13500H", "RAM": "16GB DDR5", "SSD": "512GB NVMe", "MÃ n hÃ¬nh": "14 inch OLED 2.8K"}', 0, 1, 0, 0),
(33, 1, 6, 'Laptop Gaming Lenovo Legion Pro 5', 'laptop-gaming-lenovo-legion-pro-5', 'Laptop gaming hiá»‡u nÄƒng Ä‘á»‰nh cao, táº£n nhiá»‡t tá»‘i Æ°u', 'Lenovo Legion Pro 5 trang bá»‹ Core i7 tháº¿ há»‡ má»›i cÃ¹ng card Ä‘á»“ há»a RTX 4060, Ä‘Ã¡p á»©ng xuáº¥t sáº¯c nhu cáº§u chÆ¡i game náº·ng vÃ  Ä‘á»“ há»a chuyÃªn nghiá»‡p.', 48990000, 52990000, NULL, 7, 'laptop-gaming.jpg', 4.9, 120, 10, '{"CPU": "Intel Core i7-14700HX", "RAM": "32GB DDR5", "SSD": "1TB NVMe", "VGA": "RTX 4060 8GB", "MÃ n hÃ¬nh": "16 inch QHD+ 165Hz"}', 0, 0, 1, 1),
(34, 3, 1, 'PC All-in-One ASUS A3402', 'pc-all-in-one-asus-a3402', 'MÃ¡y tÃ­nh All-in-One má»ng nháº¹ gá»n gÃ ng cho vÄƒn phÃ²ng', 'ASUS A3402 tÃ­ch há»£p toÃ n bá»™ linh kiá»‡n vÃ o sau mÃ n hÃ¬nh 24 inch sáº¯c nÃ©t, Ä‘i kÃ¨m bÃ n phÃ­m vÃ  chuá»™t khÃ´ng dÃ¢y Ä‘á»“ng bá»™.', 32990000, 35990000, NULL, 8, 'pc-build.jpg', 4.8, 62, 7, '{"CPU": "Intel Core i5-1235U", "RAM": "16GB DDR4", "SSD": "512GB NVMe", "MÃ n hÃ¬nh": "23.8 inch FHD IPS"}', 0, 0, 0, 0),

-- Gaming Gear
(35, 7, 13, 'BÃ n phÃ­m cÆ¡ Logitech G213 Prodigy', 'ban-phim-logitech-g213', 'BÃ n phÃ­m giáº£ cÆ¡ chá»‘ng trÃ n nÆ°á»›c, Ä‘Ã¨n RGB', 'PhÃ­m nháº¥n nháº¡y bÃ©n gáº¥p 4 láº§n phÃ­m thÆ°á»ng, chá»— nghá»‰ tay thoáº£i mÃ¡i khi gÃµ vÄƒn báº£n lÃ¢u.', 890000, 1190000, NULL, 25, 'keyboard-logitech.jpg', 4.4, 215, 30, '{"Kiá»ƒu káº¿t ná»‘i": "CÃ³ dÃ¢y USB", "Loáº¡i phÃ­m": "Giáº£ cÆ¡ (Membrane)", "ÄÃ¨n ná»n": "RGB 5 vÃ¹ng", "Chá»‘ng nÆ°á»›c": "CÃ³"}', 0, 0, 0, 0),
(36, 7, 7, 'Chuá»™t Razer DeathAdder V3 Pro', 'chuot-razer-deathadder-v3-pro', 'Chuá»™t gaming siÃªu nháº¹ 63g chuáº©n eSports', 'Thiáº¿t káº¿ cÃ´ng thÃ¡i há»c Ä‘á»‰nh cao, máº¯t Ä‘á»c Focus Pro 30K Optical Sensor chÃ­nh xÃ¡c nháº¥t tháº¿ giá»›i.', 3190000, 3690000, NULL, 13, 'mouse-razer.jpg', 4.9, 155, 20, '{"Káº¿t ná»‘i": "KhÃ´ng dÃ¢y Razer HyperSpeed 2.4GHz", "Máº¯t Ä‘á»c": "Focus Pro 30K", "Trá»ng lÆ°á»£ng": "63g", "Thá»i lÆ°á»£ng pin": "LÃªn tá»›i 90 giá»"}', 0, 0, 0, 1),
(37, 7, 8, 'BÃ n phÃ­m cÆ¡ Corsair K70 PRO RGB', 'ban-phim-corsair-k70-pro-rgb', 'BÃ n phÃ­m cÆ¡ hiá»‡n Ä‘áº¡i khung nhÃ´m cao cáº¥p', 'Trang bá»‹ switch Cherry MX cÆ¡ há»c, cÃ´ng nghá»‡ xá»­ lÃ½ siÃªu nhanh AXON Ä‘á»™c quyá»n tá»« Corsair.', 3890000, 4290000, NULL, 9, 'keyboard-corsair.jpg', 4.8, 142, 15, '{"Loáº¡i Switch": "Cherry MX Red / Blue / Brown", "Khung": "NhÃ´m Anodized cao cáº¥p", "Táº§n sá»‘ gá»­i tÃ­n hiá»‡u": "8000Hz (AXON)", "Káº¿t ná»‘i": "CÃ³ dÃ¢y USB-C thÃ¡o rá»i"}', 0, 1, 0, 0);

-- 4. Náº¡p bá»™ sÆ°u táº­p hÃ¬nh áº£nh sáº£n pháº©m (product_images)
INSERT INTO product_images (product_id, image_url) VALUES 
(1, 'rog-zephyrus-1.jpg'), (1, 'rog-zephyrus-2.jpg'), (1, 'rog-zephyrus-3.jpg'), (1, 'rog-zephyrus-4.jpg'),
(13, 'rog-ally-x-1.jpg'), (13, 'rog-ally-x-2.jpg'),
(14, 'pc-build-1.jpg'), (14, 'pc-build-2.jpg');

-- 5. Náº¡p quáº£n lÃ½ banner quáº£ng cÃ¡o (banners)
INSERT INTO banners (title, image, link, type, position) VALUES 
('ROG Zephyrus G16 - Sá»©c máº¡nh vÆ°á»£t trá»™i', 'hero-rog-zephyrus.jpg', 'product/detail/asus-rog-zephyrus-g16', 'hero', 1),
('Build PC theo yÃªu cáº§u - Tá»‘i Æ°u cáº¥u hÃ¬nh', '#', '#', 'hero_sidebar', 1),
('Tráº£ gÃ³p 0% - Duyá»‡t nhanh 3 phÃºt', '#', '#', 'hero_sidebar', 2),
('Thu cÅ© Ä‘á»•i má»›i - Trá»£ giÃ¡ lÃªn tá»›i 6 triá»‡u', '#', '#', 'hero_sidebar', 3),
('RTX 50 Series - Sáº¯p ra máº¯t', 'banner-rtx-50.jpg', '#', 'mid_banner', 1),
('Tráº£ gÃ³p 0% lÃ£i suáº¥t - Thá»§ tá»¥c nhanh gá»n', 'banner-tra-gop.jpg', '#', 'long_banner', 1);

-- 6. Náº¡p bÃ i viáº¿t tin tá»©c cÃ´ng nghá»‡ (posts)
INSERT INTO posts (title, slug, summary, content, image, created_at) VALUES 
('ÄÃ¡nh giÃ¡ chi tiáº¿t NVIDIA RTX 50 Series: BÆ°á»›c nháº£y vá»t hiá»‡u nÄƒng AI', 'nvidia-rtx-50-series-danh-gia', 'Nhá»¯ng thÃ´ng tin má»›i nháº¥t vá» hiá»‡u nÄƒng, giÃ¡ bÃ¡n vÃ  ngÃ y ra máº¯t card Ä‘á»“ há»a tháº¿ há»‡ tiáº¿p theo cá»§a NVIDIA.', 'Kiáº¿n trÃºc má»›i mang láº¡i bÄƒng thÃ´ng siÃªu cao, tÃ­ch há»£p Tensor Core tháº¿ há»‡ thá»© 5 giÃºp tá»‘i Æ°u hÃ³a thuáº­t toÃ¡n AI...', 'news-rtx-50.jpg', NOW() - INTERVAL 1 DAY),
('Intel Core Ultra 9: CPU tháº¿ há»‡ má»›i dÃ nh cho cÃ¡c dÃ²ng laptop má»ng nháº¹ 2026', 'intel-core-ultra-9-laptop-thin-light', 'DÃ²ng chip sá»Ÿ há»¯u NPU chuyÃªn biá»‡t phá»¥c vá»¥ cÃ¡c tÃ¡c vá»¥ trÃ­ tuá»‡ nhÃ¢n táº¡o trá»±c tiáº¿p trÃªn thiáº¿t bá»‹.', 'DÃ²ng vi xá»­ lÃ½ má»›i tiáº¿t kiá»‡m nÄƒng lÆ°á»£ng hÆ¡n, card Ä‘á»“ há»a Arc tÃ­ch há»£p máº¡nh máº½ sáºµn sÃ ng thay tháº¿ card rá»i phÃ¢n khÃºc phá»• thÃ´ng...', 'news-intel-ultra.jpg', NOW() - INTERVAL 3 DAY),
('HÆ°á»›ng dáº«n tá»± build PC gaming 20 triá»‡u chiáº¿n tá»‘t má»i game esport nÄƒm nay', 'huong-dan-build-pc-20-trieu', 'Lá»±a chá»n linh kiá»‡n chuáº©n nháº¥t, tá»‘i Æ°u ngÃ¢n sÃ¡ch tá»‘t nháº¥t trÃ¡nh ngháº½n cá»• chai.', 'Táº­p trung chi phÃ­ vÃ o CPU Core i5 / Ryzen 5 vÃ  card Ä‘á»“ há»a GTX 1660 Super hoáº·c RTX 3060 cÅ© giÃºp báº¡n chÆ¡i game tá»‘i Æ°u nháº¥t...', 'news-build-pc.jpg', NOW() - INTERVAL 5 DAY),
('Top 5 chuá»™t gaming khÃ´ng dÃ¢y siÃªu nháº¹ Ä‘Ã¡ng mua nháº¥t thá»i Ä‘iá»ƒm hiá»‡n táº¡i', 'top-5-chuot-gaming-khong-day-sieu-nhe', 'Äiá»ƒm danh cÃ¡c gÆ°Æ¡ng máº·t vÃ ng tá»« Razer, Logitech, Corsair Ä‘Æ°á»£c game thá»§ chuyÃªn nghiá»‡p tin dÃ¹ng.', 'Razer DeathAdder V3 Pro, Logitech G Pro X Superlight 2 Ä‘ang dáº«n Ä‘áº§u cuá»™c Ä‘ua chuá»™t siÃªu nháº¹ dÆ°á»›i 60 gram...', 'news-mouse-gaming.jpg', NOW() - INTERVAL 7 DAY);

-- 7. Náº¡p bÃ i Ä‘Ã¡nh giÃ¡ máº«u (reviews)
INSERT INTO reviews (product_id, user_id, reviewer_name, rating, comment) VALUES 
(1, NULL, 'Nguyá»…n HoÃ ng Nam', 5.0, 'Sáº£n pháº©m chÃ­nh hÃ£ng, mÃ n hÃ¬nh OLED siÃªu Ä‘áº¹p, chÆ¡i game mÆ°á»£t mÃ  cá»±c ká»³ thÃ­ch! Giao hÃ ng nhanh.'),
(1, NULL, 'Tráº§n Minh Äá»©c', 4.5, 'Thiáº¿t káº¿ má»ng nháº¹ tiá»‡n mang Ä‘i lÃ m, hiá»‡u nÄƒng i9 siÃªu máº¡nh nhÆ°ng mÃ¡y hÆ¡i áº¥m lÃªn khi chÆ¡i game náº·ng lÃ¢u.'),
(13, NULL, 'LÃª Minh QuÃ¢n', 5.0, 'MÃ n hÃ¬nh 120Hz mÆ°á»£t, phÃ­m báº¥m nháº¡y, Ally X dÃ¹ng sÆ°á»›ng hÆ¡n báº£n cÅ© nhiá»u, pin trÃ¢u hÆ¡n háº³n.'),
(14, NULL, 'Phan Má»¹ Linh', 5.0, 'MÃ¡y rÃ¡p ráº¥t cháº¯c cháº¯n, cháº¡y Ãªm vÃ  chiáº¿n game AAA cá»±c mÆ°á»£t.'),
(15, NULL, 'HoÃ ng Quá»‘c Báº£o', 5.0, 'Ã‚m thanh vÃ²m nghe tiáº¿ng chÃ¢n Ä‘á»‹ch trong game ráº¥t rÃµ, mic lá»c Ã¢m tá»‘t.'),
(16, NULL, 'VÅ© PhÆ°Æ¡ng Anh', 4.0, 'MÃ n hÃ¬nh cong Ä‘áº¹p, táº§n sá»‘ quÃ©t 165Hz chÆ¡i game mÆ°á»£t, tuy nhiÃªn chÃ¢n Ä‘áº¿ hÆ¡i to chiáº¿m diá»‡n tÃ­ch bÃ n.');

-- 8. Náº¡p mÃ£ giáº£m giÃ¡ (coupons)
INSERT INTO coupons (code, discount_value, type, max_discount, min_order_value, start_date, end_date) VALUES 
('TECHPILOT100', 100000, 'fixed', 100000, 2000000, NOW() - INTERVAL 1 DAY, NOW() + INTERVAL 30 DAY),
('GIAM5PHANTRAM', 5, 'percent', 500000, 5000000, NOW() - INTERVAL 1 DAY, NOW() + INTERVAL 30 DAY);

-- 9. Náº¡p tÃ i khoáº£n admin vÃ  customer máº«u
-- Admin: email=ntrungz0704@gmail.com / password=Admin@123
-- Customer: email=customer@gmail.com / password=customer123
INSERT INTO users (full_name, email, phone, password, role, status) VALUES
('Nguyá»…n Pháº¡m ThÃ nh Trung', 'ntrungz0704@gmail.com', '0987654321', '$2y$12$MfxSPGH6pjMqRLNF/3H.FeZP6.ppxtRtqz/StiY0d0BaTUxX3xdB2', 'admin', 'active'),
('KhÃ¡ch hÃ ng Demo', 'customer@gmail.com', '0123456789', '$2y$12$CYdt4fumZuJ8nc5menHuN.0mJ2zGA.Y5nTTjCnkfLWXfS6if/6WOS', 'customer', 'active');

-- 10. Náº¡p má»™t chiáº¿n dá»‹ch Flash Sale
INSERT INTO flash_sales (id, title, slug, start_time, end_time, status) VALUES
(1, 'Flash Sale CÃ´ng Nghá»‡', 'flash-sale-cong-nghe', NOW() - INTERVAL 1 HOUR, NOW() + INTERVAL 2 HOUR, 'active');

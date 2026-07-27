SET FOREIGN_KEY_CHECKS=0;

-- ============================================================================
-- TechPilot Full Seed Data (Restored Catalog & Parity Dataset)
-- Run after database/schema.sql on a clean database
-- ============================================================================

-- 1. Danh m?c s?n ph?m (categories)
INSERT INTO categories (id, parent_id, name, slug, icon) VALUES 
(1, NULL, 'Laptop Gaming', 'laptop-gaming', 'fa-solid fa-laptop-code'),
(2, NULL, 'Laptop Van Ph�ng', 'laptop-van-phong', 'fa-solid fa-laptop'),
(3, NULL, 'PC Build S?n', 'pc-build-san', 'fa-solid fa-desktop'),
(4, NULL, 'Linh Ki?n PC', 'pc-linh-kien', 'fa-solid fa-microchip'),
(5, NULL, 'M�n H�nh', 'man-hinh', 'fa-solid fa-tv'),
(6, NULL, 'M�y t�nh b?', 'may-tinh-bo', 'fa-solid fa-desktop'),
(7, NULL, 'Gaming Gear', 'gaming-gear', 'fa-solid fa-gamepad'),
(8, NULL, 'Thi?t B? Van Ph�ng', 'office-gear', 'fa-solid fa-print'),
(9, NULL, 'Thi?t B? M?ng', 'networking', 'fa-solid fa-wifi'),
(10, 4, 'CPU', 'cpu', 'fa-solid fa-microchip'),
(11, 4, 'Mainboard', 'mainboard', 'fa-solid fa-server'),
(12, 4, 'RAM', 'ram', 'fa-solid fa-memory'),
(13, 4, 'VGA', 'vga', 'fa-solid fa-vr-cardboard'),
(14, 4, '? C?ng SSD', 'ssd', 'fa-solid fa-hard-drive'),
(15, 4, '? C?ng HDD', 'hdd', 'fa-solid fa-database'),
(16, 4, 'Ngu?n (PSU)', 'psu', 'fa-solid fa-plug'),
(17, 4, 'Case', 'case', 'fa-solid fa-box'),
(18, 4, 'T?n nhi?t', 'tan-nhiet', 'fa-solid fa-fan'),
(19, NULL, 'Laptop', 'laptop', 'fa-solid fa-laptop'),
(20, NULL, 'PC', 'pc', 'fa-solid fa-desktop'),
(21, NULL, 'Cooling', 'cooling', 'fa-solid fa-fan'),
(22, NULL, 'Storage', 'storage', 'fa-solid fa-hard-drive'),
(23, NULL, 'B�n Ph�m', 'keyboard', 'fa-solid fa-keyboard'),
(24, NULL, 'Chu?t', 'mouse', 'fa-solid fa-computer-mouse'),
(25, NULL, 'Gh? Gaming', 'chair', 'fa-solid fa-chair'),
(26, NULL, 'Tai Nghe', 'headset', 'fa-solid fa-headphones'),
(27, NULL, 'Loa', 'speaker', 'fa-solid fa-volume-high'),
(28, NULL, 'Console', 'console', 'fa-solid fa-gamepad'),
(29, NULL, 'Ph? Ki?n', 'accessories', 'fa-solid fa-cubes')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- 2. Thuong hi?u (brands)
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
(16, 'LG', 'lg', 'lg.svg'),
(17, 'Kingston', 'kingston', 'kingston.png'),
(18, 'WD', 'wd', 'wd.png'),
(19, 'Lexar', 'lexar', 'lexar.png'),
(20, 'DeepCool', 'deepcool', 'deepcool.png'),
(21, 'Thermalright', 'thermalright', 'thermalright.png'),
(22, 'Montech', 'montech', 'montech.png'),
(23, 'NZXT', 'nzxt', 'nzxt.png'),
(24, 'Lian Li', 'lian-li', 'lian-li.png'),
(25, 'G.Skill', 'g-skill', 'g-skill.png')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- 3. S?n ph?m (products)
INSERT INTO products (id, category_id, brand_id, name, slug, short_desc, description, price, old_price, sale_price, discount_percent, image, rating, review_count, stock, specs, is_flash_sale, is_best_seller, is_new_arrival, is_ai_recommend, component_type, power_draw_w, recommended_psu_w) VALUES 
(1, 10, 9, 'Intel Core i3-12100F', 'intel-core-i3-12100f-1', 'Sản phẩm chính hãng', 'Mô tả chi tiết Intel Core i3-12100F', 2203000, NULL, NULL, 0, 'cpu.png', 4.9, 97, 150, '{"socket": "LGA1700", "memory_type": ["DDR4", "DDR5"], "base_power_w": 89, "integrated_graphics": false}', 0, 0, 0, 0, 'cpu', 89, NULL),
(2, 10, 9, 'Intel Core i3-13100F', 'intel-core-i3-13100f-2', 'Sản phẩm chính hãng', 'Mô tả chi tiết Intel Core i3-13100F', 3304000, NULL, NULL, 0, 'cpu.png', 4.5, 10, 149, '{"socket": "LGA1700", "memory_type": ["DDR4", "DDR5"], "base_power_w": 89, "integrated_graphics": false}', 0, 0, 0, 0, 'cpu', 89, NULL),
(3, 10, 9, 'Intel Core i3-14100F', 'intel-core-i3-14100f-3', 'Sản phẩm chính hãng', 'Mô tả chi tiết Intel Core i3-14100F', 3578000, NULL, NULL, 0, 'cpu.png', 4.9, 55, 141, '{"socket": "LGA1700", "memory_type": ["DDR4", "DDR5"], "base_power_w": 89, "integrated_graphics": false}', 0, 0, 0, 0, 'cpu', 89, NULL),
(4, 10, 9, 'Intel Core i5-12400F', 'intel-core-i5-12400f-4', 'Sản phẩm chính hãng', 'Mô tả chi tiết Intel Core i5-12400F', 3246000, NULL, NULL, 0, 'cpu.png', 4.6, 63, 114, '{"socket": "LGA1700", "memory_type": ["DDR4", "DDR5"], "base_power_w": 117, "integrated_graphics": false}', 0, 0, 0, 0, 'cpu', 117, NULL),
(5, 10, 9, 'Intel Core i5-12400', 'intel-core-i5-12400-5', 'Sản phẩm chính hãng', 'Mô tả chi tiết Intel Core i5-12400', 3794000, NULL, NULL, 0, 'cpu.png', 4.7, 29, 36, '{"socket": "LGA1700", "memory_type": ["DDR4", "DDR5"], "base_power_w": 117, "integrated_graphics": true}', 0, 0, 0, 0, 'cpu', 117, NULL),
(6, 10, 9, 'Intel Core i5-13400F', 'intel-core-i5-13400f-6', 'Sản phẩm chính hãng', 'Mô tả chi tiết Intel Core i5-13400F', 5467000, NULL, NULL, 0, 'cpu.png', 5.0, 74, 160, '{"socket": "LGA1700", "memory_type": ["DDR4", "DDR5"], "base_power_w": 148, "integrated_graphics": false}', 0, 0, 0, 0, 'cpu', 148, NULL),
(7, 10, 9, 'Intel Core i5-13400', 'intel-core-i5-13400-7', 'Sản phẩm chính hãng', 'Mô tả chi tiết Intel Core i5-13400', 5875000, NULL, NULL, 0, 'cpu.png', 4.6, 56, 24, '{"socket": "LGA1700", "memory_type": ["DDR4", "DDR5"], "base_power_w": 148, "integrated_graphics": true}', 0, 0, 0, 0, 'cpu', 148, NULL),
(8, 10, 9, 'Intel Core i5-14400F', 'intel-core-i5-14400f-8', 'Sản phẩm chính hãng', 'Mô tả chi tiết Intel Core i5-14400F', 5794000, NULL, NULL, 0, 'cpu.png', 4.7, 32, 21, '{"socket": "LGA1700", "memory_type": ["DDR4", "DDR5"], "base_power_w": 148, "integrated_graphics": false}', 0, 0, 0, 0, 'cpu', 148, NULL),
(9, 10, 9, 'Intel Core i5-14400', 'intel-core-i5-14400-9', 'Sản phẩm chính hãng', 'Mô tả chi tiết Intel Core i5-14400', 5956000, NULL, NULL, 0, 'cpu.png', 4.2, 14, 26, '{"socket": "LGA1700", "memory_type": ["DDR4", "DDR5"], "base_power_w": 148, "integrated_graphics": true}', 0, 0, 0, 0, 'cpu', 148, NULL),
(10, 10, 9, 'Intel Core i5-14600KF', 'intel-core-i5-14600kf-10', 'Sản phẩm chính hãng', 'Mô tả chi tiết Intel Core i5-14600KF', 7628000, NULL, NULL, 0, 'cpu.png', 4.9, 64, 129, '{"socket": "LGA1700", "memory_type": ["DDR4", "DDR5"], "base_power_w": 181, "integrated_graphics": false}', 0, 0, 0, 0, 'cpu', 181, NULL),
(11, 10, 9, 'Intel Core i5-14600K', 'intel-core-i5-14600k-11', 'Sản phẩm chính hãng', 'Mô tả chi tiết Intel Core i5-14600K', 7956000, NULL, NULL, 0, 'cpu.png', 4.6, 83, 50, '{"socket": "LGA1700", "memory_type": ["DDR4", "DDR5"], "base_power_w": 181, "integrated_graphics": true}', 0, 0, 0, 0, 'cpu', 181, NULL),
(12, 10, 9, 'Intel Core i7-12700F', 'intel-core-i7-12700f-12', 'Sản phẩm chính hãng', 'Mô tả chi tiết Intel Core i7-12700F', 7915000, NULL, NULL, 0, 'cpu.png', 4.8, 87, 65, '{"socket": "LGA1700", "memory_type": ["DDR4", "DDR5"], "base_power_w": 219, "integrated_graphics": false}', 0, 0, 0, 0, 'cpu', 219, NULL),
(13, 10, 9, 'Intel Core i7-13700F', 'intel-core-i7-13700f-13', 'Sản phẩm chính hãng', 'Mô tả chi tiết Intel Core i7-13700F', 9392000, NULL, NULL, 0, 'cpu.png', 4.4, 12, 82, '{"socket": "LGA1700", "memory_type": ["DDR4", "DDR5"], "base_power_w": 219, "integrated_graphics": false}', 0, 0, 0, 0, 'cpu', 219, NULL),
(14, 10, 9, 'Intel Core i7-14700F', 'intel-core-i7-14700f-14', 'Sản phẩm chính hãng', 'Mô tả chi tiết Intel Core i7-14700F', 9318000, NULL, NULL, 0, 'cpu.png', 4.8, 61, 184, '{"socket": "LGA1700", "memory_type": ["DDR4", "DDR5"], "base_power_w": 219, "integrated_graphics": false}', 0, 0, 0, 0, 'cpu', 219, NULL),
(15, 10, 9, 'Intel Core i7-14700K', 'intel-core-i7-14700k-15', 'Sản phẩm chính hãng', 'Mô tả chi tiết Intel Core i7-14700K', 10659000, NULL, NULL, 0, 'cpu.png', 4.9, 90, 131, '{"socket": "LGA1700", "memory_type": ["DDR4", "DDR5"], "base_power_w": 253, "integrated_graphics": true}', 0, 0, 0, 0, 'cpu', 253, NULL),
(16, 10, 9, 'Intel Core i9-13900K', 'intel-core-i9-13900k-16', 'Sản phẩm chính hãng', 'Mô tả chi tiết Intel Core i9-13900K', 14491000, NULL, NULL, 0, 'cpu.png', 4.6, 28, 19, '{"socket": "LGA1700", "memory_type": ["DDR4", "DDR5"], "base_power_w": 253, "integrated_graphics": true}', 0, 0, 0, 0, 'cpu', 253, NULL),
(17, 10, 9, 'Intel Core i9-14900K', 'intel-core-i9-14900k-17', 'Sản phẩm chính hãng', 'Mô tả chi tiết Intel Core i9-14900K', 15482000, NULL, NULL, 0, 'cpu.png', 4.6, 89, 40, '{"socket": "LGA1700", "memory_type": ["DDR4", "DDR5"], "base_power_w": 253, "integrated_graphics": true}', 0, 0, 0, 0, 'cpu', 253, NULL),
(18, 10, 10, 'AMD Ryzen 5 5500', 'amd-ryzen-5-5500-18', 'Sản phẩm chính hãng', 'Mô tả chi tiết AMD Ryzen 5 5500', 2581000, NULL, NULL, 0, 'cpu.png', 4.1, 47, 65, '{"socket": "AM4", "memory_type": ["DDR4"], "base_power_w": 65, "integrated_graphics": false}', 0, 0, 0, 0, 'cpu', 65, NULL),
(19, 10, 10, 'AMD Ryzen 5 5600', 'amd-ryzen-5-5600-19', 'Sản phẩm chính hãng', 'Mô tả chi tiết AMD Ryzen 5 5600', 3379000, NULL, NULL, 0, 'cpu.png', 4.3, 27, 187, '{"socket": "AM4", "memory_type": ["DDR4"], "base_power_w": 65, "integrated_graphics": false}', 0, 0, 0, 0, 'cpu', 65, NULL),
(20, 10, 10, 'AMD Ryzen 5 5600X', 'amd-ryzen-5-5600x-20', 'Sản phẩm chính hãng', 'Mô tả chi tiết AMD Ryzen 5 5600X', 4278000, NULL, NULL, 0, 'cpu.png', 4.3, 11, 129, '{"socket": "AM4", "memory_type": ["DDR4"], "base_power_w": 65, "integrated_graphics": false}', 0, 0, 0, 0, 'cpu', 65, NULL),
(21, 10, 10, 'AMD Ryzen 5 7500F', 'amd-ryzen-5-7500f-21', 'Sản phẩm chính hãng', 'Mô tả chi tiết AMD Ryzen 5 7500F', 4282000, NULL, NULL, 0, 'cpu.png', 4.8, 88, 53, '{"socket": "AM5", "memory_type": ["DDR5"], "base_power_w": 65, "integrated_graphics": false}', 0, 0, 0, 0, 'cpu', 65, NULL),
(22, 10, 10, 'AMD Ryzen 5 7600', 'amd-ryzen-5-7600-22', 'Sản phẩm chính hãng', 'Mô tả chi tiết AMD Ryzen 5 7600', 5596000, NULL, NULL, 0, 'cpu.png', 5.0, 39, 52, '{"socket": "AM5", "memory_type": ["DDR5"], "base_power_w": 65, "integrated_graphics": true}', 0, 0, 0, 0, 'cpu', 65, NULL),
(23, 10, 10, 'AMD Ryzen 5 7600X', 'amd-ryzen-5-7600x-23', 'Sản phẩm chính hãng', 'Mô tả chi tiết AMD Ryzen 5 7600X', 5891000, NULL, NULL, 0, 'cpu.png', 4.3, 33, 58, '{"socket": "AM5", "memory_type": ["DDR5"], "base_power_w": 105, "integrated_graphics": true}', 0, 0, 0, 0, 'cpu', 105, NULL),
(24, 10, 10, 'AMD Ryzen 5 9600X', 'amd-ryzen-5-9600x-24', 'Sản phẩm chính hãng', 'Mô tả chi tiết AMD Ryzen 5 9600X', 7355000, NULL, NULL, 0, 'cpu.png', 4.0, 26, 32, '{"socket": "AM5", "memory_type": ["DDR5"], "base_power_w": 65, "integrated_graphics": true}', 0, 0, 0, 0, 'cpu', 65, NULL),
(25, 10, 10, 'AMD Ryzen 7 5700X', 'amd-ryzen-7-5700x-25', 'Sản phẩm chính hãng', 'Mô tả chi tiết AMD Ryzen 7 5700X', 4815000, NULL, NULL, 0, 'cpu.png', 4.5, 32, 125, '{"socket": "AM4", "memory_type": ["DDR4"], "base_power_w": 65, "integrated_graphics": false}', 0, 0, 0, 0, 'cpu', 65, NULL),
(26, 10, 10, 'AMD Ryzen 7 5800X', 'amd-ryzen-7-5800x-26', 'Sản phẩm chính hãng', 'Mô tả chi tiết AMD Ryzen 7 5800X', 5993000, NULL, NULL, 0, 'cpu.png', 4.8, 42, 13, '{"socket": "AM4", "memory_type": ["DDR4"], "base_power_w": 105, "integrated_graphics": false}', 0, 0, 0, 0, 'cpu', 105, NULL),
(27, 10, 10, 'AMD Ryzen 7 7700', 'amd-ryzen-7-7700-27', 'Sản phẩm chính hãng', 'Mô tả chi tiết AMD Ryzen 7 7700', 8437000, NULL, NULL, 0, 'cpu.png', 4.4, 58, 140, '{"socket": "AM5", "memory_type": ["DDR5"], "base_power_w": 65, "integrated_graphics": true}', 0, 0, 0, 0, 'cpu', 65, NULL),
(28, 10, 10, 'AMD Ryzen 7 7700X', 'amd-ryzen-7-7700x-28', 'Sản phẩm chính hãng', 'Mô tả chi tiết AMD Ryzen 7 7700X', 8958000, NULL, NULL, 0, 'cpu.png', 4.6, 97, 177, '{"socket": "AM5", "memory_type": ["DDR5"], "base_power_w": 105, "integrated_graphics": true}', 0, 0, 0, 0, 'cpu', 105, NULL),
(29, 10, 10, 'AMD Ryzen 7 7800X3D', 'amd-ryzen-7-7800x3d-29', 'Sản phẩm chính hãng', 'Mô tả chi tiết AMD Ryzen 7 7800X3D', 10807000, NULL, NULL, 0, 'cpu.png', 4.8, 75, 102, '{"socket": "AM5", "memory_type": ["DDR5"], "base_power_w": 120, "integrated_graphics": true}', 0, 0, 0, 0, 'cpu', 120, NULL),
(30, 10, 10, 'AMD Ryzen 7 9700X', 'amd-ryzen-7-9700x-30', 'Sản phẩm chính hãng', 'Mô tả chi tiết AMD Ryzen 7 9700X', 10535000, NULL, NULL, 0, 'cpu.png', 4.9, 82, 38, '{"socket": "AM5", "memory_type": ["DDR5"], "base_power_w": 65, "integrated_graphics": true}', 0, 0, 0, 0, 'cpu', 65, NULL),
(31, 10, 10, 'AMD Ryzen 9 7900', 'amd-ryzen-9-7900-31', 'Sản phẩm chính hãng', 'Mô tả chi tiết AMD Ryzen 9 7900', 11411000, NULL, NULL, 0, 'cpu.png', 4.1, 72, 57, '{"socket": "AM5", "memory_type": ["DDR5"], "base_power_w": 65, "integrated_graphics": true}', 0, 0, 0, 0, 'cpu', 65, NULL),
(32, 10, 10, 'AMD Ryzen 9 7900X', 'amd-ryzen-9-7900x-32', 'Sản phẩm chính hãng', 'Mô tả chi tiết AMD Ryzen 9 7900X', 11984000, NULL, NULL, 0, 'cpu.png', 4.2, 55, 157, '{"socket": "AM5", "memory_type": ["DDR5"], "base_power_w": 170, "integrated_graphics": true}', 0, 0, 0, 0, 'cpu', 170, NULL),
(33, 10, 10, 'AMD Ryzen 9 7950X', 'amd-ryzen-9-7950x-33', 'Sản phẩm chính hãng', 'Mô tả chi tiết AMD Ryzen 9 7950X', 15038000, NULL, NULL, 0, 'cpu.png', 4.4, 52, 70, '{"socket": "AM5", "memory_type": ["DDR5"], "base_power_w": 170, "integrated_graphics": true}', 0, 0, 0, 0, 'cpu', 170, NULL),
(34, 10, 10, 'AMD Ryzen 9 9900X', 'amd-ryzen-9-9900x-34', 'Sản phẩm chính hãng', 'Mô tả chi tiết AMD Ryzen 9 9900X', 13942000, NULL, NULL, 0, 'cpu.png', 4.4, 79, 179, '{"socket": "AM5", "memory_type": ["DDR5"], "base_power_w": 120, "integrated_graphics": true}', 0, 0, 0, 0, 'cpu', 120, NULL),
(35, 10, 10, 'AMD Ryzen 9 9950X', 'amd-ryzen-9-9950x-35', 'Sản phẩm chính hãng', 'Mô tả chi tiết AMD Ryzen 9 9950X', 18541000, NULL, NULL, 0, 'cpu.png', 4.2, 19, 73, '{"socket": "AM5", "memory_type": ["DDR5"], "base_power_w": 170, "integrated_graphics": true}', 0, 0, 0, 0, 'cpu', 170, NULL),
(36, 10, 10, 'AMD Ryzen 5 7600X', 'amd-ryzen-5-7600x-36', 'Sản phẩm chính hãng', 'Mô tả chi tiết AMD Ryzen 5 7600X', 6420000, NULL, NULL, 0, 'cpu.png', 4.9, 42, 17, '{"socket": "AM5", "memory_type": ["DDR5"], "base_power_w": 105, "integrated_graphics": true}', 0, 0, 0, 0, 'cpu', 105, NULL),
(37, 10, 9, 'Intel Core i3-13100F', 'intel-core-i3-13100f-37', 'Sản phẩm chính hãng', 'Mô tả chi tiết Intel Core i3-13100F', 3208000, NULL, NULL, 0, 'cpu.png', 4.8, 27, 66, '{"socket": "LGA1700", "memory_type": ["DDR4", "DDR5"], "base_power_w": 89, "integrated_graphics": false}', 0, 0, 0, 0, 'cpu', 89, NULL),
(38, 10, 10, 'AMD Ryzen 5 7600', 'amd-ryzen-5-7600-38', 'Sản phẩm chính hãng', 'Mô tả chi tiết AMD Ryzen 5 7600', 5557000, NULL, NULL, 0, 'cpu.png', 4.2, 88, 153, '{"socket": "AM5", "memory_type": ["DDR5"], "base_power_w": 65, "integrated_graphics": true}', 0, 0, 0, 0, 'cpu', 65, NULL),
(39, 10, 10, 'AMD Ryzen 9 7900X', 'amd-ryzen-9-7900x-39', 'Sản phẩm chính hãng', 'Mô tả chi tiết AMD Ryzen 9 7900X', 12493000, NULL, NULL, 0, 'cpu.png', 4.9, 83, 92, '{"socket": "AM5", "memory_type": ["DDR5"], "base_power_w": 170, "integrated_graphics": true}', 0, 0, 0, 0, 'cpu', 170, NULL),
(40, 10, 9, 'Intel Core i5-12400', 'intel-core-i5-12400-40', 'Sản phẩm chính hãng', 'Mô tả chi tiết Intel Core i5-12400', 3318000, NULL, NULL, 0, 'cpu.png', 4.6, 36, 200, '{"socket": "LGA1700", "memory_type": ["DDR4", "DDR5"], "base_power_w": 117, "integrated_graphics": true}', 0, 0, 0, 0, 'cpu', 117, NULL),
(41, 11, 1, 'ASUS PRIME H610M-K D4', 'asus-prime-h610m-k-d4-41', 'Sản phẩm chính hãng', 'Mô tả chi tiết ASUS PRIME H610M-K D4', 2088000, NULL, NULL, 0, 'main.png', 4.7, 72, 133, '{"socket": "LGA1700", "chipset": "H610", "memory_type": "DDR4", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(42, 11, 1, 'ASUS PRIME B760M-K D4', 'asus-prime-b760m-k-d4-42', 'Sản phẩm chính hãng', 'Mô tả chi tiết ASUS PRIME B760M-K D4', 2744000, NULL, NULL, 0, 'main.png', 4.8, 69, 51, '{"socket": "LGA1700", "chipset": "B760", "memory_type": "DDR4", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(43, 11, 1, 'ASUS PRIME Z790-P WIFI', 'asus-prime-z790-p-wifi-43', 'Sản phẩm chính hãng', 'Mô tả chi tiết ASUS PRIME Z790-P WIFI', 5627000, NULL, NULL, 0, 'main.png', 4.7, 15, 81, '{"socket": "LGA1700", "chipset": "Z790", "memory_type": "DDR5", "form_factor": "ATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(44, 11, 2, 'MSI PRO B760M-E', 'msi-pro-b760m-e-44', 'Sản phẩm chính hãng', 'Mô tả chi tiết MSI PRO B760M-E', 2597000, NULL, NULL, 0, 'main.png', 4.7, 61, 39, '{"socket": "LGA1700", "chipset": "B760", "memory_type": "DDR4", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(45, 11, 3, 'Gigabyte B760M DS3H', 'gigabyte-b760m-ds3h-45', 'Sản phẩm chính hãng', 'Mô tả chi tiết Gigabyte B760M DS3H', 2779000, NULL, NULL, 0, 'main.png', 4.4, 79, 44, '{"socket": "LGA1700", "chipset": "B760", "memory_type": "DDR4", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(46, 11, 30, 'ASRock B650M Pro RS', 'asrock-b650m-pro-rs-46', 'Sản phẩm chính hãng', 'Mô tả chi tiết ASRock B650M Pro RS', 3969000, NULL, NULL, 0, 'main.png', 4.6, 58, 39, '{"socket": "AM5", "chipset": "B650", "memory_type": "DDR5", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(47, 11, 32, 'Colorful CVN B760M Frozen', 'colorful-cvn-b760m-frozen-47', 'Sản phẩm chính hãng', 'Mô tả chi tiết Colorful CVN B760M Frozen', 3918000, NULL, NULL, 0, 'main.png', 4.4, 7, 53, '{"socket": "LGA1700", "chipset": "B760", "memory_type": "DDR5", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(48, 11, 1, 'ASUS PRIME B760M-K D4 V5', 'asus-prime-b760m-k-d4-v5-48', 'Sản phẩm chính hãng', 'Mô tả chi tiết ASUS PRIME B760M-K D4 V5', 2460000, NULL, NULL, 0, 'main.png', 4.4, 49, 65, '{"socket": "LGA1700", "chipset": "B760", "memory_type": "DDR4", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(49, 11, 1, 'ASUS PRIME H610M-K D4 V1', 'asus-prime-h610m-k-d4-v1-49', 'Sản phẩm chính hãng', 'Mô tả chi tiết ASUS PRIME H610M-K D4 V1', 1920000, NULL, NULL, 0, 'main.png', 4.1, 65, 195, '{"socket": "LGA1700", "chipset": "H610", "memory_type": "DDR4", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(50, 11, 1, 'ASUS PRIME B760M-K D4 V5', 'asus-prime-b760m-k-d4-v5-50', 'Sản phẩm chính hãng', 'Mô tả chi tiết ASUS PRIME B760M-K D4 V5', 2773000, NULL, NULL, 0, 'main.png', 4.1, 24, 38, '{"socket": "LGA1700", "chipset": "B760", "memory_type": "DDR4", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(51, 11, 32, 'Colorful CVN B760M Frozen V3', 'colorful-cvn-b760m-frozen-v3-51', 'Sản phẩm chính hãng', 'Mô tả chi tiết Colorful CVN B760M Frozen V3', 3956000, NULL, NULL, 0, 'main.png', 4.2, 59, 89, '{"socket": "LGA1700", "chipset": "B760", "memory_type": "DDR5", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(52, 11, 32, 'Colorful CVN B760M Frozen V1', 'colorful-cvn-b760m-frozen-v1-52', 'Sản phẩm chính hãng', 'Mô tả chi tiết Colorful CVN B760M Frozen V1', 4498000, NULL, NULL, 0, 'main.png', 4.3, 36, 54, '{"socket": "LGA1700", "chipset": "B760", "memory_type": "DDR5", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(53, 11, 1, 'ASUS PRIME H610M-K D4 V1 V4', 'asus-prime-h610m-k-d4-v1-v4-53', 'Sản phẩm chính hãng', 'Mô tả chi tiết ASUS PRIME H610M-K D4 V1 V4', 2346000, NULL, NULL, 0, 'main.png', 5.0, 36, 61, '{"socket": "LGA1700", "chipset": "H610", "memory_type": "DDR4", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(54, 11, 1, 'ASUS PRIME Z790-P WIFI V5', 'asus-prime-z790-p-wifi-v5-54', 'Sản phẩm chính hãng', 'Mô tả chi tiết ASUS PRIME Z790-P WIFI V5', 5394000, NULL, NULL, 0, 'main.png', 4.6, 25, 163, '{"socket": "LGA1700", "chipset": "Z790", "memory_type": "DDR5", "form_factor": "ATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(55, 11, 32, 'Colorful CVN B760M Frozen V3 V5', 'colorful-cvn-b760m-frozen-v3-v5-55', 'Sản phẩm chính hãng', 'Mô tả chi tiết Colorful CVN B760M Frozen V3 V5', 4000000, NULL, NULL, 0, 'main.png', 4.5, 47, 200, '{"socket": "LGA1700", "chipset": "B760", "memory_type": "DDR5", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(56, 11, 3, 'Gigabyte B760M DS3H V5', 'gigabyte-b760m-ds3h-v5-56', 'Sản phẩm chính hãng', 'Mô tả chi tiết Gigabyte B760M DS3H V5', 3066000, NULL, NULL, 0, 'main.png', 4.5, 14, 60, '{"socket": "LGA1700", "chipset": "B760", "memory_type": "DDR4", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(57, 11, 1, 'ASUS PRIME B760M-K D4 V5 V2', 'asus-prime-b760m-k-d4-v5-v2-57', 'Sản phẩm chính hãng', 'Mô tả chi tiết ASUS PRIME B760M-K D4 V5 V2', 2782000, NULL, NULL, 0, 'main.png', 4.1, 59, 54, '{"socket": "LGA1700", "chipset": "B760", "memory_type": "DDR4", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(58, 11, 2, 'MSI PRO B760M-E V5', 'msi-pro-b760m-e-v5-58', 'Sản phẩm chính hãng', 'Mô tả chi tiết MSI PRO B760M-E V5', 2889000, NULL, NULL, 0, 'main.png', 4.8, 75, 83, '{"socket": "LGA1700", "chipset": "B760", "memory_type": "DDR4", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(59, 11, 1, 'ASUS PRIME B760M-K D4 V4', 'asus-prime-b760m-k-d4-v4-59', 'Sản phẩm chính hãng', 'Mô tả chi tiết ASUS PRIME B760M-K D4 V4', 2782000, NULL, NULL, 0, 'main.png', 4.1, 11, 193, '{"socket": "LGA1700", "chipset": "B760", "memory_type": "DDR4", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(60, 11, 1, 'ASUS PRIME B760M-K D4 V5 V3', 'asus-prime-b760m-k-d4-v5-v3-60', 'Sản phẩm chính hãng', 'Mô tả chi tiết ASUS PRIME B760M-K D4 V5 V3', 2859000, NULL, NULL, 0, 'main.png', 4.0, 27, 39, '{"socket": "LGA1700", "chipset": "B760", "memory_type": "DDR4", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(61, 11, 2, 'MSI PRO B760M-E V5 V4', 'msi-pro-b760m-e-v5-v4-61', 'Sản phẩm chính hãng', 'Mô tả chi tiết MSI PRO B760M-E V5 V4', 2825000, NULL, NULL, 0, 'main.png', 4.4, 68, 54, '{"socket": "LGA1700", "chipset": "B760", "memory_type": "DDR4", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(62, 11, 1, 'ASUS PRIME H610M-K D4 V1 V4 V2', 'asus-prime-h610m-k-d4-v1-v4-v2-62', 'Sản phẩm chính hãng', 'Mô tả chi tiết ASUS PRIME H610M-K D4 V1 V4 V2', 1808000, NULL, NULL, 0, 'main.png', 4.5, 53, 163, '{"socket": "LGA1700", "chipset": "H610", "memory_type": "DDR4", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(63, 11, 30, 'ASRock B650M Pro RS V3', 'asrock-b650m-pro-rs-v3-63', 'Sản phẩm chính hãng', 'Mô tả chi tiết ASRock B650M Pro RS V3', 3481000, NULL, NULL, 0, 'main.png', 4.5, 81, 63, '{"socket": "AM5", "chipset": "B650", "memory_type": "DDR5", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(64, 11, 32, 'Colorful CVN B760M Frozen V4', 'colorful-cvn-b760m-frozen-v4-64', 'Sản phẩm chính hãng', 'Mô tả chi tiết Colorful CVN B760M Frozen V4', 3847000, NULL, NULL, 0, 'main.png', 4.1, 45, 122, '{"socket": "LGA1700", "chipset": "B760", "memory_type": "DDR5", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(65, 11, 2, 'MSI PRO B760M-E V5 V4 V4', 'msi-pro-b760m-e-v5-v4-v4-65', 'Sản phẩm chính hãng', 'Mô tả chi tiết MSI PRO B760M-E V5 V4 V4', 2412000, NULL, NULL, 0, 'main.png', 4.2, 97, 132, '{"socket": "LGA1700", "chipset": "B760", "memory_type": "DDR4", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(66, 11, 32, 'Colorful CVN B760M Frozen V1 V3', 'colorful-cvn-b760m-frozen-v1-v3-66', 'Sản phẩm chính hãng', 'Mô tả chi tiết Colorful CVN B760M Frozen V1 V3', 4455000, NULL, NULL, 0, 'main.png', 4.7, 47, 15, '{"socket": "LGA1700", "chipset": "B760", "memory_type": "DDR5", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(67, 11, 1, 'ASUS PRIME H610M-K D4 V1 V4 V1', 'asus-prime-h610m-k-d4-v1-v4-v1-67', 'Sản phẩm chính hãng', 'Mô tả chi tiết ASUS PRIME H610M-K D4 V1 V4 V1', 1834000, NULL, NULL, 0, 'main.png', 4.3, 98, 108, '{"socket": "LGA1700", "chipset": "H610", "memory_type": "DDR4", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(68, 11, 2, 'MSI PRO B760M-E V5 V4 V2', 'msi-pro-b760m-e-v5-v4-v2-68', 'Sản phẩm chính hãng', 'Mô tả chi tiết MSI PRO B760M-E V5 V4 V2', 2557000, NULL, NULL, 0, 'main.png', 4.8, 75, 131, '{"socket": "LGA1700", "chipset": "B760", "memory_type": "DDR4", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(69, 11, 30, 'ASRock B650M Pro RS V3 V2', 'asrock-b650m-pro-rs-v3-v2-69', 'Sản phẩm chính hãng', 'Mô tả chi tiết ASRock B650M Pro RS V3 V2', 3701000, NULL, NULL, 0, 'main.png', 4.6, 30, 81, '{"socket": "AM5", "chipset": "B650", "memory_type": "DDR5", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(70, 11, 32, 'Colorful CVN B760M Frozen V3 V2', 'colorful-cvn-b760m-frozen-v3-v2-70', 'Sản phẩm chính hãng', 'Mô tả chi tiết Colorful CVN B760M Frozen V3 V2', 4175000, NULL, NULL, 0, 'main.png', 4.9, 91, 161, '{"socket": "LGA1700", "chipset": "B760", "memory_type": "DDR5", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(71, 11, 30, 'ASRock B650M Pro RS V3 V2 V1', 'asrock-b650m-pro-rs-v3-v2-v1-71', 'Sản phẩm chính hãng', 'Mô tả chi tiết ASRock B650M Pro RS V3 V2 V1', 3858000, NULL, NULL, 0, 'main.png', 4.2, 86, 41, '{"socket": "AM5", "chipset": "B650", "memory_type": "DDR5", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(72, 11, 2, 'MSI PRO B760M-E V5 V4 V5', 'msi-pro-b760m-e-v5-v4-v5-72', 'Sản phẩm chính hãng', 'Mô tả chi tiết MSI PRO B760M-E V5 V4 V5', 3074000, NULL, NULL, 0, 'main.png', 4.6, 31, 132, '{"socket": "LGA1700", "chipset": "B760", "memory_type": "DDR4", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(73, 11, 32, 'Colorful CVN B760M Frozen V1 V3 V4', 'colorful-cvn-b760m-frozen-v1-v3-v4-73', 'Sản phẩm chính hãng', 'Mô tả chi tiết Colorful CVN B760M Frozen V1 V3 V4', 4102000, NULL, NULL, 0, 'main.png', 4.7, 49, 113, '{"socket": "LGA1700", "chipset": "B760", "memory_type": "DDR5", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(74, 11, 2, 'MSI PRO B760M-E V5 V4 V4', 'msi-pro-b760m-e-v5-v4-v4-74', 'Sản phẩm chính hãng', 'Mô tả chi tiết MSI PRO B760M-E V5 V4 V4', 2650000, NULL, NULL, 0, 'main.png', 4.1, 90, 93, '{"socket": "LGA1700", "chipset": "B760", "memory_type": "DDR4", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(75, 11, 30, 'ASRock B650M Pro RS V3 V2 V2', 'asrock-b650m-pro-rs-v3-v2-v2-75', 'Sản phẩm chính hãng', 'Mô tả chi tiết ASRock B650M Pro RS V3 V2 V2', 3795000, NULL, NULL, 0, 'main.png', 4.2, 21, 149, '{"socket": "AM5", "chipset": "B650", "memory_type": "DDR5", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(76, 11, 2, 'MSI PRO B760M-E V5 V4 V4 V5', 'msi-pro-b760m-e-v5-v4-v4-v5-76', 'Sản phẩm chính hãng', 'Mô tả chi tiết MSI PRO B760M-E V5 V4 V4 V5', 3003000, NULL, NULL, 0, 'main.png', 4.1, 68, 28, '{"socket": "LGA1700", "chipset": "B760", "memory_type": "DDR4", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(77, 11, 1, 'ASUS PRIME B760M-K D4 V5 V3 V4', 'asus-prime-b760m-k-d4-v5-v3-v4-77', 'Sản phẩm chính hãng', 'Mô tả chi tiết ASUS PRIME B760M-K D4 V5 V3 V4', 2630000, NULL, NULL, 0, 'main.png', 4.5, 48, 182, '{"socket": "LGA1700", "chipset": "B760", "memory_type": "DDR4", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(78, 11, 1, 'ASUS PRIME B760M-K D4 V4 V1', 'asus-prime-b760m-k-d4-v4-v1-78', 'Sản phẩm chính hãng', 'Mô tả chi tiết ASUS PRIME B760M-K D4 V4 V1', 2962000, NULL, NULL, 0, 'main.png', 4.8, 8, 129, '{"socket": "LGA1700", "chipset": "B760", "memory_type": "DDR4", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(79, 11, 2, 'MSI PRO B760M-E V5 V4 V3', 'msi-pro-b760m-e-v5-v4-v3-79', 'Sản phẩm chính hãng', 'Mô tả chi tiết MSI PRO B760M-E V5 V4 V3', 2962000, NULL, NULL, 0, 'main.png', 4.2, 24, 111, '{"socket": "LGA1700", "chipset": "B760", "memory_type": "DDR4", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(80, 11, 1, 'ASUS PRIME H610M-K D4 V1 V4 V1 V5', 'asus-prime-h610m-k-d4-v1-v4-v1-v5-80', 'Sản phẩm chính hãng', 'Mô tả chi tiết ASUS PRIME H610M-K D4 V1 V4 V1 V5', 2489000, NULL, NULL, 0, 'main.png', 4.5, 95, 80, '{"socket": "LGA1700", "chipset": "H610", "memory_type": "DDR4", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(81, 11, 30, 'ASRock B650M Pro RS V3 V2 V1 V2', 'asrock-b650m-pro-rs-v3-v2-v1-v2-81', 'Sản phẩm chính hãng', 'Mô tả chi tiết ASRock B650M Pro RS V3 V2 V1 V2', 3861000, NULL, NULL, 0, 'main.png', 5.0, 66, 158, '{"socket": "AM5", "chipset": "B650", "memory_type": "DDR5", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(82, 11, 1, 'ASUS PRIME H610M-K D4 V1 V4 V1 V1', 'asus-prime-h610m-k-d4-v1-v4-v1-v1-82', 'Sản phẩm chính hãng', 'Mô tả chi tiết ASUS PRIME H610M-K D4 V1 V4 V1 V1', 2021000, NULL, NULL, 0, 'main.png', 4.2, 35, 157, '{"socket": "LGA1700", "chipset": "H610", "memory_type": "DDR4", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(83, 11, 1, 'ASUS PRIME H610M-K D4 V1 V4 V1 V5', 'asus-prime-h610m-k-d4-v1-v4-v1-v5-83', 'Sản phẩm chính hãng', 'Mô tả chi tiết ASUS PRIME H610M-K D4 V1 V4 V1 V5', 2149000, NULL, NULL, 0, 'main.png', 4.8, 91, 44, '{"socket": "LGA1700", "chipset": "H610", "memory_type": "DDR4", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(84, 11, 2, 'MSI PRO B760M-E V5 V4 V4 V5', 'msi-pro-b760m-e-v5-v4-v4-v5-84', 'Sản phẩm chính hãng', 'Mô tả chi tiết MSI PRO B760M-E V5 V4 V4 V5', 2433000, NULL, NULL, 0, 'main.png', 4.7, 8, 77, '{"socket": "LGA1700", "chipset": "B760", "memory_type": "DDR4", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(85, 11, 30, 'ASRock B650M Pro RS V3 V2 V1 V1', 'asrock-b650m-pro-rs-v3-v2-v1-v1-85', 'Sản phẩm chính hãng', 'Mô tả chi tiết ASRock B650M Pro RS V3 V2 V1 V1', 3704000, NULL, NULL, 0, 'main.png', 4.7, 93, 54, '{"socket": "AM5", "chipset": "B650", "memory_type": "DDR5", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(86, 11, 1, 'ASUS PRIME H610M-K D4 V1 V4 V2 V1', 'asus-prime-h610m-k-d4-v1-v4-v2-v1-86', 'Sản phẩm chính hãng', 'Mô tả chi tiết ASUS PRIME H610M-K D4 V1 V4 V2 V1', 2249000, NULL, NULL, 0, 'main.png', 4.6, 47, 189, '{"socket": "LGA1700", "chipset": "H610", "memory_type": "DDR4", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(87, 11, 1, 'ASUS PRIME B760M-K D4 V5 V3 V3', 'asus-prime-b760m-k-d4-v5-v3-v3-87', 'Sản phẩm chính hãng', 'Mô tả chi tiết ASUS PRIME B760M-K D4 V5 V3 V3', 2608000, NULL, NULL, 0, 'main.png', 4.4, 61, 91, '{"socket": "LGA1700", "chipset": "B760", "memory_type": "DDR4", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(88, 11, 1, 'ASUS PRIME Z790-P WIFI V5 V3', 'asus-prime-z790-p-wifi-v5-v3-88', 'Sản phẩm chính hãng', 'Mô tả chi tiết ASUS PRIME Z790-P WIFI V5 V3', 5940000, NULL, NULL, 0, 'main.png', 4.4, 53, 173, '{"socket": "LGA1700", "chipset": "Z790", "memory_type": "DDR5", "form_factor": "ATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(89, 11, 1, 'ASUS PRIME H610M-K D4 V1 V4 V1', 'asus-prime-h610m-k-d4-v1-v4-v1-89', 'Sản phẩm chính hãng', 'Mô tả chi tiết ASUS PRIME H610M-K D4 V1 V4 V1', 2021000, NULL, NULL, 0, 'main.png', 4.1, 38, 131, '{"socket": "LGA1700", "chipset": "H610", "memory_type": "DDR4", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(90, 11, 2, 'MSI PRO B760M-E V5 V4 V3 V1', 'msi-pro-b760m-e-v5-v4-v3-v1-90', 'Sản phẩm chính hãng', 'Mô tả chi tiết MSI PRO B760M-E V5 V4 V3 V1', 3046000, NULL, NULL, 0, 'main.png', 4.1, 29, 131, '{"socket": "LGA1700", "chipset": "B760", "memory_type": "DDR4", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(91, 11, 1, 'ASUS PRIME B760M-K D4 V5 V2 V1', 'asus-prime-b760m-k-d4-v5-v2-v1-91', 'Sản phẩm chính hãng', 'Mô tả chi tiết ASUS PRIME B760M-K D4 V5 V2 V1', 2436000, NULL, NULL, 0, 'main.png', 4.5, 55, 82, '{"socket": "LGA1700", "chipset": "B760", "memory_type": "DDR4", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(92, 11, 1, 'ASUS PRIME H610M-K D4 V1 V4 V1 V5 V5', 'asus-prime-h610m-k-d4-v1-v4-v1-v5-v5-92', 'Sản phẩm chính hãng', 'Mô tả chi tiết ASUS PRIME H610M-K D4 V1 V4 V1 V5 V5', 2018000, NULL, NULL, 0, 'main.png', 4.4, 59, 16, '{"socket": "LGA1700", "chipset": "H610", "memory_type": "DDR4", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(93, 11, 1, 'ASUS PRIME H610M-K D4 V1 V3', 'asus-prime-h610m-k-d4-v1-v3-93', 'Sản phẩm chính hãng', 'Mô tả chi tiết ASUS PRIME H610M-K D4 V1 V3', 2312000, NULL, NULL, 0, 'main.png', 4.1, 30, 36, '{"socket": "LGA1700", "chipset": "H610", "memory_type": "DDR4", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(94, 11, 1, 'ASUS PRIME H610M-K D4 V1 V4 V2 V1 V2', 'asus-prime-h610m-k-d4-v1-v4-v2-v1-v2-94', 'Sản phẩm chính hãng', 'Mô tả chi tiết ASUS PRIME H610M-K D4 V1 V4 V2 V1 V2', 2186000, NULL, NULL, 0, 'main.png', 4.8, 29, 79, '{"socket": "LGA1700", "chipset": "H610", "memory_type": "DDR4", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(95, 11, 30, 'ASRock B650M Pro RS V3 V3', 'asrock-b650m-pro-rs-v3-v3-95', 'Sản phẩm chính hãng', 'Mô tả chi tiết ASRock B650M Pro RS V3 V3', 3542000, NULL, NULL, 0, 'main.png', 4.5, 41, 171, '{"socket": "AM5", "chipset": "B650", "memory_type": "DDR5", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(96, 11, 32, 'Colorful CVN B760M Frozen V4 V2', 'colorful-cvn-b760m-frozen-v4-v2-96', 'Sản phẩm chính hãng', 'Mô tả chi tiết Colorful CVN B760M Frozen V4 V2', 4259000, NULL, NULL, 0, 'main.png', 4.6, 74, 110, '{"socket": "LGA1700", "chipset": "B760", "memory_type": "DDR5", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(97, 11, 1, 'ASUS PRIME B760M-K D4 V4 V4', 'asus-prime-b760m-k-d4-v4-v4-97', 'Sản phẩm chính hãng', 'Mô tả chi tiết ASUS PRIME B760M-K D4 V4 V4', 2809000, NULL, NULL, 0, 'main.png', 4.3, 90, 89, '{"socket": "LGA1700", "chipset": "B760", "memory_type": "DDR4", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(98, 11, 1, 'ASUS PRIME Z790-P WIFI V1', 'asus-prime-z790-p-wifi-v1-98', 'Sản phẩm chính hãng', 'Mô tả chi tiết ASUS PRIME Z790-P WIFI V1', 5779000, NULL, NULL, 0, 'main.png', 4.1, 51, 18, '{"socket": "LGA1700", "chipset": "Z790", "memory_type": "DDR5", "form_factor": "ATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(99, 11, 2, 'MSI PRO B760M-E V5 V4 V3 V5', 'msi-pro-b760m-e-v5-v4-v3-v5-99', 'Sản phẩm chính hãng', 'Mô tả chi tiết MSI PRO B760M-E V5 V4 V3 V5', 2791000, NULL, NULL, 0, 'main.png', 4.1, 74, 122, '{"socket": "LGA1700", "chipset": "B760", "memory_type": "DDR4", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL),
(100, 11, 32, 'Colorful CVN B760M Frozen V3 V2 V2', 'colorful-cvn-b760m-frozen-v3-v2-v2-100', 'Sản phẩm chính hãng', 'Mô tả chi tiết Colorful CVN B760M Frozen V3 V2 V2', 4047000, NULL, NULL, 0, 'main.png', 4.5, 91, 96, '{"socket": "LGA1700", "chipset": "B760", "memory_type": "DDR5", "form_factor": "mATX", "ram_slots": 4, "max_ram_gb": 128}', 0, 0, 0, 0, 'mainboard', 50, NULL);

-- 5. Banner qu?ng c�o (banners)
INSERT INTO banners (title, image, link, type, position) VALUES 
('ROG Zephyrus G16 - S?c m?nh vu?t tr?i', 'rog-banner-bg.jpg', 'product/detail/asus-rog-zephyrus-g16', 'hero', 1),
('Build PC theo y�u c?u - T?i uu c?u h�nh', 'banner-2.jpg', '#', 'hero_sidebar', 1),
('Tr? g�p 0% - Duy?t nhanh 3 ph�t', 'banner-3.jpg', '#', 'hero_sidebar', 2),
('Thu cu d?i m?i - Tr? gi� l�n t?i 6 tri?u', 'installment-banner.jpg', '#', 'hero_sidebar', 3),
('RTX 50 Series - S?p ra m?t', 'banner-rtx-bg.jpg', '#', 'mid_banner', 1),
('Tr? g�p 0% l�i su?t - Th? t?c nhanh g?n', 'promo-banner-2.jpg', '#', 'long_banner', 1);

-- 6. B�i vi?t tin t?c (posts)
INSERT INTO posts (title, slug, summary, content, image, category_slug, post_type, author_name, status, is_featured, published_at, created_at) VALUES 
('��nh gi� chi ti?t NVIDIA RTX 50 Series: Bu?c nh?y v?t hi?u nang AI', 'nvidia-rtx-50-series-danh-gia', 'Nh?ng th�ng tin m?i nh?t v? hi?u nang, gi� b�n v� ng�y ra m?t card d? h?a th? h? ti?p theo c?a NVIDIA.', 'Ki?n tr�c GPU m?i c?a NVIDIA mang l?i bang th�ng si�u cao, t�ch h?p Tensor Core th? h? th? 5 gi�p t?i uu h�a m?nh m? c�c thu?t to�n tr� tu? nh�n t?o v� x? l� d? h?a th?i gian th?c. �?i v?i game th? chuy�n nghi?p, c�ng ngh? DLSS 4 v� Frame Generation m?i s? n�ng t?c d? khung h�nh vu?t tr?i ? d? ph�n gi?i 4K m� kh�ng l�m suy gi?m ch?t lu?ng h�nh ?nh.', 'news-rtx-50.jpg', 'pc-linh-kien', 'news', '�?i ngu TechPilot', 'published', 1, NOW() - INTERVAL 1 DAY, NOW() - INTERVAL 1 DAY),
('Intel Core Ultra 9: CPU th? h? m?i d�nh cho c�c d�ng laptop m?ng nh? 2026', 'intel-core-ultra-9-laptop-thin-light', 'D�ng chip s? h?u NPU chuy�n bi?t ph?c v? c�c t�c v? tr� tu? nh�n t?o tr?c ti?p tr�n thi?t b?.', 'D�ng vi x? l� Intel Core Ultra 9 s? h?u ki?n tr�c l�i lai ti�n ti?n k?t h?p c�ng NPU chuy�n d?ng d? x? l� tr?c ti?p c�c m� h�nh AI ngay tr�n thi?t b? m� kh�ng ph? thu?c v�o k?t n?i d�m m�y. Card d? h?a Arc t�ch h?p s?n s�ng thay th? card r?i ph�n kh�c ph? th�ng, cho ph�p ngu?i d�ng bi�n t?p video 4K mu?t m�.', 'news-intel-ultra.jpg', 'laptop', 'news', '�?i ngu TechPilot', 'published', 0, NOW() - INTERVAL 3 DAY, NOW() - INTERVAL 3 DAY),
('Hu?ng d?n t? build PC gaming 20 tri?u chi?n t?t m?i game esport nam nay', 'huong-dan-build-pc-20-trieu', 'L?a ch?n linh ki?n chu?n nh?t, t?i uu ng�n s�ch t?t nh?t tr�nh ngh?n c? chai.', 'T? x�y d?ng m?t c?u h�nh PC gaming t?m gi� 20 tri?u d?ng d�i h?i ngu?i d�ng ph?i ph�n b? ng�n s�ch h?p l� gi?a CPU v� GPU. K?t h?p b? vi x? l� Intel Core i5 ho?c AMD Ryzen 5 c�ng bo m?ch ch? B760/B650 v� card m�n h�nh RTX 4060 8GB s? mang l?i s? c�n b?ng ho�n h?o.', 'news-build-pc.jpg', 'pc-gaming', 'guide', 'Ban bi�n t?p TechPilot', 'published', 0, NOW() - INTERVAL 5 DAY, NOW() - INTERVAL 5 DAY),
('Top 5 chu?t gaming kh�ng d�y si�u nh? d�ng mua nh?t th?i di?m hi?n t?i', 'top-5-chuot-gaming-khong-day-sieu-nhe', '�i?m danh c�c guong m?t v�ng t? Razer, Logitech, Corsair du?c game th? chuy�n nghi?p tin d�ng.', 'Cu?c dua chu?t gaming kh�ng d�y si�u nh? du?i 60 gram dang tr? n�n n�ng hon bao gi? h?t. C�c t�n tu?i h�ng d?u nhu Razer DeathAdder V3 Pro, Logitech G Pro X Superlight 2 v� Corsair M75 Wireless d?u s? h?u c?m bi?n quang h?c d? ph�n gi?i l�n t?i 30.000 DPI c�ng t?n s? truy v?n 4000Hz.', 'news-mouse-gaming.jpg', 'gaming-gear', 'review', 'Ban bi�n t?p TechPilot', 'published', 0, NOW() - INTERVAL 7 DAY, NOW() - INTERVAL 7 DAY),
('10 m?o t?i uu Windows 11 gi�p tang t?c m�y t�nh v� choi game mu?t hon 2026', '10-meo-toi-uu-windows-11-tang-toc-may-tinh-choi-game', 'Hu?ng d?n chi ti?t c�c bu?c t?i uu h�a h? di?u h�nh Windows 11 gi�p gi?m ng?n RAM, t?t ?ng d?ng ch?y ng?m v� tang FPS khi choi game.', 'T?t b?t Startup Apps, b?t Game Mode, t?i uu Virtual Memory, v� hi?u h�a Telemetry v� c�c m?o d?n d?p b? nh? d?m gi�p m�y t�nh c?a b?n v?n h�nh tron tru nh?t. Vi?c t�y ch?nh d�ng c�c thi?t l?p h? th?ng gi�p gi?m d? tr? d?u v�o v� tang th�m t? 10-15% khung h�nh khi choi c�c t?a game eSport n?ng.', 'https://images.unsplash.com/photo-1629654297299-c8506221ca97?q=80&w=800&auto=format&fit=crop', 'pc-linh-kien', 'howto', 'Ban bi�n t?p TechPilot', 'published', 0, NOW() - INTERVAL 2 DAY, NOW() - INTERVAL 2 DAY),
('So s�nh Intel Core i9-14900K vs AMD Ryzen 7 7800X3D: ��u l� Vua Gaming 2026?', 'so-sanh-intel-i9-14900k-vs-amd-ryzen-7-7800x3d-vua-gaming', 'So s�nh to�n di?n v? hi?u nang choi game, di?n nang ti�u th?, nhi?t d? v� hi?u qu? chi ph� gi?a hai vi x? l� cao c?p h�ng d?u hi?n nay.', 'AMD Ryzen 7 7800X3D vu?t tr?i trong h?u h?t t?a game eSport v� AAA nh? b? nh? d?m 3D V-Cache dung lu?ng c?c d?i, trong khi Intel Core i9-14900K l?i da nang hon cho c�ng vi?c d? h?a n?ng v� render video 8K nh? s? lu?ng nh�n lu?ng �p d?o. B�i vi?t ph�n t�ch chi ti?t hi?u nang v� lu?ng di?n ti�u th?.', 'https://images.unsplash.com/photo-1591799264318-7e6ef8ddb7ea?q=80&w=800&auto=format&fit=crop', 'pc-gaming', 'comparison', '�?i ngu TechPilot', 'published', 0, NOW() - INTERVAL 4 DAY, NOW() - INTERVAL 4 DAY),
('��nh gi� chi ti?t Laptop Gaming ASUS ROG Zephyrus G16 2026', 'danh-gia-asus-rog-zephyrus-g16-2026', 'Laptop gaming si�u m?ng nh? v?i m�n h�nh OLED 240Hz v� v? nh�m nguy�n kh?i CNC tinh x?o.', 'ASUS ROG Zephyrus G16 d?i di?n cho d?nh cao thi?t k? laptop gaming th? h? m?i v?i v? nh�m CNC s?c s?o, m�n h�nh ROG Nebula OLED 240Hz s?c n�t v� h? th?ng t?n nhi?t kh� d?ng h?c t�n ti?n. M�y trang b? vi x? l� Intel Core Ultra 9 c�ng GPU RTX 4080 cho hi?u nang chi?n game AAA ?n tu?ng trong th�n h�nh m?ng ch? 1.49cm.', 'rog-zephyrus.jpg', 'laptop', 'review', 'TechPilot Reviewer', 'published', 0, NOW() - INTERVAL 6 DAY, NOW() - INTERVAL 6 DAY),
('Kh�m ph� ki?n tr�c Copilot+ PC v� s?c m?nh c?a NPU tr�n vi x? l� th? h? m?i', 'kham-pha-kien-truc-copilot-plus-pc', 'T?ng quan v? k? nguy�n m�y t�nh t�ch h?p tr� tu? nh�n t?o v� nh?ng ti?n �ch d?t ph� cho ngu?i d�ng.', 'K? nguy�n Copilot+ PC d�nh d?u bu?c chuy?n m�nh l?n c?a ng�nh c�ng nghi?p m�y t�nh c� nh�n. V?i s? g�p m?t c?a c�c b? x? l� NPU d?t chu?n tr�n 40 TOPS t? Qualcomm, Intel v� AMD, ngu?i d�ng c� th? th?c hi?n t�nh nang Recall, Cocreator v� d?ch thu?t tr?c ti?p Live Captions theo th?i gian th?c m� ho�n to�n kh�ng lo b?o m?t d? li?u.', 'banner-rtx-bg.jpg', 'ai-cong-nghe-moi', 'news', 'Chuy�n gia TechPilot', 'published', 0, NOW() - INTERVAL 8 DAY, NOW() - INTERVAL 8 DAY);

UPDATE posts SET status = 'published', published_at = COALESCE(published_at, created_at, NOW());
UPDATE posts SET is_featured = 1 WHERE slug = 'nvidia-rtx-50-series-danh-gia';

-- 7. ��nh gi� m?u (reviews)
INSERT INTO reviews (product_id, user_id, reviewer_name, rating, comment) VALUES 
(1, NULL, 'Nguy?n Ho�ng Nam', 5.0, 'S?n ph?m ch�nh h�ng, m�n h�nh OLED si�u d?p, choi game mu?t m� c?c k? th�ch! Giao h�ng nhanh.'),
(1, NULL, 'Tr?n Minh �?c', 4.5, 'Thi?t k? m?ng nh? ti?n mang di l�m, hi?u nang i9 si�u m?nh nhung m�y hoi ?m l�n khi choi game n?ng l�u.'),
(13, NULL, 'L� Minh Qu�n', 5.0, 'M�n h�nh 120Hz mu?t, ph�m b?m nh?y, Ally X d�ng su?ng hon b?n cu nhi?u, pin tr�u hon h?n.'),
(14, NULL, 'Phan M? Linh', 5.0, 'M�y r�p r?t ch?c ch?n, ch?y �m v� chi?n game AAA c?c mu?t.'),
(15, NULL, 'Ho�ng Qu?c B?o', 5.0, '�m thanh v�m nghe ti?ng ch�n d?ch trong game r?t r�, mic l?c �m t?t.'),
(16, NULL, 'Vu Phuong Anh', 4.0, 'M�n h�nh cong d?p, t?n s? qu�t 165Hz choi game mu?t, tuy nhi�n ch�n d? hoi to chi?m di?n t�ch b�n.');

-- 8. M� gi?m gi� (coupons)
INSERT INTO coupons (code, discount_value, type, max_discount, min_order_value, start_date, end_date) VALUES 
('TECHPILOT100', 100000, 'fixed', 100000, 2000000, NOW() - INTERVAL 1 DAY, NOW() + INTERVAL 30 DAY),
('GIAM5PHANTRAM', 5, 'percent', 500000, 5000000, NOW() - INTERVAL 1 DAY, NOW() + INTERVAL 30 DAY);

-- 9. T�i kho?n demo
INSERT INTO users (full_name, email, phone, password, role, status) VALUES
('Nguy?n Ph?m Th�nh Trung', 'ntrungz0704@gmail.com', '0987654321', '$2y$12/3H.FeZP6.ppxtRtqz/StiY0d0BaTUxX3xdB2', 'admin', 'active'),
('Qu?n tr? TechPilot', 'admin@techpilot.vn', '0999888777', '$2y$12/Fg6FPB7u.fG/tFa.Sbhg3Vd.koMKY4QYi6io7wa', 'admin', 'active'),
('Kh�ch h�ng Demo', 'customer@gmail.com', '0123456789', '$2y$12.0mJ2zGA.Y5nTTjCnkfLWXfS6if/6WOS', 'customer', 'active');

-- 10. Flash Sale
INSERT INTO flash_sales (id, title, slug, start_time, end_time, status) VALUES
(1, 'Flash Sale C�ng Ngh?', 'flash-sale-cong-nghe', NOW() - INTERVAL 1 HOUR, NOW() + INTERVAL 2 HOUR, 'active');

INSERT INTO flash_sale_items (flash_sale_id, product_id, discount_price, allocation_quantity, sold_quantity, limit_per_user) VALUES
(1, 1, 2100000.00, 10, 2, 1),
(1, 4, 3100000.00, 15, 5, 1),
(1, 10, 4100000.00, 12, 1, 1),
(1, 13, 18990000.00, 10, 3, 1),
(1, 14, 25990000.00, 15, 6, 1),
(1, 15, 3290000.00, 12, 4, 1);

-- 11. C?p nh?t tr?ng th�i ki?m d?nh cho s?n ph?m d� seed
UPDATE products SET verification_status = 'verified', verification_score = 100, verified_at = COALESCE(verified_at, NOW()) WHERE status = 'active';


SET FOREIGN_KEY_CHECKS=1;

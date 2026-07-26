-- TechPilot Catalog Normalization Migration
-- Migration Date: 2026-07-26

SET FOREIGN_KEY_CHECKS = 0;

-- Reset existing category slugs temporarily to prevent UNIQUE key collisions during ID re-assignment
UPDATE `categories` SET `slug` = CONCAT('temp-', `id`, '-', `slug`);

-- Insert or Update 20 Standard Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `status`) VALUES
(1, 'Laptop', 'laptop', 'Laptop chính hãng, Laptop Gaming & Văn Phòng', 'assets/images/categories/laptop.png', 'active'),
(2, 'PC', 'pc', 'Máy tính để bàn, PC đồng bộ & PC Build sẵn', 'assets/images/categories/pc.png', 'active'),
(3, 'Màn hình', 'monitor', 'Màn hình máy tính 144Hz, 240Hz, 4K, OLED', 'assets/images/categories/monitor.png', 'active'),
(4, 'Mainboard', 'mainboard', 'Bo mạch chủ Intel & AMD chính hãng', 'assets/images/categories/mainboard.png', 'active'),
(5, 'CPU', 'cpu', 'Bộ vi xử lý Intel Core & AMD Ryzen', 'assets/images/categories/cpu.png', 'active'),
(6, 'VGA', 'vga', 'Card màn hình NVIDIA RTX & AMD Radeon', 'assets/images/categories/vga.png', 'active'),
(7, 'RAM', 'ram', 'Bộ nhớ RAM PC & Laptop DDR4, DDR5', 'assets/images/categories/ram.png', 'active'),
(8, 'Ổ cứng', 'storage', 'Ổ cứng SSD NVMe, SSD SATA & HDD dung lượng lớn', 'assets/images/categories/storage.png', 'active'),
(9, 'Case', 'case', 'Vỏ máy tính Gaming, bể kính, mATX, ATX', 'assets/images/categories/case.png', 'active'),
(10, 'Tản nhiệt', 'cooling', 'Tản nhiệt khí, Tản nhiệt nước AIO, Fan RGB', 'assets/images/categories/cooling.png', 'active'),
(11, 'Nguồn', 'psu', 'Nguồn máy tính PSU 80 Plus Gold, Bronze', 'assets/images/categories/psu.png', 'active'),
(12, 'Bàn phím', 'keyboard', 'Bàn phím cơ, bàn phím không dây, Hot-swap', 'assets/images/categories/keyboard.png', 'active'),
(13, 'Chuột', 'mouse', 'Chuột Gaming, chuột không dây siêu nhẹ', 'assets/images/categories/mouse.png', 'active'),
(14, 'Ghế', 'chair', 'Ghế Gaming, ghế công thái học Ergonomic', 'assets/images/categories/chair.png', 'active'),
(15, 'Tai nghe', 'headset', 'Tai nghe Gaming 7.1, tai nghe không dây', 'assets/images/categories/headset.png', 'active'),
(16, 'Loa', 'speaker', 'Loa vi tính, loa Bluetooth công suất lớn', 'assets/images/categories/speaker.png', 'active'),
(17, 'Console', 'console', 'Máy chơi game PS5, Nintendo Switch, Handheld PC', 'assets/images/categories/console.png', 'active'),
(18, 'Phụ kiện', 'accessories', 'Dây cáp, Hub chuyển đổi, Lót chuột, Giá đỡ', 'assets/images/categories/accessories.png', 'active'),
(19, 'Thiết bị văn phòng', 'office-equipment', 'Máy in, Máy chiếu, Webcam, Máy quét', 'assets/images/categories/office-equipment.png', 'active'),
(20, 'Sạc dự phòng', 'power-bank', 'Pin sạc dự phòng sạc nhanh 20W, 65W, 100W', 'assets/images/categories/power-bank.png', 'active')
ON DUPLICATE KEY UPDATE 
`name` = VALUES(`name`),
`slug` = VALUES(`slug`),
`description` = VALUES(`description`),
`image` = VALUES(`image`),
`status` = 'active';

-- Deactivate any categories with ID > 20
UPDATE `categories` SET `status` = 'inactive' WHERE `id` > 20;

SET FOREIGN_KEY_CHECKS = 1;

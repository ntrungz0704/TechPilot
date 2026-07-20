<?php
require_once ROOT_PATH . '/config/database.php';

class Product
{
    private ?PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** Bộ sản phẩm đầy đủ phủ khắp các danh mục storefront khi CSDL chưa có dữ liệu */
    public static function getSampleProducts(): array
    {
        return [
            // ===== 1. LAPTOP GAMING (category_slug: laptop-gaming) =====
            [
                'id' => 1,
                'name' => 'Laptop Gaming ASUS ROG Zephyrus G16 GU605 (Intel Core Ultra 9 185H / RTX 4070 / 32GB / 1TB SSD / OLED 240Hz)',
                'slug' => 'asus-rog-zephyrus-g16-2024',
                'price' => 64990000,
                'sale_price' => 59990000,
                'image' => 'rog-zephyrus.jpg',
                'category_id' => 1,
                'category_slug' => 'laptop-gaming',
                'category_name' => 'Laptop Gaming',
                'brand_id' => 1,
                'brand_name' => 'ASUS',
                'stock' => 15,
                'is_flash_sale' => 1,
                'is_best_seller' => 1,
                'rating' => 4.9,
                'rating_count' => 32,
                'status' => 'active',
                'description' => 'Laptop Gaming cao cấp ASUS ROG Zephyrus G16 màn hình 16 inch OLED 240Hz siêu sắc nét, mỏng nhẹ đẳng cấp.',
            ],
            [
                'id' => 2,
                'name' => 'Laptop Gaming MSI Vector GP68 HX 13VI (Core i9-13900H / RTX 4090 / 32GB / 1TB / QHD+ 240Hz)',
                'slug' => 'msi-vector-gp68-hx',
                'price' => 79990000,
                'sale_price' => 74990000,
                'image' => 'msi-vector.jpg',
                'category_id' => 1,
                'category_slug' => 'laptop-gaming',
                'category_name' => 'Laptop Gaming',
                'brand_id' => 2,
                'brand_name' => 'MSI',
                'stock' => 10,
                'is_flash_sale' => 1,
                'is_best_seller' => 1,
                'rating' => 4.9,
                'rating_count' => 28,
                'status' => 'active',
                'description' => 'Siêu phẩm laptop gaming MSI trang bị RTX 4090 tản nhiệt Cooler Boost 5 mát mẻ.',
            ],
            [
                'id' => 3,
                'name' => 'Laptop Gaming Lenovo Legion Pro 5 16IRX9 (Core i7-14650HX / RTX 4060 / 16GB / 1TB / WQXGA 240Hz)',
                'slug' => 'lenovo-legion-pro-5-16irx9',
                'price' => 42990000,
                'sale_price' => 39990000,
                'image' => 'lenovo-legion.jpg',
                'category_id' => 1,
                'category_slug' => 'laptop-gaming',
                'category_name' => 'Laptop Gaming',
                'brand_id' => 6,
                'brand_name' => 'Lenovo',
                'stock' => 18,
                'is_flash_sale' => 1,
                'is_best_seller' => 1,
                'rating' => 4.8,
                'rating_count' => 45,
                'status' => 'active',
                'description' => 'Laptop gaming quốc dân Lenovo Legion Pro 5 thiết kế bền bỉ, màn hình 240Hz chuẩn màu.',
            ],
            [
                'id' => 4,
                'name' => 'Laptop Gaming Dell Gaming G16 7630 (Core i9-13900HX / RTX 4070 / 16GB / 1TB / QHD+ 240Hz)',
                'slug' => 'dell-gaming-g16-7630',
                'price' => 48990000,
                'sale_price' => 44990000,
                'image' => 'dell-g16.jpg',
                'category_id' => 1,
                'category_slug' => 'laptop-gaming',
                'category_name' => 'Laptop Gaming',
                'brand_id' => 4,
                'brand_name' => 'DELL',
                'stock' => 12,
                'is_flash_sale' => 0,
                'is_best_seller' => 1,
                'rating' => 4.7,
                'rating_count' => 19,
                'status' => 'active',
                'description' => 'Dell Gaming G16 hiệu năng đỉnh cao, thiết kế hầm hố hiện đại.',
            ],
            [
                'id' => 5,
                'name' => 'Laptop Gaming HP Omen 16 (Core i7-13700HX / RTX 4060 / 16GB / 512GB / QHD 240Hz)',
                'slug' => 'hp-omen-16-2023',
                'price' => 38990000,
                'sale_price' => 35990000,
                'image' => 'hp-omen.jpg',
                'category_id' => 1,
                'category_slug' => 'laptop-gaming',
                'category_name' => 'Laptop Gaming',
                'brand_id' => 5,
                'brand_name' => 'HP',
                'stock' => 14,
                'is_flash_sale' => 0,
                'is_best_seller' => 1,
                'rating' => 4.8,
                'rating_count' => 22,
                'status' => 'active',
                'description' => 'Dòng Laptop Gaming cao cấp HP Omen thiết kế tối giản, bàn phím RGB 4 vùng.',
            ],
            [
                'id' => 6,
                'name' => 'Laptop Gaming Acer Predator Helios Neo 16 (Core i7-14700HX / RTX 4060 / 16GB / 512GB / 165Hz)',
                'slug' => 'acer-predator-helios-neo-16',
                'price' => 36990000,
                'sale_price' => 33990000,
                'image' => 'acer-predator.jpg',
                'category_id' => 1,
                'category_slug' => 'laptop-gaming',
                'category_name' => 'Laptop Gaming',
                'brand_id' => 14,
                'brand_name' => 'Acer',
                'stock' => 20,
                'is_flash_sale' => 0,
                'is_best_seller' => 1,
                'rating' => 4.9,
                'rating_count' => 30,
                'status' => 'active',
                'description' => 'Acer Predator Helios Neo 16 trang bị tản nhiệt quạt kim loại AeroBlade 3D thế hệ 5.',
            ],
            [
                'id' => 7,
                'name' => 'Máy chơi game cầm tay ASUS ROG Ally X (AMD Ryzen Z1 Extreme / 24GB RAM / 1TB SSD / 120Hz)',
                'slug' => 'asus-rog-ally-x-2024',
                'price' => 24990000,
                'sale_price' => 23490000,
                'image' => 'rog-ally-x.jpg',
                'category_id' => 1,
                'category_slug' => 'laptop-gaming',
                'category_name' => 'Laptop Gaming',
                'brand_id' => 1,
                'brand_name' => 'ASUS',
                'stock' => 25,
                'is_flash_sale' => 1,
                'is_best_seller' => 1,
                'rating' => 5.0,
                'rating_count' => 40,
                'status' => 'active',
                'description' => 'Máy chơi game cầm tay Windows mạnh nhất thế giới với viên pin 80Wh ấn tượng.',
            ],

            // ===== 2. LAPTOP VĂN PHÒNG (category_slug: laptop-van-phong) =====
            [
                'id' => 8,
                'name' => 'Apple MacBook Pro 16 inch M3 Max (36GB Unified Memory / 1TB SSD) - Space Black',
                'slug' => 'apple-macbook-pro-16-m3-max',
                'price' => 89990000,
                'sale_price' => 84990000,
                'image' => 'macbook-pro.jpg',
                'category_id' => 2,
                'category_slug' => 'laptop-van-phong',
                'category_name' => 'Laptop Văn Phòng',
                'brand_id' => 12,
                'brand_name' => 'Apple',
                'stock' => 8,
                'is_flash_sale' => 1,
                'is_best_seller' => 1,
                'rating' => 5.0,
                'rating_count' => 60,
                'status' => 'active',
                'description' => 'Quái vật hiệu năng dành cho lập trình viên và nhà làm phim chuyên nghiệp.',
            ],
            [
                'id' => 9,
                'name' => 'Apple MacBook Air 15 inch M2 (8GB RAM / 256GB SSD) - Starlight',
                'slug' => 'apple-macbook-air-15-m2',
                'price' => 32990000,
                'sale_price' => 28990000,
                'image' => 'macbook-air.jpg',
                'category_id' => 2,
                'category_slug' => 'laptop-van-phong',
                'category_name' => 'Laptop Văn Phòng',
                'brand_id' => 12,
                'brand_name' => 'Apple',
                'stock' => 15,
                'is_flash_sale' => 1,
                'is_best_seller' => 1,
                'rating' => 4.9,
                'rating_count' => 52,
                'status' => 'active',
                'description' => 'MacBook Air M2 màn hình lớn 15 inch siêu mỏng, thời lượng pin 18 giờ.',
            ],
            [
                'id' => 10,
                'name' => 'Laptop Lenovo ThinkPad X1 Carbon Gen 11 (Core i7-1355U / 16GB / 512GB / Touch)',
                'slug' => 'lenovo-thinkpad-x1-carbon-gen-11',
                'price' => 46990000,
                'sale_price' => 42990000,
                'image' => 'lenovo-thinkpad.jpg',
                'category_id' => 2,
                'category_slug' => 'laptop-van-phong',
                'category_name' => 'Laptop Văn Phòng',
                'brand_id' => 6,
                'brand_name' => 'Lenovo',
                'stock' => 10,
                'is_flash_sale' => 0,
                'is_best_seller' => 1,
                'rating' => 4.9,
                'rating_count' => 33,
                'status' => 'active',
                'description' => 'Biểu tượng doanh nhân ThinkPad X1 Carbon vỏ sợi carbon siêu nhẹ 1.12kg.',
            ],
            [
                'id' => 11,
                'name' => 'Laptop HP Pavilion 15 eg3095TU (Core i5-1335U / 16GB / 512GB / FHD IPS)',
                'slug' => 'hp-pavilion-15-eg3095tu',
                'price' => 17990000,
                'sale_price' => 15990000,
                'image' => 'hp-pavilion.jpg',
                'category_id' => 2,
                'category_slug' => 'laptop-van-phong',
                'category_name' => 'Laptop Văn Phòng',
                'brand_id' => 5,
                'brand_name' => 'HP',
                'stock' => 35,
                'is_flash_sale' => 0,
                'is_best_seller' => 1,
                'rating' => 4.7,
                'rating_count' => 25,
                'status' => 'active',
                'description' => 'Laptop văn phòng HP thiết kế vỏ nhôm thời trang, âm thanh B&O cao cấp.',
            ],
            [
                'id' => 12,
                'name' => 'Laptop ASUS Zenbook 14 OLED UX3405 (Intel Core Ultra 7 155H / 32GB / 1TB / 3K OLED 120Hz)',
                'slug' => 'asus-zenbook-14-oled-ux3405',
                'price' => 31990000,
                'sale_price' => 28990000,
                'image' => 'laptop1.png',
                'category_id' => 2,
                'category_slug' => 'laptop-van-phong',
                'category_name' => 'Laptop Văn Phòng',
                'brand_id' => 1,
                'brand_name' => 'ASUS',
                'stock' => 20,
                'is_flash_sale' => 1,
                'is_best_seller' => 1,
                'rating' => 4.9,
                'rating_count' => 41,
                'status' => 'active',
                'description' => 'Zenbook 14 OLED tích hợp trí tuệ nhân tạo NPU AI Boost tiên phong.',
            ],
            [
                'id' => 13,
                'name' => 'Laptop Dell XPS 13 Plus 9320 (Core i7-1360P / 16GB / 512GB / 3.5K OLED Touch)',
                'slug' => 'dell-xps-13-plus-9320',
                'price' => 49990000,
                'sale_price' => 45990000,
                'image' => 'laptop2.png',
                'category_id' => 2,
                'category_slug' => 'laptop-van-phong',
                'category_name' => 'Laptop Văn Phòng',
                'brand_id' => 4,
                'brand_name' => 'DELL',
                'stock' => 9,
                'is_flash_sale' => 0,
                'is_best_seller' => 1,
                'rating' => 4.8,
                'rating_count' => 18,
                'status' => 'active',
                'description' => 'Kiệt tác công nghệ Dell XPS 13 Plus với bàn phím tràn viền và touchpad vô hình.',
            ],

            // ===== 3. PC BÁN SẴN (category_slug: pc-build-san) =====
            [
                'id' => 14,
                'name' => 'PC Gaming TechPilot Ultra (Core i9-14900K / RTX 4090 24GB / 64GB DDR5 / 2TB SSD / AIO 360)',
                'slug' => 'pc-gaming-techpilot-ultra-rtx4090',
                'price' => 89990000,
                'sale_price' => 83990000,
                'image' => 'gaming-pc-ultra.jpg',
                'category_id' => 3,
                'category_slug' => 'pc-build-san',
                'category_name' => 'PC Bán Sẵn',
                'brand_id' => 15,
                'brand_name' => 'TechPilot',
                'stock' => 5,
                'is_flash_sale' => 1,
                'is_best_seller' => 1,
                'rating' => 5.0,
                'rating_count' => 15,
                'status' => 'active',
                'description' => 'Cấu hình PC Gaming siêu cấp đáp ứng mượt mà trải nghiệm chơi game 4K Ray Tracing đỉnh cao.',
            ],
            [
                'id' => 15,
                'name' => 'PC Gaming TechPilot Advanced (Core i7-14700K / RTX 4070 Ti Super 16GB / 32GB DDR5 / 1TB SSD)',
                'slug' => 'pc-gaming-techpilot-advanced-rtx4070tisuper',
                'price' => 46990000,
                'sale_price' => 42990000,
                'image' => 'gaming-pc-mid.jpg',
                'category_id' => 3,
                'category_slug' => 'pc-build-san',
                'category_name' => 'PC Bán Sẵn',
                'brand_id' => 15,
                'brand_name' => 'TechPilot',
                'stock' => 12,
                'is_flash_sale' => 1,
                'is_best_seller' => 1,
                'rating' => 4.9,
                'rating_count' => 28,
                'status' => 'active',
                'description' => 'Bộ máy tính PC Gaming lắp sẵn chiến mượt game AAA và thiết kế đồ họa 3D.',
            ],
            [
                'id' => 16,
                'name' => 'PC Gaming TechPilot Starter (Core i5-13400F / RTX 4060 8GB / 16GB RAM / 512GB SSD)',
                'slug' => 'pc-gaming-techpilot-starter-rtx4060',
                'price' => 21990000,
                'sale_price' => 19990000,
                'image' => 'pc1.png',
                'category_id' => 3,
                'category_slug' => 'pc-build-san',
                'category_name' => 'PC Bán Sẵn',
                'brand_id' => 15,
                'brand_name' => 'TechPilot',
                'stock' => 25,
                'is_flash_sale' => 1,
                'is_best_seller' => 1,
                'rating' => 4.8,
                'rating_count' => 42,
                'status' => 'active',
                'description' => 'PC Gaming phổ thông cân mọi tựa game Esports Full HD mượt mà.',
            ],
            [
                'id' => 17,
                'name' => 'PC Gaming TechPilot Esports (AMD Ryzen 7 7800X3D / RTX 4070 Super 12GB / 32GB DDR5 / 1TB SSD)',
                'slug' => 'pc-gaming-techpilot-esports-7800x3d',
                'price' => 41990000,
                'sale_price' => 38990000,
                'image' => 'pc2.png',
                'category_id' => 3,
                'category_slug' => 'pc-build-san',
                'category_name' => 'PC Bán Sẵn',
                'brand_id' => 15,
                'brand_name' => 'TechPilot',
                'stock' => 15,
                'is_flash_sale' => 0,
                'is_best_seller' => 1,
                'rating' => 4.9,
                'rating_count' => 35,
                'status' => 'active',
                'description' => 'Vũ khí chuyên nghiệp cho game thủ Esports với CPU Ryzen 7800X3D số 1 thế giới.',
            ],
            [
                'id' => 18,
                'name' => 'PC Văn Phòng TechPilot Office Intel (Core i5-12400 / 16GB RAM / 512GB NVMe / PSU 500W)',
                'slug' => 'pc-van-phong-techpilot-intel-i5',
                'price' => 10990000,
                'sale_price' => 9490000,
                'image' => 'office-pc-intel.jpg',
                'category_id' => 3,
                'category_slug' => 'pc-build-san',
                'category_name' => 'PC Bán Sẵn',
                'brand_id' => 15,
                'brand_name' => 'TechPilot',
                'stock' => 30,
                'is_flash_sale' => 0,
                'is_best_seller' => 1,
                'rating' => 4.7,
                'rating_count' => 20,
                'status' => 'active',
                'description' => 'Bộ máy tính văn phòng nhỏ gọn, xử lý tác vụ Excel nặng và đồ họa 2D nhanh chóng.',
            ],
            [
                'id' => 19,
                'name' => 'PC Văn Phòng TechPilot Office AMD (Ryzen 5 5600G / 16GB RAM / 512GB NVMe / PSU 450W)',
                'slug' => 'pc-van-phong-techpilot-amd-5600g',
                'price' => 9990000,
                'sale_price' => 8790000,
                'image' => 'office-pc-amd.jpg',
                'category_id' => 3,
                'category_slug' => 'pc-build-san',
                'category_name' => 'PC Bán Sẵn',
                'brand_id' => 15,
                'brand_name' => 'TechPilot',
                'stock' => 28,
                'is_flash_sale' => 0,
                'is_best_seller' => 1,
                'rating' => 4.8,
                'rating_count' => 16,
                'status' => 'active',
                'description' => 'PC văn phòng AMD giá cực tốt tích hợp đồ họa Radeon Vega chơi được game nhẹ.',
            ],

            // ===== 4. PC LINH KIỆN (category_slug: pc-linh-kien) =====
            [
                'id' => 20,
                'name' => 'Card màn hình GIGABYTE GeForce RTX 4070 SUPER WINDFORCE OC 12G',
                'slug' => 'vga-gigabyte-rtx-4070-super-windforce-oc-12g',
                'price' => 18490000,
                'sale_price' => 16990000,
                'image' => 'rtx-4070-super.jpg',
                'category_id' => 4,
                'category_slug' => 'pc-linh-kien',
                'category_name' => 'Linh Kiện PC',
                'brand_id' => 3,
                'brand_name' => 'GIGABYTE',
                'stock' => 14,
                'is_flash_sale' => 1,
                'is_best_seller' => 1,
                'rating' => 4.9,
                'rating_count' => 25,
                'status' => 'active',
                'description' => 'Card đồ họa VGA RTX 4070 Super kiến trúc Ada Lovelace hiệu năng cực đại.',
            ],
            [
                'id' => 21,
                'name' => 'Bộ vi xử lý Intel Core i5-12400F (Up To 4.4GHz, 6 Nhân 12 Luồng, LGA 1700)',
                'slug' => 'cpu-intel-core-i5-12400f',
                'price' => 3490000,
                'sale_price' => 3190000,
                'image' => 'core-i5-12400f.jpg',
                'category_id' => 4,
                'category_slug' => 'pc-linh-kien',
                'category_name' => 'Linh Kiện PC',
                'brand_id' => 9,
                'brand_name' => 'Intel',
                'stock' => 60,
                'is_flash_sale' => 1,
                'is_best_seller' => 1,
                'rating' => 4.9,
                'rating_count' => 88,
                'status' => 'active',
                'description' => 'Vua CPU phân khúc phổ thông Intel Core i5-12400F.',
            ],
            [
                'id' => 22,
                'name' => 'Bộ vi xử lý AMD Ryzen 5 5600X (3.7GHz Turbo 4.6GHz, 6 Nhân 12 Luồng, Socket AM4)',
                'slug' => 'cpu-amd-ryzen-5-5600x',
                'price' => 3690000,
                'sale_price' => 3290000,
                'image' => 'ryzen-5-5600x.jpg',
                'category_id' => 4,
                'category_slug' => 'pc-linh-kien',
                'category_name' => 'Linh Kiện PC',
                'brand_id' => 10,
                'brand_name' => 'AMD',
                'stock' => 40,
                'is_flash_sale' => 0,
                'is_best_seller' => 1,
                'rating' => 4.8,
                'rating_count' => 50,
                'status' => 'active',
                'description' => 'CPU AMD Ryzen 5 5600X kiến trúc Zen 3 tối ưu hóa cho gaming.',
            ],
            [
                'id' => 23,
                'name' => 'Bộ nhớ RAM Corsair Vengeance RGB 32GB (2x16GB) DDR5 6000MHz Black',
                'slug' => 'ram-corsair-vengeance-rgb-32gb-ddr5-6000mhz',
                'price' => 3490000,
                'sale_price' => 3190000,
                'image' => 'ram.jpg',
                'category_id' => 4,
                'category_slug' => 'pc-linh-kien',
                'category_name' => 'Linh Kiện PC',
                'brand_id' => 8,
                'brand_name' => 'Corsair',
                'stock' => 45,
                'is_flash_sale' => 1,
                'is_best_seller' => 1,
                'rating' => 4.9,
                'rating_count' => 35,
                'status' => 'active',
                'description' => 'RAM DDR5 Corsair Vengeance LED RGB tản nhiệt nhôm cao cấp.',
            ],
            [
                'id' => 24,
                'name' => 'Bộ nhớ RAM Kingston Fury Beast RGB 16GB (2x8GB) DDR4 3200MHz',
                'slug' => 'ram-kingston-fury-beast-rgb-16gb-ddr4-3200mhz',
                'price' => 1290000,
                'sale_price' => 1090000,
                'image' => 'ram1.jpg',
                'category_id' => 4,
                'category_slug' => 'pc-linh-kien',
                'category_name' => 'Linh Kiện PC',
                'brand_id' => 17,
                'brand_name' => 'Kingston',
                'stock' => 80,
                'is_flash_sale' => 0,
                'is_best_seller' => 1,
                'rating' => 4.8,
                'rating_count' => 60,
                'status' => 'active',
                'description' => 'RAM Kingston Fury Beast RGB DDR4 3200MHz thiết kế góc cạnh sắc nét.',
            ],
            [
                'id' => 25,
                'name' => 'Vỏ Case Corsair 4000D AIRFLOW Tempered Glass Black',
                'slug' => 'case-corsair-4000d-airflow-black',
                'price' => 2390000,
                'sale_price' => 2190000,
                'image' => 'case.png',
                'category_id' => 4,
                'category_slug' => 'pc-linh-kien',
                'category_name' => 'Linh Kiện PC',
                'brand_id' => 8,
                'brand_name' => 'Corsair',
                'stock' => 18,
                'is_flash_sale' => 0,
                'is_best_seller' => 1,
                'rating' => 4.9,
                'rating_count' => 40,
                'status' => 'active',
                'description' => 'Vỏ case thông thoáng tối đa Corsair 4000D Airflow kính cường lực.',
            ],
            [
                'id' => 26,
                'name' => 'Vỏ Case NZXT H5 Flow Black (Mid Tower)',
                'slug' => 'case-nzxt-h5-flow-black',
                'price' => 2290000,
                'sale_price' => 2090000,
                'image' => 'case1.jpg',
                'category_id' => 4,
                'category_slug' => 'pc-linh-kien',
                'category_name' => 'Linh Kiện PC',
                'brand_id' => 24,
                'brand_name' => 'NZXT',
                'stock' => 15,
                'is_flash_sale' => 0,
                'is_best_seller' => 1,
                'rating' => 4.9,
                'rating_count' => 30,
                'status' => 'active',
                'description' => 'Case NZXT H5 Flow thiết kế quạt hút đáy thổi trực tiếp vào VGA.',
            ],

            // ===== 5. GAMING GEAR (category_slug: gaming-gear) =====
            [
                'id' => 27,
                'name' => 'Chuột Gaming không dây Logitech G Pro X Superlight 2 Wireless Black 60g',
                'slug' => 'chuot-logitech-g-pro-x-superlight-2-black',
                'price' => 3790000,
                'sale_price' => 3290000,
                'image' => 'logitech-g-pro.jpg',
                'category_id' => 5,
                'category_slug' => 'gaming-gear',
                'category_name' => 'Gaming Gear',
                'brand_id' => 13,
                'brand_name' => 'Logitech',
                'stock' => 30,
                'is_flash_sale' => 1,
                'is_best_seller' => 1,
                'rating' => 5.0,
                'rating_count' => 75,
                'status' => 'active',
                'description' => 'Chuột siêu nhẹ 60g cảm biến HERO 2 độ phân giải 32.000 DPI.',
            ],
            [
                'id' => 28,
                'name' => 'Chuột Gaming không dây Razer DeathAdder V3 Pro Wireless Black 63g',
                'slug' => 'chuot-razer-deathadder-v3-pro-black',
                'price' => 3690000,
                'sale_price' => 3190000,
                'image' => 'razer-deathadder-v3.jpg',
                'category_id' => 5,
                'category_slug' => 'gaming-gear',
                'category_name' => 'Gaming Gear',
                'brand_id' => 7,
                'brand_name' => 'Razer',
                'stock' => 25,
                'is_flash_sale' => 1,
                'is_best_seller' => 1,
                'rating' => 4.9,
                'rating_count' => 50,
                'status' => 'active',
                'description' => 'Form cầm công thái học chuẩn Esports kết nối Hyperspeed siêu tốc.',
            ],
            [
                'id' => 29,
                'name' => 'Bàn phím cơ Corsair K70 RGB PRO Mechanical Gaming Keyboard Cherry MX Red',
                'slug' => 'ban-phim-co-corsair-k70-rgb-pro-red-switch',
                'price' => 4290000,
                'sale_price' => 3790000,
                'image' => 'corsair-k70.jpg',
                'category_id' => 5,
                'category_slug' => 'gaming-gear',
                'category_name' => 'Gaming Gear',
                'brand_id' => 8,
                'brand_name' => 'Corsair',
                'stock' => 20,
                'is_flash_sale' => 0,
                'is_best_seller' => 1,
                'rating' => 4.8,
                'rating_count' => 38,
                'status' => 'active',
                'description' => 'Bàn phím cơ khung nhôm phay xước cao cấp công nghệ AXON 8.000Hz.',
            ],
            [
                'id' => 30,
                'name' => 'Bàn phím cơ không dây Keychron K2 Pro Hotswap RGB Aluminium Frame',
                'slug' => 'ban-phim-co-keychron-k2-pro-hotswap-rgb',
                'price' => 2590000,
                'sale_price' => 2290000,
                'image' => 'keychron-k2.jpg',
                'category_id' => 5,
                'category_slug' => 'gaming-gear',
                'category_name' => 'Gaming Gear',
                'brand_id' => 16,
                'brand_name' => 'Keychron',
                'stock' => 40,
                'is_flash_sale' => 0,
                'is_best_seller' => 1,
                'rating' => 4.9,
                'rating_count' => 42,
                'status' => 'active',
                'description' => 'Bàn phím cơ không dây layout 75% Hotswap tương thích macOS và Windows.',
            ],
            [
                'id' => 31,
                'name' => 'Bàn phím cơ Logitech G213 Prodigy RGB Gaming Keyboard',
                'slug' => 'ban-phim-logitech-g213-prodigy-rgb',
                'price' => 1290000,
                'sale_price' => 990000,
                'image' => 'logitech-g213.jpg',
                'category_id' => 5,
                'category_slug' => 'gaming-gear',
                'category_name' => 'Gaming Gear',
                'brand_id' => 13,
                'brand_name' => 'Logitech',
                'stock' => 50,
                'is_flash_sale' => 1,
                'is_best_seller' => 1,
                'rating' => 4.7,
                'rating_count' => 65,
                'status' => 'active',
                'description' => 'Bàn phím chống nước Logitech G213 hệ thống đèn RGB LIGHTSYNC 5 vùng.',
            ],
            [
                'id' => 32,
                'name' => 'Chuột Gaming không dây Razer Viper V2 Pro Ultra-lightweight 58g',
                'slug' => 'chuot-razer-viper-v2-pro-wireless-black',
                'price' => 3590000,
                'sale_price' => 3090000,
                'image' => 'razer-viper.jpg',
                'category_id' => 5,
                'category_slug' => 'gaming-gear',
                'category_name' => 'Gaming Gear',
                'brand_id' => 7,
                'brand_name' => 'Razer',
                'stock' => 18,
                'is_flash_sale' => 0,
                'is_best_seller' => 1,
                'rating' => 4.9,
                'rating_count' => 29,
                'status' => 'active',
                'description' => 'Chuột gaming đối xứng 58g mắt đọc Focus Pro 30K cực nhạy.',
            ],

            // ===== 6. MÀN HÌNH (category_slug: man-hinh) =====
            [
                'id' => 33,
                'name' => 'Màn hình Gaming ASUS TUF Gaming VG279Q3A 27 inch IPS 180Hz 1ms Full HD',
                'slug' => 'man-hinh-asus-tuf-gaming-vg279q3a-27-inch-180hz',
                'price' => 4990000,
                'sale_price' => 4390000,
                'image' => 'monitor-asus-tuf.jpg',
                'category_id' => 6,
                'category_slug' => 'man-hinh',
                'category_name' => 'Màn Hình',
                'brand_id' => 1,
                'brand_name' => 'ASUS',
                'stock' => 25,
                'is_flash_sale' => 1,
                'is_best_seller' => 1,
                'rating' => 4.9,
                'rating_count' => 48,
                'status' => 'active',
                'description' => 'Màn hình gaming 180Hz tấm nền IPS phản hồi 1ms ELMB chống xé hình.',
            ],
            [
                'id' => 34,
                'name' => 'Màn hình Gaming LG UltraGear 27GR75Q-B 27 inch 2K IPS 165Hz 1ms HDR10',
                'slug' => 'man-hinh-lg-ultragear-27gr75q-b-27-inch-2k-165hz',
                'price' => 6790000,
                'sale_price' => 5990000,
                'image' => 'monitor-lg-27.jpg',
                'category_id' => 6,
                'category_slug' => 'man-hinh',
                'category_name' => 'Màn Hình',
                'brand_id' => 11,
                'brand_name' => 'LG',
                'stock' => 20,
                'is_flash_sale' => 1,
                'is_best_seller' => 1,
                'rating' => 4.9,
                'rating_count' => 55,
                'status' => 'active',
                'description' => 'Màn hình 2K QHD 165Hz màu sắc rực rỡ sRGB 99% hỗ trợ G-Sync Compatible.',
            ],
            [
                'id' => 35,
                'name' => 'Màn hình Cong Samsung Odyssey G7 32 inch QHD 240Hz 1ms QLED Curved 1000R',
                'slug' => 'man-hinh-samsung-odyssey-g7-32-inch-240hz',
                'price' => 14990000,
                'sale_price' => 12990000,
                'image' => 'samsung-odyssey.jpg',
                'category_id' => 6,
                'category_slug' => 'man-hinh',
                'category_name' => 'Màn Hình',
                'brand_id' => 11,
                'brand_name' => 'Samsung',
                'stock' => 10,
                'is_flash_sale' => 0,
                'is_best_seller' => 1,
                'rating' => 4.8,
                'rating_count' => 30,
                'status' => 'active',
                'description' => 'Đỉnh cao màn hình cong 1000R tần số quét 240Hz công nghệ QLED rực rỡ.',
            ],
            [
                'id' => 36,
                'name' => 'Màn hình Đồ Họa Dell UltraSharp U2723QE 27 inch 4K IPS Black USB-C Hub',
                'slug' => 'man-hinh-dell-ultrasharp-u2723qe-27-inch-4k',
                'price' => 13990000,
                'sale_price' => 12490000,
                'image' => 'display.png',
                'category_id' => 6,
                'category_slug' => 'man-hinh',
                'category_name' => 'Màn Hình',
                'brand_id' => 4,
                'brand_name' => 'DELL',
                'stock' => 15,
                'is_flash_sale' => 0,
                'is_best_seller' => 1,
                'rating' => 5.0,
                'rating_count' => 40,
                'status' => 'active',
                'description' => 'Màn hình 4K công nghệ IPS Black tương phản 2000:1 chuẩn màu thiết kế đồ họa.',
            ],
            [
                'id' => 37,
                'name' => 'Màn hình Gaming ViewSonic VX2728-2K-MHD 27 inch 2K IPS 180Hz 0.5ms',
                'slug' => 'man-hinh-viewsonic-vx2728-2k-mhd-27-inch',
                'price' => 5490000,
                'sale_price' => 4890000,
                'image' => 'display1.png',
                'category_id' => 6,
                'category_slug' => 'man-hinh',
                'category_name' => 'Màn Hình',
                'brand_id' => 14,
                'brand_name' => 'ViewSonic',
                'stock' => 22,
                'is_flash_sale' => 1,
                'is_best_seller' => 1,
                'rating' => 4.7,
                'rating_count' => 21,
                'status' => 'active',
                'description' => 'Màn hình ViewSonic 2K 180Hz tần số quét cao giá thành cực tốt.',
            ],
            [
                'id' => 38,
                'name' => 'Màn hình Gigabyte M27Q 27 inch 2K IPS 170Hz KVM Switch tích hợp',
                'slug' => 'man-hinh-gigabyte-m27q-27-inch-2k-170hz',
                'price' => 7290000,
                'sale_price' => 6490000,
                'image' => 'display2.jpg',
                'category_id' => 6,
                'category_slug' => 'man-hinh',
                'category_name' => 'Màn Hình',
                'brand_id' => 3,
                'brand_name' => 'GIGABYTE',
                'stock' => 18,
                'is_flash_sale' => 0,
                'is_best_seller' => 1,
                'rating' => 4.9,
                'rating_count' => 36,
                'status' => 'active',
                'description' => 'Màn hình gaming đầu tiên tích hợp nút chuyển mạch KVM chuyên nghiệp.',
            ],

            // ===== 7. MÁY TÍNH BỘ (category_slug: may-tinh-bo) =====
            [
                'id' => 39,
                'name' => 'Apple iMac 24 inch M3 8-core CPU 10-core GPU 8GB 256GB - Blue',
                'slug' => 'apple-imac-24-inch-m3-blue',
                'price' => 36990000,
                'sale_price' => 33990000,
                'image' => 'macbook-air.jpg',
                'category_id' => 7,
                'category_slug' => 'may-tinh-bo',
                'category_name' => 'Máy Tính Bộ',
                'brand_id' => 12,
                'brand_name' => 'Apple',
                'stock' => 10,
                'is_flash_sale' => 1,
                'is_best_seller' => 1,
                'rating' => 4.9,
                'rating_count' => 25,
                'status' => 'active',
                'description' => 'Máy tính All-in-One iMac M3 màn hình 4.5K Retina mỏng nhẹ siêu sang trọng.',
            ],
            [
                'id' => 40,
                'name' => 'Apple Mac Studio M2 Max 12-core CPU 30-core GPU 32GB 512GB',
                'slug' => 'apple-mac-studio-m2-max-32gb',
                'price' => 52990000,
                'sale_price' => 48990000,
                'image' => 'macbook-pro.jpg',
                'category_id' => 7,
                'category_slug' => 'may-tinh-bo',
                'category_name' => 'Máy Tính Bộ',
                'brand_id' => 12,
                'brand_name' => 'Apple',
                'stock' => 6,
                'is_flash_sale' => 1,
                'is_best_seller' => 1,
                'rating' => 5.0,
                'rating_count' => 30,
                'status' => 'active',
                'description' => 'Máy tính để bàn Mac Studio M2 Max cho chuyên gia đồ họa dựng phim.',
            ],
            [
                'id' => 41,
                'name' => 'Apple Mac Mini M2 (8-core CPU / 10-core GPU / 8GB RAM / 256GB SSD)',
                'slug' => 'apple-mac-mini-m2-256gb',
                'price' => 14990000,
                'sale_price' => 12990000,
                'image' => 'macbook-air.jpg',
                'category_id' => 7,
                'category_slug' => 'may-tinh-bo',
                'category_name' => 'Máy Tính Bộ',
                'brand_id' => 12,
                'brand_name' => 'Apple',
                'stock' => 20,
                'is_flash_sale' => 1,
                'is_best_seller' => 1,
                'rating' => 4.9,
                'rating_count' => 45,
                'status' => 'active',
                'description' => 'Chiếc máy tính nhỏ gọn mạnh mẽ nhất trong phân khúc dưới 15 triệu.',
            ],
            [
                'id' => 42,
                'name' => 'Máy tính đồng bộ Dell Vostro 3020 Tower (Core i5-13400 / 8GB RAM / 512GB SSD)',
                'slug' => 'dell-vostro-3020-tower-i5',
                'price' => 13990000,
                'sale_price' => 12490000,
                'image' => 'office-pc-intel.jpg',
                'category_id' => 7,
                'category_slug' => 'may-tinh-bo',
                'category_name' => 'Máy Tính Bộ',
                'brand_id' => 4,
                'brand_name' => 'DELL',
                'stock' => 25,
                'is_flash_sale' => 0,
                'is_best_seller' => 1,
                'rating' => 4.7,
                'rating_count' => 18,
                'status' => 'active',
                'description' => 'Máy tính đồng bộ Dell Vostro hoạt động 24/7 ổn định cho doanh nghiệp.',
            ],
            [
                'id' => 43,
                'name' => 'Máy tính đồng bộ HP ProTower 280 G9 (Core i7-13700 / 16GB RAM / 512GB SSD)',
                'slug' => 'hp-protower-280-g9-i7',
                'price' => 18990000,
                'sale_price' => 16990000,
                'image' => 'office-pc-amd.jpg',
                'category_id' => 7,
                'category_slug' => 'may-tinh-bo',
                'category_name' => 'Máy Tính Bộ',
                'brand_id' => 5,
                'brand_name' => 'HP',
                'stock' => 15,
                'is_flash_sale' => 0,
                'is_best_seller' => 1,
                'rating' => 4.8,
                'rating_count' => 14,
                'status' => 'active',
                'description' => 'Máy tính nguyên bộ HP trang bị chip i7 Gen 13 bảo mật phần cứng cao.',
            ],
            [
                'id' => 44,
                'name' => 'Máy tính All-in-One HP 24-df0041d 23.8 inch Full HD Touch (Core i5-1235U / 8GB / 512GB)',
                'slug' => 'hp-aio-24-df0041d-touch',
                'price' => 16990000,
                'sale_price' => 15290000,
                'image' => 'display3.jpg',
                'category_id' => 7,
                'category_slug' => 'may-tinh-bo',
                'category_name' => 'Máy Tính Bộ',
                'brand_id' => 5,
                'brand_name' => 'HP',
                'stock' => 12,
                'is_flash_sale' => 0,
                'is_best_seller' => 1,
                'rating' => 4.7,
                'rating_count' => 12,
                'status' => 'active',
                'description' => 'Máy tính tất cả trong một màn hình cảm ứng 24 inch nhỏ gọn cho văn phòng.',
            ]
        ];
    }

    /** Lấy toàn bộ sản phẩm, có thể giới hạn số lượng */
    public function getAll(int $limit = 100): array
    {
        if ($this->db !== null) {
            try {
                $stmt = $this->db->prepare('SELECT p.*, b.name as brand_name, c.name as category_name, c.slug as category_slug FROM products p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN brands b ON p.brand_id = b.id ORDER BY p.id DESC LIMIT :limit');
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->execute();
                $res = $stmt->fetchAll();
                if (!empty($res)) {
                    return $res;
                }
            } catch (Exception $e) {}
        }

        return array_slice(self::getSampleProducts(), 0, $limit);
    }

    public function getFlashSale(int $limit = 6): array
    {
        if ($this->db !== null) {
            try {
                $stmt = $this->db->prepare(
                    'SELECT p.*, COALESCE(p.sale_price, p.price * 0.85) as discount_price, p.stock as fs_stock, 
                            0 as fs_sold, COALESCE(fs.end_time, DATE_ADD(NOW(), INTERVAL 1 DAY)) as end_time 
                     FROM products p
                     LEFT JOIN flash_sales fs ON fs.status = \'active\' AND fs.start_time <= NOW() AND fs.end_time >= NOW()
                     WHERE (p.is_flash_sale = 1 OR p.sale_price IS NOT NULL)
                     ORDER BY p.id DESC LIMIT :limit'
                );
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->execute();
                $res = $stmt->fetchAll();
                if (!empty($res)) {
                    return $res;
                }
            } catch (Exception $e) {}
        }

        $samples = array_filter(self::getSampleProducts(), fn($p) => !empty($p['is_flash_sale']));
        return array_slice(array_values($samples), 0, $limit);
    }

    /** Lấy sản phẩm theo slug danh mục (hỗ trợ cả danh mục con) */
    public function getByCategorySlug(string $slug, int $limit = 6): array
    {
        if ($this->db !== null) {
            try {
                $stmt = $this->db->prepare(
                    'SELECT p.*, c.name as category_name, c.slug as category_slug, b.name as brand_name 
                     FROM products p
                     JOIN categories c ON p.category_id = c.id
                     LEFT JOIN brands b ON p.brand_id = b.id
                     WHERE c.slug = :slug OR c.parent_id IN (SELECT id FROM categories WHERE slug = :slug)
                     ORDER BY p.id DESC LIMIT :limit'
                );
                $stmt->bindValue(':slug', $slug);
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->execute();
                $res = $stmt->fetchAll();
                if (!empty($res)) {
                    return $res;
                }
            } catch (Exception $e) {}
        }

        $samples = array_filter(self::getSampleProducts(), fn($p) => ($p['category_slug'] ?? '') === $slug);
        if (empty($samples)) {
            return array_slice(self::getSampleProducts(), 0, $limit);
        }
        return array_slice(array_values($samples), 0, $limit);
    }

    /** Lấy 1 sản phẩm theo id */
    public function getById(int $id): array|false
    {
        if ($this->db !== null) {
            try {
                $stmt = $this->db->prepare(
                    'SELECT p.*, c.name as category_name, c.slug as category_slug, b.name as brand_name 
                     FROM products p 
                     LEFT JOIN categories c ON p.category_id = c.id 
                     LEFT JOIN brands b ON p.brand_id = b.id 
                     WHERE p.id = :id LIMIT 1'
                );
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $res = $stmt->fetch();
                if ($res) {
                    return $res;
                }
            } catch (Exception $e) {}
        }

        foreach (self::getSampleProducts() as $p) {
            if ($p['id'] == $id) {
                return $p;
            }
        }
        return self::getSampleProducts()[0] ?? false;
    }

    /** Lấy 1 sản phẩm theo slug (trang chi tiết) */
    public function getBySlug(string $slug): array|false
    {
        if ($this->db !== null) {
            try {
                $stmt = $this->db->prepare(
                    'SELECT p.*, c.name as category_name, c.slug as category_slug, b.name as brand_name 
                     FROM products p 
                     LEFT JOIN categories c ON p.category_id = c.id 
                     LEFT JOIN brands b ON p.brand_id = b.id 
                     WHERE p.slug = :slug LIMIT 1'
                );
                $stmt->bindValue(':slug', $slug);
                $stmt->execute();
                $res = $stmt->fetch();
                if ($res) {
                    return $res;
                }
            } catch (Exception $e) {}
        }

        foreach (self::getSampleProducts() as $p) {
            if ($p['slug'] === $slug) {
                return $p;
            }
        }
        return self::getSampleProducts()[0] ?? false;
    }

    /** Lấy danh sách ảnh phụ từ product_images */
    public function getProductImages(int $productId): array
    {
        if ($this->db !== null) {
            try {
                $stmt = $this->db->prepare('SELECT * FROM product_images WHERE product_id = :product_id');
                $stmt->execute([':product_id' => $productId]);
                return $stmt->fetchAll();
            } catch (Exception $e) {}
        }
        return [];
    }

    /** Lấy sản phẩm liên quan (cùng danh mục, khác id hiện tại) */
    public function getRelated(int $categoryId, int $excludeId, int $limit = 4): array
    {
        if ($this->db !== null) {
            try {
                $stmt = $this->db->prepare(
                    'SELECT p.*, b.name as brand_name 
                     FROM products p
                     LEFT JOIN brands b ON p.brand_id = b.id
                     WHERE p.category_id = :cat AND p.id != :id 
                     ORDER BY RAND() LIMIT :limit'
                );
                $stmt->bindValue(':cat', $categoryId, PDO::PARAM_INT);
                $stmt->bindValue(':id', $excludeId, PDO::PARAM_INT);
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->execute();
                $res = $stmt->fetchAll();
                if (!empty($res)) {
                    return $res;
                }
            } catch (Exception $e) {}
        }

        $samples = array_filter(self::getSampleProducts(), fn($p) => $p['id'] != $excludeId);
        return array_slice(array_values($samples), 0, $limit);
    }

    /** Danh sách toàn bộ danh mục */
    public function getCategories(): array
    {
        if ($this->db !== null) {
            try {
                $stmt = $this->db->query('SELECT * FROM categories ORDER BY id ASC');
                $res = $stmt->fetchAll();
                if (!empty($res)) {
                    return $res;
                }
            } catch (Exception $e) {}
        }

        return [
            ['id' => 1, 'name' => 'Laptop Gaming', 'slug' => 'laptop-gaming', 'icon' => 'fa-solid fa-laptop-code'],
            ['id' => 2, 'name' => 'Laptop Văn Phòng', 'slug' => 'laptop-van-phong', 'icon' => 'fa-solid fa-laptop'],
            ['id' => 3, 'name' => 'PC Bán Sẵn', 'slug' => 'pc-build-san', 'icon' => 'fa-solid fa-desktop'],
            ['id' => 4, 'name' => 'Linh Kiện PC', 'slug' => 'pc-linh-kien', 'icon' => 'fa-solid fa-microchip'],
            ['id' => 5, 'name' => 'Gaming Gear', 'slug' => 'gaming-gear', 'icon' => 'fa-solid fa-gamepad'],
            ['id' => 6, 'name' => 'Màn Hình', 'slug' => 'man-hinh', 'icon' => 'fa-solid fa-tv'],
            ['id' => 7, 'name' => 'Máy Tính Bộ', 'slug' => 'may-tinh-bo', 'icon' => 'fa-solid fa-server'],
        ];
    }

    /** Lấy sản phẩm best seller hoặc phân loại theo tab cho Best Sellers */
    public function getBestSellersByTab(string $tab, int $limit = 6): array
    {
        if ($this->db !== null) {
            try {
                $slugs = [];
                switch (strtolower($tab)) {
                    case 'laptop':
                        $slugs = ['laptop-gaming', 'laptop-van-phong'];
                        break;
                    case 'gaming':
                        $slugs = ['gaming-gear', 'laptop-gaming'];
                        break;
                    case 'components':
                        $slugs = ['pc-linh-kien'];
                        break;
                    case 'monitor':
                        $slugs = ['man-hinh'];
                        break;
                    case 'accessories':
                        $slugs = ['gaming-gear', 'office-gear'];
                        break;
                    default:
                        return $this->getBestSellers($limit);
                }

                $inQuery = implode(',', array_fill(0, count($slugs), '?'));
                $sql = "SELECT p.*, b.name as brand_name 
                        FROM products p
                        JOIN categories c ON p.category_id = c.id
                        LEFT JOIN brands b ON p.brand_id = b.id
                        WHERE (c.slug IN ($inQuery) OR c.parent_id IN (SELECT id FROM categories WHERE slug IN ($inQuery)))
                        ORDER BY p.id DESC LIMIT ?";

                $stmt = $this->db->prepare($sql);
                foreach ($slugs as $k => $slug) {
                    $stmt->bindValue($k + 1, $slug);
                }
                $stmt->bindValue(count($slugs) + 1, $limit, PDO::PARAM_INT);
                $stmt->execute();
                $res = $stmt->fetchAll();
                if (!empty($res)) {
                    return $res;
                }
            } catch (Exception $e) {}
        }

        return array_slice(self::getSampleProducts(), 0, $limit);
    }

    public function getBestSellers(int $limit = 6): array
    {
        if ($this->db !== null) {
            try {
                $stmt = $this->db->prepare('SELECT p.*, b.name as brand_name FROM products p LEFT JOIN brands b ON p.brand_id = b.id WHERE p.is_best_seller = 1 ORDER BY p.id DESC LIMIT :limit');
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->execute();
                $res = $stmt->fetchAll();
                if (!empty($res)) {
                    return $res;
                }
            } catch (Exception $e) {}
        }

        return array_slice(self::getSampleProducts(), 0, $limit);
    }

    public function getNewArrivals(int $limit = 6): array
    {
        if ($this->db !== null) {
            try {
                $stmt = $this->db->prepare('SELECT p.*, b.name as brand_name FROM products p LEFT JOIN brands b ON p.brand_id = b.id WHERE p.is_new_arrival = 1 ORDER BY p.id DESC LIMIT :limit');
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->execute();
                $res = $stmt->fetchAll();
                if (!empty($res)) {
                    return $res;
                }
            } catch (Exception $e) {}
        }

        return array_slice(self::getSampleProducts(), 0, $limit);
    }

    public function getAiRecommend(int $limit = 6): array
    {
        if ($this->db !== null) {
            try {
                $stmt = $this->db->prepare('SELECT p.*, b.name as brand_name FROM products p LEFT JOIN brands b ON p.brand_id = b.id WHERE p.is_ai_recommend = 1 ORDER BY p.id DESC LIMIT :limit');
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->execute();
                $res = $stmt->fetchAll();
                if (!empty($res)) {
                    return $res;
                }
            } catch (Exception $e) {}
        }

        return array_slice(self::getSampleProducts(), 0, $limit);
    }

    /** Tìm kiếm sản phẩm nâng cao với Relevance Scoring, Model Tokenization & Specs Search */
    public function search(string $keyword = '', string $categorySlug = '', string $brandSlug = '', float $minPrice = 0, float $maxPrice = 0, string $sort = 'relevance', int $limit = 48, int $offset = 0): array
    {
        if ($this->db === null) {
            return [];
        }

        try {
            $rawKeyword = trim($keyword);

            // 1. Normalize Keyword (max 100 chars, collapse whitespace)
            if (mb_strlen($rawKeyword, 'UTF-8') > 100) {
                $rawKeyword = mb_substr($rawKeyword, 0, 100, 'UTF-8');
            }
            $normalizedKeyword = function_exists('mb_strtolower') 
                ? mb_strtolower(preg_replace('/\s+/u', ' ', $rawKeyword), 'UTF-8') 
                : strtolower(preg_replace('/\s+/', ' ', $rawKeyword));

            $params = [];
            $whereConditions = ["p.status = 'active'"];

            // Filter Category
            if (!empty($categorySlug)) {
                $whereConditions[] = '(c.slug = :catSlug1 OR c.parent_id IN (SELECT id FROM categories WHERE slug = :catSlug2))';
                $params[':catSlug1'] = $categorySlug;
                $params[':catSlug2'] = $categorySlug;
            }

            // Filter Brand
            if (!empty($brandSlug)) {
                $whereConditions[] = 'b.slug = :brandSlug';
                $params[':brandSlug'] = $brandSlug;
            }

            // Filter Price
            if ($minPrice > 0) {
                $whereConditions[] = 'p.price >= :minPrice';
                $params[':minPrice'] = $minPrice;
            }
            if ($maxPrice > 0) {
                $whereConditions[] = 'p.price <= :maxPrice';
                $params[':maxPrice'] = $maxPrice;
            }

            // 2. Search Keyword & Tokenization
            $relevanceSql = '0';

            if (!empty($normalizedKeyword)) {
                $whereConditions[] = '
                    (
                        LOWER(p.name) LIKE :w_name
                        OR LOWER(p.short_desc) LIKE :w_sdesc
                        OR LOWER(p.description) LIKE :w_desc
                        OR LOWER(p.specs) LIKE :w_specs
                        OR LOWER(b.name) LIKE :w_brand
                        OR LOWER(c.name) LIKE :w_cat
                    )
                ';
                
                $kLike = '%' . $normalizedKeyword . '%';
                $params[':w_name']  = $kLike;
                $params[':w_sdesc'] = $kLike;
                $params[':w_desc']  = $kLike;
                $params[':w_specs'] = $kLike;
                $params[':w_brand'] = $kLike;
                $params[':w_cat']   = $kLike;

                // Model distinction checks (prevent "i3" from matching "i5" or "i7", etc.)
                if (preg_match('/^i[3579]$/i', $normalizedKeyword)) {
                    $otherModels = array_diff(['i3', 'i5', 'i7', 'i9'], [$normalizedKeyword]);
                    foreach ($otherModels as $idx => $other) {
                        $whereConditions[] = "(LOWER(p.name) NOT LIKE :excl_name_{$idx} OR LOWER(p.name) LIKE :target_name_{$idx})";
                        $params[":excl_name_{$idx}"] = '%core ' . $other . '%';
                        $params[":target_name_{$idx}"] = '%' . $normalizedKeyword . '%';
                    }
                }

                // Weighted Relevance Scoring
                $relevanceSql = '
                    (CASE WHEN LOWER(p.name) = :exactName THEN 100 ELSE 0 END) +
                    (CASE WHEN LOWER(p.name) LIKE :startsName THEN 80 ELSE 0 END) +
                    (CASE WHEN LOWER(p.name) LIKE :containsName THEN 60 ELSE 0 END) +
                    (CASE WHEN LOWER(p.specs) LIKE :containsSpecs THEN 50 ELSE 0 END) +
                    (CASE WHEN LOWER(c.name) LIKE :containsCategory THEN 35 ELSE 0 END) +
                    (CASE WHEN LOWER(b.name) LIKE :containsBrand THEN 30 ELSE 0 END) +
                    (CASE WHEN LOWER(p.short_desc) LIKE :containsShortDesc THEN 15 ELSE 0 END) +
                    (CASE WHEN LOWER(p.description) LIKE :containsDescription THEN 5 ELSE 0 END)
                ';

                $params[':exactName']           = $normalizedKeyword;
                $params[':startsName']          = $normalizedKeyword . '%';
                $params[':containsName']        = '%' . $normalizedKeyword . '%';
                $params[':containsSpecs']       = '%' . $normalizedKeyword . '%';
                $params[':containsCategory']    = '%' . $normalizedKeyword . '%';
                $params[':containsBrand']       = '%' . $normalizedKeyword . '%';
                $params[':containsShortDesc']   = '%' . $normalizedKeyword . '%';
                $params[':containsDescription'] = '%' . $normalizedKeyword . '%';
            }

            $whereClause = implode(' AND ', $whereConditions);

            // Whitelist Order By
            $sortClause = 'relevance_score DESC, p.id DESC';
            if ($sort === 'price_asc') {
                $sortClause = 'p.price ASC, p.id DESC';
            } elseif ($sort === 'price_desc') {
                $sortClause = 'p.price DESC, p.id DESC';
            } elseif ($sort === 'newest') {
                $sortClause = 'p.created_at DESC, p.id DESC';
            } elseif ($sort === 'name_asc') {
                $sortClause = 'p.name ASC';
            } elseif (!empty($normalizedKeyword)) {
                $sortClause = 'relevance_score DESC, p.created_at DESC';
            }

            $query = "
                SELECT 
                    p.*, 
                    b.name as brand_name, 
                    c.name as category_name, 
                    ($relevanceSql) as relevance_score
                FROM products p
                LEFT JOIN brands b ON p.brand_id = b.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE $whereClause
                ORDER BY $sortClause
                LIMIT :limit OFFSET :offset
            ";

            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Compute debug metadata for each item (matched_field & matched_value)
            foreach ($results as &$item) {
                $nameLower = mb_strtolower($item['name'] ?? '', 'UTF-8');
                $specsLower = mb_strtolower($item['specs'] ?? '', 'UTF-8');
                $catLower = mb_strtolower($item['category_name'] ?? '', 'UTF-8');
                $brandLower = mb_strtolower($item['brand_name'] ?? '', 'UTF-8');

                if (!empty($normalizedKeyword)) {
                    if (str_contains($nameLower, $normalizedKeyword)) {
                        $item['matched_field'] = 'name';
                        $item['matched_value'] = $item['name'];
                    } elseif (str_contains($specsLower, $normalizedKeyword)) {
                        $item['matched_field'] = 'specs';
                        $item['matched_value'] = $item['specs'];
                    } elseif (str_contains($catLower, $normalizedKeyword)) {
                        $item['matched_field'] = 'category_name';
                        $item['matched_value'] = $item['category_name'];
                    } elseif (str_contains($brandLower, $normalizedKeyword)) {
                        $item['matched_field'] = 'brand_name';
                        $item['matched_value'] = $item['brand_name'];
                    } else {
                        $item['matched_field'] = 'description';
                        $item['matched_value'] = $item['short_desc'] ?? '';
                    }
                } else {
                    $item['matched_field'] = 'catalog';
                    $item['matched_value'] = 'all';
                }
            }

            return $results;
        } catch (Exception $e) {
            return [];
        }
    }

    /** Lấy sản phẩm theo danh mục không giới hạn */
    public function getByCategory(string $slug, int $limit = 24): array
    {
        return $this->getByCategorySlug($slug, $limit);
    }

    /** Lấy danh sách sản phẩm từ list IDs */
    public function getProductsByIds(array $ids): array
    {
        $ids = array_values($ids);
        if (empty($ids)) return [];

        if ($this->db !== null) {
            try {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $this->db->prepare(
                    "SELECT p.*, b.name as brand_name, c.name as category_name 
                     FROM products p
                     LEFT JOIN brands b ON p.brand_id = b.id
                     LEFT JOIN categories c ON p.category_id = c.id
                     WHERE p.id IN ($placeholders) AND p.status = 'active'"
                );
                $stmt->execute(array_map('intval', $ids));
                $res = $stmt->fetchAll();
                if (!empty($res)) {
                    return $res;
                }
            } catch (Exception $e) {}
        }

        $res = array_filter(self::getSampleProducts(), fn($p) => in_array($p['id'], $ids));
        return array_values($res);
    }
}

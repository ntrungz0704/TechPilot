<?php

require_once ROOT_PATH . '/app/services/CatalogGroupService.php';

class CategoryMenuService
{
    /**
     * Lấy mảng cây 14 danh mục Mega Menu hoàn chỉnh theo đúng 14 mẫu giao diện
     */
    public static function getActiveMenuTree(): array
    {
        return [
            // 1. Laptop
            [
                'id' => 'laptop',
                'name' => 'Laptop',
                'slug' => 'laptop',
                'icon' => 'fa-solid fa-laptop',
                'mega_columns' => [
                    'Thương hiệu' => [
                        ['name' => 'ASUS', 'query' => 'brand=asus'],
                        ['name' => 'ACER', 'query' => 'brand=acer'],
                        ['name' => 'MSI', 'query' => 'brand=msi'],
                        ['name' => 'LENOVO', 'query' => 'brand=lenovo'],
                        ['name' => 'LG - Gram', 'query' => 'brand=lg'],
                    ],
                    'Giá bán' => [
                        ['name' => 'Dưới 15 triệu', 'query' => 'max_price=15000000'],
                        ['name' => 'Từ 15 đến 20 triệu', 'query' => 'min_price=15000001&max_price=20000000'],
                        ['name' => 'Trên 20 triệu', 'query' => 'min_price=20000001'],
                    ],
                    'CPU Intel - AMD' => [
                        ['name' => 'Intel Core i5', 'query' => 'cpu_family=core-i5'],
                        ['name' => 'Intel Core i7', 'query' => 'cpu_family=core-i7'],
                        ['name' => 'Intel Core i9', 'query' => 'cpu_family=core-i9'],
                        ['name' => 'Intel Core Ultra', 'query' => 'cpu_family=core-ultra'],
                        ['name' => 'AMD Ryzen 7', 'query' => 'cpu_family=ryzen-7'],
                    ],
                    'Nhu cầu sử dụng' => [
                        ['name' => 'Đồ họa - Studio', 'query' => 'q=Đồ+họa'],
                        ['name' => 'Học tập - Văn phòng', 'query' => 'gpu=integrated'],
                        ['name' => 'Mỏng nhẹ cao cấp', 'query' => 'q=Mỏng+nhẹ'],
                    ],
                    'Linh phụ kiện Laptop' => [
                        ['name' => 'Ram laptop', 'query' => 'q=RAM+laptop'],
                        ['name' => 'SSD laptop', 'query' => 'q=SSD+laptop'],
                        ['name' => 'Ổ cứng di động', 'query' => 'q=Ổ+cứng+di+động'],
                    ],
                    'Laptop ASUS' => [
                        ['name' => 'ASUS OLED Series', 'query' => 'brand=asus&q=OLED'],
                        ['name' => 'Vivobook Series', 'query' => 'brand=asus&q=Vivobook'],
                        ['name' => 'Zenbook Series', 'query' => 'brand=asus&q=Zenbook'],
                    ],
                    'Laptop ACER' => [
                        ['name' => 'Aspire Series', 'query' => 'brand=acer&q=Aspire'],
                        ['name' => 'Swift Series', 'query' => 'brand=acer&q=Swift'],
                    ],
                    'Laptop MSI' => [
                        ['name' => 'Modern Series', 'query' => 'brand=msi&q=Modern'],
                        ['name' => 'Prestige Series', 'query' => 'brand=msi&q=Prestige'],
                    ],
                    'Laptop Lenovo' => [
                        ['name' => 'Thinkbook Series', 'query' => 'brand=lenovo&q=Thinkbook'],
                        ['name' => 'Ideapad Series', 'query' => 'brand=lenovo&q=Ideapad'],
                        ['name' => 'Thinkpad Series', 'query' => 'brand=lenovo&q=Thinkpad'],
                        ['name' => 'Yoga Series', 'query' => 'brand=lenovo&q=Yoga'],
                    ],
                ]
            ],

            // 2. Laptop Gaming
            [
                'id' => 'laptop-gaming',
                'name' => 'Laptop Gaming',
                'slug' => 'laptop',
                'query' => 'gpu=dedicated',
                'icon' => 'fa-solid fa-gamepad',
                'mega_columns' => [
                    'Thương hiệu' => [
                        ['name' => 'ACER / PREDATOR', 'query' => 'brand=acer&q=gaming'],
                        ['name' => 'ASUS / ROG', 'query' => 'brand=asus&q=gaming'],
                        ['name' => 'MSI', 'query' => 'brand=msi&q=gaming'],
                        ['name' => 'LENOVO', 'query' => 'brand=lenovo&q=gaming'],
                        ['name' => 'GIGABYTE / AORUS', 'query' => 'brand=gigabyte&q=gaming'],
                    ],
                    'Giá bán' => [
                        ['name' => 'Dưới 20 triệu', 'query' => 'max_price=20000000'],
                        ['name' => 'Từ 20 đến 25 triệu', 'query' => 'min_price=20000001&max_price=25000000'],
                        ['name' => 'Từ 25 đến 30 triệu', 'query' => 'min_price=25000001&max_price=30000000'],
                        ['name' => 'Trên 30 triệu', 'query' => 'min_price=30000001'],
                        ['name' => 'Gaming RTX 50 Series', 'query' => 'q=RTX+50'],
                    ],
                    'ACER | PREDATOR' => [
                        ['name' => 'Nitro ProPanel Series', 'query' => 'brand=acer&q=Nitro'],
                        ['name' => 'Nitro Series', 'query' => 'brand=acer&q=Nitro'],
                        ['name' => 'Aspire Series', 'query' => 'brand=acer&q=Aspire'],
                        ['name' => 'Predator Series', 'query' => 'brand=acer&q=Predator'],
                    ],
                    'ASUS | ROG Gaming' => [
                        ['name' => 'ROG Series', 'query' => 'brand=asus&q=ROG'],
                        ['name' => 'TUF Series', 'query' => 'brand=asus&q=TUF'],
                        ['name' => 'Zephyrus Series', 'query' => 'brand=asus&q=Zephyrus'],
                    ],
                    'MSI Gaming' => [
                        ['name' => 'Titan GT Series', 'query' => 'brand=msi&q=Titan'],
                        ['name' => 'Stealth GS Series', 'query' => 'brand=msi&q=Stealth'],
                        ['name' => 'Raider GE Series', 'query' => 'brand=msi&q=Raider'],
                        ['name' => 'Vector GP Series', 'query' => 'brand=msi&q=Vector'],
                        ['name' => 'Katana / Cyborg Series', 'query' => 'brand=msi&q=Katana'],
                    ],
                    'LENOVO Gaming' => [
                        ['name' => 'Legion Gaming', 'query' => 'brand=lenovo&q=Legion'],
                        ['name' => 'LOQ series', 'query' => 'brand=lenovo&q=LOQ'],
                    ],
                    'GIGABYTE Gaming' => [
                        ['name' => 'Gaming Gigabyte', 'query' => 'brand=gigabyte&q=Gaming'],
                    ],
                    'Cấu hình' => [
                        ['name' => 'RTX 50 Series', 'query' => 'q=RTX+50'],
                        ['name' => 'CPU Core Ultra', 'query' => 'q=Core+Ultra'],
                        ['name' => 'CPU AMD', 'query' => 'q=AMD'],
                    ],
                    'Linh - Phụ kiện Laptop' => [
                        ['name' => 'Ram laptop', 'query' => 'q=RAM+laptop'],
                        ['name' => 'SSD laptop', 'query' => 'q=SSD+laptop'],
                    ]
                ]
            ],

            // 3. PC TechPilot
            [
                'id' => 'pc-techpilot',
                'name' => 'PC TechPilot',
                'slug' => 'pc',
                'icon' => 'fa-solid fa-desktop',
                'mega_columns' => [
                    'PC THEO GIÁ' => [
                        ['name' => 'PC DƯỚI 30 TRIỆU', 'query' => 'max_price=30000000'],
                        ['name' => 'PC TỪ 30 - 50 TRIỆU', 'query' => 'min_price=30000001&max_price=50000000'],
                        ['name' => 'PC TỪ 50 - 70 TRIỆU', 'query' => 'min_price=50000001&max_price=70000000'],
                        ['name' => 'PC TỪ 70 - 100 TRIỆU', 'query' => 'min_price=70000001&max_price=100000000'],
                        ['name' => 'PC TRÊN 100 TRIỆU', 'query' => 'min_price=100000001'],
                    ],
                    'PC theo CPU Intel' => [
                        ['name' => 'PC Core i3', 'query' => 'q=Core+i3'],
                        ['name' => 'PC Core i5 (HOT)', 'query' => 'q=Core+i5'],
                        ['name' => 'PC Core i7 (Tặng Màn)', 'query' => 'q=Core+i7'],
                        ['name' => 'PC Core i9', 'query' => 'q=Core+i9'],
                        ['name' => 'PC Ultra 5', 'query' => 'q=Ultra+5'],
                        ['name' => 'PC Ultra 7', 'query' => 'q=Ultra+7'],
                    ],
                    'PC theo CPU AMD' => [
                        ['name' => 'PC AMD R3', 'query' => 'q=Ryzen+3'],
                        ['name' => 'PC AMD R5 (HOT)', 'query' => 'q=Ryzen+5'],
                        ['name' => 'PC AMD R7', 'query' => 'q=Ryzen+7'],
                        ['name' => 'PC AMD R9', 'query' => 'q=Ryzen+9'],
                    ],
                    'PC theo cấu hình VGA' => [
                        ['name' => 'PC RTX 5090 / 4090', 'query' => 'q=RTX+4090'],
                        ['name' => 'PC RTX 4080 / 5080', 'query' => 'q=RTX+4080'],
                        ['name' => 'PC RTX 4070 Super', 'query' => 'q=RTX+4070'],
                        ['name' => 'PC RTX 4060 (HOT)', 'query' => 'q=RTX+4060'],
                        ['name' => 'PC RTX 3060 / 3050', 'query' => 'q=RTX+3060'],
                    ],
                    'PC Văn phòng & AI' => [
                        ['name' => 'PC I5 (Tặng Màn 3tr)', 'query' => 'q=i5'],
                        ['name' => 'Homework Athlon / AMD', 'query' => 'q=Homework'],
                        ['name' => 'PC AI Workstation', 'query' => 'q=AI'],
                        ['name' => 'Window / Office bản quyền', 'query' => 'q=Windows+Office'],
                    ]
                ]
            ],

            // 4. Main, CPU, VGA
            [
                'id' => 'main-cpu-vga',
                'name' => 'Main, CPU, VGA',
                'slug' => 'pc-linh-kien',
                'icon' => 'fa-solid fa-microchip',
                'mega_columns' => [
                    'VGA Card màn hình' => [
                        ['name' => 'RTX 4070 / 4070 Super', 'query' => 'cat=vga&q=4070'],
                        ['name' => 'RTX 4060 / 4060Ti', 'query' => 'cat=vga&q=4060'],
                        ['name' => 'RTX 3060 / 3050', 'query' => 'cat=vga&q=3060'],
                        ['name' => 'NVIDIA Quadro', 'query' => 'cat=vga&q=Quadro'],
                        ['name' => 'AMD Radeon', 'query' => 'cat=vga&q=Radeon'],
                    ],
                    'Bo mạch chủ Intel' => [
                        ['name' => 'Z890 (Mới) / Z790', 'query' => 'cat=mainboard&q=Z790'],
                        ['name' => 'B760 / B660', 'query' => 'cat=mainboard&q=B760'],
                        ['name' => 'H610', 'query' => 'cat=mainboard&q=H610'],
                    ],
                    'Bo mạch chủ AMD' => [
                        ['name' => 'AMD X870 / X670', 'query' => 'cat=mainboard&q=X670'],
                        ['name' => 'AMD B650 / B550', 'query' => 'cat=mainboard&q=B650'],
                        ['name' => 'AMD A320 / TRX40', 'query' => 'cat=mainboard&q=A320'],
                    ],
                    'CPU - Bộ vi xử lý Intel' => [
                        ['name' => 'CPU Intel Core Ultra Series 2', 'query' => 'cat=cpu&q=Core+Ultra'],
                        ['name' => 'CPU Intel Core i9 / i7', 'query' => 'cat=cpu&q=i7'],
                        ['name' => 'CPU Intel Core i5 / i3', 'query' => 'cat=cpu&q=i5'],
                    ],
                    'CPU - Bộ vi xử lý AMD' => [
                        ['name' => 'CPU AMD Ryzen 9 / R7', 'query' => 'cat=cpu&q=Ryzen+7'],
                        ['name' => 'CPU AMD Ryzen 5 / R3', 'query' => 'cat=cpu&q=Ryzen+5'],
                    ]
                ]
            ],

            // 5. Case, Nguồn, Tản
            [
                'id' => 'case-nguon-tan',
                'name' => 'Case, Nguồn, Tản',
                'slug' => 'pc-linh-kien',
                'icon' => 'fa-solid fa-box',
                'mega_columns' => [
                    'Case Thùng máy' => [
                        ['name' => 'Case ASUS', 'query' => 'cat=case&brand=asus'],
                        ['name' => 'Case Corsair', 'query' => 'cat=case&brand=corsair'],
                        ['name' => 'Case Lianli / NZXT', 'query' => 'cat=case&q=NZXT'],
                        ['name' => 'Case Montech / DeepCool', 'query' => 'cat=case&q=Montech'],
                    ],
                    'Nguồn - Theo Hãng' => [
                        ['name' => 'Nguồn Corsair', 'query' => 'cat=psu&brand=corsair'],
                        ['name' => 'Nguồn ASUS / MSI', 'query' => 'cat=psu&brand=asus'],
                        ['name' => 'Nguồn DeepCool / NZXT', 'query' => 'cat=psu&q=DeepCool'],
                    ],
                    'Nguồn - Theo công suất' => [
                        ['name' => 'Từ 400w - 500w', 'query' => 'cat=psu&q=500W'],
                        ['name' => 'Từ 500w - 600w', 'query' => 'cat=psu&q=600W'],
                        ['name' => 'Từ 700w - 800w', 'query' => 'cat=psu&q=750W'],
                        ['name' => 'Trên 1000w', 'query' => 'cat=psu&q=1000W'],
                    ],
                    'Loại tản nhiệt' => [
                        ['name' => 'Tản nhiệt AIO 240mm', 'query' => 'cat=cooling&q=240mm'],
                        ['name' => 'Tản nhiệt AIO 360mm', 'query' => 'cat=cooling&q=360mm'],
                        ['name' => 'Tản nhiệt khí', 'query' => 'cat=cooling&q=Air'],
                        ['name' => 'Fan RGB & Phụ kiện PC', 'query' => 'cat=cooling&q=RGB'],
                    ]
                ]
            ],

            // 6. Ổ cứng, RAM, Thẻ nhớ
            [
                'id' => 'o-cung-ram',
                'name' => 'Ổ cứng, RAM, Thẻ nhớ',
                'slug' => 'storage',
                'icon' => 'fa-solid fa-memory',
                'mega_columns' => [
                    'Dung lượng RAM' => [
                        ['name' => 'RAM 8 GB', 'query' => 'cat=ram&q=8GB'],
                        ['name' => 'RAM 16 GB', 'query' => 'cat=ram&q=16GB'],
                        ['name' => 'RAM 32 GB', 'query' => 'cat=ram&q=32GB'],
                        ['name' => 'RAM 64 GB', 'query' => 'cat=ram&q=64GB'],
                    ],
                    'Loại RAM & Hãng' => [
                        ['name' => 'RAM DDR4', 'query' => 'cat=ram&q=DDR4'],
                        ['name' => 'RAM DDR5', 'query' => 'cat=ram&q=DDR5'],
                        ['name' => 'RAM Corsair / Kingston / G.Skill', 'query' => 'cat=ram&brand=corsair'],
                    ],
                    'Dung lượng SSD' => [
                        ['name' => '250GB - 256GB', 'query' => 'cat=storage&q=256GB'],
                        ['name' => '480GB - 512GB', 'query' => 'cat=storage&q=512GB'],
                        ['name' => '960GB - 1TB', 'query' => 'cat=storage&q=1TB'],
                        ['name' => '2TB & Trên 2TB', 'query' => 'cat=storage&q=2TB'],
                    ],
                    'Hãng SSD & HDD' => [
                        ['name' => 'Samsung / WD / Kingston', 'query' => 'cat=storage&q=Samsung'],
                        ['name' => 'HDD 1TB / 2TB / 4TB', 'query' => 'cat=storage&q=HDD'],
                        ['name' => 'Thẻ nhớ Sandisk / Ổ cứng di động', 'query' => 'cat=storage&q=Sandisk'],
                    ]
                ]
            ],

            // 7. Loa, Micro, Webcam
            [
                'id' => 'loa-micro-webcam',
                'name' => 'Loa, Micro, Webcam',
                'slug' => 'speaker',
                'icon' => 'fa-solid fa-volume-high',
                'mega_columns' => [
                    'Thương hiệu loa' => [
                        ['name' => 'Edifier', 'query' => 'cat=speaker&q=Edifier'],
                        ['name' => 'Razer', 'query' => 'cat=speaker&brand=razer'],
                        ['name' => 'Logitech', 'query' => 'cat=speaker&brand=logitech'],
                        ['name' => 'SoundMax', 'query' => 'cat=speaker&q=SoundMax'],
                    ],
                    'Kiểu Loa' => [
                        ['name' => 'Loa vi tính', 'query' => 'cat=speaker&q=vi+tính'],
                        ['name' => 'Loa Bluetooth', 'query' => 'cat=speaker&q=Bluetooth'],
                        ['name' => 'Loa Soundbar / Sub trầm', 'query' => 'cat=speaker&q=Soundbar'],
                    ],
                    'Webcam & Micro' => [
                        ['name' => 'Webcam 4K / Full HD 1080p', 'query' => 'cat=accessories&q=Webcam'],
                        ['name' => 'Micro HyperX / Podcast', 'query' => 'cat=accessories&q=Micro'],
                    ]
                ]
            ],

            // 8. Màn hình
            [
                'id' => 'man-hinh',
                'name' => 'Màn hình',
                'slug' => 'monitor',
                'icon' => 'fa-solid fa-tv',
                'mega_columns' => [
                    'Hãng sản xuất' => [
                        ['name' => 'LG', 'query' => 'cat=monitor&brand=lg'],
                        ['name' => 'ASUS', 'query' => 'cat=monitor&brand=asus'],
                        ['name' => 'Dell', 'query' => 'cat=monitor&brand=dell'],
                        ['name' => 'Gigabyte / ViewSonic / AOC', 'query' => 'cat=monitor&brand=gigabyte'],
                        ['name' => 'Samsung / MSI / Acer', 'query' => 'cat=monitor&brand=samsung'],
                    ],
                    'Giá tiền' => [
                        ['name' => 'Dưới 5 triệu', 'query' => 'cat=monitor&max_price=5000000'],
                        ['name' => 'Từ 5 đến 10 triệu', 'query' => 'cat=monitor&min_price=5000001&max_price=10000000'],
                        ['name' => 'Từ 10 đến 20 triệu', 'query' => 'cat=monitor&min_price=10000001&max_price=20000000'],
                        ['name' => 'Trên 20 triệu', 'query' => 'cat=monitor&min_price=20000001'],
                    ],
                    'Độ phân giải & Tần số quét' => [
                        ['name' => 'Màn hình Full HD', 'query' => 'cat=monitor&q=Full+HD'],
                        ['name' => 'Màn hình 2K 1440p / 4K UHD', 'query' => 'cat=monitor&q=2K'],
                        ['name' => 'Tần số quét 144Hz / 240Hz', 'query' => 'cat=monitor&q=144Hz'],
                    ],
                    'Loại màn hình' => [
                        ['name' => 'Màn hình cong 24" / 27" / 32"', 'query' => 'cat=monitor&q=Curved'],
                        ['name' => 'Màn hình đồ họa chuẩn màu', 'query' => 'cat=monitor&q=Đồ+họa'],
                        ['name' => 'Màn hình OLED cao cấp', 'query' => 'cat=monitor&q=OLED'],
                        ['name' => 'Giá treo màn hình VESA', 'query' => 'cat=accessories&q=Giá+treo'],
                    ]
                ]
            ],

            // 9. Bàn phím
            [
                'id' => 'ban-phim',
                'name' => 'Bàn phím',
                'slug' => 'keyboard',
                'icon' => 'fa-solid fa-keyboard',
                'mega_columns' => [
                    'Thương hiệu phím' => [
                        ['name' => 'AKKO / Keychron / FL-Esports', 'query' => 'cat=keyboard&q=AKKO'],
                        ['name' => 'Dare-U / E-Dra / AULA', 'query' => 'cat=keyboard&brand=dareu'],
                        ['name' => 'ASUS / Corsair / Razer', 'query' => 'cat=keyboard&brand=asus'],
                        ['name' => 'Logitech / Rapoo / VGN', 'query' => 'cat=keyboard&brand=logitech'],
                    ],
                    'Giá tiền & Kết nối' => [
                        ['name' => '1 triệu - 2 triệu', 'query' => 'cat=keyboard&min_price=1000000&max_price=2000000'],
                        ['name' => '2 triệu - 3 triệu', 'query' => 'cat=keyboard&min_price=2000001&max_price=3000000'],
                        ['name' => 'Trên 3 triệu', 'query' => 'cat=keyboard&min_price=3000001'],
                        ['name' => 'Bàn phím Bluetooth / Wireless', 'query' => 'cat=keyboard&q=Wireless'],
                    ],
                    'Phụ kiện phím cơ' => [
                        ['name' => 'Bàn phím Rapid Trigger', 'query' => 'cat=keyboard&q=Rapid+Trigger'],
                        ['name' => 'Keycaps nghệ thuật', 'query' => 'cat=keyboard&q=Keycap'],
                        ['name' => 'Kê tay bàn phím', 'query' => 'cat=keyboard&q=Kê+tay'],
                    ]
                ]
            ],

            // 10. Chuột + Lót chuột
            [
                'id' => 'chuot-lot-chuot',
                'name' => 'Chuột + Lót chuột',
                'slug' => 'mouse',
                'icon' => 'fa-solid fa-computer-mouse',
                'mega_columns' => [
                    'Thương hiệu chuột' => [
                        ['name' => 'Logitech', 'query' => 'cat=mouse&brand=logitech'],
                        ['name' => 'Razer', 'query' => 'cat=mouse&brand=razer'],
                        ['name' => 'Corsair / Glorious / HyperX', 'query' => 'cat=mouse&brand=corsair'],
                        ['name' => 'ASUS / DareU / Rapoo', 'query' => 'cat=mouse&brand=asus'],
                    ],
                    'Chuột theo giá tiền' => [
                        ['name' => 'Dưới 500 nghìn', 'query' => 'cat=mouse&max_price=500000'],
                        ['name' => 'Từ 500k - 1 triệu', 'query' => 'cat=mouse&min_price=500001&max_price=1000000'],
                        ['name' => 'Từ 1 - 2 triệu', 'query' => 'cat=mouse&min_price=1000001&max_price=2000000'],
                        ['name' => 'Trên 2 triệu', 'query' => 'cat=mouse&min_price=2000001'],
                    ],
                    'Loại Chuột & Lót chuột' => [
                        ['name' => 'Chuột chơi game siêu nhẹ', 'query' => 'cat=mouse&q=Gaming'],
                        ['name' => 'Chuột văn phòng không dây', 'query' => 'cat=mouse&q=Văn+phòng'],
                        ['name' => 'Lót chuột RGB / Nỉ / Da', 'query' => 'cat=accessories&q=Lót+chuột'],
                    ]
                ]
            ],

            // 11. Tai Nghe
            [
                'id' => 'tai-nghe',
                'name' => 'Tai Nghe',
                'slug' => 'headset',
                'icon' => 'fa-solid fa-headphones',
                'mega_columns' => [
                    'Thương hiệu tai nghe' => [
                        ['name' => 'HyperX', 'query' => 'cat=headset&q=HyperX'],
                        ['name' => 'ASUS / ROG', 'query' => 'cat=headset&brand=asus'],
                        ['name' => 'Corsair / Razer', 'query' => 'cat=headset&brand=corsair'],
                        ['name' => 'Steelseries / Logitech / Edifier', 'query' => 'cat=headset&brand=logitech'],
                    ],
                    'Tai nghe theo giá' => [
                        ['name' => 'Tai nghe dưới 1 triệu', 'query' => 'cat=headset&max_price=1000000'],
                        ['name' => 'Tai nghe 1 đến 2 triệu', 'query' => 'cat=headset&min_price=1000001&max_price=2000000'],
                        ['name' => 'Tai nghe 2 đến 3 triệu', 'query' => 'cat=headset&min_price=2000001&max_price=3000000'],
                        ['name' => 'Tai nghe trên 3 triệu', 'query' => 'cat=headset&min_price=3000001'],
                    ],
                    'Kiểu dáng & Kết nối' => [
                        ['name' => 'Tai nghe Wireless / Bluetooth', 'query' => 'cat=headset&q=Wireless'],
                        ['name' => 'Tai nghe Over-ear trùm đầu', 'query' => 'cat=headset&q=Over-ear'],
                        ['name' => 'Tai nghe Gaming In-ear', 'query' => 'cat=headset&q=In-ear'],
                    ]
                ]
            ],

            // 12. Ghế - Bàn
            [
                'id' => 'ghe-ban',
                'name' => 'Ghế - Bàn',
                'slug' => 'chair',
                'icon' => 'fa-solid fa-chair',
                'mega_columns' => [
                    'Thương hiệu ghế' => [
                        ['name' => 'Corsair', 'query' => 'cat=chair&brand=corsair'],
                        ['name' => 'Warrior / Sihoo', 'query' => 'cat=chair&q=Sihoo'],
                        ['name' => 'E-DRA / DXRacer / Razer', 'query' => 'cat=chair&q=DXRacer'],
                    ],
                    'Kiểu ghế & Giá tiền' => [
                        ['name' => 'Ghế Công thái học (Ergonomic)', 'query' => 'cat=chair&q=Công+thái+học'],
                        ['name' => 'Ghế Gaming bọc da', 'query' => 'cat=chair&q=Gaming'],
                        ['name' => 'Dưới 5 triệu', 'query' => 'cat=chair&max_price=5000000'],
                        ['name' => 'Từ 5 đến 10 triệu', 'query' => 'cat=chair&min_price=5000001&max_price=10000000'],
                        ['name' => 'Trên 10 triệu', 'query' => 'cat=chair&min_price=10000001'],
                    ],
                    'Bàn Gaming & Công thái học' => [
                        ['name' => 'Bàn Gaming DXRacer / E-Dra', 'query' => 'cat=chair&q=Bàn+Gaming'],
                        ['name' => 'Bàn Công thái học nâng hạ', 'query' => 'cat=chair&q=Bàn+nâng+hạ'],
                    ]
                ]
            ],

            // 13. Phần mềm, mạng
            [
                'id' => 'phan-mem-mang',
                'name' => 'Phần mềm, mạng',
                'slug' => 'networking',
                'icon' => 'fa-solid fa-wifi',
                'mega_columns' => [
                    'Hãng sản xuất WiFi' => [
                        ['name' => 'TP-LINK', 'query' => 'cat=networking&brand=tp-link'],
                        ['name' => 'ASUS Networking', 'query' => 'cat=networking&brand=asus'],
                        ['name' => 'LinkSys / Mercusys', 'query' => 'cat=networking&q=Linksys'],
                    ],
                    'Router Wi-Fi & Mesh' => [
                        ['name' => 'Router WiFi 6 tốc độ cao', 'query' => 'cat=networking&q=WiFi+6'],
                        ['name' => 'Router Mesh phủ sóng rộng', 'query' => 'cat=networking&q=Mesh'],
                        ['name' => 'Router Gaming xuyên tường', 'query' => 'cat=networking&q=Gaming'],
                    ],
                    'USB Thu sóng & Card mạng' => [
                        ['name' => 'USB WiFi cho PC / Laptop', 'query' => 'cat=networking&q=USB+WiFi'],
                        ['name' => 'Card WiFi PCIe / Dây cáp mạng', 'query' => 'cat=networking&q=Card+WiFi'],
                    ],
                    'Phần mềm bản quyền' => [
                        ['name' => 'Microsoft Office 365 / Home', 'query' => 'cat=office-equipment&q=Office'],
                        ['name' => 'Windows 11 Home / Pro bản quyền', 'query' => 'cat=office-equipment&q=Windows'],
                    ]
                ]
            ],

            // 14. Phụ kiện (Hub, sạc, cáp..)
            [
                'id' => 'phu-kien',
                'name' => 'Phụ kiện (Hub, sạc, cáp..)',
                'slug' => 'accessories',
                'icon' => 'fa-solid fa-plug',
                'mega_columns' => [
                    'Hub, sạc, cáp' => [
                        ['name' => 'Hub chuyển đổi USB-C / Type-C', 'query' => 'cat=accessories&q=Hub'],
                        ['name' => 'Dây cáp HDMI / DisplayPort / Type-C', 'query' => 'cat=accessories&q=Dây+cáp'],
                        ['name' => 'Củ sạc nhanh GaN 65W / 100W', 'query' => 'cat=accessories&q=Củ+sạc'],
                    ],
                    'Sạc dự phòng & Quạt mini' => [
                        ['name' => 'Pin sạc dự phòng 20W - 100W', 'query' => 'cat=power-bank'],
                        ['name' => 'Quạt cầm tay Jisulife / Quạt mini', 'query' => 'cat=accessories&q=Quạt'],
                    ],
                    'Phụ kiện Elgato & Khác' => [
                        ['name' => 'Stream Deck Elgato', 'query' => 'cat=accessories&q=Elgato'],
                        ['name' => 'Giá đỡ Laptop / Điện thoại', 'query' => 'cat=accessories&q=Giá+đỡ'],
                    ]
                ]
            ]
        ];
    }
}

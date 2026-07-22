<?php
/**
 * database/seeds/news_posts.php
 * Structured seed dataset for TechPilot News articles.
 * Returns array of post items.
 */

return [
    [
        'title' => '10 mẹo tối ưu Windows 11 giúp tăng tốc máy tính và chơi game mượt hơn 2026',
        'slug' => '10-meo-toi-uu-windows-11-tang-toc-may-tinh-choi-game',
        'summary' => 'Hướng dẫn chi tiết các bước tối ưu hóa hệ điều hành Windows 11 giúp giảm ngốn RAM, tắt ứng dụng chạy ngầm và tăng FPS khi chơi game.',
        'content' => <<<'MD'
:::summary
- Xác định nhu cầu trước khi nâng cấp hệ điều hành.
- Không tắt các dịch vụ Windows quan trọng gây lỗi hệ thống.
- Ưu tiên cập nhật driver card đồ họa và kiểm tra nhiệt độ linh kiện.
:::

## 1. Tắt các ứng dụng khởi động cùng Windows (Startup Apps)

Một trong những nguyên nhân hàng đầu khiến máy tính khởi động chậm và ngốn RAM ngay từ khi bật máy là do quá nhiều ứng dụng tự động chạy ngầm. Nhiều phần mềm như Spotify, Discord, Steam hay OneDrive mặc định sẽ khởi động cùng hệ thống.

Để tắt bớt các ứng dụng không cần thiết:
1. Nhấn tổ hợp phím `Ctrl + Shift + Esc` để mở **Task Manager**.
2. Chọn tab **Startup Apps** ở menu bên trái.
3. Nhấp phải vào các ứng dụng không quan trọng và chọn **Disable**.

## 2. Bật chế độ Game Mode tích hợp trên Windows 11

Windows 11 được trang bị tính năng **Game Mode** thông minh hơn rất nhiều so với phiên bản Windows 10 trước đây. Khi bật chế độ này, hệ thống sẽ ưu tiên tài nguyên CPU và GPU cho tựa game đang chạy, đồng thời tạm ngưng các tiến trình cập nhật ngầm.

Cách kích hoạt vô cùng đơn giản:
- Vào **Settings** (`Win + I`) -> **Gaming** -> **Game Mode**.
- Chuyển trạng thái sang **On**.

## 3. Tối ưu hóa cài đặt Graphics Settings (HAGS)

Tính năng **Hardware-accelerated GPU scheduling** (HAGS) cho phép card đồ họa tự quản lý bộ nhớ VRAM thay vì phụ thuộc hoàn toàn vào CPU. Điều này giúp giảm độ trễ (latency) và tăng tốc độ phản hồi trong các tựa game eSport.

Các bước thực hiện:
- Vào **Settings** -> **System** -> **Display** -> **Graphics**.
- Nhấn chọn **Change default graphics settings**.
- Bật **Hardware-accelerated GPU scheduling** và khởi động lại máy tính.

## 4. Tắt hiệu ứng thị giác và hình tĩnh không cần thiết

Giao diện mượt mà của Windows 11 đi kèm với nhiều hiệu ứng đổ bóng, trong suốt (Transparency Effects) ngốn dung lượng RAM và tài nguyên GPU. Đối với các cấu hình máy tính tầm trung hoặc laptop mỏng nhẹ, việc tắt hiệu ứng này giúp giao diện phản hồi tức thì.

- Mở **Settings** -> **Accessibility** -> **Visual effects**.
- Tắt hai tùy chọn **Transparency effects** và **Animation effects**.

## 5. Dọn dẹp tập tin rác và tối ưu bộ nhớ đệm (Storage Sense)

Theo thời gian sử dụng, các tệp tạm (Temporary Files), bộ nhớ đệm cập nhật Windows Update có thể chiếm tới hàng chục GB dung lượng ổ cứng SSD. Khi ổ chứa hệ điều hành bị đầy (trên 85%), hiệu năng truy xuất dữ liệu sẽ giảm đáng kể.

Bạn nên sử dụng công cụ **Storage Sense**:
- Mở **Settings** -> **System** -> **Storage**.
- Bật **Storage Sense** để Windows tự động xóa tệp rác theo chu kỳ.
- Chọn **Temporary files** và nhấn **Remove files** để dọn dẹp ngay lập tức.

## 6. Lựa chọn sơ đồ nguồn điện hiệu năng cao (Ultimate Performance)

Mặc định Windows 11 đặt chế độ nguồn điện ở mức *Balanced* để tiết kiệm điện. Đối với PC để bàn, bạn nên chuyển sang *High Performance* hoặc *Ultimate Performance* để đảm bảo CPU luôn hoạt động ở xung nhịp tối đa.

- Nhấn `Win + R`, gõ `powercfg.cpl` và nhấn Enter.
- Chọn sơ đồ **High Performance** hoặc **Ultimate Performance**.

## 7. Cập nhật Driver GPU mới nhất từ Nvidia hoặc AMD

Driver đồ họa đóng vai trò cầu nối quan trọng giữa hệ điều hành và card màn hình. Các nhà sản xuất liên tục phát hành các bản cập nhật sửa lỗi và tối ưu hiệu năng riêng cho từng trò chơi mới ra mắt.

Hãy thường xuyên kiểm tra ứng dụng **Nvidia GeForce Experience / NVIDIA App** hoặc **AMD Software: Adrenalin Edition** để đảm bảo bạn đang dùng phiên bản driver mới nhất.

## 8. Kết luận

Chỉ với 15 phút thực hiện 7 mẹo tối ưu trên, chiếc máy tính Windows 11 của bạn sẽ vận hành mượt mà hơn rõ rệt, giảm thiểu tình trạng giật khựng (stuttering) và tối đa hóa FPS trong mọi trận game.
MD,
        'image' => 'assets/images/placeholder.jpg',
        'category_slug' => 'pc-linh-kien',
        'post_type' => 'howto',
        'author_name' => 'Ban biên tập TechPilot',
        'status' => 'published',
        'views' => 245,
        'is_featured' => 1,
        'reading_minutes' => 6,
        'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
    ],
    [
        'title' => 'So sánh Intel Core i9-14900K vs AMD Ryzen 7 7800X3D: Đâu là Vua Gaming 2026?',
        'slug' => 'so-sanh-intel-i9-14900k-vs-amd-ryzen-7-7800x3d-vua-gaming',
        'summary' => 'So sánh toàn diện về hiệu năng chơi game, điện năng tiêu thụ, nhiệt độ và hiệu quả chi phí giữa hai vi xử lý cao cấp hàng đầu hiện nay.',
        'content' => <<<'MD'
:::summary
- AMD Ryzen 7 7800X3D dẫn đầu về hiệu năng thuần chơi game nhờ bộ nhớ đệm 3D V-Cache lớn.
- Intel Core i9-14900K đa năng hơn cho các tác vụ làm đồ họa, dựng phim và render nặng.
- Ryzen 7 7800X3D tiết kiệm điện năng hơn hẳn và dễ làm mát hơn nhiều so với i9-14900K.
:::

## 1. Giới thiệu cuộc đối đầu đỉnh cao giữa hai gã khổng lồ

Trong phân khúc vi xử lý cao cấp dành cho game thủ và người sáng tạo nội dung, cuộc chiến giữa **Intel Core i9-14900K** và **AMD Ryzen 7 7800X3D** vẫn là tâm điểm thu hút sự chú ý lớn nhất. Một bên đại diện cho sức mạnh thô với số lượng nhân luồng đồ sộ, một bên là sát thủ gaming nhờ công nghệ bộ nhớ đệm đột phá.

## 2. Thông số kỹ thuật chi tiết

| Thông số | Intel Core i9-14900K | AMD Ryzen 7 7800X3D |
| :--- | :--- | :--- |
| Số nhân / Số luồng | 24 nhân (8P + 16E) / 32 luồng | 8 nhân / 16 luồng |
| Xung nhịp Boost | Tối đa 6.0 GHz | Tối đa 5.0 GHz |
| Bộ nhớ đệm L3 | 36 MB | 96 MB (3D V-Cache) |
| Tiến trình sản xuất | Intel 7 (10nm) | TSMC 5nm |
| Công suất TDP / PL2 | 125W / 253W+ | 120W (Thực tế ~75W) |
| Socket hỗ trợ | LGA 1700 | AM5 |

## 3. Hiệu năng chơi game thực tế (Gaming Benchmark)

Nhờ trang bị công nghệ **3D V-Cache** tiên tiến với tổng cộng 96MB bộ nhớ đệm L3, AMD Ryzen 7 7800X3D thể hiện sức mạnh áp đảo trong đa số các tựa game eSport cũng như AAA ở độ phân giải Full HD (1080p) và 2K (1440p).

Trong các tựa game như *Valorant*, *CS2*, *Shadow of the Tomb Raider* hay *Assetto Corsa Competizione*, Ryzen 7 7800X3D cho mức FPS trung bình cao hơn từ 8% đến 20% so với Core i9-14900K. Đặc biệt, chỉ số FPS 1% Low của 7800X3D cực kỳ ổn định, giúp hiện tượng giật khung hình gần như không xuất hiện.

## 4. Hiệu năng làm việc đa nhiệm và đồ họa (Workload Benchmark)

Nếu chơi game là lãnh địa của AMD thì trong các ứng dụng làm việc như *Adobe Premiere Pro*, *Blender*, *Cinebench R23* hay *V-Ray*, Intel Core i9-14900K lại hoàn toàn làm chủ cuộc chơi.

Với cấu trúc lai 24 nhân 32 luồng (8 nhân hiệu năng cao P-Core và 16 nhân tiết kiệm điện E-Core), i9-14900K đạt điểm số đa nhân Cinebench R23 vượt trội hơn tới 85% so với 7800X3D. Việc xuất video 4K hay render mô hình 3D trên i9-14900K tiết kiệm đáng kể thời gian chờ đợi.

## 5. Nhiệt độ và điện năng tiêu thụ

Đây là khác biệt lớn nhất giữa hai sản phẩm:
- **AMD Ryzen 7 7800X3D** chỉ tiêu thụ trung bình từ **50W - 75W** khi chơi game. Nhờ đó, bạn chỉ cần một tản nhiệt khí chất lượng tốt (như Thermalright Peerless Assassin) là đã có thể duy trì nhiệt độ dưới 70°C.
- **Intel Core i9-14900K** khi nạp tối đa (PL2) có thể tiêu thụ từ **250W - 320W**. Bạn bắt buộc phải trang bị tản nhiệt nước AIO 360mm cao cấp và một bộ nguồn công suất thực tối thiểu 850W.

## 6. Tổng kết và lời khuyên mua sắm

- **Hãy chọn AMD Ryzen 7 7800X3D nếu:** Mục đích chính của bạn là xây dựng một hệ thống chuyên chơi game ở mức hiệu năng tối đa, muốn hệ thống mát mẻ, tiết kiệm điện và dễ dàng nâng cấp CPU trong tương lai trên nền tảng socket AM5.
- **Hãy chọn Intel Core i9-14900K nếu:** Bạn cần một cấu hình máy tính đa năng, vừa phục vụ chơi game giải trí vừa là công cụ làm việc nặng như dựng phim 4K, lập trình hay thiết kế đồ họa 3D.
MD,
        'image' => 'assets/images/placeholder.jpg',
        'category_slug' => 'pc-gaming',
        'post_type' => 'comparison',
        'author_name' => 'Đội ngũ TechPilot',
        'status' => 'published',
        'views' => 310,
        'is_featured' => 1,
        'reading_minutes' => 7,
        'created_at' => date('Y-m-d H:i:s', strtotime('-4 days')),
    ],
    [
        'title' => 'Hướng dẫn chọn mua laptop sinh viên 2026: Đủ mỏng nhẹ, pin trâu và tối ưu ngân sách',
        'slug' => 'huong-dan-chon-laptop-sinh-vien-2026',
        'summary' => 'Kinh nghiệm chi tiết chọn mua laptop cho sinh viên các ngành Công nghệ thông tin, Kinh tế và Đồ họa năm 2026.',
        'content' => <<<'MD'
:::summary
- Xác định đúng nhu cầu sử dụng của ngành học để tránh lãng phí ngân sách.
- Ưu tiên dung lượng RAM tối thiểu 16GB và ổ cứng SSD NVMe 512GB trở lên.
- Đừng bỏ qua các yếu tố về thời lượng pin, độ phân giải màn hình và trọng lượng.
:::

## 1. Nhu cầu chọn laptop sinh viên theo từng ngành học

Bước đầu tiên và quan trọng nhất khi mua laptop là xác định rõ ngành học của bạn cần một cấu hình như thế nào. Việc mua một chiếc laptop quá mạnh so với nhu cầu sẽ làm lãng phí tiền bạc và phải mang vác nặng nề.

### Khối ngành Kinh tế, Ngôn ngữ, Xã hội
Các bạn sinh viên ngành này chủ yếu sử dụng phần mềm văn phòng (Word, Excel, PowerPoint), tra cứu tài liệu và học trực tuyến. 
- **Cấu hình đề xuất:** CPU Intel Core i3 / Core i5 hoặc AMD Ryzen 5 thế hệ mới, RAM 16GB, SSD 512GB.
- **Tiêu chí ưu tiên:** Thiết kế mỏng nhẹ dưới 1.4kg, màn hình IPS FHD sắc nét và pin dùng trên 6 tiếng.

### Khối ngành Công nghệ thông tin, Lập trình
Sinh viên IT cần chạy các môi trường lập trình (VS Code, Android Studio, Docker, Virtual Machines).
- **Cấu hình đề xuất:** CPU Intel Core i5 / i7 hoặc Ryzen 5 / 7 đa nhân, RAM 16GB - 32GB, SSD 512GB - 1TB.
- **Tiêu chí ưu tiên:** Bàn phím gõ êm, màn hình lớn 15.6 inch hoặc 16 inch độ phân giải 2K.

### Khối ngành Đồ họa, Kiến trúc, Mới sáng tạo
Cần xử lý phần mềm nặng như Photoshop, Illustrator, Premiere Pro, AutoCAD, Blender.
- **Cấu hình đề xuất:** CPU dòng H hiệu năng cao, GPU rời (Nvidia RTX 3050 / RTX 4050 trở lên), RAM 16GB - 32GB, SSD 1TB.
- **Tiêu chí ưu tiên:** Màn hình độ chuẩn màu cao 100% sRGB hoặc DCI-P3, tản nhiệt tốt.

## 2. Những thông số phần cứng không thể bỏ qua trong năm 2026

- **RAM 16GB là tiêu chuẩn tối thiểu:** Năm 2026, các trình duyệt web và hệ điều hành Windows 11 đã ngốn khá nhiều RAM. Lựa chọn 16GB RAM giúp bạn mở hàng chục tab Chrome mà không lo đơ lag.
- **Ổ cứng SSD NVMe:** Đảm bảo chọn chuẩn SSD PCIe Gen 3 hoặc Gen 4 với dung lượng tối thiểu 512GB để thoải mái cài đặt ứng dụng.
- **Thời lượng pin:** Hãy chọn những mẫu laptop trang bị pin dung lượng từ 50Wh trở lên để đảm bảo lên lớp học cả buổi không cần mang theo củ sạc.

## 3. Lời kết

Hãy cân nhắc kỹ lưỡng giữa ngân sách và nhu cầu thực tế trước khi xuống tiền mua laptop. Một chiếc máy phù hợp sẽ đồng hành cùng bạn suốt 4 năm đại học một cách hiệu quả nhất.
MD,
        'image' => 'assets/images/placeholder.jpg',
        'category_slug' => 'laptop',
        'post_type' => 'guide',
        'author_name' => 'Ban biên tập TechPilot',
        'status' => 'published',
        'views' => 180,
        'is_featured' => 0,
        'reading_minutes' => 5,
        'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
    ],
    [
        'title' => 'Hướng dẫn chọn mua SSD NVMe PCIe 4.0 tốt nhất năm 2026',
        'slug' => 'huong-dan-chon-mua-ssd-nvme-pcie-4-0-tot-nhat',
        'summary' => 'Phân tích chi tiết tốc độ đọc ghi, chỉ số độ bền TBW và tiêu chí chọn ổ SSD NVMe PCIe 4.0 mượt mà cho PC & Laptop.',
        'content' => <<<'MD'
:::summary
- Tốc độ đọc ghi lý thuyết vượt trội từ 5000 MB/s đến 7400 MB/s.
- Công nghệ DirectStorage giảm thiểu tối đa thời gian tải game AAA.
- Lựa chọn dung lượng tối thiểu từ 512GB đến 1TB để đạt tốc độ tối đa.
:::

## 1. Giới thiệu chuẩn SSD NVMe PCIe 4.0

Ổ cứng **SSD NVMe chuẩn PCIe Gen 4.0** là giải pháp lưu trữ tốc độ cao hàng đầu hiện nay dành cho PC gaming và trạm làm việc. Với băng thông gấp đôi chuẩn PCIe Gen 3.0 cũ, SSD PCIe 4.0 cho phép khởi động hệ điều hành và ứng dụng nặng chỉ trong chớp mắt.

## 2. Tiêu chí lựa chọn SSD NVMe PCIe 4.0 tốt nhất

1. **Tốc độ đọc / ghi:** Ưu tiên ổ có tốc độ đọc ngẫu nhiên (Sequential Read) đạt từ 5000 MB/s trở lên.
2. **Dung lượng bộ nhớ DRAM Cache:** Các dòng SSD cao cấp trang bị chip DRAM giúp duy trì tốc độ đọc ghi ổn định khi chép các tập tin lớn hàng chục GB.
3. **Độ bền TBW (Terabytes Written):** Lựa chọn ổ có chỉ số TBW cao từ 600TBW trở lên cho bản 1TB để đảm bảo tuổi thọ dữ liệu trong nhiều năm sử dụng.

## 3. Lời kết

Nếu bạn thường xuyên xử lý đồ họa 4K hoặc chơi các tựa game thế giới mở dung lượng lớn, việc nâng cấp lên SSD NVMe PCIe 4.0 là hoàn toàn xứng đáng với chi phí bỏ ra.
MD,
        'image' => 'assets/images/placeholder.jpg',
        'category_slug' => 'pc-linh-kien',
        'post_type' => 'guide',
        'author_name' => 'Đội ngũ TechPilot',
        'status' => 'published',
        'views' => 210,
        'is_featured' => 0,
        'reading_minutes' => 5,
        'created_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
    ],
    [
        'title' => 'Đánh giá chi tiết Intel Core Ultra 9 285K: Bước tiến hay bước lùi?',
        'slug' => 'danh-gia-chi-tiet-intel-core-ultra-9-285k',
        'summary' => 'Phân tích kiến trúc Arrow Lake, hiệu năng đa nhân, NPU tích hợp và điện năng tiêu thụ của Intel Core Ultra 9 285K.',
        'content' => <<<'MD'
:::summary
- Kiến trúc Arrow Lake hoàn toàn mới giúp giảm tới 40% điện năng tiêu thụ so với i9-14900K.
- Tích hợp NPU xử lý trí tuệ nhân tạo chuyên dụng.
- Thay đổi socket sang LGA 1851 yêu cầu nâng cấp bo mạch chủ mới.
:::

## 1. Giới thiệu kiến trúc Arrow Lake của Intel

Vi xử lý **Intel Core Ultra 9 285K** đánh dấu bước chuyển mình quan trọng của Intel khi từ bỏ thương hiệu "Core i" quen thuộc để chuyển sang dòng "Core Ultra" thế hệ mới với tiến trình sản xuất tiên tiến và thiết kế Tile/Chiplet.

## 2. Thông số kỹ thuật và điểm mới

Core Ultra 9 285K được trang bị 24 nhân 24 luồng (8 nhân Lion Cove P-Core và 16 nhân Skymont E-Core). Đáng chú ý, Intel đã loại bỏ công nghệ Hyper-Threading để tập trung tối ưu hóa hiệu quả năng lượng và xung nhịp đơn nhân.

## 3. Hiệu năng và nhiệt độ

Điểm ấn tượng nhất trên Core Ultra 9 285K là nhiệt độ hoạt động cực kỳ mát mẻ. Ngay cả khi vắt cạn hiệu năng trong ứng dụng đồ họa 3D, mức tiêu thụ điện năng chỉ dao động ở mức 190W - 220W, thấp hơn rất nhiều so với mức 300W+ của thế hệ 14900K cũ.
MD,
        'image' => 'assets/images/placeholder.jpg',
        'category_slug' => 'pc-linh-kien',
        'post_type' => 'review',
        'author_name' => 'Đội ngũ TechPilot',
        'status' => 'published',
        'views' => 420,
        'is_featured' => 1,
        'reading_minutes' => 6,
        'created_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
    ],
    [
        'title' => 'NVIDIA RTX 50 Series chính thức lộ diện: Bước nhảy vọt về AI và Ray Tracing',
        'slug' => 'nvidia-rtx-50-series-chinh-thuc-lo-dien',
        'summary' => 'Đánh giá kiến trúc Blackwell, DLSS 4 và hiệu năng xử lý đồ họa thế hệ mới của NVIDIA GeForce RTX 5090 và RTX 5080.',
        'content' => <<<'MD'
:::summary
- Kiến trúc Blackwell mang lại hiệu năng Ray Tracing gấp 2 lần thế hệ Ada Lovelace.
- Bộ nhớ GDDR7 băng thông siêu khủng cho trải nghiệm game 4K mượt mà.
- Công nghệ DLSS 4 nâng tầm khả năng dựng hình bằng AI.
:::

## 1. Tổng quan thế hệ card đồ họa NVIDIA GeForce RTX 50 Series

NVIDIA chính thức ra mắt dòng card đồ họa thế hệ mới **GeForce RTX 50 Series** dựa trên kiến trúc **Blackwell**. Đây là sản phẩm đột phá hứa hẹn làm thay đổi hoàn toàn trải nghiệm chơi game 4K và các ứng dụng sáng tạo AI.

## 2. Những cải tiến công nghệ vượt trội

- **Bộ nhớ GDDR7:** Mang lại tốc độ truyền dữ liệu chưa từng có, giải quyết triệt để nút thắt băng thông VRAM khi chơi game độ phân giải siêu cao.
- **DLSS 4 Multi-Frame Generation:** Ứng dụng mô hình AI thế hệ mới giúp tạo ra các khung hình chất lượng cao mà không làm tăng độ trễ đầu vào.
- **Ray Tracing thế hệ thứ 4:** Tăng tốc độ tính toán ánh sáng và bóng đổ theo thời gian thực một cách chân thực nhất.
MD,
        'image' => 'assets/images/placeholder.jpg',
        'category_slug' => 'pc-linh-kien',
        'post_type' => 'news',
        'author_name' => 'Đội ngũ TechPilot',
        'status' => 'published',
        'views' => 530,
        'is_featured' => 1,
        'reading_minutes' => 5,
        'created_at' => date('Y-m-d H:i:s', strtotime('-6 days')),
    ],
    [
        'title' => 'Đánh giá SSD NVMe PCIe 4.0 năm 2026: Tốc độ thực tế có đáng để nâng cấp?',
        'slug' => 'ssd-nvme-pcie-4-co-dang-mua',
        'summary' => 'Phân tích tốc độ đọc ghi, độ bền TBW và hiệu năng thực tế của ổ cứng SSD NVMe PCIe Gen 4 khi chơi game và làm đồ họa.',
        'content' => <<<'MD'
:::summary
- Tốc độ đọc ghi lý thuyết đạt từ 5000 MB/s đến 7400 MB/s.
- Thời gian load game được rút ngắn đáng kể nhờ công nghệ DirectStorage.
- Giá thành ổ SSD PCIe 4.0 hiện đã tiệm cận chuẩn PCIe 3.0 cũ.
:::

## 1. SSD NVMe PCIe 4.0 là gì?

Ổ cứng SSD NVMe chuẩn **PCIe Generation 4.0** mang lại băng thông cao gấp đôi so với chuẩn Gen 3.0 tiền nhiệm, cho phép tốc độ truy xuất dữ liệu đạt mức kỷ lục tới 7500 MB/s.

## 2. Hiệu năng thực tế khi chơi game và làm việc

Trong các tác vụ thường ngày như khởi động Windows hay mở ứng dụng văn phòng, sự khác biệt giữa Gen 3 và Gen 4 khó nhận biết bằng mắt thường. Tuy nhiên, khi render video 4K dung lượng lớn hoặc nạp các bản đồ game nặng chuẩn DirectStorage, ổ SSD PCIe 4.0 giúp tiết kiệm hàng chục giây chờ đợi.

## 3. Kết luận

Với mức giá đã giảm rất sâu trong năm 2026, SSD NVMe PCIe 4.0 chính là lựa chọn tối ưu nhất cho cả người dùng nâng cấp lẫn build PC mới.
MD,
        'image' => 'assets/images/placeholder.jpg',
        'category_slug' => 'pc-linh-kien',
        'post_type' => 'review',
        'author_name' => 'Đội ngũ TechPilot',
        'status' => 'published',
        'views' => 140,
        'is_featured' => 0,
        'reading_minutes' => 4,
        'created_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
    ]
];

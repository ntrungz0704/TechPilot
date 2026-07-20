<?php

return [
    'categories' => [
        ['slug' => 'tu-van-laptop', 'name' => 'Tư vấn Laptop'],
        ['slug' => 'build-pc', 'name' => 'Kinh nghiệm Build PC'],
        ['slug' => 'danh-gia-san-pham', 'name' => 'Đánh giá sản phẩm'],
    ],
    'articles' => [
        [
            'id' => 1,
            'slug' => 'co-nen-mua-laptop-cu-khong',
            'title' => 'Có nên mua laptop cũ không? Những điều cần kiểm tra trước khi mua',
            'excerpt' => 'Mua laptop cũ giúp bạn tiết kiệm được một khoản tiền lớn nhưng cũng tiềm ẩn nhiều rủi ro. Bài viết này sẽ phân tích chi tiết ưu, nhược điểm và hướng dẫn bạn cách kiểm tra laptop cũ từ A-Z.',
            'category' => [
                'name' => 'Tư vấn Laptop',
                'slug' => 'tu-van-laptop',
            ],
            'author' => [
                'name' => 'TechPilot Editorial',
            ],
            'published_at' => '2026-07-20',
            'updated_at' => '2026-07-20',
            'reading_time' => 8,
            'featured_image' => 'https://images.unsplash.com/photo-1593642632823-8f785ba67e45?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80',
            'key_takeaways' => [
                'Laptop cũ có thể tiết kiệm 30-50% chi phí so với mua mới, nhưng rủi ro hỏng hóc cao hơn.',
                'Cần kiểm tra kỹ ngoại hình, pin, màn hình, bàn phím, ổ cứng và nhiệt độ.',
                'Không chỉ nhìn tên CPU (Core i5, i7); cần xem đúng thế hệ chip để đánh giá sức mạnh thực tế.',
                'Ưu tiên chọn mua ở những nơi uy tín, có chính sách bảo hành rõ ràng và cho phép đổi trả.'
            ],
            'sections' => [
                [
                    'id' => 'uu-nhuoc-diem',
                    'heading' => 'Ưu và nhược điểm khi mua laptop cũ',
                    'content' => '<p>Việc chọn mua một chiếc laptop đã qua sử dụng luôn là một bài toán cân nhắc giữa tài chính và rủi ro. Dưới đây là những điểm bạn cần lưu ý:</p>
                                  <p><strong>Ưu điểm:</strong> Lợi ích lớn nhất là giá thành. Bạn có thể sở hữu một chiếc máy tính cấu hình cao với mức giá chỉ bằng 50-70% so với máy mới. Điều này đặc biệt hữu ích đối với sinh viên hoặc người đi làm có ngân sách eo hẹp.</p>
                                  <p><strong>Nhược điểm:</strong> Rủi ro về linh kiện là không thể tránh khỏi. Pin có thể đã bị chai, ổ cứng có thể có bad sector, và ngoại hình máy thường không còn hoàn hảo. Quan trọng nhất là thời gian bảo hành thường ngắn hoặc không có.</p>'
                ],
                [
                    'id' => 'nhung-dieu-can-kiem-tra',
                    'heading' => 'Những điều cần kiểm tra trước khi xuống tiền',
                    'content' => '<ul>
                                    <li><strong>Ngoại hình:</strong> Kiểm tra các góc cạnh, bản lề xem có nứt vỡ hay lỏng lẻo không.</li>
                                    <li><strong>Màn hình:</strong> Mở các hình nền đơn sắc (trắng, đen, đỏ, xanh) để kiểm tra điểm chết và hở sáng.</li>
                                    <li><strong>Bàn phím & Touchpad:</strong> Gõ thử tất cả các phím, sử dụng touchpad xem có mượt mà và nhận đa điểm tốt không.</li>
                                    <li><strong>Pin & Sạc:</strong> Kiểm tra độ chai pin bằng các phần mềm chuyên dụng như BatteryMon. Cắm sạc để đảm bảo máy nhận sạc bình thường.</li>
                                    <li><strong>Cổng kết nối:</strong> Cắm thử USB, tai nghe, HDMI vào tất cả các cổng.</li>
                                  </ul>'
                ],
                [
                    'id' => 'dia-chi-mua-uy-tin',
                    'heading' => 'Lựa chọn nơi mua uy tín',
                    'content' => '<p>Để giảm thiểu rủi ro, hãy ưu tiên những cửa hàng lớn, có uy tín và chính sách bảo hành rõ ràng. TechPilot luôn tự hào mang đến những sản phẩm chất lượng với chế độ hậu mãi tận tâm, giúp bạn an tâm tuyệt đối.</p>'
                ]
            ],
            'comparison' => [
                'title' => 'So sánh Laptop cũ và Laptop mới',
                'criteria' => ['Chi phí ban đầu', 'Hiệu năng/Giá', 'Tình trạng pin', 'Rủi ro linh kiện', 'Bảo hành', 'Độ ổn định dài hạn'],
                'old_laptop' => ['Thấp (Tiết kiệm 30-50%)', 'Cao', 'Thường bị chai một phần', 'Cao', 'Ngắn (1-3 tháng) hoặc không có', 'Phụ thuộc vào người dùng trước'],
                'new_laptop' => ['Cao', 'Thấp hơn (Cùng mức tiền)', '100% nguyên bản', 'Rất thấp', 'Chính hãng (1-2 năm)', 'Cao, ít lỗi vặt']
            ],
            'faq' => [
                [
                    'question' => 'Laptop cũ phù hợp với những ai?',
                    'answer' => 'Laptop cũ rất phù hợp với sinh viên, người mới đi làm hoặc những ai có ngân sách hạn chế nhưng cần một chiếc máy có cấu hình đủ tốt để phục vụ học tập, công việc cơ bản hoặc chơi game nhẹ.'
                ],
                [
                    'question' => 'Nên kiểm tra pin laptop cũ như thế nào?',
                    'answer' => 'Bạn có thể dùng lệnh "powercfg /batteryreport" trên Windows hoặc các phần mềm như BatteryMon, HWMonitor để xem dung lượng thiết kế (Design Capacity) và dung lượng sạc đầy hiện tại (Full Charge Capacity). Nếu độ chai quá 30%, bạn nên yêu cầu thay pin mới hoặc giảm giá.'
                ],
                [
                    'question' => 'Laptop cũ có được bảo hành không?',
                    'answer' => 'Tùy thuộc vào nơi bán. Các cửa hàng uy tín thường bảo hành từ 1 đến 6 tháng cho laptop cũ. Khi mua từ cá nhân, bạn thường phải chấp nhận rủi ro không có bảo hành.'
                ],
                [
                    'question' => 'Nên mua laptop cũ hay laptop mới giá rẻ?',
                    'answer' => 'Nếu cùng tầm tiền (ví dụ 10 triệu), laptop cũ sẽ có cấu hình vượt trội (chip i5, i7 đời cao) và build kim loại bền bỉ. Trong khi đó, laptop mới chỉ là dòng giá rẻ vỏ nhựa, chip Celeron/Pentium yếu. Do đó mua laptop cũ uy tín sẽ tốt hơn về hiệu năng.'
                ],
                [
                    'question' => 'Cần kiểm tra những cổng kết nối nào?',
                    'answer' => 'Đảm bảo cắm USB, tai nghe 3.5mm, sạc pin và cổng xuất hình HDMI/Type-C đều hoạt động ổn định không lỏng lẻo.'
                ]
            ],
            'tags' => ['Laptop cũ', 'Kinh nghiệm', 'Tư vấn mua hàng', 'TechPilot'],
            'recommended_products' => [1, 2, 3]
        ],
        [
            'id' => 2,
            'slug' => 'huong-dan-build-pc-cho-nguoi-moi',
            'title' => 'Hướng dẫn Build PC cho người mới bắt đầu từ A-Z',
            'excerpt' => 'Tự lắp ráp một dàn PC không khó như bạn nghĩ. Hãy cùng TechPilot khám phá các bước cơ bản để tự tay build một bộ PC ưng ý.',
            'category' => [
                'name' => 'Kinh nghiệm Build PC',
                'slug' => 'build-pc',
            ],
            'author' => [
                'name' => 'TechPilot Editorial',
            ],
            'published_at' => '2026-07-18',
            'updated_at' => '2026-07-18',
            'reading_time' => 10,
            'featured_image' => 'https://images.unsplash.com/photo-1587202372775-e229f172b9d7?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80',
            'key_takeaways' => [],
            'sections' => [],
            'faq' => [],
            'tags' => ['Build PC', 'Linh kiện', 'Hướng dẫn'],
        ],
        [
            'id' => 3,
            'slug' => 'danh-gia-nhanh-rtx-4060',
            'title' => 'Đánh giá nhanh card đồ họa RTX 4060: Có đáng tiền?',
            'excerpt' => 'RTX 4060 ra mắt với nhiều kỳ vọng. Liệu hiệu năng thực tế của chiếc card này có đáp ứng được nhu cầu của game thủ tầm trung?',
            'category' => [
                'name' => 'Đánh giá sản phẩm',
                'slug' => 'danh-gia-san-pham',
            ],
            'author' => [
                'name' => 'TechPilot Editorial',
            ],
            'published_at' => '2026-07-15',
            'updated_at' => '2026-07-15',
            'reading_time' => 6,
            'featured_image' => 'https://images.unsplash.com/photo-1591488320449-011701bb6704?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80',
            'key_takeaways' => [],
            'sections' => [],
            'faq' => [],
            'tags' => ['VGA', 'NVIDIA', 'RTX 4060', 'Gaming'],
        ]
    ]
];

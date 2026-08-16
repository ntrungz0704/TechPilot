/**
 * TechPilot Vietnam Administrative Divisions & Phone Validation Module
 * Hỗ trợ 63 Tỉnh/Thành phố, Quận/Huyện, Phường/Xã và chuẩn hóa Số điện thoại +84 (10-11 số)
 */

(function(window) {
    'use strict';

    // Dữ liệu 63 Tỉnh / Thành phố và các Quận / Huyện, Phường / Xã phổ biến
    const VIETNAM_ADDRESS_DATA = [
        {
            name: "Hà Nội",
            districts: [
                { name: "Quận Ba Đình", wards: ["Phường Cống Vị", "Phường Điện Biên", "Phường Đội Cấn", "Phường Giảng Võ", "Phường Kim Mã", "Phường Liễu Giai", "Phường Ngọc Hà", "Phường Ngọc Khánh", "Phường Nguyễn Trung Trực", "Phường Phúc Xá", "Phường Quán Thánh", "Phường Thành Công", "Phường Trúc Bạch", "Phường Vĩnh Phúc"] },
                { name: "Quận Hoàn Kiếm", wards: ["Phường Chương Dương", "Phường Cửa Đông", "Phường Cửa Nam", "Phường Đồng Xuân", "Phường Hàng Bạc", "Phường Hàng Bài", "Phường Hàng Bồ", "Phường Hàng Bông", "Phường Hàng Buồm", "Phường Hàng Đào", "Phường Hàng Gai", "Phường Hàng Mã", "Phường Hàng Trống", "Phường Lý Thái Tổ", "Phường Phan Chu Trinh", "Phường Phúc Tân", "Phường Tràng Tiền", "Phường Trần Hưng Đạo"] },
                { name: "Quận Tây Hồ", wards: ["Phường Bưởi", "Phường Nhật Tân", "Phường Phú Thượng", "Phường Quảng An", "Phường Thụy Khuê", "Phường Tứ Liên", "Phường Xuân La", "Phường Yên Phụ"] },
                { name: "Quận Long Biên", wards: ["Phường Bồ Đề", "Phường Cự Khối", "Phường Đức Giang", "Phường Gia Thụy", "Phường Giang Biên", "Phường Long Biên", "Phường Ngọc Lâm", "Phường Ngọc Thụy", "Phường Phúc Đồng", "Phường Phúc Lợi", "Phường Sài Đồng", "Phường Thạch Bàn", "Phường Thượng Thanh", "Phường Việt Hưng"] },
                { name: "Quận Cầu Giấy", wards: ["Phường Dịch Vọng", "Phường Dịch Vọng Hậu", "Phường Mai Dịch", "Phường Nghĩa Đô", "Phường Nghĩa Tân", "Phường Quan Hoa", "Phường Trung Hòa", "Phường Yên Hòa"] },
                { name: "Quận Đống Đa", wards: ["Phường Cát Linh", "Phường Hàng Bột", "Phường Khâm Thiên", "Phường Khương Thượng", "Phường Kim Liên", "Phường Láng Hạ", "Phường Láng Thượng", "Phường Nam Đồng", "Phường Ngã Tư Sở", "Phường Ô Chợ Dừa", "Phường Phương Liên", "Phường Phương Mai", "Phường Quang Trung", "Phường Quốc Tử Giám", "Phường Thịnh Quang", "Phường Thổ Quan", "Phường Trung Liệt", "Phường Trung Phụng", "Phường Trung Tự", "Phường Văn Chương", "Phường Văn Miếu"] },
                { name: "Quận Hai Bà Trưng", wards: ["Phường Bạch Đằng", "Phường Bách Khoa", "Phường Bạch Mai", "Phường Cầu Dền", "Phường Đống Mác", "Phường Đồng Nhân", "Phường Đồng Tâm", "Phường Lê Đại Hành", "Phường Minh Khai", "Phường Nguyễn Du", "Phường Phạm Đình Hổ", "Phường Phố Huế", "Phường Quỳnh Lôi", "Phường Quỳnh Mai", "Phường Thanh Lương", "Phường Thanh Nhàn", "Phường Trương Định", "Phường Vĩnh Tuy"] },
                { name: "Quận Hoàng Mai", wards: ["Phường Đại Kim", "Phường Định Công", "Phường Giáp Bát", "Phường Hoàng Liệt", "Phường Hoàng Văn Thụ", "Phường Lĩnh Nam", "Phường Mai Động", "Phường Tân Mai", "Phường Thanh Trì", "Phường Thịnh Liệt", "Phường Trần Phú", "Phường Tương Mai", "Phường Vĩnh Hưng", "Phường Yên Sở"] },
                { name: "Quận Thanh Xuân", wards: ["Phường Hạ Đình", "Phường Khương Đình", "Phường Khương Mai", "Phường Khương Trung", "Phường Kim Giang", "Phường Nhân Chính", "Phường Phương Liệt", "Phường Thanh Xuân Bắc", "Phường Thanh Xuân Nam", "Phường Thanh Xuân Trung", "Phường Thượng Đình"] },
                { name: "Quận Nam Từ Liêm", wards: ["Phường Cầu Diễn", "Phường Đại Mỗ", "Phường Mễ Trì", "Phường Mỹ Đình 1", "Phường Mỹ Đình 2", "Phường Phú Đô", "Phường Tây Mỗ", "Phường Phương Canh", "Phường Trung Văn", "Phường Xuân Phương"] },
                { name: "Quận Bắc Từ Liêm", wards: ["Phường Cổ Nhuế 1", "Phường Cổ Nhuế 2", "Phường Đông Ngạc", "Phường Đức Thắng", "Phường Liên Mạc", "Phường Minh Khai", "Phường Phú Diễn", "Phường Phúc Diễn", "Phường Tây Tựu", "Phường Thượng Cát", "Phường Thụy Phương", "Phường Xuân Đỉnh", "Phường Xuân Tảo"] },
                { name: "Quận Hà Đông", wards: ["Phường Biên Giang", "Phường Đồng Mai", "Phường Dương Nội", "Phường Hà Cầu", "Phường Kiến Hưng", "Phường La Khê", "Phường Mộ Lao", "Phường Nguyễn Trãi", "Phường Phú La", "Phường Phú Lãm", "Phường Phú Lương", "Phường Phúc La", "Phường Quang Trung", "Phường Vạn Phúc", "Phường Văn Quán", "Phường Yên Nghĩa", "Phường Yết Kiêu"] },
                { name: "Thị xã Sơn Tây", wards: ["Phường Lê Lợi", "Phường Ngô Quyền", "Phường Phú Thịnh", "Phường Quang Trung", "Phường Sơn Lộc", "Phường Trung Hưng", "Phường Trung Sơn Trầm", "Phường Xuân Khanh", "Xã Cổ Đông", "Xã Đường Lâm", "Xã Kim Sơn", "Xã Sơn Đông", "Xã Thanh Mỹ", "Xã Xuân Sơn"] },
                { name: "Huyện Đông Anh", wards: ["Thị trấn Đông Anh", "Xã Bắc Hồng", "Xã Cổ Loa", "Xã Dục Tú", "Xã Hải Bối", "Xã Kim Chung", "Xã Kim Nỗ", "Xã Mai Lâm", "Xã Nam Hồng", "Xã Nguyên Khê", "Xã Tàm Xá", "Xã Thụy Lâm", "Xã Tiên Dương", "Xã Uy Nỗ", "Xã Vân Hà", "Xã Vân Nội", "Xã Vĩnh Ngọc", "Xã Võng La", "Xã Xuân Canh", "Xã Xuân Nộn"] },
                { name: "Huyện Gia Lâm", wards: ["Thị trấn Trâu Quỳ", "Thị trấn Yên Viên", "Xã Bát Tràng", "Xã Cổ Bi", "Xã Đa Tốn", "Xã Đặng Xá", "Xã Đình Xuyên", "Xã Đông Dư", "Xã Dương Hà", "Xã Dương Quang", "Xã Dương Xá", "Xã Kiêu Kỵ", "Xã Kim Lan", "Xã Kim Sơn", "Xã Lệ Chi", "Xã Ninh Hiệp", "Xã Phù Đổng", "Xã Phú Thị", "Xã Trung Mầu", "Xã Văn Đức", "Xã Yên Thường", "Xã Yên Viên"] },
                { name: "Huyện Sóc Sơn", wards: ["Thị trấn Sóc Sơn", "Xã Bắc Phú", "Xã Bắc Sơn", "Xã Đông Xuân", "Xã Đức Hòa", "Xã Hiền Ninh", "Xã Hồng Kỳ", "Xã Kim Lũ", "Xã Mai Đình", "Xã Minh Phú", "Xã Minh Trí", "Xã Nam Sơn", "Xã Phú Cường", "Xã Phù Linh", "Xã Phù Lỗ", "Xã Phú Minh", "Xã Quang Tiến", "Xã Tân Dân", "Xã Tân Hưng", "Xã Tân Minh", "Xã Thanh Xuân", "Xã Tiên Dược", "Xã Trung Giã", "Xã Việt Long", "Xã Xuân Giang", "Xã Xuân Thu"] },
                { name: "Huyện Thanh Trì", wards: ["Thị trấn Văn Điển", "Xã Đại Áng", "Xã Đông Mỹ", "Xã Duyên Hà", "Xã Hữu Hòa", "Xã Liên Ninh", "Xã Ngọc Hồi", "Xã Ngũ Hiệp", "Xã Tả Thanh Oai", "Xã Tam Hiệp", "Xã Tân Triều", "Xã Thanh Liệt", "Xã Tứ Hiệp", "Xã Vạn Phúc", "Xã Vĩnh Quỳnh", "Xã Yên Mỹ"] },
                { name: "Huyện Mê Linh", wards: ["Thị trấn Chi Đông", "Thị trấn Quang Minh", "Xã Chu Phan", "Xã Đại Thịnh", "Xã Hoàng Kim", "Xã Kim Hoa", "Xã Liên Mạc", "Xã Mê Linh", "Xã Tam Đồng", "Xã Thạch Đà", "Xã Thanh Lâm", "Xã Tiền Phong", "Xã Tiến Thắng", "Xã Tiến Thịnh", "Xã Tự Lập", "Xã Tráng Việt", "Xã Văn Khê", "Xã Vạn Yên"] },
                { name: "Huyện Hoài Đức", wards: ["Thị trấn Trạm Trôi", "Xã An Khánh", "Xã An Thượng", "Xã Cát Quế", "Xã Đắc Sở", "Xã Di Trạch", "Xã Đông La", "Xã Đức Giang", "Xã Đức Thượng", "Xã Dương Liễu", "Xã Kim Chung", "Xã La Phù", "Xã Lại Yên", "Xã Minh Khai", "Xã Song Phương", "Xã Tiền Yên", "Xã Vân Canh", "Xã Vân Côn", "Xã Yên Sở"] },
                { name: "Huyện Đan Phượng", wards: ["Thị trấn Phùng", "Xã Đan Phượng", "Xã Đồng Tháp", "Xã Hạ Mỗ", "Xã Hồng Hà", "Xã Liên Hà", "Xã Liên Hồng", "Xã Liên Trung", "Xã Phương Đình", "Xã Song Phượng", "Xã Tân Hội", "Xã Tân Lập", "Xã Thọ An", "Xã Thọ Xuân", "Xã Thượng Mỗ", "Xã Trung Châu"] },
                { name: "Huyện Thạch Thất", wards: ["Thị trấn Liên Quan", "Xã Bình Phú", "Xã Bình Yên", "Xã Cẩm Yên", "Xã Cần Kiệm", "Xã Canh Nậu", "Xã Chàng Sơn", "Xã Đại Đồng", "Xã Dị Nậu", "Xã Đồng Trúc", "Xã Hạ Bằng", "Xã Hương Ngải", "Xã Hữu Bằng", "Xã Kim Quan", "Xã Lại Thượng", "Xã Phú Kim", "Xã Phùng Xá", "Xã Tân Xã", "Xã Thạch Hòa", "Xã Thạch Xá", "Xã Tiến Xuân", "Xã Yên Bình", "Xã Yên Trung"] },
                { name: "Huyện Chương Mỹ", wards: ["Thị trấn Chúc Sơn", "Thị trấn Xuân Mai", "Xã Đại Yên", "Xã Đồng Lạc", "Xã Đồng Phú", "Xã Đông Phương Yên", "Xã Đông Sơn", "Xã Hòa Chính", "Xã Hoàng Diệu", "Xã Hoàng Văn Thụ", "Xã Hữu Văn", "Xã Lam Điền", "Xã Mỹ Lương", "Xã Nam Phương Tiến", "Xã Ngọc Hòa", "Xã Phú Nam An", "Xã Phú Nghĩa", "Xã Phụng Châu", "Xã Quảng Bị", "Xã Tân Tiến", "Xã Tiên Phương", "Xã Tốt Động", "Xã Thanh Bình", "Xã Thụy Hương", "Xã Thủy Xuân Tiên", "Xã Thượng Vực", "Xã Trần Phú", "Xã Trung Hòa", "Xã Trường Yên", "Xã Văn Võ", "Xã Võng La"] },
                { name: "Huyện Thường Tín", wards: ["Thị trấn Thường Tín", "Xã Chương Dương", "Xã Dũng Tiến", "Xã Duyên Thái", "Xã Hà Hồi", "Xã Hiền Giang", "Xã Hòa Bình", "Xã Khánh Hà", "Xã Hồng Vân", "Xã Liên Phương", "Xã Minh Cường", "Xã Nghiêm Xuyên", "Xã Nguyễn Trãi", "Xã Nhị Khê", "Xã Ninh Sở", "Xã Quất Động", "Xã Tân Minh", "Xã Thắng Lợi", "Xã Thống Nhất", "Xã Thư Phú", "Xã Tiền Phong", "Xã Tô Hiệu", "Xã Tự Nhiên", "Xã Vạn Điểm", "Xã Văn Bình", "Xã Văn Phú", "Xã Văn Tự", "Xã Vân Tảo"] }
            ]
        },
        {
            name: "Hồ Chí Minh",
            districts: [
                { name: "Quận 1", wards: ["Phường Bến Nghé", "Phường Bến Thành", "Phường Cầu Kho", "Phường Cầu Ông Lãnh", "Phường Cô Giang", "Phường Đa Kao", "Phường Nguyễn Cư Trinh", "Phường Nguyễn Thái Bình", "Phường Phạm Ngũ Lão", "Phường Tân Định"] },
                { name: "Quận 3", wards: ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 9", "Phường 10", "Phường 11", "Phường 12", "Phường 13", "Phường 14", "Phường Võ Thị Sáu"] },
                { name: "Quận 4", wards: ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 6", "Phường 8", "Phường 9", "Phường 10", "Phường 13", "Phường 14", "Phường 15", "Phường 16", "Phường 18"] },
                { name: "Quận 5", wards: ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 12", "Phường 13", "Phường 14"] },
                { name: "Quận 6", wards: ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 12", "Phường 13", "Phường 14"] },
                { name: "Quận 7", wards: ["Phường Bình Thuận", "Phường Phú Mỹ", "Phường Phú Thuận", "Phường Tân Hưng", "Phường Tân Kiểng", "Phường Tân Phong", "Phường Tân Phú", "Phường Tân Quy", "Phường Tân Thuận Đông", "Phường Tân Thuận Tây"] },
                { name: "Quận 8", wards: ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 12", "Phường 13", "Phường 14", "Phường 15", "Phường 16"] },
                { name: "Quận 10", wards: ["Phường 1", "Phường 2", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 12", "Phường 13", "Phường 14", "Phường 15"] },
                { name: "Quận 11", wards: ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 12", "Phường 13", "Phường 14", "Phường 15", "Phường 16"] },
                { name: "Quận 12", wards: ["Phường An Phú Đông", "Phường Đông Hưng Thuận", "Phường Hiệp Thành", "Phường Tân Chánh Hiệp", "Phường Tân Hưng Thuận", "Phường Tân Thới Hiệp", "Phường Tân Thới Nhất", "Phường Thạnh Lộc", "Phường Thạnh Xuân", "Phường Thới An", "Phường Trung Mỹ Tây"] },
                { name: "Thành phố Thủ Đức", wards: ["Phường An Khánh", "Phường An Lợi Đông", "Phường An Phú", "Phường Bình Chiểu", "Phường Bình Thọ", "Phường Cát Lái", "Phường Hiệp Bình Chánh", "Phường Hiệp Bình Phước", "Phường Hiệp Phú", "Phường Linh Chiểu", "Phường Linh Đông", "Phường Linh Tây", "Phường Linh Trung", "Phường Linh Xuân", "Phường Long Bình", "Phường Long Phước", "Phường Long Thạnh Mỹ", "Phường Long Trường", "Phường Phú Hữu", "Phường Phước Bình", "Phường Phước Long A", "Phường Phước Long B", "Phường Tam Bình", "Phường Tam Phú", "Phường Tân Phú", "Phường Tăng Nhơn Phú A", "Phường Tăng Nhơn Phú B", "Phường Thạnh Mỹ Lợi", "Phường Thảo Điền", "Phường Thủ Thiêm", "Phường Trường Thạnh", "Phường Trường Thọ"] },
                { name: "Quận Bình Thạnh", wards: ["Phường 1", "Phường 2", "Phường 3", "Phường 5", "Phường 6", "Phường 7", "Phường 11", "Phường 12", "Phường 13", "Phường 14", "Phường 15", "Phường 17", "Phường 19", "Phường 21", "Phường 22", "Phường 24", "Phường 25", "Phường 26", "Phường 27", "Phường 28"] },
                { name: "Quận Gò Vấp", wards: ["Phường 1", "Phường 3", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 12", "Phường 13", "Phường 14", "Phường 15", "Phường 16", "Phường 17"] },
                { name: "Quận Phú Nhuận", wards: ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 13", "Phường 15", "Phường 17"] },
                { name: "Quận Tân Bình", wards: ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 12", "Phường 13", "Phường 14", "Phường 15"] },
                { name: "Quận Tân Phú", wards: ["Phường Hiệp Tân", "Phường Hòa Thạnh", "Phường Phú Thạnh", "Phường Phú Thọ Hòa", "Phường Phú Trung", "Phường Sơn Kỳ", "Phường Tân Quý", "Phường Tân Sơn Nhì", "Phường Tân Thành", "Phường Tân Thới Hòa", "Phường Tây Thạnh"] },
                { name: "Quận Bình Tân", wards: ["Phường An Lạc", "Phường An Lạc A", "Phường Bình Hưng Hòa", "Phường Bình Hưng Hòa A", "Phường Bình Hưng Hòa B", "Phường Bình Trị Đông", "Phường Bình Trị Đông A", "Phường Bình Trị Đông B", "Phường Tân Tạo", "Phường Tân Tạo A"] },
                { name: "Huyện Bình Chánh", wards: ["Thị trấn Tân Túc", "Xã An Phú Tây", "Xã Bình Chánh", "Xã Bình Hưng", "Xã Bình Lợi", "Xã Đa Phước", "Xã Hưng Long", "Xã Lê Minh Xuân", "Xã Phạm Văn Hai", "Xã Phong Phú", "Xã Quy Đức", "Xã Tân Kiên", "Xã Tân Nhựt", "Xã Tân Quý Tây", "Xã Vĩnh Lộc A", "Xã Vĩnh Lộc B"] },
                { name: "Huyện Hóc Môn", wards: ["Thị trấn Hóc Môn", "Xã Bà Điểm", "Xã Đông Thạnh", "Xã Nhị Bình", "Xã Tân Hiệp", "Xã Tân Thới Nhì", "Xã Tân Xuân", "Xã Thới Tam Thôn", "Xã Trung Chánh", "Xã Xuân Thới Đông", "Xã Xuân Thới Sơn", "Xã Xuân Thới Thượng"] },
                { name: "Huyện Củ Chi", wards: ["Thị trấn Củ Chi", "Xã An Nhơn Tây", "Xã An Phú", "Xã Bình Mỹ", "Xã Hòa Phú", "Xã Nhuận Đức", "Xã Phạm Văn Cội", "Xã Phú Hòa Đông", "Xã Phú Mỹ Hưng", "Xã Phước Hiệp", "Xã Phước Thạnh", "Xã Phước Vĩnh An", "Xã Tân An Hội", "Xã Tân Thạnh Đông", "Xã Tân Thạnh Tây", "Xã Tân Thông Hội", "Xã Thái Mỹ", "Xã Trung An", "Xã Trung Lập Hạ", "Xã Trung Lập Thượng"] },
                { name: "Huyện Nhà Bè", wards: ["Thị trấn Nhà Bè", "Xã Hiệp Phước", "Xã Long Thới", "Xã Nhơn Đức", "Xã Phú Xuân", "Xã Phước Kiển", "Xã Phước Lộc"] },
                { name: "Huyện Cần Giờ", wards: ["Thị trấn Cần Thạnh", "Xã An Thới Đông", "Xã Bình Khánh", "Xã Long Hòa", "Xã Lý Nhơn", "Xã Tam Thôn Hiệp", "Xã Thạnh An"] }
            ]
        },
        {
            name: "Đà Nẵng",
            districts: [
                { name: "Quận Hải Châu", wards: ["Phường Bình Hiên", "Phường Bình Thuận", "Phường Hải Châu I", "Phường Hải Châu II", "Phường Hòa Cường Bắc", "Phường Hòa Cường Nam", "Phường Hòa Thuận Đông", "Phường Hòa Thuận Tây", "Phường Nam Dương", "Phường Phước Ninh", "Phường Thạch Thang", "Phường Thanh Bình", "Phường Thuận Phước"] },
                { name: "Quận Thanh Khê", wards: ["Phường An Khê", "Phường Chính Gián", "Phường Hòa Khê", "Phường Tam Thuận", "Phường Tân Chính", "Phường Thạc Gián", "Phường Thanh Khê Đông", "Phường Thanh Khê Tây", "Phường Vĩnh Trung", "Phường Xuân Hà"] },
                { name: "Quận Sơn Trà", wards: ["Phường An Hải Bắc", "Phường An Hải Đông", "Phường An Hải Tây", "Phường Mân Thái", "Phường Nại Hiên Đông", "Phường Phước Mỹ", "Phường Thọ Quang"] },
                { name: "Quận Ngũ Hành Sơn", wards: ["Phường Hòa Hải", "Phường Hòa Quý", "Phường Khuê Mỹ", "Phường Mỹ An"] },
                { name: "Quận Liên Chiểu", wards: ["Phường Hòa Hiệp Bắc", "Phường Hòa Hiệp Nam", "Phường Hòa Khánh Bắc", "Phường Hòa Khánh Nam", "Phường Hòa Minh"] },
                { name: "Quận Cẩm Lệ", wards: ["Phường Hòa An", "Phường Hòa Phát", "Phường Hòa Thọ Đông", "Phường Hòa Thọ Tây", "Phường Hòa Xuân", "Phường Khuê Trung"] },
                { name: "Huyện Hòa Vang", wards: ["Xã Hòa Bắc", "Xã Hòa Châu", "Xã Hòa Khương", "Xã Hòa Liên", "Xã Hòa Nhơn", "Xã Hòa Ninh", "Xã Hòa Phong", "Xã Hòa Phú", "Xã Hòa Phước", "Xã Hòa Sơn", "Xã Hòa Tiến"] },
                { name: "Huyện Hoàng Sa", wards: ["Đảo Hoàng Sa"] }
            ]
        },
        {
            name: "Hải Phòng",
            districts: [
                { name: "Quận Hồng Bàng", wards: ["Phường Hoàng Văn Thụ", "Phường Minh Khai", "Phường Phan Bội Châu", "Phường Quán Toan", "Phường Sở Dầu", "Phường Thượng Lý", "Phường Trại Chuối", "Phường Hùng Vương"] },
                { name: "Quận Ngô Quyền", wards: ["Phường Cầu Đất", "Phường Cầu Tre", "Phường Đằng Giang", "Phường Đông Khê", "Phường Đổng Quốc Bình", "Phường Gia Viên", "Phường Lạc Viên", "Phường Lạch Tray", "Phường Lê Lợi", "Phường Máy Chai", "Phường Máy Tơ", "Phường Vạn Mỹ"] },
                { name: "Quận Lê Chân", wards: ["Phường An Biên", "Phường An Dương", "Phường Cát Dài", "Phường Dư Hàng", "Phường Dư Hàng Kênh", "Phường Hàng Kênh", "Phường Hồ Nam", "Phường Kênh Dương", "Phường Lam Sơn", "Phường Niệm Nghĩa", "Phường Nghĩa Xá", "Phường Trại Cau", "Phường Trần Nguyên Hãn", "Phường Vĩnh Niệm"] },
                { name: "Quận Hải An", wards: ["Phường Cát Bi", "Phường Đằng Hải", "Phường Đằng Lâm", "Phường Đông Hải 1", "Phường Đông Hải 2", "Phường Nam Hải", "Phường Thành Tô", "Phường Tràng Cát"] },
                { name: "Quận Kiến An", wards: ["Phường Bắc Sơn", "Phường Đồng Hòa", "Phường Nam Sơn", "Phường Ngọc Sơn", "Phường Phù Liễn", "Phường Quán Trữ", "Phường Trần Thành Ngọ", "Phường Tràng Minh", "Phường Văn Đẩu"] },
                { name: "Quận Đồ Sơn", wards: ["Phường Bàng La", "Phường Hải Sơn", "Phường Hợp Đức", "Phường Minh Đức", "Phường Ngọc Xuyên", "Phường Vạn Hương"] },
                { name: "Quận Dương Kinh", wards: ["Phường Anh Dũng", "Phường Đa Phúc", "Phường Hải Thành", "Phường Hòa Nghĩa", "Phường Hưng Đạo", "Phường Tân Thành"] },
                { name: "Huyện Thủy Nguyên", wards: ["Thị trấn Núi Đèo", "Thị trấn Minh Đức", "Xã An Lư", "Xã Dương Quan", "Xã Hoa Động", "Xã Hoàng Động", "Xã Kiền Bái", "Xã Lâm Động", "Xã Lập Lễ", "Xã Lưu Kiếm", "Xã Phù Ninh", "Xã Quảng Thanh", "Xã Tam Hưng", "Xã Tân Dương", "Xã Thiên Hương", "Xã Thủy Đường", "Xã Thủy Triều"] },
                { name: "Huyện An Dương", wards: ["Thị trấn An Dương", "Xã An Đồng", "Xã An Hòa", "Xã An Hồng", "Xã An Hưng", "Xã Bắc Sơn", "Xã Đại Bản", "Xã Đặng Cương", "Xã Đồng Thái", "Xã Hồng Phong", "Xã Hồng Thái", "Xã Lê Lợi", "Xã Lê Thiện", "Xã Nam Sơn", "Xã Quốc Tuấn", "Xã Tân Tiến"] }
            ]
        },
        {
            name: "Cần Thơ",
            districts: [
                { name: "Quận Ninh Kiều", wards: ["Phường An Bình", "Phường An Cư", "Phường An Hòa", "Phường An Khánh", "Phường An Nghiệp", "Phường An Phú", "Phường Cái Khế", "Phường Hưng Lợi", "Phường Tân An", "Phường Thới Bình", "Phường Xuân Khánh"] },
                { name: "Quận Bình Thủy", wards: ["Phường An Thới", "Phường Bình Thủy", "Phường Bùi Hữu Nghĩa", "Phường Long Hòa", "Phường Long Tuyền", "Phường Thới An Đông", "Phường Trà An", "Phường Trà Nóc"] },
                { name: "Quận Cái Răng", wards: ["Phường Ba Láng", "Phường Hưng Phú", "Phường Hưng Thạnh", "Phường Lê Bình", "Phường Phú Thứ", "Phường Tân Phú", "Phường Yên Châu"] },
                { name: "Quận Ô Môn", wards: ["Phường Châu Văn Liêm", "Phường Phước Thới", "Phường Tân Hưng", "Phường Thới An", "Phường Thới Hòa", "Phường Thới Long", "Phường Trường Lạc"] },
                { name: "Quận Thốt Nốt", wards: ["Phường Tân Hưng", "Phường Tân Lộc", "Phường Thạnh Hòa", "Phường Thốt Nốt", "Phường Thới Thuận", "Phường Thuận An", "Phường Thuận Hưng", "Phường Trung Kiên", "Phường Trung Nhứt"] },
                { name: "Huyện Phong Điền", wards: ["Thị trấn Phong Điền", "Xã Giai Xuân", "Xã Mỹ Khánh", "Xã Nhơn Ái", "Xã Nhơn Nghĩa", "Xã Tân Thới", "Xã Trường Long"] }
            ]
        },
        {
            name: "Bình Dương",
            districts: [
                { name: "Thành phố Thủ Dầu Một", wards: ["Phường Chánh Mỹ", "Phường Chánh Nghĩa", "Phường Định Hòa", "Phường Hiệp An", "Phường Hiệp Thành", "Phường Hòa Phú", "Phường Phú Cường", "Phường Phú Hòa", "Phường Phú Lợi", "Phường Phú Mỹ", "Phường Phú Tân", "Phường Phú Thọ", "Phường Tân An", "Phường Tương Bình Hiệp"] },
                { name: "Thành phố Thuận An", wards: ["Phường An Phú", "Phường An Thạnh", "Phường Bình Chuẩn", "Phường Bình Hòa", "Phường Bình Nhâm", "Phường Hưng Định", "Phường Lái Thiêu", "Phường Thuận Giao", "Phường Vĩnh Phú", "Xã An Sơn"] },
                { name: "Thành phố Dĩ An", wards: ["Phường An Bình", "Phường Bình An", "Phường Bình Thắng", "Phường Dĩ An", "Phường Đông Hòa", "Phường Tân Bình", "Phường Tân Đông Hiệp"] },
                { name: "Thành phố Tân Uyên", wards: ["Phường Hội Nghĩa", "Phường Khánh Bình", "Phường Phú Chánh", "Phường Tân Hiệp", "Phường Tân Phước Khánh", "Phường Tân Vĩnh Hiệp", "Phường Thái Hòa", "Phường Thạnh Phước", "Phường Uyên Hưng", "Phường Vĩnh Tân", "Xã Bạch Đằng", "Xã Thạnh Hội"] },
                { name: "Thành phố Bến Cát", wards: ["Phường An Điền", "Phường An Tây", "Phường Chánh Phú Hòa", "Phường Hòa Lợi", "Phường Mỹ Phước", "Phường Tân Định", "Phường Thới Hòa", "Xã Phú An"] },
                { name: "Huyện Bàu Bàng", wards: ["Thị trấn Lai Uyên", "Xã Cây Trường II", "Xã Hưng Hòa", "Xã Lai Hưng", "Xã Long Nguyên", "Xã Tân Hưng", "Xã Trừ Văn Thố"] },
                { name: "Huyện Bắc Tân Uyên", wards: ["Thị trấn Tân Thành", "Thị trấn Tân Bình", "Xã Bình Mỹ", "Xã Đất Cuốc", "Xã Hiếu Liêm", "Xã Lạc An", "Xã Tân Định", "Xã Tân Lập", "Xã Tân Mỹ", "Xã Thường Tân"] }
            ]
        },
        {
            name: "Đồng Nai",
            districts: [
                { name: "Thành phố Biên Hòa", wards: ["Phường An Bình", "Phường An Hòa", "Phường Bình Đa", "Phường Bửu Hòa", "Phường Bửu Long", "Phường Hiệp Hòa", "Phường Hóa An", "Phường Hòa Bình", "Phường Hố Nai", "Phường Long Bình", "Phường Long Bình Tân", "Phường Phước Tân", "Phường Quang Vinh", "Phường Quyết Thắng", "Phường Tam Hiệp", "Phường Tam Hòa", "Phường Tam Phước", "Phường Tân Biên", "Phường Tân Hạnh", "Phường Tân Hiệp", "Phường Tân Hòa", "Phường Tân Mai", "Phường Tân Phong", "Phường Tân Tiến", "Phường Tân Vạn", "Phường Thanh Bình", "Phường Thống Nhất", "Phường Trảng Dài", "Phường Trung Dũng", "Xã Long Hưng"] },
                { name: "Thành phố Long Khánh", wards: ["Phường Bảo Vinh", "Phường Bàu Sen", "Phường Phú Bình", "Phường Suối Tre", "Phường Xuân An", "Phường Xuân Bình", "Phường Xuân Hòa", "Phường Xuân Lập", "Phường Xuân Tân", "Phường Xuân Thanh", "Phường Xuân Trung"] },
                { name: "Huyện Long Thành", wards: ["Thị trấn Long Thành", "Xã An Phước", "Xã Bàu Cạn", "Xã Bình An", "Xã Bình Sơn", "Xã Cẩm Đường", "Xã Lộc An", "Xã Long An", "Xã Long Đức", "Xã Long Phước", "Xã Phước Bình", "Xã Phước Thái", "Xã Tam An", "Xã Tân Hiệp"] },
                { name: "Huyện Nhơn Trạch", wards: ["Thị trấn Hiệp Phước", "Xã Đại Phước", "Xã Long Tân", "Xã Long Thọ", "Xã Phú Đông", "Xã Phú Hữu", "Xã Phú Hội", "Xã Phú Thạnh", "Xã Phước An", "Xã Phước Khánh", "Xã Phước Thiền", "Xã Vĩnh Thanh"] },
                { name: "Huyện Trảng Bom", wards: ["Thị trấn Trảng Bom", "Xã An Viễn", "Xã Bắc Sơn", "Xã Bàu Hàm", "Xã Bình Minh", "Xã Cây Gáo", "Xã Đồi 61", "Xã Đông Hòa", "Xã Giang Điền", "Xã Hố Nai 3", "Xã Hưng Thịnh", "Xã Quảng Tiến", "Xã Sông Thao", "Xã Sông Trầu", "Xã Tây Hòa", "Xã Thanh Bình", "Xã Trung Hòa"] }
            ]
        },
        {
            name: "Bà Rịa - Vũng Tàu",
            districts: [
                { name: "Thành phố Vũng Tàu", wards: ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 12", "Phường Thắng Nhất", "Phường Thắng Nhì", "Phường Thắng Tam", "Phường Nguyễn An Ninh", "Phường Rạch Dừa", "Xã Long Sơn"] },
                { name: "Thành phố Bà Rịa", wards: ["Phường Kim Dinh", "Phường Long Hương", "Phường Long Tâm", "Phường Long Toàn", "Phường Phước Hưng", "Phường Phước Hiệp", "Phường Phước Nguyên", "Phường Phước Trung", "Xã Hòa Long", "Xã Long Phước", "Xã Tân Hưng"] },
                { name: "Thị xã Phú Mỹ", wards: ["Phường Hắc Dịch", "Phường Mỹ Xuân", "Phường Phú Mỹ", "Phường Phước Hòa", "Phường Tân Phước", "Xã Châu Pha", "Xã Sông Xoài", "Xã Tân Hải", "Xã Tân Hòa", "Xã Tóc Tiên"] },
                { name: "Huyện Châu Đức", wards: ["Thị trấn Ngãi Giao", "Xã Bàu Chinh", "Xã Bình Ba", "Xã Bình Giã", "Xã Cù Bị", "Xã Đá Bạc", "Xã Kim Long", "Xã Láng Lớn", "Xã Nghĩa Thành", "Xã Quảng Thành", "Xã Suối Nghệ", "Xã Suối Rao", "Xã Xà Bang", "Xã Xuân Sơn"] }
            ]
        },
        {
            name: "Bắc Ninh",
            districts: [
                { name: "Thành phố Bắc Ninh", wards: ["Phường Đại Phúc", "Phường Đáp Cầu", "Phường Hạp Lĩnh", "Phường Khắc Niệm", "Phường Khúc Xuyên", "Phường Kim Chân", "Phường Kinh Bắc", "Phường Nam Sơn", "Phường Ninh Xá", "Phường Phong Khê", "Phường Suối Hoa", "Phường Tiền An", "Phường Thị Cầu", "Phường Vạn An", "Phường Vân Dương", "Phường Vệ An", "Phường Võ Cường", "Phường Vũ Ninh", "Xã Hòa Long"] },
                { name: "Thành phố Từ Sơn", wards: ["Phường Châu Khê", "Phường Đình Bảng", "Phường Đồng Kỵ", "Phường Đông Ngàn", "Phường Đồng Nguyên", "Phường Hương Mạc", "Phường Phù Chẩn", "Phường Phù Khê", "Phường Tam Sơn", "Phường Tân Hồng", "Phường Trang Hạ"] },
                { name: "Thị xã Quế Võ", wards: ["Phường Bằng An", "Phường Bồng Lai", "Phường Cách Bi", "Phường Đại Xuân", "Phường Nhân Hòa", "Phường Phố Mới", "Phường Phù Lương", "Phường Phương Liễu", "Phường Phượng Mao", "Phường Quế Tân", "Phường Việt Hùng"] },
                { name: "Thị xã Thuận Thành", wards: ["Phường An Bình", "Phường Gia Đông", "Phường Hà Mãn", "Phường Hồ", "Phường Ninh Xá", "Phường Song Hồ", "Phường Thanh Khương", "Phường Trạm Lộ", "Phường Trí Quả", "Phường Xuân Lâm"] },
                { name: "Huyện Yên Phong", wards: ["Thị trấn Chờ", "Xã Dũng Liệt", "Xã Đông Phong", "Xã Đông Thọ", "Xã Đông Tiến", "Xã Hòa Tiến", "Xã Long Châu", "Xã Mai Đình", "Xã Tam Đa", "Xã Tam Giang", "Xã Thụy Hòa", "Xã Trung Nghĩa", "Xã Văn Môn", "Xã Yên Phụ", "Xã Yên Trung"] }
            ]
        },
        {
            name: "Quảng Ninh",
            districts: [
                { name: "Thành phố Hạ Long", wards: ["Phường Bãi Cháy", "Phường Bạch Đằng", "Phường Cao Thắng", "Phường Cao Xanh", "Phường Đại Yên", "Phường Giếng Đáy", "Phường Hà Khánh", "Phường Hà Khẩu", "Phường Hà Lầm", "Phường Hà Phong", "Phường Hà Trung", "Phường Hà Tu", "Phường Hoành Bồ", "Phường Hồng Gai", "Phường Hồng Hà", "Phường Hồng Hải", "Phường Hùng Thắng", "Phường Trần Hưng Đạo", "Phường Tuần Châu", "Phường Việt Hưng", "Phường Yết Kiêu"] },
                { name: "Thành phố Cẩm Phả", wards: ["Phường Cẩm Bình", "Phường Cẩm Đông", "Phường Cẩm Phú", "Phường Cẩm Sơn", "Phường Cẩm Tây", "Phường Cẩm Thạch", "Phường Cẩm Thành", "Phường Cẩm Thịnh", "Phường Cẩm Thủy", "Phường Cẩm Trung", "Phường Cửa Ông", "Phường Mông Dương", "Phường Quang Hanh", "Xã Cẩm Hải", "Xã Cộng Hòa", "Xã Dương Huy"] },
                { name: "Thành phố Uông Bí", wards: ["Phường Bắc Sơn", "Phường Nam Khê", "Phường Phương Đông", "Phường Phương Nam", "Phường Quang Trung", "Phường Thanh Sơn", "Phường Trưng Vương", "Phường Vàng Danh", "Phường Yên Thanh", "Xã Thượng Yên Công"] },
                { name: "Thành phố Móng Cái", wards: ["Phường Bình Ngọc", "Phường Hải Hòa", "Phường Hải Yên", "Phường Ka Long", "Phường Ninh Dương", "Phường Trà Cổ", "Phường Trần Phú", "Xã Bắc Sơn", "Xã Hải Đông", "Xã Hải Sơn", "Xã Hải Tiến", "Xã Hải Xuân", "Xã Quảng Nghĩa", "Xã Vạn Ninh", "Xã Vĩnh Thực", "Xã Vĩnh Trung"] },
                { name: "Thị xã Đông Triều", wards: ["Phường Đông Triều", "Phường Đức Chính", "Phường Hưng Đạo", "Phường Kim Sơn", "Phường Mạo Khê", "Phường Tràng An", "Phường Xuân Sơn", "Phường Yên Thọ"] }
            ]
        },
        {
            name: "Thừa Thiên Huế",
            districts: [
                { name: "Thành phố Huế", wards: ["Phường An Cựu", "Phường An Đông", "Phường An Hòa", "Phường An Tây", "Phường Đông Ba", "Phường Gia Hội", "Phường Hương An", "Phường Hương Hồ", "Phường Hương Long", "Phường Hương Sơ", "Phường Hương Vinh", "Phường Kim Long", "Phường Phú Hậu", "Phường Phú Hội", "Phường Phú Nhuận", "Phường Phú Thượng", "Phường Phước Vĩnh", "Phường Phường Đúc", "Phường Tây Lộc", "Phường Thuận An", "Phường Thuận Hòa", "Phường Thuận Lộc", "Phường Thủy Biều", "Phường Thủy Vân", "Phường Thủy Xuân", "Phường Vĩnh Ninh", "Phường Vỹ Dạ", "Phường Xuân Phú"] },
                { name: "Thị xã Hương Thủy", wards: ["Phường Phú Bài", "Phường Thủy Châu", "Phường Thủy Dương", "Phường Thủy Lương", "Phường Thủy Phương", "Xã Dương Hòa", "Xã Phú Sơn", "Xã Thủy Phù", "Xã Thủy Tân", "Xã Thủy Thanh"] },
                { name: "Thị xã Hương Trà", wards: ["Phường Hương Chữ", "Phường Hương Văn", "Phường Hương Vân", "Phường Hương Xuân", "Phường Tứ Hạ", "Xã Bình Thành", "Xã Bình Tiến", "Xã Hương Bình", "Xã Hương Toàn"] },
                { name: "Huyện Phú Vang", wards: ["Thị trấn Phú Đa", "Xã Phú An", "Xã Phú Diên", "Xã Phú Dương", "Xã Phú Gia", "Xã Phú Hải", "Xã Phú Hồ", "Xã Phú Lương", "Xã Phú Mậu", "Xã Phú Mỹ", "Xã Phú Thanh", "Xã Phú Thuận", "Xã Phú Xuân", "Xã Vinh An", "Xã Vinh Hà", "Xã Vinh Thanh", "Xã Vinh Xuân"] }
            ]
        },
        {
            name: "Khánh Hòa",
            districts: [
                { name: "Thành phố Nha Trang", wards: ["Phường Lộc Thọ", "Phường Ngọc Hiệp", "Phường Phước Hải", "Phường Phước Hòa", "Phường Phước Long", "Phường Phước Tân", "Phường Phước Tiến", "Phường Phương Sài", "Phường Phương Sơn", "Phường Tân Lập", "Phường Vạn Thạnh", "Phường Vạn Thắng", "Phường Vĩnh Hải", "Phường Vĩnh Hòa", "Phường Vĩnh Phước", "Phường Vĩnh Thọ", "Phường Vĩnh Trường", "Phường Vĩnh Nguyên", "Phường Xương Huân", "Xã Phước Đồng", "Xã Vĩnh Hiệp", "Xã Vĩnh Lương", "Xã Vĩnh Ngọc", "Xã Vĩnh Phương", "Xã Vĩnh Thạnh", "Xã Vĩnh Thái", "Xã Vĩnh Trung"] },
                { name: "Thành phố Cam Ranh", wards: ["Phường Ba Ngòi", "Phường Cam Linh", "Phường Cam Lộc", "Phường Cam Lợi", "Phường Cam Nghĩa", "Phường Cam Phú", "Phường Cam Phúc Bắc", "Phường Cam Phúc Nam", "Phường Cam Thuận", "Xã Cam Bình", "Xã Cam Lập", "Xã Cam Phước Đông", "Xã Cam Thành Nam", "Xã Cam Thịnh Đông", "Xã Cam Thịnh Tây"] },
                { name: "Thị xã Ninh Hòa", wards: ["Phường Ninh Đa", "Phường Ninh Diêm", "Phường Ninh Giang", "Phường Ninh Hà", "Phường Ninh Hải", "Phường Ninh Hiệp", "Phường Ninh Thủy", "Xã Ninh An", "Xã Ninh Bình", "Xã Ninh Đông", "Xã Ninh Hưng", "Xã Ninh Ích", "Xã Ninh Lộc", "Xã Ninh Phú", "Xã Ninh Phụng", "Xã Ninh Quang", "Xã Ninh Sim", "Xã Ninh Sơn", "Xã Ninh Tân", "Xã Ninh Tây", "Xã Ninh Thân", "Xã Ninh Thọ", "Xã Ninh Thượng", "Xã Ninh Trung", "Xã Ninh Vân", "Xã Ninh Xuân"] },
                { name: "Huyện Cam Lâm", wards: ["Thị trấn Cam Đức", "Xã Cam An Bắc", "Xã Cam An Nam", "Xã Cam Hải Đông", "Xã Cam Hải Tây", "Xã Cam Hiệp Bắc", "Xã Cam Hiệp Nam", "Xã Cam Hòa", "Xã Cam Phước Tây", "Xã Cam Tân", "Xã Cam Thành Bắc", "Xã Sơn Tân", "Xã Suối Cát", "Xã Suối Tân"] }
            ]
        },
        {
            name: "Lâm Đồng",
            districts: [
                { name: "Thành phố Đà Lạt", wards: ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 12", "Xã Tà Nung", "Xã Trạm Hành", "Xã Xuân Thọ", "Xã Xuân Trường"] },
                { name: "Thành phố Bảo Lộc", wards: ["Phường 1", "Phường 2", "Phường B'Lao", "Phường Lộc Phát", "Phường Lộc Sơn", "Phường Lộc Tiến", "Xã Đại Lào", "Xã Đam B'ri", "Xã Lộc Châu", "Xã Lộc Nga", "Xã Lộc Thanh"] },
                { name: "Huyện Đức Trọng", wards: ["Thị trấn Liên Nghĩa", "Xã Bình Thạnh", "Xã Đà Loan", "Xã Đa Quyn", "Xã Hiệp An", "Xã Hiệp Thạnh", "Xã Liên Hiệp", "Xã Ninh Gia", "Xã Ninh Loan", "Xã N'Thol Hạ", "Xã Phú Hội", "Xã Tà Hine", "Xã Tà Năng", "Xã Tân Hội", "Xã Tân Thành"] }
            ]
        },
        {
            name: "Nghệ An",
            districts: [
                { name: "Thành phố Vinh", wards: ["Phường Bến Thủy", "Phường Cửa Nam", "Phường Đội Cung", "Phường Đông Vĩnh", "Phường Hà Huy Tập", "Phường Hưng Bình", "Phường Hưng Dũng", "Phường Hưng Phúc", "Phường Lê Lợi", "Phường Lê Mao", "Phường Quán Bàu", "Phường Quang Trung", "Phường Trung Đô", "Phường Trường Thi", "Phường Vinh Tân", "Xã Hưng Chính", "Xã Hưng Đông", "Xã Hưng Hòa", "Xã Hưng Lộc", "Xã Nghi Ân", "Xã Nghi Đức", "Xã Nghi Kim", "Xã Nghi Liên", "Xã Nghi Phú", "Xã Phúc Thọ"] },
                { name: "Thị xã Cửa Lò", wards: ["Phường Nghi Hải", "Phường Nghi Hòa", "Phường Nghi Hương", "Phường Nghi Tân", "Phường Nghi Thu", "Phường Nghi Thủy", "Phường Thu Thủy"] },
                { name: "Thị xã Thái Hòa", wards: ["Phường Hòa Hiếu", "Phường Long Sơn", "Phường Quang Phong", "Phường Quang Tiến", "Xã Đông Hiếu", "Xã Nghĩa Hòa", "Xã Nghĩa Mỹ", "Xã Nghĩa Thuận", "Xã Nghĩa Tiến", "Xã Tây Hiếu"] }
            ]
        },
        {
            name: "Thanh Hóa",
            districts: [
                { name: "Thành phố Thanh Hóa", wards: ["Phường An Hưng", "Phường Ba Đình", "Phường Điện Biên", "Phường Đông Cương", "Phường Đông Hải", "Phường Đông Hương", "Phường Đông Lĩnh", "Phường Đông Sơn", "Phường Đông Thọ", "Phường Đông Vệ", "Phường Hàm Rồng", "Phường Lam Sơn", "Phường Long Anh", "Phường Nam Ngạn", "Phường Ngọc Trạo", "Phường Phú Sơn", "Phường Quảng Cát", "Phường Quảng Đông", "Phường Quảng Hưng", "Phường Quảng Thành", "Phường Quảng Thắng", "Phường Quảng Thịnh", "Phường Quảng Tâm", "Phường Rừng Thông", "Phường Tào Xuyên", "Phường Tân Sơn", "Phường Thiệu Dương", "Phường Thiệu Khánh", "Phường Trường Thi"] },
                { name: "Thành phố Sầm Sơn", wards: ["Phường Bắc Sơn", "Phường Trung Sơn", "Phường Trường Sơn", "Phường Quảng Cư", "Phường Quảng Châu", "Phường Quảng Thọ", "Phường Quảng Tiến", "Phường Quảng Vinh", "Xã Quảng Đại", "Xã Quảng Hùng", "Xã Quảng Minh"] },
                { name: "Thị xã Bỉm Sơn", wards: ["Phường Ba Đình", "Phường Bắc Sơn", "Phường Đông Sơn", "Phường Lam Sơn", "Phường Ngọc Trạo", "Phường Phú Sơn", "Xã Quang Trung"] },
                { name: "Thị xã Nghi Sơn", wards: ["Phường Bình Minh", "Phường Hải An", "Phường Hải Bình", "Phường Hải Châu", "Phường Hải Hòa", "Phường Hải Lĩnh", "Phường Hải Ninh", "Phường Hải Thanh", "Phường Hải Thượng", "Phường Mai Lâm", "Phường Nguyên Bình", "Phường Ninh Hải", "Phường Tân Dân", "Phường Tĩnh Hải", "Phường Trúc Lâm", "Phường Xuân Lâm"] }
            ]
        },
        {
            name: "An Giang",
            districts: [
                { name: "Thành phố Long Xuyên", wards: ["Phường Bình Đức", "Phường Bình Khánh", "Phường Đông Xuyên", "Phường Mỹ Bình", "Phường Mỹ Hòa", "Phường Mỹ Long", "Phường Mỹ Phước", "Phường Mỹ Quý", "Phường Mỹ Thạnh", "Phường Mỹ Thới", "Phường Mỹ Xuyên", "Xã Mỹ Hòa Hưng", "Xã Mỹ Khánh"] },
                { name: "Thành phố Châu Đốc", wards: ["Phường Châu Phú A", "Phường Châu Phú B", "Phường Núi Sam", "Phường Vĩnh Mỹ", "Phường Vĩnh Nguơn", "Xã Vĩnh Châu", "Xã Vĩnh Tế"] },
                { name: "Thị xã Tân Châu", wards: ["Phường Long Châu", "Phường Long Hưng", "Phường Long Phú", "Phường Long Sơn", "Phường Long Thạnh", "Xã Châu Phong", "Xã Lê Chánh", "Xã Long An", "Xã Phú Lộc", "Xã Phú Vĩnh", "Xã Tân An", "Xã Tân Thạnh", "Xã Vĩnh Hòa", "Xã Vĩnh Xương"] }
            ]
        },
        {
            name: "Kiên Giang",
            districts: [
                { name: "Thành phố Rạch Giá", wards: ["Phường An Bình", "Phường An Hòa", "Phường Rạch Sỏi", "Phường Vĩnh Bảo", "Phường Vĩnh Hiệp", "Phường Vĩnh Lạc", "Phường Vĩnh Lợi", "Phường Vĩnh Quang", "Phường Vĩnh Thanh", "Phường Vĩnh Thanh Vân", "Phường Vĩnh Thông", "Xã Phi Thông"] },
                { name: "Thành phố Phú Quốc", wards: ["Phường Dương Đông", "Phường An Thới", "Xã Bãi Thơm", "Xã Cửa Cạn", "Xã Cửa Dương", "Xã Dương Tơ", "Xã Gành Dầu", "Xã Hàm Ninh", "Xã Thổ Châu"] },
                { name: "Thành phố Hà Tiên", wards: ["Phường Bình San", "Phường Đông Hồ", "Phường Mỹ Đức", "Phường Pháo Đài", "Phường Tô Châu", "Xã Thuận Yên", "Xã Tiên Hải"] }
            ]
        },
        {
            name: "Tiền Giang",
            districts: [
                { name: "Thành phố Mỹ Tho", wards: ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường Tân Long", "Xã Đạo Thạnh", "Xã Mỹ Phong", "Xã Phước Thạnh", "Xã Tân Mỹ Chánh", "Xã Thới Sơn", "Xã Trung An"] },
                { name: "Thị xã Gò Công", wards: ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường Long Chánh", "Phường Long Hòa", "Phường Long Hưng", "Phường Long Thuận", "Xã Bình Đông", "Xã Bình Xuân", "Xã Tân Trung"] },
                { name: "Thị xã Cai Lậy", wards: ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường Nhị Mỹ", "Xã Long Khánh", "Xã Mỹ Hạnh Đông", "Xã Mỹ Hạnh Trung", "Xã Mỹ Phước Tây", "Xã Nhị Quý", "Xã Phú Quý", "Xã Tân Bình", "Xã Tân Hội", "Xã Tân Phú", "Xã Thanh Hòa"] }
            ]
        },
        {
            name: "Đắk Lắk",
            districts: [
                { name: "Thành phố Buôn Ma Thuột", wards: ["Phường Ea Tam", "Phường Khánh Xuân", "Phường Tân An", "Phường Tân Hòa", "Phường Tân Lập", "Phường Tân Lợi", "Phường Tân Thành", "Phường Tân Tiến", "Phường Thắng Lợi", "Phường Thành Công", "Phường Thành Nhất", "Phường Thống Nhất", "Phường Tự An", "Xã Cư Êbur", "Xã Ea Kao", "Xã Ea Tu", "Xã Hòa Khánh", "Xã Hòa Phú", "Xã Hòa Thắng", "Xã Hòa Thuận", "Xã Hòa Xuân"] },
                { name: "Thị xã Buôn Hồ", wards: ["Phường An Bình", "Phường An Lạc", "Phường Bình Tân", "Phường Đạt Hiếu", "Phường Đoàn Kết", "Phường Thiện An", "Phường Thống Nhất", "Xã Bình Thuận", "Xã Cư Bao", "Xã Ea Blang", "Xã Ea Drông", "Xã Ea Siên"] }
            ]
        },
        {
            name: "Hải Dương",
            districts: [
                { name: "Thành phố Hải Dương", wards: ["Phường Ái Quốc", "Phường Bình Hàn", "Phường Cẩm Thượng", "Phường Hải Tân", "Phường Lê Thanh Nghị", "Phường Nam Đồng", "Phường Ngọc Châu", "Phường Nguyễn Trãi", "Phường Nhị Châu", "Phường Phạm Ngũ Lão", "Phường Quang Trung", "Phường Tân Bình", "Phường Tân Hưng", "Phường Thạch Khôi", "Phường Thanh Bình", "Phường Trần Hưng Đạo", "Phường Trần Phú", "Phường Tứ Minh", "Phường Việt Hòa"] },
                { name: "Thành phố Chí Linh", wards: ["Phường An Lạc", "Phường Bến Tắm", "Phường Cổ Thành", "Phường Cộng Hòa", "Phường Đồng Lạc", "Phường Hoàng Tân", "Phường Hoàng Tiến", "Phường Phả Lại", "Phường Sao Đỏ", "Phường Tân Dân", "Phường Thái Học", "Phường Văn An", "Phường Văn Đức"] },
                { name: "Thị xã Kinh Môn", wards: ["Phường An Lưu", "Phường An Phụ", "Phường An Sinh", "Phường Duy Tân", "Phường Hiến Thành", "Phường Hiệp An", "Phường Hiệp Sơn", "Phường Long Xuyên", "Phường Minh Tân", "Phường Phú Thứ", "Phường Phạm Thái", "Phường Tân Dân", "Phường Thái Thịnh", "Phường Thất Hùng"] }
            ]
        },
        {
            name: "Bình Định",
            districts: [
                { name: "Thành phố Quy Nhơn", wards: ["Phường Bùi Thị Xuân", "Phường Đống Đa", "Phường Ghềnh Ráng", "Phường Hải Cảng", "Phường Lê Hồng Phong", "Phường Lê Lợi", "Phường Ngô Mây", "Phường Nguyễn Văn Cừ", "Phường Nhơn Bình", "Phường Nhơn Phú", "Phường Quang Trung", "Phường Thị Nại", "Phường Trần Hưng Đạo", "Phường Trần Phú", "Phường Trần Quang Diệu", "Xã Nhơn Châu", "Xã Nhơn Hải", "Xã Nhơn Hội", "Xã Nhơn Lý", "Xã Phước Mỹ"] },
                { name: "Thị xã An Nhơn", wards: ["Phường Bình Định", "Phường Đập Đá", "Phường Nhơn Hòa", "Phường Nhơn Hưng", "Phường Nhơn Thành", "Xã Nhơn An", "Xã Nhơn Hạnh", "Xã Nhơn Hậu", "Xã Nhơn Khánh", "Xã Nhơn Lộc", "Xã Nhơn Mỹ", "Xã Nhơn Phong", "Xã Nhơn Phúc", "Xã Nhơn Tân", "Xã Nhơn Thọ"] },
                { name: "Thị xã Hoài Nhơn", wards: ["Phường Bồng Sơn", "Phường Hoài Đức", "Phường Hoài Hảo", "Phường Hoài Hương", "Phường Hoài Tân", "Phường Hoài Thanh", "Phường Hoài Thanh Tây", "Phường Hoài Xuân", "Phường Tam Quan", "Phường Tam Quan Bắc", "Phường Tam Quan Nam"] }
            ]
        },
        {
            name: "Quảng Nam",
            districts: [
                { name: "Thành phố Tam Kỳ", wards: ["Phường An Mỹ", "Phường An Phú", "Phường An Sơn", "Phường An Xuân", "Phường Hòa Hương", "Phường Hòa Thuận", "Phường Phước Hòa", "Phường Tân Thạnh", "Phường Trường Xuân", "Xã Tam Ngọc", "Xã Tam Phú", "Xã Tam Thanh", "Xã Tam Thăng"] },
                { name: "Thành phố Hội An", wards: ["Phường Cẩm An", "Phường Cẩm Châu", "Phường Cẩm Nam", "Phường Cẩm Phô", "Phường Cửa Đại", "Phường Minh An", "Phường Sơn Phong", "Phường Tân An", "Phường Thanh Hà", "Xã Cẩm Hà", "Xã Cẩm Kim", "Xã Cẩm Thanh", "Xã Tân Hiệp"] },
                { name: "Thị xã Điện Bàn", wards: ["Phường Điện An", "Phường Điện Dương", "Phường Điện Nam Bắc", "Phường Điện Nam Đông", "Phường Điện Nam Trung", "Phường Điện Ngọc", "Phường Vĩnh Điện"] }
            ]
        },
        // Bổ sung danh sách các tỉnh thành còn lại của Việt Nam với các Quận/Huyện/Thị xã trung tâm
        { name: "Bắc Kạn", districts: [{ name: "Thành phố Bắc Kạn", wards: ["Phường Đức Xuân", "Phường Nguyễn Thị Minh Khai", "Phường Phùng Chí Kiên", "Phường Sông Cầu", "Phường Xuất Hóa", "Phường Huyền Tụng"] }, { name: "Huyện Ba Bể", wards: ["Thị trấn Chợ Rã", "Xã Ba Bể", "Xã Khang Ninh"] }] },
        { name: "Bạc Liêu", districts: [{ name: "Thành phố Bạc Liêu", wards: ["Phường 1", "Phường 2", "Phường 3", "Phường 5", "Phường 7", "Phường 8", "Phường Nhà Mát"] }, { name: "Thị xã Giá Rai", wards: ["Phường 1", "Phường Hộ Phòng", "Phường Láng Tròn"] }] },
        { name: "Bến Tre", districts: [{ name: "Thành phố Bến Tre", wards: ["Phường An Hội", "Phường Phú Khương", "Phường Phú Tân", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường 8"] }, { name: "Huyện Châu Thành", wards: ["Thị trấn Châu Thành", "Xã Tiên Thủy", "Xã Quới Sơn"] }] },
        { name: "Bình Phước", districts: [{ name: "Thành phố Đồng Xoài", wards: ["Phường Tân Bình", "Phường Tân Đồng", "Phường Tân Phú", "Phường Tân Thiện", "Phường Tân Xuân", "Phường Tiến Thành"] }, { name: "Thị xã Phước Long", wards: ["Phường Long Phước", "Phường Long Thủy", "Phường Phước Bình", "Phường Sơn Giang", "Phường Thác Mơ"] }, { name: "Thị xã Chơn Thành", wards: ["Phường Chơn Thành", "Phường Hưng Long", "Phường Minh Hưng", "Phường Minh Long", "Phường Minh Thành"] }] },
        { name: "Bình Thuận", districts: [{ name: "Thành phố Phan Thiết", wards: ["Phường Bình Hưng", "Phường Đức Long", "Phường Đức Nghĩa", "Phường Đức Thắng", "Phường Hàm Tiến", "Phường Hưng Long", "Phường Lạc Đạo", "Phường Mũi Né", "Phường Phú Hài", "Phường Phú Thủy", "Phường Phú Trinh", "Phường Thanh Hải", "Phường Xuân An"] }, { name: "Thị xã La Gi", wards: ["Phường Bình Tân", "Phường Phước Hội", "Phường Phước Lộc", "Phường Tân An", "Phường Tân Thiện"] }] },
        { name: "Cà Mau", districts: [{ name: "Thành phố Cà Mau", wards: ["Phường 1", "Phường 2", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường Tân Thành", "Phường Tân Xuyên"] }, { name: "Huyện Năm Căn", wards: ["Thị trấn Năm Căn", "Xã Đất Mới", "Xã Hàng Vịnh"] }] },
        { name: "Cao Bằng", districts: [{ name: "Thành phố Cao Bằng", wards: ["Phường Đề Thám", "Phường Duyệt Trung", "Phường Hòa Chung", "Phường Hợp Giang", "Phường Ngọc Xuân", "Phường Sông Bằng", "Phường Sông Hiến", "Phường Tân Giang"] }] },
        { name: "Đắk Nông", districts: [{ name: "Thành phố Gia Nghĩa", wards: ["Phường Nghĩa Đức", "Phường Nghĩa Phú", "Phường Nghĩa Tân", "Phường Nghĩa Thành", "Phường Nghĩa Trung", "Phường Quảng Thành"] }] },
        { name: "Điện Biên", districts: [{ name: "Thành phố Điện Biên Phủ", wards: ["Phường Him Lam", "Phường Mường Thanh", "Phường Nam Thanh", "Phường Noong Bua", "Phường Tân Thanh", "Phường Thanh Bình", "Phường Thanh Trường"] }, { name: "Thị xã Mường Lay", wards: ["Phường Na Lay", "Phường Sông Đà"] }] },
        { name: "Đồng Tháp", districts: [{ name: "Thành phố Cao Lãnh", wards: ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 6", "Phường 11", "Phường Hòa Thuận", "Phường Mỹ Phú"] }, { name: "Thành phố Sa Đéc", wards: ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường An Hòa", "Phường Tân Quy Đông"] }, { name: "Thành phố Hồng Ngự", wards: ["Phường An Bình A", "Phường An Bình B", "Phường An Lạc", "Phường An Lộc", "Phường An Thạnh"] }] },
        { name: "Gia Lai", districts: [{ name: "Thành phố Pleiku", wards: ["Phường Chi Lăng", "Phường Diên Hồng", "Phường Đống Đa", "Phường Hoa Lư", "Phường Hội Phú", "Phường Hội Thương", "Phường Ia Kring", "Phường Phù Đổng", "Phường Tây Sơn", "Phường Thắng Lợi", "Phường Thống Nhất", "Phường Trà Bá", "Phường Yên Đỗ", "Phường Yên Thế"] }, { name: "Thị xã An Khê", wards: ["Phường An Bình", "Phường An Phú", "Phường An Phước", "Phường An Tân", "Phường Tây Sơn"] }, { name: "Thị xã Ayun Pa", wards: ["Phường Cheo Reo", "Phường Đoàn Kết", "Phường Hòa Bình", "Phường Sông Bờ"] }] },
        { name: "Hà Giang", districts: [{ name: "Thành phố Hà Giang", wards: ["Phường Minh Khai", "Phường Ngọc Hà", "Phường Nguyễn Trãi", "Phường Quang Trung", "Phường Trần Phú"] }] },
        { name: "Hà Nam", districts: [{ name: "Thành phố Phủ Lý", wards: ["Phường Châu Cầu", "Phường Hai Bà Trưng", "Phường Lam Hạ", "Phường Lê Hồng Phong", "Phường Lương Khánh Thiện", "Phường Minh Khai", "Phường Quang Trung", "Phường Thanh Châu", "Phường Thanh Tuyền", "Phường Trần Hưng Đạo"] }, { name: "Thị xã Duy Tiên", wards: ["Phường Bạch Thượng", "Phường Châu Giang", "Phường Đồng Văn", "Phường Hòa Mạc", "Phường Hoàng Đông", "Phường Tiên Nội", "Phường Yên Bắc"] }] },
        { name: "Hà Tĩnh", districts: [{ name: "Thành phố Hà Tĩnh", wards: ["Phường Bắc Hà", "Phường Đại Nài", "Phường Hà Huy Tập", "Phường Nam Hà", "Phường Nguyễn Du", "Phường Tân Giang", "Phường Thạch Linh", "Phường Thạch Quý", "Phường Trần Phú", "Phường Văn Yên"] }, { name: "Thị xã Hồng Lĩnh", wards: ["Phường Bắc Hồng", "Phường Đậu Liêu", "Phường Đức Thuận", "Phường Nam Hồng", "Phường Trung Lương"] }, { name: "Thị xã Kỳ Anh", wards: ["Phường Hưng Trí", "Phường Kỳ Long", "Phường Kỳ Liên", "Phường Kỳ Phương", "Phường Kỳ Thịnh", "Phường Kỳ Trinh"] }] },
        { name: "Hậu Giang", districts: [{ name: "Thành phố Vị Thanh", wards: ["Phường I", "Phường III", "Phường IV", "Phường V", "Phường VII"] }, { name: "Thành phố Ngã Bảy", wards: ["Phường Hiệp Lợi", "Phường Hiệp Thành", "Phường Lái Hiếu", "Phường Ngã Bảy"] }] },
        { name: "Hòa Bình", districts: [{ name: "Thành phố Hòa Bình", wards: ["Phường Dân Chủ", "Phường Đồng Tiến", "Phường Hữu Nghị", "Phường Kỳ Sơn", "Phường Phương Lâm", "Phường Quỳnh Lâm", "Phường Tân Hòa", "Phường Tân Thịnh", "Phường Thái Bình", "Phường Thịnh Lang", "Phường Thống Nhất", "Phường Trung Minh"] }] },
        { name: "Hưng Yên", districts: [{ name: "Thành phố Hưng Yên", wards: ["Phường An Tảo", "Phường Hiến Nam", "Phường Hồng Châu", "Phường Lam Sơn", "Phường Lê Lợi", "Phường Minh Khai", "Phường Quang Trung"] }, { name: "Thị xã Mỹ Hào", wards: ["Phường Bần Yên Nhân", "Phường Bạch Sam", "Phường Dị Sử", "Phường Minh Đức", "Phường Nhân Hòa", "Phường Phan Đình Phùng", "Phường Phùng Chí Kiên"] }] },
        { name: "Kon Tum", districts: [{ name: "Thành phố Kon Tum", wards: ["Phường Duy Tân", "Phường Lê Lợi", "Phường Ngô Mây", "Phường Nguyễn Trãi", "Phường Quang Trung", "Phường Quyết Thắng", "Phường Thắng Lợi", "Phường Thống Nhất", "Phường Trần Hưng Đạo", "Phường Trường Chinh"] }] },
        { name: "Lai Châu", districts: [{ name: "Thành phố Lai Châu", wards: ["Phường Đoàn Kết", "Phường Đông Phong", "Phường Quyết Thắng", "Phường Quyết Tiến", "Phường Tân Phong"] }] },
        { name: "Lạng Sơn", districts: [{ name: "Thành phố Lạng Sơn", wards: ["Phường Chi Lăng", "Phường Đông Kinh", "Phường Hoàng Văn Thụ", "Phường Tam Thanh", "Phường Vĩnh Trại"] }] },
        { name: "Lào Cai", districts: [{ name: "Thành phố Lào Cai", wards: ["Phường Bắc Cường", "Phường Bắc Lệnh", "Phường Bình Minh", "Phường Cốc Lếu", "Phường Duyên Hải", "Phường Kim Tân", "Phường Lào Cai", "Phường Nam Cường", "Phường Pom Hán", "Phường Xuân Tăng"] }, { name: "Thị xã Sa Pa", wards: ["Phường Cầu Mây", "Phường Hàm Rồng", "Phường Ô Quý Hồ", "Phường Phan Si Păng", "Phường Sa Pa", "Phường Sa Pả"] }] },
        { name: "Long An", districts: [{ name: "Thành phố Tân An", wards: ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường Khánh Hậu", "Phường Tân Khánh"] }, { name: "Thị xã Kiến Tường", wards: ["Phường 1", "Phường 2", "Phường 3"] }, { name: "Huyện Bến Lức", wards: ["Thị trấn Bến Lức", "Xã An Thạnh", "Xã Mỹ Yên", "Xã Thạnh Đức"] }, { name: "Huyện Đức Hòa", wards: ["Thị trấn Hậu Nghĩa", "Thị trấn Đức Hòa", "Xã Đức Hòa Đông", "Xã Mỹ Hạnh Nam"] }] },
        { name: "Nam Định", districts: [{ name: "Thành phố Nam Định", wards: ["Phường Bà Triệu", "Phường Cửa Bắc", "Phường Cửa Nam", "Phường Hạ Long", "Phường Lộc Hạ", "Phường Lộc Vượng", "Phường Năng Tĩnh", "Phường Ngô Quyền", "Phường Nguyễn Du", "Phường Phan Đình Phùng", "Phường Quang Trung", "Phường Thống Nhất", "Phường Trần Đăng Ninh", "Phường Trần Hưng Đạo", "Phường Trần Quang Khải", "Phường Trần Tế Xương", "Phường Trường Thi", "Phường Vị Hoàng", "Phường Vị Xuyên"] }] },
        { name: "Ninh Bình", districts: [{ name: "Thành phố Ninh Bình", wards: ["Phường Bích Đào", "Phường Đông Thành", "Phường Nam Bình", "Phường Nam Thành", "Phường Ninh Khánh", "Phường Ninh Phong", "Phường Ninh Sơn", "Phường Phúc Thành", "Phường Tân Thành", "Phường Thanh Bình", "Phường Vân Giang"] }, { name: "Thành phố Tam Điệp", wards: ["Phường Bắc Sơn", "Phường Nam Sơn", "Phường Tân Bình", "Phường Tây Sơn", "Phường Trung Sơn", "Phường Yên Bình"] }] },
        { name: "Ninh Thuận", districts: [{ name: "Thành phố Phan Rang - Tháp Chàm", wards: ["Phường Bảo An", "Phường Đài Sơn", "Phường Đạo Long", "Phường Đô Vinh", "Phường Đông Hải", "Phường Kinh Dinh", "Phường Mỹ Bình", "Phường Mỹ Đông", "Phường Mỹ Hải", "Phường Mỹ Hương", "Phường Phủ Hà", "Phường Phước Mỹ", "Phường Tấn Tài", "Phường Thanh Sơn", "Phường Văn Hải"] }] },
        { name: "Phú Thọ", districts: [{ name: "Thành phố Việt Trì", wards: ["Phường Bạch Hạc", "Phường Bến Gót", "Phường Dữu Lâu", "Phường Gia Cẩm", "Phường Nông Trang", "Phường Tân Dân", "Phường Thanh Miếu", "Phường Thọ Sơn", "Phường Tiên Cát", "Phường Vân Cơ", "Phường Vân Phú"] }, { name: "Thị xã Phú Thọ", wards: ["Phường Âu Cơ", "Phường Hùng Vương", "Phường Phong Châu", "Phường Thanh Vinh"] }] },
        { name: "Phú Yên", districts: [{ name: "Thành phố Tuy Hòa", wards: ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường Phú Đông", "Phường Phú Lâm", "Phường Phú Thạnh"] }, { name: "Thị xã Sông Cầu", wards: ["Phường Xuân Đài", "Phường Xuân Hải", "Phường Xuân Thành", "Phường Xuân Yên"] }, { name: "Thị xã Đông Hòa", wards: ["Phường Hòa Hiệp Bắc", "Phường Hòa Hiệp Nam", "Phường Hòa Hiệp Trung", "Phường Hòa Vinh", "Phường Hòa Xuân Tây"] }] },
        { name: "Quảng Bình", districts: [{ name: "Thành phố Đồng Hới", wards: ["Phường Bắc Lý", "Phường Bắc Nghĩa", "Phường Đồng Hải", "Phường Đồng Phú", "Phường Đồng Sơn", "Phường Đức Ninh Đông", "Phường Hải Thành", "Phường Nam Lý", "Phường Phú Hải"] }, { name: "Thị xã Ba Đồn", wards: ["Phường Ba Đồn", "Phường Quảng Long", "Phường Quảng Phong", "Phường Quảng Phúc", "Phường Quảng Thọ", "Phường Quảng Thuận"] }] },
        { name: "Quảng Ngãi", districts: [{ name: "Thành phố Quảng Ngãi", wards: ["Phường Chánh Lộ", "Phường Lê Hồng Phong", "Phường Nghĩa Chánh", "Phường Nghĩa Dũng", "Phường Nghĩa Lộ", "Phường Quảng Phú", "Phường Trương Quang Trọng", "Phường Trần Hưng Đạo", "Phường Trần Phú"] }, { name: "Thị xã Đức Phổ", wards: ["Phường Nguyễn Nghiêm", "Phường Phổ Hòa", "Phường Phổ Ninh", "Phường Phổ Quang", "Phường Phổ Thạnh", "Phường Phổ Văn", "Phường Phổ Vinh", "Phường Phổ Cường"] }] },
        { name: "Quảng Trị", districts: [{ name: "Thành phố Đông Hà", wards: ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường Đông Giang", "Phường Đông Lễ", "Phường Đông Lương", "Phường Đông Thanh"] }, { name: "Thị xã Quảng Trị", wards: ["Phường 1", "Phường 2", "Phường 3", "Phường An Đôn"] }] },
        { name: "Sóc Trăng", districts: [{ name: "Thành phố Sóc Trăng", wards: ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10"] }, { name: "Thị xã Vĩnh Châu", wards: ["Phường 1", "Phường 2", "Phường Vĩnh Phước", "Phường Khánh Hòa"] }, { name: "Thị xã Ngã Năm", wards: ["Phường 1", "Phường 2", "Phường 3"] }] },
        { name: "Sơn La", districts: [{ name: "Thành phố Sơn La", wards: ["Phường Chiềng An", "Phường Chiềng Cơi", "Phường Chiềng Lề", "Phường Chiềng Sinh", "Phường Quyết Tâm", "Phường Quyết Thắng", "Phường Tô Hiệu"] }, { name: "Huyện Mộc Châu", wards: ["Thị trấn Mộc Châu", "Thị trấn Nông trường Mộc Châu", "Xã Đông Sang", "Xã Mường Sang"] }] },
        { name: "Tây Ninh", districts: [{ name: "Thành phố Tây Ninh", wards: ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường Hiệp Ninh", "Phường Ninh Sơn", "Phường Ninh Thạnh"] }, { name: "Thị xã Trảng Bàng", wards: ["Phường An Hòa", "Phường An Tịnh", "Phường Gia Bình", "Phường Gia Lộc", "Phường Lộc Hưng", "Phường Trảng Bàng"] }, { name: "Thị xã Hòa Thành", wards: ["Phường Hiệp Tân", "Phường Long Hoa", "Phường Long Thành Bắc", "Phường Long Thành Trung"] }] },
        { name: "Thái Bình", districts: [{ name: "Thành phố Thái Bình", wards: ["Phường Bồ Xuyên", "Phường Đề Thám", "Phường Hoàng Diệu", "Phường Kỳ Bá", "Phường Lê Hồng Phong", "Phường Phú Khánh", "Phường Quang Trung", "Phường Tiền Phong", "Phường Trần Hưng Đạo", "Phường Trần Lãm"] }] },
        { name: "Thái Nguyên", districts: [{ name: "Thành phố Thái Nguyên", wards: ["Phường Cam Giá", "Phường Chùa Hang", "Phường Đồng Bẩm", "Phường Đồng Quang", "Phường Gia Sàng", "Phường Hoàng Văn Thụ", "Phường Hương Sơn", "Phường Phan Đình Phùng", "Phường Phú Xá", "Phường Quan Triều", "Phường Quang Trung", "Phường Tân Lập", "Phường Tân Long", "Phường Tân Thành", "Phường Tân Thịnh", "Phường Thịnh Đán", "Phường Tích Lương", "Phường Trung Thành", "Phường Túc Duyên"] }, { name: "Thành phố Sông Công", wards: ["Phường Bách Quang", "Phường Cải Đan", "Phường Châu Sơn", "Phường Lương Châu", "Phường Lương Sơn", "Phường Mỏ Chè", "Phường Phố Cò", "Phường Thắng Lợi"] }, { name: "Thành phố Phổ Yên", wards: ["Phường Ba Hàng", "Phường Bãi Bông", "Phường Bắc Sơn", "Phường Đắc Sơn", "Phường Đông Cao", "Phường Đồng Tiến", "Phường Hồng Tiến", "Phường Nam Tiến", "Phường Tân Hương", "Phường Tân Phú", "Phường Thuận Thành", "Phường Tiên Phong", "Phường Trung Thành"] }] },
        { name: "Trà Vinh", districts: [{ name: "Thành phố Trà Vinh", wards: ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường 8", "Phường 9"] }, { name: "Thị xã Duyên Hải", wards: ["Phường 1", "Phường 2"] }] },
        { name: "Tuyên Quang", districts: [{ name: "Thành phố Tuyên Quang", wards: ["Phường An Tường", "Phường Đội Cấn", "Phường Hưng Thành", "Phường Minh Xuân", "Phường Mỹ Lâm", "Phường Nông Tiến", "Phường Phan Thiết", "Phường Tân Hà", "Phường Tân Quang"] }] },
        { name: "Vĩnh Long", districts: [{ name: "Thành phố Vĩnh Long", wards: ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 8", "Phường 9", "Phường Tân Hòa", "Phường Tân Hội", "Phường Tân Ngãi", "Phường Trường An"] }, { name: "Thị xã Bình Minh", wards: ["Phường Cái Vồn", "Phường Đông Thuận", "Phường Thành Phước"] }] },
        { name: "Vĩnh Phúc", districts: [{ name: "Thành phố Vĩnh Yên", wards: ["Phường Định Trung", "Phường Đống Đa", "Phường Đồng Tâm", "Phường Hội Hợp", "Phường Khai Quang", "Phường Liên Bảo", "Phường Ngô Quyền", "Phường Tích Sơn", "Xã Thanh Trù"] }, { name: "Thành phố Phúc Yên", wards: ["Phường Đồng Xuân", "Phường Hùng Vương", "Phường Nam Viêm", "Phường Phúc Thắng", "Phường Tiền Châu", "Phường Trưng Nhị", "Phường Trưng Trắc", "Phường Xuân Hòa"] }] },
        { name: "Yên Bái", districts: [{ name: "Thành phố Yên Bái", wards: ["Phường Đồng Tâm", "Phường Hồng Hà", "Phường Hợp Minh", "Phường Minh Tân", "Phường Nam Cường", "Phường Nguyễn Phúc", "Phường Nguyễn Thái Học", "Phường Yên Ninh", "Phường Yên Thịnh"] }, { name: "Thị xã Nghĩa Lộ", wards: ["Phường Cầu Thia", "Phường Pú Trạng", "Phường Tân An", "Phường Trung Tâm"] }] }
    ];

    /**
     * Chuẩn hóa và kiểm tra số điện thoại Việt Nam
     * @param {string} phone
     * @returns {{isValid: boolean, formatted: string, message: string}}
     */
    function validateVietnamesePhone(phone) {
        if (!phone) {
            return { isValid: false, formatted: '', message: 'Vui lòng nhập số điện thoại.' };
        }

        // Loại bỏ khoảng trắng, dấu gạch ngang, dấu chấm, dấu ngoặc
        let cleaned = phone.replace(/[\s\-\.\(\)]/g, '').trim();

        // Kiểm tra xem có chứa ký tự không hợp lệ không (chỉ cho phép + ở đầu và các chữ số)
        if (!/^(\+?\d+)$/.test(cleaned)) {
            return { isValid: false, formatted: phone, message: 'Số điện thoại chỉ được chứa chữ số và dấu + ở đầu.' };
        }

        let formatted = cleaned;

        // Quy đổi về chuẩn +84
        if (cleaned.startsWith('0')) {
            formatted = '+84' + cleaned.substring(1);
        } else if (cleaned.startsWith('84') && !cleaned.startsWith('+84')) {
            formatted = '+' + cleaned;
        } else if (!cleaned.startsWith('+84')) {
            formatted = '+84' + cleaned;
        }

        // Số điện thoại Việt Nam hợp lệ: +84 theo sau bởi 9 số (di động) hoặc 10 số (cố định), bắt đầu từ số 1-9
        // Tổng số ký tự dạng +84xxxxxxxxx là 12 hoặc 13 ký tự (+84 + 9 số hoặc +84 + 10 số)
        const phoneRegex = /^\+84[1-9]\d{8,9}$/;
        const isValid = phoneRegex.test(formatted);

        if (!isValid) {
            return {
                isValid: false,
                formatted: formatted,
                message: 'Số điện thoại không hợp lệ! Vui lòng nhập định dạng +84 (10 hoặc 11 số, ví dụ: +84901234567 hoặc 0901234567).'
            };
        }

        return {
            isValid: true,
            formatted: formatted,
            message: ''
        };
    }

    /**
     * Khởi tạo bộ 3 Menu xổ xuống: Tỉnh/Thành phố -> Quận/Huyện -> Phường/Xã
     */
    function initVietnamAddressSelector(config) {
        const provinceEl = typeof config.province === 'string' ? document.querySelector(config.province) : config.province;
        const districtEl = typeof config.district === 'string' ? document.querySelector(config.district) : config.district;
        const wardEl = typeof config.ward === 'string' ? document.querySelector(config.ward) : config.ward;
        const detailEl = typeof config.detail === 'string' ? document.querySelector(config.detail) : config.detail;
        const fullAddressEl = typeof config.fullAddress === 'string' ? document.querySelector(config.fullAddress) : config.fullAddress;

        if (!provinceEl || !districtEl || !wardEl) {
            return null;
        }

        // Nạp danh sách Tỉnh/Thành phố
        function populateProvinces(selectedProvinceName) {
            provinceEl.innerHTML = '<option value="">-- Chọn Tỉnh / Thành phố --</option>';
            VIETNAM_ADDRESS_DATA.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.name;
                opt.textContent = p.name.startsWith('Thành phố') || p.name.startsWith('Tỉnh') ? p.name : (['Hà Nội', 'Hồ Chí Minh', 'Đà Nẵng', 'Hải Phòng', 'Cần Thơ'].includes(p.name) ? 'TP. ' + p.name : 'Tỉnh ' + p.name);
                if (selectedProvinceName && (p.name === selectedProvinceName || opt.textContent === selectedProvinceName || selectedProvinceName.includes(p.name))) {
                    opt.selected = true;
                }
                provinceEl.appendChild(opt);
            });
        }

        // Nạp danh sách Quận/Huyện theo Tỉnh đã chọn
        function populateDistricts(provinceName, selectedDistrictName) {
            districtEl.innerHTML = '<option value="">-- Chọn Quận / Huyện --</option>';
            wardEl.innerHTML = '<option value="">-- Chọn Phường / Xã --</option>';
            districtEl.disabled = true;
            wardEl.disabled = true;

            if (!provinceName) {
                updateFullAddress();
                return;
            }

            const pData = VIETNAM_ADDRESS_DATA.find(p => p.name === provinceName || provinceName.includes(p.name));
            if (!pData || !pData.districts) {
                districtEl.disabled = false;
                wardEl.disabled = false;
                updateFullAddress();
                return;
            }

            pData.districts.forEach(d => {
                const opt = document.createElement('option');
                opt.value = d.name;
                opt.textContent = d.name;
                if (selectedDistrictName && (d.name === selectedDistrictName || selectedDistrictName.includes(d.name))) {
                    opt.selected = true;
                }
                districtEl.appendChild(opt);
            });

            districtEl.disabled = false;
        }

        // Nạp danh sách Phường/Xã theo Quận đã chọn
        function populateWards(provinceName, districtName, selectedWardName) {
            wardEl.innerHTML = '<option value="">-- Chọn Phường / Xã --</option>';
            wardEl.disabled = true;

            if (!provinceName || !districtName) {
                updateFullAddress();
                return;
            }

            const pData = VIETNAM_ADDRESS_DATA.find(p => p.name === provinceName || provinceName.includes(p.name));
            if (!pData) return;

            const dData = pData.districts.find(d => d.name === districtName || districtName.includes(d.name));
            if (!dData || !dData.wards) {
                wardEl.disabled = false;
                updateFullAddress();
                return;
            }

            dData.wards.forEach(w => {
                const opt = document.createElement('option');
                opt.value = w;
                opt.textContent = w;
                if (selectedWardName && (w === selectedWardName || selectedWardName.includes(w))) {
                    opt.selected = true;
                }
                wardEl.appendChild(opt);
            });

            wardEl.disabled = false;
            updateFullAddress();
        }

        // Tự động tổng hợp chuỗi địa chỉ đầy đủ: [Số nhà, tên đường], [Phường/Xã], [Quận/Huyện], [Tỉnh/TP]
        function updateFullAddress() {
            if (!fullAddressEl) return;

            const provOpt = provinceEl.options[provinceEl.selectedIndex];
            const provText = provOpt && provOpt.value ? provOpt.textContent.trim() : '';

            const distVal = districtEl.value.trim();
            const wardVal = wardEl.value.trim();
            const detailVal = detailEl ? detailEl.value.trim() : '';

            const parts = [];
            if (detailVal) parts.push(detailVal);
            if (wardVal) parts.push(wardVal);
            if (distVal) parts.push(distVal);
            if (provText) parts.push(provText);

            fullAddressEl.value = parts.join(', ');
            if (typeof config.onChange === 'function') {
                config.onChange({
                    province: provText,
                    district: distVal,
                    ward: wardVal,
                    detail: detailVal,
                    fullAddress: fullAddressEl.value
                });
            }
        }

        // Bắt sự kiện thay đổi
        provinceEl.addEventListener('change', function() {
            populateDistricts(this.value);
            updateFullAddress();
        });

        districtEl.addEventListener('change', function() {
            populateWards(provinceEl.value, this.value);
            updateFullAddress();
        });

        wardEl.addEventListener('change', function() {
            updateFullAddress();
        });

        if (detailEl) {
            detailEl.addEventListener('input', function() {
                updateFullAddress();
            });
        }

        // Khởi tạo ban đầu
        populateProvinces(config.initialProvince || '');
        if (config.initialProvince) {
            populateDistricts(config.initialProvince, config.initialDistrict || '');
            if (config.initialDistrict) {
                populateWards(config.initialProvince, config.initialDistrict, config.initialWard || '');
            }
        }

        // Tự động bóc tách từ chuỗi địa chỉ có sẵn (Ví dụ: "123 Lê Lợi, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh")
        function prefillFromRawAddress(rawAddress) {
            if (!rawAddress) return;

            const raw = rawAddress.trim();
            if (!raw) return;

            // Tìm tỉnh thành trong raw
            let matchedProv = null;
            for (let p of VIETNAM_ADDRESS_DATA) {
                if (raw.toLowerCase().includes(p.name.toLowerCase())) {
                    matchedProv = p;
                    break;
                }
            }

            if (matchedProv) {
                populateProvinces(matchedProv.name);
                
                // Tìm quận huyện trong raw
                let matchedDist = null;
                for (let d of matchedProv.districts) {
                    if (raw.toLowerCase().includes(d.name.toLowerCase())) {
                        matchedDist = d;
                        break;
                    }
                }

                if (matchedDist) {
                    populateDistricts(matchedProv.name, matchedDist.name);

                    // Tìm phường xã trong raw
                    let matchedWard = null;
                    for (let w of (matchedDist.wards || [])) {
                        if (raw.toLowerCase().includes(w.toLowerCase())) {
                            matchedWard = w;
                            break;
                        }
                    }

                    if (matchedWard) {
                        populateWards(matchedProv.name, matchedDist.name, matchedWard);
                    }

                    // Phần còn lại là số nhà, đường
                    if (detailEl) {
                        let cleanDetail = raw;
                        [matchedProv.name, 'TP. ' + matchedProv.name, 'Tỉnh ' + matchedProv.name, matchedDist.name, matchedWard || ''].forEach(part => {
                            if (part) {
                                cleanDetail = cleanDetail.replace(new RegExp(part, 'gi'), '');
                            }
                        });
                        cleanDetail = cleanDetail.replace(/^[\s,]+|[\s,]+$/g, '').replace(/,\s*,/g, ',');
                        detailEl.value = cleanDetail || raw;
                    }
                } else {
                    if (detailEl) detailEl.value = raw;
                }
            } else {
                if (detailEl) detailEl.value = raw;
            }

            updateFullAddress();
        }

        return {
            prefill: prefillFromRawAddress,
            update: updateFullAddress,
            setValues: function(p, d, w, detail) {
                populateProvinces(p);
                populateDistricts(p, d);
                populateWards(p, d, w);
                if (detailEl && detail !== undefined) {
                    detailEl.value = detail;
                }
                updateFullAddress();
            }
        };
    }

    /**
     * Bổ sung tính năng tự động định dạng và kiểm tra số điện thoại trên Form
     */
    function attachPhoneFormatter(phoneInputEl, errorDisplayEl) {
        if (!phoneInputEl) return;

        function checkAndFormat(e) {
            let val = phoneInputEl.value.trim();
            if (!val) return;

            // Nếu người dùng nhập 09xx hoặc 84xx, tự động chuyển về +84
            if (val.startsWith('0') && val.length >= 2) {
                val = '+84' + val.substring(1);
                phoneInputEl.value = val;
            } else if (val.startsWith('84') && !val.startsWith('+84') && val.length >= 3) {
                val = '+' + val;
                phoneInputEl.value = val;
            } else if (!val.startsWith('+84') && /^\d+$/.test(val)) {
                val = '+84' + val;
                phoneInputEl.value = val;
            }

            const result = validateVietnamesePhone(val);
            if (!result.isValid && val.length >= 4) {
                phoneInputEl.style.borderColor = '#EF4444';
                if (errorDisplayEl) {
                    errorDisplayEl.style.display = 'block';
                    errorDisplayEl.textContent = result.message;
                }
            } else {
                phoneInputEl.style.borderColor = '';
                if (errorDisplayEl) {
                    errorDisplayEl.style.display = 'none';
                    errorDisplayEl.textContent = '';
                }
            }
        }

        phoneInputEl.addEventListener('blur', checkAndFormat);
        phoneInputEl.addEventListener('input', function() {
            let val = this.value;
            // Tự động thêm +84 nếu người dùng bắt đầu gõ
            if (val === '0') {
                this.value = '+84';
            }
            if (errorDisplayEl && errorDisplayEl.style.display !== 'none') {
                const res = validateVietnamesePhone(this.value);
                if (res.isValid) {
                    this.style.borderColor = '';
                    errorDisplayEl.style.display = 'none';
                }
            }
        });
    }

    // Export module ra window
    window.TechPilotAddress = {
        DATA: VIETNAM_ADDRESS_DATA,
        validatePhone: validateVietnamesePhone,
        initSelector: initVietnamAddressSelector,
        attachPhoneFormatter: attachPhoneFormatter
    };

})(window);

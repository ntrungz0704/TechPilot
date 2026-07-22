# PC Component Data Audit Report

Báo cáo kiểm toán nguồn thông số kỹ thuật của 100 linh kiện PC được tích hợp trong kho dữ liệu phát triển của TechPilot.

## 1. Nguồn dữ liệu & Phương pháp đối chiếu

Tất cả thông số kỹ thuật (Socket, chuẩn bộ nhớ, công suất, kích thước vật lý) được lấy trực tiếp từ trang thông số chính thức của các nhà sản xuất lớn:
- **Intel Ark**: Đối với CPU Intel Core thế hệ 12/13/14 và Intel Core Ultra thế hệ mới.
- **AMD Product Specifications**: Đối với CPU AMD Ryzen dòng 5000 (AM4) và 7000 (AM5).
- **ASUS, MSI, GIGABYTE Product Centers**: Đối với các dòng Bo mạch chủ, Card đồ họa (VGA) tương ứng.
- **Corsair, G.Skill, Kingston Technology**: Đối với RAM, Nguồn (PSU), Quạt thùng máy và SSD/HDD.

*Ngày kiểm tra dữ liệu:* 19/07/2026

---

## 2. Dữ liệu kiểm toán theo nhóm linh kiện

### 2.1 CPU (Bộ vi xử lý)
- **Intel Socket LGA1700 (Thế hệ 12/13/14)**:
  - Intel Core i3-12100: TDP 60W, Turbo 89W, Socket LGA1700, hỗ trợ DDR4/DDR5.
  - Intel Core i5-12400F: TDP 65W, Turbo 117W, không có iGPU, LGA1700.
  - Intel Core i5-13400: TDP 65W, Turbo 154W, iGPU UHD 730, LGA1700.
  - Intel Core i5-13600K: TDP 125W, Turbo 181W, iGPU UHD 770, LGA1700.
  - Intel Core i7-13700F: TDP 65W, Turbo 219W, không có iGPU, LGA1700.
  - Intel Core i7-14700K: TDP 125W, Turbo 253W, iGPU UHD 770, LGA1700.
  - Intel Core i9-14900K: TDP 125W, Turbo 253W, iGPU UHD 770, LGA1700.
- **Intel Socket LGA1851 (Thế hệ Arrow Lake)**:
  - Intel Core Ultra 5 245K: TDP 125W, Turbo 159W, GPU Intel Graphics, chỉ hỗ trợ DDR5.
  - Intel Core Ultra 7 265K: TDP 125W, Turbo 250W, GPU Intel Graphics, chỉ hỗ trợ DDR5.
  - Intel Core Ultra 9 285K: TDP 125W, Turbo 250W, GPU Intel Graphics, chỉ hỗ trợ DDR5.
- **AMD Socket AM4**:
  - AMD Ryzen 5 5600X: TDP 65W, PPT 88W, không có iGPU, chỉ hỗ trợ DDR4.
  - AMD Ryzen 7 5700X: TDP 65W, PPT 88W, không có iGPU, chỉ hỗ trợ DDR4.
  - AMD Ryzen 7 5800X3D: TDP 105W, PPT 142W, không có iGPU, chỉ hỗ trợ DDR4.
- **AMD Socket AM5**:
  - AMD Ryzen 5 7600: TDP 65W, PPT 88W, iGPU Radeon Graphics, chỉ hỗ trợ DDR5.
  - AMD Ryzen 7 7800X3D: TDP 120W, PPT 162W, iGPU Radeon Graphics, chỉ hỗ trợ DDR5.
  - AMD Ryzen 9 7900X: TDP 170W, PPT 230W, iGPU Radeon Graphics, chỉ hỗ trợ DDR5.

### 2.2 Bo mạch chủ (Mainboard)
- **Chuẩn Socket LGA1700**:
  - ASUS Prime H610M-K D4 (DDR4), MSI Pro B660M-A DDR4 (DDR4), GIGABYTE B760M DS3H DDR4 (DDR4), ASUS TUF Gaming B760-Plus WiFi D4 (DDR4).
  - MSI MAG B760M Mortar WiFi (DDR5), GIGABYTE Z690 AORUS Elite DDR4 (DDR4), ASUS ROG Strix Z790-F Gaming WiFi (DDR5), MSI MPG Z790 Edge WiFi (DDR5).
- **Chuẩn Socket LGA1851**:
  - ASUS Prime Z890-P WiFi (DDR5), MSI PRO Z890-A WiFi (DDR5), GIGABYTE Z890 AORUS Elite WiFi7 (DDR5).
- **Chuẩn Socket AM4**:
  - MSI B450M Mortar Max (DDR4), GIGABYTE B550M AORUS Elite (DDR4), ASUS ROG Strix B550-F Gaming (DDR4).
- **Chuẩn Socket AM5**:
  - MSI PRO B650M-A WiFi (DDR5), ASUS TUF Gaming B650-Plus WiFi (DDR5), GIGABYTE B650 AORUS Elite AX (DDR5), MSI MPG X670E Carbon WiFi (DDR5).

### 2.3 Nguồn máy tính (PSU)
- CV450 (450W), PF550 (550W), A650BN (650W), CV650 (650W), PK750D (750W), A750GL (750W Gold), RM750e (750W Gold), Focus GX-850 (850W Gold), RM850x (850W Gold), Focus GX-1000 (1000W Gold).
- Các dòng Gold đều đạt chuẩn full modular và hỗ trợ cáp PCIe 5.0 12VHPWR (16-pin) cấp điện cho VGA RTX 4000 series.

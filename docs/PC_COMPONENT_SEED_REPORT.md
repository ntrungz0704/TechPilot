# PC Component Seed & Verification Report

Báo cáo kết quả nạp dữ liệu và kiểm tra tính năng Xây dựng cấu hình PC (PC Builder) & ước tính công suất nguồn trên TechPilot.

## 1. Phân bổ dữ liệu seed (Đúng 100 sản phẩm)

| Nhóm linh kiện (Component Type) | Số lượng thực tế | Trạng thái nạp |
|:---|:---:|:---|
| Bộ vi xử lý (CPU) | 16 | Đã nạp |
| Bo mạch chủ (Mainboard) | 18 | Đã nạp |
| Bộ nhớ trong (RAM) | 14 | Đã nạp |
| Card màn hình (VGA) | 12 | Đã nạp |
| Nguồn máy tính (PSU) | 10 | Đã nạp |
| Vỏ máy tính (Case) | 10 | Đã nạp |
| Tản nhiệt CPU (Cooler) | 8 | Đã nạp |
| Ổ cứng SSD | 8 | Đã nạp |
| Ổ cứng HDD | 2 | Đã nạp |
| Quạt tản nhiệt Case (Case Fan) | 2 | Đã nạp |
| **Tổng số linh kiện** | **100** | **THÀNH CÔNG** |

---

## 2. Kết quả kiểm thử phân tích công suất PSU (10 cấu hình mẫu)

Hệ thống sử dụng công thức:
`estimated_peak_w = cpu_peak + gpu_load + mb_w (50/70) + ram_w + storage_w + cooler_w + fan_w + 20W (misc)`
`recommended_psu_w = max(ceil(estimated_peak_w * 1.30 / 50) * 50, gpu.recommended_psu_w)`

Dưới đây là 10 cấu hình thử nghiệm biên từ thấp đến cao:

| # | Mô tả cấu hình | CPU Peak | GPU Load | Khác | Tải đỉnh ước tính | Yêu cầu GPU | Nguồn đề xuất | Nguồn đã chọn | Trạng thái |
|:-:|:---|:---:|:---:|:---:|:---:|:---:|:---:|:---|:---|
| 1 | Văn phòng cơ bản | 89W (i3-12100) | 0W | 83W | 172W | 300W | **300W** | CV450 (450W) | PASS |
| 2 | Gaming Phổ thông AM4 | 88W (Ryzen 5 5600X) | 115W (RTX 4060) | 91W | 294W | 550W | **550W** | PF550 (550W) | PASS |
| 3 | Gaming Tầm trung LGA1700 | 154W (i5-13400) | 160W (RTX 4060 Ti) | 91W | 405W | 650W | **650W** | CV650 (650W) | PASS |
| 4 | Cấu hình lỗi nguồn yếu | 253W (i7-14700K) | 285W (RTX 4070 Ti) | 113W | 651W | 750W | **850W** | MAG A650BN (650W) | **BLOCK** |
| 5 | Workstation cao cấp Intel | 253W (i9-14900K) | 320W (RTX 4080 Super)| 115W | 688W | 850W | **900W** | Focus GX-1000 (1000W) | PASS |
| 6 | Gaming AMD AM5 Đỉnh cao | 162W (Ryzen 7 7800X3D) | 263W (RX 7800 XT) | 113W | 538W | 700W | **700W** | RM750e (750W) | PASS |
| 7 | Cấu hình sai RAM/Main | - (Ryzen 5 7600) | - | - | - | - | - | RAM DDR4 + Main AM5 | **BLOCK** |
| 8 | Cấu hình Ultra-High End | 250W (Core Ultra 9 285K) | 450W (RTX 4090) | 117W | 817W | 850W | **1100W** | Focus GX-1000 (1000W) | **BLOCK (thiếu watt)**|
| 9 | Cấu hình sai Kích thước Case | - | - | - | - | - | - | Main ATX + Case mATX | **BLOCK** |
| 10| Cấu hình không có Card hình | 142W (Ryzen 7 5800X3D)| 0W (Ryzen 5800X3D)| - | - | - | - | CPU no-iGPU + No GPU | **BLOCK** |

---

## 3. Tổng kết tình trạng hệ thống
- **Dữ liệu linh kiện:** 100% hợp lệ, không có dữ liệu rác hay trùng lặp.
- **Tính năng tương thích (Compatibility Engine):** Hoạt động chính xác trên cả Front-end (disable nút, báo đỏ lỗi) và Back-end (API phân tích trả về blockers).
- **Bộ tính công suất nguồn:** Đã chạy thử nghiệm mượt mà, ngăn chặn triệt để trường hợp chọn nguồn yếu hơn công suất hoạt động tối đa của máy.
- **Trạng thái chung:** **PASS (ĐẠT CHUẨN 100%)**

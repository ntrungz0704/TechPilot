# TechPilot Phase 0 — Decision Log

| Thuộc tính | Giá trị |
|---|---|
| Trạng thái | **LOCKED** |
| Phiên bản | `v1.0` |
| Ngày khóa | 2026-07-15 |

## 1. Accepted decisions

| ID | Quyết định | Lý do | Tác động |
|---|---|---|---|
| `D-001` | Giữ PHP MVC + JavaScript thuần | Stack hiện có đã chạy và phù hợp quy mô P0 | Không rewrite React/Vue; chỉ refactor theo layer/component |
| `D-002` | Migrate từng lớp và từng route | CSS hiện có nhiều hard-code/inline/`!important`; rewrite toàn bộ có blast radius cao | Giữ token cũ trong thời gian chuyển đổi; chỉ xóa sau regression |
| `D-003` | P0 là purchase funnel commerce-first | Tạo một bản phát hành sử dụng được trước khi mở rộng | Home → PLP → PDP → Cart → Checkout → Success là critical path |
| `D-004` | Guest checkout được phép | Giảm friction và backend dependency | Login/register là hỗ trợ, không chặn đặt hàng |
| `D-005` | COD là payment method vận hành thật duy nhất trong P0 | Không có gateway/callback an toàn hiện tại | Bank/QR không xuất hiện như lựa chọn khả dụng |
| `D-006` | Shipping cố định 30.000₫ cho order có hàng | Khớp logic checkout hiện có và tránh tích hợp logistics sớm | Cart/Checkout/Order dùng chung một helper/service |
| `D-007` | Server-authoritative price/stock/total | Session hiện giữ price và có thể lỗi thời | Mọi render/update/submit đọc lại database; transaction khi tạo order |
| `D-008` | Max quantity mặc định 10/SKU và không vượt stock | Ngăn quantity bất thường và race đơn giản | Giá trị phải nằm trong config, không hard-code rải rác |
| `D-009` | Search contract dùng `q`, `cat`, `brand`, `price_min`, `price_max`, `sort`, `page` | URL có thể chia sẻ, refresh và Back/Forward | Backend PLP cần nâng cấp trước Build Gate |
| `D-010` | Page size P0 là 20 | Khớp grid desktop 5 cột, tablet 4 cột, mobile 2 cột | Không cần page-size selector trong UI |
| `D-011` | Light mode là release baseline | Giảm nhân đôi QA trong P0 | Dark tokens được chuẩn bị; theme hiện có không được vỡ, polish toàn bộ ở P1 |
| `D-012` | AI/PC Builder không được xây engine trong P0 | Phụ thuộc data taxonomy/rule engine chưa đủ | Chỉ dùng entry “Sắp ra mắt” hoặc điều hướng theo nhu cầu không giả AI |
| `D-013` | Coupon, forgot password, wishlist, compare và account mở rộng thuộc P1 | Không chặn critical purchase journey | Ẩn hoặc không render action; không dùng link `#` |
| `D-014` | Một `/support` hub tối thiểu trong P0 | Tạo trust và thay các footer link giả với effort thấp | Hợp nhất shipping/payment/warranty/contact; trang riêng ở P1 |
| `D-015` | Canonical route giữ theo router hiện có | Hạn chế thay đổi routing không tạo giá trị | Search `/home/search`, category `/home/category/{slug}` |
| `D-016` | Thiết kế ở 1440/1024/390, stress-test 320/360/768/1920 | Bao phủ desktop/tablet/mobile mà không nhân mọi width | Responsive rule quan trọng hơn pixel-perfect ở mọi kích thước |
| `D-017` | Token-first rồi vertical slice | Phát hiện vấn đề component/feasibility trước khi nhân rộng | Home → PLP → PDP → Cart được duyệt trước full P0 |
| `D-018` | Figma semantic token là source; CSS có mapping 1:1 | Tránh lệch giữa design và code | Nếu native Variables bị giới hạn, dùng styles/naming chuẩn và token table, không hard-code tùy ý |
| `D-019` | Success dùng PRG và context an toàn | Refresh không tạo order mới; tránh lộ guest order | Missing context có fallback, không lookup order chỉ bằng code đoán được |
| `D-020` | Analytics không thu PII | Đo funnel mà không đưa dữ liệu nhạy cảm vào event | Không gửi tên, phone, email hoặc address |
| `D-021` | Full dark mode, admin, native app và animation nặng không thuộc P0 | Bảo vệ timeline và chất lượng funnel | Chuyển backlog P1/P2 |

## 2. Known constraints

| ID | Constraint | Cách xử lý đã khóa |
|---|---|---|
| `C-001` | Native Figma Variables automation bị giới hạn ở workspace/plan hiện tại | Dùng semantic styles + token table + CSS mapping; nâng plan chỉ khi library scale yêu cầu |
| `C-002` | Database có thể không kết nối và model đang fallback | Fallback chỉ demo; production phải báo service unavailable và không clear cart |
| `C-003` | Asset product bị trùng/placeholder và thiếu banner | Asset manifest + fallback; Content đóng gap trước screen freeze |
| `C-004` | Product `specs` là JSON không đồng nhất giữa category | P0 chỉ derive 4–5 specs chính theo mapping category; taxonomy đầy đủ ở P1 |
| `C-005` | Schema chưa có email/structured address/payment status/SKU/warranty | BE duyệt migration tối thiểu trước Phase 5 |

## 3. Change request template

Mọi thay đổi sau baseline `v1.0` dùng mẫu sau:

```md
## CR-XXX — Tên thay đổi

- Ngày:
- Người đề xuất:
- Vấn đề/bằng chứng:
- Persona/journey bị ảnh hưởng:
- Đề xuất:
- Acceptance criteria:
- Effort:
- Rủi ro:
- Tác động timeline:
- Hạng mục P0 thay thế (nếu có):
- Quyết định: Accepted / Rejected / Moved to P1/P2
- Người duyệt:
```

## 4. Approval rules

- Chỉ nâng scope P0 cho pháp lý, bảo mật, sai giá/đơn, mất dữ liệu hoặc blocker của critical journey.
- Product owner duyệt phạm vi; Design xác nhận UX impact; Engineering ước lượng; QA xác nhận acceptance.
- Trao đổi miệng không thay đổi baseline.
- Thay đổi sau Design Gate phải tăng version và ghi rõ ảnh hưởng tới screen/component/timeline.
- Buffer 15–20% không được dùng làm ngân sách cho feature mới.

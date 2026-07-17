# TechPilot Phase 0 — Scope Lock

| Thuộc tính | Giá trị |
|---|---|
| Trạng thái | **LOCKED** |
| Phiên bản | `v1.0` |
| Ngày khóa | 2026-07-15 |
| Phê duyệt | Project owner đã yêu cầu khóa và bắt đầu Phase 0 |
| Phạm vi | Storefront responsive tiếng Việt, tiền tệ VND |
| Stack giữ lại | PHP MVC, MySQL/MariaDB, HTML/CSS, JavaScript thuần |
| Tài liệu visual | [`../TECHPILOT-UXUI-V2.md`](../TECHPILOT-UXUI-V2.md) |

Tài liệu này là baseline chính thức cho thiết kế và triển khai P0. Mọi thay đổi phạm vi sau ngày khóa phải đi qua quy trình change control ở cuối tài liệu.

## 1. Product direction

TechPilot là website bán thiết bị công nghệ dành cho người mua tại Việt Nam, giúp khách hàng chọn đúng sản phẩm theo nhu cầu, ngân sách và hiệu năng mà không buộc họ tự giải mã hàng loạt thông số kỹ thuật.

Định vị trải nghiệm:

- Premium nhưng nhanh và thực dụng.
- Rõ ràng trước; chi tiết kỹ thuật xuất hiện khi cần quyết định.
- Giá, tồn kho, giao nhận và bảo hành phải minh bạch.
- Search-first và mobile-first.
- AI chỉ xuất hiện khi có giá trị thật; không dùng như nhãn marketing rỗng.

Visual direction kết hợp độ thoáng của Apple, khả năng mua nhanh của Best Buy, chiều sâu kỹ thuật của Newegg và hành vi mua hàng Việt Nam của GEARVN. TechPilot không đi theo hướng gaming-neon dày đặc.

## 2. Personas và jobs-to-be-done

### Guided shopper — primary

- Cần laptop/PC cho học tập, văn phòng hoặc giải trí nhưng không rành cấu hình.
- Muốn tìm lựa chọn phù hợp ngân sách và hiểu lý do được đề xuất.
- Thành công khi có thể tự tin thêm sản phẩm vào giỏ mà không cần hỏi nhân viên.

### Performance buyer — primary

- Mua máy gaming, creator hoặc linh kiện.
- Muốn lọc nhanh theo thông số quyết định và đánh giá hiệu năng/giá trị.
- Thành công khi tìm được cấu hình đáp ứng yêu cầu mà không phải mở nhiều website.

### Trust-focused shopper — secondary

- Chuẩn bị chi một khoản tiền lớn.
- Muốn xác minh tồn kho, giao hàng, thanh toán, bảo hành và đổi trả.
- Thành công khi hoàn tất checkout mà không gặp chi phí bất ngờ hoặc thông tin mơ hồ.

## 3. Critical journeys

1. **Khám phá:** Home → Category/PLP → Filter/Sort → PDP → Add to cart.
2. **Tìm cụ thể:** Search → Results → PDP.
3. **Đánh giá:** PDP → giá/tồn kho/thông số/giao nhận/bảo hành → Add to cart.
4. **Mua hàng:** Cart → cập nhật số lượng → Checkout → Review → Submit → Success.
5. **Phục hồi lỗi:** hết hàng, đổi giá, dữ liệu sai hoặc tạo đơn thất bại phải có thông báo và hướng xử lý; dữ liệu form không bị mất.
6. **Mobile purchase:** toàn bộ các hành trình trên hoàn thành được từ viewport 360px mà không cần chuyển sang desktop.

## 4. Sitemap P0

```text
Global shell
├── Announcement
├── Header + global search
├── Category navigation / mobile drawer
├── Account entry
├── Cart entry
└── Footer

Storefront
├── /                                  Home
├── /home/search                       Search / PLP
├── /home/category/{slug}              Category / PLP
├── /product/detail/{slug}             Product detail
├── /cart                              Cart
├── /checkout                          Checkout
├── /checkout/success                  Order success
├── /auth/login                        Login
├── /auth/register                     Register
├── /support                           Support hub tối thiểu
└── /404                               Not found
```

`/support` là một trang tĩnh hợp nhất các nội dung giao hàng, thanh toán, bảo hành/đổi trả và liên hệ. Đây là giải pháp P0 để tạo trust và loại bỏ liên kết giả mà không mở rộng thành nhiều trang nội dung riêng.

## 5. Scope P0 — bắt buộc

### Design foundation

- Brand/semantic tokens, typography tiếng Việt, spacing, radius, grid và elevation.
- Component library cho navigation, form, commerce và feedback.
- Light theme là baseline duyệt phát hành.
- Dark semantic tokens được chuẩn bị; dark mode hiện có không được vỡ nhưng full visual polish không phải release gate P0.

### Global experience

- Header desktop/tablet/mobile.
- Search truy cập được trên mọi viewport.
- Mega menu trên desktop; category drawer trên tablet/mobile.
- Cart badge, account entry và footer.
- Responsive tại 1440, 1024 và 390; QA thêm tại 320, 768 và 1920.

### Discovery

- Homepage đã rút gọn, không lặp toàn bộ danh mục.
- Search/PLP có product count, filter chính, active filter chips, sort và pagination.
- Filter/sort hoạt động thật và được phản ánh trên URL.
- Product card có ảnh, tên, thông số chính, giá, khuyến mãi, tồn kho, rating khi có dữ liệu và CTA dùng được trên touch.

### Product detail

- Gallery, tên, giá, tồn kho, thông số chính, giao nhận, bảo hành và CTA.
- Trạng thái còn hàng, sắp hết, hết hàng, ảnh lỗi và không có review.
- Purchase panel sticky trên desktop và buy bar sticky trên mobile.
- Sản phẩm liên quan.

### Purchase funnel

- Cart rỗng/có hàng; ảnh thật; cập nhật số lượng; xóa; phát hiện đổi giá/hết hàng.
- Guest checkout; đăng nhập không phải điều kiện đặt hàng.
- Thông tin liên hệ và địa chỉ nhận hàng.
- COD là phương thức thanh toán vận hành thật của P0.
- Review order, validation theo field, processing, order failure và success.
- Giá, tồn kho, phí giao hàng và tổng tiền được server xác nhận trước khi tạo đơn.

### Supporting scope

- Login và register cơ bản.
- Support hub tối thiểu.
- 404/generic error.
- Loading, empty, error, disabled, processing và out-of-stock states.
- WCAG 2.2 AA cho critical journeys.
- Analytics event contract cho search, filter, PDP, add-to-cart, checkout và order result.

## 6. Không thuộc P0

- Viết lại dự án bằng React/Vue hoặc thay kiến trúc backend.
- Admin/CMS redesign.
- Marketplace hoặc multi-vendor.
- Cổng thanh toán thật và callback ngân hàng/QR.
- Coupon engine, promotion phức tạp và loyalty.
- Forgot/reset password bằng email.
- Account dashboard, address book và lịch sử/chi tiết/theo dõi đơn.
- Wishlist, compare, review submission và Q&A.
- Search autocomplete nâng cao.
- AI shopping advisor thật, PC Builder và compatibility engine.
- Cá nhân hóa homepage, 3D viewer, parallax hoặc animation nặng.
- Full dark-mode fidelity cho toàn bộ screen.
- Native app và redesign trang quản trị.

Các control hoặc liên kết thuộc danh sách trên phải được ẩn, loại khỏi P0 hoặc gắn nhãn “Sắp ra mắt” không gây hiểu nhầm; không dùng `href="#"` cho hành động trông như có thể sử dụng.

## 7. P1 và P2 backlog đã khóa

### P1 — retention và self-service

- Account/profile/address book.
- Order list/detail/tracking và hủy đơn nếu nghiệp vụ cho phép.
- Forgot/reset password.
- Wishlist, compare, review/Q&A.
- Coupon/promotion nâng cao.
- Search autocomplete.
- Recently viewed và recommendation theo rule.
- Finder Lite dạng questionnaire.
- News, store locator và các trang policy riêng.

### P2 — differentiation

- TechPilot AI Advisor thật.
- AI recommendation có giải thích.
- PC Builder với kiểm tra tương thích và tổng công suất.
- Lưu/chia sẻ cấu hình.
- Personalization, loyalty, trade-in và omnichannel showroom.

## 8. Baseline kỹ thuật đã xác nhận

- Dự án dùng PHP MVC + HTML/CSS/JavaScript thuần và MySQL/MariaDB.
- Model hiện có: Product, Order, User, Review, Brand, Banner và Post.
- Schema đã có products, product_images, carts, cart_items, orders, order_items, flash_sales, coupons, wishlists và reviews.
- Cart runtime hiện lưu trong `$_SESSION['cart']`.
- Cart hiện hiển thị shipping bằng `0`, trong khi checkout tính `30.000₫`; P0 phải dùng một nguồn tính phí duy nhất.
- Cart/session hiện giữ price nhưng không giữ image/stock và chưa đọc lại dữ liệu server khi render/submit.
- Search hiện chỉ hỗ trợ keyword hoặc category, giới hạn 24; chưa có filter giá, brand, sort hoặc pagination thật.
- Checkout hiện chưa validate đầy đủ, chưa giữ old input và chưa có idempotency protection.
- Order model có fallback trả success khi database không kết nối; fallback này chỉ dành cho demo và không được coi là production success.

Các tài liệu triển khai khóa kèm theo:

- [`SCREEN-STATE-MATRIX.md`](SCREEN-STATE-MATRIX.md): route, state, responsive và accessibility coverage.
- [`BUSINESS-RULES-AND-DATA-CONTRACTS.md`](BUSINESS-RULES-AND-DATA-CONTRACTS.md): business rules, normalized view-model và release blockers.
- [`DECISION-LOG.md`](DECISION-LOG.md): quyết định đã chấp nhận, constraint và change-control template.

## 9. Asset baseline

- Dùng `public/assets/images/logo.svg` hoặc `public/design-v2/logo-v2.svg`; không dùng `logo.png` 2.1MB trong header production.
- Cần loại bỏ hoặc thay các ảnh sản phẩm trùng/placeholder trước Design Gate.
- Các asset từng được giao diện tham chiếu nhưng chưa có: `promo-banner-2.jpg`, `promo-banner-3.jpg`, `installment-banner.jpg`.
- Banner phải có crop/tỷ lệ riêng cho desktop và mobile.
- Target: logo dưới 50KB, ảnh product WebP dưới 120KB, hero dưới 300KB khi có thể.

## 10. KPIs và release gates

### UX/Design Gate

- 100% màn hình và state P0 có thiết kế ở 1440/1024/390.
- Không có dead end trong critical journeys.
- Ít nhất 4/5 người dùng thử hoàn thành tìm sản phẩm và checkout không cần trợ giúp.
- Component không detach tùy tiện; contrast, focus và responsive behavior đã được kiểm tra.

### Build Gate

- Purchase funnel chạy end-to-end với dữ liệu tích hợp.
- Không có blocker/critical bug, console error, ảnh 404 hoặc liên kết giả trong P0.
- Không horizontal scroll tại 320, 390, 768, 1024, 1440.
- Keyboard hoàn thành được critical journey; touch target tối thiểu 44×44px.
- Checkout không làm mất dữ liệu khi validation/server failure.
- Giá, tồn kho, shipping và tổng tiền nhất quán giữa Cart → Checkout → Order.

### Performance target

- WCAG 2.2 AA cho critical journeys.
- Lighthouse Accessibility mục tiêu ≥95; Performance mobile mục tiêu ≥85 trong môi trường test thống nhất.
- LCP ≤2.5s, INP ≤200ms, CLS ≤0.1 tại percentile 75 sau khi có dữ liệu production.

### Analytics sau release

- Search success và zero-result rate.
- Filter usage.
- PLP → PDP click-through.
- PDP → add-to-cart.
- Cart → checkout start.
- Checkout completion và order failure reason.

Hai tuần đầu sau beta dùng để tạo baseline; không thêm tính năng chỉ nhằm “cải thiện KPI” khi chưa đủ dữ liệu.

## 11. Change control

Tài liệu này là baseline `v1.0`.

Mọi change request phải ghi:

1. Vấn đề và bằng chứng.
2. Persona/journey bị ảnh hưởng.
3. Độ ưu tiên và acceptance criteria.
4. Effort, rủi ro và tác động timeline.
5. Người duyệt và phiên bản tài liệu mới.

Chỉ thêm vào P0 nếu yêu cầu liên quan đến pháp lý, bảo mật, tính đúng của giá/đơn hàng, mất dữ liệu hoặc blocker của critical journey. Tính năng mới thông thường chuyển sang P1/P2.

Buffer 15–20% dành cho rủi ro tích hợp và lỗi, không phải ngân sách cho tính năng mới.

## 12. Phase 0 exit checklist

- [x] Định vị, personas và critical journeys được khóa.
- [x] Sitemap P0/P1/P2 được khóa.
- [x] In-scope/out-of-scope được khóa.
- [x] Stack và chiến lược migrate được khóa.
- [x] Business decisions cốt lõi được khóa.
- [x] Screen/state matrix hoàn tất.
- [x] Data contracts và backend gaps hoàn tất.
- [x] Decision log hoàn tất.
- [x] Kiểm tra chéo toàn bộ package và đánh dấu Phase 0 complete.

**Kết luận:** Phase 0 đã vượt gate và được đóng ở phiên bản `v1.0`. Phase tiếp theo được phép bắt đầu là Phase 1 — Foundations và Semantic Design Tokens.

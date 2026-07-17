# TechPilot UX/UI v2 — Figma-first specification

## 1. Product direction

TechPilot v2 được định vị là **nhà bán lẻ công nghệ đáng tin cậy, hiện đại và có khả năng tư vấn thông minh**.

- 70% giao diện dùng Cloud White/White để tạo khoảng thở.
- 15% dùng Graphite cho chữ, navbar và footer premium.
- 10% dùng Tech Blue cho CTA mua hàng, trạng thái chọn và liên kết.
- 5% còn lại dành cho Electric Cyan, AI Violet và màu trạng thái.
- Electric Cyan chỉ dùng cho hover, dữ liệu, viền sáng và chi tiết công nghệ.
- AI Violet chỉ dùng cho AI Advisor, gợi ý thông minh và kiểm tra tương thích.
- Không dùng phong cách gaming neon trên toàn website.

Nguồn cảm hứng bố cục:

- Apple: khoảng thở, campaign storytelling và trải nghiệm sản phẩm premium.
- Best Buy: bộ lọc, giao/nhận hàng, compare và khả năng ra quyết định nhanh.
- Newegg: thông số kỹ thuật, PC Builder và kiểm tra tương thích.
- GEARVN: hành vi mua hàng Việt Nam, bảo hành, trả góp, showroom và hotline.

Không sao chép giao diện hoặc tài sản của các thương hiệu tham khảo.

## 2. Cấu trúc file Figma

1. `00 Cover`
2. `01 Foundations`
3. `02 Components`
4. `03 Patterns`
5. `10 Home`
6. `11 PLP & Search`
7. `12 PDP`
8. `13 Cart & Checkout`
9. `14 Account`
10. `15 AI & PC Builder`
11. `16 Content & Support`
12. `90 Prototypes`

Frame chuẩn cho P0:

- Desktop: 1440px.
- Tablet: 1024px.
- Mobile: 390px.

## 3. Figma variable collections

### Primitives

Một mode `Value`. Primitives ẩn khỏi picker component.

#### Blue

| Token | Value |
| --- | --- |
| `blue/50` | `#EEF5FF` |
| `blue/100` | `#D8E8FF` |
| `blue/200` | `#B7D4FF` |
| `blue/300` | `#88B8FF` |
| `blue/400` | `#4F96FA` |
| `blue/500` | `#146EF5` |
| `blue/600` | `#0B57D0` |
| `blue/700` | `#0A46A8` |
| `blue/800` | `#0C3B82` |
| `blue/900` | `#102F5F` |

#### Cyan

| Token | Value |
| --- | --- |
| `cyan/50` | `#ECFBFF` |
| `cyan/100` | `#CFF7FF` |
| `cyan/200` | `#A5EFFF` |
| `cyan/300` | `#67E5FF` |
| `cyan/400` | `#22D5FF` |
| `cyan/500` | `#00C8FF` |
| `cyan/600` | `#009ECD` |
| `cyan/700` | `#087DA1` |
| `cyan/800` | `#0D647E` |
| `cyan/900` | `#0F5268` |

#### Violet

| Token | Value |
| --- | --- |
| `violet/50` | `#F0F0FF` |
| `violet/100` | `#E1E1FF` |
| `violet/200` | `#C7C7FF` |
| `violet/300` | `#A3A3FF` |
| `violet/400` | `#7E7EFF` |
| `violet/500` | `#5B5BFF` |
| `violet/600` | `#4444E6` |
| `violet/700` | `#3636BD` |
| `violet/800` | `#303096` |
| `violet/900` | `#2B2B75` |

#### Neutral

| Token | Value |
| --- | --- |
| `neutral/0` | `#FFFFFF` |
| `neutral/25` | `#F6F8FC` |
| `neutral/50` | `#EEF3F9` |
| `neutral/100` | `#D9E2F0` |
| `neutral/200` | `#B7C5D8` |
| `neutral/300` | `#94A3B8` |
| `neutral/400` | `#627187` |
| `neutral/500` | `#475569` |
| `neutral/600` | `#334155` |
| `neutral/700` | `#243044` |
| `neutral/800` | `#171E2E` |
| `neutral/900` | `#0E1320` |
| `neutral/950` | `#080C14` |

#### Feedback

| Token | Value |
| --- | --- |
| `success/50` | `#E8F8F2` |
| `success/500` | `#10B981` |
| `success/700` | `#047857` |
| `warning/50` | `#FFF4D6` |
| `warning/500` | `#F59E0B` |
| `warning/700` | `#B45309` |
| `error/50` | `#FEECEC` |
| `error/500` | `#EF4444` |
| `error/700` | `#B91C1C` |

### Semantic

Hai mode `Light` và `Dark`. Component chỉ bind semantic variables.

| Token | Light | Dark |
| --- | --- | --- |
| `background/canvas` | `neutral/25` | `neutral/950` |
| `background/surface` | `neutral/0` | `neutral/900` |
| `background/subtle` | `neutral/50` | `neutral/800` |
| `background/elevated` | `neutral/0` | `neutral/700` |
| `background/inverse` | `neutral/900` | `neutral/25` |
| `text/primary` | `neutral/900` | `neutral/25` |
| `text/secondary` | `neutral/500` | `neutral/200` |
| `text/tertiary` | `neutral/400` | `neutral/300` |
| `text/inverse` | `neutral/0` | `neutral/900` |
| `text/link` | `blue/600` | `cyan/500` |
| `text/link-hover` | `blue/700` | `cyan/300` |
| `border/default` | `neutral/100` | `neutral/700` |
| `border/strong` | `neutral/200` | `neutral/600` |
| `action/primary` | `blue/500` | `blue/500` |
| `action/primary-hover` | `blue/600` | `blue/600` |
| `action/primary-pressed` | `blue/700` | `blue/700` |
| `action/ai` | `violet/500` | `violet/500` |
| `focus/ring` | `blue/600` | `cyan/500` |

### Layout

Spacing: `0, 2, 4, 8, 12, 16, 20, 24, 32, 40, 48, 64, 80, 96`.

Radius:

- `radius/xs`: 4px.
- `radius/sm`: 8px.
- `radius/md`: 12px.
- `radius/lg`: 16px.
- `radius/xl`: 24px.
- `radius/full`: 999px.

### Motion

- Fast: 120ms.
- Standard: 180ms.
- Slow: 280ms.
- Carousel: 450ms.
- Easing: `cubic-bezier(.2,0,0,1)`.
- Luôn hỗ trợ reduced motion.

## 4. Typography

Font: `Be Vietnam Pro`; fallback `Inter, Arial, sans-serif`.

| Style | Desktop | Mobile |
| --- | --- | --- |
| `display/hero` | 48/56, 700 | 32/40, 700 |
| `heading/h1` | 32/40, 700 | 28/36, 700 |
| `heading/h2` | 24/32, 700 | 20/28, 700 |
| `heading/h3` | 20/28, 600 | 18/26, 600 |
| `title` | 18/26, 600 | 16/24, 600 |
| `body/lg` | 16/26, 400 | 16/24, 400 |
| `body/md` | 14/22, 400 | 14/22, 400 |
| `body/sm` | 12/18, 400 | 12/18, 400 |
| `label` | 14/20, 600 | 14/20, 600 |
| `button` | 14/20, 700 | 14/20, 700 |
| `price/lg` | 28/36, 800 | 24/32, 800 |
| `price/md` | 20/28, 700 | 18/26, 700 |

Giá, phần trăm và mã đơn dùng tabular numbers. Tên sản phẩm trên card tối đa hai dòng. Không dùng text thao tác nhỏ hơn 14px.

## 5. Responsive grid

| Frame | Container | Grid | Margin / Gutter | Product grid |
| --- | ---: | ---: | --- | ---: |
| 1440 | 1280 | 12 cột | 80 / 24 | 5 |
| 1280 | 1184 | 12 cột | 48 / 20 | 5 |
| 1024 | 960 | 8 cột | 32 / 20 | 4 |
| 768 | 720 | 8 cột | 24 / 16 | 3 |
| 390/360 | Fluid | 4 cột | 16 / 12 | 2 |

Breakpoints triển khai: 1280, 1024, 768, 480.

## 6. Component inventory

### Navigation

- Announcement bar.
- Header desktop/tablet/mobile.
- Global search autocomplete.
- Mega menu và category drawer.
- Desktop nav và mobile bottom navigation.
- Breadcrumb.
- Footer desktop và accordion mobile.

### Actions and forms

- Button: Primary, Secondary, Outline, Ghost, AI, Destructive, Inverse.
- Size: 32, 40, 48, 56.
- Input, textarea, select, autocomplete và search.
- Checkbox, radio, switch, range slider.
- Payment method radio card.
- Coupon input và quantity stepper.
- States: default, hover, focus, filled, error, disabled, loading, success.

### Commerce

- Product card: Grid, List, Compact, Flash Sale, Recommended.
- Product card states: default, hover, touch, loading, out of stock, selected-to-compare.
- Gallery, Price block, Rating, Spec chips, Stock và Delivery status.
- Offer/coupon, Warranty panel, Cart line item, Order summary.
- Shipping option, Order timeline, Review, Compare tray, Recently viewed.

### Feedback

- Alert, toast và inline validation.
- Tooltip, popover, dropdown.
- Modal, drawer và bottom sheet.
- Skeleton, empty, no-result, error và offline states.
- Pagination và load more.

## 7. Core screen requirements

### Homepage

1. Announcement, header, search và nav.
2. Hero sản phẩm chủ lực + AI Advisor.
3. Trust strip.
4. Flash Sale.
5. Popular categories.
6. Best sellers hoặc personalized recommendations.
7. Một campaign lớn.
8. Tối đa 3–4 nhóm sản phẩm ưu tiên.
9. AI Advisor hoặc PC Builder.
10. Brand partners, content, newsletter và footer.

### PLP / Search

- Breadcrumb, title, result count và quick chips.
- Active filter chips.
- Sidebar sticky desktop; drawer/bottom sheet mobile.
- Sort, grid/list switch, product cards và compare tray.
- Có loading, no-result và error states.
- Filter và sort phải có state thật ở frontend implementation.

### Product Detail

- Gallery 7 cột + purchase panel 5 cột trên desktop.
- Purchase panel sticky.
- Rating, SKU, giá, tiết kiệm, trả góp, tồn kho, cửa hàng và giao hàng.
- Variant selection, offer, warranty, wishlist, compare và CTA.
- Thông số, AI Match, review, Q&A, sản phẩm tương thích.
- Mobile có sticky buy bar; tabs chuyển thành accordion.

### Cart and Checkout

- Cart dùng ảnh thật; tên sản phẩm dẫn về PDP.
- Phí vận chuyển nhất quán giữa cart và checkout.
- Stepper: Giỏ hàng → Giao hàng → Thanh toán → Hoàn tất.
- Địa chỉ có cấu trúc, delivery options, payment cards và coupon.
- Giữ lại dữ liệu khi validation lỗi.
- Có processing, success và failure states.

## 8. Accessibility acceptance criteria

- Text thường đạt tối thiểu 4.5:1; chữ lớn đạt 3:1.
- White trên Tech Blue: khoảng 4.59:1.
- White trên AI Violet: khoảng 4.79:1.
- Graphite trên Cyan: khoảng 9.45:1.
- Graphite trên Emerald: khoảng 7.31:1.
- Graphite trên Amber: khoảng 8.63:1.
- Graphite trên Crimson: khoảng 4.93:1.
- Link trên Cloud White dùng Blue 600, không dùng Blue 500.
- Focus ring 2px, offset 2px; không xóa outline nếu không có thay thế.
- Target mobile tối thiểu 44×44px.
- Trạng thái không dựa hoàn toàn vào màu.
- Carousel có điều khiển, dừng được, hỗ trợ keyboard và reduced motion.
- Mega menu, drawer và modal quản lý focus đúng.
- Sticky header/CTA không che phần tử đang focus.

## 9. Audit findings cần sửa khi triển khai production

- Header desktop ba tầng hiện quá dày; mobile hiện đang ẩn search và nav mà không có phương án thay thế.
- Add-to-cart trên product card không được phụ thuộc hover.
- Cart đang báo miễn phí nhưng checkout cộng 30.000 ₫.
- Price filter và sort hiện mới là UI giả.
- Nhiều CTA đang trỏ `#`.
- Thiếu focus-visible, ARIA và keyboard behavior cho carousel, tab, menu và thumbnail.
- Logo SVG đang dùng màu primary cũ `#0A5BFF`; cần đổi sang `#146EF5`.
- Các raster asset có nhiều file trùng và một số banner đang thiếu.

## 10. Prototype routes

- Home: `/design-v2/index.html`
- PLP: `/design-v2/catalog.html`
- PDP: `/design-v2/product.html`
- Checkout: `/design-v2/checkout.html`
- UI Kit: `/design-v2/ui-kit.html`

Prototype nằm riêng trong `public/design-v2`; chưa ghi đè frontend production.


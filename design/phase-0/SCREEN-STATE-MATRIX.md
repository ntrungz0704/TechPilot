# TechPilot P0 — Screen and State Matrix

| Thuộc tính | Giá trị |
|---|---|
| Trạng thái | **LOCKED** |
| Phiên bản | `v1.0` |
| Breakpoint chính | 1440, 1024, 390 |
| Stress-test | 320, 360, 768, 1920 |

## 1. Quy ước

- `F`: cần full-page frame riêng ở breakpoint đó.
- `P`: thể hiện bằng component/pattern state; không nhân thêm full-page frame.
- Mỗi public route P0 có default frame tại 1440, 1024 và 390.
- Owner: `DS` Design System, `DISC` Discovery, `COM` Commerce, `ID` Identity, `FE`, `BE`, `Content`, `QA`.

## 2. Matrix khóa

| ID | Route/pattern | States bắt buộc | 1440 | 1024 | 390 | Owner | Dependency |
|---|---|---|---|---|---|---|---|
| `GLB-01` | Global shell | Guest/cart 0; guest/cart có hàng; authenticated; search focused/loading/result/no-result/error; mega menu/category drawer; offline toast | Search + mega `F`; còn lại `P` | Search + drawer `F`; còn lại `P` | Search full-screen + menu/bottom nav `F`; còn lại `P` | DS | Search, auth/session, campaign content |
| `HOM-01` | `/` | Populated; loading skeleton; module empty/error; image fallback; carousel active/paused; flash active/expired | Default + loading `F`; còn lại `P` | Default `F`; còn lại `P` | Default + loading `F`; còn lại `P` | DISC | Category/product/banner/media |
| `PLP-01` | `/home/search`, `/home/category/{slug}` | Keyword/category; filtered/sorted; filter drawer; loading; no-result; request error; pagination; clear-all | Default + filtered + no-result + loading `F` | Default + filter drawer `F` | Default + drawer + filtered + no-result + loading + error `F` | DISC | Search/filter/sort/page contract |
| `PDP-01` | `/product/detail/{slug}` | In-stock; sale; low-stock; out-of-stock; gallery; loading; add success/error; quantity limit; empty specs/reviews; image fallback; not found | Default + out-of-stock + gallery + loading `F` | Default `F`; còn lại `P` | Default + out-of-stock + gallery + loading + add-success `F` | COM | Product/media/stock contract |
| `CRT-01` | `/cart` | Populated; empty; updating; quantity over stock; price changed; unavailable item; remove/undo; request error | Default + empty + price-change `F` | Default `F`; còn lại `P` | Default + empty + stock-error + updating `F` | COM | Server-authoritative price/stock/cart |
| `CHK-01` | `/checkout` | Guest; signed-in prefill; validation error; submitting; submit failure; stock/price changed; session expired/empty cart | Default + validation + failure `F` | Default `F`; còn lại `P` | Default + validation + submitting + failure + stock-change `F` | COM | Shipping/COD/order persistence/idempotency |
| `SUC-01` | `/checkout/success` | COD confirmed; refresh-safe; missing/expired context; lookup/session failure | COD `F`; lỗi `P` | COD `F`; lỗi `P` | COD `F`; lỗi `P` | COM | Persisted order + safe success context |
| `AUT-01` | `/auth/login` | Default; password visible; invalid credentials; field errors; submitting; server error; return-to-checkout | Default + invalid `F` | Default `F` | Default + invalid + submitting `F` | ID | Auth/session/return URL |
| `AUT-02` | `/auth/register` | Default; password criteria; validation; email exists; submitting; success/redirect; server error | Default + validation `F` | Default `F` | Default + validation + password criteria `F` | ID | Auth validation/session |
| `SUP-01` | `/support` | Default; section anchor active; contact unavailable/fallback | Default `F` | Default `F` | Default `F` | DS/Content | Shipping/payment/warranty/contact copy |
| `ERR-01` | Invalid route/product | Generic 404; product not found; recovery search | Generic + product `F` | Generic `F` | Generic + product `F` | DS | Router/search recovery |
| `ERR-02` | Error handler | 500; service unavailable; offline recovery | 500 `F` | `P` | 500/offline `F` | DS/FE | Error handler/logging |

### Action endpoints trong prototype/handoff

- `POST /cart/add`
- `POST /cart/update`
- `POST /cart/remove`
- `POST /checkout/submit`
- `POST /auth/login`
- `POST /auth/register`

Coupon, forgot-password, bank/QR payment và order tracking không thuộc P0 nên không xuất hiện như action khả dụng.

## 3. Dependency register

| ID | Dependency | Owner | Điều kiện đóng |
|---|---|---|---|
| `DEP-01` | Search/filter/sort/pagination | BE + Product | Query params, sort enum, result count, empty/error và URL persistence được chốt |
| `DEP-02` | Cart/session/auth | BE | Server xác nhận price/stock; session expiry và max quantity có behavior rõ |
| `DEP-03` | Product/category/brand media | Content + BE | Image ratio, alt source, placeholder và thumbnail cart được chốt |
| `DEP-04` | Shipping | Product + BE | Fixed fee, availability và source-of-truth được dùng ở Cart/Checkout/Order |
| `DEP-05` | COD/payment status | Product + BE | Status enum, failure/retry và success copy được chốt |
| `DEP-06` | Order persistence | BE | Transaction, idempotency và success refresh behavior được chốt |
| `DEP-07` | Auth | BE | Login/register validation, rate-limit và return-to-checkout được chốt |
| `DEP-08` | Campaign/flash content | Content | Start/end time và fallback khi hết hạn được chốt |
| `DEP-09` | Product semantics | BE + Content | Specs, rating, low-stock và empty defaults được chốt |
| `DEP-10` | Analytics | Product + FE | Event names và payload tối thiểu được chốt |

## 4. Responsive behavior

| ID | 1440 | 1024 | 390 |
|---|---|---|---|
| `GLB-01` | Utility + main header + nav; search 520–640px; keyboard mega menu | Compact header; category drawer; icon actions | Header 56px + search row; full-screen menu/search; bottom nav 64px + safe area |
| `HOM-01` | Hero category/main/promo; product grid 5 cột | Category drawer; promo rail; grid 4 cột | Hero một cột; category/promo rail; grid 2 cột |
| `PLP-01` | Sidebar khoảng 280px sticky + grid 4 cột | Filter drawer + grid 4 cột | Sticky filter/sort; drawer/bottom sheet; grid 2 cột |
| `PDP-01` | Gallery + purchase panel 6/6; panel sticky | Hai cột trên 8-column grid | Gallery → info → offer; tabs thành accordion; sticky buy bar |
| `CRT-01` | Cart 8 cột + summary 4 cột sticky | Hai cột nếu đủ chỗ, nếu không stack | Cart item stack; sticky checkout CTA |
| `CHK-01` | Simplified header; form 7 + summary 5 sticky | Form 5 + summary 3 hoặc stack | Một cột; total luôn thấy; CTA không bị keyboard/safe-area che |
| `SUC-01` | Card max 720px | Card centered | Full-width với padding 16px; CTA có thể xếp dọc |
| `AUT-*` | Form 440–480px | Centered | Full-width, padding 16px; keyboard không che submit |
| `SUP/ERR` | Centered hoặc container 1280px | Container fluid | Copy ngắn; CTA full-width khi cần |

Global responsive acceptance:

- Không horizontal scroll ngoài rail chủ đích.
- Sticky header/CTA không che anchor, validation error hoặc footer.
- Touch target mobile tối thiểu 44×44px.
- Không có thao tác bắt buộc chỉ xuất hiện khi hover.

## 5. Accessibility acceptance theo route

| ID | Acceptance |
|---|---|
| `GLB-01` | Skip link/landmark; accessible search; Escape, focus trap/return; active nav không chỉ bằng màu; cart count có accessible name |
| `HOM-01` | Một H1; carousel có pause/manual control; timer không announce mỗi giây; ảnh có alt; product card không nested interactive conflict |
| `PLP-01` | Filter có label/fieldset; sort có label; result count `aria-live="polite"`; Apply đưa focus về results heading; pagination có current-page semantics |
| `PDP-01` | Thumbnail là button; gallery trap/return focus; rating có text; stock không chỉ bằng màu; quantity có label; tabs/accordion đúng semantics; add-to-cart được announce |
| `CRT-01` | Tên/ảnh liên kết PDP; stepper có name; tổng tiền được announce; lỗi stock gắn đúng item; remove có undo |
| `CHK-01` | Label thật; field error dùng `aria-describedby`; có error summary; autocomplete/inputmode; giữ dữ liệu; loading ngăn double-submit; DOM order đúng |
| `SUC-01` | Focus vào H1; status bằng text; order code selectable/copyable; không chỉ dùng icon check |
| `AUT-*` | Password manager/autofill; show-password có state; error summary; criteria không chỉ đỏ/xanh |
| `SUP/ERR` | Heading/status rõ; Home/Search/Retry khả dụng; không redirect loop |

Global gate:

- Chữ thường ≥4.5:1; text lớn/UI boundary ≥3:1.
- Focus indicator 2px, offset 2px.
- Zoom 200% không mất chức năng.
- Reduced motion dừng chuyển động tự chạy không thiết yếu.
- Icon-only control có accessible name.

## 6. Matrix exit checklist

- [x] 100% route P0 có default frame được yêu cầu ở 1440/1024/390.
- [x] Critical states được phân loại full frame hoặc component/pattern state.
- [x] Responsive behavior được khóa.
- [x] Accessibility behavior được khóa.
- [x] Dependency có owner theo vai trò và điều kiện đóng.
- [ ] Phase 1 tạo đủ frame/state theo matrix.
- [ ] FE/BE xác nhận feasibility sau vertical slice.
- [ ] QA xác nhận keyboard/focus/contrast sau implementation.

Quy mô dự kiến: khoảng 33 default frames và 22–28 critical state frames, tổng khoảng 55–61 screen frames production-ready.

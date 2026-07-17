# TechPilot P0 — Business Rules and Data Contracts

| Thuộc tính | Giá trị |
|---|---|
| Trạng thái | **LOCKED** |
| Phiên bản | `v1.0` |
| Nguồn đối chiếu | `database/schema.sql`, Product/Order models, Home/Cart/Checkout/Product controllers |

## 1. Contract principles

1. Server là nguồn đúng duy nhất cho price, effective price, stock, shipping fee và order total.
2. Session/client không được quyết định giá hoặc khả năng mua hàng.
3. Tiền được biểu diễn bằng số nguyên VND trong application contract; database tiếp tục dùng `DECIMAL(12,0)`.
4. Order item lưu snapshot tên, giá, số lượng và line total để lịch sử không đổi khi product thay đổi.
5. Public identifier dùng `slug` hoặc `order_code`; database relation dùng integer `id`.
6. Field nullable phải có default UI/state rõ ràng; không để missing field làm vỡ page.
7. Mọi write action phải có CSRF protection; checkout submit phải có idempotency protection.
8. Transport timestamp dùng ISO 8601; giao diện hiển thị theo múi giờ Việt Nam.

## 2. Business rules khóa cho P0

### BR-PRODUCT — Giá và tồn kho

- `effective_price` là `flash_sales.discount_price` khi flash sale đang active; nếu không là `products.price`.
- `compare_at_price` chỉ hiển thị khi `old_price > effective_price`.
- `discount_percent` dùng giá trị tính lại từ `compare_at_price` và `effective_price`; không tin cột seed nếu lệch.
- Trong flash sale, `available_stock = min(products.stock, flash_sales.stock - flash_sales.sold)`.
- Ngoài flash sale, `available_stock = products.stock`.
- Stock status:
  - `out_of_stock`: `available_stock = 0`.
  - `low_stock`: `1..5`.
  - `in_stock`: `>5`.
- Số lượng mỗi SKU trong cart: `1..min(available_stock, MAX_QTY_PER_SKU)`; `MAX_QTY_PER_SKU` mặc định là `10` và phải nằm trong config.
- Nếu ảnh chính thiếu/hỏng, dùng product placeholder thống nhất và alt text là tên sản phẩm.
- Rating chỉ hiển thị khi có `review_count > 0`; nếu chưa có review, hiển thị “Chưa có đánh giá”.

### BR-CART — Giỏ hàng

- Guest cart tiếp tục dùng session trong P0.
- Session chỉ giữ `product_id`, `quantity` và có thể giữ `price_snapshot` để phát hiện đổi giá; không dùng snapshot để tính tiền.
- Mỗi lần render cart, update quantity và checkout submit phải đọc lại product/effective price/stock từ database.
- Item hết hàng hoặc không còn tồn tại vẫn hiển thị với trạng thái unavailable và phải được xóa trước khi checkout.
- Khi giá thay đổi, dùng giá hiện tại, cập nhật tổng và thông báo rõ cho người dùng.
- Xóa item có undo trong UI nếu khả thi; backend action vẫn là idempotent.
- Cart rỗng có shipping `0`; cart có hàng dùng cùng shipping rule với checkout.

### BR-SHIPPING — Giao hàng

- P0 chỉ hỗ trợ địa chỉ tại Việt Nam.
- Phí giao hàng cố định là **30.000₫** cho mọi order có hàng.
- Không có free-shipping threshold hoặc shipping carrier integration trong P0.
- Một service/helper duy nhất phải tính shipping cho Cart, Checkout và Order.
- Nếu không thể xác định/ghi nhận địa chỉ hợp lệ, không cho submit order.
- Thay đổi phí hoặc thêm phương thức giao hàng là change request P1 trừ khi là yêu cầu nghiệp vụ bắt buộc trước release.

### BR-CHECKOUT — Checkout

- Guest checkout được phép; login không bắt buộc.
- Nếu đã đăng nhập, prefill dữ liệu có sẵn nhưng người dùng được phép sửa cho order hiện tại.
- Field bắt buộc:
  - `customer_name`: 2–150 ký tự.
  - `phone`: sau normalize còn 10 chữ số và bắt đầu bằng `0`.
  - `address_line`: 5–255 ký tự.
  - `ward`, `district`, `province`: không rỗng.
  - `terms_accepted`: phải là `true`.
- `email` là tùy chọn nhưng nếu có phải hợp lệ và tối đa 150 ký tự.
- `note` tùy chọn, tối đa 1000 ký tự.
- P0 chỉ cho phép `payment_method = COD`.
- Validation error phải trả theo field, có error summary và giữ toàn bộ old input.
- Submit button chuyển loading/disabled ngay khi gửi; idempotency key ngăn double-submit/reload tạo đơn trùng.
- Trước khi tạo order, server re-fetch tất cả item, kiểm tra stock/price và tính lại total.
- Nếu stock/price thay đổi, không tạo order; trả người dùng về trạng thái review có thông báo và đường quay lại cart.
- Tạo order, order items và trừ stock phải nằm trong một database transaction.
- Database unavailable là order failure; không được trả success giả bằng fallback.

### BR-ORDER — Đơn hàng

- `order_code` unique theo format hiện có `TP-YYYYMMDDHHMMSS-XXXXXX` hoặc format tương đương có collision protection.
- Status P0:
  - `pending`: vừa tạo, chờ xác nhận.
  - `confirmed`: đã xác nhận.
  - `processing`: đang chuẩn bị.
  - `shipping`: đang giao.
  - `completed`: hoàn tất.
  - `cancelled`: đã hủy.
- `payment_status` cho COD bắt đầu bằng `unpaid`; chuyển `paid` khi nghiệp vụ xác nhận thu tiền. Schema hiện chưa có field này và cần migration trước production.
- Success page dùng Post/Redirect/Get. Refresh không được tạo đơn mới.
- Direct access khi không có session/order context phải chuyển về cart hoặc trang tra cứu an toàn; không lộ order theo code có thể đoán.

### BR-SEARCH — Search, filter, sort và pagination

Canonical query params:

| Param | Kiểu | Quy tắc |
|---|---|---|
| `q` | string | Trim, tối đa 100 ký tự |
| `cat` | slug | Một category tại một thời điểm |
| `brand` | CSV slug | Nhiều brand, ví dụ `asus,dell` |
| `price_min` | int VND | `>=0` |
| `price_max` | int VND | `>= price_min` |
| `sort` | enum | `relevance`, `newest`, `price_asc`, `price_desc`, `rating` |
| `page` | int | Bắt đầu từ `1` |

- Page size mặc định khóa là `20` sản phẩm; backend có thể cho phép tối đa `48` nhưng UI P0 không cần selector page size.
- `relevance` với query rỗng fallback về `newest`.
- Filter/sort/page phải tồn tại trong URL và hoạt động với refresh, Back và Forward.
- Result trả `total`, `page`, `page_size`, `total_pages` và filter đã normalize.
- Query không hợp lệ được normalize hoặc trả field error; không tạo SQL fragment trực tiếp từ input.

### BR-AUTH — Login/Register tối thiểu

- Login/register tiếp tục dùng session và prepared statements.
- Giữ `return_to` an toàn để quay lại checkout; chỉ cho phép internal relative path.
- Login error không tiết lộ email có tồn tại hay không.
- Form hỗ trợ password manager/autocomplete và chống submit lặp.
- Forgot/reset password được ẩn hoặc đưa P1; không dùng liên kết `#`.

## 3. Product contracts

### ProductSummary

```json
{
  "id": 1,
  "slug": "asus-rog-zephyrus-g16",
  "sku": "TP-LAP-0001",
  "name": "ASUS ROG Zephyrus G16",
  "short_desc": "Laptop gaming mỏng nhẹ",
  "image": "/assets/images/rog-zephyrus.jpg",
  "brand": { "id": 1, "name": "ASUS", "slug": "asus" },
  "category": { "id": 1, "name": "Laptop Gaming", "slug": "laptop-gaming" },
  "price": 39990000,
  "effective_price": 37990000,
  "compare_at_price": 42990000,
  "discount_percent": 12,
  "available_stock": 6,
  "stock_status": "in_stock",
  "rating": 4.8,
  "review_count": 120,
  "specs_preview": ["Core Ultra 9", "RTX 4070", "32GB RAM", "1TB SSD"],
  "flags": ["best_seller"]
}
```

Field mới cần bổ sung hoặc derive:

- `sku`: schema hiện chưa có; thêm nullable unique column trước production hoặc dùng code derive ổn định.
- `effective_price`, `compare_at_price`, `available_stock`, `stock_status`: derive ở service/view-model.
- `specs_preview`: derive có kiểm soát từ `specs` JSON theo từng category.

### ProductDetail

`ProductDetail` mở rộng `ProductSummary` với:

```json
{
  "description": "...",
  "specs": { "CPU": "...", "GPU": "..." },
  "images": [
    { "url": "/assets/images/product-1.webp", "alt": "Mặt trước sản phẩm" }
  ],
  "warranty_months": 24,
  "reviews": [],
  "related_products": []
}
```

`warranty_months` chưa có trong schema; cần thêm field hoặc một nguồn content chính thức. Không hard-code cùng một thời hạn cho mọi product.

## 4. SearchResult contract

```json
{
  "items": [],
  "total": 37,
  "page": 1,
  "page_size": 20,
  "total_pages": 2,
  "query": {
    "q": "laptop gaming",
    "cat": "laptop-gaming",
    "brand": ["asus"],
    "price_min": 20000000,
    "price_max": 50000000,
    "sort": "relevance"
  },
  "facets": {
    "brands": [{ "slug": "asus", "label": "ASUS", "count": 8 }],
    "price": { "min": 0, "max": 100000000 }
  }
}
```

P0 bắt buộc brand và price facet. Category-specific spec filters có thể bổ sung P1 sau khi taxonomy/spec data được chuẩn hóa.

## 5. Cart contracts

### SessionCartItem

```json
{
  "product_id": 1,
  "quantity": 2,
  "price_snapshot": 37990000
}
```

### CartView

```json
{
  "items": [
    {
      "product": "ProductSummary",
      "quantity": 2,
      "max_quantity": 6,
      "unit_price": 37990000,
      "line_total": 75980000,
      "price_changed": false,
      "purchasable": true,
      "message": null
    }
  ],
  "summary": {
    "subtotal": 75980000,
    "discount": 0,
    "shipping_fee": 30000,
    "total": 76010000,
    "currency": "VND"
  },
  "can_checkout": true
}
```

Coupon/discount engine không thuộc P0 nên `discount` luôn là `0`.

## 6. Checkout and error contracts

### ShippingQuote

P0 chỉ có một quote cố định nhưng vẫn dùng chung một contract để Cart, Checkout và Order không tự tính riêng:

```json
{
  "method_code": "STANDARD",
  "label": "Giao hàng tiêu chuẩn",
  "available": true,
  "fee": 30000,
  "currency": "VND",
  "eta_min_days": null,
  "eta_max_days": null
}
```

Không hiển thị ETA nếu chưa có dữ liệu nghiệp vụ đáng tin cậy.

### CheckoutInput

```json
{
  "customer_name": "Nguyễn Văn A",
  "phone": "0901234567",
  "email": "customer@example.com",
  "address_line": "123 Nguyễn Huệ",
  "ward": "Phường Bến Nghé",
  "district": "Quận 1",
  "province": "TP. Hồ Chí Minh",
  "note": "Gọi trước khi giao",
  "payment_method": "COD",
  "terms_accepted": true,
  "csrf_token": "...",
  "idempotency_key": "..."
}
```

### ValidationError

```json
{
  "ok": false,
  "code": "VALIDATION_ERROR",
  "message": "Vui lòng kiểm tra lại thông tin.",
  "field_errors": {
    "phone": "Số điện thoại không hợp lệ."
  },
  "old_input": {}
}
```

Other error codes:

- `CART_EMPTY`
- `PRODUCT_UNAVAILABLE`
- `STOCK_CHANGED`
- `PRICE_CHANGED`
- `ORDER_CREATE_FAILED`
- `SERVICE_UNAVAILABLE`
- `DUPLICATE_SUBMISSION`

## 7. Order contract

```json
{
  "id": 101,
  "order_code": "TP-20260715123000-A1B2C3",
  "user_id": null,
  "customer": {
    "name": "Nguyễn Văn A",
    "phone": "0901234567",
    "email": "customer@example.com"
  },
  "shipping_address": {
    "address_line": "123 Nguyễn Huệ",
    "ward": "Phường Bến Nghé",
    "district": "Quận 1",
    "province": "TP. Hồ Chí Minh",
    "formatted": "123 Nguyễn Huệ, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh"
  },
  "payment_method": "COD",
  "payment_status": "unpaid",
  "status": "pending",
  "items": [],
  "summary": {
    "subtotal": 75980000,
    "discount": 0,
    "shipping_fee": 30000,
    "total": 76010000,
    "currency": "VND"
  },
  "created_at": "2026-07-15T12:30:00+07:00"
}
```

## 8. Analytics event contract

| Event | Required payload |
|---|---|
| `search_submitted` | `query`, `category`, `result_count` |
| `filter_applied` | `filter_name`, `filter_value`, `result_count` |
| `product_viewed` | `product_id`, `category`, `effective_price` |
| `add_to_cart` | `product_id`, `quantity`, `effective_price` |
| `cart_viewed` | `item_count`, `total` |
| `checkout_started` | `item_count`, `total` |
| `checkout_validation_failed` | `field_names` only; không gửi PII |
| `order_succeeded` | `order_code`, `item_count`, `total`, `payment_method` |
| `order_failed` | `error_code`; không gửi PII |

Không gửi tên, số điện thoại, email hoặc địa chỉ vào analytics.

## 9. Backend gaps và release blockers

| ID | Gap hiện tại | Mức | Điều kiện đóng |
|---|---|---|---|
| `BE-00` | Database/schema/seed production chưa có health gate rõ | Blocker | DB thật hoạt động ổn định; migration và seed QA có thể lặp lại |
| `BE-01` | Cart tin price trong session, thiếu image/stock | Blocker | Session/refetch theo BR-CART; CartView dùng dữ liệu server |
| `BE-02` | Cart shipping `0`, checkout `30.000`; marketing còn có thể ghi “miễn phí” | Blocker | Một shipping helper/service dùng ở cả ba nơi; mọi claim UI khớp quote |
| `BE-03` | Update cart không kiểm tra stock/max quantity | Blocker | Validate mọi add/update/submit |
| `BE-04` | Checkout thiếu field validation và old input | Blocker | Field errors + error summary + preserved input |
| `BE-05` | Chưa thấy CSRF/idempotency cho purchase actions | Blocker | CSRF token và duplicate-submit protection |
| `BE-06` | Order fallback trả success khi DB unavailable | Blocker | Production trả `SERVICE_UNAVAILABLE`; không clear cart |
| `BE-07` | Order create chưa trừ stock atomically | Blocker | Transaction recheck + insert + decrement + rollback |
| `BE-08` | Search chỉ có `q`/`cat`, limit 24; chưa count/sort/filter/page | Blocker PLP | Implement BR-SEARCH và SearchResult contract |
| `BE-09` | Order schema thiếu email/structured address/payment status | Blocker checkout | Migration hoặc storage strategy được duyệt và test |
| `BE-10` | Product thiếu SKU/warranty source | Design blocker PDP | Add field/source hoặc loại khỏi UI đến khi có dữ liệu thật |
| `BE-11` | Success chủ yếu dựa vào session | Cao | PRG + refresh-safe session/order context; direct URL fallback |
| `BE-12` | Asset thiếu/trùng/placeholder | Cao | Asset manifest và production fallback hoàn tất trước Design Gate |
| `BE-13` | Header/view còn truy vấn database trực tiếp | Cao | Controller/view-model cấp dữ liệu shell và có degraded state khi DB lỗi |
| `BE-14` | Controller đang cast tiền sang `float` | Cao | Chuẩn hóa integer VND/`DECIMAL(12,0)` xuyên suốt và test giá trị lớn |

## 10. Deferred contracts

Các bảng `coupons`, `wishlists` và `carts/cart_items` có trong schema nhưng không bắt buộc đưa vào runtime P0. Bank/QR, coupon application, persistent cart, wishlist và order tracking sẽ có contract riêng ở P1/P2.

## 11. Contract exit checklist

- [x] Giá, stock, shipping và total có một source-of-truth.
- [x] Search query và response contract được khóa.
- [x] Product, Cart, Checkout, Error và Order contract được khóa.
- [x] COD-only và guest checkout được khóa.
- [x] Blocker hiện tại được liệt kê cùng điều kiện đóng.
- [ ] BE xác nhận migration fields trước Phase 5.
- [ ] Product/Content xác nhận SKU, warranty và support copy trước screen freeze.
- [ ] QA tạo fixture cho price/stock change, DB failure và duplicate submit.

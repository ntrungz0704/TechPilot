# TechPilot Ground-Truth Extraction & Diagram Reconciliation Report
**Báo Cáo Kiểm Định & Đối Chiếu Toàn Bộ Codebase Với 3 Sơ Đồ Hiện Có**

---

## 📌 I. KẾT LUẬN KIỂM CHỨNG 7 NGHI VẤN BAN ĐẦU & SỐ LIỆU RATE LIMIT

| # | Nghi vấn | Kết luận từ Code thật | Bằng chứng Code thực tế (File & Line) |
|---|---|---|---|
| 1 | Sơ đồ Customer xếp **"So sánh sản phẩm"** vào nhánh phải cần `<<include>> Đăng nhập`, route/controller thật có yêu cầu login không? | ❌ **SƠ ĐỒ SAI VAI TRÒ**: `CompareController` không có middleware check login, lưu danh sách so sánh qua `$_SESSION['compare']`. Khách vãng lai (Guest) có thể thêm, xóa sản phẩm và chạy AI Compare trực tiếp. | [CompareController.php](file:///d:/TechPilot/app/controllers/CompareController.php#L15-L65) (Hàm `index`), [CompareController.php](file:///d:/TechPilot/app/controllers/CompareController.php#L95-L135) (Hàm `aiCompare`) |
| 2 | **XÁC MINH HẠN MỨC CHATBOT AI (5/20 hay 20/50?)** | ⚡ **SỰ THẬT CHẠY THỰC TẾ**: Code [ChatbotController.php](file:///d:/TechPilot/app/controllers/ChatbotController.php#L276) thực thi 100% qua `$_SESSION['ai_rate_limit']` với hạn mức **Guest 20 lượt/ngày, Customer 50 lượt/ngày**. Bảng `chatbot_rate_limits` trong DB và comment migration (5/20) **KHÔNG ĐƯỢC CODE TRUY VẤN HAY SỬ DỤNG** (chỉ là schema dự phòng/lỗi thời). | [ChatbotController.php](file:///d:/TechPilot/app/controllers/ChatbotController.php#L276-L295) (`$maxPerDay = $isLoggedIn ? 50 : 20;`) |
| 3 | Sơ đồ có use case **"Xem sản phẩm gần đây"** — có tồn tại route/logic này trong code không? | ✅ **TỒN TẠI TRONG CODE**: `ProductController@detail` tự động ghi nhận ID sản phẩm vào `$_SESSION['recently_viewed']` và query danh sách `recentlyViewedProducts` hiển thị cuối trang. Cả Guest lẫn Customer đều tự động có session này mà không bắt buộc login. | [ProductController.php](file:///d:/TechPilot/app/controllers/ProductController.php#L41-L87) |
| 4 | Sơ đồ Customer KHÔNG có: PC Builder, Thu cũ đổi mới, AI Compare, Ajax gợi ý tìm kiếm, Áp dụng mã giảm giá — các route này có tồn tại không? | ⚠️ **SƠ ĐỒ THIẾU 5 TÍNH NĂNG**: Cả 5 tính năng đều **CÓ CODE THẬT 100% VÀ CHẠY HOÀN CHỈNH**, nhưng Sơ đồ Customer Diagram hoàn toàn không vẽ 5 use case này. | [index.php](file:///d:/TechPilot/public/index.php#L232-L260) (`PcBuilderController`, `HomeController@trade_in`, `CompareController@aiCompare`, `HomeController@ajaxSearch`, `CheckoutController@apply_coupon`) |
| 5 | Sơ đồ Admin có **"Quản lý vai trò"** tách riêng — có bảng `roles`/`permissions` và controller CRUD role thật không? | ❌ **SƠ ĐỒ VẼ KHỐNG / SAI MÔ HÌNH**: Không có bảng `roles`/`permissions` hay Role Controller nào. Phân quyền dùng duy nhất cột `role` enum (`'admin'\|'customer'`) trong bảng `users`. Admin thay đổi quyền user trực tiếp qua API `AdminUserController@changeRole`. | [seed.sql](file:///d:/TechPilot/database/seed.sql#L658), [AdminUserController.php](file:///d:/TechPilot/app/controllers/AdminUserController.php#L107-L141) |
| 6 | ERD chỉ vẽ **19 bảng**, KHÔNG có `inventory_logs`, `ai_assistant_logs` — các bảng này CÓ tồn tại thật không? | ⚠️ **SƠ ĐỒ ERD THIẾU BẢNG**: Bảng `inventory_logs` tồn tại trong [seed.sql](file:///d:/TechPilot/database/seed.sql#L718) và migration `2026_07_28_000001`. Bảng `ai_assistant_logs` có code lưu log thật trong `AdminProductController` và `AiProductAssistantService`. Ngoài ra còn có 3 bảng Chatbot AI (`user_behavior_logs`, `user_interest_profiles`, `chatbot_rate_limits`). | [seed.sql](file:///d:/TechPilot/database/seed.sql#L718), [2026_07_28_000001_create_inventory_logs_table.php](file:///d:/TechPilot/database/migrations/2026_07_28_000001_create_inventory_logs_table.php#L12), [2026_07_28_000002_create_chatbot_tables.php](file:///d:/TechPilot/database/migrations/2026_07_28_000002_create_chatbot_tables.php#L15) |
| 7 | Bảng `users` trong ERD chỉ có 1 cột `address`, KHÔNG có `user_addresses` riêng — tính năng "Quản lý Sổ địa chỉ" có chạy thật không? | ⚠️ **SƠ ĐỒ ERD THIẾU BẢNG**: Bảng `user_addresses` **TỒN TẠI THẬT** trong [seed.sql](file:///d:/TechPilot/database/seed.sql#L618). Tính năng Sổ địa chỉ hoạt động 100% qua `ProfileController` các hàm (`addresses`, `add_address`, `edit_address`, `delete_address`, `set_default_address`). | [seed.sql](file:///d:/TechPilot/database/seed.sql#L618-L633), [ProfileController.php](file:///d:/TechPilot/app/controllers/ProfileController.php#L414-L530) |

---

## 🌐 PHẦN A — TRÍCH XUẤT GROUND TRUTH TỪ CODE

### 1. Phân Loại Routes & Authenticated Middleware Thực Tế

Mã nguồn sử dụng Front Controller tại [public/index.php](file:///d:/TechPilot/public/index.php) kết hợp lớp [Router.php](file:///d:/TechPilot/app/core/Router.php). Phân loại chính xác dựa vào Auth Guard trong từng Controller:

#### A. Guest / Public Routes (Không yêu cầu Đăng nhập)
- **Trang chủ & Tìm kiếm**: `GET /`, `GET /home`, `GET /home/search`, `GET /search`, `GET /home/ajaxSearch` (`HomeController`)
- **Danh mục & Chi tiết**: `GET /category/{slug}`, `GET /product/detail/{slug}` (`HomeController`, `ProductController`)
- **Xác thực**: `GET|POST /auth/login`, `GET|POST /auth/register`, `GET|POST /auth/forgot`, `GET|POST /auth/reset`, `GET|POST /auth/logout` (`AuthController`)
- **Giỏ hàng**: `GET /cart`, `POST /cart/add`, `POST /cart/update`, `POST /cart/remove` (`CartController` — dùng Session `$_SESSION['cart']`)
- **Thanh toán & Coupon**: `GET /checkout`, `POST /checkout/submit`, `GET /checkout/success`, `POST /checkout/apply_coupon`, `POST /checkout/remove_coupon` (`CheckoutController` — cho phép cả Guest tạo đơn `user_id = NULL` và Customer)
- **Callback Thanh toán**: `GET /payment/vnpay-return`, `GET /payment/vnpay-ipn`, `GET /payment/vnpay-sandbox-sim` (`PaymentController`)
- **Wishlist tạm**: `GET /wishlist`, `POST /wishlist/add`, `POST /wishlist/remove`, `POST /wishlist/toggle` (`WishlistController` — dùng Session nếu chưa login)
- **So sánh & AI**: `GET /compare`, `POST /compare/add`, `POST /compare/remove`, `POST /compare/aiCompare`, `POST /ai/compare` (`CompareController`)
- **Trợ lý AI & Chatbot**: `GET /chatbot/query`, `POST /chatbot/sync` (`ChatbotController`), `GET /ai-assistant`, `POST /ai/recommend`, `POST /ai/favorite` (`AiAssistantController`), `POST /product/ai-chat` (`ProductController`)
- **Thu cũ đổi mới & PC Builder**: `GET /thu-cu-doi-moi` (`HomeController`), `GET /build-pc`, `GET /pc-builder/products`, `GET /pc-builder/prebuilt`, `POST /pc-builder/analysis`, `POST /pc-builder/add-to-cart` (`PcBuilderController`)
- **Tin tức & Bài viết**: `GET /tin-tuc`, `GET /tin-tuc/{slug}`, `GET /post`, `GET /post/detail/{slug}` (`NewsController`, `PostController`)

#### B. Customer Authenticated Routes (Yêu cầu `requireLogin()`)
- **Hồ sơ cá nhân**: `GET /profile`, `POST /profile` (`ProfileController@index`)
- **Quản lý Đơn hàng**: `GET /profile/orders`, `GET /profile/order_detail/{id}`, `POST /profile/cancel_order`, `POST /profile/repay` (`ProfileController`)
- **Yêu cầu Đổi trả**: `GET /profile/return/{id}`, `POST /profile/submit_return` (`ProfileController`)
- **Sổ địa chỉ**: `GET /profile/addresses`, `POST /profile/add-address`, `POST /profile/edit-address`, `POST /profile/delete-address`, `POST /profile/set-default-address` (`ProfileController`)
- **Đổi mật khẩu**: `POST /profile/change_password` (`ProfileController`)
- **Thông báo & Wishlist cá nhân**: `GET /profile/notifications`, `GET /profile/wishlist`, `GET /api/notifications/unread` (`ProfileController`)
- **Đánh giá sản phẩm**: `POST /product/review` (`ProductController@review` — kiểm tra đã mua hàng `hasPurchasedProduct`)

#### C. Admin Authenticated Routes (Yêu cầu `requireAdmin()`)
- **Dashboard & Notification**: `GET /admin`, `GET /admin/dashboard`, `GET /api/admin/notifications`, `POST /api/admin/notifications/mark_read` (`AdminController`)
- **Danh mục & Thương hiệu**: `GET|POST /admin/categories/*` (`AdminCategoryController`), `GET|POST /admin/brands/*` (`AdminBrandController`)
- **Sản phẩm & Tồn kho**: `GET|POST /admin/products/*`, `POST /admin/products/adjust-stock`, `GET /admin/inventory/logs` (`AdminProductController`, `AdminInventoryController`)
- **AI Product Assistant**: `POST /admin/products/ai-assistant`, `POST /admin/products/ai-assistant/rewrite`, `GET /admin/products/ai-assistant/history`, `POST /admin/products/ai-assistant/history/action`, `POST /admin/products/ai-assistant/feedback`
- **Đơn hàng & Trạng thái**: `GET /admin/orders`, `GET /admin/orders/detail/{id}`, `POST /admin/orders/update_status/{id}` (`AdminOrderController`)
- **Khách hàng & Tài khoản**: `GET /admin/users`, `GET /admin/customers`, `POST /admin/users/toggle_status/{id}`, `POST /admin/users/change_role/{id}` (`AdminUserController`)
- **Khuyến mãi & Banner & Bài viết & Đánh giá**: `GET|POST /admin/flash-sales/*`, `GET|POST /admin/coupons/*`, `GET|POST /admin/banners/*`, `GET|POST /admin/posts/*`, `GET|POST /admin/reviews/*`

---

### 2. Danh Sách 24 Bảng Database Thực Tế (Ground Truth Schema)

Mã nguồn bao gồm **24 bảng database thực tế** (18 bảng nghiệp vụ + 1 bảng migrations trong `seed.sql` + 5 bảng mở rộng từ migrations và mã nguồn AI).

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                                 TECHPILOT DATABASE (24 TABLES)                         │
├─────────────────────────┬──────────────────────────┬───────────────────────────────────┤
│ 1. users                │ 9. orders                │ 17. flash_sale_items              │
│ 2. user_addresses       │ 10. order_items          │ 18. banners                       │
│ 3. categories           │ 11. coupons              │ 19. posts                         │
│ 4. brands               │ 12. reviews              │ 20. inventory_logs (Thiếu ERD)    │
│ 5. products             │ 13. wishlists            │ 21. ai_assistant_logs (Thiếu ERD) │
│ 6. product_images       │ 14. notifications        │ 22. user_behavior_logs (Thiếu ERD)│
│ 7. carts                │ 15. migrations           │ 23. user_interest_profiles        │
│ 8. cart_items           │ 16. flash_sales          │ 24. chatbot_rate_limits           │
└─────────────────────────┴──────────────────────────┴───────────────────────────────────┘
```

#### Kết quả xác minh tính năng Rate Limit:
- **Thực tế đang chạy (Active Code)**: `ChatbotController.php` (L276-295) quản lý rate limit trực tiếp trên PHP Session (`$_SESSION['ai_rate_limit']`), áp đặt hạn mức **Guest: 20 lượt/ngày**, **Customer (đã đăng nhập): 50 lượt/ngày**.
- **Trạng thái bảng DB `chatbot_rate_limits`**: Bảng tồn tại trong DB do migration `2026_07_28_000002` tạo ra, nhưng trong toàn bộ thư mục `app/` **KHÔNG có bất kỳ đoạn code PHP nào truy vấn hay đọc/ghi vào bảng này**. Comment trong migration file (`guest: 5, thành viên: 20`) chỉ là thiết kế ban đầu không được đưa vào thực thi.

---

## 📐 PHẦN CHUẨN MERMAID ERD (24 BẢNG ĐẦY ĐỦ)

```mermaid
erDiagram
    USERS ||--o{ USER_ADDRESSES : "has"
    USERS ||--o{ ORDERS : "places"
    USERS ||--o{ CARTS : "owns"
    USERS ||--o{ REVIEWS : "writes"
    USERS ||--o{ WISHLISTS : "saves"
    USERS ||--o{ NOTIFICATIONS : "receives"
    USERS ||--o{ AI_ASSISTANT_LOGS : "created_by"
    USERS ||--o{ USER_BEHAVIOR_LOGS : "tracked_by"
    USERS ||--o{ USER_INTEREST_PROFILES : "profiled_by"

    CATEGORIES ||--o{ PRODUCTS : "contains"
    BRANDS ||--o{ PRODUCTS : "manufactures"

    PRODUCTS ||--o{ PRODUCT_IMAGES : "has"
    PRODUCTS ||--o{ ORDER_ITEMS : "included_in"
    PRODUCTS ||--o{ CART_ITEMS : "added_to"
    PRODUCTS ||--o{ REVIEWS : "receives"
    PRODUCTS ||--o{ WISHLISTS : "saved_in"
    PRODUCTS ||--o{ INVENTORY_LOGS : "logs"
    PRODUCTS ||--o{ FLASH_SALE_ITEMS : "promoted_in"

    CARTS ||--o{ CART_ITEMS : "contains"
    ORDERS ||--o{ ORDER_ITEMS : "contains"
    ORDERS ||--o{ INVENTORY_LOGS : "triggers"
    COUPONS ||--o{ ORDERS : "applied_to"
    FLASH_SALES ||--o{ FLASH_SALE_ITEMS : "includes"

    USERS {
        int id PK
        string full_name
        string email UK
        string phone
        string password
        enum role "admin, customer"
        string address
        enum status "active, inactive"
    }
    USER_ADDRESSES {
        int id PK
        int user_id FK
        string recipient_name
        string phone
        string address_line
        string ward
        string district
        string province
        bool is_default
    }
    PRODUCTS {
        int id PK
        int category_id FK
        int brand_id FK
        string name
        string slug UK
        decimal price
        decimal sale_price
        int stock
        json specs
        enum status "active, inactive"
    }
    ORDERS {
        int id PK
        string order_code UK
        int user_id FK "nullable — guest checkout"
        int coupon_id FK "nullable"
        string customer_name
        string phone
        string address
        string payment_method
        string payment_status
        decimal subtotal
        decimal discount_amount
        decimal shipping_fee
        decimal total_amount
        enum status "pending, confirmed, processing, shipping, completed, cancelled"
        enum inventory_status "reserved, deducted, released"
    }
    ORDER_ITEMS {
        int id PK
        int order_id FK
        int product_id FK
        string product_name
        decimal price
        int quantity
        decimal total_price
    }
    INVENTORY_LOGS {
        int id PK
        int product_id FK
        int order_id FK "nullable"
        int created_by FK "admin thao tác"
        enum type "manual_import, manual_export, order_reserve, order_release, stock_correction"
        int quantity_delta
        int old_stock
        int new_stock
        string idempotency_key UK
    }
    AI_ASSISTANT_LOGS {
        int id PK
        string prompt
        string model_key
        string provider
        int confidence_score
        json request_payload
        json response_data
        enum status "pending, applied, rejected"
        int created_by FK
    }
    USER_BEHAVIOR_LOGS {
        int id PK
        int user_id FK
        string action_type
        string target_type
        int target_id
        json metadata
    }
    USER_INTEREST_PROFILES {
        int id PK
        int user_id FK UK
        json brand_scores
        json category_scores
        decimal budget_min
        decimal budget_max
        json last_keywords
    }
    CHATBOT_RATE_LIMITS {
        int id PK
        string identifier UK
        date rate_date UK
        int query_count
    }
    FLASH_SALES {
        int id PK
        string title
        enum discount_type
        decimal discount_value
        datetime start_date
        datetime end_date
    }
    FLASH_SALE_ITEMS {
        int id PK
        int flash_sale_id FK
        int product_id FK
        decimal discount_price
        int allocation_quantity
        int sold_quantity
    }
    COUPONS {
        int id PK
        string code UK
        string discount_type
        decimal discount
        int quantity
        datetime start_date
        datetime end_date
    }
    REVIEWS {
        int id PK
        int user_id FK
        int product_id FK
        int rating
        string comment
        enum status
    }
    WISHLISTS {
        int id PK
        int user_id FK
        int product_id FK
    }
    NOTIFICATIONS {
        int id PK
        int user_id FK
        string title
        string content
        bool is_read
    }
    CARTS {
        int id PK
        int user_id FK
    }
    CART_ITEMS {
        int id PK
        int cart_id FK
        int product_id FK
        int quantity
    }
    CATEGORIES {
        int id PK
        string name
        string slug UK
    }
    BRANDS {
        int id PK
        string name
        string slug UK
        string logo
    }
    PRODUCT_IMAGES {
        int id PK
        int product_id FK
        string image
    }
    BANNERS {
        int id PK
        string title
        string image
        string url
        int position
        enum status
    }
    POSTS {
        int id PK
        string title
        string slug UK
        string content
        int author
        enum status
    }
    MIGRATIONS {
        int id PK
        string migration
        int batch
    }
```

---
*Báo cáo kiểm định lại 100% khớp với cấu trúc Database & Source Code thật của TechPilot.*

# Route Matrix — Phase 1 (TechPilot)

| STT | HTTP Method | URL | Actor / Quyền | Controller / Action | View File | Bảng sử dụng | CSRF | Trạng thái |
|---|---|---|---|---|---|---|---|---|
| **I** | **Public & Customer Storefront** | | | | | | | |
| 1 | GET | `/` | Guest/Customer | `HomeController@index` | `home/index` | `banners`, `categories`, `products`, `flash_sales`, `posts`, `reviews` | Không | Hoàn thành |
| 2 | GET | `/home/search` | Guest/Customer | `HomeController@search` | `home/search` | `products`, `categories`, `brands` | Không | Hoàn thành |
| 3 | GET | `/product/detail/{slug}` | Guest/Customer | `ProductController@detail` | `product/detail` | `products`, `product_images`, `reviews` | Không | Hoàn thành |
| 4 | GET | `/auth/login` | Guest | `AuthController@login` | `auth/login` | Không | Không | Hoàn thành |
| 5 | POST | `/auth/login` | Guest | `AuthController@login` | `auth/login` | `users` | Có | Hoàn thành |
| 6 | GET | `/auth/register` | Guest | `AuthController@register` | `auth/register` | Không | Không | Hoàn thành |
| 7 | POST | `/auth/register` | Guest | `AuthController@register` | `auth/register` | `users` | Có | Hoàn thành |
| 8 | POST | `/auth/logout` | Customer/Admin | `AuthController@logout` | Không | Không | Có | Hoàn thành |
| 9 | GET | `/cart` | Guest/Customer | `CartController@index` | `cart` | `products` | Không | Hoàn thành |
| 10 | POST | `/cart/add` | Guest/Customer | `CartController@add` | Không | `products` | Có | Hoàn thành |
| 11 | POST | `/cart/update` | Guest/Customer | `CartController@update` | Không | `products` | Có | Hoàn thành |
| 12 | POST | `/cart/remove` | Guest/Customer | `CartController@remove` | Không | Không | Có | Hoàn thành |
| 13 | GET | `/checkout` | Customer | `CheckoutController@index` | `checkout` | `products` | Không | Hoàn thành |
| 14 | POST | `/checkout/submit` | Customer | `CheckoutController@submit` | Không | `orders`, `order_items`, `products`, `coupons` | Có | Hoàn thành |
| 15 | GET | `/checkout/success` | Customer | `CheckoutController@success` | `checkout-success` | Không | Không | Hoàn thành |
| 16 | GET | `/profile` | Customer | `ProfileController@index` | `profile/orders` | `orders` | Không | Hoàn thành |
| 17 | GET | `/profile/orders` | Customer | `ProfileController@orders` | `profile/orders` | `orders` | Không | Hoàn thành |
| 18 | GET | `/profile/orders/{id}` | Customer | `ProfileController@orderDetail`| `profile/order_detail`| `orders`, `order_items` | Không | Hoàn thành |
| 19 | GET | `/profile/return` | Customer | `ProfileController@return` | `profile/return` | `orders`, `order_items` | Không | Hoàn thành |
| 20 | POST | `/profile/submit_return`| Customer | `ProfileController@submitReturn`| Không | `return_requests` | Có | Hoàn thành |
| 21 | GET | `/wishlist` | Customer | `WishlistController@index` | `wishlist/index` | `wishlists`, `products` | Không | Hoàn thành |
| 22 | POST | `/wishlist/add` | Customer | `WishlistController@add` | Không | `wishlists` | Có | Hoàn thành |
| 23 | POST | `/wishlist/remove` | Customer | `WishlistController@remove` |  Không | `wishlists` | Có | Hoàn thành |
| 24 | GET | `/compare` | Guest/Customer | `CompareController@index` | `compare/index` | `products` | Không | Hoàn thành |
| 25 | POST | `/compare/add` | Guest/Customer | `CompareController@add` | Không | `products` | Có | Hoàn thành |
| 26 | POST | `/compare/remove` | Guest/Customer | `CompareController@remove` | Không | Không | Có | Hoàn thành |
| **II**| **Admin Panel (Mục tiêu phát triển mới)** | | | | | | | |
| 27 | GET | `/admin` | Admin | `AdminController@dashboard` | `admin/dashboard` | Thống kê từ orders/products/users | Không | Chưa làm |
| 28 | GET | `/admin/categories` | Admin | `AdminCategoryController@index`| `admin/categories/index` | `categories` | Không | Chưa làm |
| 29 | GET | `/admin/categories/create`| Admin | `AdminCategoryController@create`| `admin/categories/create` | Không | Không | Chưa làm |
| 30 | POST | `/admin/categories/store`| Admin | `AdminCategoryController@store` | Không | `categories` | Có | Chưa làm |
| 31 | GET | `/admin/categories/edit`| Admin | `AdminCategoryController@edit` | `admin/categories/edit` | `categories` | Không | Chưa làm |
| 32 | POST | `/admin/categories/update`| Admin | `AdminCategoryController@update`| Không | `categories` | Có | Chưa làm |
| 33 | POST | `/admin/categories/delete`| Admin | `AdminCategoryController@delete`| Không | `categories` | Có | Chưa làm |
| 34 | GET | `/admin/brands` | Admin | `AdminBrandController@index` | `admin/brands/index` | `brands` | Không | Chưa làm |
| 35 | GET | `/admin/brands/create` | Admin | `AdminBrandController@create` | `admin/brands/create` | Không | Không | Chưa làm |
| 36 | POST | `/admin/brands/store` | Admin | `AdminBrandController@store` | Không | `brands` | Có | Chưa làm |
| 37 | GET | `/admin/brands/edit` | Admin | `AdminBrandController@edit` | `admin/brands/edit` | `brands` | Không | Chưa làm |
| 38 | POST | `/admin/brands/update` | Admin | `AdminBrandController@update` | Không | `brands` | Có | Chưa làm |
| 39 | POST | `/admin/brands/delete` | Admin | `AdminBrandController@delete` | Không | `brands` | Có | Chưa làm |
| 40 | GET | `/admin/products` | Admin | `AdminProductController@index` | `admin/products/index` | `products`, `categories`, `brands` | Không | Chưa làm |
| 41 | GET | `/admin/products/create`| Admin | `AdminProductController@create` | `admin/products/create` | `categories`, `brands` | Không | Chưa làm |
| 42 | POST | `/admin/products/store` | Admin | `AdminProductController@store` |  Không | `products`, `product_images` | Có | Chưa làm |
| 43 | GET | `/admin/products/edit` | Admin | `AdminProductController@edit` | `admin/products/edit` | `products`, `categories`, `brands` | Không | Chưa làm |
| 44 | POST | `/admin/products/update`| Admin | `AdminProductController@update`| Không | `products`, `product_images` | Có | Chưa làm |
| 45 | POST | `/admin/products/delete`| Admin | `AdminProductController@delete`| Không | `products` | Có | Chưa làm |
| 46 | GET | `/admin/orders` | Admin | `AdminOrderController@index` | `admin/orders/index` | `orders` | Không | Chưa làm |
| 47 | GET | `/admin/orders/detail` | Admin | `AdminOrderController@detail` | `admin/orders/detail` | `orders`, `order_items` | Không | Chưa làm |
| 48 | POST | `/admin/orders/update_status`| Admin | `AdminOrderController@updateStatus`| Không | `orders`, `products` | Có | Chưa làm |
| 49 | GET | `/admin/users` | Admin | `AdminUserController@index` | `admin/users/index` | `users` | Không | Chưa làm |
| 50 | POST | `/admin/users/toggle_status`| Admin | `AdminUserController@toggleStatus`| Không | `users` | Có | Chưa làm |
| 51 | POST | `/admin/users/change_role`| Admin | `AdminUserController@changeRole` | Không | `users` | Có | Chưa làm |
| 52 | GET | `/admin/reviews` | Admin | `AdminReviewController@index` | `admin/reviews/index` | `reviews` | Không | Chưa làm |
| 53 | POST | `/admin/reviews/approve`| Admin | `AdminReviewController@approve` | Không | `reviews` | Có | Chưa làm |
| 54 | POST | `/admin/reviews/hide` | Admin | `AdminReviewController@hide` | Không | `reviews` | Có | Chưa làm |
| 55 | GET | `/admin/flash-sales` | Admin | `AdminFlashSaleController@index`| `admin/flash_sales/index` | `flash_sales` | Không | Chưa làm |
| 56 | GET | `/admin/flash-sales/create`| Admin | `AdminFlashSaleController@create`| `admin/flash_sales/create`| `products` | Không | Chưa làm |
| 57 | POST | `/admin/flash-sales/store`| Admin | `AdminFlashSaleController@store` | Không | `flash_sales`, `flash_sale_items` | Có | Chưa làm |
| 58 | GET | `/admin/flash-sales/edit`| Admin | `AdminFlashSaleController@edit` | `admin/flash_sales/edit` | `flash_sales` | Không | Chưa làm |
| 59 | POST | `/admin/flash-sales/update`| Admin | `AdminFlashSaleController@update`| Không | `flash_sales`, `flash_sale_items` | Có | Chưa làm |
| 60 | POST | `/admin/flash-sales/delete`| Admin | `AdminFlashSaleController@delete`| Không | `flash_sales`, `flash_sale_items` | Có | Chưa làm |
| 61 | GET | `/admin/coupons` | Admin | `AdminCouponController@index` | `admin/coupons/index` | `coupons` | Không | Chưa làm |
| 62 | GET | `/admin/coupons/create`| Admin | `AdminCouponController@create` | `admin/coupons/create` | Không | Không | Chưa làm |
| 63 | POST | `/admin/coupons/store` | Admin | `AdminCouponController@store` | Không | `coupons` | Có | Chưa làm |
| 64 | GET | `/admin/coupons/edit` | Admin | `AdminCouponController@edit` | `admin/coupons/edit` | `coupons` | Không | Chưa làm |
| 65 | POST | `/admin/coupons/update` | Admin | `AdminCouponController@update` | Không | `coupons` | Có | Chưa làm |
| 66 | POST | `/admin/coupons/delete` | Admin | `AdminCouponController@delete` | Không | `coupons` | Có | Chưa làm |
| 67 | GET | `/admin/banners` | Admin | `AdminBannerController@index` | `admin/banners/index` | `banners` | Không | Chưa làm |
| 68 | GET | `/admin/banners/create`| Admin | `AdminBannerController@create` | `admin/banners/create` | Không | Không | Chưa làm |
| 69 | POST | `/admin/banners/store` | Admin | `AdminBannerController@store` | Không | `banners` | Có | Chưa làm |
| 70 | GET | `/admin/banners/edit` | Admin | `AdminBannerController@edit` | `admin/banners/edit` | `banners` | Không | Chưa làm |
| 71 | POST | `/admin/banners/update` | Admin | `AdminBannerController@update` | Không | `banners` | Có | Chưa làm |
| 72 | POST | `/admin/banners/delete` | Admin | `AdminBannerController@delete` |  Không | `banners` | Có | Chưa làm |
| 73 | GET | `/admin/posts` | Admin | `AdminPostController@index` | `admin/posts/index` | `posts` | Không | Chưa làm |
| 74 | GET | `/admin/posts/create` | Admin | `AdminPostController@create` | `admin/posts/create` | Không | Không | Chưa làm |
| 75 | POST | `/admin/posts/store` | Admin | `AdminPostController@store` | Không | `posts` | Có | Chưa làm |
| 76 | GET | `/admin/posts/edit` | Admin | `AdminPostController@edit` | `admin/posts/edit` | `posts` | Không | Chưa làm |
| 77 | POST | `/admin/posts/update` | Admin | `AdminPostController@update` | Không | `posts` | Có | Chưa làm |
| 78 | POST | `/admin/posts/delete` | Admin | `AdminPostController@delete` | Không | `posts` | Có | Chưa làm |

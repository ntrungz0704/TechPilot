# Ma trận Route dành cho Khách vãng lai và Khách hàng (Customer Route Matrix)

Dưới đây là ma trận mô tả tất cả các URL, phương thức HTTP, Actor (Vai trò), Controller/Action xử lý, View tương ứng và các bảng cơ sở dữ liệu được tham chiếu.

| Method | URL | Actor | Controller / Action | View file | Bảng sử dụng | CSRF | Trạng thái test |
|---|---|---|---|---|---|---|---|
| **GET** | `/` | Guest/Customer | `HomeController@index` | `home/index` | `products`, `categories`, `brands`, `banners`, `posts`, `reviews` | Không | Chưa test |
| **GET** | `/home/search` | Guest/Customer | `HomeController@search` | `home/search` | `products`, `categories`, `brands` | Không | Chưa test |
| **GET** | `/product/detail/{slug}` | Guest/Customer | `ProductController@detail` | `product/detail` | `products`, `product_images`, `reviews`, `categories`, `brands` | Không | Chưa test |
| **GET** | `/cart` | Guest/Customer | `CartController@index` | `cart/index` | `carts`, `cart_items`, `products` | Không | Chưa test |
| **POST** | `/cart/add` | Guest/Customer | `CartController@add` | (Redirect) | `carts`, `cart_items`, `products` | Có | Chưa test |
| **POST** | `/cart/update` | Guest/Customer | `CartController@update` | (Redirect) | `carts`, `cart_items`, `products` | Có | Chưa test |
| **POST** | `/cart/remove` | Guest/Customer | `CartController@remove` | (Redirect) | `carts`, `cart_items` | Có | Chưa test |
| **GET** | `/checkout` | Customer | `CheckoutController@index` | `checkout` | `carts`, `cart_items`, `products`, `coupons` | Không | Chưa test |
| **POST** | `/checkout/apply_coupon`| Customer | `CheckoutController@apply_coupon`| (AJAX Response) | `coupons` | Có | Chưa test |
| **POST** | `/checkout/place_order` | Customer | `CheckoutController@place_order` | (Redirect) | `orders`, `order_items`, `carts`, `cart_items`, `products`, `coupons` | Có | Chưa test |
| **GET** | `/auth/login` | Guest | `AuthController@loginForm` | `auth/login` | `users` | Không | Chưa test |
| **POST** | `/auth/login` | Guest | `AuthController@login` | (Redirect) | `users` | Có | Chưa test |
| **GET** | `/auth/register` | Guest | `AuthController@registerForm` | `auth/register` | `users` | Không | Chưa test |
| **POST** | `/auth/register` | Guest | `AuthController@register` | (Redirect) | `users` | Có | Chưa test |
| **POST** | `/auth/logout` | Customer | `AuthController@logout` | (Redirect) | Không | Có | Chưa test |
| **GET** | `/profile` | Customer | `ProfileController@index` | `profile/index` | `users` | Không | Chưa test |
| **POST** | `/profile/update` | Customer | `ProfileController@update` | (Redirect) | `users` | Có | Chưa test |
| **POST** | `/profile/change_password`| Customer | `ProfileController@changePassword`| (Redirect) | `users` | Có | Chưa test |
| **GET** | `/wishlist` | Customer | `WishlistController@index` | `wishlist/index` | `wishlists`, `products` | Không | Chưa test |
| **POST** | `/wishlist/add` | Customer | `WishlistController@add` | (Redirect) | `wishlists`, `products` | Có | Chưa test |
| **POST** | `/wishlist/remove` | Customer | `WishlistController@remove` | (Redirect) | `wishlists` | Có | Chưa test |
| **GET** | `/orders` | Customer | `OrderController@index` | `order/index` | `orders` | Không | Chưa test |
| **GET** | `/orders/detail/{id}` | Customer | `OrderController@detail` | `order/detail` | `orders`, `order_items`, `products` | Không | Chưa test |
| **POST** | `/orders/cancel/{id}` | Customer | `OrderController@cancel` | (Redirect) | `orders`, `products` | Có | Chưa test |
| **POST** | `/product/review` | Customer | `ProductController@review` | (Redirect) | `reviews`, `orders`, `order_items` | Có | Chưa test |
| **GET** | `/posts` | Guest/Customer | `PostController@index` | `post/index` | `posts` | Không | Chưa test |
| **GET** | `/posts/detail/{slug}` | Guest/Customer | `PostController@detail` | `post/detail` | `posts` | Không | Chưa test |

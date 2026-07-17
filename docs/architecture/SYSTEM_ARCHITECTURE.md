---
document_status: DRAFT
planning_authority: "ChatGPT Work"
decision_owner: "Human Project Owner"
approved_by: HUMAN_PLAN_APPROVAL_REQUIRED
approval_ref: UNRESOLVED
baseline_commit: "1ae679461e1f709488155ebf275ef070b54d723a"
audit_date: "2026-07-16"
---

# TechPilot System Architecture

## 1. Mục đích và thẩm quyền tài liệu

Tài liệu này mô tả kiến trúc đã quan sát được của TechPilot, đề xuất các ranh giới cần bảo toàn khi thay đổi hệ thống và chỉ ra những quyết định phải qua checkpoint do con người phê duyệt. Trạng thái hiện tại là **DRAFT**.

Tài liệu này:

- là tài liệu mô tả và quản trị kiến trúc, không phải một ADR;
- chỉ các mục HEAD FACT là bằng chứng mô tả; chúng không tự cấp quyền thay đổi;
- các mục GOVERNANCE là đề xuất không ràng buộc cho tới khi Human Project Owner duyệt checkpoint hoặc ADR liên quan và ghi approval canonical;
- không tự phê duyệt bất kỳ thay đổi nào đang nằm trong working tree;
- không bắt buộc bổ sung Service layer, Repository layer, Clean Architecture, DDD hoặc một framework;
- không thay thế lịch sử Git, schema database, mã nguồn hay quyết định của người có thẩm quyền.

## 2. Provenance và cách đọc

### 2.1 Baseline đã kiểm chứng

- Repository baseline: commit HEAD 1ae679461e1f709488155ebf275ef070b54d723a.
- Ngày rà soát: 2026-07-16.
- Phạm vi: ứng dụng trong thư mục techpilot.

### 2.2 Nhãn bằng chứng

Tài liệu sử dụng ba nhãn:

| Nhãn | Ý nghĩa |
| --- | --- |
| HEAD FACT | Sự thật có thể kiểm chứng trong commit baseline nêu trên. |
| WT CANDIDATE | Quan sát từ file modified hoặc untracked trong working tree; chưa phải baseline và chưa được coi là đã phê duyệt. |
| GOVERNANCE | Guardrail đề xuất; chỉ có hiệu lực khi được contract hoặc ADR có Human approval viện dẫn. |

Khi HEAD và working tree khác nhau, HEAD FACT là mô tả của hệ thống đã commit. WT CANDIDATE chỉ giúp người review hiểu hướng thay đổi đang được thử nghiệm. Một candidate chỉ trở thành kiến trúc hiện hành sau khi có phê duyệt cần thiết, được commit và tài liệu này được cập nhật.

## 3. Tóm tắt hệ thống

### 3.1 Kiểu ứng dụng

HEAD FACT: TechPilot là một monolith thương mại điện tử render HTML phía server, xây dựng bằng PHP thuần theo MVC tự viết. Không phát hiện framework PHP, Composer manifest, autoloader chuẩn, container dependency injection hoặc package runtime trong HEAD.

HEAD FACT: repository không pin phiên bản PHP. Union types trong code HEAD đặt mức tương thích cú pháp tối thiểu ở PHP 8.0, nhưng đây chưa phải runtime contract được khai báo.

WT CANDIDATE: untracked local server log ghi nhận một lần chạy bằng PHP 8.3.26. Đây chỉ là bằng chứng của máy phát triển tại thời điểm rà soát, không phải phiên bản production đã được phê duyệt.

Các thành phần chính:

- PHP xử lý HTTP, session, routing, controller, render view và truy cập database;
- MySQL hoặc MariaDB được truy cập bằng PDO;
- PHP templates tạo HTML;
- CSS, JavaScript và ảnh tĩnh nằm dưới public/assets;
- session PHP lưu trạng thái đăng nhập và giỏ hàng runtime.

### 3.2 Sơ đồ runtime hiện hành

HEAD FACT:

    HTTP request
        |
        v
    public/index.php
        |
        v
    app/core/Router.php
        |
        v
    *Controller.php
       |        \
       |         \--> Controller::render() --> layouts + view template
       v
    *Model.php
        |
        v
    config/database.php --> PDO --> MySQL/MariaDB

Helpers là các hàm toàn cục dùng cho URL, format, escaping và session-derived UI state. Controllers và models được nạp thủ công bằng require/require_once; không có namespace trong baseline.

GOVERNANCE: sơ đồ trên mô tả hiện trạng, không phải yêu cầu phải tạo thêm tầng. Một helper điều phối hoặc service cụ thể có thể được đề xuất khi giải quyết một vấn đề rõ ràng, nhưng sự tồn tại của một service không tạo quy định rằng mọi use case phải có Service hoặc Repository.

## 4. Điểm vào và web root

### 4.1 Web root chuẩn

HEAD FACT: public/ là document root được dự án tuyên bố và là public surface chuẩn.

- public/index.php là front controller cho request động.
- public/router.php là router adapter dành cho PHP built-in server; file tĩnh có thật được server phục vụ trực tiếp, request còn lại được chuyển vào public/index.php qua tham số url.
- public/assets chứa tài nguyên trình duyệt được phép truy cập.
- index.php ở project root là entrypoint tương thích cho một số cấu hình XAMPP/Apache và redirect sang /techpilot/public/...; đây không phải web root ưu tiên.

HEAD FACT: README của HEAD nhắc đến public/.htaccess nhưng file đó không tồn tại trong tree của commit baseline.

WT CANDIDATE: public/.htaccess đang tồn tại dưới dạng untracked file và triển khai rewrite vào public/index.php. Candidate này chưa được coi là deployment contract đã commit.

GOVERNANCE:

1. Môi trường deploy phải trỏ document root trực tiếp vào public/.
2. config/, app/, database/, docs/ và file repository không được public server phục vụ như static content.
3. Thay đổi entrypoint, base path, rewrite rule hoặc canonical URL phải có kiểm thử trên môi trường mục tiêu.
4. Xóa hoặc thay đổi root index.php cần checkpoint vì có thể phá URL tương thích đang được dùng cục bộ.
5. public/design-v2 hiện là WT CANDIDATE; trước khi deploy phải có quyết định rõ đây là public artifact hay prototype cần đưa ra khỏi web root.

## 5. Routing và public URL contracts

### 5.1 Cơ chế routing

HEAD FACT: Router phân tách URL theo controller/action/params:

- segment đầu tiên được chuyển thành tên lớp PascalCase với hậu tố Controller;
- segment thứ hai là tên public action, mặc định index;
- các segment còn lại được truyền làm positional parameters;
- URL rỗng ánh xạ tới HomeController::index;
- controller không tồn tại được chuyển tới HomeController::notFound.

Không có route table, HTTP-method map, middleware pipeline hoặc dependency injection ở baseline. Một số action mutation tự kiểm tra POST trong controller; router tự nó không giới hạn HTTP method.

### 5.2 Route inventory của HEAD

Phương thức trong cột “Contract quan sát được” mô tả hành vi code, không phải khuyến nghị bảo mật.

| Path | Contract quan sát được | Handler | Ghi chú |
| --- | --- | --- | --- |
| / | Không bị router giới hạn; dùng như GET | HomeController::index | Trang chủ. |
| /home/search | Không bị router giới hạn; dùng như GET | HomeController::search | Query q và cat. |
| /home/category/{slug} | Không bị router giới hạn; dùng như GET | HomeController::category | Route theo convention, dù không được README HEAD liệt kê đầy đủ. |
| /product/detail/{slug} | Không bị router giới hạn; dùng như GET | ProductController::detail | Chi tiết sản phẩm. |
| /auth/login | GET và POST trong cùng action | AuthController::login | Validation inline. |
| /auth/register | GET và POST trong cùng action | AuthController::register | Validation inline. |
| /auth/logout | Router chấp nhận mọi method | AuthController::logout | HEAD không có POST guard hoặc CSRF. |
| /cart | Không bị router giới hạn; dùng như GET | CartController::index | Giỏ hàng từ session. |
| /cart/add | POST guard trong controller | CartController::add | Mutation session. |
| /cart/update | POST guard trong controller | CartController::update | Mutation session. |
| /cart/remove | POST guard trong controller | CartController::remove | Mutation session. |
| /checkout | Không bị router giới hạn; dùng như GET | CheckoutController::index | Tính summary từ cart session. |
| /checkout/submit | POST guard trong controller | CheckoutController::submit | Tạo order. |
| /checkout/success | Không bị router giới hạn; dùng như GET | CheckoutController::success | Đọc last_order từ session. |

### 5.3 Rủi ro routing đã biết

HEAD FACT:

- Router dùng method_exists trước khi gọi action, nhưng method protected kế thừa cũng có thể được nhận diện; một URL trỏ đến method không public có thể gây lỗi runtime.
- Khi action không tồn tại trên controller có thật, router đổi tên action thành notFound nhưng không đảm bảo controller đó có notFound; chỉ HomeController khai báo action này trong HEAD.
- Router không phát sinh 405 Method Not Allowed.
- Route contract phụ thuộc tên file, tên class và tên method, do đó rename có thể là breaking change công khai.

GOVERNANCE:

- Không thêm public controller method nếu chưa xác định method đó có được phép trở thành route hay không.
- Mutation mới phải có method contract rõ; POST là mặc định cho form mutation.
- Một route table/allowlist hoặc middleware chỉ là phương án có thể xem xét, không phải quyết định đã được tài liệu này thông qua.
- Thay đổi URL hiện hữu, cơ chế dispatch hoặc xử lý 404/405 cần ADR nếu ảnh hưởng xuyên nhiều controller hoặc phá tương thích.

## 6. Các thành phần MVC

### 6.1 Core

HEAD FACT:

- Router chịu trách nhiệm convention-based dispatch.
- Controller cung cấp render, model loader, redirect và isPost.
- Controller::render dùng extract để đưa data thành biến view, sau đó include header, view và footer.
- helpers.php cung cấp formatPrice, renderStars, e, url, productImageUrl, currentUser, cartItems, cartCount và cartSubtotal.

Rủi ro hiện hữu:

- extract tạo context ngầm và có thể gây va chạm tên biến;
- lỗi view/model dùng die thay vì response/error handler tập trung;
- helpers phụ thuộc trực tiếp vào superglobals cho một số trạng thái.

### 6.2 Controllers

HEAD FACT: có năm controller:

| Controller | Trách nhiệm hiện tại |
| --- | --- |
| HomeController | Trang chủ, tìm kiếm, danh mục và 404. |
| ProductController | Chi tiết sản phẩm, ảnh, thông số, review và sản phẩm liên quan. |
| AuthController | Đăng nhập, đăng ký và đăng xuất bằng session. |
| CartController | Đọc và mutation giỏ hàng trong session. |
| CheckoutController | Hiển thị checkout, nhận form, gọi Order model và hiển thị success. |

Các controller hiện đọc trực tiếp $_GET, $_POST và $_SESSION. Validation nằm inline và không đồng nhất giữa các use case.

### 6.3 Models và persistence access

HEAD FACT: có bảy model:

| Model | Trách nhiệm hiện tại |
| --- | --- |
| Product | Catalog queries, search, category, flash sale, product detail và related products. |
| User | Lookup, password hashing, account creation và password verification. |
| Order | Tạo order và order items trong PDO transaction. |
| Brand | Danh sách thương hiệu. |
| Banner | Banner theo placement/type. |
| Post | Bài viết mới. |
| Review | Review mới và review theo product. |

Models trả về associative arrays hoặc false/empty arrays; không có entity objects hay repository abstraction trong HEAD. Order chứa cả transaction orchestration và SQL. Đây là convention hiện hành, không tự động là yêu cầu phải tách layer.

### 6.4 Views

HEAD FACT:

- views là PHP templates;
- layouts/header.php và layouts/footer.php bao quanh view theo mặc định;
- home/_product_card.php là partial dùng lại;
- e() là HTML escaping helper chính;
- view dùng snake_case keys từ database nhưng controller-to-view variables thường dùng camelCase;
- một số views và layouts đọc trực tiếp query/session-derived helpers.

GOVERNANCE:

- Dữ liệu không tin cậy phải được encode đúng context khi render.
- e() chỉ là HTML escaping; dữ liệu chèn vào JavaScript, URL, CSS hoặc event attributes cần cơ chế encode phù hợp với context đó.
- View mới không được tự mở PDO connection hoặc tự thực hiện mutation.
- Giữ convention layout và partial hiện hữu trừ khi một ADR phê duyệt thay đổi rendering model.

### 6.5 Trạng thái CartService

HEAD FACT: commit baseline không có app/services và không có CartService.

WT CANDIDATE:

- app/services/CartService.php đang là untracked file;
- các bản sửa chưa commit của CartController và CheckoutController gọi CartService;
- candidate service rehydrate cart từ Product model thay vì tin toàn bộ giá/tên được lưu trong session.

Candidate này có thể giải quyết duplication và trust-boundary cụ thể, nhưng:

- chưa phải dependency rule của hệ thống;
- không chứng minh rằng mọi controller cần service;
- không cho phép tự động tạo Repository/Clean/DDD layers;
- cần review cùng controller, model, session contract và tests trước khi được coi là accepted.

## 7. Persistence và data contracts

### 7.1 Database connection

HEAD FACT:

- config/database.php triển khai singleton PDO nullable;
- thông số host/database/user/password được hard-code bằng constants;
- PDO dùng exception mode, associative fetch mode và native prepared statements;
- lỗi kết nối bị chuyển thành null;
- models tự require database config.

WT CANDIDATE: database config đang được sửa để đọc DB_* environment variables và optional database.local.php; database.local.example.php là untracked candidate.

GOVERNANCE:

- Secret thật không được commit.
- Cấu hình production không được phụ thuộc mặc định root/blank password.
- Việc chấp nhận cơ chế env/local candidate phải đi cùng quy ước environment, error handling và deployment documentation.

### 7.2 Schema HEAD

HEAD FACT: database/schema.sql là destructive fresh-install script: drop và tạo lại database techpilot. Schema HEAD gồm users, categories, brands, products, product_images, carts, cart_items, orders, order_items, reviews, wishlists, flash_sales, banners, posts và coupons.

Các thực tế quan trọng:

- runtime cart của HEAD dùng PHP session, không dùng carts/cart_items;
- Order model ghi orders/order_items trong transaction;
- catalog và checkout trao đổi dữ liệu bằng associative arrays;
- schema script không phải migration chain và không an toàn để chạy trên database production có dữ liệu.

WT CANDIDATE: schema.sql đang được sửa lớn theo hướng roles, variants, inventory, payments, shipments, status history và các bảng khác; database/README.md là untracked candidate. Đây không phải schema baseline cho tới khi được phê duyệt và commit.

### 7.3 Giá, tồn kho và checkout

HEAD FACT:

- CartController lưu name, slug, price và quantity trong session khi add.
- Cart và Checkout tính tiền từ giá trong session.
- Order model tin subtotal, shipping, total và item prices do controller truyền vào; transaction chỉ đảm bảo các INSERT cùng commit/rollback.
- HEAD chưa đọc lại giá/tồn kho từ database, chưa khóa inventory và chưa có idempotency key.

WT CANDIDATE: Product, CartService, CheckoutController, Order và schema đang được sửa để tăng revalidation, transaction locking, idempotency và inventory/flash-sale handling.

GOVERNANCE: quyết định nguồn sự thật cho price, stock, variant, promotion và order totals là checkpoint bắt buộc. Không merge một phần schema/model/controller khiến nhiều nguồn dữ liệu mâu thuẫn mà không có test end-to-end và kế hoạch dữ liệu.

## 8. Authentication, authorization và session

### 8.1 Baseline HEAD

HEAD FACT:

- session_start chạy trong config/app.php;
- login lookup user theo email và dùng password_verify;
- register dùng password_hash với PASSWORD_DEFAULT;
- user array sau khi bỏ password được lưu trong $_SESSION['user'];
- logout xóa session user và destroy session;
- role/authorization không được router hoặc controller thực thi;
- không có middleware auth/RBAC;
- không có CSRF token trong form/action HEAD;
- login HEAD không regenerate session ID;
- cookie flags, login throttling và security headers không được cấu hình trong code HEAD.

### 8.2 Candidate đang thử nghiệm

WT CANDIDATE: helpers, views và controllers đang được sửa để thêm CSRF, POST-only logout, session_regenerate_id sau login và validation mạnh hơn. User model candidate cũng thay đổi role/status behavior.

Không được ghi nhận các candidate này là security control đã triển khai cho tới khi:

1. contract form/action được review;
2. happy path và invalid/expired token được test;
3. session cookie policy được quyết định;
4. backward compatibility của logout và các form được xác nhận;
5. thay đổi được phê duyệt và commit.

## 9. Validation và output safety

HEAD FACT:

- AuthController validate required fields, email và password confirmation; minimum password trong HEAD là 6 ký tự.
- CartController cast product ID/quantity và có POST guard cho mutation.
- CheckoutController HEAD chủ yếu trim input, không có validation domain đầy đủ.
- Product search trim query input và model dùng prepared statements cho input values.
- e() dùng htmlspecialchars với ENT_QUOTES và UTF-8.

Validation hiện là convention hỗn hợp, chủ yếu nằm trong controller hoặc thông qua cast ngay tại điểm dùng. Không có validator package, request DTO hay schema validation trong HEAD.

GOVERNANCE:

- Mỗi action phải xác định input contract, normalization, validation failure behavior và output encoding.
- Không tin giá, tổng tiền, quyền truy cập hoặc identity do browser gửi.
- Việc tạo validator dùng chung là tùy chọn; chỉ đưa vào khi có lợi ích cụ thể và không tạo layer bắt buộc không cần thiết.

## 10. Error handling, configuration và observability

HEAD FACT:

- config/app.php bật error_reporting(E_ALL) và display_errors=1 vô điều kiện;
- database connection failure trả null;
- một số models trả empty data khi không có database;
- Order rollback và trả false khi Throwable;
- không có error handler, logger abstraction, structured logs, health endpoint hoặc metrics trong HEAD.

WT CANDIDATE: có hai server log files untracked và một số fallback/error messages mới. Log files không phải source artifact và không chứng minh có observability production.

GOVERNANCE:

- Production không được hiển thị stack trace hoặc database details cho client.
- Error response và logging policy cần được quyết định theo environment.
- Demo fallback và production failure behavior phải được phân biệt rõ; không tự động hiển thị dữ liệu giả khi production database hỏng nếu chưa có quyết định được phê duyệt.

## 11. Testing và quality gates

HEAD FACT:

- không phát hiện tests directory, PHPUnit configuration, Composer scripts, static-analysis configuration, CI workflow, Dockerfile hoặc reproducible verification command trong commit baseline;
- README HEAD tuyên bố đã kiểm thử một số luồng nhưng không cung cấp automated evidence;
- public/router.php hỗ trợ chạy thủ công bằng PHP built-in server.

WT CANDIDATE: tại thời điểm rà soát vẫn chưa phát hiện automated tests hoặc CI cho các thay đổi lớn về auth/cart/checkout/schema.

GOVERNANCE: mức test cần tỷ lệ với rủi ro, nhưng các thay đổi sau không được chỉ dựa vào manual happy path:

- auth/session/CSRF;
- cart price và quantity;
- checkout totals, transaction, idempotency và concurrent stock;
- schema migration có dữ liệu;
- router, 404/405 và public URL compatibility.

Việc chọn PHPUnit, công cụ static analysis hoặc CI provider là quyết định triển khai riêng; tài liệu này không tự chọn công cụ.

## 12. Conventions cần bảo toàn

Các ràng buộc sau áp dụng cho thay đổi mới trừ khi ADR được phê duyệt nêu rõ ngoại lệ:

1. Giữ custom plain-PHP MVC là baseline; không âm thầm đưa framework hoặc kiến trúc mới vào.
2. public/ là web root chuẩn và public/index.php là front controller cho request động.
3. Tên controller dùng PascalCase và hậu tố Controller; model hiện dùng danh từ số ít; route public dùng lower-case controller/action paths.
4. Controllers là HTTP boundary hiện hữu; views là rendering boundary; models là nơi persistence hiện đang được thực hiện.
5. Không bắt buộc một Service hoặc Repository cho mọi feature. Chỉ thêm abstraction khi có responsibility, ownership và test boundary cụ thể.
6. Không để view mới thực hiện database write hoặc business mutation.
7. Database access có dữ liệu người dùng phải tiếp tục dùng parameter binding/prepared statements.
8. Output động phải được encode theo context.
9. Thay đổi state phải có HTTP method contract và CSRF decision rõ.
10. Public URL, session key, view-data key và schema column đang được dùng là compatibility contracts; rename cần impact analysis.
11. Schema production không được nâng cấp bằng cách chạy destructive fresh-install script.
12. Tài liệu không được nâng WT CANDIDATE thành fact nếu chưa có commit và approval evidence.

## 13. Rủi ro và checkpoint cần con người phê duyệt

| Chủ đề | Rủi ro quan sát được | Checkpoint bắt buộc |
| --- | --- | --- |
| Router/action exposure | Protected/missing action có thể gây lỗi; không có 405. | Human review trước khi đổi dispatch, thêm allowlist/route table hoặc đổi URL. |
| Web root/canonical URL | Root index compatibility và public/ có thể tạo nhiều base path. | Human xác nhận deployment contract trước khi xóa redirect hoặc đổi rewrite. |
| Auth/session | HEAD thiếu CSRF, session regeneration, cookie policy và throttling. | Human security approval cho contract mới và migration của forms/logout. |
| Authorization | Không có RBAC enforcement dù data model có thể chứa role. | Human xác định role model và protected routes trước khi thêm admin feature. |
| Price/stock/order totals | HEAD tin session/browser-derived state; candidate thay đổi nhiều nguồn dữ liệu. | Human business + data approval cho source of truth và concurrency behavior. |
| Schema V2 candidate | Schema candidate thay đổi lớn và fresh-install script destructive. | Human DBA/product approval, migration/rollback/reconciliation plan và backup evidence. |
| Cart persistence | Runtime dùng session trong khi schema HEAD có cart tables. | Human product decision: session-only, account cart hay merge behavior. |
| Optional CartService | Candidate có thể cải thiện coordination nhưng thay đổi dependency flow. | Human review theo use case; không suy diễn thành universal service rule. |
| Demo fallback | Candidate có catalog fallback khi database mất kết nối. | Human quyết định rõ behavior theo environment. |
| Error/config policy | HEAD hiển thị lỗi và hard-code DB credentials. | Human operations/security approval cho environment contract. |
| Test/tooling | Không có reproducible automated gate. | Human chọn mức gate và tool phù hợp trước thay đổi high-risk. |
| Framework/layer adoption | Có thể làm tăng migration cost và phá convention. | ADR riêng; không được suy ra từ một feature hoặc một candidate file. |

AI, automation hoặc tác nhân triển khai không được tự đánh dấu checkpoint là approved. Việc code đã tồn tại trong working tree hoặc đã chạy được không thay thế phê duyệt.

## 14. Quy trình thay đổi kiến trúc

1. Ghi vấn đề và bằng chứng, chỉ rõ HEAD FACT và WT CANDIDATE.
2. Xác định thay đổi có chạm checkpoint ở mục 13 hay không.
3. Nếu có, tạo ADR từ docs/architecture/adr/ADR-TEMPLATE.md ở trạng thái Proposed.
4. Nêu ít nhất phương án “giữ nguyên” và các trade-off.
5. Chỉ owner/approver là con người có thẩm quyền mới được chuyển ADR sang Accepted hoặc Rejected, kèm bằng chứng phê duyệt.
6. Triển khai trong scope đã duyệt; commit implementation không được coi là approval.
7. Chạy verification đã cam kết và ghi commit thực tế vào ADR.
8. Cập nhật tài liệu này nếu architecture baseline thực sự thay đổi.

## 15. Tiêu chí cập nhật tài liệu

Tài liệu phải được review lại khi xảy ra một trong các việc sau:

- thêm/xóa/rename controller, public action hoặc route;
- thay đổi web root, front controller hoặc rewrite behavior;
- thêm framework, package manager hoặc abstraction bắt buộc;
- thay đổi session/auth/RBAC contract;
- thay đổi ownership của price, stock, promotion hoặc checkout;
- thay đổi schema theo cách cần migration;
- thêm rendering mode hoặc API surface mới;
- candidate quan trọng được phê duyệt và merge vào baseline.

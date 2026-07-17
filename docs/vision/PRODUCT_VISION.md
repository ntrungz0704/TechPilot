# TechPilot Product Vision

| Thuộc tính | Giá trị |
|---|---|
| Document status | `DRAFT` |
| Planning authority | ChatGPT Work |
| Decision owner | Human Project Owner (`UNRESOLVED`) |
| Human approval | `HUMAN_PLAN_APPROVAL_REQUIRED` |
| Approved by | `UNRESOLVED` |
| Approval date | `UNRESOLVED` |
| Canonical baseline | `1ae679461e1f709488155ebf275ef070b54d723a` |

Đây là bản hợp nhất có kiểm soát từ tài liệu design chưa được Git theo dõi. Nội
dung là đề xuất, không phải Product Vision đã được Human phê duyệt.

## Ultimate goal đề xuất

Giúp người mua công nghệ tại Việt Nam đi từ nhu cầu đến một đơn hàng chính xác,
minh bạch và có thể phục hồi khi lỗi, đồng thời giữ mọi quyết định sản phẩm và kỹ
thuật có thể truy vết trong repository.

Human Project Owner là decision owner duy nhất có thể chấp nhận, sửa hoặc từ chối
goal này.

## Tầm nhìn

TechPilot hướng tới một cửa hàng công nghệ trực tuyến đáng tin cậy cho người mua
tại Việt Nam. Sản phẩm giúp khách hàng chọn đúng thiết bị theo nhu cầu, ngân sách
và hiệu năng mà không buộc họ tự giải mã quá nhiều thông số kỹ thuật.

Trải nghiệm mong muốn: hiện đại nhưng thực dụng, minh bạch về giá/tồn kho/giao
nhận/bảo hành, ưu tiên tìm kiếm và hoạt động tốt trên mobile.

## Vấn đề cần giải quyết

- Người mua phổ thông khó hiểu sự khác nhau giữa nhiều cấu hình.
- Người mua hiệu năng cần lọc nhanh theo tiêu chí quyết định.
- Giao dịch giá trị cao cần thông tin giá, tồn kho, phí, bảo hành và trạng thái
  đơn nhất quán.
- Checkout không được tạo cảm giác thành công giả khi database hoặc transaction
  thất bại.

## Người dùng mục tiêu

### Guided shopper

Người mua laptop/PC cho học tập, văn phòng hoặc giải trí nhưng không rành cấu
hình. Thành công khi họ tự tin chọn và thêm sản phẩm phù hợp vào giỏ.

### Performance buyer

Người mua gaming, creator hoặc linh kiện. Thành công khi họ lọc và so sánh được
các yếu tố hiệu năng/giá trị mà không cần mở nhiều website.

### Trust-focused shopper

Người chuẩn bị chi khoản tiền lớn. Thành công khi họ hiểu rõ tổng chi phí và hoàn
tất checkout mà không gặp thông tin bất ngờ.

## Nguyên tắc sản phẩm đề xuất

1. Rõ ràng trước, chi tiết kỹ thuật xuất hiện đúng lúc.
2. Giá, tồn kho, shipping và total phải có nguồn server đáng tin cậy.
3. Search-first và mobile-first.
4. Guest checkout không bị login chặn trong P0 đề xuất.
5. COD là phương thức vận hành đề xuất cho P0; không giả lập gateway thật.
6. AI chỉ xuất hiện khi có dữ liệu, rule và giá trị giải thích được.
7. Không dùng CTA hoặc link trông như hoạt động nếu feature chưa tồn tại.
8. Accessibility là acceptance criterion, không phải phần trang trí sau cùng.

## Critical journeys đề xuất

1. Home → Category/Search → Filter/Sort → Product Detail → Add to Cart.
2. Search → Results → Product Detail.
3. Product Detail → kiểm tra giá/tồn kho/thông số/giao nhận/bảo hành.
4. Cart → cập nhật số lượng → Checkout → Review → Submit → Success.
5. Phục hồi khi đổi giá, hết hàng, validation lỗi hoặc tạo đơn thất bại.
6. Hoàn thành các hành trình trên ở mobile mà không cần chuyển sang desktop.

## Kết quả mong muốn

- Người dùng tìm được sản phẩm phù hợp và hiểu lý do lựa chọn.
- Không có sai lệch giữa Cart, Checkout và Order về giá/tồn kho/phí/tổng.
- Critical journey không có dead end, link giả hoặc success giả.
- Mọi thay đổi có contract, test/evidence và independent review.
- Repository luôn cho biết phase, checkpoint, role và next action chính thức.

## Assumptions đang chờ xác nhận

- Thị trường ưu tiên là Việt Nam, nội dung tiếng Việt và tiền tệ VND.
- Codebase PHP MVC thuần tiếp tục là nền tảng trong các phase gần nhất.
- Guest checkout và COD là ứng viên hợp lý cho P0, chưa phải quyết định đã duyệt.
- Catalog, giá, tồn kho và order data có thể được chuẩn hóa đủ cho critical journey.
- Human sẽ chỉ định Owner, Writer, Reviewer và môi trường validation trước execution.

Assumption không phải fact hay approval. Assumption sai phải đưa plan về
`PLAN_REVIEW`.

## Constraints cần bảo toàn

- Codebase hiện là PHP MVC thuần, MySQL/MariaDB, HTML/CSS/JavaScript.
- Architecture được khám phá từ code, không áp đặt một kiến trúc mới.
- Không tự chuyển sang React/Vue, DDD, Clean/Hexagonal, CQRS hoặc microservices.
- Layer tùy chọn chỉ được dùng khi đã tồn tại hoặc checkpoint được Human phê
  duyệt.
- Figma có thể là công cụ authoring/reference; repository mới là canonical state.

## Limitations của vision draft

- Chưa có Human approval record hoặc named decision owner canonical.
- Chưa có user-research/analytics evidence được lưu trong repository.
- Dirty product delta chưa có contract/review nên không chứng minh vision đã được
  implementation.
- Design inputs còn conflict về P0 scope và PDP layout.
- Vision không quyết định phase, checkpoint, budget, timeline hay release date.

## Không phải cam kết hiện tại

AI Advisor, PC Builder, personalization, marketplace, native app, full admin
redesign, payment gateway thật và các feature P1/P2 không được coi là đã duyệt chỉ
vì xuất hiện trong concept hoặc prototype.

## Conflict cần Human quyết định

- UX/UI draft đưa AI Advisor, compare, wishlist, Q&A, coupon/payment cards vào
  core screens; Phase-0 draft lại loại chúng khỏi P0.
- PDP desktop được mô tả cả `7/5` và `6/6` columns.
- Phase-0 draft tự nhận đã `LOCKED` nhưng chưa có approval record canonical.
- Scope tier P0/P1/P2 đang bị trộn với delivery Phase 0/1/5.

Human Project Owner phải giải quyết các conflict này trước khi đổi trạng thái tài
liệu sang `PLAN_APPROVED`.

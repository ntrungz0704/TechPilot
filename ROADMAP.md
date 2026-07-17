# TechPilot Roadmap

| Thuộc tính | Giá trị |
|---|---|
| Document status | `DRAFT` |
| Planning authority | ChatGPT Work |
| Decision owner | Human Project Owner (`UNRESOLVED`) |
| Plan approval | `HUMAN_PLAN_APPROVAL_REQUIRED` |
| Active phase | `UNRESOLVED` |
| Active checkpoint | `NO_ACTIVE_CHECKPOINT` |
| Canonical baseline | `1ae679461e1f709488155ebf275ef070b54d723a` |

Roadmap này là đề xuất để Human Project Owner review. Nó không mở phase, không cấp
quyền implementation và không phải `PLAN_APPROVED`.

Các trường trạng thái trên là snapshot tiện tra cứu ngày `2026-07-16`.
`docs/checkpoints/ACTIVE.md` là bảng trạng thái live canonical duy nhất. Nếu khác
nhau, ưu tiên `ACTIVE.md` và báo conflict.

## Ultimate goal đề xuất

Đưa TechPilot tới một storefront commerce có thể vận hành an toàn: người mua tìm,
đánh giá và đặt sản phẩm với giá/tồn kho/phí/tổng nhất quán; team có thể thay tool
mà không thay scope, authority hoặc quality gate.

Human Project Owner là người duy nhất quyết định goal, thứ tự phase, investment và
release. Roadmap này không cam kết deadline.

## Bối cảnh cần giải quyết trước

Gói tài liệu chưa được Git theo dõi tại `techpilot/design/phase-0/` tự ghi
`LOCKED`, Phase 0 đã đóng và Phase 1 có thể bắt đầu. Tuy nhiên chưa có approval
record, người duyệt hoặc commit chứa tài liệu. Gói này cũng dùng cả “Phase 0/1/5”
và “P0/P1/P2” cho hai ý nghĩa khác nhau.

Trong roadmap này:

- **Phase** là giai đoạn delivery được Human phê duyệt.
- **P0/P1/P2** chỉ là tier ưu tiên sản phẩm lấy từ draft cũ, chưa phải phase state.

## Trình tự phase đề xuất

| Phase đề xuất | Mục tiêu | Entry condition | Exit condition | Trạng thái |
|---|---|---|---|---|
| `PROPOSED-01` Baseline & Governance | Chốt Owner, xử lý dirty baseline, duyệt vision/roadmap và vận hành governance | Human xác nhận vai trò và cách xử lý diff cũ | Canonical docs được Human phê duyệt; active checkpoint đầu tiên có contract | `DRAFT` |
| `PROPOSED-02` Foundations & Semantic Tokens | Chốt foundation thiết kế và mapping token repository/Figma | Phase trước đạt exit; scope P0 được duyệt | Token, component foundation và vertical-slice plan được review | `DRAFT` |
| `PROPOSED-03` Commerce Vertical Slice | Chứng minh Home → PLP → PDP → Cart theo MVC hiện tại | Contract/data/UX cho slice được duyệt | Slice chạy với test/evidence, independent review đạt gate | `DRAFT` |
| `PROPOSED-04` P0 Purchase Funnel | Hoàn thiện Cart → Checkout → Order Success và các critical states | Slice đạt gate; backend blockers được refresh | P0 acceptance, accessibility và data correctness đạt gate | `DRAFT` |
| `PROPOSED-05` Release Hardening | Migration, security, performance, content, observability và release readiness | Candidate P0 ổn định | Không còn blocker; rollback/release plan được Human duyệt | `DRAFT` |
| `PROPOSED-06` P1 Retention & Self-service | Account, order tracking, wishlist/compare và các mục P1 đã được chọn | Dữ liệu P0 và KPI đủ để quyết định | Exit conditions do phase contract tương lai quy định | `DRAFT` |
| `PROPOSED-07` P2 Differentiation | AI Advisor, PC Builder và differentiation được chứng minh bằng data | P1 ổn định; Human duyệt business case | Exit conditions do phase contract tương lai quy định | `DRAFT` |

Tên, số thứ tự và nội dung đều là đề xuất. Human có thể đổi, gộp hoặc loại bỏ trước
khi chuyển sang `PLAN_APPROVED`.

## Dependencies bắt buộc

- Human Project Owner và GitHub handle được xác nhận canonical.
- Dirty product baseline được Human phân loại, tách và gắn candidate SHA rõ.
- Product Vision, P0 scope và terminology Phase/P-tier được Human quyết định.
- Conflict UX/UI `7/5` với `6/6` và P0/P1/P2 feature placement được giải quyết.
- Architecture/runtime/test baseline được audit trên một candidate commit.
- Mỗi phase/checkpoint có dependency owner, môi trường test và evidence path.
- Candidate commit phải được Human materialize trước independent gate review.

## Deliverables đề xuất

1. Governance baseline và canonical state có thể kiểm tra tự động.
2. Product Vision, roadmap và phase contracts được Human duyệt theo version.
3. Design/token foundation có mapping repository rõ, không coi Figma là project
   state canonical.
4. Commerce vertical slice có contract, runtime evidence và independent review.
5. P0 purchase funnel đạt data correctness, accessibility và failure recovery.
6. Release package có security/performance evidence, release và rollback plan.

Deliverable chỉ được coi là hoàn tất khi phase/checkpoint exit criteria tương ứng
được ghi canonical; file tồn tại không đồng nghĩa hoàn tất.

## Scope tier đề xuất từ baseline cũ

### P0 — Commerce cơ bản

- Storefront responsive tiếng Việt, VND.
- Home, search/PLP, product detail, cart, guest checkout COD và order success.
- Login/register hỗ trợ nhưng không chặn guest checkout.
- Giá, tồn kho, shipping và total do server xác nhận.
- Loading/empty/error/disabled/out-of-stock state và WCAG cho critical journey.

### P1 — Retention và self-service

- Account/profile/address book.
- Order list/detail/tracking.
- Forgot password, wishlist, compare, review/Q&A.
- Coupon/promotion, search autocomplete, recently viewed và finder theo rule.

### P2 — Differentiation

- AI Advisor có dữ liệu và giải thích.
- PC Builder/compatibility, lưu/chia sẻ cấu hình.
- Personalization, loyalty, trade-in và omnichannel.

Các tier trên chưa được Human phê duyệt. UX/UI draft hiện còn đưa một số P1/P2
feature vào màn hình P0; conflict này phải được giải quyết trước phase approval.

## Gate chung cho mọi phase

- Có phase document với entry/exit condition rõ.
- Mỗi checkpoint có contract, Writer và Reviewer độc lập.
- Không thay đổi ngoài allowlist.
- Required tests/evidence có kết quả thực tế.
- `GATE_PASS` gắn với đúng candidate SHA.
- Human Project Owner thực hiện commit/merge/push/release và cập nhật canonical
  state.

## Exit criteria của roadmap draft

Roadmap chỉ có thể đề nghị chuyển khỏi `DRAFT` khi:

- Human đã xác nhận decision owner.
- Ultimate goal, phase sequence, dependencies, deliverables và out-of-scope được
  review.
- Mỗi phase có owner, entry/exit condition và checkpoint decomposition đủ rõ.
- Risk/assumption/limitation có disposition hoặc owner.
- Approval record chỉ tới đúng document commit.

Roadmap được duyệt vẫn không tự mở phase hoặc checkpoint.

## Out of scope của roadmap này

- Approve hoặc hợp thức hóa dirty product delta hiện tại.
- Chọn implementation HOW cho checkpoint tương lai.
- Cam kết ngày phát hành, ngân sách hoặc staffing khi chưa có Human decision.
- Tự thêm framework, architecture layer, dependency, payment gateway hoặc AI
  feature.
- Thay checkpoint contract, gate review hoặc release approval.

## Risks

| Risk | Tác động | Mitigation/decision cần có |
|---|---|---|
| Dirty source chưa có ownership/contract | Không xác định candidate hợp lệ | Human tách và materialize candidate commit |
| Design docs tự nhận `LOCKED` | Có thể mở sai phase/scope | Giữ `UNVERIFIED`, Human review lại |
| Phase và P-tier bị trộn | Roadmap/dependency sai | Chuẩn hóa vocabulary trước approval |
| Không có test suite/evidence baseline | Gate có thể dựa trên claim | Audit command thực tế theo checkpoint |
| Figma/repository lệch token | Design và code không đồng bộ | Repository giữ approved mapping canonical |
| Writer/Reviewer không độc lập | Gate mất giá trị | Human gán hai identity/phiên khác nhau |

## Assumptions

- PHP MVC hiện tại được bảo toàn trong các phase đề xuất.
- P0 commerce journey là ưu tiên business hợp lý nhưng chưa được Human duyệt.
- Human có thể cung cấp môi trường, credential và release decision khi cần.
- P1/P2 chỉ bắt đầu sau P0 evidence; không mở feature chỉ vì có prototype.

Assumption sai phải được ghi risk/change request; không tự sửa roadmap state.

## Limitations

- Chưa có named Human Project Owner, budget, timeline hoặc staffing plan.
- Phase sequence là proposal, chưa có dependency estimation được xác minh.
- Backend blocker list chưa được refresh trên candidate SHA.
- Không có production analytics/user research canonical để ưu tiên chi tiết.
- Roadmap không chứng minh code hiện tại đã đạt bất kỳ phase nào.

## Decision owner

Human Project Owner (`UNRESOLVED`) quyết định approve/reject/defer roadmap và mọi
scope/phase change. ChatGPT Work chỉ là Planning Authority và không tự approve.

## Quyết định Human còn thiếu

1. Xác nhận Human Project Owner và GitHub handle.
2. Quyết định cách xử lý working-tree source changes tồn tại sẵn.
3. Duyệt hoặc sửa Product Vision và roadmap này.
4. Giải quyết conflict trong design draft và làm mới blocker register.
5. Chọn phase đầu tiên, checkpoint đầu tiên, Writer và Reviewer.

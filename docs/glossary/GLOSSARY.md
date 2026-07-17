# Glossary — Từ điển TechPilot

Các định nghĩa này dùng trong governance và onboarding.

| Khái niệm | Giải thích đơn giản | Ví dụ |
|---|---|---|
| Repository | Thư mục Git chứa code, tài liệu và trạng thái chính thức. | Repository TechPilot chứa `techpilot/`, `AGENTS.md` và `docs/`. |
| Branch | Dòng làm việc tách khỏi nhánh chính. | `cp/CP-01.1-cart-validation`. |
| Commit | Snapshot đã được Git ghi lại. | Commit lưu implementation của một checkpoint. |
| Commit SHA | Mã định danh duy nhất của commit. | `1ae6794...` là baseline đã audit. |
| Pull Request (PR) | Yêu cầu review và nhập một branch vào branch khác. | PR từ checkpoint branch vào `main`. |
| Diff | Danh sách thay đổi giữa hai trạng thái Git. | Reviewer dùng diff để tìm file ngoài allowlist. |
| Scope | Phần việc được phép làm. | “Sửa validation checkout, không đổi schema.” |
| Allowed paths | File/thư mục Writer được sửa. | `techpilot/app/controllers/CheckoutController.php`. |
| Forbidden paths | File/thư mục không được sửa trong checkpoint. | `docs/governance/**` trong checkpoint feature. |
| Phase | Giai đoạn delivery có mục tiêu và entry/exit condition. | Phase “Release Hardening”. |
| Checkpoint | Đơn vị công việc nhỏ, có contract và gate riêng. | `CP-03.2-checkout-validation`. |
| Contract | Tài liệu khóa scope, paths, acceptance, tests và evidence. | Reviewer đối chiếu code với checkpoint contract. |
| Acceptance criteria | Điều kiện cụ thể để xác định đạt hay không. | “Sai phone phải hiện lỗi và giữ old input.” |
| Writer | Người/agent thực hiện code trong approved scope. | Writer sửa allowlist và dừng ở `READY_FOR_REVIEW`. |
| Reviewer | Người/phiên độc lập kiểm tra Writer. | Reviewer rerun test nhưng không sửa source. |
| Planning Authority | Vai trò soạn WHY/WHAT, roadmap và contract. | ChatGPT Work đề xuất checkpoint. |
| Gate Authority | Quyền kiểm tra và đưa gate decision. | Independent Reviewer kết luận `GATE_PASS`. |
| Release Authority | Quyền merge, deploy, release và rollback. | Human Project Owner. |
| Human Project Owner | Người quyết định cuối cùng của dự án. | Duyệt phase và chỉ định Writer/Reviewer. |
| Canonical state | Trạng thái chính thức đã được ghi trong repository. | `ACTIVE.md` ghi checkpoint đang `PLAN_REVIEW`. |
| Handoff | Báo cáo Writer bàn giao cho Reviewer. | Liệt kê changed files, tests, evidence và risk. |
| Evidence | Bằng chứng thực tế cho kết quả. | Log test, screenshot runtime hoặc report kiểm tra. |
| Gate | Điểm kiểm tra bắt buộc trước merge/release. | Reviewer kiểm tra exact candidate SHA. |
| MVC | Cách tổ chức Model, View và Controller. | TechPilot dùng PHP MVC thuần. |
| Model | Lớp dữ liệu/persistence theo convention repo. | Model đọc product từ database. |
| View | Template hiển thị giao diện. | View render cart; không tự quyết định authorization. |
| Controller | Lớp nhận request và điều phối response. | Controller validate input rồi gọi Model/Service đã có. |
| Route | URL và HTTP contract dẫn request tới xử lý. | `/product/detail/{slug}`. |
| Middleware | Lớp xử lý request trước/sau controller nếu repo có dùng. | Kiểm tra auth trước route; không tự thêm nếu repo chưa có. |
| Validation | Kiểm tra input có hợp lệ không. | Phone bắt buộc đúng định dạng. |
| Dependency | Thư viện, service hoặc thành phần mà code phụ thuộc. | Payment gateway là external dependency. |
| CI | Kiểm tra tự động khi push/PR. | Chạy repo doctor và governance check trên GitHub Actions. |
| Deployment | Đưa build/code lên một môi trường chạy. | Deploy candidate đã được Human duyệt lên staging. |
| Candidate commit | Commit do Human materialize sau Writer handoff để Reviewer kiểm tra. | Full `candidate_sha` được ghi trong `ACTIVE.md`. |
| Preliminary patch review | Góp ý sơ bộ khi diff chưa thành candidate commit. | Có thể nêu finding nhưng không được `GATE_PASS`. |
| Reviewed commit | Commit chính xác Reviewer đã kiểm tra. | Gate PASS chỉ áp dụng SHA này. |
| `DRAFT` | Plan đang được soạn, chưa chờ duyệt chính thức. | Roadmap mới tạo bởi Planning Authority. |
| `PLAN_REVIEW` | Plan đang chờ Human review. | Không được bắt đầu source work. |
| `PLAN_APPROVED` | Human đã duyệt đúng contract/version. | Writer được phép bắt đầu nếu assignment hợp lệ. |
| `EXECUTING` | Writer đang làm approved checkpoint. | Writer sửa file trong allowlist. |
| `READY_FOR_REVIEW` | Writer xong và đã tạo handoff/evidence. | Chờ Reviewer độc lập. |
| `GATE_PASS` | Reviewed commit đạt contract. | Không đồng nghĩa được tự merge. |
| `REWORK_REQUIRED` | Reviewer tìm thấy lỗi cần Writer sửa. | Candidate mới phải review lại. |
| `BLOCKED` | Phải dừng vì không thể tiếp tục an toàn. | Thiếu credential hoặc governance conflict. |
| `REVIEW_INVALIDATED` | Source đổi sau review nên Gate PASS cũ hết hiệu lực. | Thêm một dòng code sau review. |
| `MERGED` | Human đã merge exact reviewed commit. | PR được nhập vào `main`. |
| `ROLLBACK_REQUIRED` | Sau merge có evidence cho thấy cần Human quyết định rollback hoặc forward fix. | Production lỗi nghiêm trọng trên merged SHA. |
| `CLOSED` | Human xác nhận checkpoint hoàn tất và state đã cập nhật. | Exit conditions đều đạt. |
| Tool adapter | Hướng dẫn dùng một tool mà không đổi governance. | Codex adapter mô tả cách giới hạn write. |
| Tool transition | Việc đổi công cụ trong cùng role/checkpoint. | Writer đổi từ Codex sang tool khác và ghi vào handoff. |
| Repo doctor | Script chỉ đọc để báo repo có thể bắt đầu không. | `PASS`, `WARNING` hoặc `BLOCKED`. |
| Governance conflict | Hai nguồn authority cùng cấp mâu thuẫn. | Hai contract active khác scope cho cùng checkpoint. |
| Destructive action | Hành động có thể mất dữ liệu hoặc khó phục hồi. | Xóa database, force push hoặc reset hard. |
| Rollback | Quay release về trạng thái an toàn trước đó. | Human đưa production về reviewed SHA cũ. |

Conversation không phải canonical state. Khi thuật ngữ hoặc trạng thái không rõ,
dùng `UNRESOLVED`/`UNASSIGNED` và báo Human thay vì tự đoán.

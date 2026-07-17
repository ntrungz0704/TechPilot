# Evidence conventions

Evidence chứng minh một acceptance criterion hoặc test result; nó không tự tạo approval.

## Cấu trúc đề xuất

Mỗi checkpoint dùng thư mục:

    docs/evidence/CP-XX.X/

Tên file nên nêu nội dung và thời điểm, ví dụ:

    php-lint-2026-07-16.txt
    test-results-2026-07-16.txt
    route-smoke-2026-07-16.md
    screenshots/

## Yêu cầu

- Ghi checkpoint ID, branch, HEAD/candidate SHA, command, môi trường, thời gian, result và exit code.
- Không sửa hoặc tóm tắt sai raw output; nếu output chứa secret, phải dừng và báo Human thay vì commit.
- Không commit database dump thật, credential, token, cookie, log có PII hoặc dữ liệu khách hàng.
- Screenshot phải loại bỏ thông tin nhạy cảm và có chú thích acceptance criterion.
- Handoff và review phải liên kết đúng evidence path.
- Evidence của commit cũ không chứng minh commit mới; source thay đổi sau review làm review invalid.

## Retention

Human Project Owner quyết định evidence nào được commit. File lớn hoặc dữ liệu nhạy cảm phải dùng kho phù hợp đã được duyệt và chỉ ghi reference an toàn trong repository.


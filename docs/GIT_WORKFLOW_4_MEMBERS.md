# Hướng dẫn Quy trình Git (Git Workflow) cho Nhóm 4 Thành viên — TechPilot

Tài liệu này định nghĩa quy trình Git chuẩn áp dụng cho tất cả thành viên trong nhóm phát triển dự án TechPilot nhằm đảm bảo mã nguồn ổn định, tránh xung đột và quản lý phiên bản chuyên nghiệp.

---

## 1. Sơ đồ cấu trúc Nhánh (Branching Model)

Dự án TechPilot áp dụng mô hình phân nhánh Git như sau:

```
                  ┌──────────────┐
                  │     main     │ (Nhánh Release ổn định)
                  └──────▲───────┘
                         │ (Pull Request phát hành)
                  ┌──────┴───────┐
                  │   develop    │ (Nhánh Tích hợp chung)
                  └──────▲───────┘
                         │ (Pull Request sau khi Review)
      ┌──────────────────┼──────────────────┐
      │                  │                  │
┌─────┴──────┐     ┌─────┴──────┐     ┌─────┴──────┐
│  trung/... │     │   dinh/... │     │   kim/...  │ (Các nhánh Feature)
└────────────┘     └────────────┘     └────────────┘
```

### Bảng phân vai và nhánh cá nhân (Nhiệm vụ):

| Thành viên | Tiền tố nhánh Feature | Vai trò / Phạm vi công việc chính |
|---|---|---|
| **Trung** | `trung/feature-...` | Layout chung, trang chủ, danh mục/tìm kiếm, product card và UI system |
| **Dinh** | `dinh/feature-...` | Đăng ký/đăng nhập, tài khoản cá nhân, chi tiết sản phẩm, wishlist và review |
| **Kim** | `kim/feature-...` | Giỏ hàng, mã giảm giá (coupon), checkout thanh toán COD, quản lý đơn hàng và transaction |
| **Hieu** | `hieu/feature-...` | Trang quản trị Admin CRUD tối thiểu, tối ưu responsive toàn trang, QA/Test, tài liệu và dọn dẹp |

---

## 2. Quy trình làm việc hàng ngày (Daily Workflow)

Mỗi thành viên tuyệt đối **KHÔNG** commit và push trực tiếp lên nhánh `main` hoặc `develop`. Mọi thay đổi phải đi qua nhánh feature riêng biệt tách từ `develop`.

### Bước 1: Cập nhật mã nguồn mới nhất từ nhánh `develop`
Trước khi bắt đầu code tính năng mới, hãy lấy code mới nhất từ nhánh tích hợp chung:
```bash
git checkout develop
git pull origin develop
```

### Bước 2: Tạo nhánh Feature từ nhánh `develop`
```bash
# Định dạng tên nhánh: <tên-thành-viên>/feature-<tên-tính-năng>
git checkout -b trung/feature-layout-mobile develop
```

### Bước 3: Phát triển và commit mã nguồn
Commit theo chuẩn **Conventional Commits**:
- `feat(home): redesign flash sale countdown layout`
- `fix(responsive): solve overlapping on 360px viewport`
- `docs(git): write git workflow for team members`
- `chore(cleanup): remove unused local js files`

### Bước 4: Đẩy nhánh Feature lên Remote GitHub
```bash
git push -u origin trung/feature-layout-mobile
```

### Bước 5: Mở Pull Request (PR) vào nhánh `develop`
- Truy cập GitHub và mở Pull Request từ nhánh feature của bạn (ví dụ: `trung/feature-layout-mobile`) thẳng vào nhánh `develop`.
- Điền đầy đủ thông tin theo mẫu **Pull Request Template** có sẵn.
- Tag ít nhất **1 thành viên khác** trong nhóm làm Reviewer.
- Chỉ merge sau khi Reviewer đã phê duyệt (`Approve`) và các bài kiểm thử tự động (nếu có) báo xanh.

---

## 3. Quy tắc xử lý xung đột (Conflict Resolution)

Khi xảy ra xung đột mã nguồn (`Merge Conflict`), tuyệt đối **KHÔNG** được tự ý chọn "Accept All Incoming" hoặc "Accept Current Change" một cách máy móc. Hãy thực hiện theo các bước sau:

1. **Xác định file bị xung đột**: Chạy lệnh `git status` để tìm các file có trạng thái `both modified`.
2. **Mở file xung đột**: Tìm các thẻ đánh dấu:
   ```
   <<<<<<< HEAD
   Mã nguồn hiện tại của bạn
   =======
   Mã nguồn mới của người khác
   >>>>>>> develop
   ```
3. **Trao đổi trực tiếp**: Nói chuyện với thành viên đã viết đoạn code xung đột để thống nhất giải pháp giữ lại phần code nào hoặc kết hợp cả hai.
4. **Kiểm tra lại hệ thống**: Sau khi sửa thủ công, hãy chạy thử dự án (`php -S 127.0.0.1:8000 router.php`) để đảm bảo giao diện và logic hoạt động hoàn toàn bình thường trước khi commit.
5. **Hoàn tất merge**:
   ```bash
   git add <đường-dẫn-file>
   git commit -m "merge: resolve conflicts with develop"
   ```

---

## 4. Các lệnh Git bị nghiêm cấm (Forbidden Actions)

> [!CAUTION]
> - **CẤM** chạy lệnh `git push --force` (hoặc `-f`) lên các nhánh chung như `main` và `develop`.
> - **CẤM** chạy lệnh `git reset --hard` hoặc `git clean -fdx` trên thư mục dự án khi chưa lưu trữ các thay đổi quan trọng của thành viên khác.
> - **CẤM** commit các file chứa mật khẩu, API key, thông tin cấu hình cá nhân hoặc thư mục log chạy thử vào Git.

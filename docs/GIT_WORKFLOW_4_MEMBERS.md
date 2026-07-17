# Quy trình làm việc Git cho Nhóm Phát triển TechPilot (4 thành viên)

Tài liệu này quy định luồng làm việc với Git, cách đặt tên nhánh, quy ước commit và xử lý xung đột (conflict) cho nhóm 4 người trong dự án TechPilot.

---

## 1. Sơ đồ phân nhánh (Branching Model)

Dự án sử dụng mô hình Git Flow rút gọn phù hợp với nhóm nhỏ:

```text
main (Nhánh production ổn định)
  ▲
  │ (Pull Request / Merge khi phát hành bản ổn định)
  │
develop (Nhánh tích hợp chung - Chứa code mới nhất đã qua kiểm thử)
  ▲
  ├── trung (Nhánh làm việc của Trung - Design tokens, layout, header/footer)
  ├── dinh  (Nhánh làm việc của Dinh  - Trang chủ, Flash Sale, product card)
  ├── kim   (Nhánh làm việc của Kim   - Responsive, accessibility, animation nhẹ)
  └── hieu  (Nhánh làm việc của Hieu  - QA, test, viết tài liệu, dọn dẹp)
```

---

## 2. Ánh xạ vai trò và Nhánh cá nhân

| Thành viên | Nhánh chính | Vai trò đảm nhận |
|---|---|---|
| **Trung** | `trung` | Quản lý Design tokens, layout chính, header & footer |
| **Dinh** | `dinh` | Phát triển Trang chủ, tối ưu khu vực Flash Sale, product card |
| **Kim** | `kim` | Tối ưu hóa hiển thị Responsive, Accessibility (hỗ trợ đọc), Animation |
| **Hieu** | `hieu` | QA, viết Test case, soạn tài liệu dự án, kiểm kê & dọn dẹp tệp tin |

---

## 3. Quy trình làm việc hàng ngày

### Bước 1: Khởi tạo/Cập nhật code từ nhánh tích hợp chung (`develop`)
Mỗi ngày trước khi code, hãy đồng bộ nhánh cá nhân của bạn với code mới nhất trên `develop`:
```bash
git fetch origin --prune
git checkout develop
git pull --ff-only origin develop
git checkout <ten_nhanh_ca_nhan>  # Ví dụ: git checkout trung
git merge develop
```

### Bước 2: Tạo nhánh tính năng ngắn hạn (Feature Branch)
Khi làm một nhiệm vụ cụ thể, nên tách một nhánh ngắn hạn từ nhánh cá nhân của bạn:
```bash
git checkout -b <ten_nhanh_ca_nhan>/feature-<mo_ta_tinh_nang>
# Ví dụ: git checkout -b trung/feature-redesign-footer
```

### Bước 3: Commit code theo chuẩn Conventional Commits
Quy định định dạng thông điệp commit:
- `feat(scope):` Thêm tính năng mới.
- `fix(scope):` Vá lỗi.
- `docs(scope):` Cập nhật tài liệu.
- `style(scope):` Thay đổi format code, CSS (không đổi logic).
- `refactor(scope):` Tái cấu trúc mã nguồn.
- `chore(scope):` Các công việc lặt vặt khác (dọn dẹp, gitignore...).

*Ví dụ commit hợp lệ:*
```bash
git commit -m "feat(home): redesign flash sale cards for better alignment"
git commit -m "fix(responsive): prevent product grid overflow on mobile"
```

### Bước 4: Đẩy nhánh lên Remote và mở Pull Request (PR)
Sau khi hoàn thành và tự test cục bộ thành công:
```bash
git push -u origin <ten_nhanh_tinh_nang>
```
Mở Pull Request trên GitHub/GitLab từ nhánh của bạn vào nhánh `develop`.

---

## 4. Quy trình Review và Merge Pull Request
- **Quy tắc 2-Eyes:** PR phải được ít nhất **1 thành viên khác** trong nhóm review và phê duyệt (Approve) trước khi merge. Tác giả không tự ý merge code của mình.
- **Squash Merge:** Khi merge PR nhỏ vào `develop`, hãy dùng tùy chọn **Squash and Merge** để gộp lịch sử commit giúp lịch sử Git gọn gàng.
- **Bảo vệ nhánh (Branch Protection):**
  - Chặn push trực tiếp vào `main` và `develop`.
  - Bắt buộc phải có PR và ít nhất 1 Approve mới được merge.
  - Yêu cầu các bài kiểm tra tự động (CI) phải pass thành công.

---

## 5. Quy trình xử lý Xung đột (Resolve Conflict)

Khi bạn thực hiện `git merge develop` hoặc khi mở PR gặp conflict:
1. **Tìm file bị conflict:** Git sẽ thông báo danh sách file bị xung đột. Mở file đó trong IDE.
2. **Đọc kỹ các chỉ thị conflict:**
   ```text
   <<<<<<< HEAD
   Mã nguồn của bạn đang viết
   =======
   Mã nguồn mới nhất trên develop vừa được kéo về
   >>>>>>> develop
   ```
3. **Thảo luận và lựa chọn:** Liên hệ trực tiếp với người viết đoạn code xung đột để thống nhất phương án gộp. **KHÔNG tự ý chọn "Accept All"** hoặc xóa code của người khác khi chưa hiểu rõ.
4. **Đánh dấu đã giải quyết:** Sau khi sửa xong, lưu file và chạy:
   ```bash
   git add <ten_file>
   git commit -m "merge: resolve conflict with develop"
   ```

---

## 6. Các lệnh BỊ CẤM HOÀN TOÀN
- `git push --force` hoặc `git push -f` lên các nhánh chung (`main`, `develop`).
- Commit trực tiếp các thông tin nhạy cảm (mật khẩu database, API key...) vào Git.
- Tự ý chạy `git reset --hard` làm mất lịch sử commit của người khác.

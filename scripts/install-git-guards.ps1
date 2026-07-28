# TechPilot — Cài đặt Git Guards
# Chạy một lần sau khi clone:
#   powershell -ExecutionPolicy Bypass -File scripts/install-git-guards.ps1

Write-Host ""
Write-Host "=== TechPilot Git Guards Installer ===" -ForegroundColor Cyan
Write-Host ""

# Cài đặt hooks path
git config core.hooksPath .githooks

# Kiểm tra kết quả
$hooksPath = git config --get core.hooksPath

if ($hooksPath -eq ".githooks") {
    Write-Host "[PASS] Git hooks path đã được cài: .githooks" -ForegroundColor Green
} else {
    Write-Host "[FAIL] Không cài được Git hooks path." -ForegroundColor Red
    Write-Host "       Thử chạy thủ công: git config core.hooksPath .githooks" -ForegroundColor Yellow
    exit 1
}

Write-Host ""
Write-Host "Pre-commit hook sẽ chặn commit trên main/develop và branch không được phép." -ForegroundColor Yellow
Write-Host "Pre-push hook sẽ chặn push trực tiếp lên main/develop." -ForegroundColor Yellow
Write-Host ""
Write-Host "Lưu ý: Local hook không thay thế GitHub Ruleset." -ForegroundColor DarkGray
Write-Host "       Chủ repository cần cấu hình Ruleset trên GitHub Settings." -ForegroundColor DarkGray
Write-Host ""

exit 0

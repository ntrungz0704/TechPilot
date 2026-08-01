<div class="container" style="max-width: 600px; margin: 40px auto; padding: 0 15px;">
    <div style="background: #FFFFFF; border-radius: 16px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); border: 1px solid #E2E8F0; overflow: hidden;">
        
        <!-- Header VNPay Sandbox -->
        <div style="background: linear-gradient(135deg, #005BAA, #003B75); padding: 24px; color: #FFFFFF; text-align: center;">
            <div style="display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 8px;">
                <span style="background: #FFFFFF; color: #005BAA; font-weight: 900; padding: 4px 10px; border-radius: 6px; font-size: 16px; letter-spacing: 1px;">VNPAY</span>
                <span style="background: rgba(255,255,255,0.2); font-size: 11px; padding: 3px 8px; border-radius: 12px; font-weight: 600;">SANDBOX SIMULATOR</span>
            </div>
            <h2 style="font-size: 18px; font-weight: 700; margin: 0; color: #FFFFFF;">Cổng Thanh Toán Trực Tuyến VNPay</h2>
            <p style="font-size: 13px; opacity: 0.85; margin: 4px 0 0 0;">Môi trường thử nghiệm đơn hàng TechPilot</p>
        </div>

        <!-- Body Order Details -->
        <div style="padding: 24px;">
            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px; margin-bottom: 24px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span style="color: #64748B; font-size: 13px;">Mã đơn hàng:</span>
                    <strong style="color: #0F172A; font-size: 14px; font-family: monospace;"><?= e($orderCode) ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span style="color: #64748B; font-size: 13px;">Nhà cung cấp:</span>
                    <strong style="color: #0F172A; font-size: 13px;">TechPilot E-Commerce</strong>
                </div>
                <div style="display: flex; justify-content: space-between; border-top: 1px dashed #CBD5E1; padding-top: 10px; margin-top: 10px;">
                    <span style="color: #64748B; font-size: 14px; font-weight: 600;">Số tiền thanh toán:</span>
                    <strong style="color: #005BAA; font-size: 20px; font-weight: 800;"><?= e($displayAmount) ?></strong>
                </div>
            </div>

            <div style="text-align: center; margin-bottom: 24px;">
                <p style="font-size: 13.5px; color: #475569; margin-bottom: 16px;">Chọn kết quả thanh toán bạn muốn mô phỏng để kiểm tra hệ thống:</p>
                
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <a href="<?= e($successUrl) ?>" class="btn" style="background: linear-gradient(135deg, #10B981, #059669); color: #FFF; padding: 14px 20px; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 15px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);">
                        <i class="fa-solid fa-circle-check"></i> Xác Nhận Thanh Toán Thành Công
                    </a>

                    <a href="<?= e($failUrl) ?>" class="btn" style="background: #F1F5F9; color: #EF4444; border: 1px solid #FECDD3; padding: 12px 20px; border-radius: 10px; text-decoration: none; font-weight: 600; font-size: 14px;">
                        <i class="fa-solid fa-circle-xmark"></i> Hủy Giao Dịch / Thanh Toán Thất Bại
                    </a>
                </div>
            </div>

            <div style="font-size: 12px; color: #94A3B8; text-align: center; line-height: 1.5;">
                <i class="fa-solid fa-shield-halved"></i> Chế độ Mô phỏng Cổng VNPay hoàn tất xác thực HMAC SHA512 bảo mật và cập nhật trạng thái đơn hàng thời gian thực.
            </div>
        </div>
    </div>
</div>

<div class="vnpay-sim-wrap" style="max-width: 880px; margin: 30px auto; padding: 0 15px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <div style="background: #FFFFFF; border-radius: 16px; box-shadow: 0 20px 45px rgba(0,0,0,0.08); border: 1px solid #E2E8F0; overflow: hidden;">
        
        <!-- Header VNPay Simulator -->
        <div style="background: linear-gradient(135deg, #005BAA 0%, #003B75 100%); padding: 20px 24px; color: #FFFFFF; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="background: #FFFFFF; color: #005BAA; font-weight: 900; padding: 6px 14px; border-radius: 8px; font-size: 18px; letter-spacing: 1.5px; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                    VN<span style="color: #E11D48;">PAY</span>
                </div>
                <div>
                    <h1 style="font-size: 17px; font-weight: 700; margin: 0; color: #FFFFFF;">Cổng Thanh Toán Trực Tuyến</h1>
                    <span style="font-size: 12px; opacity: 0.85; display: inline-flex; align-items: center; gap: 4px;">
                        <i class="fa-solid fa-flask"></i> Trình mô phỏng thanh toán thông minh (Smart Simulator)
                    </span>
                </div>
            </div>

            <!-- Countdown Timer -->
            <div style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.25); border-radius: 10px; padding: 6px 14px; text-align: right;">
                <div style="font-size: 11px; opacity: 0.85; text-transform: uppercase;">Thời gian còn lại</div>
                <div id="simCountdown" style="font-size: 16px; font-weight: 800; font-family: monospace; color: #FDE047;">14:59</div>
            </div>
        </div>

        <!-- Notification Banner -->
        <div style="background: #FFFBEB; border-bottom: 1px solid #FDE68A; padding: 10px 24px; font-size: 13px; color: #92400E; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-circle-info" style="color: #D97706; font-size: 15px;"></i>
            <span>Đây là chế độ thử nghiệm. Bạn có thể <strong>quét mã QR bằng Camera điện thoại</strong> hoặc bấm nút <strong>[Xác nhận thanh toán]</strong> bên dưới.</span>
        </div>

        <!-- Main Grid -->
        <div style="display: grid; grid-template-columns: 1fr 1.3fr; gap: 24px; padding: 24px;">
            
            <!-- Left Column: Order Summary -->
            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <h3 style="font-size: 15px; font-weight: 700; color: #1E293B; margin-top: 0; margin-bottom: 16px; padding-bottom: 10px; border-bottom: 1px solid #E2E8F0;">
                        <i class="fa-solid fa-receipt" style="color: #005BAA; margin-right: 6px;"></i> Thông tin đơn hàng
                    </h3>

                    <div style="display: flex; flex-direction: column; gap: 12px; font-size: 13.5px;">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #64748B;">Mã đơn hàng:</span>
                            <strong style="color: #0F172A; font-family: monospace; font-size: 14px;"><?= e($orderCode) ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #64748B;">Nhà cung cấp:</span>
                            <strong style="color: #0F172A;">TechPilot Store</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #64748B;">Phí giao dịch:</span>
                            <strong style="color: #10B981;">0đ (Miễn phí)</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #64748B;">Nội dung:</span>
                            <span style="color: #334155; text-align: right; max-width: 180px; word-break: break-word;">Thanh toan don hang <?= e($orderCode) ?></span>
                        </div>
                    </div>
                </div>

                <div style="border-top: 2px dashed #CBD5E1; padding-top: 16px; margin-top: 20px;">
                    <div style="color: #64748B; font-size: 13px; margin-bottom: 4px;">Số tiền thanh toán</div>
                    <div style="color: #005BAA; font-size: 26px; font-weight: 800;"><?= e($displayAmount) ?></div>
                </div>
            </div>

            <!-- Right Column: Interactive QR Code & Simulator Controls -->
            <div style="display: flex; flex-direction: column; align-items: center; text-align: center;">
                
                <!-- Tab Controls -->
                <div style="display: flex; background: #F1F5F9; border-radius: 10px; padding: 4px; width: 100%; margin-bottom: 20px;">
                    <button type="button" id="tabQrBtn" onclick="switchSimTab('qr')" style="flex: 1; padding: 8px 12px; border: none; border-radius: 8px; background: #FFFFFF; color: #005BAA; font-weight: 700; font-size: 13px; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.06); transition: all 0.2s;">
                        <i class="fa-solid fa-qrcode"></i> Quét mã VNPAY-QR
                    </button>
                    <button type="button" id="tabAtmBtn" onclick="switchSimTab('atm')" style="flex: 1; padding: 8px 12px; border: none; border-radius: 8px; background: transparent; color: #64748B; font-weight: 600; font-size: 13px; cursor: pointer; transition: all 0.2s;">
                        <i class="fa-solid fa-credit-card"></i> Thẻ ATM Nội địa
                    </button>
                </div>

                <!-- TAB 1: QR CODE VIEW -->
                <div id="tabQrContent" style="width: 100%; display: flex; flex-direction: column; align-items: center;">
                    <div style="position: relative; padding: 16px; background: #FFFFFF; border: 2px solid #005BAA; border-radius: 16px; box-shadow: 0 8px 25px rgba(0, 91, 170, 0.12); margin-bottom: 16px;">
                        <img src="<?= e($qrImageUrl) ?>" alt="VNPAY-QR" style="width: 200px; height: 200px; display: block; border-radius: 8px;">
                        
                        <!-- Mini Logo in Center -->
                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: #FFFFFF; padding: 4px 6px; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); font-weight: 900; font-size: 11px; color: #005BAA;">
                            VN<span style="color: #E11D48;">PAY</span>
                        </div>
                    </div>

                    <p style="font-size: 13px; color: #475569; margin: 0 0 16px 0; line-height: 1.4;">
                        <i class="fa-solid fa-mobile-screen-button" style="color: #005BAA;"></i> Mở <strong>Camera điện thoại / Zalo / App Ngân hàng</strong> quét mã trên hoặc bấm nút dưới:
                    </p>

                    <!-- Quick Action Buttons -->
                    <div style="width: 100%; display: flex; flex-direction: column; gap: 10px;">
                        <a href="<?= e($successUrl) ?>" class="btn" style="background: linear-gradient(135deg, #10B981, #059669); color: #FFFFFF; padding: 13px 20px; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 15px; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);">
                            <i class="fa-solid fa-circle-check"></i> Xác Nhận Quét Mã & Thanh Toán Thành Công
                        </a>

                        <a href="<?= e($failUrl) ?>" class="btn" style="background: #F8FAFC; color: #EF4444; border: 1px solid #FECDD3; padding: 10px 18px; border-radius: 10px; text-decoration: none; font-weight: 600; font-size: 13px; display: flex; align-items: center; justify-content: center; gap: 6px;">
                            <i class="fa-solid fa-xmark"></i> Hủy giao dịch thanh toán
                        </a>
                    </div>
                </div>

                <!-- TAB 2: ATM CARD VIEW -->
                <div id="tabAtmContent" style="width: 100%; display: none; text-align: left;">
                    <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 16px; margin-bottom: 16px; font-size: 13px;">
                        <div style="font-weight: 700; color: #005BAA; margin-bottom: 8px;"><i class="fa-solid fa-shield"></i> Thông tin thẻ Test chuẩn NCB:</div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-family: monospace;">
                            <div>Số thẻ: <strong>9704198526191432198</strong></div>
                            <div>Tên: <strong>NGUYEN VAN A</strong></div>
                            <div>Ngày phát hành: <strong>07/15</strong></div>
                            <div>Mã OTP: <strong>123456</strong></div>
                        </div>
                    </div>

                    <a href="<?= e($successUrl) ?>" class="btn" style="background: #005BAA; color: #FFFFFF; padding: 13px 20px; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 15px; display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%;">
                        <i class="fa-solid fa-lock"></i> Thanh toán bằng thẻ ATM Test
                    </a>
                </div>

            </div>
        </div>

        <!-- Bank Logos Footer -->
        <div style="background: #F8FAFC; border-top: 1px solid #E2E8F0; padding: 16px 24px; text-align: center;">
            <div style="font-size: 12px; color: #64748B; margin-bottom: 10px; font-weight: 600;">HỖ TRỢ THANH TOÁN QUA 40+ NGÂN HÀNG & VÍ ĐIỆN TỬ VIỆT NAM</div>
            <div style="display: flex; justify-content: center; align-items: center; gap: 14px; flex-wrap: wrap; opacity: 0.75; font-size: 12px; font-weight: 700; color: #475569;">
                <span>Vietcombank</span> • <span>MBBank</span> • <span>Techcombank</span> • <span>VietinBank</span> • <span>BIDV</span> • <span>ACB</span> • <span>VPBank</span> • <span>MoMo</span> • <span>ZaloPay</span>
            </div>
        </div>
    </div>
</div>

<script>
function switchSimTab(tab) {
    const qrContent = document.getElementById('tabQrContent');
    const atmContent = document.getElementById('tabAtmContent');
    const qrBtn = document.getElementById('tabQrBtn');
    const atmBtn = document.getElementById('tabAtmBtn');

    if (tab === 'qr') {
        qrContent.style.display = 'flex';
        atmContent.style.display = 'none';
        qrBtn.style.background = '#FFFFFF';
        qrBtn.style.color = '#005BAA';
        qrBtn.style.fontWeight = '700';
        atmBtn.style.background = 'transparent';
        atmBtn.style.color = '#64748B';
        atmBtn.style.fontWeight = '600';
    } else {
        qrContent.style.display = 'none';
        atmContent.style.display = 'block';
        atmBtn.style.background = '#FFFFFF';
        atmBtn.style.color = '#005BAA';
        atmBtn.style.fontWeight = '700';
        qrBtn.style.background = 'transparent';
        qrBtn.style.color = '#64748B';
        qrBtn.style.fontWeight = '600';
    }
}

// Countdown timer simulation
let secondsLeft = 15 * 60;
const timerEl = document.getElementById('simCountdown');
if (timerEl) {
    setInterval(() => {
        if (secondsLeft <= 0) return;
        secondsLeft--;
        const m = String(Math.floor(secondsLeft / 60)).padStart(2, '0');
        const s = String(secondsLeft % 60).padStart(2, '0');
        timerEl.textContent = `${m}:${s}`;
    }, 1000);
}
</script>

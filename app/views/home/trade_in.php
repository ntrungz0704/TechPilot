<section class="container breadcrumb">
    <a href="<?= url('/') ?>">Trang chủ</a> <i class="fa-solid fa-chevron-right"></i>
    <span>Thu cũ đổi mới máy cũ</span>
</section>

<section class="container section" style="padding-top: 32px;">
    <div style="display: grid; gap: 32px;">
        <div>
            <div class="section__head">
                <h2>Thu cũ đổi mới máy cũ</h2>
            </div>

            <p style="font-size: 24px; font-weight: 700; margin-bottom: 14px;">Bán dễ dàng. Lên đời tiết kiệm</p>
            <p style="color: var(--text-secondary); line-height: 1.8; max-width: 780px; margin-bottom: 24px;">
                Liên hệ Zalo Nguyễn Minh Hiếu hoặc Zalo Trần Ngọc Xuân Đình để được tư vấn thu cũ đổi mới LCD, CPU, Mainboard, VGA sau khi kiểm tra thực tế.
            </p>

            <div style="display: flex; flex-wrap: wrap; gap: 12px;">
                <a href="https://zalo.me/84793591111" class="btn btn--light">LIÊN HỆ NGUYỄN MINH HIẾU</a>
                <a href="https://zalo.me/84966002464" class="btn btn--outline">LIÊN HỆ TRẦN NGỌC XUÂN ĐÌNH</a>
            </div>
        </div>
    </div>
</section>

<section class="container section" style="padding-bottom: 40px;">
    <div class="section__head">
        <h2>Ước tính giá thu</h2>
    </div>

    <p style="color: var(--text-secondary); line-height: 1.8; max-width: 860px; margin-bottom: 24px;">
        Chọn nhóm hàng và thông tin sản phẩm để xem khoảng giá tham khảo trước khi chat với chúng tôi.
    </p>

    <div style="display: grid; gap: 24px; max-width: 960px;">
        <div style="display: flex; flex-wrap: wrap; gap: 12px;">
            <?php foreach (['LCD', 'CPU', 'Mainboard', 'VGA'] as $category): ?>
                <button type="button" class="btn btn--outline" style="padding: 10px 18px; min-width: 120px; font-size: 13px; text-transform: none;">
                    <?= e($category) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <div style="display: grid; gap: 16px;">
            <div style="display: grid; gap: 8px;">
                <label style="font-weight: 700; color: var(--text-primary);">Tìm model</label>
                <input type="text" placeholder="Nhập model sản phẩm" style="width: 100%; border: 1px solid var(--border); border-radius: var(--radius-elem); padding: 14px 16px; background: #FFFFFF; color: var(--text-primary);">
            </div>

            <div style="display: grid; gap: 12px;">
                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <button type="button" class="btn btn--light" style="min-width: 160px; text-transform: none;">Ngoại quan đẹp</button>
                    <button type="button" class="btn btn--outline" style="min-width: 160px; text-transform: none;">Ngoại quan xấu</button>
                </div>
                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <button type="button" class="btn btn--outline" style="min-width: 160px; text-transform: none;">Còn hộp</button>
                    <button type="button" class="btn btn--outline" style="min-width: 160px; text-transform: none;">Còn bảo hành hãng</button>
                </div>
            </div>
        </div>

        <button type="button" class="btn btn--block" style="max-width: 260px;">Ước tính giá thu</button>
    </div>
</section>

<section class="container section">
    <div class="section__head">
        <h2>Không có bảng giá cố định - GearVN tư vấn sau kiểm tra</h2>
    </div>

    <p style="color: var(--text-secondary); line-height: 1.8; max-width: 860px; margin-bottom: 24px;">
        Trang này không công bố giá thu trước. Liên hệ qua Zalo Nguyễn Minh Hiếu hoặc Zalo Trần Ngọc Xuân Đình để nhân viên hướng dẫn thông tin cần chuẩn bị.
    </p>

    <div style="display: flex; flex-wrap: wrap; gap: 12px;">
        <a href="https://zalo.me/84793591111" class="btn btn--light">LIÊN HỆ NGUYỄN MINH HIẾU</a>
        <a href="https://zalo.me/84966002464" class="btn btn--light">LIÊN HỆ TRẦN NGỌC XUÂN ĐÌNH</a>
    </div>
</section>

<section class="container section">
    <div class="section__head">
        <h2>4 bước bán hoặc lên đời</h2>
    </div>

    <p style="color: var(--text-secondary); line-height: 1.8; max-width: 860px; margin-bottom: 24px;">
        Giá thu không cố định trước khi kiểm tra. GearVN sẽ tư vấn phương án phù hợp nếu khách muốn lên đời màn hình, VGA, CPU hoặc mainboard.
    </p>

    <div class="product-grid product-grid--4" style="grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 20px;">
        <div class="promo-card">
            <span style="font-size: 28px; font-weight: 800; color: var(--primary);">01</span>
            <h4>Liên hệ</h4>
            <p>Nhắn Zalo Nguyễn Minh Hiếu hoặc Zalo Trần Ngọc Xuân Đình với model, ảnh thực tế và tình trạng sản phẩm.</p>
        </div>
        <div class="promo-card">
            <span style="font-size: 28px; font-weight: 800; color: var(--primary);">02</span>
            <h4>Kiểm tra</h4>
            <p>Kiểm tra tình trạng sản phẩm tại showroom hoặc theo hướng dẫn tư vấn.</p>
        </div>
        <div class="promo-card">
            <span style="font-size: 28px; font-weight: 800; color: var(--primary);">03</span>
            <h4>Báo giá</h4>
            <p>Giá thu được xác định sau khi kỹ thuật kiểm tra và xác nhận điều kiện thực tế.</p>
        </div>
        <div class="promo-card">
            <span style="font-size: 28px; font-weight: 800; color: var(--primary);">04</span>
            <h4>Nhận tiền hoặc bù chênh lệch</h4>
            <p>Khách chọn nhận thanh toán hoặc dùng giá trị thu để nâng cấp sản phẩm.</p>
        </div>
    </div>
</section>

<section class="container section" style="margin-bottom: 60px;">
    <div class="section__head">
        <h2>Vì sao chọn hàng cũ GearVN</h2>
    </div>

    <p style="color: var(--text-secondary); line-height: 1.8; max-width: 860px; margin-bottom: 24px;">
        Minh bạch - kiểm tra - có showroom. Trang này không cam kết giá thu trước khi kiểm tra. Niềm tin nằm ở quy trình và chính sách rõ ràng.
    </p>

    <div class="product-grid product-grid--4" style="grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 20px;">
        <div class="promo-card promo-card--dark">
            <h4>Kiểm tra kỹ thuật</h4>
            <p>Sản phẩm được kiểm tra thực tế trước khi GearVN tư vấn giá thu.</p>
        </div>
        <div class="promo-card promo-card--dark">
            <h4>Ghi rõ tình trạng</h4>
            <p>Model, ngoại hình, lỗi, phụ kiện và bảo hành còn lại đều ảnh hưởng kết quả kiểm tra.</p>
        </div>
        <div class="promo-card promo-card--dark">
            <h4>Showroom kiểm tra</h4>
            <p>Khách có thể đem sản phẩm đến showroom để kỹ thuật kiểm tra nhanh hơn.</p>
        </div>
        <div class="promo-card promo-card--dark">
            <h4>Tư vấn rõ ràng</h4>
            <p>GearVN xác nhận điều kiện tiếp nhận và chương trình thu cũ đổi mới theo từng thời điểm.</p>
        </div>
    </div>
</section>

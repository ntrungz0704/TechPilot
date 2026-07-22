<?php include ROOT_PATH . '/app/views/layouts/header.php'; ?>
<main class="container address-book">
    <div class="address-book__head"><div><h1>Sổ địa chỉ</h1><p>Lưu địa chỉ để đặt hàng nhanh hơn.</p></div><a class="btn" href="<?= url('profile') ?>">Hồ sơ</a></div>
    <?php foreach ($flashes ?? [] as $message): ?><div class="alert alert--<?= e($message['type']) ?>"><?= e($message['message']) ?></div><?php endforeach; ?>
    <div class="address-book__grid">
        <section class="address-card">
            <h2>Thêm địa chỉ</h2>
            <form method="post" action="<?= url('profile/save_address') ?>" class="address-form">
                <?= csrf_field() ?>
                <input name="recipient_name" required placeholder="Tên người nhận"><input name="phone" required placeholder="Số điện thoại">
                <input name="address_line" required placeholder="Số nhà, tên đường"><input name="ward" placeholder="Phường / xã">
                <input name="district" placeholder="Quận / huyện"><input name="province" required placeholder="Tỉnh / thành phố">
                <label><input type="checkbox" name="is_default" value="1"> Đặt làm địa chỉ mặc định</label><button class="btn" type="submit">Lưu địa chỉ</button>
            </form>
        </section>
        <section class="address-list">
            <?php if (empty($addresses)): ?><p>Chưa có địa chỉ nào.</p><?php endif; ?>
            <?php foreach ($addresses as $address): ?><article class="address-card">
                <strong><?= e($address['recipient_name']) ?></strong> · <?= e($address['phone']) ?>
                <?php if ($address['is_default']): ?><span class="badge badge--success">Mặc định</span><?php endif; ?>
                <p><?= e(Address::formatted($address)) ?></p>
                <form method="post" action="<?= url('profile/delete_address') ?>" onsubmit="return confirm('Xóa địa chỉ này?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$address['id'] ?>"><button class="btn btn--sm address-delete" type="submit">Xóa</button></form>
            </article><?php endforeach; ?>
        </section>
    </div>
</main>
<style>
.address-book{padding:32px 0 60px;max-width:1000px}.address-book__head{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px}.address-book__head h1{margin:0 0 6px}.address-book__head p,.address-card p{color:var(--text-secondary)}.address-book__grid{display:grid;grid-template-columns:1fr 1fr;gap:24px}.address-card{background:#fff;border:1px solid var(--border);border-radius:12px;padding:22px}.address-card h2{font-size:18px;margin-top:0}.address-form,.address-list{display:grid;gap:12px}.address-form input{width:100%;padding:11px 13px;border:1px solid var(--border);border-radius:8px}.address-form label input{width:auto}.address-delete{background:#dc2626}@media(max-width:760px){.address-book__grid{grid-template-columns:1fr}}
</style>
<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>

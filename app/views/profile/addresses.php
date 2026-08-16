<?php include ROOT_PATH . '/app/views/layouts/header.php'; ?>

<main class="container section" id="main-content" style="margin-top: 40px; min-height: 60vh;">
    <div style="display: flex; gap: 30px; flex-wrap: wrap;">
        <!-- Left Sidebar Menu -->
        <?php $activeMenu = 'addresses'; include ROOT_PATH . '/app/views/profile/_sidebar.php'; ?>

        <!-- Right Content Area -->
        <div style="flex: 1; min-width: 300px; display: flex; flex-direction: column; gap: 24px;">
            
            <?php if (isset($flashes['success'])): ?>
                <div class="alert alert--success" style="padding: 12px; background-color: #DEF7EC; color: #03543F; border-radius: 8px;">
                    <?= e($flashes['success']) ?>
                </div>
            <?php endif; ?>
            <?php if (isset($flashes['error'])): ?>
                <div class="alert alert--danger" style="padding: 12px; background-color: #FDE8E8; color: #9B1C1C; border-radius: 8px;">
                    <?= e($flashes['error']) ?>
                </div>
            <?php endif; ?>

            <div style="background-color: var(--bg-white); border: 1px solid var(--border); border-radius: 12px; padding: 24px; box-shadow: var(--shadow-card);">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 12px; margin-bottom: 20px;">
                    <h3 style="font-size: 18px; font-weight: 700; color: var(--text-primary); margin: 0;"><i class="fa-solid fa-location-dot" style="margin-right: 8px; color: var(--primary);"></i> Sổ địa chỉ</h3>
                    <button class="btn btn-primary" onclick="showAddModal()" style="padding: 8px 16px; font-size: 14px; font-weight: 600;"><i class="fa-solid fa-plus"></i> Thêm địa chỉ mới</button>
                </div>
                
                <?php if (empty($addresses)): ?>
                    <p style="text-align: center; color: var(--text-secondary); margin: 20px 0;">Bạn chưa có địa chỉ nào được lưu.</p>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <?php foreach ($addresses as $addr): ?>
                            <div style="border: 1px solid var(--border); border-radius: 8px; padding: 16px; display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; <?= $addr['is_default'] ? 'border-color: var(--primary); background-color: #F0F5FF;' : '' ?>">
                                <div>
                                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                        <h4 style="margin: 0; font-size: 16px; font-weight: 700;"><?= e($addr['recipient_name']) ?></h4>
                                        <?php if ($addr['is_default']): ?>
                                            <span style="background-color: var(--primary); color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">Mặc định</span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="color: var(--text-secondary); font-size: 14px; margin-bottom: 4px;"><i class="fa-solid fa-phone" style="width: 16px;"></i> <?= e($addr['phone']) ?></div>
                                    <div style="color: var(--text-secondary); font-size: 14px;"><i class="fa-solid fa-location-arrow" style="width: 16px;"></i> <?= e($addr['address_line']) ?></div>
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 8px; align-items: flex-end;">
                                    <button class="btn" style="padding: 4px 12px; font-size: 13px; background-color: var(--bg-gray); color: var(--text-primary); border: 1px solid var(--border);" onclick='showEditModal(<?= json_encode($addr) ?>)'>Chỉnh sửa</button>
                                    <?php if (!$addr['is_default']): ?>
                                        <form method="post" action="<?= url('profile/set-default-address') ?>" style="margin:0;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= $addr['id'] ?>">
                                            <button type="submit" class="btn" style="padding: 4px 12px; font-size: 13px; background-color: white; color: var(--primary); border: 1px solid var(--primary);">Đặt mặc định</button>
                                        </form>
                                        <form method="post" action="<?= url('profile/delete-address') ?>" style="margin:0;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa địa chỉ này?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= $addr['id'] ?>">
                                            <button type="submit" class="btn" style="padding: 4px 12px; font-size: 13px; background-color: white; color: #EF4444; border: 1px solid #EF4444;">Xóa</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<!-- Modal Thêm/Sửa Địa chỉ -->
<div id="address-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; width: 100%; max-width: 500px; padding: 24px; box-shadow: var(--shadow-card);">
        <h3 id="modal-title" style="margin-top: 0; margin-bottom: 20px; font-size: 18px; font-weight: 700;">Thêm địa chỉ mới</h3>
        <form id="address-form" method="post" action="<?= url('profile/add-address') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="addr-id" value="">
            
            <div style="display: flex; flex-direction: column; gap: 14px;">
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 13.5px; font-weight: 600; color: var(--text-secondary);">Họ và tên người nhận <span style="color:#EF4444">*</span></label>
                    <input type="text" name="recipient_name" id="addr-name" required style="padding: 10px 14px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px; outline: none;">
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 13.5px; font-weight: 600; color: var(--text-secondary);">Số điện thoại <span style="color:#EF4444">*</span></label>
                    <input type="tel" name="phone" id="addr-phone" required placeholder="+84 901 234 567" style="padding: 10px 14px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px; outline: none; font-weight:600;">
                    <div id="addrPhoneError" style="display:none; color: #EF4444; font-size: 12px; margin-top: 2px;"></div>
                    <small style="color: var(--text-secondary); font-size: 11.5px;">Chuẩn +84 (10 hoặc 11 chữ số, ví dụ: +84901234567 hoặc 0901234567)</small>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-size: 13px; font-weight: 600; color: var(--text-secondary);">Tỉnh / Thành phố <span style="color:#EF4444">*</span></label>
                        <select name="province" id="addr-province" required style="padding: 10px; border: 1px solid var(--border); border-radius: 8px; font-size: 13.5px; outline: none; background: white;">
                            <option value="">-- Chọn Tỉnh / TP --</option>
                        </select>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-size: 13px; font-weight: 600; color: var(--text-secondary);">Quận / Huyện <span style="color:#EF4444">*</span></label>
                        <select name="district" id="addr-district" required disabled style="padding: 10px; border: 1px solid var(--border); border-radius: 8px; font-size: 13.5px; outline: none; background: white;">
                            <option value="">-- Chọn Quận / Huyện --</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-size: 13px; font-weight: 600; color: var(--text-secondary);">Phường / Xã <span style="color:#EF4444">*</span></label>
                        <select name="ward" id="addr-ward" required disabled style="padding: 10px; border: 1px solid var(--border); border-radius: 8px; font-size: 13.5px; outline: none; background: white;">
                            <option value="">-- Chọn Phường / Xã --</option>
                        </select>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-size: 13px; font-weight: 600; color: var(--text-secondary);">Số nhà, tên đường <span style="color:#EF4444">*</span></label>
                        <input type="text" name="address_detail" id="addr-detail" required placeholder="Số nhà, tên đường..." style="padding: 10px; border: 1px solid var(--border); border-radius: 8px; font-size: 13.5px; outline: none; background: white;">
                    </div>
                </div>

                <input type="hidden" name="address_line" id="addr-line" value="">
                
                <div style="display: flex; align-items: center; gap: 8px; margin-top: 4px;">
                    <input type="checkbox" name="is_default" id="addr-default" value="1" style="width: 16px; height: 16px; accent-color: var(--primary);">
                    <label for="addr-default" style="font-size: 14px; color: var(--text-primary);">Đặt làm địa chỉ mặc định</label>
                </div>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn" style="background: white; border: 1px solid var(--border); color: var(--text-secondary);" onclick="closeModal()">Hủy</button>
                <button type="submit" class="btn btn-primary" style="padding: 8px 24px;">Lưu địa chỉ</button>
            </div>
        </form>
    </div>
</div>

<script>
    let modalAddrSelector = null;

    document.addEventListener('DOMContentLoaded', function() {
        if (window.TechPilotAddress) {
            modalAddrSelector = window.TechPilotAddress.initSelector({
                province: '#addr-province',
                district: '#addr-district',
                ward: '#addr-ward',
                detail: '#addr-detail',
                fullAddress: '#addr-line'
            });

            const phoneInput = document.getElementById('addr-phone');
            const phoneError = document.getElementById('addrPhoneError');
            window.TechPilotAddress.attachPhoneFormatter(phoneInput, phoneError);
        }
    });

    function showAddModal() {
        document.getElementById('modal-title').innerText = 'Thêm địa chỉ mới';
        document.getElementById('address-form').action = '<?= url("profile/add-address") ?>';
        document.getElementById('addr-id').value = '';
        document.getElementById('addr-name').value = '';
        document.getElementById('addr-phone').value = '';
        document.getElementById('addr-line').value = '';
        document.getElementById('addr-detail').value = '';
        document.getElementById('addr-default').checked = false;
        if (modalAddrSelector) {
            modalAddrSelector.setValues('', '', '', '');
        }
        document.getElementById('address-modal').style.display = 'flex';
    }

    function showEditModal(addr) {
        document.getElementById('modal-title').innerText = 'Cập nhật địa chỉ';
        document.getElementById('address-form').action = '<?= url("profile/edit-address") ?>';
        document.getElementById('addr-id').value = addr.id;
        document.getElementById('addr-name').value = addr.recipient_name;
        document.getElementById('addr-phone').value = addr.phone;
        document.getElementById('addr-line').value = addr.address_line;
        document.getElementById('addr-default').checked = addr.is_default == 1;
        
        if (modalAddrSelector && addr.address_line) {
            modalAddrSelector.prefill(addr.address_line);
        }
        document.getElementById('address-modal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('address-modal').style.display = 'none';
    }
</script>

<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>

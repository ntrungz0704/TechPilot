<div class="card" style="max-width: 800px; margin: 0 auto 30px;">
    <h3 class="card-title">Chỉnh sửa sản phẩm</h3>
    
    <form method="post" action="<?= url('admin/products/update/' . $product['id']) ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="current_image" value="<?= e($product['image']) ?>">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="name">Tên sản phẩm <span style="color: red;">*</span></label>
                <input type="text" name="name" id="name" class="form-control" value="<?= e($product['name']) ?>" required>
            </div>

            <div class="form-group">
                <label for="slug">Slug (Tự sinh nếu để trống)</label>
                <input type="text" name="slug" id="slug" class="form-control" value="<?= e($product['slug']) ?>">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="category_id">Danh mục sản phẩm <span style="color: red;">*</span></label>
                <select name="category_id" id="category_id" class="form-control" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>" <?= $product['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="brand_id">Thương hiệu <span style="color: red;">*</span></label>
                <select name="brand_id" id="brand_id" class="form-control" required>
                    <?php foreach ($brands as $b): ?>
                        <option value="<?= (int)$b['id'] ?>" <?= $product['brand_id'] == $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="price">Giá bán gốc (đ) <span style="color: red;">*</span></label>
                <input type="number" name="price" id="price" class="form-control" value="<?= (float)$product['price'] ?>" min="0" required>
            </div>

            <div class="form-group">
                <label for="sale_price">Giá khuyến mãi (đ)</label>
                <input type="number" name="sale_price" id="sale_price" class="form-control" value="<?= $product['sale_price'] !== null ? (float)$product['sale_price'] : '' ?>" min="0">
            </div>

            <div class="form-group">
                <label for="stock">Số lượng tồn kho <span style="color: red;">*</span></label>
                <input type="number" name="stock" id="stock" class="form-control" value="<?= (int)$product['stock'] ?>" min="0" required>
            </div>
        </div>

        <div class="form-group" style="border: 1px dashed var(--border); padding: 15px; border-radius: 8px; background-color: #F9FAFB;">
            <label for="image">Ảnh sản phẩm</label>
            
            <?php if (!empty($product['image'])): ?>
                <div style="margin-bottom: 15px;">
                    <span style="font-size: 12px; color: var(--text-secondary); display: block; margin-bottom: 5px;">Ảnh hiện tại:</span>
                    <img src="<?= url('assets/images/' . e($product['image'])) ?>" alt="<?= e($product['name']) ?>" style="height: 80px; width: 80px; object-fit: contain; border: 1px solid var(--border); padding: 4px; border-radius: 4px; background: #FFF;">
                </div>
            <?php endif; ?>

            <input type="file" name="image" id="image" class="form-control" style="border: none; padding: 5px 0;">
            <small style="color: var(--text-secondary); display: block; margin-top: 5px;">Chọn ảnh để thay thế ảnh cũ.</small>
        </div>

        <div class="form-group">
            <label>Thông số kỹ thuật (Specs Editor)</label>
            
            <!-- Specs Interactive Mode -->
            <div id="specsBuilderContainer" style="border: 1px solid var(--border); border-radius: var(--radius-elem); padding: 16px; background-color: #F8FAFC;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <span style="font-size: 12.5px; color: var(--text-secondary); font-weight: 600;">Danh sách thuộc tính:</span>
                    <button type="button" class="btn btn--outline btn--sm" id="btnQuickTemplate" style="font-size: 11.5px; padding: 6px 12px;"><i class="fa-solid fa-wand-magic-sparkles"></i> Áp dụng mẫu danh mục</button>
                </div>
                <div id="specsRowsContainer" style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px;">
                    <!-- Rows will be injected here dynamically -->
                </div>
                <button type="button" class="btn btn--outline btn--sm" id="btnAddSpecRow" style="font-size: 12px; width: 100%; border-style: dashed; justify-content: center;"><i class="fa-solid fa-plus"></i> Thêm thuộc tính khác</button>
            </div>

            <!-- Hidden/Raw Textarea (Fallback) -->
            <div id="rawSpecsWrapper" style="display: none; margin-top: 10px;">
                <textarea name="specs" id="specs" class="form-control" rows="4" placeholder='{"Key": "Value"}'><?= e($product['specs']) ?></textarea>
                <small style="color: var(--text-secondary); display: block; margin-top: 4px;">Cú pháp JSON thô đang đồng bộ.</small>
            </div>
            
            <div style="margin-top: 8px; display: flex; justify-content: space-between; align-items: center;">
                <small id="specsJsonStatus" style="color: var(--text-secondary); font-weight: 500;"><i class="fa-solid fa-circle-check" style="color: var(--primary);"></i> JSON đã đồng bộ thành công.</small>
                <button type="button" id="btnToggleSpecsMode" style="background: none; border: none; color: var(--primary); font-size: 11.5px; font-weight: 600; cursor: pointer; text-decoration: underline;">Chỉnh sửa JSON thô</button>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const categorySelect = document.getElementById('category_id');
                const specsTextarea = document.getElementById('specs');
                const rowsContainer = document.getElementById('specsRowsContainer');
                const addRowBtn = document.getElementById('btnAddSpecRow');
                const quickTemplateBtn = document.getElementById('btnQuickTemplate');
                const toggleModeBtn = document.getElementById('btnToggleSpecsMode');
                const rawSpecsWrapper = document.getElementById('rawSpecsWrapper');
                const jsonStatus = document.getElementById('specsJsonStatus');

                let isBuilderMode = true;

                // Các bộ thuộc tính mẫu chuẩn theo loại sản phẩm
                const templates = {
                    laptop: ['CPU', 'RAM', 'SSD', 'VGA', 'Màn hình'],
                    pc: ['CPU', 'Mainboard', 'RAM', 'SSD', 'VGA', 'Nguồn'],
                    monitor: ['Kích thước', 'Độ phân giải', 'Tần số quét', 'Tấm nền'],
                    cpu: ['Nhân', 'Luồng', 'Xung nhịp', 'Socket'],
                    mainboard: ['Chipset', 'Socket', 'RAM hỗ trợ', 'Kích thước'],
                    ram: ['Loại RAM', 'Dung lượng', 'Tốc độ', 'Độ trễ'],
                    gear: ['Kết nối', 'Loại phím / Mắt đọc', 'Đèn nền', 'Trọng lượng']
                };

                // Nhận diện loại sản phẩm dựa trên tùy chọn select
                function getCategoryType() {
                    const text = categorySelect.options[categorySelect.selectedIndex]?.text.toLowerCase() || '';
                    if (text.includes('laptop')) return 'laptop';
                    if (text.includes('pc') || text.includes('máy tính bộ')) return 'pc';
                    if (text.includes('màn hình')) return 'monitor';
                    if (text.includes('cpu') || text.includes('vi xử lý')) return 'cpu';
                    if (text.includes('main') || text.includes('bo mạch')) return 'mainboard';
                    if (text.includes('ram') || text.includes('bộ nhớ')) return 'ram';
                    if (text.includes('gear') || text.includes('phím') || text.includes('chuột') || text.includes('tai nghe')) return 'gear';
                    return null;
                }

                // Tạo một dòng thuộc tính mới
                function createRow(key = '', value = '') {
                    const row = document.createElement('div');
                    row.className = 'spec-row';
                    row.style.display = 'grid';
                    row.style.gridTemplateColumns = '0.4fr 0.6fr auto';
                    row.style.gap = '8px';
                    row.style.alignItems = 'center';

                    row.innerHTML = `
                        <input type="text" class="form-control spec-key" placeholder="Thuộc tính (ví dụ: CPU)" value="${key}" style="padding: 8px 12px; font-size: 13px;">
                        <input type="text" class="form-control spec-value" placeholder="Giá trị (ví dụ: i7 13700H)" value="${value}" style="padding: 8px 12px; font-size: 13px;">
                        <button type="button" class="btn btn--danger btn--sm btn-delete-row" style="padding: 8px 10px; box-shadow: none;"><i class="fa-solid fa-trash-can"></i></button>
                    `;

                    // Lắng nghe sự kiện để đồng bộ hóa JSON
                    row.querySelector('.spec-key').addEventListener('input', syncToJSON);
                    row.querySelector('.spec-value').addEventListener('input', syncToJSON);
                    row.querySelector('.btn-delete-row').addEventListener('click', function() {
                        row.remove();
                        syncToJSON();
                    });

                    rowsContainer.appendChild(row);
                }

                // Đồng bộ từ giao diện Key-Value sang ô Textarea JSON
                function syncToJSON() {
                    if (!isBuilderMode) return;
                    const data = {};
                    rowsContainer.querySelectorAll('.spec-row').forEach(row => {
                        const key = row.querySelector('.spec-key').value.trim();
                        const val = row.querySelector('.spec-value').value.trim();
                        if (key !== '') {
                            data[key] = val;
                        }
                    });
                    specsTextarea.value = JSON.stringify(data, null, 4);
                    updateStatus(true);
                }

                // Đồng bộ ngược từ ô Textarea JSON sang giao diện Key-Value
                function syncFromJSON() {
                    rowsContainer.innerHTML = '';
                    try {
                        const val = specsTextarea.value.trim();
                        if (val === '' || val === 'null' || val === '{}') {
                            createRow('', '');
                            return;
                        }
                        const data = JSON.parse(val);
                        let count = 0;
                        Object.keys(data).forEach(key => {
                            createRow(key, data[key]);
                            count++;
                        });
                        if (count === 0) createRow('', '');
                        updateStatus(true);
                    } catch (e) {
                        createRow('', '');
                        updateStatus(false);
                    }
                }

                function updateStatus(isValid) {
                    if (isValid) {
                        jsonStatus.innerHTML = '<i class="fa-solid fa-circle-check" style="color: #10B981;"></i> Cấu trúc JSON hợp lệ.';
                        jsonStatus.style.color = '#15803D';
                    } else {
                        jsonStatus.innerHTML = '<i class="fa-solid fa-circle-xmark" style="color: #EF4444;"></i> Lỗi cú pháp JSON thô!';
                        jsonStatus.style.color = '#B91C1C';
                    }
                }

                // Nạp mẫu thuộc tính nhanh
                function applyQuickTemplate() {
                    const type = getCategoryType();
                    if (!type) {
                        alert('Vui lòng chọn Danh mục sản phẩm trước để áp dụng mẫu phù hợp.');
                        return;
                    }
                    const keys = templates[type];
                    rowsContainer.innerHTML = '';
                    keys.forEach(k => createRow(k, ''));
                    syncToJSON();
                }

                // Thêm dòng mới
                addRowBtn.addEventListener('click', () => {
                    createRow('', '');
                });

                // Bấm nút template nhanh
                quickTemplateBtn.addEventListener('click', applyQuickTemplate);

                // Tự động nạp template khi thay đổi danh mục
                categorySelect.addEventListener('change', () => {
                    if (rowsContainer.querySelectorAll('.spec-row').length === 0) {
                        applyQuickTemplate();
                    }
                });

                // Toggle chế độ Builder / Raw JSON
                toggleModeBtn.addEventListener('click', function() {
                    isBuilderMode = !isBuilderMode;
                    if (isBuilderMode) {
                        rawSpecsWrapper.style.display = 'none';
                        document.getElementById('specsBuilderContainer').style.display = 'block';
                        toggleModeBtn.innerText = 'Chỉnh sửa JSON thô';
                        syncFromJSON();
                    } else {
                        rawSpecsWrapper.style.display = 'block';
                        document.getElementById('specsBuilderContainer').style.display = 'none';
                        toggleModeBtn.innerText = 'Quay lại Trình biên tập trực quan';
                    }
                });

                // Validate JSON trước khi submit form
                specsTextarea.closest('form').addEventListener('submit', function(e) {
                    if (!isBuilderMode) {
                        try {
                            const val = specsTextarea.value.trim();
                            if (val !== '') {
                                JSON.parse(val);
                            }
                        } catch (err) {
                            e.preventDefault();
                            alert('Lỗi: Định dạng thông số kỹ thuật (JSON) của bạn không hợp lệ. Vui lòng kiểm tra lại trước khi lưu.');
                        }
                    }
                });

                // Khởi động: Nạp sẵn thông số từ JSON hiện tại
                syncFromJSON();
            });
        </script>

        <div class="form-group">
            <label for="description">Mô tả chi tiết sản phẩm</label>
            <textarea name="description" id="description" class="form-control" rows="6"><?= e($product['description']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="status">Trạng thái hiển thị</label>
            <select name="status" id="status" class="form-control">
                <option value="active" <?= $product['status'] === 'active' ? 'selected' : '' ?>>Hiển thị bán hàng (Active)</option>
                <option value="inactive" <?= $product['status'] === 'inactive' ? 'selected' : '' ?>>Tạm ẩn bán hàng (Inactive)</option>
            </select>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 25px;">
            <button type="submit" class="btn"><i class="fa-solid fa-floppy-disk"></i> Cập nhật</button>
            <a href="<?= url('admin/products') ?>" class="btn btn--secondary">Quay lại</a>
        </div>
    </form>
</div>

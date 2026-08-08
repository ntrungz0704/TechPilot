<div class="card" style="margin-bottom: 30px;">
    <h3 class="card-title">Chỉnh sửa sản phẩm</h3>
    
    <form method="post" action="<?= url('admin/products/update/' . $product['id']) ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="current_image" value="<?= e($product['image']) ?>">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <label for="name" style="margin-bottom: 0;">Tên sản phẩm <span style="color: red;">*</span></label>
                    <button type="button" class="btn btn--outline btn--sm" id="btnOpenAiModal" style="border-color: #8B5CF6; color: #7C3AED; background: #F5F3FF; font-weight: 600; padding: 4px 10px; font-size: 12px;" title="Tự động bổ sung mô tả, thông số kỹ thuật và SEO bằng AI">
                        <i class="fa-solid fa-wand-magic-sparkles"></i> Tự động điền bằng AI
                    </button>
                </div>
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

        <div class="form-group" style="border: 1px dashed var(--border); padding: 18px; border-radius: 12px; background-color: var(--bg-body);">
            <label for="image">Ảnh sản phẩm</label>
            
            <?php if (!empty($product['image'])): ?>
                <div style="margin-bottom: 15px;">
                    <span style="font-size: 12px; color: var(--text-secondary); display: block; margin-bottom: 5px;">Ảnh hiện tại:</span>
                    <img src="<?= e(productImageUrl($product['image'] ?? '', $product['name'] ?? '')) ?>" alt="<?= e($product['name']) ?>" style="height: 80px; width: 80px; object-fit: contain; border: 1px solid var(--border); padding: 4px; border-radius: 4px; background: var(--bg-body);">
                </div>
            <?php endif; ?>

            <input type="file" name="image" id="image" class="form-control">
            <small style="color: var(--text-secondary); display: block; margin-top: 5px;">Chọn ảnh để thay thế ảnh cũ.</small>
        </div>

        <div class="form-group">
            <label>Thông số kỹ thuật (Specs Editor)</label>
            
            <!-- Specs Interactive Mode -->
            <div id="specsBuilderContainer" style="border: 1px solid var(--border); border-radius: var(--radius-elem); padding: 16px; background-color: var(--bg-body);">
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
            <label for="status">Trạng thái vòng đời sản phẩm (Lifecycle Status)</label>
            <select name="status" id="status" class="form-control">
                <?php foreach ($productStatuses as $value => $meta): ?>
                    <option value="<?= e($value) ?>" <?= $product['status'] === $value ? 'selected' : '' ?>><?= e($meta['form_label']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 25px;">
            <button type="submit" class="btn"><i class="fa-solid fa-floppy-disk"></i> Cập nhật</button>
            <a href="<?= url('admin/products') ?>" class="btn btn--secondary">Quay lại</a>
        </div>
    </form>
</div>

<!-- MODAL AI ASSISTANT -->
<div id="aiAssistantModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 9999; justify-content: center; align-items: center; padding: 20px;">
    <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 720px; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); display: flex; flex-direction: column;">
        
        <!-- Header -->
        <div style="padding: 18px 24px; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, #4F46E5, #7C3AED); color: #FFFFFF; border-top-left-radius: 16px; border-top-right-radius: 16px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 38px; height: 38px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                </div>
                <div>
                    <h3 style="font-size: 16.5px; font-weight: 700; margin: 0; color: #FFF;">Trợ Lý AI Bổ Sung Dữ Liệu Sản Phẩm TechPilot v2</h3>
                    <p style="font-size: 12px; margin: 2px 0 0 0; opacity: 0.9;">Validate Model · 8 Section Description · AI Editor · History & Cache</p>
                </div>
            </div>
            <button type="button" id="btnCloseAiModal" style="background: none; border: none; color: #FFF; font-size: 20px; cursor: pointer; padding: 4px;"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <!-- Body -->
        <div style="padding: 20px 24px; flex: 1; overflow-y: auto;">
            <!-- Modal Navigation Tabs -->
            <div style="display: flex; gap: 8px; margin-bottom: 16px; border-bottom: 1px solid #E2E8F0; padding-bottom: 8px;">
                <button type="button" id="tabBtnGenerate" class="btn btn--sm" style="background: #4F46E5; color: #FFF; font-weight: 600;"><i class="fa-solid fa-bolt"></i> Sinh Dữ Liệu AI</button>
                <button type="button" id="tabBtnRewrite" class="btn btn--outline btn--sm" style="font-weight: 600;"><i class="fa-solid fa-pen-nib"></i> AI Tone Editor</button>
                <button type="button" id="tabBtnHistory" class="btn btn--outline btn--sm" style="font-weight: 600;"><i class="fa-solid fa-clock-rotate-left"></i> Lịch Sử & Cache</button>
            </div>

            <!-- Tab 1: Generate Container -->
            <div id="aiTabGenerate">
                <div style="margin-bottom: 16px;">
                    <label style="font-size: 13px; font-weight: 700; color: #1E293B; display: block; margin-bottom: 6px;">Nhập tên sản phẩm, Model hoặc từ khóa (Ví dụ: lap, Laptop Gaming, RTX 4060):</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" id="aiInputQuery" class="form-control" value="<?= e($product['name']) ?>" placeholder="Nhập tên sản phẩm, Model hoặc từ khóa..." style="flex: 1; padding: 10px 14px; font-size: 13.5px;">
                        <button type="button" id="btnRunAiGenerate" class="btn" style="background: linear-gradient(135deg, #4F46E5, #7C3AED); border: none; padding: 10px 18px; font-weight: 600; white-space: nowrap;">
                            <i class="fa-solid fa-magnifying-glass"></i> Phân Tích AI
                        </button>
                    </div>
                </div>

                <!-- Validation Error Alert -->
                <div id="aiValidationAlert" style="display: none; background: #FEF2F2; border: 1px solid #FCA5A5; color: #991B1B; padding: 12px 16px; border-radius: 8px; font-size: 13px; margin-bottom: 16px;">
                    <i class="fa-solid fa-triangle-exclamation"></i> <span id="aiValidationMsg">Không tìm thấy model hợp lệ.</span>
                </div>

                <!-- Loading State -->
                <div id="aiLoadingState" style="display: none; text-align: center; padding: 30px 20px; background: #F8FAFC; border-radius: 12px; border: 1px dashed #CBD5E1;">
                    <div style="font-size: 30px; color: #4F46E5; margin-bottom: 10px;" class="fa-spin-hover">
                        <i class="fa-solid fa-circle-notch fa-spin"></i>
                    </div>
                    <h4 style="font-size: 14.5px; font-weight: 700; color: #1E293B; margin-bottom: 4px;">AI Engine đang phân tích thông số kỹ thuật...</h4>
                    <p style="font-size: 12px; color: #64748B; margin: 0;">Truy vấn Nguồn Hãng (ASUS/Dell/MSI) & Chuẩn hóa đơn vị đo lường</p>
                </div>

                <!-- Result Preview Card -->
                <div id="aiResultContainer" style="display: none; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px;">
                    <!-- Metadata Header Badges -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px solid #E2E8F0; padding-bottom: 10px; flex-wrap: wrap; gap: 8px;">
                        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                            <span class="badge" style="background: #E0F2FE; color: #0369A1; font-weight: 700; font-size: 11px;" id="aiSourceBadge"><i class="fa-solid fa-building-columns"></i> Source</span>
                            <span class="badge" style="background: #D1FAE5; color: #065F46; font-weight: 700; font-size: 11px;" id="aiConfidenceBadge"><i class="fa-solid fa-bullseye"></i> Confidence: 98%</span>
                            <span class="badge" style="background: #F3E8FF; color: #6B21A8; font-weight: 600; font-size: 11px;" id="aiProviderBadge"><i class="fa-solid fa-robot"></i> AI Engine</span>
                        </div>
                        <button type="button" id="btnForceRefreshAi" class="btn btn--outline btn--sm" style="font-size: 11.5px;" title="Tải lại từ AI loại bỏ Cache"><i class="fa-solid fa-rotate-right"></i> Refresh Data</button>
                    </div>

                    <!-- Warning if manual review needed -->
                    <div id="aiManualReviewWarning" style="display: none; background: #FFFBEB; border: 1px solid #FCD34D; color: #92400E; padding: 8px 12px; border-radius: 6px; margin-bottom: 12px; font-size: 12px;">
                        <i class="fa-solid fa-triangle-exclamation"></i> <strong>Cảnh báo:</strong> Điểm tin cậy < 80%. Admin cần kiểm tra thủ công thông số trước khi áp dụng.
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px; background: #FFF; padding: 12px; border-radius: 8px; border: 1px solid #E2E8F0;">
                        <div>
                            <small style="color: #64748B; font-weight: 600; display: block; font-size: 11px;">Tên sản phẩm đề xuất:</small>
                            <strong style="font-size: 13px; color: #0F172A;" id="aiResName">-</strong>
                        </div>
                        <div>
                            <small style="color: #64748B; font-weight: 600; display: block; font-size: 11px;">Slug chuẩn SEO:</small>
                            <code style="font-size: 11.5px; color: #2563EB;" id="aiResSlug">-</code>
                        </div>
                        <div>
                            <small style="color: #64748B; font-weight: 600; display: block; font-size: 11px;">Danh mục phù hợp:</small>
                            <span style="font-size: 12px; font-weight: 600; color: #059669;" id="aiResCategory">-</span>
                        </div>
                        <div>
                            <small style="color: #64748B; font-weight: 600; display: block; font-size: 11px;">Thương hiệu:</small>
                            <span style="font-size: 12px; font-weight: 600; color: #D97706;" id="aiResBrand">-</span>
                        </div>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <small style="color: #64748B; font-weight: 600; display: block; font-size: 11px; margin-bottom: 4px;">Mô tả sản phẩm chuẩn 8 Section (HTML Preview):</small>
                        <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 8px; padding: 12px; font-size: 12px; color: #334155; max-height: 140px; overflow-y: auto;" id="aiResDesc">-</div>
                    </div>

                    <div>
                        <small style="color: #64748B; font-weight: 600; display: block; font-size: 11px; margin-bottom: 4px;">Thông số kỹ thuật chuẩn hóa (Specs JSON):</small>
                        <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 8px; padding: 10px; font-size: 11.5px;" id="aiResSpecs">-</div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: AI Tone Editor Container -->
            <div id="aiTabRewrite" style="display: none;">
                <div style="margin-bottom: 14px;">
                    <label style="font-size: 13px; font-weight: 700; color: #1E293B; display: block; margin-bottom: 6px;">Chọn Văn phong & Phong cách viết (AI Style Adjuster):</label>
                    <div style="display: flex; gap: 10px;">
                        <select id="aiStyleSelect" class="form-control" style="flex: 1;">
                            <option value="gearvn">🎮 Phong cách GearVN (Game thủ & Đam mê phần cứng)</option>
                            <option value="phongvu">🏢 Phong cách Phong Vũ (Chuyên nghiệp & Tin cậy)</option>
                            <option value="seo">🚀 Tối ưu SEO từ khóa công nghệ E-commerce</option>
                            <option value="gaming">⚡ Văn phong Chuyên Game (Cực bốc & Mạnh mẽ)</option>
                            <option value="office">💼 Văn phong Doanh Nhân & Văn Phòng (Trang nhã)</option>
                            <option value="premium">👑 Văn phong Cao Cấp & Sang Trọng (Flagship)</option>
                            <option value="short">📐 Súc tích & Ngắn gọn</option>
                            <option value="detailed">📚 Chi tiết & Phân tích chuyên sâu</option>
                        </select>
                        <button type="button" id="btnExecuteRewrite" class="btn" style="background: #4F46E5; border: none; font-weight: 600; white-space: nowrap;"><i class="fa-solid fa-pen-nib"></i> Viết Lại Văn Phong</button>
                    </div>
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 600; color: #64748B; display: block; margin-bottom: 4px;">Nội dung viết lại (Preview):</label>
                    <textarea id="aiRewriteTextarea" class="form-control" rows="8" style="font-size: 12.5px; font-family: monospace;" placeholder="Nội dung đã điều chỉnh văn phong sẽ xuất hiện ở đây..."></textarea>
                </div>
            </div>

            <!-- Tab 3: History Container -->
            <div id="aiTabHistory" style="display: none;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <span style="font-size: 12px; font-weight: 700; color: #475569;">Lịch sử 20 lượt sinh AI gần nhất (Database Cache):</span>
                    <button type="button" id="btnRefreshHistory" class="btn btn--outline btn--sm" style="font-size: 11px;"><i class="fa-solid fa-rotate"></i> Làm mới danh sách</button>
                </div>
                <div id="aiHistoryList" style="max-height: 240px; overflow-y: auto; background: #FFF; border: 1px solid #E2E8F0; border-radius: 8px; padding: 8px;">
                    <div style="text-align: center; color: #94A3B8; padding: 20px; font-size: 12px;">Đang tải lịch sử...</div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div style="padding: 14px 24px; border-top: 1px solid #E2E8F0; background: #F8FAFC; border-bottom-left-radius: 16px; border-bottom-right-radius: 16px; display: flex; justify-content: flex-end; gap: 10px;">
            <button type="button" id="btnCancelAiModal" class="btn btn--secondary">Đóng</button>
            <button type="button" id="btnApplyAiToForm" class="btn" style="background: #10B981; border: none; font-weight: 600;" disabled><i class="fa-solid fa-check"></i> Áp Dụng Vào Form</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('aiAssistantModal');
    const openBtn = document.getElementById('btnOpenAiModal');
    const closeBtn = document.getElementById('btnCloseAiModal');
    const cancelBtn = document.getElementById('btnCancelAiModal');
    const runBtn = document.getElementById('btnRunAiGenerate');
    const refreshBtn = document.getElementById('btnForceRefreshAi');
    const applyBtn = document.getElementById('btnApplyAiToForm');
    const inputQuery = document.getElementById('aiInputQuery');
    const loadingState = document.getElementById('aiLoadingState');
    const resultContainer = document.getElementById('aiResultContainer');
    const validationAlert = document.getElementById('aiValidationAlert');
    const validationMsg = document.getElementById('aiValidationMsg');

    // Tabs
    const tabGenerate = document.getElementById('aiTabGenerate');
    const tabRewrite = document.getElementById('aiTabRewrite');
    const tabHistory = document.getElementById('aiTabHistory');
    const btnTabGenerate = document.getElementById('tabBtnGenerate');
    const btnTabRewrite = document.getElementById('tabBtnRewrite');
    const btnTabHistory = document.getElementById('tabBtnHistory');

    let currentAiData = null;

    function switchTab(target) {
        tabGenerate.style.display = target === 'generate' ? 'block' : 'none';
        tabRewrite.style.display = target === 'rewrite' ? 'block' : 'none';
        tabHistory.style.display = target === 'history' ? 'block' : 'none';

        btnTabGenerate.style.background = target === 'generate' ? '#4F46E5' : '#FFF';
        btnTabGenerate.style.color = target === 'generate' ? '#FFF' : '#334155';
        btnTabRewrite.style.background = target === 'rewrite' ? '#4F46E5' : '#FFF';
        btnTabRewrite.style.color = target === 'rewrite' ? '#FFF' : '#334155';
        btnTabHistory.style.background = target === 'history' ? '#4F46E5' : '#FFF';
        btnTabHistory.style.color = target === 'history' ? '#FFF' : '#334155';

        if (target === 'history') {
            loadHistory();
        }
    }

    btnTabGenerate.addEventListener('click', () => switchTab('generate'));
    btnTabRewrite.addEventListener('click', () => switchTab('rewrite'));
    btnTabHistory.addEventListener('click', () => switchTab('history'));

    if (openBtn && modal) {
        openBtn.addEventListener('click', function() {
            const currentName = document.getElementById('name').value.trim();
            if (currentName !== '') {
                inputQuery.value = currentName;
            }
            modal.style.display = 'flex';
            switchTab('generate');
        });

        const hideModal = () => { modal.style.display = 'none'; };
        closeBtn.addEventListener('click', hideModal);
        cancelBtn.addEventListener('click', hideModal);

        const executeAiGenerate = (forceRefresh = false) => {
            const q = inputQuery.value.trim();
            if (!q) {
                validationAlert.style.display = 'block';
                validationMsg.innerText = 'Vui lòng nhập Tên Model, SKU hoặc Link website chính hãng.';
                return;
            }

            validationAlert.style.display = 'none';
            loadingState.style.display = 'block';
            resultContainer.style.display = 'none';
            applyBtn.disabled = true;

            const bodyParams = new URLSearchParams();
            bodyParams.append('product_name', q);
            bodyParams.append('_csrf', '<?= $_SESSION["csrf_token"] ?? "" ?>');
            if (forceRefresh) {
                bodyParams.append('force_refresh', '1');
            }

            fetch('<?= url("admin/products/ai-assistant") ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-Token': '<?= $_SESSION["csrf_token"] ?? "" ?>'
                },
                body: bodyParams.toString()
            })
            .then(res => res.json())
            .then(res => {
                loadingState.style.display = 'none';
                if (res.success && res.data) {
                    currentAiData = res.data;
                    renderAiResult(res.data);
                    resultContainer.style.display = 'block';
                    applyBtn.disabled = false;
                } else {
                    validationAlert.style.display = 'block';
                    validationMsg.innerText = res.message || res.error || 'Không thể phân tích model sản phẩm.';
                }
            })
            .catch(err => {
                loadingState.style.display = 'none';
                validationAlert.style.display = 'block';
                validationMsg.innerText = 'Lỗi kết nối tới hệ thống AI Assistant.';
            });
        };

        function renderAiResult(data) {
            let primaryUrl = (data.source_urls && data.source_urls.length > 0) ? data.source_urls[0] : '';
            let sourceHtml = '<i class="fa-solid fa-link"></i> Nguồn: ';
            if (primaryUrl) {
                sourceHtml += `<a href="${primaryUrl}" target="_blank" style="color:#0369A1; text-decoration:underline;">${data.source_name || 'Link nguồn'}</a>`;
            } else {
                sourceHtml += (data.source_name || 'Hãng sản xuất');
            }
            document.getElementById('aiSourceBadge').innerHTML = sourceHtml;

            let score = data.confidence_score || 0;
            let scoreBg = '#D1FAE5';
            let scoreColor = '#065F46';
            if (score < 50) {
                scoreBg = '#FEE2E2';
                scoreColor = '#991B1B';
            } else if (score < 80) {
                scoreBg = '#FEF3C7';
                scoreColor = '#92400E';
            }
            document.getElementById('aiConfidenceBadge').innerHTML = '<i class="fa-solid fa-bullseye"></i> Confidence: ' + score + '% (Thực tế)';
            document.getElementById('aiConfidenceBadge').style.background = scoreBg;
            document.getElementById('aiConfidenceBadge').style.color = scoreColor;

            document.getElementById('aiProviderBadge').innerHTML = '<i class="fa-solid fa-robot"></i> ' + (data.provider || 'TSIE Engine') + (data.is_cached ? ' (Cached)' : '');
            
            const warningBox = document.getElementById('aiManualReviewWarning');
            if (data.needs_manual_review || score < 50) {
                warningBox.style.display = 'block';
                warningBox.style.background = '#FEF2F2';
                warningBox.style.borderColor = '#FCA5A5';
                warningBox.style.color = '#991B1B';
                warningBox.innerHTML = `
                    <div style="margin-bottom: 6px;"><i class="fa-solid fa-triangle-exclamation"></i> <strong>Cảnh báo TSIE (Confidence ${score}%):</strong> Thiếu thông số [${missingFieldsStr(data.missing_fields)}]. Cần kiểm tra thủ công trước khi áp dụng.</div>
                    <label style="font-size: 12px; font-weight: 700; color: #991B1B; cursor: pointer;">
                        <input type="checkbox" id="chkConfirmLowConfidence" style="margin-right: 6px;"> Tôi đã kiểm tra thủ công thông số kỹ thuật trước khi áp dụng.
                    </label>
                `;
                applyBtn.disabled = true;
                setTimeout(() => {
                    const chk = document.getElementById('chkConfirmLowConfidence');
                    if (chk) {
                        chk.addEventListener('change', function() {
                            applyBtn.disabled = !this.checked;
                        });
                    }
                }, 100);
            } else {
                warningBox.style.display = 'none';
                applyBtn.disabled = false;
            }

            document.getElementById('aiResName').innerText = data.name || '';
            document.getElementById('aiResSlug').innerText = data.slug || '';
            document.getElementById('aiResCategory').innerText = data.proposed_category || 'N/A';
            document.getElementById('aiResBrand').innerText = data.proposed_brand || 'N/A';
            document.getElementById('aiResDesc').innerHTML = data.description || '';

            let specsHtml = '<table style="width:100%; border-collapse:collapse;">';
            if (data.specs && typeof data.specs === 'object') {
                Object.keys(data.specs).forEach(k => {
                    specsHtml += `<tr style="border-bottom:1px solid #EDF2F7;"><td style="padding:4px 8px; font-weight:600; width:35%; color:#334155;">${k}</td><td style="padding:4px 8px; color:#475569;">${data.specs[k]}</td></tr>`;
                });
            }
            specsHtml += '</table>';
            document.getElementById('aiResSpecs').innerHTML = specsHtml;

            document.getElementById('aiRewriteTextarea').value = data.description || '';
        }

        function missingFieldsStr(arr) {
            return (arr && arr.length > 0) ? arr.join(', ') : 'Nguồn chưa xác minh';
        }

        runBtn.addEventListener('click', () => executeAiGenerate(false));
        refreshBtn.addEventListener('click', () => executeAiGenerate(true));

        // Rewrite Tone Action
        document.getElementById('btnExecuteRewrite').addEventListener('click', function() {
            const content = document.getElementById('aiRewriteTextarea').value;
            const style = document.getElementById('aiStyleSelect').value;
            if (!content) return;

            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang viết lại...';

            fetch('<?= url("admin/products/ai-assistant/rewrite") ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-Token': '<?= $_SESSION["csrf_token"] ?? "" ?>'
                },
                body: 'content=' + encodeURIComponent(content) + '&style=' + encodeURIComponent(style) + '&_csrf=' + encodeURIComponent('<?= $_SESSION["csrf_token"] ?? "" ?>')
            })
            .then(res => res.json())
            .then(res => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-pen-nib"></i> Viết Lại Văn Phong';
                if (res.success && res.rewritten_text) {
                    document.getElementById('aiRewriteTextarea').value = res.rewritten_text;
                    if (currentAiData) {
                        currentAiData.description = res.rewritten_text;
                        document.getElementById('aiResDesc').innerHTML = res.rewritten_text;
                    }
                    alert('✨ Đã viết lại văn phong thành công!');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-pen-nib"></i> Viết Lại Văn Phong';
            });
        });

        // Load History
        function loadHistory() {
            const container = document.getElementById('aiHistoryList');
            fetch('<?= url("admin/products/ai-assistant/history") ?>')
            .then(res => res.json())
            .then(res => {
                if (res.success && res.history && res.history.length > 0) {
                    let html = '';
                    res.history.forEach(item => {
                        let parsedData = {};
                        try { parsedData = JSON.parse(item.response_data); } catch(e){}
                        html += `
                            <div style="border-bottom: 1px solid #F1F5F9; padding: 8px 4px; display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <strong style="font-size: 12.5px; color: #1E293B;">${item.prompt}</strong>
                                    <div style="font-size: 11px; color: #64748B;">Nguồn: ${item.source_name || 'N/A'} | Điểm: ${item.confidence_score}% | Ngày: ${item.created_at}</div>
                                </div>
                                <div style="display: flex; gap: 6px;">
                                    <button type="button" class="btn btn--outline btn--sm btn-reuse-history" data-payload='${JSON.stringify(parsedData)}' style="font-size: 11px; padding: 3px 8px;">Tái sử dụng</button>
                                </div>
                            </div>
                        `;
                    });
                    container.innerHTML = html;

                    container.querySelectorAll('.btn-reuse-history').forEach(b => {
                        b.addEventListener('click', function() {
                            const data = JSON.parse(this.getAttribute('data-payload'));
                            if (data && data.name) {
                                currentAiData = data;
                                renderAiResult(data);
                                switchTab('generate');
                                resultContainer.style.display = 'block';
                                applyBtn.disabled = false;
                            }
                        });
                    });
                } else {
                    container.innerHTML = '<div style="text-align: center; color: #94A3B8; padding: 15px; font-size: 12px;">Chưa có lịch sử sinh AI nào.</div>';
                }
            });
        }
        document.getElementById('btnRefreshHistory').addEventListener('click', loadHistory);

        // Apply To Form
        applyBtn.addEventListener('click', function() {
            if (!currentAiData) return;

            if (currentAiData.name) document.getElementById('name').value = currentAiData.name;
            if (currentAiData.slug) document.getElementById('slug').value = currentAiData.slug;
            if (currentAiData.description) document.getElementById('description').value = currentAiData.description;

            // Populate Specs
            if (currentAiData.specs && typeof currentAiData.specs === 'object') {
                const specsTextarea = document.getElementById('specs');
                specsTextarea.value = JSON.stringify(currentAiData.specs, null, 4);
                const event = new Event('input', { bubbles: true });
                specsTextarea.dispatchEvent(event);
                
                const rowsContainer = document.getElementById('specsRowsContainer');
                if (rowsContainer) {
                    rowsContainer.innerHTML = '';
                    Object.keys(currentAiData.specs).forEach(key => {
                        const row = document.createElement('div');
                        row.className = 'spec-row';
                        row.style.display = 'grid';
                        row.style.gridTemplateColumns = '0.4fr 0.6fr auto';
                        row.style.gap = '8px';
                        row.style.alignItems = 'center';
                        row.innerHTML = `
                            <input type="text" class="form-control spec-key" value="${key}" style="padding: 8px 12px; font-size: 13px;">
                            <input type="text" class="form-control spec-value" value="${currentAiData.specs[key]}" style="padding: 8px 12px; font-size: 13px;">
                            <button type="button" class="btn btn--danger btn--sm btn-delete-row" style="padding: 8px 10px; box-shadow: none;"><i class="fa-solid fa-trash-can"></i></button>
                        `;
                        row.querySelector('.btn-delete-row').addEventListener('click', function() { row.remove(); });
                        rowsContainer.appendChild(row);
                    });
                }
            }

            modal.style.display = 'none';
            alert('✨ Đã áp dụng đầy đủ thông tin từ AI vào Form thành công!');
        });
    }
});
</script>

<div class="card" style="margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
        <h3 class="card-title" style="margin: 0;">Thêm danh mục mới</h3>
        <button type="button" id="btnAiAutoCategory" class="btn" style="background: linear-gradient(135deg, #6366F1 0%, #A855F7 100%); color: #fff; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(168, 85, 247, 0.25);">
            <i class="fa-solid fa-wand-magic-sparkles"></i> AI Tự động sinh (Mô tả, Icon, Slug & Ảnh)
        </button>
    </div>
    
    <form method="post" action="<?= url('admin/categories/store') ?>" id="categoryForm">
        <?= csrf_field() ?>
        
        <div class="form-group">
            <label for="name">Tên danh mục <span style="color: red;">*</span> <small style="color: #64748b; font-weight: normal;">(Bạn chỉ cần gõ tên, AI sẽ tự điền tất cả các ô bên dưới)</small></label>
            <div style="display: flex; gap: 10px;">
                <input type="text" name="name" id="name" class="form-control" style="flex: 1;" placeholder="Ví dụ: Kính thực tế ảo VR, Máy ảnh & Flycam, Bàn phím cơ..." required autofocus>
            </div>
        </div>

        <div class="form-group">
            <label for="slug">Slug (Đường dẫn tĩnh - AI Tự sinh hoặc chỉnh sửa tùy ý)</label>
            <input type="text" name="slug" id="slug" class="form-control" placeholder="Ví dụ: kinh-thuc-te-ao-vr">
        </div>

        <div class="form-group">
            <label for="description">Mô tả danh mục (AI Tự sáng tác chuẩn SEO hoặc chỉnh sửa tùy ý)</label>
            <textarea name="description" id="description" class="form-control" rows="4" placeholder="AI sẽ tự động sáng tác mô tả sản phẩm chuẩn SEO cho bạn..."></textarea>
        </div>

        <div class="form-group">
            <label for="parent_id">Danh mục cha</label>
            <select name="parent_id" id="parent_id" class="form-control">
                <option value="">-- Là danh mục gốc (Không có cha) --</option>
                <?php foreach ($categories ?? [] as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="icon">Icon class (AI Tự chọn Icon FontAwesome khớp nhất)</label>
            <div style="display: flex; gap: 10px; align-items: center;">
                <input type="text" name="icon" id="icon" class="form-control" style="flex: 1;" placeholder="fa-solid fa-tag">
                <div id="iconPreview" style="width: 40px; height: 40px; border: 1px solid var(--border); border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 18px; color: var(--primary); background: var(--bg-subtle);">
                    <i class="fa-solid fa-tag"></i>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="image">Ảnh danh mục (đường dẫn hoặc tên file)</label>
            <input type="text" name="image" id="image" class="form-control" placeholder="assets/images/categories/category-laptop.png">
        </div>

        <div class="form-group">
            <label for="sort_order">Thứ tự sắp xếp hiển thị</label>
            <input type="number" name="sort_order" id="sort_order" class="form-control" value="0" min="0">
        </div>

        <div class="form-group">
            <label for="status">Trạng thái hoạt động</label>
            <select name="status" id="status" class="form-control">
                <option value="active">Kích hoạt (Hiển thị storefront)</option>
                <option value="inactive">Tạm ngưng (Ẩn khỏi storefront)</option>
            </select>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 25px;">
            <button type="submit" class="btn"><i class="fa-solid fa-floppy-disk"></i> Lưu lại</button>
            <a href="<?= url('admin/categories') ?>" class="btn btn--secondary">Quay lại</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    const descInput = document.getElementById('description');
    const iconInput = document.getElementById('icon');
    const imageInput = document.getElementById('image');
    const iconPreview = document.getElementById('iconPreview');
    const btnAi = document.getElementById('btnAiAutoCategory');

    function updateIconPreview() {
        const iconClass = iconInput.value.trim() || 'fa-solid fa-tag';
        iconPreview.innerHTML = `<i class="${iconClass}"></i>`;
    }

    iconInput.addEventListener('input', updateIconPreview);

    function triggerAiAutoFill() {
        const nameVal = nameInput.value.trim();
        if (!nameVal) {
            alert('Vui lòng nhập Tên danh mục trước.');
            nameInput.focus();
            return;
        }

        btnAi.disabled = true;
        const originalText = btnAi.innerHTML;
        btnAi.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> AI đang suy nghĩ & phân tích...';

        const formData = new FormData();
        formData.append('name', nameVal);
        const csrfToken = document.querySelector('input[name="_csrf"]')?.value || '';
        formData.append('_csrf', csrfToken);

        fetch('<?= url("admin/categories/ai-generate") ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': csrfToken
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (data.slug) slugInput.value = data.slug;
                if (data.icon) {
                    iconInput.value = data.icon;
                    updateIconPreview();
                }
                if (data.description) descInput.value = data.description;
                if (data.image) imageInput.value = data.image;
            } else {
                alert(data.message || 'Lỗi khi gọi AI.');
            }
        })
        .catch(err => {
            console.error(err);
        })
        .finally(() => {
            btnAi.disabled = false;
            btnAi.innerHTML = originalText;
        });
    }

    btnAi.addEventListener('click', triggerAiAutoFill);

    // Tự động gọi AI khi người dùng ngưng gõ tên danh mục (debounce 800ms)
    let autoDebounce = null;
    nameInput.addEventListener('input', function() {
        clearTimeout(autoDebounce);
        autoDebounce = setTimeout(() => {
            if (nameInput.value.trim().length >= 2 && !descInput.value.trim()) {
                triggerAiAutoFill();
            }
        }, 900);
    });
});
</script>

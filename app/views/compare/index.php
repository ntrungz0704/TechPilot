<?php include ROOT_PATH . '/app/views/layouts/header.php'; ?>

<style>
    .cat-bar-container {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 24px;
        background: var(--surface-card, #FFFFFF);
        padding: 16px;
        border-radius: 16px;
        border: 1px solid var(--border, #E2E8F0);
    }
    .cat-tab-btn {
        padding: 8px 16px;
        border-radius: 20px;
        border: 1px solid var(--border);
        background: #F8FAFC;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
        color: var(--text-primary);
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
    }
    .cat-tab-btn.active {
        background: var(--primary);
        color: #FFFFFF;
        border-color: var(--primary);
        box-shadow: 0 4px 12px rgba(10, 91, 255, 0.25);
    }
    .cat-tab-btn.locked {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .persona-box {
        background: var(--surface-card, #FFFFFF);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 24px;
        box-shadow: var(--shadow-card);
    }
    .persona-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 10px;
    }
    .persona-pill {
        padding: 8px 18px;
        border-radius: 20px;
        border: 2px solid var(--border);
        background: #FFFFFF;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
    }
    .persona-pill.active {
        border-color: var(--primary);
        background: rgba(10, 91, 255, 0.08);
        color: var(--primary);
    }

    /* Highlight cell styles */
    .cell-highlight-best {
        background-color: rgba(16, 185, 129, 0.12) !important;
        color: #047857 !important;
        font-weight: 800 !important;
    }
    .cell-highlight-warn {
        background-color: rgba(245, 158, 11, 0.12) !important;
        color: #B45309 !important;
    }
    .cell-highlight-ineligible {
        background-color: rgba(239, 68, 68, 0.12) !important;
        color: #B91C1C !important;
        font-weight: 700 !important;
    }

    .winner-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        color: #FFFFFF;
        margin-bottom: 6px;
    }
    .badge-best_fit { background: linear-gradient(135deg, #10B981, #059669); }
    .badge-best_value { background: linear-gradient(135deg, #3B82F6, #1D4ED8); }
    .badge-best_performance { background: linear-gradient(135deg, #8B5CF6, #6D28D9); }
</style>

<main class="container section" id="main-content" style="margin-top: 40px; min-height: 60vh;">
    <div class="section__head" style="margin-bottom: 24px;">
        <h2><i class="fa-solid fa-code-compare" style="color: var(--primary); margin-right: 8px;"></i> So sánh sản phẩm theo Persona (TechPilot 4.0)</h2>
        <p style="color: var(--text-secondary); font-size: 14.5px; margin-top: 4px;">Chọn danh mục trước, thêm 2-4 sản phẩm và chọn đối tượng (Persona) để hệ thống phân tích người chiến thắng.</p>
    </div>

    <?php if (isset($flashes['success'])): ?>
        <div class="alert alert--success" style="margin-bottom: 20px; padding: 12px; background-color: #DEF7EC; color: #03543F; border-radius: 8px;">
            <?= e($flashes['success']) ?>
        </div>
    <?php endif; ?>
    <?php if (isset($flashes['error'])): ?>
        <div class="alert alert--danger" style="margin-bottom: 20px; padding: 12px; background-color: #FDE8E8; color: #9B1C1C; border-radius: 8px;">
            <?= e($flashes['error']) ?>
        </div>
    <?php endif; ?>
    <?php if (isset($flashes['info'])): ?>
        <div class="alert alert--info" style="margin-bottom: 20px; padding: 12px; background-color: #E1EFFE; color: #1E429F; border-radius: 8px;">
            <?= e($flashes['info']) ?>
        </div>
    <?php endif; ?>

    <!-- STEP 1: THANH CHỌN DANH MỤC BAN ĐẦU -->
    <div class="cat-bar-container">
        <span style="font-size: 13px; font-weight: 800; color: var(--text-secondary); display: flex; align-items: center; gap: 6px; width: 100%; margin-bottom: 4px;">
            <i class="fa-solid fa-filter"></i> BƯỚC 1: CHỌN LOẠI SẢN PHẨM MUỐN SO SÁNH:
            <?php if (!empty($products)): ?>
                <span style="margin-left: auto; color: #10B981; font-size: 12px; background: rgba(16, 185, 129, 0.1); padding: 4px 10px; border-radius: 12px;">🔒 Đã khóa danh mục theo sản phẩm đã chọn</span>
            <?php endif; ?>
        </span>

        <?php foreach ($compareConfig['categories'] as $cKey => $cMeta):
            $isActive = ($cKey === $activeCategorySlug || (isset($cMeta['slugs']) && in_array($activeCategorySlug, $cMeta['slugs'])));
            $isLocked = !empty($products);
        ?>
            <a href="<?= $isLocked ? 'javascript:void(0)' : url('compare?cat=' . $cKey) ?>" class="cat-tab-btn <?= $isActive ? 'active' : '' ?> <?= $isLocked ? 'locked' : '' ?>">
                <i class="fa-solid <?= $cMeta['icon'] ?>"></i> <?= e($cMeta['label']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- KHUNG TÌM KIẾM SẢN PHẨM KHÓA THEO DANH MỤC -->
    <?php if (count($products) < 4): ?>
        <div style="background-color: var(--bg-white); border: 1px solid var(--border); border-radius: 14px; padding: 20px; margin-bottom: 24px; box-shadow: var(--shadow-card); position: relative;">
            <label for="compareSearchInput" style="font-weight: 700; font-size: 14.5px; display: flex; align-items: center; gap: 8px; margin-bottom: 10px; color: var(--text-primary);">
                <i class="fa-solid fa-magnifying-glass" style="color: var(--primary);"></i> Tìm & Thêm sản phẩm vào so sánh (Còn trống <?= 4 - count($products) ?> vị trí)
            </label>
            <div style="position: relative;">
                <input type="text" id="compareSearchInput" placeholder="Nhập tên sản phẩm (Hệ thống sẽ lọc đúng danh mục đã chọn)..." style="width: 100%; padding: 12px 16px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px; box-sizing: border-box;" oninput="onCompareSearchInput(this.value)">
                <div id="compareSearchResults" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: var(--bg-white); border: 1px solid var(--border); border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); z-index: 100; max-height: 320px; overflow-y: auto; margin-top: 4px;"></div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (empty($products)): ?>
        <div style="text-align: center; padding: 60px 20px; background-color: var(--bg-white); border: 1px solid var(--border); border-radius: 14px;">
            <i class="fa-solid fa-scale-unbalanced" style="font-size: 64px; color: var(--text-secondary); margin-bottom: 20px; display: block;"></i>
            <h3 style="margin-bottom: 10px; font-weight: 700;">Chưa có sản phẩm so sánh</h3>
            <p style="color: var(--text-secondary); margin-bottom: 20px;">Hãy bấm chọn loại sản phẩm ở trên hoặc nhập tên vào ô tìm kiếm để bắt đầu so sánh chuyên sâu!</p>
        </div>
    <?php else: ?>

        <!-- STEP 2 & 3: PERSONA & TIÊU CHÍ ƯU TIÊN PANEL -->
        <div class="persona-box">
            <h4 style="margin: 0 0 8px 0; font-weight: 800; color: var(--text-primary);">🎯 Chọn Persona / Nhu cầu sử dụng thực tế của bạn:</h4>
            <p style="color: var(--text-secondary); font-size: 13px; margin-bottom: 12px;">Hệ thống sẽ thay đổi ma trận trọng số chấm điểm 100 điểm để tìm sản phẩm phù hợp nhất cho đối tượng này.</p>
            <div class="persona-pills" id="personaPills">
                <?php
                $personaList = $compareConfig['personas'][$activeCategorySlug] ?? ($compareConfig['personas']['laptop'] ?? []);
                foreach ($personaList as $idx => $pItem):
                ?>
                    <div class="persona-pill <?= $idx === 0 ? 'active' : '' ?>" onclick="selectPersona('<?= $pItem['code'] ?>', this)">
                        <?= e($pItem['label']) ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <input type="hidden" id="selectedPersonaCode" value="<?= !empty($personaList[0]['code']) ? $personaList[0]['code'] : 'developer' ?>">

            <!-- Nút bấm phân tích AI -->
            <div style="margin-top: 20px; display: flex; align-items: center; gap: 16px;">
                <button type="button" class="btn" id="btnRunCompareAi" onclick="runAiPersonaAnalysis()" style="padding: 12px 28px; font-weight: 800; background: linear-gradient(135deg, var(--primary) 0%, #059669 100%); color: #FFFFFF; border: none; border-radius: 10px; cursor: pointer;">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Phân Tích & Chấm Điểm AI Theo Persona
                </button>
            </div>
        </div>

        <!-- BẢNG SO SÁNH THÔNG SỐ SẠCH KHÔNG LỘ METADATA -->
        <div style="overflow-x: auto; background-color: var(--bg-white); border: 1px solid var(--border); border-radius: 14px; box-shadow: var(--shadow-card); margin-bottom: 30px;">
            <table style="width: 100%; border-collapse: collapse; min-width: 700px; text-align: left; font-size: 14px;" id="compareMainTable">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border); background-color: #F8FAFC;">
                        <th style="padding: 20px; width: 20%; font-weight: 800; color: var(--text-primary);">Hạng mục so sánh</th>
                        <?php foreach ($products as $p): ?>
                            <th class="prod-col-<?= $p['id'] ?>" style="padding: 20px; width: 20%; text-align: center; position: relative; border-left: 1px solid var(--border);">
                                <form method="post" action="<?= url('compare/remove') ?>" style="position: absolute; top: 10px; right: 10px;">
                                    <input type="hidden" name="_csrf" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                    <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                                    <button type="submit" style="background: none; border: none; color: #9CA3AF; cursor: pointer; font-size: 16px;" title="Xóa khỏi so sánh"><i class="fa-solid fa-xmark-circle"></i></button>
                                </form>

                                <div id="winnerBadgeSlot-<?= $p['id'] ?>"></div>

                                <div style="display: flex; flex-direction: column; align-items: center; text-align: center; margin-top: 15px;">
                                    <img src="<?= productImageUrl($p['image'] ?? '', $p['category_slug'] ?? '', (int)$p['id']) ?>" alt="<?= e($p['name']) ?>" style="height: 90px; object-fit: contain; margin-bottom: 15px;">
                                    <strong style="font-size: 13.5px; line-height: 1.4; height: 38px; overflow: hidden; display: block; margin-bottom: 8px;"><?= e($p['name']) ?></strong>
                                    <span style="color: var(--primary); font-weight: 800; font-size: 16px;"><?= number_format($p['price'], 0, ',', '.') ?>đ</span>
                                </div>
                            </th>
                        <?php endforeach; ?>

                        <?php for ($i = 0; $i < (4 - count($products)); $i++):
                            $slotNum = count($products) + $i + 1;
                        ?>
                            <th style="padding: 16px; width: 20%; text-align: center; border-left: 1px solid var(--border); vertical-align: middle;">
                                <button type="button" class="btn-add-compare-slot" onclick="focusCompareSearch()" style="width: 100%; min-height: 120px; border: 2px dashed #10B981; background: rgba(16, 185, 129, 0.06); border-radius: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; cursor: pointer; transition: all 0.2s ease; color: #047857;" title="Click để chọn sản phẩm thêm vào vị trí <?= $slotNum ?>">
                                    <i class="fa-solid fa-circle-plus" style="font-size: 34px; color: #10B981;"></i>
                                    <span style="font-size: 13px; font-weight: 700;">+ Thêm sản phẩm <?= $slotNum ?></span>
                                    <span style="font-size: 11px; color: #64748B; font-weight: 500;">(Bấm để chọn)</span>
                                </button>
                            </th>
                        <?php endfor; ?>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 15px 20px; font-weight: 700; color: var(--text-secondary); background-color: #F8FAFC;">Thương hiệu</td>
                        <?php foreach ($products as $p): ?>
                            <td class="prod-col-<?= $p['id'] ?>" style="padding: 15px 20px; font-weight: 600; text-align: center; border-left: 1px solid var(--border);"><?= e($p['brand_name']) ?></td>
                        <?php endforeach; ?>
                        <?php for ($i = 0; $i < (4 - count($products)); $i++): ?>
                            <td style="border-left: 1px solid var(--border); text-align: center; color: #94A3B8; font-style: italic; font-size: 12px;">Chưa chọn</td>
                        <?php endfor; ?>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 15px 20px; font-weight: 700; color: var(--text-secondary); background-color: #F8FAFC;">Danh mục</td>
                        <?php foreach ($products as $p): ?>
                            <td class="prod-col-<?= $p['id'] ?>" style="padding: 15px 20px; text-align: center; border-left: 1px solid var(--border);"><?= e($p['category_name']) ?></td>
                        <?php endforeach; ?>
                        <?php for ($i = 0; $i < (4 - count($products)); $i++): ?>
                            <td style="border-left: 1px solid var(--border); text-align: center; color: #94A3B8; font-style: italic; font-size: 12px;">Chưa chọn</td>
                        <?php endfor; ?>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 15px 20px; font-weight: 700; color: var(--text-secondary); background-color: #F8FAFC;">Tồn kho thực tế</td>
                        <?php foreach ($products as $p): ?>
                            <td class="prod-col-<?= $p['id'] ?>" style="padding: 15px 20px; text-align: center; border-left: 1px solid var(--border); font-weight: 700; color: #10B981;">Còn <?= (int)$p['stock'] ?> máy</td>
                        <?php endforeach; ?>
                        <?php for ($i = 0; $i < (4 - count($products)); $i++): ?>
                            <td style="border-left: 1px solid var(--border); text-align: center; color: #94A3B8; font-style: italic; font-size: 12px;">Chưa chọn</td>
                        <?php endfor; ?>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- KHUNG KẾT QUẢ PHÂN TÍCH AI PERSONA -->
        <div id="compareAiResultBox" style="display: none; background: var(--bg-white); border: 1px solid var(--border); border-radius: 16px; padding: 24px; box-shadow: var(--shadow-card); margin-bottom: 40px;">
            <h3 style="margin: 0 0 10px 0; color: var(--text-primary); font-weight: 800; font-size: 20px;">
                🤖 Báo Cáo Phân Tích So Sánh AI Chi Tiết
            </h3>
            <p id="aiSummaryText" style="color: var(--text-secondary); font-size: 14.5px; line-height: 1.6; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid var(--border);"></p>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.2); padding: 18px; border-radius: 12px;">
                    <h4 style="color: #047857; margin: 0 0 8px 0; font-weight: 700; font-size: 15px;"><i class="fa-solid fa-user-check"></i> Khuyên dùng theo Persona</h4>
                    <div id="aiWhoShouldBuy" style="font-size: 13.5px; line-height: 1.6; color: var(--text-primary);"></div>
                </div>
                <div style="background: rgba(239, 68, 68, 0.05); border: 1px solid rgba(239, 68, 68, 0.2); padding: 18px; border-radius: 12px;">
                    <h4 style="color: #B91C1C; margin: 0 0 8px 0; font-weight: 700; font-size: 15px;"><i class="fa-solid fa-triangle-exclamation"></i> Điểm cần đánh đổi / Cân nhắc</h4>
                    <div id="aiTradeoffs" style="font-size: 13.5px; line-height: 1.6; color: var(--text-primary);"></div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

<script>
    const activeCatSlug = '<?= $activeCategorySlug ?>';
    const csrfToken = '<?= $csrf_token ?>';

    function selectPersona(code, el) {
        document.querySelectorAll('#personaPills .persona-pill').forEach(p => p.classList.remove('active'));
        el.classList.add('active');
        document.getElementById('selectedPersonaCode').value = code;
    }

    function focusCompareSearch() {
        const input = document.getElementById('compareSearchInput');
        if (!input) return;

        input.scrollIntoView({ behavior: 'smooth', block: 'center' });
        setTimeout(() => {
            input.focus();
            input.style.border = '2px solid #10B981';
            input.style.boxShadow = '0 0 0 3px rgba(16, 185, 129, 0.25)';
            setTimeout(() => {
                input.style.border = '';
                input.style.boxShadow = '';
            }, 2500);
            onCompareSearchInput('');
        }, 300);
    }

    let compareSearchTimer = null;
    function onCompareSearchInput(query) {
        clearTimeout(compareSearchTimer);
        const resultsBox = document.getElementById('compareSearchResults');
        if (!resultsBox) return;

        query = (query || '').trim();

        compareSearchTimer = setTimeout(() => {
            fetch('<?= url("compare?search_ajax=") ?>' + encodeURIComponent(query) + '&category_slug=' + encodeURIComponent(activeCatSlug))
                .then(res => res.json())
                .then(res => {
                    if (res.success && res.data && res.data.length > 0) {
                        const headerText = `<div style="padding: 8px 16px; font-size: 11px; font-weight: 700; color: #64748B; background: #F8FAFC; text-transform: uppercase; border-bottom: 1px solid var(--border);">💡 Sản phẩm danh mục [${activeCatSlug.toUpperCase()}]:</div>`;
                        resultsBox.innerHTML = headerText + res.data.map(p => `
                            <div style="padding: 12px 16px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                                <div style="display: flex; align-items: center; gap: 12px; flex: 1;">
                                    <img src="${p.image_url}" alt="${p.name}" style="width: 40px; height: 40px; object-fit: contain;">
                                    <div>
                                        <strong style="font-size: 13.5px; display: block; color: var(--text-primary);">${p.name}</strong>
                                        <span style="font-size: 12.5px; color: var(--primary); font-weight: 700;">${p.price_formatted}</span>
                                    </div>
                                </div>
                                <form method="post" action="<?= url('compare/add') ?>" style="margin:0;">
                                    <input type="hidden" name="_csrf" value="${csrfToken}">
                                    <input type="hidden" name="product_id" value="${p.id}">
                                    <button type="submit" class="btn btn--sm" style="padding: 6px 12px; font-size: 12px; background-color: #10B981; color: #FFFFFF; border: none; cursor: pointer;">
                                        <i class="fa-solid fa-plus"></i> Thêm so sánh
                                    </button>
                                </form>
                            </div>
                        `).join('');
                        resultsBox.style.display = 'block';
                    } else {
                        resultsBox.innerHTML = '<div style="padding: 16px; text-align: center; color: var(--text-secondary); font-size: 13px;">Không tìm thấy sản phẩm phù hợp trong danh mục này</div>';
                        resultsBox.style.display = 'block';
                    }
                })
                .catch(err => {
                    resultsBox.style.display = 'none';
                });
        }, 200);
    }

    function runAiPersonaAnalysis() {
        const btn = document.getElementById('btnRunCompareAi');
        const personaCode = document.getElementById('selectedPersonaCode').value;

        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Đang phân tích AI...';

        const formData = new URLSearchParams();
        formData.append('_csrf', csrfToken);
        formData.append('category', activeCatSlug);
        formData.append('persona', personaCode);

        fetch('<?= url("compare/aiCompare") ?>', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> Phân Tích & Chấm Điểm AI Theo Persona';

            if (res.success) {
                // Hiển thị Winner Badges
                const winners = res.winners || {};
                Object.keys(winners).forEach(wRole => {
                    $pId = winners[wRole];
                    const slot = document.getElementById(`winnerBadgeSlot-${$pId}`);
                    if (slot) {
                        let labelText = 'PHÙ HỢP NHẤT';
                        if (wRole === 'best_value') labelText = 'ĐÁNG TIỀN NHẤT';
                        if (wRole === 'best_performance') labelText = 'HIỆU NĂNG CAO NHẤT';
                        slot.innerHTML = `<span class="winner-badge badge-${wRole}">${labelText}</span>`;
                    }
                });

                // Hiển thị báo cáo AI
                document.getElementById('compareAiResultBox').style.display = 'block';
                document.getElementById('aiSummaryText').innerText = res.analysis.summary || '';
                document.getElementById('aiWhoShouldBuy').innerText = res.analysis.who_should_buy || '';
                document.getElementById('aiTradeoffs').innerText = res.analysis.tradeoffs || '';

                document.getElementById('compareAiResultBox').scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                alert('Thông báo: ' + (res.message || 'Không thể thực hiện phân tích.'));
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> Phân Tích & Chấm Điểm AI Theo Persona';
            alert('Lỗi phân tích: ' + err.message);
        });
    }
</script>

<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>

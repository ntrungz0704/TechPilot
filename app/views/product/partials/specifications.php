<?php
/**
 * Reusable Product Specifications Table Partial (TechPilot Clean Design System)
 * Rendered by ProductSpecPresenter for 20 tech categories.
 */

$categorySlug = $categorySlug ?? ($product['category_slug'] ?? '');
$specsData = $specsData ?? ($specs ?? []);

require_once ROOT_PATH . '/app/services/ProductSpecPresenter.php';
$groupedSpecs = ProductSpecPresenter::getGroupedSpecs($categorySlug, $specsData);
?>

<?php if (!empty($groupedSpecs)): ?>
    <div class="product-specifications-wrapper" style="border: 1px solid var(--border, #E2E8F0); border-radius: 12px; overflow: hidden; margin-bottom: 24px; box-shadow: var(--shadow-card, 0 4px 12px rgba(0,0,0,0.04)); background: var(--bg-card, #FFFFFF);">
        <!-- Banner Tiêu đề Chuẩn TechPilot -->
        <div style="background: linear-gradient(90deg, var(--primary, #0A5BFF) 0%, #1D4ED8 100%); color: #FFFFFF; font-weight: 700; font-size: 14px; text-transform: uppercase; padding: 14px 20px; letter-spacing: 0.5px; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-sliders" style="font-size: 15px;"></i> THÔNG SỐ KỸ THUẬT CHI TIẾT
        </div>

        <div style="padding: 16px 20px;">
            <?php foreach ($groupedSpecs as $groupTitle => $groupItems): ?>
                <?php if (!empty($groupItems)): ?>
                    <div class="spec-group" style="margin-bottom: 20px;">
                        <h4 style="font-size: 14px; font-weight: 700; color: var(--primary, #0A5BFF); margin: 12px 0 10px 0; text-transform: uppercase; letter-spacing: 0.4px; display: flex; align-items: center; gap: 8px; border-left: 3.5px solid var(--primary, #0A5BFF); padding-left: 10px;">
                            <?= e($groupTitle) ?>
                        </h4>
                        <table class="specs-table" style="width: 100%; border-collapse: collapse; background-color: var(--surface-card); border: 1px solid var(--border); border-radius: 8px; overflow: hidden;">
                            <tbody>
                                <?php $rIdx = 0; foreach ($groupItems as $sLabel => $sValue): $rIdx++; ?>
                                    <?php if ($sLabel !== '' && $sValue !== '' && $sValue !== 'Đang cập nhật'): ?>
                                        <tr class="spec-row-<?= $rIdx % 2 === 0 ? 'even' : 'odd' ?>" style="border-bottom: 1px solid var(--border);">
                                            <th style="width: 30%; padding: 11px 16px; text-align: left; font-weight: 600; color: var(--text-secondary); font-size: 13.5px; border-right: 1px solid var(--border); vertical-align: top;"><?= e($sLabel) ?></th>
                                            <td style="padding: 11px 16px; color: var(--text-primary); font-size: 13.5px; font-weight: 500; line-height: 1.5; vertical-align: top;"><?= nl2br(e($sValue)) ?></td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
<?php else: ?>
    <p style="color: var(--text-secondary); font-style: italic;">Đang cập nhật thông số kỹ thuật cho sản phẩm này.</p>
<?php endif; ?>

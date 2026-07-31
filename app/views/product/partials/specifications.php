<?php
/**
 * Reusable Product Specifications Table Partial (GearVN Red Header Style)
 * Rendered by ProductSpecPresenter for 20 tech categories.
 */

$categorySlug = $categorySlug ?? ($product['category_slug'] ?? '');
$specsData = $specsData ?? ($specs ?? []);

require_once ROOT_PATH . '/app/services/ProductSpecPresenter.php';
$groupedSpecs = ProductSpecPresenter::getGroupedSpecs($categorySlug, $specsData);
?>

<?php if (!empty($groupedSpecs)): ?>
    <div class="product-specifications-wrapper" style="border: 1px solid #E2E8F0; border-radius: 8px; overflow: hidden; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.04);">
        <!-- Banner Tiêu đề Đỏ Chuẩn GearVN -->
        <div style="background: linear-gradient(90deg, #DC2626 0%, #EF4444 100%); color: #FFFFFF; font-weight: 800; font-size: 14px; text-transform: uppercase; padding: 12px 18px; letter-spacing: 0.5px; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-list-check"></i> THÔNG SỐ KỸ THUẬT
        </div>

        <div style="padding: 12px 16px; background-color: var(--bg-card, #FFFFFF);">
            <?php foreach ($groupedSpecs as $groupTitle => $groupItems): ?>
                <?php if (!empty($groupItems)): ?>
                    <div class="spec-group" style="margin-bottom: 16px;">
                        <h4 style="font-size: 13.5px; font-weight: 700; color: #DC2626; margin: 10px 0 8px 0; text-transform: uppercase; letter-spacing: 0.4px; display: flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-angle-right" style="font-size: 11px;"></i> <?= e($groupTitle) ?>
                        </h4>
                        <table class="specs-table" style="width: 100%; border-collapse: collapse; background-color: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 6px; overflow: hidden;">
                            <tbody>
                                <?php $rIdx = 0; foreach ($groupItems as $sLabel => $sValue): $rIdx++; ?>
                                    <?php if ($sLabel !== '' && $sValue !== '' && $sValue !== 'Đang cập nhật'): ?>
                                        <tr style="border-bottom: 1px solid #E2E8F0; background-color: <?= $rIdx % 2 === 0 ? '#F8FAFC' : '#FFFFFF' ?>;">
                                            <th style="width: 32%; padding: 10px 14px; text-align: left; font-weight: 700; color: #1E293B; font-size: 13px; border-right: 1px solid #E2E8F0; vertical-align: top;"><?= e($sLabel) ?></th>
                                            <td style="padding: 10px 14px; color: #334155; font-size: 13px; font-weight: 500; line-height: 1.5; vertical-align: top;"><?= nl2br(e($sValue)) ?></td>
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

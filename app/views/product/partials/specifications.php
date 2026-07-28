<?php
/**
 * Reusable Product Specifications Table Partial
 * Rendered by ProductSpecPresenter for 20 tech categories.
 */

$categorySlug = $categorySlug ?? ($product['category_slug'] ?? '');
$specsData = $specsData ?? ($specs ?? []);

require_once ROOT_PATH . '/app/services/ProductSpecPresenter.php';
$groupedSpecs = ProductSpecPresenter::getGroupedSpecs($categorySlug, $specsData);
?>

<?php if (!empty($groupedSpecs)): ?>
    <div class="product-specifications-wrapper">
        <?php foreach ($groupedSpecs as $groupTitle => $groupItems): ?>
            <?php if (!empty($groupItems)): ?>
                <div class="spec-group" style="margin-bottom: 20px;">
                    <h4 style="font-size: 15px; font-weight: 700; color: var(--text-primary); margin: 15px 0 10px 0; border-left: 4px solid var(--primary, #0A5BFF); padding-left: 10px; display: flex; align-items: center; gap: 8px;">
                        <?= e($groupTitle) ?>
                    </h4>
                    <table class="specs-table" style="width: 100%; border-collapse: collapse; margin-bottom: 10px; background-color: var(--bg-card, #F8FAFC); border: 1px solid var(--border, #E2E8F0); border-radius: 8px; overflow: hidden;">
                        <tbody>
                            <?php foreach ($groupItems as $sLabel => $sValue): ?>
                                <?php if ($sLabel !== '' && $sValue !== '' && $sValue !== 'Đang cập nhật'): ?>
                                    <tr style="border-bottom: 1px solid var(--border, #E2E8F0);">
                                        <th style="width: 35%; padding: 10px 14px; text-align: left; background-color: rgba(0,0,0,0.02); font-weight: 600; color: var(--text-secondary, #475569); font-size: 13.5px; border-right: 1px solid var(--border, #E2E8F0);"><?= e($sLabel) ?></th>
                                        <td style="padding: 10px 14px; color: var(--text-primary, #0F172A); font-size: 13.5px; font-weight: 500;"><?= e($sValue) ?></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p style="color: var(--text-secondary); font-style: italic;">Đang cập nhật thông số kỹ thuật cho sản phẩm này.</p>
<?php endif; ?>

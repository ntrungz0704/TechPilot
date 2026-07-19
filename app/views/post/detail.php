<?php
$post = $post ?? null;
$related = $related ?? [];
?>

<section class="container breadcrumb">
    <a href="<?= url('/') ?>">Trang chủ</a> <i class="fa-solid fa-chevron-right"></i>
    <a href="<?= url('post') ?>">Tin tức công nghệ</a> <i class="fa-solid fa-chevron-right"></i>
    <span><?= e($post['title']) ?></span>
</section>

<!-- ===== CHI TIẾT BÀI VIẾT ===== -->
<section class="container news-page">
    <div class="news-layout">
        
        <!-- Cột trái: Nội dung chi tiết bài viết -->
        <article class="news-main news-detail-card" style="background: var(--bg-white); border: 1px solid var(--border); border-radius: var(--radius-card); padding: 30px; box-shadow: var(--shadow-card);">
            <span class="news-badge-category" style="margin-bottom: 12px; display: inline-block;">Công nghệ</span>
            <h1 style="font-size: 28px; font-weight: 800; line-height: 1.3; color: var(--text-primary); margin: 0 0 16px 0;"><?= e($post['title']) ?></h1>
            
            <div class="news-meta" style="margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--border); display: flex; gap: 16px; font-size: 13px; color: var(--text-secondary);">
                <span><i class="fa-solid fa-user"></i> Tác giả: <strong>Admin TechPilot</strong></span>
                <span><i class="fa-regular fa-calendar"></i> <?= date('d/m/Y', strtotime($post['created_at'])) ?></span>
                <span><i class="fa-regular fa-eye"></i> <?= (int)$post['views'] ?> lượt xem</span>
            </div>

            <?php if (!empty($post['image'])): ?>
                <div class="news-detail-image" style="width: 100%; border-radius: var(--radius-card); overflow: hidden; margin-bottom: 24px; border: 1px solid var(--border);">
                    <img src="<?= url('assets/images/news/' . e($post['image'])) ?>" alt="<?= e($post['title']) ?>" style="width: 100%; height: auto; object-fit: cover;" onerror="this.outerHTML='<div class=\'news-img-placeholder\' style=\'height: 350px;\'><i class=\'fa-solid fa-newspaper\' style=\'font-size: 64px;\'></i></div>'">
                </div>
            <?php endif; ?>

            <div class="news-detail-content" style="font-size: 15.5px; line-height: 1.7; color: var(--text-primary); text-align: justify;">
                <?= nl2br(e($post['content'] ?? 'Nội dung đang được cập nhật.')) ?>
            </div>
            
            <div class="news-detail-footer" style="margin-top: 40px; padding-top: 20px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                <div class="share-buttons" style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 13.5px; font-weight: 700; color: var(--text-secondary);">Chia sẻ bài viết:</span>
                    <a href="#" class="btn btn--outline btn--sm" style="box-shadow: none; font-size: 12px; padding: 6px 10px;" onclick="event.preventDefault(); alert('Đã sao chép link liên kết!');"><i class="fa-solid fa-link"></i> Sao chép link</a>
                </div>
                <a href="<?= url('post') ?>" class="btn btn--outline btn--sm" style="box-shadow: none; font-size: 12px; padding: 6px 14px;"><i class="fa-solid fa-arrow-left"></i> Quay lại tin tức</a>
            </div>
        </article>

        <!-- Cột phải: Sidebar -->
        <aside class="news-sidebar">
            
            <!-- Box 1: Bài viết liên quan -->
            <div class="news-sidebar-widget">
                <h3 class="widget-title">Bài viết liên quan</h3>
                <div class="news-related-list" style="display: flex; flex-direction: column; gap: 16px;">
                    <?php if (!empty($related)): ?>
                        <?php foreach ($related as $r): ?>
                            <div class="news-related-item" style="display: flex; gap: 12px; align-items: flex-start;">
                                <a href="<?= url('post/detail/' . $r['slug']) ?>" style="width: 80px; height: 60px; border-radius: 6px; overflow: hidden; flex-shrink: 0; border: 1px solid var(--border); display: block;">
                                    <img src="<?= url('assets/images/news/' . e($r['image'])) ?>" alt="<?= e($r['title']) ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.outerHTML='<div class=\'news-img-placeholder\' style=\'font-size: 16px;\'><i class=\'fa-solid fa-newspaper\' style=\'font-size: 16px;\'></i></div>'">
                                </a>
                                <div style="min-width: 0;">
                                    <h5 style="margin: 0 0 4px 0; font-size: 13px; font-weight: 700; line-height: 1.35; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><a href="<?= url('post/detail/' . $r['slug']) ?>" style="color: var(--text-primary); text-decoration: none; transition: var(--transition);"><?= e($r['title']) ?></a></h5>
                                    <span style="font-size: 11px; color: var(--text-secondary);"><i class="fa-regular fa-clock"></i> <?= date('d/m/Y', strtotime($r['created_at'])) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="font-size: 12.5px; color: var(--text-secondary); margin: 0;">Không có bài viết liên quan.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Box 2: Đăng ký nhận tin -->
            <div class="news-sidebar-widget news-subscribe-box">
                <div class="subscribe-icon">
                    <i class="fa-solid fa-paper-plane"></i>
                </div>
                <h4>Đăng ký nhận tin tức</h4>
                <p>Nhận các đánh giá công nghệ, thủ thuật build PC và khuyến mãi mới nhất từ TechPilot.</p>
                <form class="subscribe-form" onsubmit="event.preventDefault(); alert('Đăng ký nhận tin thành công!'); this.reset();">
                    <input type="email" placeholder="Nhập email của bạn..." required>
                    <button type="submit" class="btn btn--block">Đăng ký ngay</button>
                </form>
            </div>
            
        </aside>
    </div>
</section>

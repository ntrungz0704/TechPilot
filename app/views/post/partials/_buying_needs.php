<?php
/**
 * Buying Needs Sidebar Widget
 * Dùng route storefront: home/search?cat=...
 */
?>
<div class="news-sidebar-widget news-buying-needs">
    <h3 class="widget-title">Mua theo nhu cầu</h3>
    <ul class="buying-needs-list">
        <li><a href="<?= url('home/search?cat=laptop-gaming') ?>"><i class="fa-solid fa-gamepad" aria-hidden="true"></i> Laptop Gaming</a></li>
        <li><a href="<?= url('home/search?cat=laptop-van-phong') ?>"><i class="fa-solid fa-briefcase" aria-hidden="true"></i> Laptop văn phòng</a></li>
        <li><a href="<?= url('home/search?cat=pc-linh-kien') ?>"><i class="fa-solid fa-microchip" aria-hidden="true"></i> PC &amp; Linh kiện</a></li>
        <li><a href="<?= url('home/search?cat=man-hinh') ?>"><i class="fa-solid fa-tv" aria-hidden="true"></i> Màn hình</a></li>
        <li><a href="<?= url('home/search?cat=gaming-gear') ?>"><i class="fa-solid fa-headset" aria-hidden="true"></i> Gaming Gear</a></li>
        <li><a href="<?= url('home/search?promo=1') ?>"><i class="fa-solid fa-tags" aria-hidden="true"></i> Khuyến mãi</a></li>
        <li><a href="<?= url('build-pc') ?>"><i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i> Xây dựng cấu hình</a></li>
    </ul>
</div>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Quản trị hệ thống') ?> - TechPilot Admin</title>
    <!-- Google Fonts & FontAwesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS Nhận diện Mockup Admin của TechPilot -->
    <style>
        :root {
            --primary: #0B63E5;
            --primary-light: rgba(11, 99, 229, 0.08);
            --bg-body: #F4F6F8;
            --bg-sidebar: #081325;
            --bg-card: #FFFFFF;
            --text-primary: #1F2937;
            --text-secondary: #64748B;
            --border: #E8ECEF;
            --radius-card: 12px;
            --radius-elem: 8px;
            --shadow-card: 0 4px 18px -4px rgba(15, 23, 42, 0.04);
            --shadow-focus: 0 0 0 3px rgba(11, 99, 229, 0.18);
            --transition: all 0.2s ease-in-out;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-primary);
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 250px;
            background-color: var(--bg-sidebar);
            color: #FFFFFF;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            transition: var(--transition);
        }

        .sidebar__logo {
            padding: 24px 20px;
            font-weight: 800;
            font-size: 20px;
            color: #FFFFFF;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar__logo i {
            color: var(--primary);
            font-size: 24px;
        }

        .sidebar__menu {
            list-style: none;
            padding: 10px 14px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            flex: 1;
            overflow-y: auto;
        }

        .sidebar__menu-item a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 16px;
            color: #94A3B8;
            text-decoration: none;
            font-weight: 500;
            font-size: 13.5px;
            border-radius: var(--radius-elem);
            transition: var(--transition);
        }

        .sidebar__menu-item a i {
            font-size: 16px;
            width: 20px;
            text-align: center;
            transition: var(--transition);
        }

        .sidebar__menu-item a:hover {
            color: #FFFFFF;
            background-color: rgba(255, 255, 255, 0.03);
        }

        .sidebar__menu-item.active a {
            color: #FFFFFF;
            background-color: var(--primary);
            font-weight: 600;
        }

        .sidebar__menu-item.active a i {
            color: #FFFFFF;
        }

        .sidebar__footer {
            padding: 16px 14px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .sidebar__collapse-btn {
            background: none;
            border: none;
            color: #64748B;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            padding: 8px;
            border-radius: var(--radius-elem);
            transition: var(--transition);
            width: max-content;
        }

        .sidebar__collapse-btn:hover {
            color: #FFFFFF;
            background-color: rgba(255, 255, 255, 0.03);
        }

        /* ===== MAIN CONTENT ===== */
        .main-wrapper {
            margin-left: 250px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            transition: var(--transition);
        }

        .header {
            background-color: var(--bg-card);
            border-bottom: 1px solid var(--border);
            height: 70px;
            padding: 0 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 90;
        }

        .header__toggle-sidebar {
            display: none;
            background: none;
            border: none;
            font-size: 20px;
            color: var(--text-secondary);
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
        }

        .header__welcome {
            font-size: 16px;
            font-weight: 600;
            color: #1F2937;
        }

        .header__search-wrap {
            position: relative;
            width: 280px;
        }

        .header__search-wrap i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 14px;
        }

        .header__search-input {
            width: 100%;
            padding: 8px 16px 8px 38px;
            border: 1px solid var(--border);
            border-radius: 9999px;
            font-size: 13.5px;
            outline: none;
            background-color: #F8FAFC;
            transition: var(--transition);
        }

        .header__search-input:focus {
            background-color: #FFFFFF;
            border-color: var(--primary);
            box-shadow: var(--shadow-focus);
        }

        .header__right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header__bell {
            position: relative;
            font-size: 20px;
            color: var(--text-secondary);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 50%;
        }

        .header__bell:hover {
            background-color: #F1F5F9;
            color: var(--text-primary);
        }

        .header__bell-badge {
            position: absolute;
            top: 5px;
            right: 5px;
            background-color: #EF4444;
            color: #FFFFFF;
            font-size: 9px;
            font-weight: 800;
            border-radius: 50%;
            width: 14px;
            height: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #FFFFFF;
        }

        .header__avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            cursor: pointer;
            border: 1px solid var(--border);
            transition: var(--transition);
        }

        .header__avatar:hover {
            transform: scale(1.05);
        }

        .content {
            padding: 30px;
            flex: 1;
        }

        /* ===== UTILITIES & CARDS ===== */
        .card {
            background-color: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-card);
            padding: 24px;
            box-shadow: var(--shadow-card);
            margin-bottom: 24px;
        }

        .card-title {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Forms & Buttons */
        .btn {
            background-color: var(--primary);
            color: #FFFFFF;
            border: none;
            border-radius: var(--radius-elem);
            padding: 9px 16px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn:hover {
            opacity: 0.95;
        }

        .btn--danger {
            background-color: #EF4444;
        }

        .btn--secondary {
            background-color: #64748B;
        }

        .btn--outline {
            background-color: transparent;
            color: var(--text-secondary);
            border: 1px solid var(--border);
        }

        .btn--outline:hover {
            background-color: #F8FAFC;
        }

        .form-group {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            font-weight: 600;
            font-size: 13px;
            color: var(--text-primary);
        }

        .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: var(--radius-elem);
            font-size: 14px;
            background-color: #FFFFFF;
            color: var(--text-primary);
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: var(--shadow-focus);
        }

        /* Tables */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            border: 1px solid var(--border);
            border-radius: var(--radius-elem);
            background: #FFFFFF;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 13.5px;
        }

        .table th {
            background-color: #F8FAFC;
            padding: 12px 16px;
            font-weight: 600;
            color: var(--text-secondary);
            border-bottom: 1px solid var(--border);
            font-size: 12px;
        }

        .table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            color: var(--text-primary);
        }

        .table tr:hover td {
            background-color: #F8FAFC;
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        /* Badge status */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11.5px;
            font-weight: 600;
        }

        .badge--success { background-color: #DCFCE7; color: #15803D; }
        .badge--warning { background-color: #FEF3C7; color: #B45309; }
        .badge--danger { background-color: #FEE2E2; color: #B91C1C; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .main-wrapper {
                margin-left: 0;
            }
            .header__toggle-sidebar {
                display: block;
            }
            .header__search-wrap {
                display: none;
            }
        }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <aside class="sidebar" id="adminSidebar" style="background-color: #091830;">
        <a href="<?= url('admin') ?>" class="sidebar__logo">
            <i class="fa-solid fa-rocket" style="color: #0B63E5;"></i> TechPilot
        </a>
        <ul class="sidebar__menu">
            <li class="sidebar__menu-item <?= $activeMenu === 'dashboard' ? 'active' : '' ?>">
                <a href="<?= url('admin') ?>"><i class="fa-solid fa-chart-pie"></i> Tổng quan</a>
            </li>
            <li class="sidebar__menu-item <?= $activeMenu === 'products' ? 'active' : '' ?>">
                <a href="<?= url('admin/products') ?>"><i class="fa-solid fa-box"></i> Sản phẩm</a>
            </li>
            <li class="sidebar__menu-item <?= $activeMenu === 'pc-builder' ? 'active' : '' ?>">
                <a href="<?= url('admin') ?>"><i class="fa-solid fa-screwdriver-wrench"></i> Xây dựng cấu hình</a>
            </li>
            <li class="sidebar__menu-item <?= $activeMenu === 'orders' ? 'active' : '' ?>">
                <a href="<?= url('admin/orders') ?>"><i class="fa-solid fa-cart-shopping"></i> Đơn hàng</a>
            </li>
            <li class="sidebar__menu-item <?= $activeMenu === 'users' ? 'active' : '' ?>">
                <a href="<?= url('admin/users') ?>"><i class="fa-solid fa-users-gear"></i> Khách hàng</a>
            </li>
            <li class="sidebar__menu-item <?= $activeMenu === 'categories' ? 'active' : '' ?>">
                <a href="<?= url('admin/categories') ?>"><i class="fa-solid fa-folder"></i> Danh mục</a>
            </li>
            <li class="sidebar__menu-item <?= $activeMenu === 'brands' ? 'active' : '' ?>">
                <a href="<?= url('admin/brands') ?>"><i class="fa-solid fa-award"></i> Thương hiệu</a>
            </li>
            <li class="sidebar__menu-item <?= $activeMenu === 'coupons' ? 'active' : '' ?>">
                <a href="<?= url('admin/coupons') ?>"><i class="fa-solid fa-tags"></i> Mã giảm giá</a>
            </li>
            <li class="sidebar__menu-item <?= $activeMenu === 'flash-sales' ? 'active' : '' ?>">
                <a href="<?= url('admin/flash-sales') ?>"><i class="fa-solid fa-bolt-lightning"></i> Flash Sale</a>
            </li>
            <li class="sidebar__menu-item <?= $activeMenu === 'posts' ? 'active' : '' ?>">
                <a href="<?= url('admin/posts') ?>"><i class="fa-solid fa-file-lines"></i> Bài viết</a>
            </li>
            <li class="sidebar__menu-item <?= $activeMenu === 'reviews' ? 'active' : '' ?>">
                <a href="<?= url('admin/reviews') ?>"><i class="fa-solid fa-star"></i> Đánh giá</a>
            </li>
            <li class="sidebar__menu-item <?= $activeMenu === 'banners' ? 'active' : '' ?>">
                <a href="<?= url('admin/banners') ?>"><i class="fa-solid fa-image"></i> Banner</a>
            </li>
        </ul>
        <div class="sidebar__footer">
            <button class="sidebar__collapse-btn" id="collapseSidebarBtn"><i class="fa-solid fa-angle-left"></i></button>
            <form method="post" action="<?= url('auth/logout') ?>" style="width: 100%;">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn--danger btn--block" style="width: 100%;"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</button>
            </form>
        </div>
    </aside>

    <!-- MAIN WRAPPER -->
    <div class="main-wrapper">
        <header class="header">
            <div style="display: flex; align-items: center; gap: 16px;">
                <button class="header__toggle-sidebar" id="toggleSidebarBtn"><i class="fa-solid fa-bars"></i></button>
                <span class="header__welcome">Xin chào, Quản trị viên</span>
            </div>
            
            <div class="header__search-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" class="header__search-input" placeholder="Tìm kiếm...">
            </div>
            
            <div class="header__right">
                <div class="header__bell">
                    <i class="fa-solid fa-bell"></i>
                    <span class="header__bell-badge">3</span>
                </div>
                <img src="https://ui-avatars.com/api/?name=Admin&background=0B63E5&color=fff" class="header__avatar" alt="Avatar" onerror="this.src='<?= url('assets/images/logo.png') ?>'">
            </div>
        </header>
        <main class="content">
            <!-- Flash notification messages -->
            <?php if (!empty($_SESSION['flashes'])): ?>
                <?php foreach (pullFlashes() as $f): ?>
                    <div class="alert alert--<?= e($f['type']) ?>" style="margin-bottom: 20px; padding: 12px 16px; border-radius: 10px; font-size: 13.5px; font-weight: 600; display: flex; align-items: center; gap: 10px; background-color: <?= $f['type'] === 'success' ? '#D1FAE5' : '#FEE2E2' ?>; color: <?= $f['type'] === 'success' ? '#065F46' : '#991B1B' ?>; border: 1px solid <?= $f['type'] === 'success' ? '#10B981' : '#F87171' ?>;">
                        <i class="fa-solid <?= $f['type'] === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
                        <span><?= e($f['message']) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?= $adminContent ?>
        </main>
    </div>

    <!-- Script Toggles -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('toggleSidebarBtn');
            const sidebar = document.getElementById('adminSidebar');

            if (toggleBtn && sidebar) {
                toggleBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    sidebar.classList.toggle('open');
                });

                document.addEventListener('click', function(e) {
                    if (sidebar.classList.contains('open') && !sidebar.contains(e.target) && e.target !== toggleBtn) {
                        sidebar.classList.remove('open');
                    }
                });
            }
        });
    </script>
</body>
</html>

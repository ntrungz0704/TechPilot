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
    
    <!-- CSS Tối giản & Hiện đại dành riêng cho Admin Panel -->
    <style>
        :root {
            --primary: #0A5BFF;
            --primary-dark: #0045D8;
            --bg-body: #F3F4F6;
            --bg-card: #FFFFFF;
            --text-primary: #1F2937;
            --text-secondary: #4B5563;
            --border: #E5E7EB;
            --radius-card: 12px;
            --radius-elem: 8px;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
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
            width: 260px;
            background-color: #0B192C;
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
            padding: 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            font-weight: 800;
            font-size: 20px;
            color: #FFFFFF;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar__logo span {
            color: var(--primary);
        }

        .sidebar__menu {
            list-style: none;
            padding: 20px 12px;
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
            padding: 12px 16px;
            color: #9CA3AF;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            border-radius: var(--radius-elem);
            transition: var(--transition);
        }

        .sidebar__menu-item a:hover,
        .sidebar__menu-item.active a {
            color: #FFFFFF;
            background-color: rgba(255, 255, 255, 0.08);
        }

        .sidebar__menu-item.active a {
            background-color: var(--primary);
            font-weight: 600;
        }

        .sidebar__footer {
            padding: 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        /* ===== MAIN CONTENT ===== */
        .main-wrapper {
            margin-left: 260px;
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
        }

        .header__user {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            font-size: 14px;
        }

        .header__user i {
            font-size: 20px;
            color: var(--text-secondary);
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
            box-shadow: var(--shadow);
            margin-bottom: 24px;
        }

        .card-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--text-primary);
        }

        /* Forms & Buttons */
        .btn {
            background-color: var(--primary);
            color: #FFFFFF;
            border: none;
            border-radius: var(--radius-elem);
            padding: 10px 20px;
            font-weight: 600;
            font-size: 13.5px;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn:hover {
            background-color: var(--primary-dark);
        }

        .btn--danger {
            background-color: #EF4444;
        }

        .btn--danger:hover {
            background-color: #DC2626;
        }

        .btn--secondary {
            background-color: #6B7280;
        }

        .btn--secondary:hover {
            background-color: #555D6C;
        }

        .btn--outline {
            background-color: transparent;
            color: var(--text-secondary);
            border: 1px solid var(--border);
        }

        .btn--outline:hover {
            background-color: #F3F4F6;
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
            box-shadow: 0 0 0 3px rgba(10, 91, 255, 0.15);
        }

        /* Tables */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            border: 1px solid var(--border);
            border-radius: var(--radius-elem);
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 14px;
        }

        .table th {
            background-color: #F9FAFB;
            padding: 14px 20px;
            font-weight: 700;
            color: var(--text-secondary);
            border-bottom: 1px solid var(--border);
        }

        .table td {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            color: var(--text-primary);
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        /* Badge status */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .badge--success { background-color: #D1FAE5; color: #065F46; }
        .badge--warning { background-color: #FEF3C7; color: #92400E; }
        .badge--danger { background-color: #FEE2E2; color: #991B1B; }

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
        }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <aside class="sidebar" id="adminSidebar">
        <a href="<?= url('admin') ?>" class="sidebar__logo">
            <i class="fa-solid fa-laptop-code"></i> Tech<span>Pilot</span> Admin
        </a>
        <ul class="sidebar__menu">
            <li class="sidebar__menu-item <?= $activeMenu === 'dashboard' ? 'active' : '' ?>">
                <a href="<?= url('admin') ?>"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
            </li>
            <li class="sidebar__menu-item <?= $activeMenu === 'categories' ? 'active' : '' ?>">
                <a href="<?= url('admin/categories') ?>"><i class="fa-solid fa-list-ul"></i> Danh mục</a>
            </li>
            <li class="sidebar__menu-item <?= $activeMenu === 'brands' ? 'active' : '' ?>">
                <a href="<?= url('admin/brands') ?>"><i class="fa-solid fa-copyright"></i> Thương hiệu</a>
            </li>
            <li class="sidebar__menu-item <?= $activeMenu === 'products' ? 'active' : '' ?>">
                <a href="<?= url('admin/products') ?>"><i class="fa-solid fa-box-open"></i> Sản phẩm</a>
            </li>
            <li class="sidebar__menu-item <?= $activeMenu === 'orders' ? 'active' : '' ?>">
                <a href="<?= url('admin/orders') ?>"><i class="fa-solid fa-receipt"></i> Đơn hàng</a>
            </li>
            <li class="sidebar__menu-item <?= $activeMenu === 'users' ? 'active' : '' ?>">
                <a href="<?= url('admin/users') ?>"><i class="fa-solid fa-users"></i> Khách hàng</a>
            </li>
            <li class="sidebar__menu-item <?= $activeMenu === 'reviews' ? 'active' : '' ?>">
                <a href="<?= url('admin/reviews') ?>"><i class="fa-solid fa-star-half-stroke"></i> Đánh giá</a>
            </li>
            <li class="sidebar__menu-item <?= $activeMenu === 'flash-sales' ? 'active' : '' ?>">
                <a href="<?= url('admin/flash-sales') ?>"><i class="fa-solid fa-bolt"></i> Flash Sale</a>
            </li>
            <li class="sidebar__menu-item <?= $activeMenu === 'coupons' ? 'active' : '' ?>">
                <a href="<?= url('admin/coupons') ?>"><i class="fa-solid fa-ticket-simple"></i> Mã giảm giá</a>
            </li>
            <li class="sidebar__menu-item <?= $activeMenu === 'banners' ? 'active' : '' ?>">
                <a href="<?= url('admin/banners') ?>"><i class="fa-solid fa-images"></i> Banners</a>
            </li>
            <li class="sidebar__menu-item <?= $activeMenu === 'posts' ? 'active' : '' ?>">
                <a href="<?= url('admin/posts') ?>"><i class="fa-solid fa-newspaper"></i> Tin tức</a>
            </li>
        </ul>
        <div class="sidebar__footer">
            <form method="post" action="<?= url('auth/logout') ?>" style="width: 100%;">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn--danger btn--block" style="width: 100%;"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</button>
            </form>
        </div>
    </aside>

    <!-- MAIN WRAPPER -->
    <div class="main-wrapper">
        <header class="header">
            <button class="header__toggle-sidebar" id="toggleSidebarBtn"><i class="fa-solid fa-bars"></i></button>
            <h2><?= e($pageTitle ?? 'Quản trị hệ thống') ?></h2>
            <div class="header__user">
                <i class="fa-solid fa-circle-user"></i>
                <span><?= e($_SESSION['user']['full_name'] ?? 'Admin') ?></span>
            </div>
        </header>
        <main class="content">
            <!-- Flash notification messages -->
            <?php if (!empty($_SESSION['flashes'])): ?>
                <?php foreach (pullFlashes() as $f): ?>
                    <div class="alert alert--<?= e($f['type']) ?>" style="margin-bottom: 20px; padding: 12px 16px; border-radius: 6px; font-size: 13.5px; font-weight: 600; display: flex; align-items: center; gap: 10px; background-color: <?= $f['type'] === 'success' ? '#D1FAE5' : '#FEE2E2' ?>; color: <?= $f['type'] === 'success' ? '#065F46' : '#991B1B' ?>; border: 1px solid <?= $f['type'] === 'success' ? '#10B981' : '#F87171' ?>;">
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

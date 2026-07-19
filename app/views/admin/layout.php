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
            --primary: #3B82F6;
            --primary-gradient: linear-gradient(135deg, #3B82F6, #1D4ED8);
            --primary-light: rgba(59, 130, 246, 0.08);
            --bg-body: #F8FAFC;
            --bg-sidebar: #0F172A;
            --bg-card: #FFFFFF;
            --text-primary: #0F172A;
            --text-secondary: #64748B;
            --border: #E2E8F0;
            --radius-card: 16px;
            --radius-elem: 10px;
            --shadow-card: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -2px rgba(0, 0, 0, 0.02), 0 10px 15px -3px rgba(15, 23, 42, 0.03);
            --shadow-focus: 0 0 0 4px rgba(59, 130, 246, 0.15);
            --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
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
            letter-spacing: -0.01em;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #CBD5E1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94A3B8;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 270px;
            background-color: var(--bg-sidebar);
            color: #FFFFFF;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            position: fixed;
            top: 16px;
            bottom: 16px;
            left: 16px;
            z-index: 100;
            border-radius: 20px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            transition: var(--transition);
        }

        .sidebar__logo {
            padding: 28px 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            font-weight: 800;
            font-size: 21px;
            color: #FFFFFF;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            letter-spacing: -0.02em;
        }

        .sidebar__logo i {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 24px;
        }

        .sidebar__logo span {
            color: var(--primary);
        }

        .sidebar__menu {
            list-style: none;
            padding: 24px 16px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            flex: 1;
            overflow-y: auto;
        }

        .sidebar__menu-item a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #94A3B8;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
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
            background-color: rgba(255, 255, 255, 0.04);
        }

        .sidebar__menu-item.active a {
            color: #FFFFFF;
            background: var(--primary-gradient);
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .sidebar__menu-item.active a i {
            color: #FFFFFF;
        }

        .sidebar__footer {
            padding: 20px 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .admin-profile-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: var(--radius-elem);
        }

        .admin-profile-card img {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(59, 130, 246, 0.4);
        }

        .admin-profile-info {
            display: flex;
            flex-direction: column;
        }

        .admin-profile-name {
            font-weight: 600;
            font-size: 13.5px;
            color: #FFFFFF;
        }

        .admin-profile-role {
            font-size: 11px;
            color: #64748B;
        }

        /* ===== MAIN CONTENT ===== */
        .main-wrapper {
            margin-left: 302px;
            margin-right: 16px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            transition: var(--transition);
        }

        .header {
            background-color: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--border);
            border-radius: 16px;
            height: 70px;
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 16px;
            z-index: 90;
            box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.02);
            margin-top: 16px;
            margin-bottom: 24px;
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
            transition: var(--transition);
        }

        .header__toggle-sidebar:hover {
            background-color: var(--primary-light);
            color: var(--primary);
        }

        .header h2 {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.02em;
        }

        .header__user {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            font-size: 14px;
            padding: 6px 12px;
            background: #F1F5F9;
            border-radius: 9999px;
            transition: var(--transition);
            cursor: pointer;
        }

        .header__user:hover {
            background: #E2E8F0;
        }

        .header__user i {
            font-size: 18px;
            color: var(--primary);
        }

        .content {
            flex: 1;
            padding-bottom: 40px;
        }

        /* ===== UTILITIES & CARDS ===== */
        .card {
            background-color: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-card);
            padding: 28px;
            box-shadow: var(--shadow-card);
            margin-bottom: 24px;
            transition: var(--transition);
        }

        .card:hover {
            box-shadow: 0 10px 20px -3px rgba(15, 23, 42, 0.04);
        }

        .card-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--text-primary);
            letter-spacing: -0.01em;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Forms & Buttons */
        .btn {
            background: var(--primary-gradient);
            color: #FFFFFF;
            border: none;
            border-radius: var(--radius-elem);
            padding: 10px 18px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            box-shadow: 0 4px 10px rgba(59, 130, 246, 0.15);
        }

        .btn:hover {
            opacity: 0.95;
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(59, 130, 246, 0.25);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn--danger {
            background: linear-gradient(135deg, #EF4444, #B91C1C);
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.15);
        }

        .btn--danger:hover {
            box-shadow: 0 6px 14px rgba(239, 68, 68, 0.25);
        }

        .btn--secondary {
            background: linear-gradient(135deg, #64748B, #475569);
            box-shadow: 0 4px 10px rgba(100, 116, 139, 0.15);
        }

        .btn--secondary:hover {
            box-shadow: 0 6px 14px rgba(100, 116, 139, 0.25);
        }

        .btn--outline {
            background: transparent;
            color: var(--text-secondary);
            border: 1px solid var(--border);
            box-shadow: none;
        }

        .btn--outline:hover {
            background-color: #F1F5F9;
            color: var(--text-primary);
            border-color: #CBD5E1;
            box-shadow: none;
            transform: none;
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
            padding: 11px 16px;
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
            font-size: 14px;
        }

        .table th {
            background-color: #F8FAFC;
            padding: 14px 20px;
            font-weight: 700;
            color: var(--text-secondary);
            border-bottom: 1px solid var(--border);
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.05em;
        }

        .table td {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            color: var(--text-primary);
            transition: var(--transition);
        }

        .table tr {
            transition: var(--transition);
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
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 11.5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .badge--success { background-color: #DCFCE7; color: #15803D; }
        .badge--warning { background-color: #FEF3C7; color: #B45309; }
        .badge--danger { background-color: #FEE2E2; color: #B91C1C; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-290px);
                left: 10px;
                top: 10px;
                bottom: 10px;
            }
            .sidebar.open {
                transform: translateX(0);
                box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25);
            }
            .main-wrapper {
                margin-left: 16px;
                margin-right: 16px;
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
            <div class="admin-profile-card">
                <img src="<?= url('assets/images/logo.png') ?>" alt="Admin Avatar" onerror="this.src='https://ui-avatars.com/api/?name=Admin&background=0A5BFF&color=fff'">
                <div class="admin-profile-info">
                    <span class="admin-profile-name"><?= e($_SESSION['user']['full_name'] ?? 'Quản trị viên') ?></span>
                    <span class="admin-profile-role">TechPilot Administrator</span>
                </div>
            </div>
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
                <h2><?= e($pageTitle ?? 'Quản trị hệ thống') ?></h2>
            </div>
            <div class="header__user">
                <i class="fa-solid fa-circle-user"></i>
                <span><?= e($_SESSION['user']['full_name'] ?? 'Admin') ?></span>
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

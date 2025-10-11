<?php
/**
 * Admin Layout
 * Professional admin interface layout
 */

// 从 Cookie 读取偏好（服务器端直接作用，避免 FOUC）
$cookies   = $this->request->getCookieParams();
$theme     = $cookies['pref_theme']      ?? 'auto';     // auto | light | dark
$contrast  = $cookies['pref_contrast']   ?? 'normal';   // normal | high
$fontScale = (float)($cookies['pref_font_scale'] ?? 1.0);

$bodyClasses = [];
if ($theme === 'dark')  $bodyClasses[] = 'theme-dark';
if ($theme === 'light') $bodyClasses[] = 'theme-light';
if ($contrast === 'high') $bodyClasses[] = 'hc'; // 高对比
$bodyClass = implode(' ', $bodyClasses);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->fetch('title') ? $this->fetch('title') . ' - ' : '' ?>Admin - Curd & Culture</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= $this->Url->webroot('favicon.ico') ?>">

    <!-- CSS -->
    <?= $this->Html->css(['normalize.min', 'milligram.min', 'fonts', 'app', 'home']) ?>

    <!-- Custom Admin Styles -->
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            margin: 0;
        }

        .admin-layout { display: flex; min-height: 100vh; }
        .admin-sidebar {
            width: 280px; background: #1f2937; color: white;
            flex-shrink: 0; display: flex; flex-direction: column;
        }
        .admin-main   { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
        .admin-header {
            background: white; border-bottom: 1px solid #e5e7eb;
            padding: 1rem 2rem; display: flex; align-items: center; justify-content: space-between;
        }
        .admin-content { flex: 1; overflow-y: auto; background: #f8fafc; }

        /* Sidebar */
        .sidebar-brand { padding: 2rem 1.5rem; border-bottom: 1px solid #374151; }
        .brand-text    { font-size: 1.25rem; font-weight: 700; color: white; text-decoration: none; }
        .sidebar-nav   { flex: 1; padding: 1rem 0; }
        .nav-section   { margin-bottom: 2rem; }
        .nav-title     {
            padding: 0 1.5rem; font-size: 0.75rem; font-weight: 600; color: #9ca3af;
            text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;
        }
        .nav-link {
            display: flex; align-items: center; padding: 0.75rem 1.5rem;
            color: #d1d5db; text-decoration: none; transition: all 0.2s; border-left: 3px solid transparent;
        }
        .nav-link i { margin-right: 0.75rem; width: 1.25rem; text-align: center; }
        .nav-link:hover { background: #374151; color: white; border-left-color: #6b7280; }
        .nav-link.active { background: #374151; color: white; border-left-color: #3b82f6; }

        /* Header */
        .header-title   { font-size: 1.5rem; font-weight: 600; color: #111827; margin: 0; }
        .header-actions { display: flex; align-items: center; gap: 1rem; }
        .header-user    { display: flex; align-items: center; gap: 0.5rem; color: #6b7280; }

        /* Buttons */
        .btn {
            display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem;
            border-radius: 0.375rem; font-size: 0.875rem; font-weight: 500;
            text-decoration: none; border: 1px solid transparent; cursor: pointer; transition: all 0.2s;
        }
        .btn-primary { background: #3b82f6; color: white; }
        .btn-primary:hover { background: #2563eb; }
        .btn-outline { background: white; color: #374151; border-color: #d1d5db; }
        .btn-outline:hover { background: #f9fafb; }

        /* Icons*/
        .icon-home::before { content: '🏠'; }
        .icon-message::before { content: '💬'; }
        .icon-package::before { content: '📦'; }
        .icon-shopping-cart::before { content: '🛒'; }
        .icon-users::before { content: '👥'; }
        .icon-bar-chart::before { content: '📊'; }
        .icon-settings::before { content: '⚙️'; }
        .icon-log-out::before { content: '🚪'; }

        /* ========== High Contrast（更强对比） ========== */
        .hc { filter: none; } /* 移除滤镜，改用明确的颜色与边框保证可读性 */
        .hc, .hc * { text-shadow: none !important; }
        .hc .admin-sidebar { background: #000 !important; color: #fff !important; }
        .hc .admin-header  { background: #fff !important; border-bottom: 2px solid #000 !important; }
        .hc .nav-link      { color: #fff !important; border-left: 3px solid transparent !important; }
        .hc .nav-link:hover, .hc .nav-link.active { background: #333 !important; border-left-color: #fff !important; }
        .hc .header-title, .hc .header-user { color: #000 !important; }
        .hc .btn-primary { background: #000 !important; border-color: #000 !important; color: #fff !important; }
        .hc a { color: #000 !important; text-decoration: underline; }

        /* ========== Theme: Dark ========== */
        .theme-dark { background: #1a1a1a; color: #e5e5e5; }
        .theme-dark .admin-header { background: #2d2d2d; border-bottom-color: #404040; }
        .theme-dark .admin-content { background: #1a1a1a; }

        /* Responsive */
        @media (max-width: 1024px) { .admin-sidebar { width: 250px; } }
        @media (max-width: 768px) {
            .admin-layout { flex-direction: column; }
            .admin-sidebar { width: 100%; height: auto; order: 2; }
            .sidebar-nav { display: flex; overflow-x: auto; padding: 0.5rem; }
            .nav-section { display: flex; margin: 0; gap: 0.5rem; }
            .nav-title { display: none; }
            .nav-link { white-space: nowrap; border-left: none; border-bottom: 3px solid transparent; }
            .nav-link:hover, .nav-link.active { border-left: none; border-bottom-color: #3b82f6; }
        }
        /* ============================
   High Contrast – global tokens
   Scope: .hc on <body> or .page
   ============================ */
        .hc {
            /* dark surfaces + bright text with AAA-ish contrast on common UIs */
            --hc-bg:        #0b111b;    /* page background */
            --hc-surface:   #0f172a;    /* cards / panels / inputs */
            --hc-border:    #3b455a;    /* neutral borders */
            --hc-text:      #f5f7fa;    /* body text */
            --hc-muted:     #cdd6e1;    /* secondary text */
            --hc-link:      #9dd1ff;    /* links (always underlined) */
            --hc-primary:   #5fb0ff;    /* primary button/brand */
            --hc-primary-t: #08101b;    /* primary text on button */
            --hc-accent:    #ffd166;    /* focus ring/alerts accent */
            --hc-danger:    #ff6b6b;
            --hc-success:   #22d3a6;
            color-scheme: dark;
        }

        /* Base & typography */
        .hc,
        .hc .page,
        .hc body {
            background: var(--hc-bg) !important;
            color: var(--hc-text) !important;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
        }
        .hc * { text-shadow: none !important; }

        /* Headings slightly heavier for legibility */
        .hc h1, .hc h2, .hc h3, .hc .page-title { color: var(--hc-text); font-weight: 750; }
        .hc .text-muted, .hc .hint, .hc .form-text, .hc .small { color: var(--hc-muted) !important; }

        /* Links: brighter + underline with offset for clarity */
        .hc a { color: var(--hc-link) !important; text-decoration: underline; text-underline-offset: 2px; }
        .hc a:hover { filter: brightness(1.08); }

        /* Cards / panels (Customer/Admin/Settings/Auth shared) */
        .hc .card,
        .hc .group,
        .hc .settings-card,
        .hc .auth-card,
        .hc .sec-box,
        .hc .admin-content .card,
        .hc .dashboard-content .card {
            background: var(--hc-surface) !important;
            border: 1px solid var(--hc-border) !important;
            color: var(--hc-text) !important;
        }
        .hc .card-header,
        .hc .group__title { background: transparent; color: var(--hc-text); border-bottom: 1px solid var(--hc-border); }

        /* Inputs / selects / textareas (Bootstrap + custom) */
        .hc .form-control,
        .hc .form-select,
        .hc select.input,
        .hc .auth-input,
        .hc input[type="text"],
        .hc input[type="email"],
        .hc input[type="password"],
        .hc input[type="tel"],
        .hc textarea {
            background: var(--hc-surface) !important;
            color: var(--hc-text) !important;
            border: 1px solid var(--hc-border) !important;
        }
        .hc .form-control::placeholder,
        .hc .auth-input::placeholder { color: #a7b1c0 !important; }

        /* Toggles / switches (Bootstrap & custom) */
        .hc .form-check-input { background-color: #0b1220; border-color: var(--hc-border); }
        .hc .form-check-input:checked { background-color: var(--hc-primary); border-color: var(--hc-primary); }

        /* Buttons */
        .hc .btn {
            background: #141c2b;
            color: var(--hc-text);
            border: 1px solid var(--hc-border);
        }
        .hc .btn:hover { filter: brightness(1.08); }
        .hc .btn.btn-primary,
        .hc .btn-primary {
            background: var(--hc-primary) !important;
            border-color: var(--hc-primary) !important;
            color: var(--hc-primary-t) !important;
            font-weight: 700;
        }
        .hc .btn.btn-outline,
        .hc .btn-outline-secondary,
        .hc .btn-ghost {
            background: transparent !important;
            color: var(--hc-text) !important;
            border-color: var(--hc-border) !important;
        }

        /* Focus visibility – keyboard friendly, thick & offset ring */
        .hc a:focus-visible,
        .hc button:focus-visible,
        .hc .btn:focus-visible,
        .hc .form-control:focus,
        .hc .form-select:focus,
        .hc select.input:focus,
        .hc .auth-input:focus {
            outline: 3px solid var(--hc-accent) !important;
            outline-offset: 2px !important;
            box-shadow: none !important;
        }

        /* Sidebar (Customer/Admin) */
        .hc .dashboard-sidebar,
        .hc .admin-sidebar {
            background: #060a12 !important;
            border-right: 1px solid var(--hc-border);
        }
        .hc .dashboard-nav .nav-link,
        .hc .admin-sidebar .nav-link {
            color: var(--hc-muted) !important;
            border-left: 3px solid transparent;
        }
        .hc .dashboard-nav .nav-link:hover,
        .hc .admin-sidebar .nav-link:hover {
            background: #0f172a !important;
            color: var(--hc-text) !important;
            border-left-color: var(--hc-border);
        }
        .hc .dashboard-nav .nav-link.active,
        .hc .admin-sidebar .nav-link.active {
            background: #132033 !important;
            color: var(--hc-text) !important;
            border-left-color: var(--hc-primary);
            font-weight: 700;
        }

        /* Top bar buttons in default layout */
        .hc .topbar { background: #0f172a !important; border-color: var(--hc-border) !important; }
        .hc .topbar .btn { background: #131c2c; color: var(--hc-text); border-color: var(--hc-border); }
        .hc .topbar .btn.btn-primary { background: var(--hc-primary); color: var(--hc-primary-t); border-color: var(--hc-primary); }

        /* Range slider knob is clearly visible */
        .hc input[type="range"]::-webkit-slider-thumb { background: var(--hc-primary); }
        .hc input[type="range"]::-moz-range-thumb { background: var(--hc-primary); }
        .hc input[type="range"]::-webkit-slider-runnable-track,
        .hc input[type="range"]::-moz-range-track { background: #22304a; }

        /* Alerts/badges */
        .hc .alert-info    { background:#0f2236; border-color:#284b72; color:#cfe8ff; }
        .hc .alert-success { background:#072b27; border-color:#116f62; color:#bef5ea; }
        .hc .alert-danger  { background:#3a0b13; border-color:#7a1b2b; color:#ffdfe3; }
        .hc .badge.bg-primary { background: var(--hc-primary) !important; color: var(--hc-primary-t) !important; }

        /* Tables (if any) */
        .hc .table { color: var(--hc-text); }
        .hc .table thead { color: var(--hc-text); border-bottom: 1px solid var(--hc-border); }
        .hc .table tbody tr { border-color: var(--hc-border); }
        .hc .table tbody tr:hover { background: #132033; }

        /* Small separators/HR */
        .hc hr { border-color: var(--hc-border); }

        /* Make tiny helper text a hair larger for legibility */
        @media (min-width: 0) {
            .hc .form-text, .hc .hint, .hc .small { font-size: 0.95em; }
        }

    </style>


    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>


<body class="<?= h($bodyClass) ?>" style="font-size: calc(16px * <?= h($fontScale) ?>);">
<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="sidebar-brand">
            <?= $this->Html->link(
                'Curd & Culture Admin',
                ['prefix' => 'Admin', 'controller' => 'Dashboard', 'action' => 'index'],
                ['class' => 'brand-text']
            ) ?>
        </div>

        <?php
        $currentController = $this->request->getParam('controller');
        $currentAction     = $this->request->getParam('action');
        ?>
        <nav class="sidebar-nav">

            <div class="nav-section">
                <div class="nav-title">Main</div>
                <?= $this->Html->link(
                    '<i class="icon-home"></i>Dashboard',
                    ['prefix' => 'Admin', 'controller' => 'Dashboard', 'action' => 'index'],
                    ['class' => 'nav-link' . ($currentController === 'Dashboard' ? ' active' : ''), 'escape' => false]
                ) ?>
            </div>

            <div class="nav-section">
                <div class="nav-title">Management</div>
                <?= $this->Html->link(
                    '<i class="icon-message"></i>Customer Inquiries',
                    ['prefix' => 'Admin', 'controller' => 'ContactMessages', 'action' => 'index'],
                    ['class' => 'nav-link' . ($currentController === 'ContactMessages' ? ' active' : ''), 'escape' => false]
                ) ?>
                <?= $this->Html->link(
                    '<i class="icon-package"></i>Products',
                    ['prefix' => 'Admin', 'controller' => 'Products', 'action' => 'index'],
                    ['class' => 'nav-link' . ($currentController === 'Products' ? ' active' : ''), 'escape' => false]
                ) ?>
                <?= $this->Html->link(
                    '<i class="icon-shopping-cart"></i>Orders',
                    ['prefix' => 'Admin', 'controller' => 'Orders', 'action' => 'index'],
                    ['class' => 'nav-link' . ($currentController === 'Orders' && $currentAction !== 'analytics' ? ' active' : ''), 'escape' => false]
                ) ?>
                <?= $this->Html->link(
                    '<i>🚚</i> Deliveries',
                    ['prefix' => 'Admin', 'controller' => 'Deliveries', 'action' => 'index'],
                    ['class' => 'nav-link' . ($currentController === 'Deliveries' ? ' active' : ''), 'escape' => false]
                ) ?>
                <?= $this->Html->link(
                    '<i>📍</i> Pickups',
                    ['prefix' => 'Admin', 'controller' => 'Pickups', 'action' => 'index'],
                    ['class' => 'nav-link' . ($currentController === 'Pickups' ? ' active' : ''), 'escape' => false]
                ) ?>
                <?= $this->Html->link(
                    '<i>📅</i> Delivery Slots',
                    ['prefix' => 'Admin', 'controller' => 'DeliverySlots', 'action' => 'index'],
                    ['class' => 'nav-link' . ($currentController === 'DeliverySlots' ? ' active' : ''), 'escape' => false]
                ) ?>
            </div>

            <div class="nav-section">
                <div class="nav-title">Analytics</div>
                <?= $this->Html->link(
                    '<i class="icon-bar-chart"></i>Reports',
                    ['prefix' => 'Admin', 'controller' => 'Orders', 'action' => 'analytics'],
                    ['class' => 'nav-link' . ($currentController === 'Orders' && $currentAction === 'analytics' ? ' active' : ''), 'escape' => false]
                ) ?>
            </div>

            <div class="nav-section">
                <div class="nav-title">System</div>
                <?= $this->Html->link(
                    '<i class="icon-settings"></i>Settings',
                    ['prefix' => 'Admin', 'controller' => 'Settings', 'action' => 'index'],
                    ['class' => 'nav-link' . ($currentController === 'Settings' ? ' active' : ''), 'escape' => false]
                ) ?>
                <?= $this->Html->link(
                    '<i class="icon-log-out"></i>Back to Site',
                    ['prefix' => false, 'controller' => 'Pages', 'action' => 'display', 'home'],
                    ['class' => 'nav-link', 'escape' => false]
                ) ?>
            </div>

        </nav>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
        <header class="admin-header">
            <h1 class="header-title"><?= $this->fetch('title') ?: 'Admin Panel' ?></h1>
            <div class="header-actions">
                <div class="header-user">Welcome, Admin</div>
                <?= $this->Html->link(
                    'View Site',
                    ['prefix' => false, 'controller' => 'Pages', 'action' => 'display', 'home'],
                    ['class' => 'btn btn-outline', 'target' => '_blank']
                ) ?>
            </div>
        </header>

        <div class="admin-content">
            <?= $this->Flash->render() ?>
            <?= $this->fetch('content') ?>
        </div>
    </main>
</div>
</body>
</html>

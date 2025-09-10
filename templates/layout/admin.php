<?php
/**
 * Admin Layout
 * Professional admin interface layout
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->fetch('title') ? $this->fetch('title') . ' - ' : '' ?>Admin - Curd & Culture</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= $this->Url->webroot('favicon.ico') ?>">

    <!-- CSS -->
    <?= $this->Html->css(['normalize.min', 'milligram.min', 'fonts', 'app', 'home']) ?>

    <!-- Custom Admin Styles -->
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            margin: 0;
        }

        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        .admin-sidebar {
            width: 280px;
            background: #1f2937;
            color: white;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
        }

        .admin-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .admin-header {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .admin-content {
            flex: 1;
            overflow-y: auto;
            background: #f8fafc;
        }

        /* Sidebar Styles */
        .sidebar-brand {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid #374151;
        }

        .brand-text {
            font-size: 1.25rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
        }

        .sidebar-nav {
            flex: 1;
            padding: 1rem 0;
        }

        .nav-section {
            margin-bottom: 2rem;
        }

        .nav-title {
            padding: 0 1.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: #d1d5db;
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .nav-link:hover {
            background: #374151;
            color: white;
            border-left-color: #6b7280;
        }

        .nav-link.active {
            background: #374151;
            color: white;
            border-left-color: #3b82f6;
        }

        .nav-link i {
            margin-right: 0.75rem;
            width: 1.25rem;
            text-align: center;
        }

        /* Header Styles */
        .header-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #111827;
            margin: 0;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-user {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #6b7280;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
        }

        .btn-outline {
            background: white;
            color: #374151;
            border-color: #d1d5db;
        }

        .btn-outline:hover {
            background: #f9fafb;
        }

        /* Icons */
        .icon-home::before { content: '🏠'; }
        .icon-message::before { content: '💬'; }
        .icon-package::before { content: '📦'; }
        .icon-shopping-cart::before { content: '🛒'; }
        .icon-users::before { content: '👥'; }
        .icon-bar-chart::before { content: '📊'; }
        .icon-settings::before { content: '⚙️'; }
        .icon-log-out::before { content: '🚪'; }

        /* Responsive */
        @media (max-width: 1024px) {
            .admin-sidebar {
                width: 250px;
            }
        }

        @media (max-width: 768px) {
            .admin-layout {
                flex-direction: column;
            }

            .admin-sidebar {
                width: 100%;
                height: auto;
                order: 2;
            }

            .sidebar-nav {
                display: flex;
                overflow-x: auto;
                padding: 0.5rem;
            }

            .nav-section {
                display: flex;
                margin: 0;
                gap: 0.5rem;
            }

            .nav-title {
                display: none;
            }

            .nav-link {
                white-space: nowrap;
                border-left: none;
                border-bottom: 3px solid transparent;
            }

            .nav-link:hover,
            .nav-link.active {
                border-left: none;
                border-bottom-color: #3b82f6;
            }
        }
    </style>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>

</head>
<body>
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
                        [
                            'class' => 'nav-link' . ($this->request->getParam('controller') === 'Dashboard' ? ' active' : ''),
                            'escape' => false
                        ]
                    ) ?>
                </div>

                <div class="nav-section">
                    <div class="nav-title">Management</div>
                    <?= $this->Html->link(
                        '<i class="icon-message"></i>Customer Inquiries',
                        ['prefix' => 'Admin', 'controller' => 'ContactMessages', 'action' => 'index'],
                        [
                            'class' => 'nav-link' . ($this->request->getParam('controller') === 'ContactMessages' ? ' active' : ''),
                            'escape' => false
                        ]
                    ) ?>
                    <?= $this->Html->link(
                        '<i class="icon-package"></i>Products',
                        ['prefix' => 'Admin', 'controller' => 'Products', 'action' => 'index'],
                        [
                            'class' => 'nav-link' . ($this->request->getParam('controller') === 'Products' ? ' active' : ''),
                            'escape' => false
                        ]
                    ) ?>
                    <?= $this->Html->link(
                        '<i class="icon-shopping-cart"></i>Orders',
                        ['prefix' => 'Admin', 'controller' => 'Orders', 'action' => 'index'],
                        [
                            'class' => 'nav-link' . (
                                $currentController === 'Orders'
                                && $currentAction !== 'analytics'
                                    ? ' active' : ''
                                ),
                            'escape' => false
                        ]
                    ) ?>

                    <?= $this->Html->link(
                        '<i class="icon-users"></i>Users',
                        ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'index'],
                        [
                            'class' => 'nav-link' . ($this->request->getParam('controller') === 'Users' ? ' active' : ''),
                            'escape' => false
                        ]
                    ) ?>
                </div>

                <div class="nav-section">
                    <div class="nav-title">Analytics</div>
                    <?= $this->Html->link(
                        '<i class="icon-bar-chart"></i>Reports',
                        ['prefix' => 'Admin', 'controller' => 'Orders', 'action' => 'analytics'],
                        [
                            'class'  => 'nav-link' . (
                                ($currentController === 'Orders' && $currentAction === 'analytics')
                                    ? ' active' : ''
                                ),
                            'escape' => false
                        ]
                    ) ?>

                </div>

                <div class="nav-section">
                    <div class="nav-title">System</div>
                    <?= $this->Html->link(
                        '<i class="icon-settings"></i>Settings',
                        ['prefix' => 'Admin', 'controller' => 'Settings', 'action' => 'index'],
                        [
                            'class' => 'nav-link' . ($this->request->getParam('controller') === 'Settings' ? ' active' : ''),
                            'escape' => false
                        ]
                    ) ?>
                    <?= $this->Html->link(
                        '<i class="icon-log-out"></i>Back to Site',
                        ['prefix' => false, 'controller' => 'Pages', 'action' => 'display', 'home'],
                        [
                            'class' => 'nav-link',
                            'escape' => false
                        ]
                    ) ?>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <h1 class="header-title"><?= $this->fetch('title') ?: 'Admin Panel' ?></h1>
                <div class="header-actions">
                    <div class="header-user">
                        Welcome, Admin
                    </div>
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

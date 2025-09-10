<?php
$cakeDescription = 'Curd & Culture - Customer Dashboard';
?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?= $cakeDescription ?>:
        <?= $this->fetch('title') ?>
    </title>
    <?= $this->Html->meta('icon') ?>

    <link href="https://fonts.googleapis.com/css?family=Raleway:400,700" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">

    <?= $this->Html->css(['normalize.min', 'milligram.min', 'fonts', 'cake']) ?>
    <style>
        .dashboard-sidebar {
            background-color: #f8f9fa;
            min-height: 100vh;
            border-right: 1px solid #dee2e6;
        }
        .dashboard-nav .nav-link {
            color: #495057;
            padding: 1rem 1.5rem;
            border-radius: 0;
            margin-bottom: 0.25rem;
        }
        .dashboard-nav .nav-link:hover,
        .dashboard-nav .nav-link.active {
            background-color: #e9ecef;
            color: #212529;
        }
        .dashboard-nav .nav-link i {
            margin-right: 0.5rem;
            width: 1.25rem;
        }
        .dashboard-content {
            padding: 2rem;
        }
        .order-card {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .order-card:hover {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-color: #adb5bd;
        }
        .order-status {
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .status-pending { background-color: #fff3cd; color: #664d03; }
        .status-confirmed { background-color: #cff4fc; color: #055160; }
        .status-processing { background-color: #e2e3e5; color: #41464b; }
        .status-shipped { background-color: #d1ecf1; color: #0c5460; }
        .status-delivered { background-color: #d4edda; color: #155724; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }
        
        .btn-buy-again {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: white;
            padding: 0.375rem 0.75rem;
            border-radius: 0.25rem;
            text-decoration: none;
            font-size: 0.875rem;
        }
        .btn-buy-again:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
            color: white;
        }
    </style>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 dashboard-sidebar">
                <div class="d-flex flex-column">
                    <div class="p-3 border-bottom">
                        <h5 class="mb-0">Customer Dashboard</h5>
                        <small class="text-muted">
                            Welcome, <?= h($this->request->getAttribute('identity')->get('name') ?: $this->request->getAttribute('identity')->get('email')) ?>
                        </small>
                    </div>
                    
                    <nav class="dashboard-nav nav nav-pills flex-column">
                        <?= $this->Html->link(
                            '<i class="bi bi-house"></i>Home',
                            ['controller' => 'Pages', 'action' => 'display', 'home'],
                            [
                                'class' => 'nav-link',
                                'escape' => false
                            ]
                        ) ?>
                        
                        <?= $this->Html->link(
                            '<i class="bi bi-shop"></i>Shop',
                            ['controller' => 'Products', 'action' => 'index'],
                            [
                                'class' => 'nav-link',
                                'escape' => false
                            ]
                        ) ?>
                        
                        <?= $this->Html->link(
                            '<i class="bi bi-box-seam"></i>Orders',
                            ['controller' => 'Customer', 'action' => 'orders'],
                            [
                                'class' => 'nav-link' . ($this->request->getParam('action') === 'orders' || $this->request->getParam('action') === 'orderDetails' ? ' active' : ''),
                                'escape' => false
                            ]
                        ) ?>
                        
                        <?= $this->Html->link(
                            '<i class="bi bi-person"></i>Profile',
                            ['controller' => 'Customer', 'action' => 'profile'],
                            [
                                'class' => 'nav-link' . ($this->request->getParam('action') === 'profile' ? ' active' : ''),
                                'escape' => false
                            ]
                        ) ?>
                        
                        <div class="mt-auto p-3">
                            <?= $this->Html->link(
                                '<i class="bi bi-box-arrow-right"></i>Logout',
                                ['controller' => 'Customer', 'action' => 'logout'],
                                [
                                    'class' => 'nav-link text-danger',
                                    'escape' => false,
                                    'confirm' => 'Are you sure you want to logout?'
                                ]
                            ) ?>
                        </div>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="dashboard-content">
                    <?= $this->Flash->render() ?>
                    <?= $this->fetch('content') ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

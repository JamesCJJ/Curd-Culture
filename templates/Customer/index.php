<?php
$this->extend('/layout/default');
$this->assign('title', 'Dashboard');
?>

<h2><i class="bi bi-house me-2"></i>Welcome to Your Dashboard</h2>

<div class="row">
    <div class="col-md-12">
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            Welcome to your customer dashboard! Use the navigation on the left to access your orders, manage your profile, or continue shopping.
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-box-seam display-4 text-primary mb-3"></i>
                <h5 class="card-title">Your Orders</h5>
                <p class="card-text">View and track your order history, check delivery status, and reorder your favorites.</p>
                <?= $this->Html->link(
                    'View Orders',
                    ['action' => 'orders'],
                    ['class' => 'btn btn-primary']
                ) ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-shop display-4 text-success mb-3"></i>
                <h5 class="card-title">Continue Shopping</h5>
                <p class="card-text">Explore our selection of artisanal cheeses and gourmet products.</p>
                <?= $this->Html->link(
                    'Browse Products',
                    ['controller' => 'Products', 'action' => 'index'],
                    ['class' => 'btn btn-success']
                ) ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-person display-4 text-info mb-3"></i>
                <h5 class="card-title">Manage Profile</h5>
                <p class="card-text">Update your personal information, manage addresses, and account settings.</p>
                <?= $this->Html->link(
                    'Edit Profile',
                    ['action' => 'profile'],
                    ['class' => 'btn btn-info']
                ) ?>
            </div>
        </div>
    </div>
</div>

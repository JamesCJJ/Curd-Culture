<?php
/**
 * Admin Order Edit
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Order $order
 */

$this->assign('title', 'Edit Order #' . $order->id);
?>

<div class="admin-order-edit">
    <div class="page-header">
        <div class="page-header-content">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <?= $this->Html->link('Orders', ['action' => 'index']) ?>
                    </li>
                    <li class="breadcrumb-item">
                        <?= $this->Html->link('Order #' . $order->id, ['action' => 'view', $order->id]) ?>
                    </li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
            <h1 class="page-title">Edit Order #<?= h($order->id) ?></h1>
            <p class="page-subtitle">
                Last modified: <?= $order->modified->format('M j, Y \a\t g:i A') ?>
            </p>
        </div>
        <div class="page-actions">
            <?= $this->Html->link('View Order', ['action' => 'view', $order->id], ['class' => 'btn btn-outline']) ?>
            <?= $this->Html->link('Back to Orders', ['action' => 'index'], ['class' => 'btn btn-outline']) ?>
        </div>
    </div>

    <?= $this->Form->create($order, ['class' => 'order-edit-form']) ?>
    <div class="form-content">
        <div class="form-main">
            <!-- Order Status -->
            <div class="card">
                <div class="card-header">
                    <h3>Order Status</h3>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <?= $this->Form->control('status', [
                                'label' => 'Order Status',
                                'options' => [
                                    'pending' => 'Pending',
                                    'processing' => 'Processing',
                                    'shipped' => 'Shipped',
                                    'delivered' => 'Delivered',
                                    'cancelled' => 'Cancelled'
                                ],
                                'class' => 'form-control'
                            ]) ?>
                        </div>
                        <div class="form-group">
                            <?= $this->Form->control('payment_status', [
                                'label' => 'Payment Status',
                                'options' => [
                                    'unpaid' => 'Unpaid',
                                    'paid' => 'Paid',
                                    'refunded' => 'Refunded'
                                ],
                                'class' => 'form-control'
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="card">
                <div class="card-header">
                    <h3>Customer Information</h3>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <?= $this->Form->control('full_name', [
                                'label' => 'Customer Name',
                                'class' => 'form-control'
                            ]) ?>
                        </div>
                        <div class="form-group">
                            <?= $this->Form->control('email', [
                                'label' => 'Email Address',
                                'type' => 'email',
                                'class' => 'form-control'
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Billing Address -->
            <div class="card">
                <div class="card-header">
                    <h3>Billing Address</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <?= $this->Form->control('address', [
                            'label' => 'Street Address',
                            'class' => 'form-control'
                        ]) ?>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <?= $this->Form->control('city', [
                                'label' => 'City',
                                'class' => 'form-control'
                            ]) ?>
                        </div>
                        <div class="form-group">
                            <?= $this->Form->control('postcode', [
                                'label' => 'Postal Code',
                                'class' => 'form-control'
                            ]) ?>
                        </div>
                        <div class="form-group">
                            <?= $this->Form->control('country', [
                                'label' => 'Country',
                                'class' => 'form-control'
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items (Read-only) -->
            <div class="card">
                <div class="card-header">
                    <h3>Order Items</h3>
                    <p class="card-subtitle">Items cannot be modified after order creation</p>
                </div>
                <div class="card-body">
                    <div class="items-list">
                        <?php foreach ($order->order_items as $item): ?>
                            <div class="item-row">
                                <div class="item-info">
                                    <div class="item-name"><?= h($item->name) ?></div>
                                    <div class="item-details">
                                        Price: <?= h($item->currency) ?> <?= number_format((float)$item->price, 2) ?> each
                                    </div>
                                </div>
                                <div class="item-quantity">
                                    Qty: <?= h($item->qty) ?>
                                </div>
                                <div class="item-total">
                                    <?= h($item->currency) ?> <?= number_format((float)$item->line_total, 2) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-sidebar">
            <!-- Order Summary -->
            <div class="card">
                <div class="card-header">
                    <h3>Order Summary</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <?= $this->Form->control('subtotal', [
                            'label' => 'Subtotal',
                            'type' => 'number',
                            'step' => '0.01',
                            'class' => 'form-control'
                        ]) ?>
                    </div>
                    <div class="form-group">
                        <?= $this->Form->control('shipping_fee', [
                            'label' => 'Shipping Fee',
                            'type' => 'number',
                            'step' => '0.01',
                            'class' => 'form-control'
                        ]) ?>
                    </div>
                    <div class="form-group">
                        <?= $this->Form->control('discount', [
                            'label' => 'Discount',
                            'type' => 'number',
                            'step' => '0.01',
                            'class' => 'form-control'
                        ]) ?>
                    </div>
                    <div class="form-group">
                        <?= $this->Form->control('total', [
                            'label' => 'Total',
                            'type' => 'number',
                            'step' => '0.01',
                            'class' => 'form-control'
                        ]) ?>
                    </div>
                    <div class="form-group">
                        <?= $this->Form->control('currency', [
                            'label' => 'Currency',
                            'class' => 'form-control'
                        ]) ?>
                    </div>
                </div>
            </div>

            <!-- Payment Information -->
            <div class="card">
                <div class="card-header">
                    <h3>Payment Information</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <?= $this->Form->control('payment_method', [
                            'label' => 'Payment Method',
                            'class' => 'form-control'
                        ]) ?>
                    </div>
                    <div class="form-group">
                        <?= $this->Form->control('payment_ref', [
                            'label' => 'Payment Reference',
                            'class' => 'form-control'
                        ]) ?>
                    </div>
                    <?php if ($order->paid_at): ?>
                        <div class="info-row">
                            <strong>Paid At:</strong> <?= $order->paid_at->format('M j, Y \a\t g:i A') ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Order Notes -->
            <div class="card">
                <div class="card-header">
                    <h3>Order Notes</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <?= $this->Form->control('notes', [
                            'label' => 'Internal Notes',
                            'type' => 'textarea',
                            'rows' => 4,
                            'class' => 'form-control'
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <?= $this->Form->button('Save Changes', ['class' => 'btn btn-primary']) ?>
        <?= $this->Html->link('Cancel', ['action' => 'view', $order->id], ['class' => 'btn btn-outline']) ?>
    </div>
    <?= $this->Form->end() ?>
</div>

<style>
.admin-order-edit { max-width: 1200px; margin: 0 auto; padding: 1.25rem 1rem; }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid #e5e7eb; }
.page-title { font-size: 1.6rem; font-weight: 700; margin: 0; }
.page-subtitle { color: #6b7280; margin: 0.25rem 0 0; }
.breadcrumb { display: flex; list-style: none; padding: 0; margin: 0 0 0.5rem 0; }
.breadcrumb-item { margin-right: 0.5rem; }
.breadcrumb-item:not(:last-child)::after { content: '/'; margin-left: 0.5rem; color: #6b7280; }
.form-content { display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; }
.card { background: #fff; border: 1px solid #eef0f3; border-radius: 0.6rem; margin-bottom: 1.5rem; }
.card-header { padding: 1rem 1.5rem; border-bottom: 1px solid #f3f4f6; background: #f9fafb; }
.card-header h3 { margin: 0; font-size: 1.1rem; font-weight: 600; }
.card-subtitle { margin: 0.25rem 0 0; color: #6b7280; font-size: 0.875rem; }
.card-body { padding: 1.5rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.form-group { margin-bottom: 1rem; }
.form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151; }
.form-control { width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem; }
.form-control:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
.items-list { space-y: 0.75rem; }
.item-row { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: #f9fafb; border-radius: 0.375rem; margin-bottom: 0.5rem; }
.item-name { font-weight: 500; margin-bottom: 0.25rem; }
.item-details { color: #6b7280; font-size: 0.875rem; }
.item-quantity { margin: 0 1rem; color: #6b7280; }
.item-total { font-weight: 600; }
.info-row { margin-bottom: 0.5rem; }
.form-actions { display: flex; gap: 1rem; justify-content: flex-end; padding: 1.5rem 0; border-top: 1px solid #e5e7eb; margin-top: 2rem; }
.btn { display: inline-block; padding: 0.5rem 1rem; border-radius: 0.375rem; text-decoration: none; font-weight: 500; border: 1px solid transparent; cursor: pointer; }
.btn-primary { background: #3b82f6; color: white; border-color: #3b82f6; }
.btn-outline { background: white; color: #374151; border-color: #d1d5db; }
.btn:hover { opacity: 0.9; }
@media (max-width: 768px) { 
    .form-content { grid-template-columns: 1fr; } 
    .form-row { grid-template-columns: 1fr; }
    .form-actions { flex-direction: column; }
}
</style>

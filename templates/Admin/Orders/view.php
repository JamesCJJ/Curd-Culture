<?php
/**
 * Admin Order View
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Order $order
 */

$this->assign('title', 'Order #' . $order->id);
?>

<div class="admin-order-view">
    <div class="page-header">
        <div class="page-header-content">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <?= $this->Html->link('Orders', ['action' => 'index']) ?>
                    </li>
                    <li class="breadcrumb-item active">Order #<?= h($order->id) ?></li>
                </ol>
            </nav>
            <h1 class="page-title">Order #<?= h($order->id) ?></h1>
            <p class="page-subtitle">
                Placed on <?= $order->created->format('M j, Y \a\t g:i A') ?>
            </p>
        </div>
        <div class="page-actions">
            <?= $this->Html->link('Edit Order', ['action' => 'edit', $order->id], ['class' => 'btn btn-primary']) ?>
            <?= $this->Html->link('Back to Orders', ['action' => 'index'], ['class' => 'btn btn-outline']) ?>
        </div>
    </div>

    <div class="order-content">
        <div class="order-main">
            <!-- Order Status -->
            <div class="card">
                <div class="card-header">
                    <h3>Order Status</h3>
                </div>
                <div class="card-body">
                    <div class="status-info">
                        <div class="status-badge status-<?= h($order->status) ?>">
                            <?= ucfirst(h($order->status)) ?>
                        </div>
                        <div class="payment-status">
                            Payment: <span class="payment-badge payment-<?= h($order->payment_status) ?>">
                                <?= ucfirst(h($order->payment_status)) ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Quick Status Update -->
                    <div class="status-actions">
                        <?= $this->Form->create(null, ['url' => ['action' => 'updateStatus', $order->id], 'class' => 'status-form']) ?>
                        <div class="form-row">
                            <div class="form-group">
                                <?= $this->Form->select('status', [
                                    'pending' => 'Pending',
                                    'processing' => 'Processing',
                                    'shipped' => 'Shipped',
                                    'delivered' => 'Delivered',
                                    'cancelled' => 'Cancelled'
                                ], [
                                    'value' => $order->status,
                                    'class' => 'form-control'
                                ]) ?>
                            </div>
                            <div class="form-group">
                                <?= $this->Form->select('payment_status', [
                                    'unpaid' => 'Unpaid',
                                    'paid' => 'Paid',
                                    'refunded' => 'Refunded'
                                ], [
                                    'value' => $order->payment_status,
                                    'class' => 'form-control'
                                ]) ?>
                            </div>
                            <div class="form-group">
                                <?= $this->Form->button('Update Status', ['class' => 'btn btn-primary']) ?>
                            </div>
                        </div>
                        <?= $this->Form->end() ?>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="card">
                <div class="card-header">
                    <h3>Order Items</h3>
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

        <div class="order-sidebar">
            <!-- Order Summary -->
            <div class="card">
                <div class="card-header">
                    <h3>Order Summary</h3>
                </div>
                <div class="card-body">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span><?= h($order->currency) ?> <?= number_format((float)($order->subtotal ?? 0), 2) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping:</span>
                        <span><?= ($order->shipping_fee ?? 0) > 0 ? h($order->currency) . ' ' . number_format((float)$order->shipping_fee, 2) : 'Free' ?></span>
                    </div>
                    <?php if (($order->discount ?? 0) > 0): ?>
                        <div class="summary-row">
                            <span>Discount:</span>
                            <span class="text-success">-<?= h($order->currency) ?> <?= number_format((float)$order->discount, 2) ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="summary-row total">
                        <span>Total:</span>
                        <span><?= h($order->currency) ?> <?= number_format((float)($order->total ?? 0), 2) ?></span>
                    </div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="card">
                <div class="card-header">
                    <h3>Customer Information</h3>
                </div>
                <div class="card-body">
                    <div class="customer-info">
                        <div class="info-row">
                            <strong>Name:</strong> <?= h($order->full_name ?? 'N/A') ?>
                        </div>
                        <div class="info-row">
                            <strong>Email:</strong> <?= h($order->email ?? 'N/A') ?>
                        </div>
                        <?php if ($order->user): ?>
                            <div class="info-row">
                                <strong>User ID:</strong> <?= h($order->user_id) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Billing Address -->
            <div class="card">
                <div class="card-header">
                    <h3>Billing Address</h3>
                </div>
                <div class="card-body">
                    <address>
                        <?= h($order->address ?? 'N/A') ?><br>
                        <?= h($order->city ?? 'N/A') ?>, <?= h($order->postcode ?? 'N/A') ?><br>
                        <?= h($order->country ?? 'N/A') ?>
                    </address>
                </div>
            </div>

            <!-- Order Notes -->
            <?php if (!empty($order->notes)): ?>
                <div class="card">
                    <div class="card-header">
                        <h3>Order Notes</h3>
                    </div>
                    <div class="card-body">
                        <p><?= nl2br(h($order->notes)) ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.admin-order-view { max-width: 1200px; margin: 0 auto; padding: 1.25rem 1rem; }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid #e5e7eb; }
.page-title { font-size: 1.6rem; font-weight: 700; margin: 0; }
.page-subtitle { color: #6b7280; margin: 0.25rem 0 0; }
.breadcrumb { display: flex; list-style: none; padding: 0; margin: 0 0 0.5rem 0; }
.breadcrumb-item { margin-right: 0.5rem; }
.breadcrumb-item:not(:last-child)::after { content: '/'; margin-left: 0.5rem; color: #6b7280; }
.order-content { display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; }
.card { background: #fff; border: 1px solid #eef0f3; border-radius: 0.6rem; margin-bottom: 1.5rem; }
.card-header { padding: 1rem 1.5rem; border-bottom: 1px solid #f3f4f6; background: #f9fafb; }
.card-header h3 { margin: 0; font-size: 1.1rem; font-weight: 600; }
.card-body { padding: 1.5rem; }
.status-info { display: flex; gap: 1rem; align-items: center; margin-bottom: 1rem; }
.status-badge { padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem; font-weight: 500; text-transform: uppercase; }
.status-pending { background: #fef3c7; color: #92400e; }
.status-processing { background: #dbeafe; color: #1e40af; }
.status-shipped { background: #d1fae5; color: #065f46; }
.status-delivered { background: #dcfce7; color: #166534; }
.status-cancelled { background: #fee2e2; color: #991b1b; }
.payment-badge { padding: 0.25rem 0.5rem; border-radius: 0.375rem; font-size: 0.8rem; font-weight: 500; }
.payment-unpaid { background: #fee2e2; color: #991b1b; }
.payment-paid { background: #d1fae5; color: #065f46; }
.payment-refunded { background: #f3f4f6; color: #374151; }
.status-actions { margin-top: 1rem; }
.form-row { display: flex; gap: 0.75rem; align-items: end; }
.form-group { flex: 1; }
.form-control { width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem; }
.items-list { space-y: 0.75rem; }
.item-row { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid #f3f4f6; }
.item-row:last-child { border-bottom: none; }
.item-info { flex: 1; }
.item-name { font-weight: 500; margin-bottom: 0.25rem; }
.item-details { color: #6b7280; font-size: 0.875rem; }
.item-quantity { margin: 0 1rem; color: #6b7280; }
.item-total { font-weight: 600; }
.summary-row { display: flex; justify-content: space-between; padding: 0.5rem 0; }
.summary-row.total { border-top: 1px solid #e5e7eb; margin-top: 0.5rem; padding-top: 0.75rem; font-weight: 600; font-size: 1.1rem; }
.customer-info { space-y: 0.5rem; }
.info-row { margin-bottom: 0.5rem; }
.text-success { color: #059669; }
address { font-style: normal; line-height: 1.5; }
.btn { display: inline-block; padding: 0.5rem 1rem; border-radius: 0.375rem; text-decoration: none; font-weight: 500; }
.btn-primary { background: #3b82f6; color: white; border: 1px solid #3b82f6; }
.btn-outline { background: white; color: #374151; border: 1px solid #d1d5db; }
.btn:hover { opacity: 0.9; }
@media (max-width: 768px) { .order-content { grid-template-columns: 1fr; } .form-row { flex-direction: column; } }
</style>

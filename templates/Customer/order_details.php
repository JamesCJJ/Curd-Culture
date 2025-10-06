<?php
$this->extend('/layout/customer');
$this->assign('title', 'Order #' . $order->id);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <?= $this->Html->link('Orders', ['action' => 'orders']) ?>
                </li>
                <li class="breadcrumb-item active">Order #<?= h($order->id) ?></li>
            </ol>
        </nav>
        <h2>Order #<?= h($order->id) ?></h2>
        <p class="text-muted mb-0">
            Confirmed <?= $order->created->format('M j, Y') ?>
        </p>
    </div>

    <div>
        <?= $this->Html->link(
            'Buy again',
            ['action' => 'buyAgain', '?' => ['id' => $order->id]],
            [
                'class' => 'btn btn-primary',
                'confirm' => 'Add all items from this order to your cart?'
            ]
        ) ?>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Order Status -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-truck me-2 text-primary" style="font-size: 1.5rem;"></i>
                    <div>
                        <div class="order-status status-<?= h($order->status) ?>">
                            <?= ucfirst(h($order->status)) ?>
                        </div>
                        <small class="text-muted">
                            <?= $order->created->format('M j') ?>
                        </small>
                    </div>
                </div>

                <div class="progress mb-3" style="height: 8px;">
                    <?php
                    $statusProgress = [
                        'pending'   => 20,
                        'confirmed' => 40,
                        'processing'=> 60,
                        'shipped'   => 80,
                        'delivered' => 100,
                        'cancelled' => 0
                    ];
                    $progress = $statusProgress[$order->status] ?? 0;
                    ?>
                    <div class="progress-bar" style="width: <?= $progress ?>%"></div>
                </div>

                <div class="row text-center small">
                    <div class="col">
                        <div class="<?= in_array($order->status, ['confirmed', 'processing', 'shipped', 'delivered']) ? 'text-primary' : 'text-muted' ?>">
                            <i class="bi bi-check-circle"></i><br>
                            Confirmed
                        </div>
                    </div>
                    <div class="col">
                        <div class="<?= in_array($order->status, ['processing', 'shipped', 'delivered']) ? 'text-primary' : 'text-muted' ?>">
                            <i class="bi bi-gear"></i><br>
                            Processing
                        </div>
                    </div>
                    <div class="col">
                        <div class="<?= in_array($order->status, ['shipped', 'delivered']) ? 'text-primary' : 'text-muted' ?>">
                            <i class="bi bi-truck"></i><br>
                            Shipped
                        </div>
                    </div>
                    <div class="col">
                        <div class="<?= $order->status === 'delivered' ? 'text-primary' : 'text-muted' ?>">
                            <i class="bi bi-house-check"></i><br>
                            Delivered
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fulfillment Info (Delivery / Pickup) -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-calendar2-week me-2"></i>
                    Fulfillment
                </h5>
            </div>
            <div class="card-body">
                <?php if (($order->fulfillment_method ?? 'delivery') === 'pickup'): ?>
                    <p class="mb-1"><strong>Method:</strong> Click &amp; Collect</p>
                    <?php if (!empty($order->pickup_location_id)): ?>
                        <p class="mb-1"><strong>Pickup Location:</strong> #<?= (int)$order->pickup_location_id ?></p>
                    <?php endif; ?>
                    <?php if (!empty($order->delivery_instructions)): ?>
                        <p class="mb-0"><strong>Notes:</strong> <?= h($order->delivery_instructions) ?></p>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="mb-1"><strong>Method:</strong> Home Delivery</p>
                    <?php if (!empty($order->delivery_date)): ?>
                        <p class="mb-1"><strong>Date:</strong> <?= h($order->delivery_date->format('M j, Y')) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($order->delivery_slot_id)): ?>
                        <p class="mb-1"><strong>Time Slot:</strong> Slot #<?= (int)$order->delivery_slot_id ?></p>
                    <?php endif; ?>
                    <?php if (!empty($order->delivery_instructions)): ?>
                        <p class="mb-0"><strong>Notes:</strong> <?= h($order->delivery_instructions) ?></p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Order Items -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Order Items</h5>
                <span class="badge bg-secondary"><?= count($order->order_items) ?> item<?= count($order->order_items) !== 1 ? 's' : '' ?></span>
            </div>
            <div class="card-body p-0">
                <?php foreach ($order->order_items as $item): ?>
                    <div class="d-flex align-items-center p-3 border-bottom">
                        <div class="me-3">
                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="bi bi-box text-muted" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>

                        <div class="flex-grow-1">
                            <h6 class="mb-1"><?= h($item->name) ?></h6>
                            <p class="text-muted mb-0 small">
                                $<?= number_format($item->price, 2) ?>/ea
                            </p>
                        </div>

                        <div class="text-end">
                            <div class="fw-bold">$<?= number_format($item->price * $item->qty, 2) ?></div>
                            <div class="text-muted small">Qty: <?= $item->qty ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Payment Summary -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-credit-card me-2"></i>
                    Payment Summary
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal</span>
                    <span>$<?= number_format((float)($order->subtotal ?? 0), 2) ?></span>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span>Shipping</span>
                    <span><?= ($order->shipping_fee ?? 0) > 0 ? '$' . number_format((float)$order->shipping_fee, 2) : 'Free' ?></span>
                </div>

                <?php if (($order->discount ?? 0) > 0): ?>
                    <div class="d-flex justify-content-between mb-2 text-success">
                        <span>Discount</span>
                        <span>-$<?= number_format((float)$order->discount, 2) ?></span>
                    </div>
                <?php endif; ?>

                <hr>

                <div class="d-flex justify-content-between fw-bold">
                    <span>Total</span>
                    <span>AUD $<?= number_format((float)($order->total ?? 0), 2) ?></span>
                </div>

                <?php if (($order->total ?? 0) > 50): ?>
                    <small class="text-muted d-block mt-2">
                        Including $<?= number_format((float)$order->total * 0.1, 2) ?> in taxes
                    </small>
                <?php endif; ?>

                <!-- Show "fully paid" only if payment_status === 'paid' -->
                <?php if (($order->payment_status ?? '') === 'paid'): ?>
                    <div class="mt-3 p-2 bg-success bg-opacity-10 rounded">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        <small class="text-success">Your order is fully paid.</small>
                    </div>
                <?php elseif (($order->payment_method ?? '') === 'bank_transfer' && ($order->payment_status ?? '') === 'unpaid'): ?>
                    <div class="mt-3 p-2 bg-warning bg-opacity-10 rounded">
                        <i class="bi bi-clock-history text-warning me-2"></i>
                        <small class="text-warning">Awaiting bank transfer. We will confirm your order after payment is received.</small>
                    </div>
                <?php else: ?>
                    <div class="mt-3 p-2 bg-secondary bg-opacity-10 rounded">
                        <i class="bi bi-info-circle text-secondary me-2"></i>
                        <small class="text-secondary">Payment status: <?= h($order->payment_status ?? 'unknown') ?></small>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Contact Information</h6>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong><?= h($order->full_name ?? 'N/A') ?></strong></p>
                <p class="text-muted mb-0"><?= h($order->email ?? 'N/A') ?></p>
            </div>
        </div>

        <!-- Billing Address -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Billing Address</h6>
            </div>
            <div class="card-body">
                <address class="mb-0">
                    <?= h($order->address ?? 'N/A') ?><br>
                    <?= h($order->city ?? 'N/A') ?>, <?= h($order->postcode ?? 'N/A') ?><br>
                    <?= h($order->country ?? 'N/A') ?>
                </address>
            </div>
        </div>

        <!-- Shipping Method -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Shipping Method</h6>
            </div>
            <div class="card-body">
                <p class="mb-0">
                    <?= (($order->fulfillment_method ?? 'delivery') === 'pickup')
                        ? 'Click & Collect'
                        : (($order->shipping_fee ?? 0) > 0 ? 'Standard Shipping' : 'Free Shipping') ?>
                </p>
            </div>
        </div>
    </div>
</div>

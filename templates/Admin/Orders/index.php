<?php
/**
 * Admin Orders Index
 *
 * @var \App\View\AppView $this
 * @var iterable $orders
 * @var array $pagination
 * @var array $stats
 */

$this->assign('title', 'Orders');
?>

<div class="admin-orders">
    <div class="page-header">
        <div class="page-header-content">
            <h1 class="page-title">Orders</h1>
            <p class="page-subtitle">Manage customer orders, payments and fulfillment</p>
        </div>
        <div class="page-actions">
            <?= $this->Html->link('Analytics', ['action' => 'analytics'], ['class' => 'btn btn-outline']) ?>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card"><div class="stat-value"><?= (int)$stats['total'] ?></div><div class="stat-label">Total</div></div>
        <div class="stat-card"><div class="stat-value"><?= (int)$stats['pending'] ?></div><div class="stat-label">Pending</div></div>
        <div class="stat-card"><div class="stat-value"><?= (int)$stats['processing'] ?></div><div class="stat-label">Processing</div></div>
        <div class="stat-card"><div class="stat-value"><?= (int)$stats['completed'] ?></div><div class="stat-label">Completed</div></div>
        <div class="stat-card"><div class="stat-value"><?= (int)$stats['cancelled'] ?></div><div class="stat-label">Cancelled</div></div>
        <div class="stat-card"><div class="stat-value">$<?= number_format((float)$stats['total_revenue'], 2) ?></div><div class="stat-label">Revenue</div></div>
    </div>

    <div class="filters-section">
        <?= $this->Form->create(null, ['type' => 'get', 'class' => 'filters-form']) ?>
        <div class="filters-row">
            <?= $this->Form->control('q', ['label' => false, 'placeholder' => 'Search by email, name or ID', 'class' => 'form-control', 'type' => 'search']) ?>
            <?= $this->Form->control('status', ['label' => false, 'class' => 'form-control', 'type' => 'select', 'options' => ['' => 'All status', 'pending' => 'Pending', 'processing' => 'Processing', 'completed' => 'Completed', 'cancelled' => 'Cancelled']]) ?>
            <?= $this->Form->control('payment_status', ['label' => false, 'class' => 'form-control', 'type' => 'select', 'options' => ['' => 'All payments', 'unpaid' => 'Unpaid', 'paid' => 'Paid', 'refunded' => 'Refunded']]) ?>
            <?= $this->Form->control('from', ['label' => false, 'placeholder' => 'From (YYYY-MM-DD)', 'class' => 'form-control']) ?>
            <?= $this->Form->control('to', ['label' => false, 'placeholder' => 'To (YYYY-MM-DD)', 'class' => 'form-control']) ?>
            <div class="filter-actions">
                <?= $this->Form->button('Filter', ['class' => 'btn btn-outline']) ?>
                <?= $this->Html->link('Reset', ['action' => 'index'], ['class' => 'btn btn-subtle']) ?>
            </div>
        </div>
        <?= $this->Form->end() ?>
    </div>

    <div class="table-section">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="7" class="empty">No orders yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $o): ?>
                    <tr>
                        <td>#<?= h($o->id) ?></td>
                        <td>
                            <div><strong><?= h($o->full_name ?: ($o->user->name ?? 'Customer')) ?></strong></div>
                            <div class="muted"><?= h($o->email) ?></div>
                        </td>
                        <td><?= h($o->currency) ?> <?= number_format((float)$o->total, 2) ?></td>
                        <td><?= h(ucfirst((string)$o->status)) ?></td>
                        <td><?= h(ucfirst((string)$o->payment_status)) ?></td>
                        <td><?= $o->created?->format('Y-m-d H:i') ?></td>
                        <td class="actions">
                            <!-- View / Edit disabled placeholders -->
                            <button type="button" class="btn tiny text-muted" disabled title="Coming soon">
                                View (Coming soon)
                            </button>
                            <button type="button" class="btn tiny text-muted" disabled title="Coming soon">
                                Edit (Coming soon)
                            </button>
                        </td>

                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (($pagination['totalPages'] ?? 1) > 1): ?>
        <div class="pagination">
            <?php if (!empty($pagination['hasPrev'])): ?>
                <?= $this->Html->link('← Prev', array_merge($this->request->getQueryParams(), ['page' => $pagination['page'] - 1]), ['class' => 'btn btn-outline btn-sm']) ?>
            <?php endif; ?>
            <span>Page <?= (int)$pagination['page'] ?> of <?= (int)$pagination['totalPages'] ?></span>
            <?php if (!empty($pagination['hasNext'])): ?>
                <?= $this->Html->link('Next →', array_merge($this->request->getQueryParams(), ['page' => $pagination['page'] + 1]), ['class' => 'btn btn-outline btn-sm']) ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.admin-orders{max-width:1200px;margin:0 auto;padding:1.25rem 1rem}
.page-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:1px solid #e5e7eb}
.page-title{font-size:1.6rem;font-weight:700;margin:0}
.page-subtitle{color:#6b7280;margin:.25rem 0 0}
.stats-grid{display:grid;grid-template-columns:repeat(6,1fr);gap:.6rem;margin-bottom:1rem}
.stat-card{background:#fff;border:1px solid #eef0f3;border-radius:.6rem;padding:.6rem .7rem}
.stat-value{font-size:1.2rem;font-weight:700}
.stat-label{color:#6b7280;font-size:.8rem}
.filters-row{display:grid;grid-template-columns:repeat(5,1fr) auto auto;gap:.5rem}
.form-control{padding:.5rem;border:1px solid #d1d5db;border-radius:.5rem}
.filter-actions{display:flex;gap:.5rem}
.table-section{background:#fff;border:1px solid #eef0f3;border-radius:.6rem;overflow:hidden}
.data-table{width:100%;border-collapse:separate;border-spacing:0}
.data-table th{background:#f9fafb;text-align:left;border-bottom:1px solid #eef0f3;padding:.6rem}
.data-table td{border-bottom:1px solid #f3f4f6;padding:.6rem;vertical-align:top}
.actions .btn.tiny{padding:.25rem .45rem;font-size:.8rem}
.muted{color:#6b7280}
.btn{display:inline-block;padding:.45rem .7rem;border-radius:.45rem;border:1px solid #d1d5db;text-decoration:none;color:#111;background:#fff}
.btn-outline{background:#fff}
.btn-subtle{background:transparent;color:#6b7280}
.btn-sm{padding:.3rem .5rem;font-size:.8rem}
.pagination{display:flex;gap:.75rem;justify-content:center;align-items:center;padding:.8rem}
@media (max-width: 1000px){.stats-grid{grid-template-columns:repeat(3,1fr)}.filters-row{grid-template-columns:1fr 1fr 1fr 1fr}}
@media (max-width: 720px){.stats-grid{grid-template-columns:repeat(2,1fr)}.filters-row{grid-template-columns:1fr 1fr}}
</style>



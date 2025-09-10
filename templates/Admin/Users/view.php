<?php
/**
 * Admin View User
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 * @var iterable $orders
 */
$this->assign('title', 'User #' . (int)$user->id);
?>

<div class="admin-user-view">
    <div class="view-header">
        <div class="view-header-content">
            <h1 class="view-title"><?= h($user->name ?? $user->email) ?></h1>
            <p class="view-subtitle">Role: <?= h(ucfirst((string)$user->role)) ?> • Status: <?= h(ucfirst((string)$user->status)) ?></p>
        </div>
        <div class="view-actions">
            <?= $this->Html->link('← Back to Users', ['action' => 'index'], ['class' => 'btn btn-outline']) ?>
            <?= $this->Html->link('Edit User', ['action' => 'edit', $user->id], ['class' => 'btn btn-primary']) ?>
        </div>
    </div>

    <div class="details-grid">
        <div class="detail-card">
            <h3 class="card-title">Account Information</h3>
            <div class="detail-items">
                <div class="detail-item"><span class="detail-label">User ID</span><span class="detail-value">#<?= (int)$user->id ?></span></div>
                <div class="detail-item"><span class="detail-label">Name</span><span class="detail-value"><?= h($user->name ?? '-') ?></span></div>
                <div class="detail-item"><span class="detail-label">Email</span><span class="detail-value"><?= h($user->email) ?></span></div>
                <div class="detail-item"><span class="detail-label">Role</span><span class="detail-value"><?= h(ucfirst((string)$user->role)) ?></span></div>
                <div class="detail-item"><span class="detail-label">Status</span><span class="detail-value"><?= h(ucfirst((string)$user->status)) ?></span></div>
                <div class="detail-item"><span class="detail-label">Timezone</span><span class="detail-value"><?= h($user->timezone ?? 'UTC') ?></span></div>
                <div class="detail-item"><span class="detail-label">Language</span><span class="detail-value"><?= h($user->language ?? 'en') ?></span></div>
                <div class="detail-item"><span class="detail-label">Theme</span><span class="detail-value"><?= h($user->theme ?? 'auto') ?></span></div>
                <div class="detail-item"><span class="detail-label">Created</span><span class="detail-value"><?= $user->created?->format('Y-m-d H:i') ?></span></div>
                <div class="detail-item"><span class="detail-label">Modified</span><span class="detail-value"><?= $user->modified?->format('Y-m-d H:i') ?></span></div>
            </div>
        </div>

        <div class="detail-card">
            <h3 class="card-title">Recent Orders</h3>
            <?php if (empty($orders)): ?>
                <div class="empty">No recent orders.</div>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $o): ?>
                        <tr>
                            <td>#<?= (int)$o->id ?></td>
                            <td><?= h($o->currency) ?> <?= number_format((float)$o->total, 2) ?></td>
                            <td><?= h(ucfirst((string)$o->status)) ?></td>
                            <td><?= $o->created?->format('Y-m-d H:i') ?></td>
                            <td><?= $this->Html->link('View', ['prefix' => 'Admin', 'controller' => 'Orders', 'action' => 'view', $o->id], ['class' => 'btn btn-sm']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.admin-user-view{max-width:1100px;margin:0 auto;padding:1.25rem 1rem}
.view-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.25rem;padding-bottom:1rem;border-bottom:1px solid #e5e7eb}
.view-title{font-size:1.6rem;font-weight:700;margin:0}
.view-subtitle{color:#6b7280;margin:.25rem 0 0}
.details-grid{display:grid;grid-template-columns:1fr;gap:1rem}
.detail-card{background:#fff;border:1px solid #eef0f3;border-radius:.6rem;padding:1rem}
.card-title{margin:0 0 .6rem;font-weight:600}
.detail-items{display:grid;grid-template-columns:1fr 1fr;gap:.6rem}
.detail-item{display:flex;flex-direction:column}
.detail-label{font-size:.8rem;color:#6b7280}
.detail-value{font-weight:600}
.data-table{width:100%;border-collapse:separate;border-spacing:0}
.data-table th{background:#f9fafb;text-align:left;border-bottom:1px solid #eef0f3;padding:.6rem}
.data-table td{border-bottom:1px solid #f3f4f6;padding:.6rem;vertical-align:top}
.btn{display:inline-block;padding:.45rem .7rem;border-radius:.45rem;border:1px solid #d1d5db;text-decoration:none;color:#111;background:#fff}
.btn-outline{background:#fff}
.btn-primary{background:#2563eb;color:#fff;border-color:#2563eb}
.btn-sm{padding:.3rem .5rem;font-size:.8rem}
.empty{color:#6b7280}
@media (max-width: 900px){.detail-items{grid-template-columns:1fr}}
</style>

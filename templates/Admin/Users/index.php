<?php
/**
 * Admin Users Index
 *
 * @var \App\View\AppView $this
 * @var iterable $users
 * @var array $pagination
 * @var array $stats
 */

$this->assign('title', 'Users');
?>

<div class="admin-users">
    <div class="page-header">
        <div class="page-header-content">
            <h1 class="page-title">Users</h1>
            <p class="page-subtitle">Manage customers and administrators</p>
        </div>
        <div class="page-actions">
            <?= $this->Html->link('Export CSV', ['action' => 'export'], ['class' => 'btn btn-outline', 'target' => '_blank']) ?>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card"><div class="stat-value"><?= (int)$stats['total'] ?></div><div class="stat-label">Total</div></div>
        <div class="stat-card"><div class="stat-value"><?= (int)$stats['admins'] ?></div><div class="stat-label">Admins</div></div>
        <div class="stat-card"><div class="stat-value"><?= (int)$stats['customers'] ?></div><div class="stat-label">Customers</div></div>
        <div class="stat-card"><div class="stat-value"><?= (int)$stats['active'] ?></div><div class="stat-label">Active</div></div>
        <div class="stat-card"><div class="stat-value"><?= (int)$stats['inactive'] ?></div><div class="stat-label">Inactive</div></div>
    </div>

    <div class="filters-section">
        <?= $this->Form->create(null, ['type' => 'get', 'class' => 'filters-form']) ?>
        <div class="filters-row">
            <?= $this->Form->control('q', ['label' => false, 'placeholder' => 'Search name or email', 'class' => 'form-control', 'type' => 'search']) ?>
            <?= $this->Form->control('role', ['label' => false, 'class' => 'form-control', 'type' => 'select', 'options' => ['' => 'All roles', 'admin' => 'Admin', 'customer' => 'Customer']]) ?>
            <?= $this->Form->control('status', ['label' => false, 'class' => 'form-control', 'type' => 'select', 'options' => ['' => 'All status', 'active' => 'Active', 'inactive' => 'Inactive']]) ?>
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
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="7" class="empty">No users found.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= (int)$u->id ?></td>
                        <td><?= h($u->name ?? $u->username ?? 'User') ?></td>
                        <td><?= h($u->email) ?></td>
                        <td><?= h(ucfirst((string)$u->role)) ?></td>
                        <td><?= h(ucfirst((string)$u->status)) ?></td>
                        <td><?= $u->created?->format('Y-m-d H:i') ?></td>
                        <td class="actions">
                            <?= $this->Html->link('View', ['action' => 'view', $u->id], ['class' => 'btn tiny']) ?>
                            <?= $this->Html->link('Edit', ['action' => 'edit', $u->id], ['class' => 'btn tiny']) ?>
                            <?php 
                            $identity = $this->request->getAttribute('identity');
                            $currentUserId = $identity ? $identity->get('id') : null;
                            if (!$currentUserId || (int)$u->id !== (int)$currentUserId): 
                            ?>
                                <?= $this->Form->postLink('Toggle', ['action' => 'toggleStatus', $u->id], ['class' => 'btn tiny'], 'Toggle user status?') ?>
                            <?php else: ?>
                                <span class="btn tiny disabled" title="Cannot toggle your own status">Toggle</span>
                            <?php endif; ?>
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
.admin-users{max-width:1100px;margin:0 auto;padding:1.25rem 1rem}
.page-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:1px solid #e5e7eb}
.page-title{font-size:1.6rem;font-weight:700;margin:0}
.page-subtitle{color:#6b7280;margin:.25rem 0 0}
.stats-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:.6rem;margin-bottom:1rem}
.stat-card{background:#fff;border:1px solid #eef0f3;border-radius:.6rem;padding:.6rem .7rem}
.stat-value{font-size:1.2rem;font-weight:700}
.stat-label{color:#6b7280;font-size:.8rem}
.filters-row{display:grid;grid-template-columns:2fr 1fr 1fr auto auto;gap:.5rem}
.form-control{padding:.5rem;border:1px solid #d1d5db;border-radius:.5rem}
.filter-actions{display:flex;gap:.5rem}
.table-section{background:#fff;border:1px solid #eef0f3;border-radius:.6rem;overflow:hidden}
.data-table{width:100%;border-collapse:separate;border-spacing:0}
.data-table th{background:#f9fafb;text-align:left;border-bottom:1px solid #eef0f3;padding:.6rem}
.data-table td{border-bottom:1px solid #f3f4f6;padding:.6rem;vertical-align:top}
.actions .btn.tiny{padding:.25rem .45rem;font-size:.8rem}
.btn{display:inline-block;padding:.45rem .7rem;border-radius:.45rem;border:1px solid #d1d5db;text-decoration:none;color:#111;background:#fff}
.btn-outline{background:#fff}
.btn-subtle{background:transparent;color:#6b7280}
.btn-sm{padding:.3rem .5rem;font-size:.8rem}
.btn.disabled{background:#f3f4f6;color:#9ca3af;border-color:#e5e7eb;cursor:not-allowed}
.pagination{display:flex;gap:.75rem;justify-content:center;align-items:center;padding:.8rem}
@media (max-width: 900px){.stats-grid{grid-template-columns:repeat(3,1fr)}.filters-row{grid-template-columns:1fr 1fr}}
</style>



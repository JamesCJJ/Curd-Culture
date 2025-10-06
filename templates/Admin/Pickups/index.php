<?php
/**
 * Admin Pickup Locations Index
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\ResultSetInterface $locations
 */
$this->assign('title', 'Pickups');
?>
<div class="admin-products">

    <div class="page-header">
        <div class="page-header-content">
            <h1 class="page-title">Pickup Locations</h1>
            <p class="page-subtitle">Manage in-store “Click & Collect” locations</p>
        </div>
        <div class="page-actions">
            <?= $this->Html->link('<i class="icon-plus"></i> Add Location', ['action'=>'add'], ['class'=>'btn btn-primary','escape'=>false]) ?>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card"><div class="stat-icon stat-icon-blue">🏪</div><div class="stat-content"><div class="stat-value"><?= number_format($stats['total']) ?></div><div class="stat-label">Total</div></div></div>
        <div class="stat-card"><div class="stat-icon stat-icon-green">✅</div><div class="stat-content"><div class="stat-value"><?= number_format($stats['active']) ?></div><div class="stat-label">Active</div></div></div>
        <div class="stat-card"><div class="stat-icon stat-icon-red">⛔</div><div class="stat-content"><div class="stat-value"><?= number_format($stats['inactive']) ?></div><div class="stat-label">Inactive</div></div></div>
    </div>

    <div class="filters-section">
        <?= $this->Form->create(null, ['type'=>'get', 'class'=>'filters-form']) ?>
        <div class="filters-row">
            <div class="filter-group">
                <?= $this->Form->control('q', ['label'=>false, 'type'=>'search', 'placeholder'=>'Search name / address / city / postcode...', 'value'=>$q, 'class'=>'form-control']) ?>
            </div>
            <div class="filter-group" style="max-width:220px">
                <?= $this->Form->control('status', [
                    'label'=>false,'type'=>'select','class'=>'form-control',
                    'options'=>[''=>'All','active'=>'Active','inactive'=>'Inactive'],
                    'value'=>$status
                ]) ?>
            </div>
            <div class="filter-actions">
                <?= $this->Form->button('Search', ['class'=>'btn btn-outline']) ?>
                <?= $this->Html->link('Clear', ['action'=>'index'], ['class'=>'btn btn-subtle']) ?>
            </div>
        </div>
        <?= $this->Form->end() ?>
    </div>

    <div class="table-section">
        <div class="table-container">
            <table class="data-table">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Address</th>
                    <th>City / Postcode</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th class="col-actions">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($locations->isEmpty()): ?>
                    <tr><td colspan="6" class="empty-state">
                            <div class="empty-content">
                                <h3>No locations found</h3>
                                <p>Add your first Click & Collect location.</p>
                                <?= $this->Html->link('Add Location', ['action'=>'add'], ['class'=>'btn btn-primary']) ?>
                            </div>
                        </td></tr>
                <?php else: foreach ($locations as $loc): ?>
                    <tr>
                        <td><strong><?= h($loc->name) ?></strong></td>
                        <td><?= h($loc->address) ?></td>
                        <td><?= h($loc->city) ?> <?= $loc->postcode ? '・'.h($loc->postcode) : '' ?></td>
                        <td>
                            <?php if ((int)$loc->is_active === 1): ?>
                                <span class="badge badge-success">Active</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $loc->created ? $loc->created->format('M j, Y') : '—' ?></td>
                        <td class="col-actions">
                            <div class="action-buttons">
                                <?= $this->Html->link('<i class="icon-edit"></i>', ['action'=>'edit',$loc->id], ['class'=>'btn-action btn-edit','escape'=>false,'title'=>'Edit']) ?>
                                <?= $this->Form->postLink(
                                    (int)$loc->is_active ? 'Disable' : 'Enable',
                                    ['action'=>'toggle',$loc->id],
                                    ['class'=>'btn btn-outline btn-sm', 'confirm'=>'Change status?']
                                ) ?>
                                <?= $this->Form->postLink('<i class="icon-trash"></i>',
                                    ['action'=>'delete',$loc->id],
                                    ['class'=>'btn-action btn-delete','escape'=>false,'confirm'=>'Delete this location?']
                                ) ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($pagination['totalPages'] > 1): ?>
        <div class="pagination-section">
            <div class="pagination-info">
                Showing <?= number_format(($pagination['page']-1)*20+1) ?>
                to <?= number_format(min($pagination['page']*20, $pagination['totalCount'])) ?>
                of <?= number_format($pagination['totalCount']) ?> locations
            </div>
            <div class="pagination-controls">
                <?php if ($pagination['hasPrev']): ?>
                    <?= $this->Html->link('← Previous', array_merge($this->request->getQueryParams(), ['page'=>$pagination['page']-1]), ['class'=>'btn btn-outline btn-sm']) ?>
                <?php endif; ?>
                <span class="page-info">Page <?= $pagination['page'] ?> of <?= $pagination['totalPages'] ?></span>
                <?php if ($pagination['hasNext']): ?>
                    <?= $this->Html->link('Next →', array_merge($this->request->getQueryParams(), ['page'=>$pagination['page']+1]), ['class'=>'btn btn-outline btn-sm']) ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
<style>
    /* Root */
    .admin-deliveries { max-width: 1400px; margin: 0 auto; padding: 2rem 1rem; }

    /* Header */
    .page-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:2rem; padding-bottom:1.5rem; border-bottom:1px solid #e5e7eb;}
    .page-title { font-size:2rem; font-weight:700; color:#111827; margin:0 0 .5rem;}
    .page-subtitle { color:#6b7280; margin:0;}
    .page-actions { display:flex; gap:.5rem; }

    /* Filters */
    .filters-section { background:#fff; border:1px solid #e5e7eb; border-radius:.75rem; padding:1.25rem; margin-bottom:1.25rem; }
    .filters-row { display:flex; align-items:flex-end; gap:1rem; flex-wrap:wrap; }
    .filter-group { min-width:220px; }
    .label { display:block; font-size:.85rem; color:#6b7280; margin-bottom:.25rem; }

    /* Stats */
    .stats-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:1.25rem; margin-bottom:1.5rem; }
    .stat-card { background:#fff; border:1px solid #e5e7eb; border-radius:.75rem; padding:1.25rem; display:flex; align-items:center; gap:1rem; box-shadow:0 1px 3px rgba(0,0,0,.06); }
    .stat-icon { width:3rem; height:3rem; border-radius:.75rem; display:flex; align-items:center; justify-content:center; font-size:1.25rem; }
    .stat-icon-blue { background:#dbeafe; color:#1d4ed8; }
    .stat-icon-green { background:#dcfce7; color:#16a34a; }
    .stat-icon-orange { background:#fed7aa; color:#ea580c; }
    .stat-icon-red { background:#fecaca; color:#dc2626; }
    .stat-value { font-size:1.6rem; font-weight:700; color:#111827; }
    .stat-label { color:#6b7280; font-size:.9rem; }

    /* Tables */
    .table-section { background:#fff; border:1px solid #e5e7eb; border-radius:.75rem; overflow:hidden; margin-bottom:1rem; }
    .table-header { display:flex; justify-content:space-between; align-items:center; padding:1rem 1rem .75rem 1rem; border-bottom:1px solid #e5e7eb; }
    .slot-meta { display:flex; align-items:baseline; gap:.75rem; }
    .slot-title { margin:0; font-size:1.1rem; }
    .slot-sub { color:#6b7280; }

    .table-container { overflow-x:auto; }
    .data-table { width:100%; border-collapse:collapse; }
    .data-table th { background:#f9fafb; padding:0.8rem 1rem; text-align:left; font-weight:600; color:#374151; border-bottom:1px solid #e5e7eb; white-space:nowrap;}
    .data-table td { padding:0.8rem 1rem; border-bottom:1px solid #f3f4f6; vertical-align:top; }
    .data-table tbody tr:hover { background:#f9fafb; }
    .inline-form { display:flex; gap:.4rem; align-items:center; }
    .form-control { width:100%; padding:.5rem .6rem; border:1px solid #d1d5db; border-radius:.5rem; font-size:.85rem; }
    .form-control-sm { padding:.35rem .5rem; font-size:.82rem; }
    .price { font-weight:600; color:#059669; }
    .muted { color:#6b7280; }

    /* Badges & Buttons */
    .badge { display:inline-block; padding:.2rem .6rem; border-radius:9999px; font-size:.75rem; font-weight:600; background:#eef2f7; color:#374151;}
    .badge-success { background:#dcfce7; color:#166534; }
    .badge-warning { background:#fef3c7; color:#92400e; }
    .badge-danger  { background:#fecaca; color:#991b1b; }

    .btn { display:inline-flex; align-items:center; gap:.5rem; padding:.55rem .9rem; border-radius:.5rem; font-size:.875rem; font-weight:500; text-decoration:none; transition:all .2s; border:1px solid transparent; cursor:pointer;}
    .btn-primary { background:#2563eb; color:#fff; }
    .btn-primary:hover { background:#1d4ed8; }
    .btn-outline { background:#fff; color:#374151; border-color:#d1d5db; }
    .btn-outline:hover { background:#f9fafb; border-color:#9ca3af; }
    .btn-subtle { background:transparent; color:#6b7280; }
    .btn-subtle:hover { color:#374151; background:#f3f4f6; }
    .btn-sm { padding:.35rem .6rem; font-size:.82rem; }

    /* Empty */
    .empty-state { text-align:center; padding:2.5rem 1rem; }
    .empty-content { max-width:460px; margin:0 auto; }
    .empty-icon { font-size:2.25rem; color:#d1d5db; margin-bottom:.5rem; }

    /* Responsive */
    @media (max-width: 1024px) {
        .page-header { flex-direction:column; align-items:stretch; gap:1rem; }
        .stats-grid { grid-template-columns:repeat(2,1fr); }
    }
    @media (max-width: 768px) {
        .stats-grid { grid-template-columns:1fr; }
    }
</style>

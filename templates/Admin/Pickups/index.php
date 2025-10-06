<?php
/**
 * Admin Pickup Locations Index
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\ResultSetInterface|\Cake\Collection\CollectionInterface $pickups
 * @var array $stats
 * @var string $q
 * @var string $status
 */
$this->assign('title', 'Pickup Locations');
?>
<div class="admin-pickups"><!-- NOTE: use admin-pickups so CSS below applies -->

    <!-- Header -->
    <div class="page-header">
        <div class="page-header-content">
            <h1 class="page-title">Pickup Locations</h1>
            <p class="page-subtitle">Manage customer pickup points & store hours</p>
        </div>
        <div class="page-actions">
            <?= $this->Html->link('<i class="icon-plus"></i> Add Pickup', ['action' => 'add'], ['class' => 'btn btn-primary','escape'=>false]) ?>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon stat-icon-blue">🏪</div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($stats['total']) ?></div>
                <div class="stat-label">Total Locations</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-green">✅</div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($stats['active']) ?></div>
                <div class="stat-label">Active</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-red">⛔</div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($stats['inactive']) ?></div>
                <div class="stat-label">Inactive</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <?= $this->Form->create(null, ['type' => 'get', 'class' => 'filters-form']) ?>
        <div class="filters-row">
            <div class="filter-group">
                <?= $this->Form->control('q', [
                    'label'=>false, 'type'=>'search',
                    'placeholder'=>'Search pickups (name/address/suburb/state/postcode)...',
                    'value'=>$q, 'class'=>'form-control'
                ]) ?>
            </div>
            <div class="filter-group" style="max-width:220px">
                <?= $this->Form->control('status', [
                    'label'=>false,'type'=>'select','class'=>'form-control',
                    'options'=>['' => 'All','active'=>'Active','inactive'=>'Inactive'],
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

    <!-- Table -->
    <div class="table-section">
        <div class="table-container">
            <table class="data-table">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Address</th>
                    <th>Hours</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th class="col-actions">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($pickups)): ?>
                    <tr>
                        <td colspan="6" class="empty-state">
                            <div class="empty-content">
                                <h3>No pickup locations</h3>
                                <p>Click “Add Pickup” to create your first pickup point.</p>
                                <?= $this->Html->link('Add Pickup', ['action'=>'add'], ['class'=>'btn btn-primary']) ?>
                            </div>
                        </td>
                    </tr>
                <?php else: foreach ($pickups as $p): ?>
                    <?php
                    $of = $p->open_from;
                    $ot = $p->open_to;
                    $ofTxt = $of ? (is_object($of) ? $of->format('g:i A') : (is_string($of) ? date('g:i A', strtotime($of)) : '')) : '';
                    $otTxt = $ot ? (is_object($ot) ? $ot->format('g:i A') : (is_string($ot) ? date('g:i A', strtotime($ot)) : '')) : '';
                    ?>
                    <tr>
                        <td><strong><?= h($p->name) ?></strong></td>
                        <td>
                            <?= h($p->address_line_1) ?>
                            <?php if ($p->address_line_2) echo ', ' . h($p->address_line_2); ?>
                            , <?= h($p->suburb) ?>, <?= h($p->state) ?> <?= h($p->postcode) ?>
                        </td>
                        <td><?= $ofTxt ?: '—' ?><?= ($ofTxt||$otTxt) ? ' – ' : '' ?><?= $otTxt ?: '—' ?></td>
                        <td>
                            <?php if ((int)$p->is_active === 1): ?>
                                <span class="badge badge-success">Active</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $p->created ? $p->created->format('M j, Y') : '—' ?></td>
                        <td class="col-actions">
                            <div class="action-buttons">
                                <?= $this->Html->link(
                                    'Edit',
                                    ['prefix'=>'Admin','controller'=>'Pickups','action'=>'edit',$p->id],
                                    ['class'=>'btn btn-outline btn-sm', 'title'=>'Edit']
                                ) ?>

                                <?php if ((int)$p->is_active === 1): ?>
                                    <?= $this->Form->postLink(
                                        'Disable',
                                        ['prefix'=>'Admin','controller'=>'Pickups','action'=>'toggle',$p->id],
                                        ['class'=>'btn btn-outline btn-sm', 'confirm'=>'Disable this pickup?']
                                    ) ?>
                                <?php else: ?>
                                    <?= $this->Form->postLink(
                                        'Enable',
                                        ['prefix'=>'Admin','controller'=>'Pickups','action'=>'toggle',$p->id],
                                        ['class'=>'btn btn-outline btn-sm']
                                    ) ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    /* Root */
    .admin-pickups { max-width: 1400px; margin: 0 auto; padding: 2rem 1rem; }

    /* Header */
    .page-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:2rem; padding-bottom:1.5rem; border-bottom:1px solid #e5e7eb;}
    .page-title { font-size:2rem; font-weight:700; color:#111827; margin:0 0 .5rem;}
    .page-subtitle { color:#6b7280; margin:0;}
    .page-actions { display:flex; gap:.5rem; }

    /* Filters */
    .filters-section { background:#fff; border:1px solid #e5e7eb; border-radius:.75rem; padding:1.25rem; margin-bottom:1.25rem; }
    .filters-row { display:flex; align-items:flex-end; gap:1rem; flex-wrap:wrap; }
    .filter-group { min-width:220px; }
    .form-control { width:100%; padding:.5rem .6rem; border:1px solid #d1d5db; border-radius:.5rem; font-size:.85rem; }

    /* Stats */
    .stats-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:1.25rem; margin-bottom:1.5rem; }
    .stat-card { background:#fff; border:1px solid #e5e7eb; border-radius:.75rem; padding:1.25rem; display:flex; align-items:center; gap:1rem; box-shadow:0 1px 3px rgba(0,0,0,.06); }
    .stat-icon { width:3rem; height:3rem; border-radius:.75rem; display:flex; align-items:center; justify-content:center; font-size:1.25rem; }
    .stat-icon-blue { background:#dbeafe; color:#1d4ed8; }
    .stat-icon-green { background:#dcfce7; color:#16a34a; }
    .stat-icon-red { background:#fecaca; color:#dc2626; }
    .stat-value { font-size:1.6rem; font-weight:700; color:#111827; }
    .stat-label { color:#6b7280; font-size:.9rem; }

    /* Table */
    .table-section { background:#fff; border:1px solid #e5e7eb; border-radius:.75rem; overflow:hidden; margin-bottom:1rem; }
    .table-container { overflow-x:auto; }
    .data-table { width:100%; border-collapse:collapse; }
    .data-table th { background:#f9fafb; padding:0.8rem 1rem; text-align:left; font-weight:600; color:#374151; border-bottom:1px solid #e5e7eb; white-space:nowrap;}
    .data-table td { padding:0.8rem 1rem; border-bottom:1px solid #f3f4f6; vertical-align:top; }
    .data-table tbody tr:hover { background:#f9fafb; }

    /* Buttons & badges */
    .btn { display:inline-flex; align-items:center; gap:.5rem; padding:.55rem .9rem; border-radius:.5rem; font-size:.875rem; font-weight:500; text-decoration:none; transition:all .2s; border:1px solid transparent; cursor:pointer;}
    .btn-primary { background:#2563eb; color:#fff; }
    .btn-primary:hover { background:#1d4ed8; }
    .btn-outline { background:#fff; color:#374151; border-color:#d1d5db; }
    .btn-outline:hover { background:#f9fafb; border-color:#9ca3af; }
    .btn-subtle { background:transparent; color:#6b7280; }
    .btn-subtle:hover { color:#374151; background:#f3f4f6; }
    .btn-sm { padding:.35rem .6rem; font-size:.82rem; }

    .badge { display:inline-block; padding:.2rem .6rem; border-radius:9999px; font-size:.75rem; font-weight:600; background:#eef2f7; color:#374151;}
    .badge-success { background:#dcfce7; color:#166534; }
    .badge-danger  { background:#fecaca; color:#991b1b; }

    .empty-state { text-align:center; padding:2.5rem 1rem; }
    .empty-content { max-width:460px; margin:0 auto; }

    .col-actions .action-buttons { display:flex; gap:.5rem; flex-wrap:wrap; }
</style>

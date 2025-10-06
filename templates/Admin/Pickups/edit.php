<?php
$this->assign('title', 'Edit Pickup Location');
?>
<div class="admin-products">
    <div class="page-header">
        <div class="page-header-content">
            <h1 class="page-title">Edit Pickup Location</h1>
        </div>
    </div>

    <div class="filters-section">
        <?= $this->Form->create($loc) ?>

        <div class="form-grid">
            <?= $this->Form->control('name', ['label'=>'Name','required'=>true,'class'=>'form-control']) ?>
            <?= $this->Form->control('address', ['label'=>'Address','class'=>'form-control']) ?>

            <div class="form-row-2">
                <?= $this->Form->control('city', ['label'=>'City','class'=>'form-control']) ?>
                <?= $this->Form->control('postcode', ['label'=>'Postcode','class'=>'form-control']) ?>
            </div>

            <?= $this->Form->control('country', ['label'=>'Country (2-letter)','class'=>'form-control']) ?>
            <?= $this->Form->control('notes', ['label'=>'Notes','type'=>'textarea','rows'=>3,'class'=>'form-control']) ?>

            <label class="switch">
                <?= $this->Form->checkbox('is_active', ['checked'=>(bool)$loc->is_active]) ?>
                <span>Active</span>
            </label>
        </div>

        <div class="form-actions">
            <?= $this->Form->button('Save changes', ['class'=>'btn btn-primary']) ?>
            <?= $this->Html->link('Back', ['action'=>'index'], ['class'=>'btn btn-subtle']) ?>
        </div>

        <?= $this->Form->end() ?>
    </div>
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

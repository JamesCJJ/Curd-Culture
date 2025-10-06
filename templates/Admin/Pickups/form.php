<?php
/**
 * Admin Pickup Location Form (Add/Edit)
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\PickupLocation $pickup
 */
$isNew = $pickup->isNew();
$this->assign('title', $isNew ? 'Add Pickup Location' : 'Edit Pickup Location');
?>
<div class="admin-pickups">

    <!-- Header -->
    <div class="page-header">
        <div class="page-header-content">
            <h1 class="page-title"><?= $isNew ? 'Add Pickup Location' : 'Edit Pickup Location' ?></h1>
            <p class="page-subtitle"><?= $isNew ? 'Create a new pickup point for customers' : 'Update pickup details & availability' ?></p>
        </div>
        <div class="page-actions">
            <?= $this->Html->link('Back to List', ['action' => 'index'], ['class' => 'btn btn-outline']) ?>
        </div>
    </div>

    <!-- Form Card -->
    <div class="form-card">
        <?= $this->Form->create($pickup, ['class' => 'form-body']) ?>

        <div class="form-grid">
            <div class="form-section">
                <h3 class="section-title">Basic Info</h3>
                <div class="grid-2">
                    <?= $this->Form->control('name', ['class'=>'form-control', 'label'=>'Name']) ?>
                    <?= $this->Form->control('is_active', ['type'=>'checkbox', 'label'=>'Active']) ?>
                </div>
            </div>

            <div class="form-section">
                <h3 class="section-title">Address</h3>
                <div class="grid-2">
                    <?= $this->Form->control('address_line_1', ['class'=>'form-control','label'=>'Address Line 1']) ?>
                    <?= $this->Form->control('address_line_2', ['class'=>'form-control','label'=>'Address Line 2','required'=>false]) ?>
                    <?= $this->Form->control('suburb', ['class'=>'form-control','label'=>'Suburb']) ?>
                    <?= $this->Form->control('state', ['class'=>'form-control','label'=>'State']) ?>
                    <?= $this->Form->control('postcode', ['class'=>'form-control','label'=>'Postcode']) ?>
                </div>
            </div>

            <div class="form-section">
                <h3 class="section-title">Hours</h3>
                <div class="grid-2">
                    <?= $this->Form->control('open_from', [
                        'type'=>'time','empty'=>true,'second'=>false,
                        'class'=>'form-control','label'=>'Open From'
                    ]) ?>
                    <?= $this->Form->control('open_to', [
                        'type'=>'time','empty'=>true,'second'=>false,
                        'class'=>'form-control','label'=>'Open To'
                    ]) ?>
                </div>
                <p class="help-text">Leave blank if this pickup location has flexible hours.</p>
            </div>
        </div>

        <div class="form-actions">
            <?= $this->Form->button($isNew ? 'Create Pickup' : 'Update Pickup', ['class'=>'btn btn-primary']) ?>
            <?= $this->Html->link('Cancel', ['action'=>'index'], ['class'=>'btn btn-subtle']) ?>
        </div>

        <?= $this->Form->end() ?>
    </div>
</div>

<style>
    .admin-pickups { max-width: 900px; margin: 0 auto; padding: 2rem 1rem; }
    .page-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1.5rem; padding-bottom:1rem; border-bottom:1px solid #e5e7eb;}
    .page-title { font-size:1.6rem; font-weight:700; color:#111827; margin:0 0 .25rem;}
    .page-subtitle { color:#6b7280; margin:0;}
    .page-actions { display:flex; gap:.5rem; }

    .form-card { background:#fff; border:1px solid #e5e7eb; border-radius:.75rem; padding:1.25rem; }
    .form-grid { display:flex; flex-direction:column; gap:1rem; }
    .form-section { border:1px dashed #e5e7eb; border-radius:.6rem; padding:1rem; }
    .section-title { margin:0 0 .75rem; font-size:1.05rem; color:#374151; }

    .grid-2 { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:1rem; }
    .form-control { width:100%; padding:.5rem .6rem; border:1px solid #d1d5db; border-radius:.5rem; font-size:.95rem; }
    .help-text { color:#6b7280; font-size:.85rem; margin-top:.5rem; }

    .form-actions { display:flex; gap:.5rem; margin-top:1rem; }
    .btn { display:inline-flex; align-items:center; gap:.5rem; padding:.55rem .9rem; border-radius:.5rem; font-size:.875rem; font-weight:500; text-decoration:none; transition:all .2s; border:1px solid transparent; cursor:pointer;}
    .btn-primary { background:#2563eb; color:#fff; }
    .btn-primary:hover { background:#1d4ed8; }
    .btn-outline { background:#fff; color:#374151; border-color:#d1d5db; }
    .btn-outline:hover { background:#f9fafb; border-color:#9ca3af; }
    .btn-subtle { background:transparent; color:#6b7280; }
    .btn-subtle:hover { color:#374151; background:#f3f4f6; }

    @media (max-width: 680px) { .grid-2 { grid-template-columns:1fr; } }
</style>

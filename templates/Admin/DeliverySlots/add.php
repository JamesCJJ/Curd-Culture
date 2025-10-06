<?php
$this->assign('title', 'Add Delivery Slot');
?>
<div class="admin-products">
    <div class="page-header">
        <div class="page-header-content">
            <h1 class="page-title">Add Delivery Slot</h1>
            <p class="page-subtitle">Define a new delivery time window</p>
        </div>
    </div>

    <div class="filters-section">
        <?= $this->Form->create($slot) ?>

        <div class="form-grid">
            <?= $this->Form->control('name', [
                'label' => 'Name',
                'required' => true,
                'class' => 'form-control'
            ]) ?>

            <div class="form-row-2">
                <?= $this->Form->control('window_start', [
                    'label' => 'Window start (HH:MM)',
                    'placeholder' => '09:00',
                    'class' => 'form-control'
                ]) ?>
                <?= $this->Form->control('window_end', [
                    'label' => 'Window end (HH:MM)',
                    'placeholder' => '12:00',
                    'class' => 'form-control'
                ]) ?>
            </div>

            <?= $this->Form->control('capacity', [
                'label' => 'Capacity (blank = no limit)',
                'type'  => 'number',
                'min'   => 0,
                'class' => 'form-control'
            ]) ?>

            <label class="switch">
                <?= $this->Form->checkbox('is_active', ['checked' => true]) ?>
                <span>Active</span>
            </label>
        </div>

        <div class="form-actions">
            <?= $this->Form->button('Save', ['class' => 'btn btn-primary']) ?>
            <?= $this->Html->link('Cancel', ['action' => 'index'], ['class' => 'btn btn-subtle']) ?>
        </div>

        <?= $this->Form->end() ?>
    </div>
</div>

<style>
    /* Root */
    .admin-products { max-width: 1400px; margin: 0 auto; padding: 2rem 1rem; }

    /* Header */
    .page-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:2rem; padding-bottom:1.5rem; border-bottom:1px solid #e5e7eb; }
    .page-title { font-size:2rem; font-weight:700; color:#111827; margin:0 0 .5rem; }
    .page-subtitle { color:#6b7280; margin:0; }

    /* Card */
    .filters-section { background:#fff; border:1px solid #e5e7eb; border-radius:.75rem; padding:1.25rem; }

    /* NEW: form layout */
    .form-grid { display:grid; grid-template-columns:1fr; gap:1rem; }
    .form-row-2 { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:1rem; min-width:0; }
    @media (max-width: 720px){ .form-row-2{ grid-template-columns:1fr; } }

    /* Cake Form controls */
    .filters-section .input,
    .filters-section .form-group,
    .filters-section .field { margin:0; width:100%; }

    .filters-section label { display:block; margin-bottom:.35rem; color:#6b7280; }
    .filters-section input[type="text"],
    .filters-section input[type="email"],
    .filters-section input[type="number"],
    .filters-section input[type="time"],
    .filters-section select,
    .filters-section textarea {
        width:100%;
        border:1px solid #d1d5db;
        border-radius:.5rem;
        padding:.55rem .7rem;
        font-size:.875rem;
        background:#fff;
        transition:border-color .2s, box-shadow .2s;
    }
    .filters-section input:focus,
    .filters-section select:focus,
    .filters-section textarea:focus {
        outline:none;
        border-color:#2563eb;
        box-shadow:0 0 0 3px rgba(37,99,235,.12);
    }

    /* Checkbox row */
    .switch { display:flex; align-items:center; gap:.5rem; user-select:none; }

    /* Actions */
    .form-actions { display:flex; gap:.6rem; justify-content:flex-start; margin-top:1rem; }


    .btn{ display:inline-flex; align-items:center; gap:.5rem; padding:.55rem .9rem; border-radius:.5rem; font-size:.875rem; font-weight:500; text-decoration:none; transition:all .2s; border:1px solid transparent; cursor:pointer; }
    .btn-primary{ background:#2563eb; color:#fff; }
    .btn-primary:hover{ background:#1d4ed8; }
    .btn-subtle{ background:transparent; color:#6b7280; }
    .btn-subtle:hover{ color:#374151; background:#f3f4f6; }
</style>

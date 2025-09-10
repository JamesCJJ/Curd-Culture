<?php
/**
 * Admin Add User
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
$this->assign('title', 'Add User');
?>

<div class="admin-user-form">
    <div class="form-header">
        <div class="form-header-content">
            <h1 class="form-title">Add User</h1>
            <p class="form-subtitle">Create a new user account</p>
        </div>
        <div class="form-actions">
            <?= $this->Html->link('← Back to Users', ['action' => 'index'], ['class' => 'btn btn-outline']) ?>
        </div>
    </div>

    <?= $this->Form->create($user, ['novalidate' => true, 'class' => 'user-form']) ?>
    <div class="form-grid">
        <div class="form-group">
            <?= $this->Form->control('name', ['label' => 'Name', 'class' => 'form-control']) ?>
        </div>
        <div class="form-group">
            <?= $this->Form->control('email', ['label' => 'Email', 'class' => 'form-control', 'required' => true]) ?>
        </div>
        <div class="form-group">
            <?= $this->Form->control('password', ['label' => 'Password', 'class' => 'form-control', 'required' => true]) ?>
        </div>
        <div class="form-group">
            <?= $this->Form->control('role', [
                'label' => 'Role',
                'type' => 'select',
                'options' => ['admin' => 'Admin', 'customer' => 'Customer'],
                'class' => 'form-control',
                'default' => 'customer'
            ]) ?>
        </div>
        <div class="form-group">
            <?= $this->Form->control('status', [
                'label' => 'Status',
                'type' => 'select',
                'options' => ['active' => 'Active', 'inactive' => 'Inactive'],
                'class' => 'form-control',
                'default' => 'active'
            ]) ?>
        </div>
        <div class="form-group">
            <?= $this->Form->control('timezone', ['label' => 'Timezone', 'class' => 'form-control', 'default' => 'UTC']) ?>
        </div>
        <div class="form-group">
            <?= $this->Form->control('language', ['label' => 'Language', 'class' => 'form-control', 'default' => 'en']) ?>
        </div>
        <div class="form-group">
            <?= $this->Form->control('theme', ['label' => 'Theme', 'class' => 'form-control', 'default' => 'auto']) ?>
        </div>
    </div>

    <div class="form-footer">
        <div class="form-actions">
            <?= $this->Html->link('Cancel', ['action' => 'index'], ['class' => 'btn btn-outline']) ?>
            <?= $this->Form->button('Create User', ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
    <?= $this->Form->end() ?>
</div>

<style>
.admin-user-form{max-width:800px;margin:0 auto;padding:1.25rem 1rem}
.form-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.25rem;padding-bottom:1rem;border-bottom:1px solid #e5e7eb}
.form-title{font-size:1.6rem;font-weight:700;margin:0}
.form-subtitle{color:#6b7280;margin:.25rem 0 0}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:.8rem;background:#fff;border:1px solid #eef0f3;border-radius:.6rem;padding:1rem}
.form-group label{font-weight:600}
.form-control{padding:.6rem;border:1px solid #d1d5db;border-radius:.5rem}
.form-footer{display:flex;justify-content:flex-end;margin-top:1rem}
.btn{display:inline-block;padding:.45rem .7rem;border-radius:.45rem;border:1px solid #d1d5db;text-decoration:none;color:#111;background:#fff}
.btn-outline{background:#fff}
.btn-primary{background:#2563eb;color:#fff;border-color:#2563eb}
@media (max-width: 900px){.form-grid{grid-template-columns:1fr}}
</style>

<?php
$this->extend('/layout/customer');
$this->assign('title', 'Profile');
?>

<h2><i class="bi bi-person me-2"></i>Profile</h2>

<div class="row">
    <div class="col-md-8">
        <!-- Personal Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Personal Information</h5>
            </div>
            <div class="card-body">
                <?= $this->Form->create($user) ?>
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <?= $this->Form->email('email', [
                        'class' => 'form-control',
                        'required' => true
                    ]) ?>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check me-2"></i>Update Profile
                    </button>
                </div>
                <?= $this->Form->end() ?>
            </div>
        </div>

        <!-- Address Management -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Addresses</h5>
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                    <i class="bi bi-plus me-1"></i>Add Address
                </button>
            </div>
            <div class="card-body">
                <div class="row" id="addressList">
                    <?php if ($addresses->isEmpty()): ?>
                        <div class="col-12">
                            <div class="text-center py-4">
                                <i class="bi bi-house display-4 text-muted mb-3"></i>
                                <h5 class="text-muted">No addresses found</h5>
                                <p class="text-muted">Add your first address to get started.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($addresses as $address): ?>
                            <div class="col-md-6 mb-3">
                                <div class="border rounded p-3 position-relative <?= $address->is_default ? 'border-primary' : '' ?>">
                                    <?php if ($address->is_default): ?>
                                        <div class="position-absolute top-0 start-0 p-2">
                                            <span class="badge bg-primary">Default</span>
                                        </div>
                                    <?php endif; ?>

                                    <div class="position-absolute top-0 end-0 p-2">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <button class="dropdown-item" onclick="editAddress(<?= (int)$address->id ?>)">
                                                        <i class="bi bi-pencil me-2"></i>Edit
                                                    </button>
                                                </li>

                                                <?php if (!(int)$address->is_default): ?>
                                                    <li>
                                                        <?= $this->Form->postLink(
                                                            '<i class="bi bi-check me-2"></i>Set as Default',
                                                            ['action' => 'setDefaultAddress', (int)$address->id],
                                                            [
                                                                'class'  => 'dropdown-item',
                                                                'escape' => false,
                                                                'data'   => ['id' => (int)$address->id],
                                                            ]
                                                        ) ?>
                                                    </li>
                                                <?php endif; ?>

                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <?= $this->Form->postLink(
                                                        '<i class="bi bi-trash me-2"></i>Delete',
                                                        ['action' => 'deleteAddress', (int)$address->id],
                                                        [
                                                            'class'   => 'dropdown-item text-danger',
                                                            'escape'  => false,
                                                            'confirm' => 'Are you sure you want to delete this address?',
                                                            'data'    => ['id' => (int)$address->id],
                                                        ]
                                                    ) ?>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <address class="mb-0" style="margin-top: <?= $address->is_default ? '1.5rem' : '0' ?>;">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <strong><?= h(trim(($address->first_name ?? '') . ' ' . ($address->last_name ?? ''))) ?></strong>
                                            <span class="badge bg-light text-dark">
                                                <?= h(ucfirst($address->type ?? 'billing')) ?>
                                            </span>
                                        </div>

                                        <?php if (!empty($address->company)): ?>
                                            <?= h($address->company) ?><br>
                                        <?php endif; ?>

                                        <?= h($address->address_line_1) ?><br>
                                        <?php if (!empty($address->address_line_2)): ?>
                                            <?= h($address->address_line_2) ?><br>
                                        <?php endif; ?>
                                        <?= h($address->suburb) ?>, <?= h($address->state) ?> <?= h($address->postcode) ?><br>
                                        <?= h($address->country) ?><br>
                                        <?php if (!empty($address->phone)): ?>
                                            <small class="text-muted">+61 <?= h($address->phone) ?></small>
                                        <?php endif; ?>
                                    </address>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <div class="col-md-6 mb-3">
                        <div class="border border-dashed rounded p-3 d-flex align-items-center justify-content-center text-muted" style="min-height: 120px; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                            <div class="text-center">
                                <i class="bi bi-plus-circle display-6 mb-2"></i>
                                <p class="mb-0">Add a new address</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Account Settings -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Account Settings</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center py-2">
                    <span>Email notifications</span>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center py-2">
                    <span>SMS notifications</span>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="smsNotifications">
                    </div>
                </div>
            </div>
        </div>

        <!-- Security -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Security</h6>
            </div>
            <div class="card-body">
                <button type="button" class="btn btn-outline-secondary w-100 mb-2">
                    <i class="bi bi-key me-2"></i>Change Password
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Address Modal -->
<div class="modal fade" id="addAddressModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add billing address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?= $this->Form->create(null, [
                    'url' => ['action' => 'addAddress'],
                    'id' => 'addAddressForm'
                ]) ?>

                <!-- 隐藏：与 UI 文案一致，默认添加 billing 地址 -->
                <?= $this->Form->hidden('type', ['value' => 'billing']) ?>

                <div class="mb-3">
                    <label for="country" class="form-label">Country/region</label>
                    <?= $this->Form->select('country', ['Australia' => 'Australia'], [
                        'value' => 'Australia',
                        'class' => 'form-select',
                        'required' => true
                    ]) ?>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="first_name" class="form-label">First name</label>
                            <?= $this->Form->text('first_name', [
                                'class' => 'form-control',
                                'required' => true
                            ]) ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last name</label>
                            <?= $this->Form->text('last_name', [
                                'class' => 'form-control',
                                'required' => true
                            ]) ?>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="company" class="form-label">Company/attention</label>
                    <?= $this->Form->text('company', [
                        'class' => 'form-control'
                    ]) ?>
                </div>

                <div class="mb-3">
                    <label for="address_line_1" class="form-label">Address</label>
                    <?= $this->Form->text('address_line_1', [
                        'class' => 'form-control',
                        'required' => true
                    ]) ?>
                </div>

                <div class="mb-3">
                    <label for="address_line_2" class="form-label">Apartment, suite, etc (optional)</label>
                    <?= $this->Form->text('address_line_2', [
                        'class' => 'form-control'
                    ]) ?>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="suburb" class="form-label">Suburb</label>
                            <?= $this->Form->text('suburb', [
                                'class' => 'form-control',
                                'required' => true
                            ]) ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="state" class="form-label">State/territory</label>
                            <?= $this->Form->select('state', [
                                '' => 'Select state',
                                'NSW' => 'New South Wales',
                                'VIC' => 'Victoria',
                                'QLD' => 'Queensland',
                                'WA'  => 'Western Australia',
                                'SA'  => 'South Australia',
                                'TAS' => 'Tasmania',
                                'ACT' => 'Australian Capital Territory',
                                'NT'  => 'Northern Territory'
                            ], [
                                'class' => 'form-select',
                                'required' => true
                            ]) ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="postcode" class="form-label">Postcode</label>
                            <?= $this->Form->text('postcode', [
                                'class' => 'form-control',
                                'pattern' => '[0-9]{4}',
                                'required' => true
                            ]) ?>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <div class="input-group">
                        <span class="input-group-text">🇦🇺 +61</span>
                        <?= $this->Form->tel('phone', [
                            'class' => 'form-control',
                            'placeholder' => '400 000 000'
                        ]) ?>
                    </div>
                </div>

                <div class="form-check mb-2">
                    <?= $this->Form->checkbox('is_default', ['value' => 1, 'class' => 'form-check-input', 'id' => 'addrDefault']) ?>
                    <label class="form-check-label" for="addrDefault">Set as default billing address</label>
                </div>

                <?= $this->Form->end() ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="addAddressForm" class="btn btn-dark">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
    function editAddress(id) {
        alert('Edit functionality coming soon!');
    }

    // Auto-reset the add-address form when the modal closes
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('addAddressModal');
        if (modal) {
            modal.addEventListener('hidden.bs.modal', function () {
                const form = document.getElementById('addAddressForm');
                if (form) form.reset();
            });
        }
    });
</script>

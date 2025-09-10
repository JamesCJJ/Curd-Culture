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
                    <!-- Sample address - in a real app, this would be populated from database -->
                    <div class="col-md-6 mb-3">
                        <div class="border rounded p-3 position-relative">
                            <div class="position-absolute top-0 end-0 p-2">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="editAddress(1)">Edit</a></li>
                                        <li><a class="dropdown-item text-danger" href="#" onclick="deleteAddress(1)">Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                            
                            <address class="mb-0">
                                <strong>Home</strong><br>
                                123 Example Street<br>
                                Melbourne, VIC 3000<br>
                                Australia<br>
                                <small class="text-muted">+61 400 000 000</small>
                            </address>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="border border-dashed rounded p-3 d-flex align-items-center justify-content-center text-muted" style="min-height: 120px;">
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
                <form id="addAddressForm">
                    <div class="mb-3">
                        <label for="country" class="form-label">Country/region</label>
                        <select class="form-select" id="country" name="country" required>
                            <option value="Australia" selected>Australia</option>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="firstName" class="form-label">First name</label>
                                <input type="text" class="form-control" id="firstName" name="firstName" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="lastName" class="form-label">Last name</label>
                                <input type="text" class="form-control" id="lastName" name="lastName" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="company" class="form-label">Company/attention</label>
                        <input type="text" class="form-control" id="company" name="company">
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" class="form-control" id="address" name="address" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="apartment" class="form-label">Apartment, suite, etc (optional)</label>
                        <input type="text" class="form-control" id="apartment" name="apartment">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="suburb" class="form-label">Suburb</label>
                                <input type="text" class="form-control" id="suburb" name="suburb" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="state" class="form-label">State/territory</label>
                                <select class="form-select" id="state" name="state" required>
                                    <option value="">Select state</option>
                                    <option value="NSW">New South Wales</option>
                                    <option value="VIC">Victoria</option>
                                    <option value="QLD">Queensland</option>
                                    <option value="WA">Western Australia</option>
                                    <option value="SA">South Australia</option>
                                    <option value="TAS">Tasmania</option>
                                    <option value="ACT">Australian Capital Territory</option>
                                    <option value="NT">Northern Territory</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="postcode" class="form-label">Postcode</label>
                                <input type="text" class="form-control" id="postcode" name="postcode" pattern="[0-9]{4}" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <div class="input-group">
                            <select class="form-select" style="max-width: 100px;" id="phoneCode" name="phoneCode">
                                <option value="+61" selected>🇦🇺 +61</option>
                            </select>
                            <input type="tel" class="form-control" id="phone" name="phone" placeholder="400 000 000">
                        </div>
                    </div>
                </form>
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
    // In a real application, this would load the address data and populate the modal
    alert('Edit address functionality would be implemented here');
}

function deleteAddress(id) {
    if (confirm('Are you sure you want to delete this address?')) {
        // In a real application, this would make an AJAX call to delete the address
        alert('Delete address functionality would be implemented here');
    }
}

document.getElementById('addAddressForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Get form data
    const formData = new FormData(this);
    
    // In a real application, this would make an AJAX call to save the address
    alert('Address saved successfully! (This would be implemented with proper backend integration)');
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('addAddressModal'));
    modal.hide();
    
    // Reset form
    this.reset();
});
</script>

<?php
/**
 * Admin Edit Product
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Product $product
 */
$this->assign('title', 'Edit Product');
?>

<div class="admin-product-form">
    <div class="form-header">
        <div class="form-header-content">
            <h1 class="form-title">Edit Product</h1>
            <p class="form-subtitle">Update product information and settings</p>
        </div>
        <div class="form-actions">
            <?= $this->Html->link(
                '← Back to Products',
                ['action' => 'index'],
                ['class' => 'btn btn-outline']
            ) ?>
            <?= $this->Html->link(
                'View Product',
                ['action' => 'view', $product->id],
                ['class' => 'btn btn-subtle']
            ) ?>
        </div>
    </div>

    <?= $this->Form->create($product, [
        'type' => 'file',
        'class' => 'product-form',
        'novalidate' => true
    ]) ?>

    <div class="form-grid">
        <!-- Basic Information -->
        <div class="form-section">
            <h3 class="section-title">Basic Information</h3>
            <div class="form-row">
                <div class="form-group">
                    <?= $this->Form->control('name', [
                        'label' => 'Product Name *',
                        'class' => 'form-control',
                        'placeholder' => 'Enter product name',
                        'required' => true
                    ]) ?>
                </div>
                <div class="form-group">
                    <?= $this->Form->control('slug', [
                        'label' => 'URL Slug',
                        'class' => 'form-control',
                        'placeholder' => 'Auto-generated from name'
                    ]) ?>
                </div>
            </div>
            
            <div class="form-group">
                <?= $this->Form->control('summary', [
                    'label' => 'Short Summary *',
                    'class' => 'form-control',
                    'placeholder' => 'Brief description for product listings',
                    'required' => true
                ]) ?>
            </div>
            
            <div class="form-group">
                <?= $this->Form->control('description', [
                    'type' => 'textarea',
                    'label' => 'Full Description',
                    'class' => 'form-control',
                    'rows' => 5,
                    'placeholder' => 'Detailed product description'
                ]) ?>
            </div>
        </div>

        <!-- Pricing & Inventory -->
        <div class="form-section">
            <h3 class="section-title">Pricing & Inventory</h3>
            <div class="form-row">
                <div class="form-group">
                    <?= $this->Form->control('price', [
                        'type' => 'number',
                        'step' => '0.01',
                        'label' => 'Price *',
                        'class' => 'form-control',
                        'placeholder' => '0.00',
                        'required' => true
                    ]) ?>
                </div>
                <div class="form-group">
                    <?= $this->Form->control('currency', [
                        'type' => 'select',
                        'options' => [
                            'AUD' => 'AUD - Australian Dollar',
                            'USD' => 'USD - US Dollar',
                            'EUR' => 'EUR - Euro',
                            'GBP' => 'GBP - British Pound'
                        ],
                        'label' => 'Currency',
                        'class' => 'form-control'
                    ]) ?>
                </div>
            </div>
            
            <div class="form-group">
                <?= $this->Form->control('stock', [
                    'type' => 'number',
                    'label' => 'Stock Quantity *',
                    'class' => 'form-control',
                    'placeholder' => '0',
                    'required' => true,
                    'min' => 0
                ]) ?>
                <div class="stock-status">
                    <?php
                    $stockStatus = 'In Stock';
                    $stockClass = 'stock-good';
                    if ($product->stock <= 0) {
                        $stockStatus = 'Out of Stock';
                        $stockClass = 'stock-danger';
                    } elseif ($product->stock <= 10) {
                        $stockStatus = 'Low Stock';
                        $stockClass = 'stock-warning';
                    }
                    ?>
                    <span class="stock-indicator <?= $stockClass ?>">
                        Current Status: <?= $stockStatus ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Product Details -->
        <div class="form-section">
            <h3 class="section-title">Product Details</h3>
            <div class="form-row">
                <div class="form-group">
                    <?= $this->Form->control('origin_country', [
                        'label' => 'Country of Origin',
                        'class' => 'form-control',
                        'placeholder' => 'e.g., Australia, France, Italy'
                    ]) ?>
                </div>
                <div class="form-group">
                    <?= $this->Form->control('milk_type', [
                        'type' => 'select',
                        'options' => [
                            '' => 'Select milk type',
                            'Cow' => 'Cow',
                            'Goat' => 'Goat',
                            'Sheep' => 'Sheep',
                            'Buffalo' => 'Buffalo',
                            'Mixed' => 'Mixed'
                        ],
                        'label' => 'Milk Type',
                        'class' => 'form-control'
                    ]) ?>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <?= $this->Form->control('age', [
                        'label' => 'Age/Maturation',
                        'class' => 'form-control',
                        'placeholder' => 'e.g., 12 months, Fresh, 2-3 weeks'
                    ]) ?>
                </div>
                <div class="form-group">
                    <?= $this->Form->control('style', [
                        'label' => 'Cheese Style',
                        'class' => 'form-control',
                        'placeholder' => 'e.g., Hard, Soft, Semi-hard, Blue'
                    ]) ?>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <?= $this->Form->control('rennet', [
                        'type' => 'select',
                        'options' => [
                            '' => 'Select rennet type',
                            'Animal' => 'Animal Rennet',
                            'Vegetarian' => 'Vegetarian Rennet',
                            'Microbial' => 'Microbial Rennet'
                        ],
                        'label' => 'Rennet Type',
                        'class' => 'form-control'
                    ]) ?>
                </div>
                <div class="form-group">
                    <?= $this->Form->control('pasteurised', [
                        'type' => 'select',
                        'options' => [
                            '' => 'Select pasteurisation',
                            'yes' => 'Yes',
                            'no' => 'No'
                        ],
                        'label' => 'Pasteurised',
                        'class' => 'form-control'
                    ]) ?>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <?= $this->Form->control('fat_content', [
                        'label' => 'Fat Content',
                        'class' => 'form-control',
                        'placeholder' => 'e.g., 32%, 25-30%'
                    ]) ?>
                </div>
                <div class="form-group">
                    <?= $this->Form->control('allergens', [
                        'label' => 'Allergens',
                        'class' => 'form-control',
                        'placeholder' => 'e.g., Milk, Contains dairy'
                    ]) ?>
                </div>
            </div>
        </div>

        <!-- Dietary Information -->
        <div class="form-section">
            <h3 class="section-title">Dietary Information</h3>
            <div class="form-row">
                <div class="form-group checkbox-group">
                    <?= $this->Form->control('vegetarian', [
                        'type' => 'checkbox',
                        'label' => 'Vegetarian Friendly',
                        'class' => 'form-checkbox'
                    ]) ?>
                </div>
                <div class="form-group checkbox-group">
                    <?= $this->Form->control('gluten_free', [
                        'type' => 'checkbox',
                        'label' => 'Gluten Free',
                        'class' => 'form-checkbox'
                    ]) ?>
                </div>
                <div class="form-group checkbox-group">
                    <?= $this->Form->control('lactose_free', [
                        'type' => 'checkbox',
                        'label' => 'Lactose Free',
                        'class' => 'form-checkbox'
                    ]) ?>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="form-section">
            <h3 class="section-title">Additional Information</h3>
            <div class="form-group">
                <?= $this->Form->control('pairing_notes', [
                    'type' => 'textarea',
                    'label' => 'Pairing Notes',
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Wine pairings, serving suggestions, accompaniments'
                ]) ?>
            </div>
            
            <div class="form-group">
                <?= $this->Form->control('awards', [
                    'type' => 'textarea',
                    'label' => 'Awards & Recognition',
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Awards, certifications, special recognition'
                ]) ?>
            </div>
        </div>

        <!-- Image Upload -->
        <div class="form-section">
            <h3 class="section-title">Product Image</h3>
            
            <?php if ($product->image_url): ?>
                <div class="current-image">
                    <div class="image-preview">
                        <img src="<?= h($this->Url->webroot($product->image_url)) ?>" 
                             alt="<?= h($product->name) ?>" 
                             class="current-product-image">
                    </div>
                    <div class="image-info">
                        <h4>Current Image</h4>
                        <p>Upload a new image to replace the current one</p>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <?= $this->Form->control('image_file', [
                    'type' => 'file',
                    'label' => $product->image_url ? 'Replace Image' : 'Upload Image',
                    'class' => 'form-file',
                    'accept' => 'image/jpeg,image/jpg,image/png,image/gif,image/webp'
                ]) ?>
                <div class="form-help">
                    Upload a high-quality product image. <strong>Supported formats:</strong> JPG, PNG, GIF, WebP. <strong>Maximum size:</strong> 2MB. <strong>Recommended dimensions:</strong> 800x600px or larger.
                </div>
                <div id="file-validation-message" class="validation-message" style="display: none; color: #dc3545; margin-top: 5px; font-size: 0.875rem;"></div>
            </div>
        </div>
    </div>

    <div class="form-footer">
        <div class="form-meta">
            <div class="meta-item">
                <span class="meta-label">Created:</span>
                <span class="meta-value"><?= $product->created->format('M j, Y \a\t g:i A') ?></span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Last Modified:</span>
                <span class="meta-value"><?= $product->modified->format('M j, Y \a\t g:i A') ?></span>
            </div>
        </div>
        <div class="form-actions">
            <?= $this->Html->link(
                'Cancel',
                ['action' => 'index'],
                ['class' => 'btn btn-outline']
            ) ?>
            <?= $this->Form->button('Update Product', [
                'type' => 'submit',
                'class' => 'btn btn-primary'
            ]) ?>
        </div>
    </div>

    <?= $this->Form->end() ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.querySelector('input[type="file"][name="image_file"]');
    const validationMessage = document.getElementById('file-validation-message');
    
    if (fileInput && validationMessage) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            validationMessage.style.display = 'none';
            
            if (file) {
                // Check file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                const fileType = file.type.toLowerCase();
                
                if (!allowedTypes.includes(fileType)) {
                    const supportedTypes = 'JPG, PNG, GIF, WebP';
                    validationMessage.textContent = `Unsupported file type "${file.name.split('.').pop().toUpperCase()}". Please upload an image file in one of these formats: ${supportedTypes}`;
                    validationMessage.style.display = 'block';
                    fileInput.value = ''; // Clear the input
                    return;
                }
                
                // Check file size (2MB = 2 * 1024 * 1024 bytes)
                const maxSize = 2 * 1024 * 1024;
                if (file.size > maxSize) {
                    validationMessage.textContent = 'Image file is too large. Maximum size is 2MB.';
                    validationMessage.style.display = 'block';
                    fileInput.value = ''; // Clear the input
                    return;
                }
                
                // If validation passes, show success message
                validationMessage.textContent = 'File selected successfully!';
                validationMessage.style.color = '#28a745';
                validationMessage.style.display = 'block';
            }
        });
    }
});
</script>

<style>
/* Additional styles for edit form */
/* Base font size for this page */
html{font-size:18px}
.current-image {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
}

.image-preview {
    flex-shrink: 0;
}

.current-product-image {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 0.5rem;
    border: 1px solid #d1d5db;
}

.image-info h4 {
    margin: 0 0 0.25rem 0;
    font-size: 1rem;
    font-weight: 600;
    color: #374151;
}

.image-info p {
    margin: 0;
    font-size: 0.9rem;
    color: #6b7280;
}

.stock-status {
    margin-top: 0.5rem;
}

.stock-indicator {
    display: inline-block;
    padding: 0.375rem 0.875rem;
    border-radius: 9999px;
    font-size: 0.875rem;
    font-weight: 600;
}

.stock-good {
    background: #dcfce7;
    color: #166534;
}

.stock-warning {
    background: #fef3c7;
    color: #92400e;
}

.stock-danger {
    background: #fecaca;
    color: #991b1b;
}

.form-meta {
    display: flex;
    gap: 2rem;
    color: #6b7280;
    font-size: 1rem;
}

.meta-item {
    display: flex;
    gap: 0.5rem;
}

.meta-label {
    font-weight: 500;
}

/* Include all styles from add.php */
.admin-product-form {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 1rem;
}

.form-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.form-title {
    font-size: 2.125rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 0.5rem 0;
}

.form-subtitle {
    color: #6b7280;
    margin: 0;
}

.product-form {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    overflow: hidden;
}

.form-grid {
    padding: 2rem;
}

.form-section {
    margin-bottom: 2.5rem;
}

.form-section:last-child {
    margin-bottom: 0;
}

.section-title {
    font-size: 1.35rem;
    font-weight: 600;
    color: #111827;
    margin: 0 0 1.5rem 0;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #f3f4f6;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.form-row:last-child {
    margin-bottom: 0;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 1.0625rem;
}

.form-control {
    width: 100%;
    padding: 0.85rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 1.0625rem;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.form-control::placeholder {
    color: #9ca3af;
}

textarea.form-control {
    resize: vertical;
    min-height: 100px;
}

.checkbox-group {
    flex-direction: row;
    align-items: center;
    gap: 0.5rem;
}

.checkbox-group label {
    margin-bottom: 0;
    cursor: pointer;
}

.form-checkbox {
    width: auto;
    margin: 0;
}

.form-file {
    width: 100%;
    padding: 0.75rem;
    border: 2px dashed #d1d5db;
    border-radius: 0.5rem;
    background: #f9fafb;
    transition: border-color 0.2s;
}

.form-file:hover {
    border-color: #9ca3af;
}

.form-help {
    font-size: 0.95rem;
    color: #6b7280;
    margin-top: 0.5rem;
    line-height: 1.6;
}

.form-footer {
    background: #f9fafb;
    padding: 1.5rem 2rem;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.form-actions {
    display: flex;
    gap: 1rem;
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.85rem 1.35rem;
    border-radius: 0.5rem;
    font-size: 1.0625rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
    border: 1px solid transparent;
    cursor: pointer;
}

.btn-primary {
    background: #2563eb;
    color: #fff;
}

.btn-primary:hover {
    background: #1d4ed8;
}

.btn-outline {
    background: #fff;
    color: #374151;
    border-color: #d1d5db;
}

.btn-outline:hover {
    background: #f9fafb;
    border-color: #9ca3af;
}

.btn-subtle {
    background: transparent;
    color: #6b7280;
}

.btn-subtle:hover {
    color: #374151;
    background: #f3f4f6;
}

/* Error Styles */
.form-group .error {
    color: #dc2626;
    font-size: 0.75rem;
    margin-top: 0.25rem;
}

.form-control.error {
    border-color: #dc2626;
}

.form-control.error:focus {
    border-color: #dc2626;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

/* Responsive */
@media (max-width: 768px) {
    .form-header {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .form-grid {
        padding: 1.5rem;
    }
    
    .form-footer {
        padding: 1.5rem;
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }
    
    .form-actions {
        flex-direction: column-reverse;
    }
    
    .btn {
        justify-content: center;
    }
    
    .current-image {
        flex-direction: column;
        text-align: center;
    }
    
    .form-meta {
        flex-direction: column;
        gap: 0.5rem;
    }
}
</style>

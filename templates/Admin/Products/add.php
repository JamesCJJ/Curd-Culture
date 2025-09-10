<?php
/**
 * Admin Add Product
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Product $product
 */
$this->assign('title', 'Add Product');
?>

<div class="admin-product-form">
    <div class="form-header">
        <div class="form-header-content">
            <h1 class="form-title">Add New Product</h1>
            <p class="form-subtitle">Create a new product in your catalog</p>
        </div>
        <div class="form-actions">
            <?= $this->Html->link(
                '← Back to Products',
                ['action' => 'index'],
                ['class' => 'btn btn-outline']
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
                        'class' => 'form-control',
                        'default' => 'AUD'
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
            <div class="form-group">
                <?= $this->Form->control('image_file', [
                    'type' => 'file',
                    'label' => 'Upload Image',
                    'class' => 'form-file',
                    'accept' => 'image/*'
                ]) ?>
                <div class="form-help">
                    Upload a high-quality product image (JPG, PNG). Recommended size: 800x600px or larger.
                </div>
            </div>
        </div>
    </div>

    <div class="form-footer">
        <div class="form-actions">
            <?= $this->Html->link(
                'Cancel',
                ['action' => 'index'],
                ['class' => 'btn btn-outline']
            ) ?>
            <?= $this->Form->button('Create Product', [
                'type' => 'submit',
                'class' => 'btn btn-primary'
            ]) ?>
        </div>
    </div>

    <?= $this->Form->end() ?>
</div>

<style>
/* Admin Product Form Styles */
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
    font-size: 2rem;
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
    font-size: 1.25rem;
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
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 0.875rem;
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
    font-size: 0.75rem;
    color: #6b7280;
    margin-top: 0.5rem;
}

.form-footer {
    background: #f9fafb;
    padding: 1.5rem 2rem;
    border-top: 1px solid #e5e7eb;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
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
    }
    
    .form-actions {
        flex-direction: column-reverse;
    }
    
    .btn {
        justify-content: center;
    }
}
</style>

<script>
// Auto-generate slug from product name
document.addEventListener('DOMContentLoaded', function() {
    const nameField = document.querySelector('input[name="name"]');
    const slugField = document.querySelector('input[name="slug"]');
    
    if (nameField && slugField) {
        nameField.addEventListener('input', function() {
            if (!slugField.value || slugField.dataset.autoGenerated) {
                const slug = this.value
                    .toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .trim('-');
                slugField.value = slug;
                slugField.dataset.autoGenerated = 'true';
            }
        });
        
        slugField.addEventListener('input', function() {
            delete this.dataset.autoGenerated;
        });
    }
});
</script>

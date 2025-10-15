<?php
/**
 * Admin View Product
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Product $product
 */
$this->assign('title', 'View Product');
?>

<div class="admin-product-view">
    <div class="view-header">
        <div class="view-header-content">
            <h1 class="view-title"><?= h($product->name) ?></h1>
            <p class="view-subtitle">Product Details & Information</p>
        </div>
        <div class="view-actions">
            <?= $this->Html->link(
                '← Back to Products',
                ['action' => 'index'],
                ['class' => 'btn btn-outline']
            ) ?>
            <?= $this->Html->link(
                'Edit Product',
                ['action' => 'edit', $product->id],
                ['class' => 'btn btn-primary']
            ) ?>
        </div>
    </div>

    <div class="product-details">
        <!-- Product Overview -->
        <div class="detail-section overview-section">
            <div class="product-image-container">
                <?php if ($product->image_url): ?>
                    <img src="<?= h($this->Url->webroot($product->image_url)) ?>" 
                         alt="<?= h($product->name) ?>" 
                         class="product-image">
                <?php else: ?>
                    <div class="image-placeholder">
                        <i class="icon-image"></i>
                        <span>No Image</span>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="product-overview">
                <div class="overview-item">
                    <span class="overview-label">Summary</span>
                    <span class="overview-value"><?= h($product->summary) ?: 'Not specified' ?></span>
                </div>
                
                <div class="overview-item">
                    <span class="overview-label">Price</span>
                    <span class="overview-value price"><?= h($product->currency) ?> <?= number_format($product->price, 2) ?></span>
                </div>
                
                <div class="overview-item">
                    <span class="overview-label">Stock Status</span>
                    <span class="overview-value">
                        <?php
                        if ($product->stock <= 0) {
                            echo '<span class="badge badge-danger">Out of Stock (0)</span>';
                        } elseif ($product->stock <= 10) {
                            echo '<span class="badge badge-warning">Low Stock (' . $product->stock . ')</span>';
                        } else {
                            echo '<span class="badge badge-success">In Stock (' . $product->stock . ')</span>';
                        }
                        ?>
                    </span>
                </div>
                
                <div class="overview-item">
                    <span class="overview-label">URL Slug</span>
                    <span class="overview-value">
                        <code><?= h($product->slug) ?></code>
                        <?= $this->Html->link(
                            'View on Site',
                            ['prefix' => false, 'controller' => 'Products', 'action' => 'view', $product->slug],
                            ['class' => 'view-link', 'target' => '_blank', 'rel' => 'noopener']
                        ) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Product Information Grid -->
        <div class="details-grid">
            <!-- Basic Information -->
            <div class="detail-card">
                <h3 class="card-title">Basic Information</h3>
                <div class="detail-items">
                    <div class="detail-item">
                        <span class="detail-label">Product ID</span>
                        <span class="detail-value">#<?= $product->id ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Name</span>
                        <span class="detail-value"><?= h($product->name) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Summary</span>
                        <span class="detail-value"><?= h($product->summary) ?: 'Not specified' ?></span>
                    </div>
                    <?php if ($product->description): ?>
                        <div class="detail-item full-width">
                            <span class="detail-label">Description</span>
                            <div class="detail-value description">
                                <?= nl2br(h($product->description)) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pricing & Inventory -->
            <div class="detail-card">
                <h3 class="card-title">Pricing & Inventory</h3>
                <div class="detail-items">
                    <div class="detail-item">
                        <span class="detail-label">Price</span>
                        <span class="detail-value price"><?= h($product->currency) ?> <?= number_format($product->price, 2) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Currency</span>
                        <span class="detail-value"><?= h($product->currency) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Stock Quantity</span>
                        <span class="detail-value"><?= number_format($product->stock) ?> units</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Rating</span>
                        <span class="detail-value">
                            <?php if ($product->rating): ?>
                                <div class="rating">
                                    <span class="rating-value"><?= number_format($product->rating, 1) ?></span>
                                    <span class="rating-stars">
                                        <?php
                                        $fullStars = floor($product->rating);
                                        $hasHalfStar = ($product->rating - $fullStars) >= 0.5;
                                        
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $fullStars) {
                                                echo '<span class="star star-full">★</span>';
                                            } elseif ($i == $fullStars + 1 && $hasHalfStar) {
                                                echo '<span class="star star-half">☆</span>';
                                            } else {
                                                echo '<span class="star star-empty">☆</span>';
                                            }
                                        }
                                        ?>
                                    </span>
                                </div>
                            <?php else: ?>
                                Not rated
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Product Details -->
            <div class="detail-card">
                <h3 class="card-title">Product Details</h3>
                <div class="detail-items">
                    <div class="detail-item">
                        <span class="detail-label">Origin Country</span>
                        <span class="detail-value"><?= h($product->origin_country) ?: 'Not specified' ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Milk Type</span>
                        <span class="detail-value"><?= h($product->milk_type) ?: 'Not specified' ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Age/Maturation</span>
                        <span class="detail-value"><?= h($product->age) ?: 'Not specified' ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Style</span>
                        <span class="detail-value"><?= h($product->style) ?: 'Not specified' ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Rennet Type</span>
                        <span class="detail-value"><?= h($product->rennet) ?: 'Not specified' ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Pasteurised</span>
                        <span class="detail-value">
                            <?php
                            if ($product->pasteurised === 'yes') {
                                echo '<span class="badge badge-info">Yes</span>';
                            } elseif ($product->pasteurised === 'no') {
                                echo '<span class="badge badge-neutral">No</span>';
                            } else {
                                echo 'Not specified';
                            }
                            ?>
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Fat Content</span>
                        <span class="detail-value"><?= h($product->fat_content) ?: 'Not specified' ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Allergens</span>
                        <span class="detail-value"><?= h($product->allergens) ?: 'Not specified' ?></span>
                    </div>
                </div>
            </div>

            <!-- Dietary Information -->
            <div class="detail-card">
                <h3 class="card-title">Dietary Information</h3>
                <div class="dietary-badges">
                    <?php if ($product->vegetarian): ?>
                        <span class="dietary-badge vegetarian">
                            <i class="icon-check"></i> Vegetarian
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($product->gluten_free): ?>
                        <span class="dietary-badge gluten-free">
                            <i class="icon-check"></i> Gluten Free
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($product->lactose_free): ?>
                        <span class="dietary-badge lactose-free">
                            <i class="icon-check"></i> Lactose Free
                        </span>
                    <?php endif; ?>
                    
                    <?php if (!$product->vegetarian && !$product->gluten_free && !$product->lactose_free): ?>
                        <span class="no-dietary">No dietary certifications specified</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Additional Information -->
            <?php if ($product->pairing_notes || $product->awards): ?>
                <div class="detail-card full-width">
                    <h3 class="card-title">Additional Information</h3>
                    <div class="detail-items">
                        <?php if ($product->pairing_notes): ?>
                            <div class="detail-item full-width">
                                <span class="detail-label">Pairing Notes</span>
                                <div class="detail-value">
                                    <?= nl2br(h($product->pairing_notes)) ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($product->awards): ?>
                            <div class="detail-item full-width">
                                <span class="detail-label">Awards & Recognition</span>
                                <div class="detail-value">
                                    <?= nl2br(h($product->awards)) ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Metadata -->
            <div class="detail-card">
                <h3 class="card-title">Metadata</h3>
                <div class="detail-items">
                    <div class="detail-item">
                        <span class="detail-label">Created</span>
                        <span class="detail-value"><?= $product->created->format('M j, Y \a\t g:i A') ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Last Modified</span>
                        <span class="detail-value"><?= $product->modified->format('M j, Y \a\t g:i A') ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Gallery</span>
                        <span class="detail-value"><?= $product->gallery ? 'Has gallery images' : 'No gallery images' ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Admin Product View Styles */
/* Base font size for this page */
html{font-size:18px}
.admin-product-view {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem 1rem;
}

.view-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.view-title {
    font-size: 2.125rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 0.5rem 0;
}

.view-subtitle {
    color: #6b7280;
    margin: 0;
}

.view-actions {
    display: flex;
    gap: 0.75rem;
}

.product-details {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

/* Overview Section */
.overview-section {
    display: flex;
    gap: 2rem;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    padding: 2rem;
}

.product-image-container {
    flex-shrink: 0;
}

.product-image {
    width: 300px;
    height: 300px;
    object-fit: cover;
    border-radius: 0.75rem;
    border: 1px solid #e5e7eb;
}

.image-placeholder {
    width: 300px;
    height: 300px;
    background: #f9fafb;
    border: 2px dashed #d1d5db;
    border-radius: 0.75rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
    font-size: 1rem;
    gap: 0.5rem;
}

.image-placeholder i {
    font-size: 2rem;
}

.product-overview {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.overview-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.overview-label {
    font-size: 1rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.overview-value {
    font-size: 1.25rem;
    color: #111827;
}

.overview-value.price {
    font-size: 1.5rem;
    font-weight: 700;
    color: #059669;
}

.overview-value code {
    background: #f3f4f6;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-family: 'Monaco', 'Menlo', monospace;
    font-size: 0.875rem;
}

.view-link {
    margin-left: 0.5rem;
    color: #2563eb;
    text-decoration: none;
    font-size: 0.875rem;
}

.view-link:hover {
    text-decoration: underline;
}

/* Details Grid */
.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 1.5rem;
}

.detail-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    padding: 1.5rem;
}

.detail-card.full-width {
    grid-column: 1 / -1;
}

.card-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #111827;
    margin: 0 0 1rem 0;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #f3f4f6;
}

.detail-items {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.detail-item.full-width {
    grid-column: 1 / -1;
}

.detail-label {
    font-size: 0.95rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.detail-value {
    font-size: 1.0625rem;
    color: #111827;
}

.detail-value.price {
    font-weight: 600;
    color: #059669;
}

.detail-value.description {
    line-height: 1.6;
    color: #374151;
}

/* Rating */
.rating {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.rating-value {
    font-weight: 600;
}

.rating-stars {
    display: flex;
    gap: 0.125rem;
}

.star {
    font-size: 0.875rem;
}

.star-full {
    color: #fbbf24;
}

.star-half {
    color: #fbbf24;
}

.star-empty {
    color: #d1d5db;
}

/* Badges */
.badge {
    display: inline-block;
    padding: 0.375rem 0.875rem;
    border-radius: 9999px;
    font-size: 0.875rem;
    font-weight: 600;
}

.badge-success {
    background: #dcfce7;
    color: #166534;
}

.badge-warning {
    background: #fef3c7;
    color: #92400e;
}

.badge-danger {
    background: #fecaca;
    color: #991b1b;
}

.badge-info {
    background: #dbeafe;
    color: #1d4ed8;
}

.badge-neutral {
    background: #f3f4f6;
    color: #374151;
}

/* Dietary Badges */
.dietary-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.dietary-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.75rem 1.1rem;
    border-radius: 0.5rem;
    font-size: 1.0625rem;
    font-weight: 600;
}

.dietary-badge.vegetarian {
    background: #dcfce7;
    color: #166534;
}

.dietary-badge.gluten-free {
    background: #fef3c7;
    color: #92400e;
}

.dietary-badge.lactose-free {
    background: #dbeafe;
    color: #1d4ed8;
}

.no-dietary {
    color: #9ca3af;
    font-style: italic;
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

/* Icons */
.icon-image::before { content: '🖼'; }
.icon-check::before { content: '✓'; }

/* Responsive */
@media (max-width: 1024px) {
    .view-header {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }
    
    .overview-section {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .product-overview {
        width: 100%;
        max-width: 500px;
    }
    
    .details-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .product-image,
    .image-placeholder {
        width: 250px;
        height: 250px;
    }
    
    .detail-items {
        grid-template-columns: 1fr;
    }
    
    .dietary-badges {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<?php
/**
 * Admin Products Index
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Product> $products
 */
$this->assign('title', 'Product Management');

$stockBadge = function ($stock): string {
    if ($stock <= 0) {
        return '<span class="badge badge-danger">Out of Stock</span>';
    } elseif ($stock <= 10) {
        return '<span class="badge badge-warning">Low Stock (' . $stock . ')</span>';
    }
    return '<span class="badge badge-success">In Stock (' . $stock . ')</span>';
};
?>

<div class="admin-products">
    <!-- Header -->
    <div class="page-header">
        <div class="page-header-content">
            <h1 class="page-title">Product Management</h1>
            <p class="page-subtitle">Manage your product catalog, inventory, and pricing</p>
        </div>
        <div class="page-actions">
            <?= $this->Html->link(
                '<i class="icon-plus"></i> Add Product',
                ['action' => 'add'],
                ['class' => 'btn btn-primary', 'escape' => false]
            ) ?>
            <?= $this->Html->link(
                '<i class="icon-download"></i> Export CSV',
                ['action' => 'export'],
                ['class' => 'btn btn-outline', 'escape' => false, 'target' => '_blank']
            ) ?>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon stat-icon-blue">
                <i class="icon-package"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($stats['total']) ?></div>
                <div class="stat-label">Total Products</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-green">
                <i class="icon-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($stats['in_stock']) ?></div>
                <div class="stat-label">In Stock</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-orange">
                <i class="icon-alert-triangle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($stats['low_stock']) ?></div>
                <div class="stat-label">Low Stock</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-red">
                <i class="icon-x-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($stats['out_of_stock']) ?></div>
                <div class="stat-label">Out of Stock</div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="filters-section">
        <?= $this->Form->create(null, ['type' => 'get', 'class' => 'filters-form']) ?>
        <div class="filters-row">
            <div class="filter-group">
                <?= $this->Form->control('q', [
                    'type' => 'search',
                    'placeholder' => 'Search products...',
                    'value' => $query,
                    'class' => 'form-control',
                    'label' => false
                ]) ?>
            </div>
            <div class="filter-group">
                <?= $this->Form->control('status', [
                    'type' => 'select',
                    'options' => [
                        '' => 'All Status',
                        'in_stock' => 'In Stock',
                        'low_stock' => 'Low Stock',
                        'out_of_stock' => 'Out of Stock'
                    ],
                    'value' => $status,
                    'class' => 'form-control',
                    'label' => false
                ]) ?>
            </div>
            <div class="filter-actions">
                <?= $this->Form->button('Search', ['class' => 'btn btn-outline']) ?>
                <?= $this->Html->link('Clear', ['action' => 'index'], ['class' => 'btn btn-subtle']) ?>
            </div>
        </div>
        <?= $this->Form->end() ?>
    </div>

    <!-- Products Table -->
    <div class="table-section">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="col-image">Image</th>
                        <th class="col-product">Product</th>
                        <th class="col-price">Price</th>
                        <th class="col-stock">Stock</th>
                        <th class="col-origin">Origin</th>
                        <th class="col-created">Created</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="7" class="empty-state">
                                <div class="empty-content">
                                    <i class="icon-package empty-icon"></i>
                                    <h3>No products found</h3>
                                    <p>Start by adding your first product to the catalog.</p>
                                    <?= $this->Html->link('Add Product', ['action' => 'add'], ['class' => 'btn btn-primary']) ?>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td class="col-image">
                                    <?php if ($product->image_url): ?>
                                        <img src="<?= h($this->Url->webroot($product->image_url)) ?>" 
                                             alt="<?= h($product->name) ?>" 
                                             class="product-thumbnail">
                                    <?php else: ?>
                                        <div class="product-placeholder">
                                            <i class="icon-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="col-product">
                                    <div class="product-info">
                                        <h4 class="product-name"><?= h($product->name) ?></h4>
                                        <p class="product-summary"><?= h($product->summary) ?></p>
                                        <div class="product-meta">
                                            <span class="meta-item"><?= h($product->milk_type) ?> • <?= h($product->style) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="col-price">
                                    <span class="price"><?= h($product->currency) ?> <?= number_format($product->price, 2) ?></span>
                                </td>
                                <td class="col-stock">
                                    <?= $stockBadge($product->stock) ?>
                                </td>
                                <td class="col-origin">
                                    <?= h($product->origin_country) ?>
                                </td>
                                <td class="col-created">
                                    <span class="date"><?= $product->created->format('M j, Y') ?></span>
                                    <span class="time"><?= $product->created->format('g:i A') ?></span>
                                </td>
                                <td class="col-actions">
                                    <div class="action-buttons">
                                        <?= $this->Html->link(
                                            '<i class="icon-eye"></i>',
                                            ['action' => 'view', $product->id],
                                            ['class' => 'btn-action btn-view', 'escape' => false, 'title' => 'View']
                                        ) ?>
                                        <?= $this->Html->link(
                                            '<i class="icon-edit"></i>',
                                            ['action' => 'edit', $product->id],
                                            ['class' => 'btn-action btn-edit', 'escape' => false, 'title' => 'Edit']
                                        ) ?>
                                        <?= $this->Form->postLink(
                                            '<i class="icon-trash"></i>',
                                            ['action' => 'delete', $product->id],
                                            [
                                                'class' => 'btn-action btn-delete',
                                                'escape' => false,
                                                'title' => 'Delete',
                                                'confirm' => 'Are you sure you want to delete "' . $product->name . '"?'
                                            ]
                                        ) ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($pagination['totalPages'] > 1): ?>
        <div class="pagination-section">
            <div class="pagination-info">
                Showing <?= number_format(($pagination['page'] - 1) * 20 + 1) ?> to 
                <?= number_format(min($pagination['page'] * 20, $pagination['totalCount'])) ?> 
                of <?= number_format($pagination['totalCount']) ?> products
            </div>
            <div class="pagination-controls">
                <?php if ($pagination['hasPrev']): ?>
                    <?= $this->Html->link(
                        '← Previous',
                        array_merge($this->request->getQueryParams(), ['page' => $pagination['page'] - 1]),
                        ['class' => 'btn btn-outline btn-sm']
                    ) ?>
                <?php endif; ?>
                
                <span class="page-info">Page <?= $pagination['page'] ?> of <?= $pagination['totalPages'] ?></span>
                
                <?php if ($pagination['hasNext']): ?>
                    <?= $this->Html->link(
                        'Next →',
                        array_merge($this->request->getQueryParams(), ['page' => $pagination['page'] + 1]),
                        ['class' => 'btn btn-outline btn-sm']
                    ) ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
/* Admin Products Styles */
.admin-products {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem 1rem;
}

/* Page Header */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 0.5rem 0;
}

.page-subtitle {
    color: #6b7280;
    margin: 0;
}

.page-actions {
    display: flex;
    gap: 0.75rem;
}

/* Statistics Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.stat-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.stat-icon-blue { background: #dbeafe; color: #1d4ed8; }
.stat-icon-green { background: #dcfce7; color: #16a34a; }
.stat-icon-orange { background: #fed7aa; color: #ea580c; }
.stat-icon-red { background: #fecaca; color: #dc2626; }

.stat-value {
    font-size: 1.875rem;
    font-weight: 700;
    color: #111827;
}

.stat-label {
    color: #6b7280;
    font-size: 0.875rem;
}

/* Filters */
.filters-section {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.filters-row {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.filter-group {
    flex: 1;
    min-width: 200px;
}

.filter-actions {
    display: flex;
    gap: 0.5rem;
}

/* Table */
.table-section {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    overflow: hidden;
    margin-bottom: 1.5rem;
}

.table-container {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    background: #f9fafb;
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: #374151;
    border-bottom: 1px solid #e5e7eb;
    white-space: nowrap;
}

.data-table td {
    padding: 1rem;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: top;
}

.data-table tbody tr:hover {
    background: #f9fafb;
}

/* Column Widths */
.col-image { width: 80px; }
.col-product { width: 300px; }
.col-price { width: 100px; }
.col-stock { width: 120px; }
.col-origin { width: 120px; }
.col-created { width: 120px; }
.col-actions { width: 120px; }

/* Product Info */
.product-thumbnail {
    width: 48px;
    height: 48px;
    border-radius: 0.5rem;
    object-fit: cover;
}

.product-placeholder {
    width: 48px;
    height: 48px;
    background: #f3f4f6;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
}

.product-name {
    font-size: 0.875rem;
    font-weight: 600;
    color: #111827;
    margin: 0 0 0.25rem 0;
}

.product-summary {
    font-size: 0.75rem;
    color: #6b7280;
    margin: 0 0 0.25rem 0;
    line-height: 1.4;
}

.product-meta {
    font-size: 0.75rem;
    color: #9ca3af;
}

.price {
    font-weight: 600;
    color: #059669;
}

.date {
    display: block;
    font-size: 0.875rem;
    color: #374151;
}

.time {
    display: block;
    font-size: 0.75rem;
    color: #9ca3af;
}

/* Badges */
.badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
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

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 0.25rem;
}

.btn-action {
    width: 2rem;
    height: 2rem;
    border-radius: 0.375rem;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.btn-view {
    background: #f3f4f6;
    color: #6b7280;
}

.btn-view:hover {
    background: #e5e7eb;
    color: #374151;
}

.btn-edit {
    background: #dbeafe;
    color: #1d4ed8;
}

.btn-edit:hover {
    background: #bfdbfe;
    color: #1e40af;
}

.btn-delete {
    background: #fecaca;
    color: #dc2626;
}

.btn-delete:hover {
    background: #fca5a5;
    color: #b91c1c;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
}

.empty-content {
    max-width: 400px;
    margin: 0 auto;
}

.empty-icon {
    font-size: 3rem;
    color: #d1d5db;
    margin-bottom: 1rem;
}

.empty-content h3 {
    font-size: 1.25rem;
    color: #374151;
    margin-bottom: 0.5rem;
}

.empty-content p {
    color: #6b7280;
    margin-bottom: 1.5rem;
}

/* Pagination */
.pagination-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 0;
}

.pagination-info {
    color: #6b7280;
    font-size: 0.875rem;
}

.pagination-controls {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.page-info {
    color: #374151;
    font-size: 0.875rem;
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1rem;
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

.btn-subtle {
    background: transparent;
    color: #6b7280;
}

.btn-subtle:hover {
    color: #374151;
    background: #f3f4f6;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.8125rem;
}

/* Form Controls */
.form-control {
    width: 100%;
    padding: 0.625rem 0.875rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    transition: border-color 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

/* Icons */
.icon-plus::before { content: '+'; }
.icon-download::before { content: '↓'; }
.icon-package::before { content: '📦'; }
.icon-check-circle::before { content: '✓'; }
.icon-alert-triangle::before { content: '⚠'; }
.icon-x-circle::before { content: '✕'; }
.icon-eye::before { content: '👁'; }
.icon-edit::before { content: '✏'; }
.icon-trash::before { content: '🗑'; }
.icon-image::before { content: '🖼'; }

/* Responsive */
@media (max-width: 1024px) {
    .page-header {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .filters-row {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-group {
        min-width: auto;
    }
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .col-product { width: 200px; }
    .col-origin, .col-created { display: none; }
}
</style>

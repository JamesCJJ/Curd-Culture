<?php
$this->extend('/layout/customer');
$this->assign('title', 'Orders');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-box-seam me-2"></i>Orders</h2>
    <div class="d-flex align-items-center">
        <span class="me-2">
            <i class="bi bi-list"></i>
            <span class="dropdown-toggle" data-bs-toggle="dropdown">List</span>
        </span>
        <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#filterModal">
            <i class="bi bi-funnel"></i> Filter
        </button>
    </div>
</div>

<?php if ($orders->isEmpty()): ?>
    <div class="text-center py-5">
        <i class="bi bi-box-seam display-1 text-muted"></i>
        <h4 class="mt-3 text-muted">No orders found</h4>
        <p class="text-muted">You haven't placed any orders yet.</p>
        <?= $this->Html->link(
            'Start Shopping',
            ['controller' => 'Products', 'action' => 'index'],
            ['class' => 'btn btn-primary']
        ) ?>
    </div>
<?php else: ?>
    <?php foreach ($orders as $order): ?>
        <!-- Removed onclick so the whole card does not redirect anymore -->
        <div class="order-card">
            <div class="row align-items-center">
                <div class="col-md-2">
                    <div class="d-flex">
                        <?php
                        $itemCount = count($order->order_items);
                        for ($i = 0; $i < min(3, $itemCount); $i++): ?>
                            <div class="me-1 mb-1">
                                <i class="bi bi-box text-muted" style="font-size: 2rem;"></i>
                            </div>
                        <?php endfor; ?>
                    </div>
                    <small class="text-muted"><?= $itemCount ?> item<?= $itemCount !== 1 ? 's' : '' ?></small>
                </div>

                <div class="col-md-2">
                    <div class="order-status status-<?= h($order->status) ?>">
                        <?= ucfirst(h($order->status)) ?>
                    </div>
                    <small class="text-muted d-block mt-1">
                        <?= $order->created->format('M j') ?>
                    </small>
                </div>

                <div class="col-md-4">
                    <strong><?= h($order->full_name) ?></strong>
                    <div class="text-muted small">
                        <?= h($order->email) ?>
                    </div>
                </div>

                <div class="col-md-2 text-end">
                    <strong>$<?= number_format((float)$order->total, 2) ?> AUD</strong>
                </div>

                <div class="col-md-2 text-end">
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle"
                                type="button"
                                data-bs-toggle="dropdown"
                                onclick="event.stopPropagation();">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <ul class="dropdown-menu" onclick="event.stopPropagation();">
                            <li>
                                <?= $this->Html->link(
                                    '<i class="bi bi-eye me-2"></i>View Details',
                                    ['action' => 'orderDetails', '?' => ['id' => $order->id]],
                                    [
                                        'class'   => 'dropdown-item',
                                        'escape'  => false,
                                        'onclick' => 'event.stopPropagation();'
                                    ]
                                ) ?>
                            </li>
                            <li>
                                <?= $this->Form->postLink(
                                    '<i class="bi bi-arrow-repeat me-2"></i>Buy Again',
                                    ['action' => 'buyAgain', (int)$order->id],
                                    [
                                        'class'   => 'dropdown-item',
                                        'escape'  => false,
                                        'confirm' => 'Add all items from this order to your cart?',
                                        'data'    => ['id' => (int)$order->id],
                                        'onclick' => 'event.stopPropagation();'
                                    ]
                                ) ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if ($pagination['pages'] > 1): ?>
        <nav aria-label="Orders pagination" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= !$pagination['hasPrev'] ? 'disabled' : '' ?>">
                    <?php if ($pagination['hasPrev']): ?>
                        <a class="page-link"
                           href="<?= $this->Url->build(['?' => array_merge($this->request->getQueryParams(), ['page' => $pagination['page'] - 1])]) ?>">
                            <span aria-hidden="true">&laquo;</span> Previous
                        </a>
                    <?php else: ?>
                        <span class="page-link"><span aria-hidden="true">&laquo;</span> Previous</span>
                    <?php endif; ?>
                </li>

                <?php
                $start = max(1, $pagination['page'] - 2);
                $end   = min($pagination['pages'], $pagination['page'] + 2);

                if ($start > 1): ?>
                    <li class="page-item">
                        <a class="page-link"
                           href="<?= $this->Url->build(['?' => array_merge($this->request->getQueryParams(), ['page' => 1])]) ?>">1</a>
                    </li>
                    <?php if ($start > 2): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <li class="page-item <?= $i == $pagination['page'] ? 'active' : '' ?>">
                        <?php if ($i == $pagination['page']): ?>
                            <span class="page-link"><?= $i ?></span>
                        <?php else: ?>
                            <a class="page-link"
                               href="<?= $this->Url->build(['?' => array_merge($this->request->getQueryParams(), ['page' => $i])]) ?>"><?= $i ?></a>
                        <?php endif; ?>
                    </li>
                <?php endfor; ?>

                <?php if ($end < $pagination['pages']): ?>
                    <?php if ($end < $pagination['pages'] - 1): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                    <li class="page-item">
                        <a class="page-link"
                           href="<?= $this->Url->build(['?' => array_merge($this->request->getQueryParams(), ['page' => $pagination['pages']])]) ?>">
                            <?= $pagination['pages'] ?></a>
                    </li>
                <?php endif; ?>

                <li class="page-item <?= !$pagination['hasNext'] ? 'disabled' : '' ?>">
                    <?php if ($pagination['hasNext']): ?>
                        <a class="page-link"
                           href="<?= $this->Url->build(['?' => array_merge($this->request->getQueryParams(), ['page' => $pagination['page'] + 1])]) ?>">
                            Next <span aria-hidden="true">&raquo;</span>
                        </a>
                    <?php else: ?>
                        <span class="page-link">Next <span aria-hidden="true">&raquo;</span></span>
                    <?php endif; ?>
                </li>
            </ul>

            <div class="text-center mt-2">
                <small class="text-muted">
                    Page <?= (int)$pagination['page'] ?> of <?= (int)$pagination['pages'] ?>
                    (<?= (int)$pagination['count'] ?> total orders)
                </small>
            </div>
        </nav>
    <?php endif; ?>
<?php endif; ?>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filter Orders</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <?= $this->Form->create(null, ['type' => 'get', 'class' => 'modal-body']) ?>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <?= $this->Form->select('status', [''] + $statusOptions, [
                    'value' => $status,
                    'class' => 'form-select',
                    'empty' => 'All Statuses'
                ]) ?>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="date_from" class="form-label">From Date</label>
                        <?= $this->Form->date('date_from', [
                            'value' => $dateFrom,
                            'class' => 'form-control'
                        ]) ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="date_to" class="form-label">To Date</label>
                        <?= $this->Form->date('date_to', [
                            'value' => $dateTo,
                            'class' => 'form-control'
                        ]) ?>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <?= $this->Html->link('Clear Filters', ['action' => 'orders'], ['class' => 'btn btn-outline-secondary']) ?>
                <button type="submit" class="btn btn-primary">Apply Filters</button>
            </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>

<script>
    // Prevent dropdown buttons/menus from triggering card navigation
    document.querySelectorAll('.dropdown-toggle').forEach(function (el) {
        el.addEventListener('click', function (e) { e.stopPropagation(); });
    });
    document.querySelectorAll('.dropdown-menu').forEach(function (el) {
        el.addEventListener('click', function (e) { e.stopPropagation(); });
    });
</script>

<?php
/**
 * Admin Deliveries Dashboard
 * Variables:
 *  @var \Cake\I18n\FrozenDate $date
 *  @var array<int,array> $slots
 *  @var array<int,array{slot:array,orders:array,used:int,remaining:?int}> $groups
 */
$this->assign('title', 'Delivery Management');

$totalOrders = 0;
$scheduled   = 0;
$unassigned  = 0;
$totalCap    = 0;
$totalUsed   = 0;

foreach ($groups as $sid => $g) {
    $cnt = count($g['orders']);
    $totalOrders += $cnt;
    if ($sid && $sid !== 0) {
        $scheduled += $cnt;
    } else {
        $unassigned += $cnt;
    }
    $cap = $g['slot']['capacity'] ?? null;
    if ($cap !== null) {
        $totalCap += (int)$cap;
        $totalUsed += (int)$g['used'];
    }
}

$slotLabel = function(array $s): string {
    $name = (string)($s['name'] ?? 'Unassigned');
    $ws = isset($s['window_start']) ? substr((string)$s['window_start'], 0, 5) : '';
    $we = isset($s['window_end'])   ? substr((string)$s['window_end'], 0, 5)   : '';
    if ($ws && $we) {
        $name .= " ({$ws}–{$we})";
    }
    return $name;
};

$statuses = [
    'pending'    => 'Pending',
    'confirmed'  => 'Confirmed',
    'processing' => 'Processing',
    'shipped'    => 'Out for Delivery',
    'delivered'  => 'Delivered',
    'cancelled'  => 'Cancelled',
];

$todayStr = (new \Cake\I18n\FrozenDate())->format('Y-m-d');
$prevStr  = $date->subDays(1)->format('Y-m-d');
$nextStr  = $date->addDays(1)->format('Y-m-d');

$slotOptions = [];
foreach ($slots as $s) {
    $slotOptions[(int)$s['id']] = $slotLabel($s);
}
?>
<div class="admin-deliveries">

    <div class="page-header">
        <div class="page-header-content">
            <h1 class="page-title">Delivery Management</h1>
            <p class="page-subtitle">Plan and track all deliveries by date and time slot</p>
        </div>
        <div class="page-actions">
            <?= $this->Html->link(
                '← Prev',
                ['prefix' => 'Admin', 'controller' => 'Deliveries', 'action' => 'index', '?' => ['date' => $prevStr]],
                ['class' => 'btn btn-outline btn-sm']
            ) ?>
            <?= $this->Html->link(
                'Today',
                ['prefix' => 'Admin', 'controller' => 'Deliveries', 'action' => 'index', '?' => ['date' => $todayStr]],
                ['class' => 'btn btn-outline btn-sm']
            ) ?>
            <?= $this->Html->link(
                'Next →',
                ['prefix' => 'Admin', 'controller' => 'Deliveries', 'action' => 'index', '?' => ['date' => $nextStr]],
                ['class' => 'btn btn-outline btn-sm']
            ) ?>

            <?= $this->Html->link(
                'Export CSV',
                ['prefix' => 'Admin', 'controller' => 'Deliveries', 'action' => 'index', '?' => ['date' => $date->format('Y-m-d'), 'export' => 'csv']],
                ['class' => 'btn btn-outline', 'target' => '_blank']
            ) ?>
        </div>
    </div>

    <div class="filters-section">
        <?= $this->Form->create(null, ['type' => 'get', 'url' => ['prefix' => 'Admin', 'controller' => 'Deliveries', 'action' => 'index'], 'class' => 'filters-form']) ?>
        <div class="filters-row">
            <div class="filter-group">
                <label class="label">Date</label>
                <?= $this->Form->control('date', [
                    'type'  => 'date',
                    'value' => $date->format('Y-m-d'),
                    'label' => false,
                    'class' => 'form-control',
                ]) ?>
            </div>
            <div class="filter-actions">
                <?= $this->Form->button('Apply', ['class' => 'btn btn-outline']) ?>
                <?= $this->Html->link('Reset', ['prefix' => 'Admin', 'controller' => 'Deliveries', 'action' => 'index'], ['class' => 'btn btn-subtle']) ?>
            </div>
        </div>
        <?= $this->Form->end() ?>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon stat-icon-blue">📦</div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($totalOrders) ?></div>
                <div class="stat-label">Total Orders (<?= h($date->format('M j, Y')) ?>)</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-green">✅</div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($scheduled) ?></div>
                <div class="stat-label">Scheduled</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-orange">⚠</div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($unassigned) ?></div>
                <div class="stat-label">Unassigned</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-red">⏱</div>
            <div class="stat-content">
                <div class="stat-value">
                    <?php
                    if ($totalCap > 0) {
                        $pct = ($totalUsed / $totalCap) * 100;
                        echo number_format($pct, 0) . '%';
                    } else {
                        echo '—';
                    }
                    ?>
                </div>
                <div class="stat-label">Capacity Utilization</div>
            </div>
        </div>
    </div>

    <div class="filters-section">
        <?= $this->Form->create(null, [
            'url'   => ['prefix' => 'Admin', 'controller' => 'Deliveries', 'action' => 'bulkUpdate'],
            'class' => 'filters-form',
            'id'    => 'bulk-form'
        ]) ?>
        <div class="filters-row">
            <div class="filter-group">
                <label class="label">With selected:</label>
                <?= $this->Form->control('status', [
                    'type'    => 'select',
                    'options' => $statuses,
                    'empty'   => 'Choose status…',
                    'label'   => false,
                    'class'   => 'form-control'
                ]) ?>
            </div>
            <div class="filter-actions">
                <?= $this->Form->button('Update Status', ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
        <?= $this->Form->end() ?>
    </div>

    <?php
    $orderedGroups = $groups;
    if (isset($orderedGroups[0])) {
        $u = $orderedGroups[0];
        unset($orderedGroups[0]);
        $orderedGroups[999999] = $u;
    }
    ?>

    <?php if (empty($groups)): ?>
        <div class="table-section">
            <div class="empty-state">
                <div class="empty-content">
                    <div class="empty-icon">🚚</div>
                    <h3>No deliveries for <?= h($date->format('M j, Y')) ?></h3>
                    <p>Orders will show here once customers schedule delivery.</p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($orderedGroups as $sid => $g): ?>
            <?php
            $s = $g['slot'] ?? ['id' => 0, 'name' => 'Unassigned', 'window_start' => null, 'window_end' => null, 'capacity' => null];
            $cap = $s['capacity'] ?? null;
            $used = (int)($g['used'] ?? 0);
            $remaining = $g['remaining'] ?? null;
            $badge = 'badge-success';
            if ($remaining !== null && $remaining <= 0) $badge = 'badge-danger';
            elseif ($remaining !== null && $remaining <= 2) $badge = 'badge-warning';
            ?>
            <div class="table-section">
                <div class="table-header">
                    <div class="slot-meta">
                        <h3 class="slot-title"><?= h($slotLabel($s)) ?></h3>
                        <div class="slot-sub">
                            <?php if ($cap !== null): ?>
                                <span class="badge <?= $badge ?>">Used <?= number_format($used) ?> / <?= number_format((int)$cap) ?><?php if ($remaining !== null): ?> (<?= number_format($remaining) ?> left)<?php endif; ?></span>
                            <?php else: ?>
                                <span class="badge">No capacity limit</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="slot-actions">
                        <?= $this->Html->link(
                            'Export CSV',
                            ['prefix' => 'Admin', 'controller' => 'Deliveries', 'action' => 'index', '?' => ['date' => $date->format('Y-m-d'), 'export' => 'csv']],
                            ['class' => 'btn btn-outline btn-sm', 'target' => '_blank']
                        ) ?>
                    </div>
                </div>

                <div class="table-container">
                    <table class="data-table">
                        <thead>
                        <tr>
                            <th style="width:28px"><input type="checkbox" onclick="document.querySelectorAll('.chk-order').forEach(c=>c.checked=this.checked); syncBulk();" /></th>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Address</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th style="width:280px">Reschedule / Move</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($g['orders'])): ?>
                            <tr><td colspan="7" class="empty-state"><div class="empty-content"><p>No orders.</p></div></td></tr>
                        <?php else: ?>
                            <?php foreach ($g['orders'] as $o): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="chk-order" value="<?= (int)$o->id ?>" onchange="syncBulk()" />
                                    </td>
                                    <td>
                                        <strong>#<?= (int)$o->id ?></strong><br>
                                        <span class="date"><?= $o->created ? $o->created->format('M j, Y g:i A') : '' ?></span>
                                    </td>
                                    <td>
                                        <?= h($o->full_name ?? '') ?><br>
                                        <span class="muted"><?= h($o->email ?? '') ?></span>
                                    </td>
                                    <td>
                                        <?= h($o->address ?? '') ?><br>
                                        <span class="muted"><?= h(($o->city ?? '') . ' ' . ($o->postcode ?? '')) ?></span>
                                    </td>
                                    <td>
                                        <span class="price">AUD <?= number_format((float)($o->total ?? 0), 2) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge"><?= h(ucfirst((string)$o->status)) ?></span>
                                    </td>
                                    <td>
                                        <?= $this->Form->create(null, [
                                            'url'   => ['prefix' => 'Admin', 'controller' => 'Deliveries', 'action' => 'move'],
                                            'class' => 'inline-form'
                                        ]) ?>
                                        <?= $this->Form->hidden('order_id', ['value' => (int)$o->id]) ?>
                                        <?= $this->Form->control('delivery_date', [
                                            'type'  => 'date',
                                            'value' => $date->format('Y-m-d'),
                                            'label' => false,
                                            'class' => 'form-control form-control-sm',
                                        ]) ?>
                                        <?= $this->Form->control('delivery_slot_id', [
                                            'type'    => 'select',
                                            'options' => ['' => 'Unassigned'] + $slotOptions,
                                            'value'   => (string)($o->delivery_slot_id ?? ''),
                                            'label'   => false,
                                            'class'   => 'form-control form-control-sm',
                                        ]) ?>
                                        <?= $this->Form->button('Move', ['class' => 'btn btn-outline btn-sm']) ?>
                                        <?= $this->Form->end() ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
    function syncBulk() {
        const bulk = document.getElementById('bulk-form');
        [...bulk.querySelectorAll('input[name="order_ids[]"]')].forEach(n => n.remove());
        document.querySelectorAll('.chk-order:checked').forEach(chk => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'order_ids[]';
            input.value = chk.value;
            bulk.appendChild(input);
        });
    }
</script>

<style>
    /* Root */
    .admin-deliveries { max-width: 1400px; margin: 0 auto; padding: 2rem 1rem; }

    /* Header */
    .page-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:2rem; padding-bottom:1.5rem; border-bottom:1px solid #e5e7eb;}
    .page-title { font-size:2rem; font-weight:700; color:#111827; margin:0 0 .5rem;}
    .page-subtitle { color:#6b7280; margin:0;}
    .page-actions { display:flex; gap:.5rem; }

    /* Filters */
    .filters-section { background:#fff; border:1px solid #e5e7eb; border-radius:.75rem; padding:1.25rem; margin-bottom:1.25rem; }
    .filters-row { display:flex; align-items:flex-end; gap:1rem; flex-wrap:wrap; }
    .filter-group { min-width:220px; }
    .label { display:block; font-size:.85rem; color:#6b7280; margin-bottom:.25rem; }

    /* Stats */
    .stats-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:1.25rem; margin-bottom:1.5rem; }
    .stat-card { background:#fff; border:1px solid #e5e7eb; border-radius:.75rem; padding:1.25rem; display:flex; align-items:center; gap:1rem; box-shadow:0 1px 3px rgba(0,0,0,.06); }
    .stat-icon { width:3rem; height:3rem; border-radius:.75rem; display:flex; align-items:center; justify-content:center; font-size:1.25rem; }
    .stat-icon-blue { background:#dbeafe; color:#1d4ed8; }
    .stat-icon-green { background:#dcfce7; color:#16a34a; }
    .stat-icon-orange { background:#fed7aa; color:#ea580c; }
    .stat-icon-red { background:#fecaca; color:#dc2626; }
    .stat-value { font-size:1.6rem; font-weight:700; color:#111827; }
    .stat-label { color:#6b7280; font-size:.9rem; }

    /* Tables */
    .table-section { background:#fff; border:1px solid #e5e7eb; border-radius:.75rem; overflow:hidden; margin-bottom:1rem; }
    .table-header { display:flex; justify-content:space-between; align-items:center; padding:1rem 1rem .75rem 1rem; border-bottom:1px solid #e5e7eb; }
    .slot-meta { display:flex; align-items:baseline; gap:.75rem; }
    .slot-title { margin:0; font-size:1.1rem; }
    .slot-sub { color:#6b7280; }

    .table-container { overflow-x:auto; }
    .data-table { width:100%; border-collapse:collapse; }
    .data-table th { background:#f9fafb; padding:0.8rem 1rem; text-align:left; font-weight:600; color:#374151; border-bottom:1px solid #e5e7eb; white-space:nowrap;}
    .data-table td { padding:0.8rem 1rem; border-bottom:1px solid #f3f4f6; vertical-align:top; }
    .data-table tbody tr:hover { background:#f9fafb; }
    .inline-form { display:flex; gap:.4rem; align-items:center; }
    .form-control { width:100%; padding:.5rem .6rem; border:1px solid #d1d5db; border-radius:.5rem; font-size:.85rem; }
    .form-control-sm { padding:.35rem .5rem; font-size:.82rem; }
    .price { font-weight:600; color:#059669; }
    .muted { color:#6b7280; }

    /* Badges & Buttons */
    .badge { display:inline-block; padding:.2rem .6rem; border-radius:9999px; font-size:.75rem; font-weight:600; background:#eef2f7; color:#374151;}
    .badge-success { background:#dcfce7; color:#166534; }
    .badge-warning { background:#fef3c7; color:#92400e; }
    .badge-danger  { background:#fecaca; color:#991b1b; }

    .btn { display:inline-flex; align-items:center; gap:.5rem; padding:.55rem .9rem; border-radius:.5rem; font-size:.875rem; font-weight:500; text-decoration:none; transition:all .2s; border:1px solid transparent; cursor:pointer;}
    .btn-primary { background:#2563eb; color:#fff; }
    .btn-primary:hover { background:#1d4ed8; }
    .btn-outline { background:#fff; color:#374151; border-color:#d1d5db; }
    .btn-outline:hover { background:#f9fafb; border-color:#9ca3af; }
    .btn-subtle { background:transparent; color:#6b7280; }
    .btn-subtle:hover { color:#374151; background:#f3f4f6; }
    .btn-sm { padding:.35rem .6rem; font-size:.82rem; }

    /* Empty */
    .empty-state { text-align:center; padding:2.5rem 1rem; }
    .empty-content { max-width:460px; margin:0 auto; }
    .empty-icon { font-size:2.25rem; color:#d1d5db; margin-bottom:.5rem; }

    /* Responsive */
    @media (max-width: 1024px) {
        .page-header { flex-direction:column; align-items:stretch; gap:1rem; }
        .stats-grid { grid-template-columns:repeat(2,1fr); }
    }
    @media (max-width: 768px) {
        .stats-grid { grid-template-columns:1fr; }
    }
</style>

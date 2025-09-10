<?php
/**
 * Admin Orders Analytics
 *
 * @var \App\View\AppView $this
 * @var int $recentOrders
 * @var array<array{month:string,revenue:float|int}> $monthlyRevenue
 * @var \Cake\Datasource\ResultSetInterface|array $topProducts
 */

$this->assign('title', 'Reports');

?>
<div class="reports">
    <h1 style="margin:0 0 1rem">Reports</h1>

    <section class="cards">
        <div class="card">
            <div class="card__label">Orders (last 30 days)</div>
            <div class="card__value"><?= (int)$recentOrders ?></div>
        </div>
    </section>

    <section class="grid">
        <div class="panel">
            <h3 class="panel__title">Revenue (last 12 months)</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th style="text-align:right">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($monthlyRevenue as $row): ?>
                    <tr>
                        <td><?= h($row['month']) ?></td>
                        <td style="text-align:right"><?= number_format((float)$row['revenue'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="panel">
            <h3 class="panel__title">Top Products</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th style="text-align:right">Qty</th>
                        <th style="text-align:right">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($topProducts as $p): ?>
                    <tr>
                        <td><?= h($p->product->name ?? $p->get('Products')['name'] ?? 'Unknown') ?></td>
                        <td style="text-align:right"><?= (int)($p->total_qty ?? $p->get('total_qty') ?? 0) ?></td>
                        <td style="text-align:right"><?= number_format((float)($p->total_revenue ?? $p->get('total_revenue') ?? 0), 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<style>
.reports{max-width:1000px;margin:0 auto;padding:1.25rem 1rem}
.cards{display:grid;grid-template-columns:repeat(3,1fr);gap:.8rem;margin-bottom:1rem}
.card{background:#fff;border:1px solid #eef0f3;border-radius:.9rem;padding:.9rem .9rem;box-shadow:0 6px 24px rgba(0,0,0,.06)}
.card__label{font-size:.9rem;color:#6b7280}
.card__value{font-size:1.6rem;font-weight:700;margin:.15rem 0 .1rem}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.panel{background:#fff;border:1px solid #eef0f3;border-radius:1rem;box-shadow:0 10px 30px rgba(0,0,0,.06);padding:1rem}
.panel__title{margin:0 0 .5rem}
.table{width:100%;border-collapse:separate;border-spacing:0}
.table thead th{font-weight:600;color:#6b7280;text-align:left;border-bottom:1px solid #eef0f3;padding:.55rem}
.table tbody td{padding:.6rem .55rem;border-bottom:1px solid #f2f4f6;vertical-align:top}
.table tbody tr:last-child td{border-bottom:0}
@media (max-width: 900px){.grid{grid-template-columns:1fr}.cards{grid-template-columns:1fr}}
</style>



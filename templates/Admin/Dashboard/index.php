<?php
/**
 * Admin Dashboard
 *
 * - int   $total
 * - int   $unreadCount
 * - int   $repliedCount
 * - int   $todayCount
 * - \Cake\Collection\CollectionInterface|\App\Model\Entity\ContactMessage[] $latest
 */
$this->assign('title', 'Dashboard');


$total        = $total        ?? 0;
$unreadCount  = $unreadCount  ?? 0;
$repliedCount = $repliedCount ?? 0;
$todayCount   = $todayCount   ?? 0;
?>

<div class="dash">


    <section class="stats">
        <div class="stat">
            <div class="stat__label">Total</div>
            <div class="stat__value"><?= (int)$total ?></div>
            <div class="stat__hint">All contact messages</div>
        </div>

        <div class="stat">
            <div class="stat__label">Unread</div>
            <div class="stat__value"><?= (int)$unreadCount ?></div>
            <div class="stat__hint">Need attention</div>
        </div>

        <div class="stat">
            <div class="stat__label">Replied</div>
            <div class="stat__value"><?= (int)$repliedCount ?></div>
            <div class="stat__hint">Marked as handled</div>
        </div>

        <div class="stat">
            <div class="stat__label">Today</div>
            <div class="stat__value"><?= (int)$todayCount ?></div>
            <div class="stat__hint">New since 00:00</div>
        </div>
    </section>

    <section class="grid">

        <div class="card">
            <div class="card__head">
                <h3 class="card__title">Latest 10</h3>
                <?= $this->Html->link(
                    'View all',
                    ['prefix' => 'Admin', 'controller' => 'ContactMessages', 'action' => 'index'],
                    ['class' => 'btn small btn-subtle']
                ) ?>
            </div>

            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th>From</th>
                        <th>Message</th>
                        <th>Received</th>
                        <th style="width:140px">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (count($latest) === 0): ?>
                        <tr><td colspan="4" class="muted">No messages yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($latest as $m): ?>
                            <tr>
                                <td>
                                    <div class="from">
                                        <strong><?= h($m->name ?: 'Unknown') ?></strong>
                                        <span class="muted"><?= h($m->email) ?></span>
                                    </div>
                                </td>
                                <td class="ellipsis">
                                    <?= h(mb_strimwidth((string)$m->message, 0, 80, '…')) ?>
                                </td>
                                <td class="muted">
                                    <?= $m->created ? $m->created->format('M j, Y · g:i A') : '' ?>
                                </td>
                                <td class="actions">
                                    <?= $this->Html->link('View',   ['prefix'=>'Admin','controller'=>'ContactMessages','action'=>'view',  $m->id], ['class'=>'btn tiny']) ?>
                                    <?= $this->Html->link('Reply',  ['prefix'=>'Admin','controller'=>'ContactMessages','action'=>'view', $m->id], ['class'=>'btn tiny btn-primary']) ?>
                                    <?= $this->Form->postLink(
                                        'Delete',
                                        ['prefix'=>'Admin','controller'=>'ContactMessages','action'=>'delete', $m->id],
                                        ['class'=>'btn tiny danger', 'confirm'=>'Delete this message?']
                                    ) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>


        <aside class="side">
            <div class="card">
                <h3 class="card__title">Quick filters</h3>
                <div class="pill-list">
                    <?= $this->Html->link(
                        "Unread ({$unreadCount})",
                        ['prefix'=>'Admin','controller'=>'ContactMessages','action'=>'index','?'=>['status'=>'unread']],
                        ['class'=>'pill']
                    ) ?>
                    <?= $this->Html->link(
                        "Replied ({$repliedCount})",
                        ['prefix'=>'Admin','controller'=>'ContactMessages','action'=>'index','?'=>['status'=>'replied']],
                        ['class'=>'pill']
                    ) ?>
                    <?= $this->Html->link(
                        "Today ({$todayCount})",
                        ['prefix'=>'Admin','controller'=>'ContactMessages','action'=>'index','?'=>['date'=>'today']],
                        ['class'=>'pill']
                    ) ?>
                </div>
            </div>

            <div class="card">
                <h3 class="card__title">Bulk tools</h3>
                <ul class="tools">
                    <li><?= $this->Html->link('Export CSV',   ['prefix'=>'Admin','controller'=>'ContactMessages','action'=>'export'],   ['class'=>'tool']) ?></li>
                    <li><?= $this->Html->link('Mark all read',['prefix'=>'Admin','controller'=>'ContactMessages','action'=>'markAllRead'], ['class'=>'tool']) ?></li>
                    <li><?= $this->Html->link('Settings',     ['prefix'=>'Admin','controller'=>'Settings','action'=>'index'],            ['class'=>'tool']) ?></li>
                </ul>
            </div>
        </aside>
    </section>
</div>

<style>

    .dash{max-width:1100px;margin:0 auto;padding:1.25rem 1rem}


    .stats{display:grid;grid-template-columns:repeat(4,1fr);gap:.8rem;margin-bottom:1rem}
    .stat{background:#fff;border:1px solid #eef0f3;border-radius:.9rem;padding:.9rem .9rem;box-shadow:0 6px 24px rgba(0,0,0,.06)}
    .stat__label{font-size:.9rem;color:#6b7280}
    .stat__value{font-size:1.6rem;font-weight:700;margin:.15rem 0 .1rem}
    .stat__hint{color:#9aa3af;font-size:.85rem}


    .grid{display:grid;grid-template-columns:2fr 1fr;gap:1rem}
    .card{background:#fff;border:1px solid #eef0f3;border-radius:1rem;box-shadow:0 10px 30px rgba(0,0,0,.06);padding:1rem}
    .card__head{display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem}
    .card__title{margin:0;font-size:1.05rem}


    .table-wrap{overflow:auto}
    .table{width:100%;border-collapse:separate;border-spacing:0}
    .table thead th{font-weight:600;color:#6b7280;text-align:left;border-bottom:1px solid #eef0f3;padding:.55rem}
    .table tbody td{padding:.6rem .55rem;border-bottom:1px solid #f2f4f6;vertical-align:top}
    .table tbody tr:last-child td{border-bottom:0}
    .from{display:flex;flex-direction:column}
    .ellipsis{max-width:420px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .muted{color:#6b7280}


    .side{display:flex;flex-direction:column;gap:1rem}
    .pill-list{display:flex;flex-wrap:wrap;gap:.5rem}
    .pill{display:inline-block;padding:.4rem .65rem;border-radius:999px;background:#eef5ff;border:1px solid #d7e7ff;color:#1c4ea0;text-decoration:none;font-weight:600}
    .tools{list-style:none;margin:.25rem 0 0;padding:0;display:flex;flex-direction:column;gap:.35rem}
    .tool{text-decoration:none;color:#111;background:#f7f8fb;border:1px solid #eceff4;border-radius:.6rem;padding:.5rem .6rem;display:block}
    .tool:hover{filter:brightness(.98)}


    .btn{display:inline-block;padding:.45rem .75rem;border-radius:.55rem;border:1px solid #e4e7ec;background:#f3f5f7;color:#111;text-decoration:none}
    .btn-subtle{background:transparent;border-color:#d1d5db;color:#374151}
    .btn.small{font-size:.9rem;padding:.35rem .55rem}
    .btn.tiny{font-size:.85rem;padding:.28rem .5rem}
    .btn-primary{background:#2c7be5;color:#fff;border-color:transparent}
    .danger{background:#fee2e2;border-color:#fecaca;color:#991b1b}


    .page.hc .card,
    .page.hc .stat{background:#0f172a;border-color:#334155;box-shadow:none}
    .page.hc .table thead th{color:#cbd5e1;border-color:#334155}
    .page.hc .table tbody td{border-color:#233044}
    .page.hc .pill{background:#111827;border-color:#334155;color:#e5e7eb}
    .page.hc .tool{background:#111827;border-color:#334155;color:#e5e7eb}
    .page.hc .btn{background:#1f2937;color:#fff;border-color:#475569}
    .page.hc .btn-primary{background:#60a5fa;color:#111}
    .page.hc .muted{color:#cbd5e1}


    @media (max-width: 960px){
        .grid{grid-template-columns:1fr}
        .ellipsis{max-width:unset}
    }
    @media (max-width: 720px){
        .stats{grid-template-columns:repeat(2,1fr)}
    }
</style>

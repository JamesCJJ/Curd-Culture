<?php
/**
 * Enhanced Admin Dashboard
 * 
 * @var \App\View\AppView $this
 * @var array $contactStats
 * @var array $productStats
 * @var array $orderStats
 * @var array $userStats
 * @var array $recentActivity
 * @var iterable $latestMessages
 * @var iterable $latestOrders
 */
$this->assign('title', 'Dashboard');

$total      = $total      ?? 0;
$unreadCount= $unreadCount?? 0;
$readCount  = $readCount  ?? 0;
$todayCount = $todayCount ?? 0;

// Status badge function
$badge = function (?string $state): string {
    $state = strtolower((string)$state);
    $map = [
        'unread'      => ['Unread',       '#dbeafe', '#1d4ed8'], // Blue
        'read'        => ['Read',         '#dcfce7', '#166534'], // Green
        'in_progress' => ['In Progress',  '#fef3c7', '#92400e'], // Orange
        'closed'      => ['Closed',       '#fee2e2', '#dc2626'], // Red
        'new'         => ['Unread',       '#dbeafe', '#1d4ed8'], // Blue (for backward compatibility)
        ''            => ['Unread',       '#dbeafe', '#1d4ed8'], // Default to Unread for empty status
    ];
    [$label, $bg, $fg] = $map[$state] ?? $map['unread'];
    return sprintf(
        '<span class="badge" style="background:%s;color:%s;padding:0.25rem 0.5rem;border-radius:0.375rem;font-size:0.75rem;font-weight:600">%s</span>',
        $bg,
        $fg,
        h($label)
    );
};
?>

<div class="dash">

    <!-- Flash messages -->
    <div class="flash-wrap">
        <?= $this->Flash->render() ?>
        <?php if ($this->request->getQuery('export')): ?>
            <div class="alert alert-success">
                <div class="alert__title">CSV is ready</div>
                <div class="alert__body">
                    Your export was prepared successfully.
                    <?= $this->Html->link(
                        'Download CSV',
                        ['prefix'=>'Admin','controller'=>'ContactMessages','action'=>'exportDownload', $this->request->getQuery('export')],
                        ['class'=>'btn small btn-primary', 'style'=>'margin-left:.5rem']
                    ) ?>
                </div>
            </div>
            <script>
                (function(){
                    var token = <?= json_encode((string)$this->request->getQuery('export')) ?>;
                    if(!token) return;
                    var url = <?= json_encode($this->Url->build(
                        ['prefix'=>'Admin','controller'=>'ContactMessages','action'=>'exportDownload'],
                        ['escape'=>false]
                    )) ?> + '/' + encodeURIComponent(token);
                    setTimeout(function(){
                        var a=document.createElement('a');
                        a.href=url; a.target='_blank'; a.rel='noopener';
                        document.body.appendChild(a); a.click(); a.remove();
                    },300);
                })();
            </script>
        <?php endif; ?>
    </div>

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
            <div class="stat__label">Read</div>
            <div class="stat__value"><?= (int)$readCount ?></div>
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
                        <th>Status</th>
                        <th style="width:140px">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($latest)): ?>
                        <tr><td colspan="5" class="muted">No messages yet.</td></tr>
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
                                <td>
                                    <?= $badge($m->status ?? '') ?>
                                </td>
                                <td class="actions">
                                    <?= $this->Html->link('View',
                                        ['prefix'=>'Admin','controller'=>'ContactMessages','action'=>'view', $m->id],
                                        ['class'=>'btn tiny', 'title'=>'View message details']
                                    ) ?>
                                    <?= $this->Html->link('Reply',
                                        ['prefix'=>'Admin','controller'=>'ContactMessages','action'=>'reply', $m->id],
                                        ['class'=>'btn tiny btn-primary', 'title'=>'Reply to message']
                                    ) ?>
                                    <?= $this->Form->postLink(
                                        'Delete',
                                        ['prefix'=>'Admin','controller'=>'ContactMessages','action'=>'delete', $m->id],
                                        ['class'=>'btn tiny danger', 'confirm'=>'Are you sure you want to delete this message?']
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
                        "Read ({$readCount})",
                        ['prefix'=>'Admin','controller'=>'ContactMessages','action'=>'index','?'=>['status'=>'read']],
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
                    <li>
                    <li>
                        <?= $this->Html->link(
                            'Export CSV',
                            ['prefix'=>'Admin','controller'=>'ContactMessages','action'=>'export'],
                            ['class'=>'tool', 'target'=>'_blank', 'rel'=>'noopener']
                        ) ?>
                    </li>

                    </li>
                    <li>
                        <?= $this->Form->postLink(
                            'Mark all read',
                            ['prefix'=>'Admin','controller'=>'ContactMessages','action'=>'markAllRead'],
                            ['class'=>'tool', 'confirm'=>'Mark all unread and in-progress messages as Read?']
                        ) ?>
                    </li>
                </ul>
            </div>
        </aside>
    </section>
</div>

<style>
    .dash{max-width:1100px;margin:0 auto;padding:1.25rem 1rem}

    /* Flash / alert */
    .flash-wrap{margin:0 0 .8rem}
    .alert{border:1px solid #e5e7eb;background:#f9fafb;border-radius:.9rem;padding:.75rem .9rem;box-shadow:0 6px 24px rgba(0,0,0,.06)}
    .alert__title{font-weight:700;margin:0 0 .15rem}
    .alert__body{color:#374151}
    .alert-success{border-color:#bbf7d0;background:#ecfdf5}
    .alert-error{border-color:#fecaca;background:#fef2f2}
    .alert-info{border-color:#bfdbfe;background:#eff6ff}
    .page.hc .alert{background:#0f172a;border-color:#334155;color:#e5e7eb;box-shadow:none}
    .page.hc .alert-success{background:#0f3a2a;border-color:#14532d}
    .page.hc .alert-error{background:#3b0f17;border-color:#7f1d1d}
    .page.hc .alert-info{background:#0d1b34;border-color:#1d4ed8}

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

    .page.hc .card, .page.hc .stat{background:#0f172a;border-color:#334155;box-shadow:none}
    .page.hc .table thead th{color:#cbd5e1;border-color:#334155}
    .page.hc .table tbody td{border-color:#233044}
    .page.hc .pill{background:#111827;border-color:#334155;color:#e5e7eb}
    .page.hc .tool{background:#111827;border-color:#334155;color:#e5e7eb}
    .page.hc .btn{background:#1f2937;color:#fff;border-color:#475569}
    .page.hc .btn-primary{background:#60a5fa;color:#111}
    .page.hc .muted{color:#cbd5e1}

    @media (max-width: 960px){ .grid{grid-template-columns:1fr} .ellipsis{max-width:unset} }
    @media (max-width: 720px){ .stats{grid-template-columns:repeat(2,1fr)} }
</style>

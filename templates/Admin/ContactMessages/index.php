<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\ResultSetInterface|\App\Model\Entity\ContactMessage[] $messages
 * @var string|null $q
 * @var string|null $status
 * @var string|null $from
 * @var string|null $to
 */
$this->assign('title', 'Contact Messages');

$badge = function (?string $state): string {
    $state = strtolower((string)$state);
    $map = [
        'unread'      => ['Unread',       '#dbeafe', '#1d4ed8'], // 蓝
        'read'        => ['Read',         '#dcfce7', '#166534'], // 绿
        'in_progress' => ['In Progress',  '#fef3c7', '#92400e'], // 橙
        'closed'      => ['Closed',       '#fee2e2', '#dc2626'], // 红
        ''            => ['—',            '#e5e7eb', '#374151'],
    ];
    [$label, $bg, $fg] = $map[$state] ?? $map[''];
    return sprintf('<span class="badge" style="background:%s;color:%s">%s</span>', $bg, $fg, h($label));
};
?>

<section class="cm-admin">
    <header class="head">
        <h2>Contact Messages</h2>
        <p class="muted">Review, filter and manage customer enquiries.</p>
    </header>

    <!-- Filters -->
    <?= $this->Form->create(null, ['type' => 'get', 'class' => 'filter']) ?>
    <div class="filter__grid">
        <div class="field">
            <?= $this->Form->label('q', 'Search') ?>
            <?= $this->Form->text('q', [
                'value' => $q ?? '',
                'placeholder' => 'Name / Email / Message…',
                'autocomplete' => 'off'
            ]) ?>
        </div>

        <div class="field">
            <?= $this->Form->label('status', 'Status') ?>
            <?= $this->Form->select('status', [
                '' => 'All',
                'new' => 'New',
                'in_progress' => 'In progress',
                'closed' => 'Closed',
            ], ['value' => $status ?? '']) ?>
        </div>

        <div class="field">
            <?= $this->Form->label('from', 'From') ?>
            <?= $this->Form->control('from', [
                'type' => 'date',
                'label' => false,
                'value' => $from ?? '',
            ]) ?>
        </div>

        <div class="field">
            <?= $this->Form->label('to', 'To') ?>
            <?= $this->Form->control('to', [
                'type' => 'date',
                'label' => false,
                'value' => $to ?? '',
            ]) ?>
        </div>

        <div class="actions">
            <?= $this->Form->button('Filter', ['class' => 'btn btn-primary']) ?>
            <?= $this->Html->link('Reset', ['action' => 'index'], ['class' => 'btn btn-subtle']) ?>
        </div>
    </div>
    <?= $this->Form->end() ?>

    <!-- Table -->
    <div class="card">
        <div class="card__head">
            <strong><?= $pagination['count'] ?> message(s) total</strong>
        </div>

        <div class="table-wrap">
            <table class="tbl">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th class="col-message">Message</th>
                    <th>Created</th>
                    <th class="col-status">Status</th>
                    <th class="col-actions">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($messages as $m): ?>
                    <tr>
                        <td><?= h($m->name) ?></td>
                        <td><?= h($m->email) ?></td>
                        <td class="col-message">
                            <?= h(mb_strimwidth((string)$m->message, 0, 70, '…')) ?>
                        </td>
                        <td><?= $m->created?->i18nFormat('yyyy-MM-dd HH:mm') ?></td>
                        <td class="col-status">
                            <?php if ($m->status): ?>
                                <?= $badge($m->status) ?>
                            <?php else: ?>
                                <span class="badge" style="background:#e5e7eb;color:#374151">No Status</span>
                            <?php endif; ?>
                        </td>
                        <td class="col-actions">
                            <?= $this->Html->link('View', ['action' => 'view', $m->id], [
                                'class' => 'link',
                                'title' => 'View message details'
                            ]) ?>
                            <?= $this->Html->link('Reply', ['action' => 'reply', $m->id], [
                                'class' => 'link link-primary',
                                'title' => 'Reply to message'
                            ]) ?>
                            <?= $this->Form->postLink(
                                'Delete',
                                ['action' => 'delete', $m->id],
                                ['confirm' => 'Are you sure you want to delete this message?', 'class' => 'link link-danger']
                            ) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($messages->count() === 0): ?>
                    <tr><td colspan="6" class="empty">No messages found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Bootstrap Pagination -->
        <?php if ($pagination['pages'] > 1): ?>
            <nav aria-label="Contact messages pagination" class="mt-4">
                <ul class="pagination justify-content-center">
                    <!-- Previous Button -->
                    <li class="page-item <?= !$pagination['hasPrev'] ? 'disabled' : '' ?>">
                        <?php if ($pagination['hasPrev']): ?>
                            <a class="page-link" href="<?= $this->Url->build(['?' => array_merge($this->request->getQueryParams(), ['page' => $pagination['page'] - 1])]) ?>">
                                <span aria-hidden="true">&laquo;</span> Previous
                            </a>
                        <?php else: ?>
                            <span class="page-link"><span aria-hidden="true">&laquo;</span> Previous</span>
                        <?php endif; ?>
                    </li>

                    <?php
                    // Calculate page range
                    $start = max(1, $pagination['page'] - 2);
                    $end = min($pagination['pages'], $pagination['page'] + 2);
                    
                    // Show first page if not in range
                    if ($start > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= $this->Url->build(['?' => array_merge($this->request->getQueryParams(), ['page' => 1])]) ?>">1</a>
                        </li>
                        <?php if ($start > 2): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Page Numbers -->
                    <?php for ($i = $start; $i <= $end; $i++): ?>
                        <li class="page-item <?= $i == $pagination['page'] ? 'active' : '' ?>">
                            <?php if ($i == $pagination['page']): ?>
                                <span class="page-link"><?= $i ?></span>
                            <?php else: ?>
                                <a class="page-link" href="<?= $this->Url->build(['?' => array_merge($this->request->getQueryParams(), ['page' => $i])]) ?>"><?= $i ?></a>
                            <?php endif; ?>
                        </li>
                    <?php endfor; ?>

                    <?php
                    // Show last page if not in range
                    if ($end < $pagination['pages']): ?>
                        <?php if ($end < $pagination['pages'] - 1): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= $this->Url->build(['?' => array_merge($this->request->getQueryParams(), ['page' => $pagination['pages']])]) ?>"><?= $pagination['pages'] ?></a>
                        </li>
                    <?php endif; ?>

                    <!-- Next Button -->
                    <li class="page-item <?= !$pagination['hasNext'] ? 'disabled' : '' ?>">
                        <?php if ($pagination['hasNext']): ?>
                            <a class="page-link" href="<?= $this->Url->build(['?' => array_merge($this->request->getQueryParams(), ['page' => $pagination['page'] + 1])]) ?>">
                                Next <span aria-hidden="true">&raquo;</span>
                            </a>
                        <?php else: ?>
                            <span class="page-link">Next <span aria-hidden="true">&raquo;</span></span>
                        <?php endif; ?>
                    </li>
                </ul>
                
                <!-- Page Info -->
                <div class="text-center mt-2">
                    <small class="text-muted">
                        Page <?= $pagination['page'] ?> of <?= $pagination['pages'] ?> 
                        (<?= $pagination['count'] ?> total messages)
                    </small>
                </div>
            </nav>
        <?php endif; ?>
    </div>
</section>

<style>
    /* ====== Layout ====== */
    .cm-admin { max-width: 1100px; margin: 0 auto; padding: 1rem; }
    .head h2 { margin: .25rem 0; }
    .muted { color:#6b7280; }

    /* ====== Filters ====== */
    .filter { margin:.6rem 0 1rem; }
    .filter__grid { display:grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap:.7rem; align-items:end; }
    .field { display:flex; flex-direction:column; gap:.35rem; }
    .field input, .field select {
        padding:.55rem .7rem; border:1px solid #d1d5db; border-radius:.55rem; background:#f9fafb;
    }
    .actions .btn { margin-right:.4rem; }

    /* ====== Buttons ====== */
    .btn{display:inline-block;padding:.6rem 1rem;border-radius:.6rem;border:1px solid transparent;background:#e5e7eb;color:#111;text-decoration:none}
    .btn:hover{filter:brightness(.98)}
    .btn-primary{background:#2c7be5;color:#fff}
    .btn-subtle{background:transparent;border-color:#d1d5db;color:#374151}

    /* ====== Card & table ====== */
    .card{ background:#fff; border-radius:1rem; box-shadow:0 12px 36px rgba(0,0,0,.06); }
    .card__head{ padding:.8rem 1rem; border-bottom:1px solid #eef0f3; }
    .table-wrap{ overflow-x:auto; }
    .tbl{ width:100%; border-collapse:separate; border-spacing:0; min-width: 800px; }
    .tbl th, .tbl td{ padding:.7rem .9rem; border-bottom:1px solid #eef0f3; text-align:left; }
    .tbl thead th{ font-weight:700; color:#111; white-space:nowrap; }
    .tbl tbody tr:hover{ background:#fafafa; }
    .col-message{ min-width: 320px; }
    .col-actions{ white-space:nowrap; min-width: 180px; }
    .col-status{ min-width: 100px; }
    .link{ color:#2563eb; text-decoration:none; margin-right:.55rem }
    .link:hover{ text-decoration:underline }
    .link-primary{ color:#2563eb; font-weight:600; }
    .link-danger{ color:#b91c1c }

    /* ====== Badges ====== */
    .badge{ display:inline-block; padding:.18rem .55rem; border-radius:999px; font-weight:700; font-size:.85rem }

    /* ====== Empty ====== */
    .empty{ text-align:center; color:#6b7280; }

    /* ====== Pager ====== */
    .pager{ display:flex; justify-content:space-between; align-items:center; padding:.8rem 1rem }
    .page, .nums a{ padding:.35rem .55rem; border:1px solid #e5e7eb; border-radius:.45rem; margin-right:.25rem; text-decoration:none; color:#111; background:#fff }
    .nums .current{ padding:.35rem .55rem; border-radius:.45rem; background:#2c7be5; color:#fff; border-color:#2c7be5 }
    .pager__counter{ color:#6b7280 }

    /* ====== High contrast ====== */
    .page.hc .card{ background:#0f172a; }
    .page.hc .tbl th, .page.hc .tbl td{ border-color:#334155; color:#e5e7eb }
    .page.hc .link{ color:#93c5fd }
    .page.hc .link-danger{ color:#fca5a5 }
    .page.hc .field input, .page.hc .field select{ background:#0b1220; color:#e5e7eb; border-color:#334155 }
    .page.hc .btn{ background:#1f2937; color:#fff; border-color:#475569 }
    .page.hc .btn-primary{ background:#60a5fa; color:#111 }

    /* ====== Responsive ====== */
    @media (max-width: 900px){
        .filter__grid{ grid-template-columns: 1fr 1fr; }
        .col-message{ min-width: 260px; }
    }
</style>

<script>
    document.addEventListener('change', (e) => {
        const ids = ['status','from','to'];
        if (ids.includes(e.target.id)) {
            e.target.closest('form')?.submit();
        }
    });
</script>

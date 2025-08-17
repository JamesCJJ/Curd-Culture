<?php
/**
 * Contact Submissions list
 * @var \Cake\Datasource\ResultSetInterface $contactMessages
 */
$this->assign('title', 'Contact Submissions');
?>

<section class="cm-list">
    <header class="cm-list-head">
        <h2>Contact Submissions</h2>
        <p class="muted">Newest first. Use pagination below to browse.</p>
    </header>

    <?= $this->Flash->render() ?>

    <div class="table-wrap">
        <table class="pretty">
            <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Message</th>
                <th>Submitted At</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($contactMessages as $m): ?>
                <tr>
                    <td><?= h($m->name) ?></td>
                    <td><?= $this->Html->link(h($m->email), 'mailto:' . h($m->email)) ?></td>
                    <td><?= h(mb_strimwidth((string)$m->message, 0, 120, '…')) ?></td>
                    <td><?= $m->created ? $m->created->format('Y-m-d H:i') : '' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('« First') ?>
            <?= $this->Paginator->prev('‹ Prev') ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next('Next ›') ?>
            <?= $this->Paginator->last('Last »') ?>
        </ul>
        <p class="muted"><?= $this->Paginator->counter(['format' => 'Page {{page}} of {{pages}}, showing {{current}} record(s).']) ?></p>
    </div>
</section>

<style>
    .cm-list{max-width:1100px;margin:0 auto;padding:1rem}
    .cm-list-head{display:flex;align-items:baseline;gap:.75rem;flex-wrap:wrap}
    .cm-list-head h2{margin:.25rem 0}

    .table-wrap{background:#fff;border-radius:1rem;box-shadow:0 10px 30px rgba(0,0,0,.06);overflow:hidden}
    .page.hc .table-wrap{background:#0f172a}

    table.pretty{width:100%;border-collapse:collapse}
    table.pretty th, table.pretty td{padding:.8rem 1rem;text-align:left;vertical-align:top}
    table.pretty thead th{font-weight:700;border-bottom:1px solid #e5e7eb;background:#f8fafc}
    table.pretty tbody tr:nth-child(odd){background:#fbfbfd}
    table.pretty tbody tr:hover{background:#f3f4f6}

    .pagination{display:flex;flex-wrap:wrap;gap:.35rem;list-style:none;padding:0;margin:1rem 0}
    .pagination a, .pagination span{
        display:inline-block; padding:.45rem .7rem; border-radius:.5rem; border:1px solid #e5e7eb; text-decoration:none; color:#111
    }
    .pagination .active a, .pagination .active span{background:#2c7be5;color:#fff;border-color:#2c7be5}
    .pagination a:hover{filter:brightness(.98)}
</style>

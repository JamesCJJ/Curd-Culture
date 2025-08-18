<?php
/**
 * @var \App\Model\Entity\ContactMessage $msg
 * @var array|null $statuses
 */
$this->assign('title', 'Message #' . (int)$msg->id);


$statuses = $statuses ?? [
    'new'         => 'New',
    'in_progress' => 'In progress',
    'closed'      => 'Closed',
];
?>

<section class="card" style="max-width:900px;margin:0 auto;padding:1rem;">
    <h2>Message #<?= h($msg->id) ?></h2>
    <p class="muted">View &amp; reply to a contact message, and update its status.</p>

    <?= $this->Flash->render() ?>

    <div class="box">
        <p><strong>From:</strong> <?= h($msg->name) ?> &lt;<?= h($msg->email) ?>&gt;</p>
        <p><strong>Sent:</strong> <?= $msg->created?->i18nFormat('yyyy-MM-dd HH:mm') ?></p>
        <p><strong>Status:</strong> <span class="badge"><?= h(ucwords(str_replace('_', ' ', (string)$msg->status))) ?></span></p>
        <hr>
        <p><strong>Message</strong></p>
        <div style="white-space:pre-wrap"><?= h($msg->message) ?></div>
    </div>

    <?= $this->Form->create($msg) ?>
    <fieldset style="margin-top:1rem;">
        <legend>Reply / Update</legend>

        <?= $this->Form->control('status', [
            'type'    => 'select',
            'label'   => 'Status',
            'options' => $statuses,
            'empty'   => false,
            'value'   => $msg->status
        ]) ?>

        <?= $this->Form->control('reply_note', [
            'type'        => 'textarea',
            'label'       => 'Reply note',
            'rows'        => 5,
            'placeholder' => 'Write your note to the customer (for record)…',
        ]) ?>
    </fieldset>

    <div style="margin-top:.75rem;display:flex;gap:.5rem;">
        <?= $this->Form->button('Save', ['class'=>'btn btn-primary']) ?>
        <?= $this->Html->link('Back', ['action' => 'index', '?' => $this->request->getQueryParams()], ['class'=>'btn']) ?>
        <?= $this->Form->postLink('Delete', ['action' => 'delete', $msg->id], [
            'confirm' => 'Delete this message?', 'class'=>'btn btn-danger'
        ]) ?>
    </div>
    <?= $this->Form->end() ?>
</section>

<style>
    .badge{display:inline-block;padding:.15rem .5rem;border-radius:999px;background:#eef2ff;color:#1f2a5a;font-weight:700}
    .muted{color:#6b7280}
    .card{background:#fff;border-radius:1rem;box-shadow:0 12px 36px rgba(0,0,0,.06)}
    .btn{display:inline-block;padding:.55rem .9rem;border-radius:.6rem;border:1px solid #d1d5db;background:#f3f4f6;color:#111;text-decoration:none}
    .btn-primary{background:#2c7be5;color:#fff;border-color:#2c7be5}
    .btn-danger{background:#fee2e2;color:#991b1b;border-color:#fecaca}
    .box{padding:.75rem;border:1px solid #e5e7eb;border-radius:.6rem;background:#fafafa}
    .page.hc .card{background:#0f172a}
    .page.hc .btn{background:#1f2937;color:#fff;border-color:#475569}
    .page.hc .btn-primary{background:#60a5fa;color:#111}
</style>

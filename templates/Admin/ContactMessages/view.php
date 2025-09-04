<?php
/**
 * @var \App\Model\Entity\ContactMessage $msg
 * @var \App\View\AppView $this
 */
$this->assign('title', 'Message #' . (int)$msg->id);

$statuses = [
    'unread'      => 'Unread',
    'read'        => 'Read',
    'in_progress' => 'In Progress',
    'closed'      => 'Closed',
];


$badgeClass = [
    'unread'      => 'chip chip--blue',
    'read'        => 'chip chip--green',
    'in_progress' => 'chip chip--amber',
    'closed'      => 'chip chip--red',
];
$st  = (string)($msg->status ?? 'unread');
$cls = $badgeClass[$st] ?? 'chip';
$lbl = $statuses[$st] ?? ucfirst($st);
?>
<div class="view-wrap">
    <?= $this->Flash->render() ?>

    <header class="hero">
        <div class="hero__title">
            <h1>Message #<?= h($msg->id) ?></h1>
            <p class="muted">View the details, update status, and leave a reply note.</p>
        </div>
        <div class="hero__chip <?= h($cls) ?>"><?= h($lbl) ?></div>
    </header>

    <section class="meta">
        <div class="meta__item">
            <div class="meta__label">From</div>
            <div class="meta__value"><?= h($msg->name) ?> &lt;<?= h($msg->email) ?>&gt;</div>
        </div>
        <div class="meta__item">
            <div class="meta__label">Sent</div>
            <div class="meta__value"><?= $msg->created?->i18nFormat('yyyy-MM-dd HH:mm') ?></div>
        </div>
        <div class="meta__item">
            <div class="meta__label">Status</div>
            <div class="meta__value">
                <?= $this->Form->create($msg, [
                    'class' => 'status-form', 
                    'url' => ['action' => 'view', $msg->id],
                    'type' => 'post'
                ]) ?>
                <?php
                // Build status options excluding the current status
                $statusOptions = [];
                $allStatuses = [
                    'read' => 'Read',
                    'in_progress' => 'In Progress', 
                    'closed' => 'Closed'
                ];
                foreach ($allStatuses as $key => $label) {
                    if ($key !== $msg->status) {
                        $statusOptions[$key] = $label;
                    }
                }
                ?>
                <?= $this->Form->select('status', $statusOptions, [
                    'empty' => 'Change status...',
                    'class' => 'status-select',
                    'onchange' => 'this.form.submit();'
                ]) ?>
                <?= $this->Form->end() ?>
            </div>
        </div>
        <?php if ($msg->replied_at): ?>
            <div class="meta__item">
                <div class="meta__label">Last updated</div>
                <div class="meta__value"><?= $msg->modified?->i18nFormat('yyyy-MM-dd HH:mm') ?></div>
            </div>
        <?php endif; ?>
    </section>

    <section class="card message">
        <div class="card__head">
            <h2>Message</h2>
        </div>
        <div class="card__body">
            <pre class="bubble"><?= h($msg->message) ?></pre>
        </div>
    </section>

    <?= $this->Form->create($msg, ['class' => 'card form', 'novalidate' => true]) ?>
    <div class="card__head">
        <h2>Reply / Update</h2>
    </div>

    <div class="card__body grid">
        <div class="field">
            <?= $this->Form->label('status', 'Status') ?>
            <?= $this->Form->select('status', $statuses, [
                'empty' => false,
                'value' => $msg->status,
                'class' => 'select'
            ]) ?>
        </div>

        <div class="field field--full">
            <?= $this->Form->label('reply_note', 'Reply note') ?>
            <?= $this->Form->textarea('reply_note', [
                'rows'        => 6,
                'placeholder' => 'Write a concise note for the customer and for internal record…',
                'class'       => 'textarea'
            ]) ?>
            <small class="help">When you save, <code>replied_at</code> and <code>replied_by</code> will be recorded automatically (if you wrote a note).</small>
        </div>
    </div>

    <div class="card__foot actions">
        <?= $this->Form->button('Save', ['class' => 'btn btn--primary']) ?>
        <?= $this->Html->link('Back', ['action' => 'index', '?' => $this->request->getQueryParams()], ['class' => 'btn']) ?>
        <?= $this->Form->postLink('Delete', ['action' => 'delete', $msg->id], [
            'confirm' => 'Delete this message?',
            'class'   => 'btn btn--danger'
        ]) ?>
    </div>
    <?= $this->Form->end() ?>
</div>

<style>
    /* ======= Design tokens ======= */
    .view-wrap{
        --bg:#f6f7fb;
        --card:#fff;
        --text:#0f172a;
        --muted:#6b7280;
        --line:#e6e8ef;
        --shadow:0 18px 60px rgba(15,23,42,.06);
        --radius:18px;
        --blue:#2563eb;
        --blue-weak:#e8efff;
        --green:#16a34a;
        --green-weak:#e8f7ee;
        --amber:#f59e0b;
        --amber-weak:#fff3d6;
        --red:#dc2626;
        --red-weak:#fee2e2;
        color:var(--text);
        background:var(--bg);
        padding:24px 16px 48px;
    }

    /* container */
    .view-wrap > *{ max-width:960px; margin-inline:auto; }

    /* hero */
    .hero{
        display:flex; justify-content:space-between; align-items:flex-end;
        gap:1rem; margin: 6px auto 18px;
    }
    .hero__title h1{ font-size: clamp(1.25rem, 2.3vw, 1.6rem); margin:.25rem 0 4px; }
    .muted{ color:var(--muted); margin:0; }

    /* status chip */
    .chip{ display:inline-flex; align-items:center; gap:.5rem;
        border-radius:999px; padding:.4rem .75rem; font-weight:700; font-size:.9rem; background:#eef2f7 }
    .chip--blue{ background:var(--blue-weak); color:var(--blue) }
    .chip--green{ background:var(--green-weak); color:var(--green) }
    .chip--amber{ background:var(--amber-weak); color:var(--amber) }
    .chip--red{ background:var(--red-weak); color:var(--red) }
    .hero__chip{ align-self:flex-start }

    /* meta row */
    .meta{
        display:grid; grid-template-columns: 1.4fr .8fr 1fr; gap:.9rem;
        margin:0 auto 12px;
    }
    .meta__item{ background:var(--card); border:1px solid var(--line); border-radius:12px; padding:.7rem .9rem; box-shadow: var(--shadow); }
    .meta__label{ color:var(--muted); font-size:.85rem; margin-bottom:.25rem }
    .meta__value{ font-weight:600 }

    /* card */
    .card{ background:var(--card); border:1px solid var(--line); border-radius:var(--radius); box-shadow:var(--shadow); margin:16px auto }
    .card__head{ padding:1rem 1.15rem 0; }
    .card__head h2{ margin:0; font-size:1.05rem }
    .card__body{ padding: .9rem 1.15rem 1.15rem; }
    .card__foot{ padding: .9rem 1.15rem 1.15rem; border-top:1px solid var(--line) }

    /* message bubble */
    .bubble{
        margin:0; white-space:pre-wrap; line-height:1.7;
        background:linear-gradient(180deg, #fbfcff, #f4f7ff);
        border:1px dashed #d7def0; border-radius:14px; padding:1rem 1.1rem; font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
    }

    /* form */
    .grid{ display:grid; grid-template-columns: 260px 1fr; gap:1rem 1.25rem }
    .field{ display:flex; flex-direction:column; gap:.4rem }
    .field--full{ grid-column: 1 / -1 }
    label{ font-weight:600 }
    .select, .textarea{
        border:1px solid var(--line); border-radius:12px; padding:.65rem .8rem; background:#fbfbfe;
    }
    .select:focus, .textarea:focus{ outline:3px solid rgba(37,99,235,.15); border-color:#b9c6ff }
    .help{ color:var(--muted) }

    /* status container */
    .status-container{ display:flex; flex-direction:column; gap:.6rem; align-items:flex-start }
    .status-actions{ display:flex; align-items:center; gap:.5rem }
    .status-form{ margin:0 }
    .status-select{
        border:1px solid var(--line); border-radius:8px; padding:.4rem .6rem; background:#fbfbfe;
        font-size:.85rem; min-width:150px;
    }
    .status-select:focus{ outline:2px solid rgba(37,99,235,.15); border-color:#b9c6ff }

    .actions{ display:flex; gap:.5rem; justify-content:flex-end }
    .btn{
        background:#eef1f6; color:#111; border:1px solid #dfe4ef;
        padding:.65rem 1rem; border-radius:12px; text-decoration:none; display:inline-flex; gap:.5rem; align-items:center;
    }
    .btn:hover{ filter:brightness(.98); transform: translateY(-1px); transition:.15s }
    .btn--primary{ background:var(--blue); border-color:var(--blue); color:#fff }
    .btn--danger{ background:#fee2e2; border-color:#fecaca; color:#7f1d1d }

    @media (max-width: 880px){
        .meta{ grid-template-columns: 1fr 1fr 1fr; }
    }
    @media (max-width: 650px){
        .meta{ grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 600px){
        .grid{ grid-template-columns: 1fr; }
        .hero{ align-items:flex-start; flex-direction:column; }
    }


    .page.hc .view-wrap{
        --bg:#0b0f14; --card:#0f172a; --text:#e5e7eb; --muted:#cbd5e1; --line:#334155;
        --blue-weak:#102a5c; --green-weak:#0f3922; --amber-weak:#3c2f10;
    }
</style>

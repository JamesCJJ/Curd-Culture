<?php
/**
 * @var \App\Model\Entity\ContactMessage $msg
 * @var \App\View\AppView $this
 */
$this->assign('title', 'Reply to Message #' . (int)$msg->id);

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
<div class="reply-wrap">
    <?= $this->Flash->render() ?>

    <header class="hero">
        <div class="hero__title">
            <h1>Reply to Message #<?= h($msg->id) ?></h1>
            <p class="muted">Compose a reply and update the message status.</p>
        </div>
        <div class="hero__actions">
            <?= $this->Html->link('View Only', ['action' => 'view', $msg->id], [
                'class' => 'btn btn--secondary',
                'title' => 'View message without replying'
            ]) ?>
            <div class="hero__chip <?= h($cls) ?>"><?= h($lbl) ?></div>
        </div>
    </header>

    <section class="grid-layout">
        <!-- Original Message -->
        <div class="original-message">
            <div class="card">
                <div class="card__head">
                    <h2>Original Message</h2>
                    <span class="message-date"><?= $msg->created?->i18nFormat('MMM d, yyyy · h:mm a') ?></span>
                </div>
                <div class="card__body">
                    <div class="sender-info">
                        <strong><?= h($msg->name) ?></strong>
                        <span class="email">&lt;<?= h($msg->email) ?>&gt;</span>
                    </div>
                    <pre class="bubble original"><?= h($msg->message) ?></pre>
                </div>
            </div>

            <?php if ($msg->replied_at && $msg->reply_note): ?>
            <div class="card previous-reply">
                <div class="card__head">
                    <h3>Previous Reply</h3>
                    <span class="reply-date"><?= $msg->replied_at?->i18nFormat('MMM d, yyyy · h:mm a') ?></span>
                </div>
                <div class="card__body">
                    <pre class="bubble previous"><?= h($msg->reply_note) ?></pre>
                    <?php if ($msg->user): ?>
                        <p class="reply-author">By: <?= h($msg->user->name ?? $msg->user->email) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Reply Form -->
        <div class="reply-form">
            <?= $this->Form->create($msg, ['class' => 'card form', 'novalidate' => true]) ?>
            <div class="card__head">
                <h2>Your Reply</h2>
            </div>

            <div class="card__body">
                <div class="field field--status">
                    <?= $this->Form->label('status', 'Update Status') ?>
                    <?= $this->Form->select('status', $statuses, [
                        'empty' => false,
                        'value' => $msg->status,
                        'class' => 'select'
                    ]) ?>
                    <small class="help">Choose the appropriate status for this message</small>
                </div>

                <div class="field field--reply">
                    <?= $this->Form->label('reply_note', 'Reply Message') ?>
                    <?= $this->Form->textarea('reply_note', [
                        'rows'        => 8,
                        'placeholder' => 'Type your response to the customer here...',
                        'class'       => 'textarea',
                        'required'    => true
                    ]) ?>
                    <small class="help">This reply will be recorded with your user information and timestamp.</small>
                </div>
            </div>

            <div class="card__foot actions">
                <?= $this->Form->button('Send Reply', ['class' => 'btn btn--primary btn--large']) ?>
                <?= $this->Html->link('Cancel', ['action' => 'view', $msg->id], ['class' => 'btn btn--secondary']) ?>
                <?= $this->Html->link('Back to Messages', ['action' => 'index', '?' => $this->request->getQueryParams()], ['class' => 'btn']) ?>
            </div>
            <?= $this->Form->end() ?>
        </div>
    </section>
</div>

<style>
    /* ======= Design tokens ======= */
    .reply-wrap{
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
    .reply-wrap > *{ max-width:1200px; margin-inline:auto; }

    /* hero */
    .hero{
        display:flex; justify-content:space-between; align-items:flex-end;
        gap:1rem; margin: 6px auto 24px;
    }
    .hero__title h1{ font-size: clamp(1.25rem, 2.3vw, 1.6rem); margin:.25rem 0 4px; }
    .hero__actions{ display:flex; align-items:center; gap:1rem; }
    .muted{ color:var(--muted); margin:0; }

    /* status chip */
    .chip{ display:inline-flex; align-items:center; gap:.5rem;
        border-radius:999px; padding:.4rem .75rem; font-weight:700; font-size:.9rem; background:#eef2f7 }
    .chip--blue{ background:var(--blue-weak); color:var(--blue) }
    .chip--green{ background:var(--green-weak); color:var(--green) }
    .chip--amber{ background:var(--amber-weak); color:var(--amber) }
    .chip--red{ background:var(--red-weak); color:var(--red) }

    /* grid layout */
    .grid-layout{
        display:grid; grid-template-columns: 1fr 1fr; gap:2rem;
        margin:0 auto;
    }

    /* card */
    .card{ background:var(--card); border:1px solid var(--line); border-radius:var(--radius); box-shadow:var(--shadow); margin:16px 0 }
    .card__head{ padding:1rem 1.15rem 0; display:flex; justify-content:space-between; align-items:center; }
    .card__head h2, .card__head h3{ margin:0; font-size:1.05rem }
    .card__body{ padding: .9rem 1.15rem 1.15rem; }
    .card__foot{ padding: .9rem 1.15rem 1.15rem; border-top:1px solid var(--line) }

    /* message elements */
    .message-date, .reply-date{ color:var(--muted); font-size:.85rem; }
    .sender-info{ margin-bottom:1rem; }
    .sender-info strong{ color:var(--text); }
    .email{ color:var(--muted); margin-left:.5rem; }

    /* message bubbles */
    .bubble{
        margin:0; white-space:pre-wrap; line-height:1.7; font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
        border-radius:14px; padding:1rem 1.1rem;
    }
    .bubble.original{
        background:linear-gradient(180deg, #fbfcff, #f4f7ff);
        border:1px dashed #d7def0;
    }
    .bubble.previous{
        background:linear-gradient(180deg, #f0f9ff, #e0f2fe);
        border:1px dashed #a7d8f0;
    }

    .previous-reply{ margin-top:1rem; }
    .reply-author{ color:var(--muted); font-size:.85rem; margin:.5rem 0 0; font-style:italic; }

    /* form */
    .field{ display:flex; flex-direction:column; gap:.4rem; margin-bottom:1.5rem; }
    .field--status{ margin-bottom:1rem; }
    .field--reply{ flex:1; }
    label{ font-weight:600; color:var(--text); }
    .select, .textarea{
        border:1px solid var(--line); border-radius:12px; padding:.65rem .8rem; background:#fbfbfe;
        font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
    }
    .textarea{ resize:vertical; min-height:200px; line-height:1.6; }
    .select:focus, .textarea:focus{ outline:3px solid rgba(37,99,235,.15); border-color:#b9c6ff }
    .help{ color:var(--muted); font-size:.85rem; margin-top:.25rem; }

    /* actions */
    .actions{ display:flex; gap:.75rem; justify-content:flex-end; flex-wrap:wrap; }
    .btn{
        background:#eef1f6; color:#111; border:1px solid #dfe4ef;
        padding:.65rem 1rem; border-radius:12px; text-decoration:none; display:inline-flex; gap:.5rem; align-items:center;
        font-weight:600; transition: all .15s ease; cursor:pointer;
    }
    .btn:hover{ filter:brightness(.98); transform: translateY(-1px); }
    .btn--primary{ background:var(--blue); border-color:var(--blue); color:#fff }
    .btn--secondary{ background:#f8fafc; border-color:#e2e8f0; color:#374151; }
    .btn--large{ padding:.85rem 1.5rem; font-size:1.05rem; }

    /* responsive */
    @media (max-width: 1024px){
        .grid-layout{ grid-template-columns: 1fr; gap:1rem; }
        .original-message{ order:1; }
        .reply-form{ order:2; }
    }
    @media (max-width: 600px){
        .hero{ align-items:flex-start; flex-direction:column; }
        .hero__actions{ align-self:stretch; justify-content:space-between; }
        .actions{ justify-content:stretch; }
        .btn{ flex:1; justify-content:center; }
    }

    /* dark theme */
    .page.hc .reply-wrap{
        --bg:#0b0f14; --card:#0f172a; --text:#e5e7eb; --muted:#cbd5e1; --line:#334155;
        --blue-weak:#102a5c; --green-weak:#0f3922; --amber-weak:#3c2f10;
    }
</style>

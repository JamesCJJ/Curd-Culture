<?php
/**
 * Contact form
 * @var \App\Model\Entity\ContactMessage $contact
 */
use Cake\Core\Configure;

$this->assign('title', 'Contact Us');
$siteKey = h(Configure::read('Security.recaptcha.site_key')); // 从配置读取
?>

<section class="cm-wrap">
    <header class="cm-head">
        <h2>Contact Us</h2>
        <p class="muted">
            Questions about our cheeses or delivery windows?
            Send us a note and we’ll get back shortly.
        </p>
    </header>

    <?= $this->Flash->render() ?>

    <div class="cm-card">
        <?= $this->Form->create($contact, ['novalidate' => true]) ?>
        <div class="grid">
            <div class="field">
                <?= $this->Form->label('name', 'Name') ?>
                <?= $this->Form->text('name', [
                    'placeholder' => 'Your name',
                    'required'    => true
                ]) ?>
            </div>

            <div class="field">
                <?= $this->Form->label('email', 'Email') ?>
                <?= $this->Form->email('email', [
                    'placeholder' => 'you@example.com',
                    'required'    => true
                ]) ?>
            </div>
        </div>

        <div class="field">
            <?= $this->Form->label('message', 'Message') ?>
            <?= $this->Form->textarea('message', [
                'rows'       => 6,
                'placeholder'=> 'How can we help?',
                'required'   => true
            ]) ?>
            <small class="muted">Please avoid sharing sensitive data.</small>
        </div>

        <!-- === Google reCAPTCHA v2 checkbox === -->
        <?= $this->Html->script(
            'https://www.google.com/recaptcha/api.js',
            ['async' => true, 'defer' => true]
        ) ?>
        <div class="field" style="margin-top:1rem">
            <div class="g-recaptcha" data-sitekey="<?= $siteKey ?>"></div>
        </div>

        <div class="actions">
            <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
            <?= $this->Form->reset(__('Reset'),  ['class' => 'btn']) ?>
            <?= $this->Html->link(__('Back'), 'javascript:history.back()', ['class' => 'btn btn-subtle']) ?>
        </div>
        <?= $this->Form->end() ?>
    </div>
</section>

<style>
    /* —— 保留你原来的样式 —— */
    .cm-wrap{max-width:780px;margin:0 auto;padding:1rem}
    .cm-head h2{margin:.25rem 0}
    .muted{color:#6b7280}
    .cm-card{background:#fff;border-radius:1rem;box-shadow:0 10px 30px rgba(0,0,0,.06);padding:1.25rem}
    .page.hc .cm-card{background:#0f172a}
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
    @media(max-width:700px){.grid{grid-template-columns:1fr}}
    .field{display:flex;flex-direction:column;gap:.35rem;margin:.5rem 0}
    .field input,.field textarea{width:100%;padding:.65rem .8rem;border:1px solid #d1d5db;border-radius:.6rem;background:#f9fafb}
    .field input:focus,.field textarea:focus{outline:3px solid rgba(44,123,229,.2);border-color:#9ca3af}
    .actions{display:flex;gap:.6rem;flex-wrap:wrap;margin-top:1rem}
    /* High-contrast */
    .page.hc .cm-card{background:#0f172a;color:#f1f5f9}
    .page.hc .cm-captcha{border-color:#334155}
    .page.hc .field input,.page.hc .field textarea,.page.hc .captcha-row input{background:#0b1220;color:#e5e7eb;border-color:#334155}
    .page.hc .field input::placeholder,.page.hc .field textarea::placeholder{color:#9aa3ae}
    .page.hc .btn{background:#1f2937;color:#fff;border-color:#475569}
    .page.hc .btn-primary{background:#60a5fa;color:#111}
</style>

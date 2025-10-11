<?php
$this->assign('title', 'Forgot password');
?>
<section class="auth-page fp">
    <div class="auth-card fp-card">
        <header class="auth-head fp-head">
            <div class="auth-emoji" aria-hidden="true">🔐</div>
            <h1>Forgot password</h1>
            <p>Enter your account email. We’ll send a 6-digit verification code.</p>
        </header>

        <!-- Flash messages -->
        <div class="auth-flash">
            <?= $this->Flash->render() ?>
        </div>

        <?= $this->Form->create(null, ['class' => 'auth-form', 'novalidate' => true]) ?>

        <!-- Email -->
        <div class="form-group">
            <label for="email" class="auth-label">Email</label>
            <div class="input-with-icon">
                <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none"
                     xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M4 6h16a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2z" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M22 8l-9.2 5.75a2 2 0 0 1-1.6 0L2 8" stroke="currentColor" stroke-width="1.5"/>
                </svg>
                <?= $this->Form->control('email', [
                    'id' => 'email',
                    'label' => false,
                    'autocomplete' => 'email',
                    'placeholder' => 'you@example.com',
                    'class' => 'auth-input with-icon'
                ]) ?>
            </div>
            <small class="fp-help">We’ll send a code that expires in about 10 minutes.</small>
        </div>

        <div class="auth-actions">
            <?= $this->Form->button('Send verification code', ['class' => 'auth-btn auth-btn-primary fp-btn']) ?>
        </div>

        <?= $this->Form->end() ?>

        <div class="fp-back">
            <?= $this->Html->link('← Back to sign in', ['controller' => 'Users', 'action' => 'login'], ['class' => 'auth-link-small']) ?>
        </div>
    </div>
</section>

<style>
    /* === Layout: hard-center the card regardless of outer layout === */
    .auth-page.fp{
        min-height: 72vh;           /* keeps it away from footer */
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 3rem 1rem;
        background:
            radial-gradient(1200px 900px at 10% -10%, rgba(44,123,229,.06), transparent 60%),
            radial-gradient(1000px 700px at 110% 10%, rgba(16,185,129,.05), transparent 55%);
    }
    .theme-dark .auth-page.fp{
        background:
            radial-gradient(1200px 900px at 10% -10%, rgba(96,165,250,.08), transparent 60%),
            radial-gradient(1000px 700px at 110% 10%, rgba(45,212,191,.07), transparent 55%);
    }

    /* === Card === */
    .auth-card.fp-card{
        width: min(520px, 100%);
        padding: 1.6rem 1.35rem 1.35rem;
        border-radius: 1rem;
        background: #fff;
        border: 1px solid #eef0f3;
        box-shadow: 0 12px 36px rgba(2,6,23,.08);
    }
    .theme-dark .auth-card.fp-card{
        background:#111827;
        border-color:#1f2937;
        box-shadow: 0 16px 48px rgba(0,0,0,.35);
    }

    /* === Header === */
    .fp-head{ text-align:center; margin-bottom: .8rem; }
    .fp-head h1{
        margin:.15rem 0 .35rem;
        font-size: 1.6rem;
        font-weight: 800;
        letter-spacing:.2px;
    }
    .fp-head p{ margin: 0; color:#6b7280; }
    .theme-dark .fp-head p{ color:#cbd5e1; }

    /* Lock badge */
    .auth-emoji{
        width:54px;height:54px;
        display:inline-grid;place-items:center;
        margin:0 auto .4rem;
        border-radius:16px;
        background:#eef2ff; color:#1d4ed8;
        box-shadow:0 6px 16px rgba(29,78,216,.12);
        font-size:26px; line-height:1;
    }
    .theme-dark .auth-emoji{ background:#0b1220; color:#93c5fd; border:1px solid #1e293b; box-shadow:none; }

    /* === Flash messages === */
    .auth-flash :where(.message, .success, .error){
        display:block; width:100%;
        padding:.55rem .7rem; border-radius:.65rem; font-size:.92rem;
        margin: .25rem 0 .6rem;
    }
    .auth-flash .success{ background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; }
    .auth-flash .error{   background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }
    .theme-dark .auth-flash .success{ background:#052e2b; color:#a7f3d0; border-color:#115e59; }
    .theme-dark .auth-flash .error{   background:#3f1f1f; color:#fecaca; border-color:#7f1d1d; }

    /* === Input + icon === */
    .form-group{ margin: .9rem 0 1.1rem; }
    .auth-label{ display:block; font-weight:700; font-size:.95rem; margin:0 0 .35rem; color:#374151; }
    .theme-dark .auth-label{ color:#e5e7eb; }

    .input-with-icon{ position:relative; }
    .input-with-icon .input-icon{
        position:absolute; left:12px; top:50%; transform:translateY(-50%); opacity:.65;
    }
    .auth-input{
        display:block; width:100%;
        padding:12px 14px; font-size:15px; border-radius:.7rem;
        border:none; outline:none; box-shadow: inset 0 1px 2px rgba(0,0,0,.05);
        background:#f9fafb; color:#111827; transition:all .2s ease-in-out;
    }
    .auth-input.with-icon{ padding-left:38px; }
    .auth-input::placeholder{ color:#9ca3af; }
    .auth-input:focus{ background:#fff; box-shadow:0 0 0 3px rgba(44,123,229,.28); }
    .theme-dark .auth-input{
        background:#0f172a;color:#e5e7eb;box-shadow: inset 0 1px 2px rgba(0,0,0,.2)
    }
    .theme-dark .auth-input:focus{ background:#0b1220; box-shadow:0 0 0 3px rgba(96,165,250,.32) }

    /* Helper text */
    .fp-help{
        display:block; margin:.45rem 0 0;
        color:#6b7280; font-size:.86rem;
    }
    .theme-dark .fp-help{ color:#94a3b8; }

    /* Button full width on small screens */
    .auth-actions{ margin-top:.4rem; }
    .fp-btn{ width:100%; }
    @media (min-width:480px){
        .fp-btn{ width:320px; }
    }

    /* Back link */
    .fp-back{ margin-top:.9rem; text-align:center; }
    /* Center the "Send verification code" button */
    .auth-page.fp .auth-actions{
        display: flex;
        justify-content: center;   /* horizontally center children */
    }

    .auth-page.fp .auth-actions .fp-btn{
        display: block;
        width: min(320px, 100%);   /* nice fixed width on desktop, full on mobile */
        margin: 0 auto;            /* ensure centering even if flex is overridden */
    }
</style>

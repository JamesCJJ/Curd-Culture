<?php
/**
 * Admin login view
 */
$this->assign('title', 'Admin Login');
?>

<section class="auth-wrap">
    <header class="auth-head">
        <h2>Admin Login</h2>
        <p class="muted">Only authorized staff may sign in.</p>
    </header>

    <?= $this->Flash->render() ?>

    <div class="auth-card">
        <?= $this->Form->create(null, ['autocomplete' => 'on']) ?>

        <div class="field">
            <?= $this->Form->label('email', 'Email') ?>
            <?= $this->Form->email('email', [
                'placeholder' => 'you@curdandculture.com',
                'required' => true,
                'autofocus' => true,
                'aria-describedby' => 'emailHelp'
            ]) ?>
            <small id="emailHelp" class="muted">Use your admin account email.</small>
        </div>

        <div class="field pw-field">
            <?= $this->Form->label('password', 'Password') ?>
            <div class="pw-input">
                <?= $this->Form->password('password', [
                    'placeholder' => '••••••••',
                    'required' => true,
                    'id' => 'password'
                ]) ?>
                <button type="button" class="btn small btn-subtle" id="togglePw" aria-pressed="false" aria-controls="password" title="Show/Hide password">Show</button>
            </div>
        </div>

        <div class="actions">
            <?= $this->Form->button(__('Login'), ['class' => 'btn btn-primary']) ?>
            <?= $this->Html->link('Back to site', ['controller' => 'Pages', 'action' => 'display', 'home'], ['class' => 'btn btn-subtle']) ?>
        </div>

        <?= $this->Form->end() ?>
    </div>
</section>

<style>
    /* container */
    .auth-wrap{max-width:520px;margin:0 auto;padding:1.25rem}
    .auth-head h2{margin:.25rem 0}
    .muted{color:#6b7280}

    /* card */
    .auth-card{
        background:#fff;border-radius:1rem;
        box-shadow:0 12px 36px rgba(0,0,0,.08);
        padding:1.1rem 1rem;
    }
    .page.hc .auth-card{background:#0f172a}

    /* fields */
    .field{display:flex;flex-direction:column;gap:.4rem;margin:.6rem 0}
    .field input{
        width:100%;padding:.65rem .8rem;border:1px solid #d1d5db;border-radius:.6rem;background:#f9fafb
    }
    .field input:focus{outline:3px solid rgba(44,123,229,.2);border-color:#9ca3af}

    /* password group */
    .pw-field .pw-input{display:flex;gap:.5rem;align-items:center}
    .pw-field .pw-input .btn{padding:.5rem .65rem}

    /* actions */
    .actions{display:flex;gap:.6rem;flex-wrap:wrap;margin-top:.75rem}

    /* reuse global buttons from layout */
    .btn{display:inline-block;padding:.65rem 1.05rem;border-radius:.6rem;border:1px solid transparent;background:#e5e7eb;color:#111;text-decoration:none}
    .btn:hover{filter:brightness(.98);transform:translateY(-1px);transition:.15s}
    .btn:focus-visible{outline:3px solid rgba(44,123,229,.25);outline-offset:2px}
    .btn-primary{background:#2c7be5;color:#fff}
    .btn-subtle{background:transparent;border-color:#d1d5db;color:#374151}
    .small{font-size:.9rem;padding:.35rem .6rem}

    /* High-contrast adjustments */
    .page.hc .muted{color:#cbd5e1}
    .page.hc .field input{background:#0b1220;color:#e5e7eb;border-color:#334155}
    .page.hc .btn{background:#1f2937;color:#fff;border-color:#475569}
    .page.hc .btn-primary{background:#60a5fa;color:#111}
</style>

<script>
    /* password visibility toggle */
    (function(){
        const btn = document.getElementById('togglePw');
        const input = document.getElementById('password');
        if(btn && input){
            btn.addEventListener('click', () => {
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.textContent = show ? 'Hide' : 'Show';
                btn.setAttribute('aria-pressed', String(show));
                input.focus();
            });
        }
    })();
</script>

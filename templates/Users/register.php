<?php
/**
 * View: Create account
 * - Basic registration form with password strength meter
 * - Password must be ≥8 chars and include uppercase, lowercase and a number
 */
$this->assign('title', 'Create account');
?>
<div class="auth-page">
    <div class="auth-card">
        <header class="auth-head">
            <h1>Create account</h1>
            <p>Join us in seconds</p>
        </header>

        <?= $this->Form->create($user) ?>

        <!-- Name -->
        <div class="form-group">
            <div class="field-block">
                <div class="auth-row">
                    <?= $this->Form->label('name', 'Name', ['class' => 'auth-label']) ?>
                </div>
                <?= $this->Form->control('name', [
                    'label' => false,
                    'autocomplete' => 'name',
                    'placeholder' => 'Your full name',
                    'class' => 'auth-input',
                    'required' => true
                ]) ?>
            </div>
        </div>

        <!-- Email -->
        <div class="form-group">
            <div class="field-block">
                <div class="auth-row">
                    <?= $this->Form->label('email', 'Email', ['class' => 'auth-label']) ?>
                </div>
                <?= $this->Form->control('email', [
                    'label' => false,
                    'autocomplete' => 'email',
                    'placeholder' => 'you@example.com',
                    'class' => 'auth-input'
                ]) ?>
            </div>
        </div>

        <!-- Password with strength meter -->
        <div class="form-group">
            <div class="field-block">
                <div class="auth-row">
                    <?= $this->Form->label('password', 'Password', ['class' => 'auth-label']) ?>
                </div>
                <?= $this->Form->control('password', [
                    'label' => false,
                    'autocomplete' => 'new-password',
                    'placeholder' => '••••••••',
                    'class' => 'auth-input',
                    'minlength' => 8,
                    'pattern' => '(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}',
                    'required' => true,
                    'title' => 'At least 8 characters with uppercase, lowercase and a number',
                    'id' => 'reg_password'
                ]) ?>

                <div class="pw-meter" id="regPwMeter" aria-hidden="true">
                    <div class="pw-meter-bar"></div>
                </div>
                <small class="pw-hint" id="regPwHint"></small>
            </div>
        </div>

        <div class="auth-actions">
            <?= $this->Form->button('Create account', ['class' => 'auth-btn auth-btn-primary']) ?>
        </div>

        <?= $this->Form->end() ?>

        <div class="auth-divider"><span>or</span></div>

        <div class="auth-actions">
            <?= $this->Html->link(
                'Back to sign in',
                ['controller' => 'Users', 'action' => 'login'],
                ['class' => 'auth-btn auth-btn-ghost', 'data-no-transition' => true]
            ) ?>
        </div>
    </div>
</div>

<style>
    .auth-page{
        min-height:calc(100vh - 120px);
        display:grid; place-items:center;
        padding:2.5rem 1rem;
        background:
            radial-gradient(1200px 900px at 10% -10%, rgba(44,123,229,.06), transparent 60%),
            radial-gradient(1000px 700px at 110% 10%, rgba(16,185,129,.05), transparent 55%);
    }
    .theme-dark .auth-page{
        background:
            radial-gradient(1200px 900px at 10% -10%, rgba(96,165,250,.08), transparent 60%),
            radial-gradient(1000px 700px at 110% 10%, rgba(45,212,191,.07), transparent 55%);
    }

    .auth-card{
        width:100%; max-width:440px;
        padding:1.35rem 1.2rem 1.25rem;
        border-radius:1.05rem;
        background:#fff; border:1px solid #eef0f3;
        box-shadow:0 12px 36px rgba(2,6,23,.08);
    }
    .theme-dark .auth-card{background:#111827;border-color:#1f2937;box-shadow:0 16px 48px rgba(0,0,0,.35)}
    .page.hc .auth-card{background:#0f172a;border-color:#334155;box-shadow:none}

    .auth-head h1{margin:0 0 .25rem;font-size:1.45rem;font-weight:800;letter-spacing:.2px}
    .auth-head p{margin:.2rem 0 1.1rem;color:#6b7280}
    .theme-dark .auth-head p{color:#cbd5e1}

    .form-group{margin-bottom:1.05rem}
    .field-block{width:320px;max-width:100%;margin:0 auto}
    .auth-row{display:flex;align-items:center;justify-content:space-between;margin:0 0 .32rem}
    .auth-label{margin:0;font-weight:700;font-size:.92rem;color:#374151}
    .theme-dark .auth-label{color:#e5e7eb}

    /* Inputs */
    .auth-input{
        display:block; width:100%;
        padding:12px 14px; font-size:15px; border-radius:.7rem;
        border:none; outline:none; box-shadow: inset 0 1px 2px rgba(0,0,0,.05);
        background:#f9fafb; color:#111827; transition:all .2s ease-in-out;
    }
    .auth-input::placeholder{color:#9ca3af}
    .auth-input:focus{background:#fff; box-shadow:0 0 0 3px rgba(44,123,229,.28)}
    .theme-dark .auth-input{background:#0f172a;color:#e5e7eb;box-shadow: inset 0 1px 2px rgba(0,0,0,.2)}
    .theme-dark .auth-input:focus{background:#0b1220; box-shadow:0 0 0 3px rgba(96,165,250,.32)}

    /* Buttons */
    .auth-actions{display:flex;justify-content:center;margin-top:.2rem}
    .auth-btn{display:block;width:320px;text-align:center;border-radius:.75rem;padding:.72rem .9rem;font-weight:700;text-decoration:none;cursor:pointer}
    .auth-btn-primary{border:0;background:#2563eb;color:#fff;box-shadow:0 6px 18px rgba(37,99,235,.25);transition:transform .06s,filter .2s,box-shadow .2s}
    .auth-btn-primary:hover{filter:brightness(1.02);box-shadow:0 10px 24px rgba(37,99,235,.28)}
    .auth-btn-primary:active{transform:translateY(1px)}
    .auth-btn-ghost{border:1px solid #e5e7eb;background:#f3f4f6;color:#111}
    .auth-btn-ghost:hover{filter:brightness(.98)}
    .theme-dark .auth-btn-ghost{background:#0f172a;border-color:#334155;color:#e5e7eb}

    /* Divider */
    .auth-divider{display:flex;align-items:center;gap:.6rem;margin:1.05rem 0;color:#9aa3af;font-size:.86rem}
    .auth-divider::before,.auth-divider::after{content:"";flex:1;height:1px;background:#edf0f4}
    .theme-dark .auth-divider{color:#94a3b8}
    .theme-dark .auth-divider::before,.theme-dark .auth-divider::after{background:#334155}

    /* Password strength meter (shared) */
    .pw-meter{height:8px;border-radius:8px;background:#eef2f7;margin:.5rem 0 .25rem;overflow:hidden}
    .theme-dark .pw-meter{background:#1f2937}
    .pw-meter-bar{height:100%;width:0%;transition:width .2s ease-in-out}
    .pw-hint{display:block;margin-top:.15rem;font-size:.85rem;color:#6b7280}
    .theme-dark .pw-hint{color:#94a3b8}
    .pw-weak  {background:#ef4444}
    .pw-fair  {background:#f59e0b}
    .pw-good  {background:#10b981}
    .pw-strong{background:#22c55e}

    @media (max-width:420px){
        .field-block{width:100%}
        .auth-btn{width:100%}
    }
</style>

<script>
    // ===== Password strength (Register page) =====
    (function(){
        const pwd   = document.getElementById('reg_password');
        const meter = document.getElementById('regPwMeter');
        const bar   = meter?.querySelector('.pw-meter-bar');
        const hint  = document.getElementById('regPwHint');
        const form  = document.querySelector('.auth-card form');

        if (!pwd || !meter || !bar || !hint || !form) return;

        function score(s){
            let sc = 0;
            if (s.length >= 8) sc++;
            if (/[a-z]/.test(s)) sc++;
            if (/[A-Z]/.test(s)) sc++;
            if (/\d/.test(s))    sc++;
            return sc;
        }

        function render(){
            const s = pwd.value || '';
            const sc = score(s);
            const pct = [0,25,50,75,100][sc];
            bar.style.width = pct + '%';
            bar.className = 'pw-meter-bar ' + (
                sc <= 1 ? 'pw-weak' : sc === 2 ? 'pw-fair' : sc === 3 ? 'pw-good' : 'pw-strong'
            );
            const txt = sc<=1 ? 'Weak' : sc===2 ? 'Fair' : sc===3 ? 'Good' : 'Strong';
            hint.textContent = 'Strength: ' + txt + ' — must include uppercase, lowercase and a number (min 8).';

            const ok = /(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}/.test(s);
            pwd.setCustomValidity(ok ? '' : 'Password must be at least 8 characters and include uppercase, lowercase, and a number.');
        }

        pwd.addEventListener('input', render);
        render();

        form.addEventListener('submit', function(e){
            render();
            if (!form.checkValidity()) {
                e.preventDefault();
                form.reportValidity();
            }
        });
    })();
</script>

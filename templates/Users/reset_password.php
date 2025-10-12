<?php
/**
 * View: Reset Password
 * - 6-digit OTP with auto-advance
 * - New/Confirm password fields with show/hide
 * - Password strength meter + HTML5 validation
 * - Posts to Users::resetPassword
 *
 * Required view vars:
 *   - string $email
 */
$this->assign('title', 'Reset password');

echo $this->Form->create(
    null,
    ['url' => ['controller' => 'Users', 'action' => 'resetPassword'], 'class' => 'auth-form', 'novalidate' => true]
);
?>

<section class="auth-page rp">
    <div class="auth-card rp-card">
        <header class="auth-head rp-head">
            <div class="auth-emoji" aria-hidden="true">🔑</div>
            <h1>Reset password</h1>
            <p>
                Enter the 6-digit code we sent to
                <strong><?= h($email) ?></strong>, and choose a new password.
            </p>
        </header>

        <!-- Flash messages -->
        <div class="auth-flash">
            <?= $this->Flash->render() ?>
        </div>

        <!-- Keep the email with the form -->
        <?= $this->Form->hidden('email', ['value' => $email]) ?>

        <!-- OTP (6 inputs) -->
        <div class="form-group">
            <label class="auth-label">Verification code</label>

            <!-- Hidden field actually submitted as "code" -->
            <input type="hidden" name="code" id="otp-hidden" value="<?= h($this->request->getData('code') ?? '') ?>">

            <div class="otp-wrap" id="otp-wrap" data-inputs="6">
                <?php
                $prefill = preg_replace('/\D+/', '', (string)($this->request->getData('code') ?? ''));
                for ($i = 0; $i < 6; $i++):
                    $val = $prefill[$i] ?? '';
                    ?>
                    <input
                        class="otp-box"
                        inputmode="numeric"
                        pattern="\d*"
                        maxlength="1"
                        aria-label="Digit <?= $i+1 ?>"
                        value="<?= h($val) ?>"
                    >
                <?php endfor; ?>
            </div>
            <small class="rp-help">The code expires in about 10 minutes.</small>
        </div>

        <!-- New password -->
        <div class="form-group">
            <label class="auth-label" for="password">New password</label>
            <div class="input-with-icon">
                <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M4 11a8 8 0 0 1 16 0v4a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3v-4z" stroke="currentColor" stroke-width="1.5"/>
                    <circle cx="12" cy="15" r="1.25" fill="currentColor"/>
                </svg>
                <?= $this->Form->control('password', [
                    'label' => false,
                    'id' => 'password',
                    'autocomplete' => 'new-password',
                    'placeholder' => '••••••••',
                    'class' => 'auth-input with-icon',
                    // HTML5 validation: ≥8, includes upper + lower + digit
                    'minlength' => 8,
                    'pattern' => '(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}',
                    'required' => true,
                    'title' => 'At least 8 characters with uppercase, lowercase and a number'
                ]) ?>
                <button type="button" class="show-toggle" data-target="#password" aria-label="Show password">👁</button>
            </div>

            <!-- Strength meter -->
            <div class="pw-meter" id="rpPwMeter" aria-hidden="true">
                <div class="pw-meter-bar"></div>
            </div>
            <small class="pw-hint" id="rpPwHint"></small>
        </div>

        <!-- Confirm password -->
        <div class="form-group">
            <label class="auth-label" for="confirm_password">Confirm password</label>
            <div class="input-with-icon">
                <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M4 11a8 8 0 0 1 16 0v4a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3v-4z" stroke="currentColor" stroke-width="1.5"/>
                    <circle cx="12" cy="15" r="1.25" fill="currentColor"/>
                </svg>
                <?= $this->Form->control('confirm_password', [
                    'type' => 'password',
                    'label' => false,
                    'id' => 'confirm_password',
                    'autocomplete' => 'new-password',
                    'placeholder' => '••••••••',
                    'class' => 'auth-input with-icon',
                    'minlength' => 8,
                    'pattern' => '(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}',
                    'required' => true,
                    'title' => 'At least 8 characters with uppercase, lowercase and a number'
                ]) ?>
                <button type="button" class="show-toggle" data-target="#confirm_password" aria-label="Show password">👁</button>
            </div>
        </div>

        <!-- Submit -->
        <div class="auth-actions">
            <?= $this->Form->button('Reset password', ['class' => 'auth-btn auth-btn-primary rp-btn']) ?>
        </div>

        <?= $this->Form->end() ?>

        <div class="rp-back">
            <?= $this->Html->link('← Back to sign in', ['controller' => 'Users', 'action' => 'login'], ['class' => 'auth-link-small']) ?>
        </div>
    </div>
</section>

<style>
    /* ===== Page background & centering ===== */
    .auth-page.rp{
        min-height: 72vh;
        display: flex; align-items: center; justify-content: center;
        padding: 3rem 1rem;
        background:
            radial-gradient(1200px 900px at 10% -10%, rgba(44,123,229,.06), transparent 60%),
            radial-gradient(1000px 700px at 110% 10%, rgba(16,185,129,.05), transparent 55%);
    }
    .theme-dark .auth-page.rp{
        background:
            radial-gradient(1200px 900px at 10% -10%, rgba(96,165,250,.08), transparent 60%),
            radial-gradient(1000px 700px at 110% 10%, rgba(45,212,191,.07), transparent 55%);
    }

    /* ===== Card ===== */
    .auth-card.rp-card{
        width: min(560px, 100%);
        padding: 1.65rem 1.4rem 1.35rem;
        border-radius: 1rem;
        background: #fff;
        border: 1px solid #eef0f3;
        box-shadow: 0 12px 36px rgba(2,6,23,.08);
    }
    .theme-dark .auth-card.rp-card{
        background:#111827;border-color:#1f2937;box-shadow:0 16px 48px rgba(0,0,0,.35)
    }

    /* ===== Header ===== */
    .rp-head{text-align:center;margin-bottom:.9rem}
    .rp-head h1{margin:.2rem 0 .4rem;font-size:1.8rem;font-weight:800;letter-spacing:.2px}
    .rp-head p{margin:.2rem 0 0;color:#6b7280}
    .theme-dark .rp-head p{color:#cbd5e1}

    .auth-emoji{
        width:54px;height:54px;display:inline-grid;place-items:center;margin:0 auto .45rem;
        border-radius:16px;background:#eef2ff;color:#1d4ed8;box-shadow:0 6px 16px rgba(29,78,216,.12);
        font-size:26px;line-height:1
    }
    .theme-dark .auth-emoji{background:#0b1220;color:#93c5fd;border:1px solid #1e293b;box-shadow:none}

    /* ===== Flash ===== */
    .auth-flash :where(.message,.success,.error){
        display:block;width:100%;padding:.6rem .75rem;border-radius:.65rem;font-size:.92rem;margin:.25rem 0 .8rem
    }
    .auth-flash .success{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}
    .auth-flash .error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}
    .theme-dark .auth-flash .success{background:#052e2b;color:#a7f3d0;border-color:#115e59}
    .theme-dark .auth-flash .error{background:#3f1f1f;color:#fecaca;border-color:#7f1d1d}

    /* ===== Inputs ===== */
    .form-group{margin:.95rem 0 1.15rem}
    .auth-label{display:block;font-weight:700;font-size:.95rem;margin:0 0 .35rem;color:#374151}
    .theme-dark .auth-label{color:#e5e7eb}

    .input-with-icon{position:relative}
    .input-icon{position:absolute;left:12px;top:50%;transform:translateY(-50%);opacity:.65}
    .auth-input{
        display:block;width:100%;
        padding:12px 14px;font-size:15px;border-radius:.7rem;border:none;outline:none;
        box-shadow:inset 0 1px 2px rgba(0,0,0,.05);
        background:#f9fafb;color:#111827;transition:all .2s ease-in-out
    }
    .auth-input.with-icon{padding-left:38px}
    .auth-input::placeholder{color:#9ca3af}
    .auth-input:focus{background:#fff;box-shadow:0 0 0 3px rgba(44,123,229,.28)}
    .theme-dark .auth-input{background:#0f172a;color:#e5e7eb;box-shadow: inset 0 1px 2px rgba(0,0,0,.2)}
    .theme-dark .auth-input:focus{background:#0b1220;box-shadow:0 0 0 3px rgba(96,165,250,.32)}

    .show-toggle{
        position:absolute;right:10px;top:50%;transform:translateY(-50%);
        border:0;background:transparent;cursor:pointer;font-size:15px;opacity:.65
    }
    .show-toggle:hover{opacity:.9}

    /* ===== OTP ===== */
    .otp-wrap{display:flex;gap:.5rem}
    .otp-box{
        width:46px;height:46px;border-radius:.7rem;border:1px solid #e5e7eb;
        text-align:center;font-size:18px;outline:none;background:#f9fafb;
        box-shadow:inset 0 1px 2px rgba(0,0,0,.05)
    }
    .otp-box:focus{background:#fff;box-shadow:0 0 0 3px rgba(44,123,229,.28)}
    .theme-dark .otp-box{background:#0f172a;border-color:#334155;color:#e5e7eb}
    .theme-dark .otp-box:focus{background:#0b1220;box-shadow:0 0 0 3px rgba(96,165,250,.32)}

    .rp-help{display:block;margin:.45rem 0 0;color:#6b7280;font-size:.86rem}
    .theme-dark .rp-help{color:#94a3b8}

    /* ===== Actions ===== */
    .auth-actions{margin-top:.5rem;display:flex;justify-content:center}
    .auth-btn{display:block;border-radius:.75rem;padding:.72rem .9rem;font-weight:700;cursor:pointer}
    .auth-btn-primary{
        width:min(320px,100%);border:0;background:#2563eb;color:#fff;
        box-shadow:0 6px 18px rgba(37,99,235,.25);transition:transform .06s,filter .2s,box-shadow .2s
    }
    .auth-btn-primary:hover{filter:brightness(1.02);box-shadow:0 10px 24px rgba(37,99,235,.28)}
    .auth-btn-primary:active{transform:translateY(1px)}

    .rp-back{margin-top:.95rem;text-align:center}

    /* ===== Password strength meter ===== */
    .pw-meter{height:8px;border-radius:8px;background:#eef2f7;margin:.5rem 0 .25rem;overflow:hidden}
    .theme-dark .pw-meter{background:#1f2937}
    .pw-meter-bar{height:100%;width:0%;transition:width .2s ease-in-out}
    .pw-hint{display:block;margin-top:.15rem;font-size:.85rem;color:#6b7280}
    .theme-dark .pw-hint{color:#94a3b8}
    .pw-weak  {background:#ef4444}
    .pw-fair  {background:#f59e0b}
    .pw-good  {background:#10b981}
    .pw-strong{background:#22c55e}
</style>

<script>
    // ===== OTP logic: auto-advance, backspace, paste, and submit as "code" =====
    (function () {
        const wrap = document.getElementById('otp-wrap');
        if (!wrap) return;
        const boxes = Array.from(wrap.querySelectorAll('.otp-box'));
        const hidden = document.getElementById('otp-hidden');

        function updateHidden() {
            hidden.value = boxes.map(b => b.value.replace(/\D/g,'')).join('').slice(0,6);
        }

        boxes.forEach((box, idx) => {
            box.addEventListener('input', () => {
                box.value = box.value.replace(/\D/g,'').slice(0,1);
                updateHidden();
                if (box.value && idx < boxes.length - 1) boxes[idx + 1].focus();
            });
            box.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !box.value && idx > 0) boxes[idx - 1].focus();
                if (e.key === 'ArrowLeft' && idx > 0) boxes[idx - 1].focus();
                if (e.key === 'ArrowRight' && idx < boxes.length - 1) boxes[idx + 1].focus();
            });
            box.addEventListener('paste', (e) => {
                const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g,'').slice(0,6);
                if (!pasted) return;
                e.preventDefault();
                for (let i = 0; i < boxes.length; i++) boxes[i].value = pasted[i] || '';
                updateHidden();
                const next = pasted.length < 6 ? pasted.length : 5;
                boxes[next].focus();
            });
        });

        updateHidden();
    })();

    // ===== Show/hide password toggles =====
    document.querySelectorAll('.show-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = document.querySelector(btn.dataset.target);
            if (!target) return;
            const isPwd = target.getAttribute('type') === 'password';
            target.setAttribute('type', isPwd ? 'text' : 'password');
            btn.textContent = isPwd ? '🙈' : '👁';
        });
    });

    // ===== Password strength + confirm match (Reset page) =====
    (function(){
        const pwd   = document.getElementById('password');
        const cpwd  = document.getElementById('confirm_password');
        const meter = document.getElementById('rpPwMeter');
        const bar   = meter?.querySelector('.pw-meter-bar');
        const hint  = document.getElementById('rpPwHint');
        const form  = document.querySelector('form.auth-form');

        if (!pwd || !meter || !bar || !hint || !form) return;

        function score(s){
            let sc = 0;
            if (s.length >= 8) sc++;
            if (/[a-z]/.test(s)) sc++;
            if (/[A-Z]/.test(s)) sc++;
            if (/\d/.test(s))    sc++;
            return sc; // 0..4
        }

        function render(){
            const s = pwd.value || '';
            const sc = score(s);
            const pct = [0, 25, 50, 75, 100][sc];
            bar.style.width = pct + '%';
            bar.className = 'pw-meter-bar ' + (
                sc <= 1 ? 'pw-weak' : sc === 2 ? 'pw-fair' : sc === 3 ? 'pw-good' : 'pw-strong'
            );
            const txt = sc<=1 ? 'Weak' : sc===2 ? 'Fair' : sc===3 ? 'Good' : 'Strong';
            hint.textContent = 'Strength: ' + txt + ' — must include uppercase, lowercase and a number (min 8).';

            const patternOk = /(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}/.test(s);
            pwd.setCustomValidity(patternOk ? '' : 'Password must be at least 8 characters and include uppercase, lowercase, and a number.');

            if (cpwd.value) {
                const match = s === cpwd.value;
                cpwd.setCustomValidity(match ? '' : 'Passwords do not match.');
            } else {
                cpwd.setCustomValidity('');
            }
        }

        pwd.addEventListener('input', render);
        cpwd.addEventListener('input', render);
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

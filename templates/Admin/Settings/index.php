<?php
/**
 * Admin Settings Index
 * @var \App\View\AppView $this
 * @var array $prefs
 * @var string|null $adminEmail
 */
use Cake\Core\Configure;

$this->assign('title', 'Settings');
$val = fn($k,$d='') => $prefs[$k] ?? $d;

if (!isset($adminEmail) || !$adminEmail) {
    $adminEmail = isset($this->Identity) && $this->Identity->isLoggedIn()
        ? (string)$this->Identity->get('email') : '';
}
$codeTtl = (int)(Configure::read('PasswordReset.code_ttl_minutes') ?? 10);
?>

<div class="admin-settings">
    <div class="page-header">
        <div class="page-header-content">
            <h1 class="page-title">Settings</h1>
            <p class="page-subtitle">Tune how the site looks and how we contact you</p>
        </div>
    </div>

    <div class="settings-card">

        <?= $this->Form->create(null, ['id' => 'prefs-form']) ?>

        <div class="settings-grid">
            <!-- Appearance -->
            <fieldset class="group">
                <legend class="group__title">Appearance</legend>

                <div class="field">
                    <?= $this->Form->label('theme', 'Theme', ['class'=>'label']) ?>
                    <?= $this->Form->select('theme', [
                        'auto'=>'Auto','light'=>'Light','dark'=>'Dark',
                    ], ['value'=>$val('theme','auto'), 'class'=>'input']) ?>
                    <p class="hint">Use Auto to follow your system preference.</p>
                </div>

                <div class="field">
                    <?= $this->Form->label('contrast', 'Contrast', ['class'=>'label']) ?>
                    <?= $this->Form->select('contrast', [
                        'normal'=>'Normal','high'=>'High contrast',
                    ], ['value'=>$val('contrast','normal'), 'class'=>'input']) ?>
                </div>

                <div class="field">
                    <label class="label">Font size <span id="font-val" class="mono"></span></label>
                    <input type="range" name="font_scale" class="range" min="0.9" max="1.25" step="0.05"
                           value="<?= h($val('font_scale','1.0')) ?>">
                    <p class="hint">Adjust overall text size. We'll remember this on this device.</p>
                </div>
            </fieldset>

            <!-- Language -->
            <fieldset class="group">
                <legend class="group__title">Language</legend>
                <div class="field">
                    <?= $this->Form->label('language', 'Display language', ['class'=>'label']) ?>
                    <?= $this->Form->select('language', [
                        'en'=>'English','zh'=>'中文','ja'=>'日本語',
                    ], ['value'=>$val('language','en'), 'class'=>'input']) ?>
                    <p class="hint">Some content may not be available in all languages.</p>
                </div>
            </fieldset>

            <!-- Notifications -->
            <fieldset class="group">
                <legend class="group__title">Notifications & Privacy</legend>

                <label class="switch">
                    <input type="checkbox" name="email_optin" <?= $val('email_optin', true) ? 'checked' : '' ?>>
                    <span class="slider" aria-hidden="true"></span>
                    <span class="switch__label">Email me about updates and replies</span>
                </label>

                <label class="switch">
                    <input type="checkbox" name="cookie_consent" <?= $val('cookie_consent', false) ? 'checked' : '' ?>>
                    <span class="slider" aria-hidden="true"></span>
                    <span class="switch__label">I accept the use of cookies for preferences</span>
                </label>
            </fieldset>


            <fieldset class="group group--security">
                <legend class="group__title">Security</legend>

                <div class="sec-box">
                    <div class="sec-icon" aria-hidden="true">🔒</div>
                    <div class="sec-content">
                        <h3 class="sec-title">Reset your admin password</h3>
                        <p class="sec-text">
                            We can email a one-time 6-digit verification code to
                            <strong><?= h($adminEmail ?: 'your admin email') ?></strong>.
                            Use it on the reset page to choose a new password.
                            The code expires in about <strong><?= $codeTtl ?></strong> minutes.
                        </p>

                        <div class="sec-actions">

                            <button type="submit" class="btn btn-primary" form="sec-form">
                                Send reset code
                            </button>


                            <?= $this->Html->link(
                                'Open reset page',
                                ['prefix' => false, 'controller' => 'Users', 'action' => 'resetPassword', '?' => ['email' => $adminEmail]],
                                ['class' => 'btn btn-ghost']
                            ) ?>
                        </div>
                    </div>
                </div>
            </fieldset>
        </div>

        <div class="actions-row">
            <?= $this->Form->button('Save preferences', ['class'=>'btn btn-primary']) ?>
        </div>

        <?= $this->Form->end() ?>
    </div>
</div>


<?= $this->Form->create(null, ['id' => 'sec-form']) ?>
<?= $this->Form->hidden('action', ['value' => 'request_reset']) ?>
<?= $this->Form->end() ?>

<style>
    /* Layout */
    .admin-settings{max-width:1000px;margin:0 auto;padding:1.25rem 1rem}
    .page-header{margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:1px solid #e5e7eb}
    .page-title{font-size:1.6rem;font-weight:700;margin:0}
    .page-subtitle{color:#6b7280;margin:.25rem 0 0}
    .settings-card{background:#fff;border:1px solid #eef0f3;border-radius:.6rem;padding:1.25rem}
    .settings-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:1rem}

    /* Groups */
    .group{border:1px solid #eef0f3;border-radius:.6rem;padding:1rem;background:#f9fafb}
    .group__title{margin:0 0 .8rem;font-size:1rem;font-weight:600;color:#111827}

    /* Fields */
    .field{display:flex;flex-direction:column;gap:.4rem;margin-bottom:.8rem}
    .field:last-child{margin-bottom:0}
    .label{font-weight:600;color:#374151}


    .input{
        appearance:none;
        border:1px solid #d1d5db;background:#fff;border-radius:.4rem;
        padding:.6rem;color:#111827
    }
    .input:focus{outline:2px solid #2563eb;outline-offset:2px}


    select.input{appearance:auto;-webkit-appearance:auto;-moz-appearance:auto;background:#fff;padding-right:.85rem}


    .hint{color:#6b7280;font-size:.85rem;margin:0}
    .mono{font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;color:#6b7280;margin-left:.3rem}

    /* Range */
    .range{width:100%;height:4px;border-radius:999px;background:#e5e7eb;outline:none}
    .range::-webkit-slider-thumb{appearance:none;height:18px;width:18px;border-radius:50%;background:#2563eb;border:0;margin-top:-7px}
    .range::-webkit-slider-runnable-track{height:4px;border-radius:999px;background:#e5e7eb}
    .range::-moz-range-thumb{height:18px;width:18px;border-radius:50%;background:#2563eb;border:0}
    .range::-moz-range-track{height:4px;border-radius:999px;background:#e5e7eb}

    /* Switch */
    .switch{position:relative;display:flex;align-items:center;gap:.6rem;margin:.4rem 0;cursor:pointer}
    .switch input{position:absolute;opacity:0}
    .switch .slider{position:relative;flex:0 0 44px;height:24px;background:#d1d5db;border-radius:999px;transition:.2s}
    .switch .slider::after{content:"";position:absolute;top:2px;left:2px;width:20px;height:20px;background:#fff;border-radius:50%;box-shadow:0 1px 3px rgba(0,0,0,.3);transition:.2s}
    .switch input:checked + .slider{background:#2563eb}
    .switch input:checked + .slider::after{transform:translateX(20px)}
    .switch__label{user-select:none;color:#374151}

    /* Buttons */
    .actions-row{margin-top:1.5rem;display:flex;gap:.6rem}
    .btn{display:inline-block;padding:.6rem 1rem;border-radius:.4rem;border:1px solid #d1d5db;text-decoration:none;color:#111;background:#fff;cursor:pointer}
    .btn-primary{background:#2563eb;color:#fff;border-color:#2563eb}
    .btn-ghost{background:#fff;color:#111;border:1px solid #d1d5db}

    /* Security card */
    .group--security{background:#f8fafc}
    .sec-box{display:flex;gap:1rem;align-items:flex-start;padding:1rem;border:1px dashed #e5e7eb;border-radius:.75rem;background:#fff}
    .sec-icon{flex:0 0 52px;height:52px;width:52px;display:grid;place-items:center;font-size:24px;border-radius:16px;background:#eef2ff;color:#1d4ed8;box-shadow:0 6px 16px rgba(29,78,216,.12)}
    .sec-title{margin:.1rem 0 .25rem;font-size:1.05rem;font-weight:700}
    .sec-text{margin:.15rem 0 .6rem;color:#4b5563}
    .sec-actions{display:flex;flex-wrap:wrap;gap:.5rem}

    @media (max-width:768px){
        .settings-grid{grid-template-columns:1fr}
        .sec-box{flex-direction:column}
        .sec-icon{width:44px;height:44px;font-size:20px}
    }
</style>

<script>
    (function(){
        const slider = document.querySelector('input[name="font_scale"]');
        const label = document.getElementById('font-val');
        if(!slider || !label) return;
        const apply = v => label.textContent = '(' + (parseFloat(v||1)).toFixed(2) + '×)';
        apply(slider.value);
        slider.addEventListener('input', e => apply(e.target.value));
    })();
</script>

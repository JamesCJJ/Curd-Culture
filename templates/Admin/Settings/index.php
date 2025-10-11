<?php
/**
 * Admin Settings (2x2 layout)
 * @var \App\View\AppView $this
 * @var array  $prefs
 * @var string $adminEmail
 */
use Cake\Core\Configure;

$this->assign('title', 'Settings');
$val = fn($k,$d='') => $prefs[$k] ?? $d;

if (empty($adminEmail)) {
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

        <!-- 2×2 固定网格 -->
        <div class="settings-grid grid-2x2">

            <!-- Security -->
            <fieldset class="tile">
                <legend class="tile__title">
                    <span class="tile__icon" aria-hidden="true">🔒</span>
                    Security
                </legend>

                <div class="sec">
                    <h3 class="sec__heading">Reset your admin password</h3>
                    <p class="sec__text">
                        We can email a one-time 6-digit verification code to
                        <strong><?= h($adminEmail ?: 'your admin email') ?></strong>.
                        Use it on the reset page to choose a new password.
                        The code expires in about <strong><?= $codeTtl ?></strong> minutes.
                    </p>

                    <div class="sec__actions">
                        <!-- 独立表单提交（不影响偏好保存表单） -->
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
            </fieldset>

            <!-- Appearance -->
            <fieldset class="tile">
                <legend class="tile__title">
                    <span class="tile__icon" aria-hidden="true">🎨</span>
                    Appearance
                </legend>

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
            <fieldset class="tile">
                <legend class="tile__title">
                    <span class="tile__icon" aria-hidden="true">🌐</span>
                    Language
                </legend>

                <div class="field">
                    <?= $this->Form->label('language', 'Display language', ['class'=>'label']) ?>
                    <?= $this->Form->select('language', [
                        'en'=>'English','zh'=>'中文','ja'=>'日本語',
                    ], ['value'=>$val('language','en'), 'class'=>'input']) ?>
                    <p class="hint">Some content may not be available in all languages.</p>
                </div>
            </fieldset>

            <!-- Notifications -->
            <fieldset class="tile">
                <legend class="tile__title">
                    <span class="tile__icon" aria-hidden="true">🔔</span>
                    Notifications & Privacy
                </legend>

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
        </div>

        <div class="actions-row">
            <?= $this->Form->button('Save preferences', ['class'=>'btn btn-primary']) ?>
        </div>

        <?= $this->Form->end() ?>
    </div>
</div>

<!-- 独立的“发送重置码”表单（与偏好表单分离） -->
<?= $this->Form->create(null, ['id' => 'sec-form']) ?>
<?= $this->Form->hidden('action', ['value' => 'request_reset']) ?>
<?= $this->Form->end() ?>

<style>
    /* ===== Layout ===== */
    .admin-settings{max-width:1100px;margin:0 auto;padding:1.25rem 1rem}
    .page-header{margin-bottom:1.25rem;padding-bottom:1rem;border-bottom:1px solid #e5e7eb}
    .page-title{font-size:1.6rem;font-weight:700;margin:0}
    .page-subtitle{color:#6b7280;margin:.25rem 0 0}

    .settings-card{
        background:#fff;border:1px solid #eef0f3;border-radius:12px;padding:1.25rem;
        box-shadow:0 8px 24px rgba(2,6,23,.06);
    }

    /* 2×2 固定栅格 */
    .settings-grid.grid-2x2{
        display:grid;
        grid-template-columns:repeat(2, minmax(0, 1fr));
        gap:1.1rem;
    }
    @media (max-width: 900px){
        .settings-grid.grid-2x2{ grid-template-columns: 1fr; }
    }

    /* ===== Tile (统一卡片视觉) ===== */
    .tile{
        background:#f9fafb;border:1px solid #eef0f3;border-radius:12px;
        padding:0.9rem 0.9rem 1rem; display:flex; flex-direction:column; min-height:270px;
    }
    .tile__title{
        display:flex; align-items:center; gap:.5rem;
        margin:0 0 .8rem; font-size:1rem; font-weight:700; color:#0f172a;
    }
    .tile__icon{
        width:34px;height:34px;display:grid;place-items:center;
        border-radius:10px; background:#eef2ff; color:#1d4ed8; box-shadow:0 4px 12px rgba(29,78,216,.15);
    }

    /* ===== Fields ===== */
    .field{display:flex;flex-direction:column;gap:.45rem;margin-bottom:.85rem}
    .field:last-child{margin-bottom:0}
    .label{font-weight:700;color:#111827}
    .input{
        appearance:none;border:1px solid #d1d5db;background:#fff;border-radius:.55rem;
        padding:.62rem .7rem;color:#111827;transition:border .15s ease, box-shadow .15s ease;
    }
    .input:focus{outline:0; border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.25)}
    select.input{appearance:auto;-webkit-appearance:auto;-moz-appearance:auto;background:#fff}

    /* Range */
    .mono{font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;color:#6b7280;margin-left:.3rem}
    .range{width:100%;height:4px;border-radius:999px;background:#e5e7eb;outline:none}
    .range::-webkit-slider-thumb{appearance:none;height:18px;width:18px;border-radius:50%;background:#2563eb;border:0;margin-top:-7px}
    .range::-webkit-slider-runnable-track{height:4px;border-radius:999px;background:#e5e7eb}
    .range::-moz-range-thumb{height:18px;width:18px;border-radius:50%;background:#2563eb;border:0}
    .range::-moz-range-track{height:4px;border-radius:999px;background:#e5e7eb}

    .hint{color:#6b7280;font-size:.86rem;margin:0}

    /* Switch */
    .switch{position:relative;display:flex;align-items:center;gap:.6rem;margin:.45rem 0;cursor:pointer}
    .switch input{position:absolute;opacity:0}
    .switch .slider{position:relative;flex:0 0 48px;height:26px;background:#d1d5db;border-radius:999px;transition:.2s}
    .switch .slider::after{content:"";position:absolute;top:3px;left:3px;width:20px;height:20px;background:#fff;border-radius:50%;box-shadow:0 1px 3px rgba(0,0,0,.3);transition:.2s}
    .switch input:checked + .slider{background:#2563eb}
    .switch input:checked + .slider::after{transform:translateX(22px)}
    .switch__label{user-select:none;color:#374151}

    /* Buttons */
    .actions-row{margin-top:1.1rem;display:flex;gap:.6rem}
    .btn{
        display:inline-block;padding:.6rem 1rem;border-radius:.55rem;border:1px solid #d1d5db;
        text-decoration:none;color:#111;background:#fff;cursor:pointer;transition:all .15s ease;
    }
    .btn:hover{box-shadow:0 2px 10px rgba(2,6,23,.08)}
    .btn-primary{background:#2563eb;color:#fff;border-color:#2563eb}
    .btn-primary:hover{filter:brightness(0.98)}
    .btn-ghost{background:#fff;color:#111;border:1px solid #d1d5db}

    /* Security content */
    .sec{display:flex;flex-direction:column;gap:.65rem}
    .sec__heading{margin:.1rem 0 0;font-size:2.5rem;font-weight:700}
    .sec__text{margin:2px;color:#4b5563;font-size:1.8rem;line-height:1.6}
    .sec__actions{display:flex;flex-wrap:wrap;gap:.6rem;margin-top:3rem}
</style>

<script>
    (function(){
        const slider = document.querySelector('input[name="font_scale"]');
        const label  = document.getElementById('font-val');
        if(!slider || !label) return;
        const apply = v => label.textContent = '(' + (parseFloat(v||1)).toFixed(2) + '×)';
        apply(slider.value);
        slider.addEventListener('input', e => apply(e.target.value));
    })();
</script>

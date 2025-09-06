<?php
$this->assign('title', 'Settings');
$val = fn($k,$d='') => $prefs[$k] ?? $d;
?>
<div class="dash">

    <div class="settings-hero">
        <h1>Your Settings</h1>
        <p class="muted">Tune how the site looks and how we contact you.</p>
    </div>

    <div class="card settings-card">
        <?= $this->Form->create(null) ?>

        <section class="settings-grid">

            <!-- Appearance -->
            <fieldset class="group">
                <legend class="group__title">Appearance</legend>
                <div class="field">
                    <?= $this->Form->label('theme', 'Theme', ['class'=>'label']) ?>
                    <?= $this->Form->select('theme', [
                        'auto'  => 'Auto',
                        'light' => 'Light',
                        'dark'  => 'Dark',
                    ], ['value'=>$val('theme','auto'), 'class'=>'input']) ?>
                    <p class="hint">Use Auto to follow your system preference.</p>
                </div>

                <div class="field">
                    <?= $this->Form->label('contrast', 'Contrast', ['class'=>'label']) ?>
                    <?= $this->Form->select('contrast', [
                        'normal' => 'Normal',
                        'high'   => 'High contrast',
                    ], ['value'=>$val('contrast','normal'), 'class'=>'input']) ?>
                </div>

                <div class="field">
                    <label class="label">Font size <span id="font-val" class="mono"></span></label>
                    <input type="range" name="font_scale" class="range"
                           min="0.9" max="1.25" step="0.05" value="<?= h($val('font_scale','1.0')) ?>">
                    <p class="hint">Adjust overall text size. We’ll remember this on this device.</p>
                </div>
            </fieldset>

            <!-- Language -->
            <fieldset class="group">
                <legend class="group__title">Language</legend>
                <div class="field">
                    <?= $this->Form->label('language', 'Display language', ['class'=>'label']) ?>
                    <?= $this->Form->select('language', [
                        'en'=>'English',
                        'zh'=>'中文',
                        'ja'=>'日本語',
                    ], ['value'=>$val('language','english'), 'class'=>'input']) ?>
                    <p class="hint">Some content may not be available in all languages.</p>
                </div>
            </fieldset>

            <!-- Notifications & Privacy -->
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

        </section>

        <div class="actions-row">
            <?= $this->Form->button('Save preferences', ['class'=>'btn btn-primary']) ?>
            <?= $this->Html->link('Cancel', ['controller'=>'Pages','action'=>'display','home'], ['class'=>'btn']) ?>
        </div>

        <?= $this->Form->end() ?>
    </div>
</div>

<style>
    /* Layout */
    .settings-hero{margin:0 0 .6rem}
    .settings-hero h1{margin:0 0 .25rem;font-size:1.6rem}
    .settings-card{padding:1.1rem}
    .settings-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:1rem}
    @media (max-width: 880px){ .settings-grid{grid-template-columns:1fr} }

    /* Groups */
    .group{border:1px solid #eef0f3;border-radius:.8rem;padding:.9rem;background:#fff}
    .group__title{margin:0 0 .6rem;font-size:1rem;font-weight:700}

    /* Fields */
    .field{display:flex;flex-direction:column;gap:.35rem}
    .label{font-weight:600}
    .input{appearance:none;border:1px solid #e5e7eb;background:#f7f8fb;border-radius:.6rem;padding:.5rem .6rem}
    .input:focus{outline:3px solid rgba(44,123,229,.25);outline-offset:2px}
    .hint{color:#6b7280;font-size:.9rem;margin:0}
    .mono{font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;color:#6b7280;margin-left:.3rem}

    /* Slider (range) */
    .range{width:100%}
    .range::-webkit-slider-thumb{appearance:none;height:16px;width:16px;border-radius:50%;background:#2c7be5;border:0;margin-top:-6px}
    .range::-webkit-slider-runnable-track{height:4px;border-radius:999px;background:#dbeafe}
    .range::-moz-range-thumb{height:16px;width:16px;border-radius:50%;background:#2c7be5;border:0}
    .range::-moz-range-track{height:4px;border-radius:999px;background:#dbeafe}

    /* Switches */
    .switch{position:relative;display:flex;align-items:center;gap:.6rem;margin:.25rem 0}
    .switch input{position:absolute;opacity:0}
    .switch .slider{position:relative;flex:0 0 42px;height:24px;background:#e5e7eb;border-radius:999px;transition:.2s}
    .switch .slider::after{content:"";position:absolute;top:3px;left:3px;width:18px;height:18px;background:#fff;border-radius:50%;box-shadow:0 1px 2px rgba(0,0,0,.2);transition:.2s}
    .switch input:checked + .slider{background:#2c7be5}
    .switch input:checked + .slider::after{transform:translateX(18px)}
    .switch__label{user-select:none}

    /* Actions */
    .actions-row{margin-top:1rem;display:flex;gap:.5rem}

    /* High contrast */
    .page.hc .group{background:#0f172a;border-color:#334155}
    .page.hc .input{background:#0f172a;border-color:#334155;color:#e5e7eb}
    .page.hc .hint,.page.hc .mono{color:#cbd5e1}
    .page.hc .range::-webkit-slider-runnable-track,
    .page.hc .range::-moz-range-track{background:#233044}
    .page.hc .switch .slider{background:#334155}
</style>

<script>
    // Live font-size preview + numeric label
    (function(){
        const slider = document.querySelector('input[name="font_scale"]');
        const label = document.getElementById('font-val');
        if(!slider || !label) return;

        const base = 16;
        const apply = v => {
            const s = parseFloat(v || 1);
            document.documentElement.style.fontSize = (base * s) + 'px';
            label.textContent = '(' + s.toFixed(2) + '×)';
        };

        apply(slider.value);
        slider.addEventListener('input', e => apply(e.target.value));
    })();
</script>

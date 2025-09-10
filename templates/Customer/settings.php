<?php
/**
 * Customer Settings
 * @var \App\View\AppView $this
 * @var array $prefs
 */
$this->extend('/layout/customer');
$this->assign('title', 'Settings');
$val = fn($k,$d='') => $prefs[$k] ?? $d;
?>

<h2><i class="bi bi-gear me-2"></i>Settings</h2>

<div class="row">
    <div class="col-md-12">
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            Customize your experience by adjusting your preferences below. Changes are saved automatically to this device.
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?= $this->Form->create(null) ?>

        <div class="row">
            <!-- Appearance -->
            <div class="col-lg-6 mb-4">
                <div class="settings-group">
                    <h5 class="settings-group-title"><i class="bi bi-palette me-2"></i>Appearance</h5>
                    
                    <div class="mb-3">
                        <?= $this->Form->label('theme', 'Theme', ['class'=>'form-label fw-bold']) ?>
                        <?= $this->Form->select('theme', [
                            'auto'  => 'Auto (Follow system)',
                            'light' => 'Light mode',
                            'dark'  => 'Dark mode',
                        ], ['value'=>$val('theme','auto'), 'class'=>'form-select']) ?>
                        <div class="form-text">Auto will follow your device's theme preference.</div>
                    </div>

                    <div class="mb-3">
                        <?= $this->Form->label('contrast', 'Contrast', ['class'=>'form-label fw-bold']) ?>
                        <?= $this->Form->select('contrast', [
                            'normal' => 'Normal',
                            'high'   => 'High contrast',
                        ], ['value'=>$val('contrast','normal'), 'class'=>'form-select']) ?>
                        <div class="form-text">High contrast improves readability.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Font size <span id="font-val" class="text-muted ms-1"></span></label>
                        <input type="range" name="font_scale" class="form-range"
                               min="0.9" max="1.25" step="0.05" value="<?= h($val('font_scale','1.0')) ?>">
                        <div class="form-text">Adjust text size for better readability.</div>
                    </div>
                </div>
            </div>

            <!-- Language & Communication -->
            <div class="col-lg-6 mb-4">
                <div class="settings-group">
                    <h5 class="settings-group-title"><i class="bi bi-globe me-2"></i>Language & Communication</h5>
                    
                    <div class="mb-3">
                        <?= $this->Form->label('language', 'Display language', ['class'=>'form-label fw-bold']) ?>
                        <?= $this->Form->select('language', [
                            'en'=>'English',
                            'zh'=>'中文',
                            'ja'=>'日本語',
                        ], ['value'=>$val('language','en'), 'class'=>'form-select']) ?>
                        <div class="form-text">Some content may not be available in all languages.</div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="email_optin" id="email_optin" <?= $val('email_optin', true) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold" for="email_optin">
                                Email notifications
                            </label>
                            <div class="form-text">Receive updates about your orders and account.</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="cookie_consent" id="cookie_consent" <?= $val('cookie_consent', false) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold" for="cookie_consent">
                                Cookie consent
                            </label>
                            <div class="form-text">Allow cookies for preferences and analytics.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
            <div class="text-muted">
                <small><i class="bi bi-info-circle me-1"></i>Settings are saved to this device only</small>
            </div>
            <div>
                <?= $this->Form->button('Save Preferences', [
                    'class'=>'btn btn-primary'
                ]) ?>
            </div>
        </div>

        <?= $this->Form->end() ?>
    </div>
</div>

<style>
.settings-group {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1.25rem;
    height: 100%;
}

.settings-group-title {
    color: #495057;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e9ecef;
}

.form-range {
    margin: 0.5rem 0;
}

.form-check-label {
    cursor: pointer;
}

.form-text {
    font-size: 0.875rem;
    color: #6c757d;
}

@media (max-width: 768px) {
    .settings-group {
        margin-bottom: 1rem;
    }
}
</style>

<script>
// Live font-size preview + numeric label
(function(){
    const slider = document.querySelector('input[name="font_scale"]');
    const label = document.getElementById('font-val');
    if(!slider || !label) return;

    const apply = v => {
        const s = parseFloat(v || 1);
        // Only update the label, don't change the actual font size until form is submitted
        label.textContent = '(' + s.toFixed(2) + '×)';
    };

    apply(slider.value);
    slider.addEventListener('input', e => apply(e.target.value));
})();
</script>

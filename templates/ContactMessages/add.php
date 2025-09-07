<?php
/**
 * Contact form
 * @var \App\Model\Entity\ContactMessage $contact
 */
use Cake\Core\Configure;

$this->assign('title', 'Contact Us');
$siteKey = h(Configure::read('Security.recaptcha.site_key'));
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
        <?= $this->Form->create($contact, ['novalidate' => true, 'type' => 'post']) ?>
        <div class="grid">
            <div class="field">
                <?= $this->Form->label('name', 'Name') ?>
                <?= $this->Form->text('name', [
                    'placeholder' => 'Your name',
                    'required'    => true,
                    'class'       => !empty($contact->getErrors()['name']) ? 'error' : ''
                ]) ?>
                <?php if (!empty($contact->getErrors()['name'])): ?>
                    <div class="field-error">
                        <span class="error-icon">⚠</span>
                        <?= h($contact->getErrors()['name'][0]) ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="field">
                <?= $this->Form->label('email', 'Email') ?>
                <?= $this->Form->email('email', [
                    'placeholder' => 'you@example.com',
                    'required'    => true,
                    'class'       => !empty($contact->getErrors()['email']) ? 'error' : ''
                ]) ?>
                <?php if (!empty($contact->getErrors()['email'])): ?>
                    <div class="field-error">
                        <span class="error-icon">⚠</span>
                        <?= h($contact->getErrors()['email'][0]) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="field">
            <?= $this->Form->label('message', 'Message') ?>
            <?= $this->Form->textarea('message', [
                'rows'       => 6,
                'placeholder'=> 'How can we help?',
                'required'   => true,
                'class'      => !empty($contact->getErrors()['message']) ? 'error' : ''
            ]) ?>
            <?php if (!empty($contact->getErrors()['message'])): ?>
                <div class="field-error">
                    <span class="error-icon">⚠</span>
                    <?= h($contact->getErrors()['message'][0]) ?>
                </div>
            <?php endif; ?>
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

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.querySelector('input[type="email"]');
            const form = document.querySelector('form');
            
            // Email validation function
            function validateEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }
            
            // Show field error
            function showFieldError(field, message) {
                // Remove existing error
                const existingError = field.parentElement.querySelector('.field-error');
                if (existingError) {
                    existingError.remove();
                }
                
                // Add error class to field
                field.classList.add('error');
                
                // Create error message
                const errorDiv = document.createElement('div');
                errorDiv.className = 'field-error';
                errorDiv.innerHTML = `<span class="error-icon">⚠</span>${message}`;
                
                // Insert after field
                field.parentElement.insertBefore(errorDiv, field.nextSibling);
            }
            
            // Remove field error
            function removeFieldError(field) {
                field.classList.remove('error');
                const existingError = field.parentElement.querySelector('.field-error');
                if (existingError) {
                    existingError.remove();
                }
            }
            
            // Real-time email validation
            if (emailInput) {
                emailInput.addEventListener('blur', function() {
                    const email = this.value.trim();
                    if (email && !validateEmail(email)) {
                        showFieldError(this, 'Please enter a valid email address (e.g., user@example.com)');
                    } else if (email) {
                        removeFieldError(this);
                    }
                });
                
                emailInput.addEventListener('input', function() {
                    const email = this.value.trim();
                    if (email && validateEmail(email)) {
                        removeFieldError(this);
                    }
                });
            }
            
            // Form submission validation
            if (form) {
                form.addEventListener('submit', function(e) {
                    let hasErrors = false;
                    
                    // Validate email
                    const email = emailInput.value.trim();
                    if (!email) {
                        showFieldError(emailInput, 'Please enter your email address');
                        hasErrors = true;
                    } else if (!validateEmail(email)) {
                        showFieldError(emailInput, 'Please enter a valid email address (e.g., user@example.com)');
                        hasErrors = true;
                    }
                    
                    // Validate name
                    const nameInput = document.querySelector('input[name="name"]');
                    if (nameInput && !nameInput.value.trim()) {
                        showFieldError(nameInput, 'Please enter your name');
                        hasErrors = true;
                    }
                    
                    // Validate message
                    const messageInput = document.querySelector('textarea[name="message"]');
                    if (messageInput && !messageInput.value.trim()) {
                        showFieldError(messageInput, 'Please enter your message');
                        hasErrors = true;
                    }
                    
                    if (hasErrors) {
                        e.preventDefault();
                        // Scroll to first error
                        const firstError = form.querySelector('.field-error');
                        if (firstError) {
                            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }
                });
            }
        });
        </script>

        <div class="actions">
            <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
            <?= $this->Form->reset(__('Reset'),  ['class' => 'btn']) ?>
            <?= $this->Html->link(__('Back'), 'javascript:history.back()', ['class' => 'btn btn-subtle']) ?>
        </div>
        <?= $this->Form->end() ?>
    </div>
</section>

<style>
    .cm-wrap{max-width:780px;margin:0 auto;padding:1rem}
    .cm-head h2{margin:.25rem 0}
    .muted{color:#6b7280}
    .cm-card{background:#fff;border-radius:1rem;box-shadow:0 10px 30px rgba(0,0,0,.06);padding:1.25rem}
    .page.hc .cm-card{background:#0f172a}
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
    @media(max-width:700px){.grid{grid-template-columns:1fr}}
    .field{display:flex;flex-direction:column;gap:.35rem;margin:.5rem 0}
    .field input,.field textarea{width:100%;padding:.65rem .8rem;border:1px solid #d1d5db;border-radius:.6rem;background:#f9fafb;transition:border-color 0.2s ease, box-shadow 0.2s ease}
    .field input:focus,.field textarea:focus{outline:3px solid rgba(44,123,229,.2);border-color:#9ca3af}
    .field input.error,.field textarea.error{border-color:#dc2626;background:#fef2f2;box-shadow:0 0 0 3px rgba(220,38,38,0.1)}
    .field-error{display:flex;align-items:center;gap:.5rem;color:#dc2626;font-size:.875rem;margin-top:.25rem;padding:.5rem .75rem;background:#fef2f2;border:1px solid #fecaca;border-radius:.5rem}
    .error-icon{font-size:1rem;flex-shrink:0}
    .actions{display:flex;gap:.6rem;flex-wrap:wrap;margin-top:1rem}
    /* High-contrast */
    .page.hc .cm-card{background:#0f172a;color:#f1f5f9}
    .page.hc .cm-captcha{border-color:#334155}
    .page.hc .field input,.page.hc .field textarea,.page.hc .captcha-row input{background:#0b1220;color:#e5e7eb;border-color:#334155}
    .page.hc .field input::placeholder,.page.hc .field textarea::placeholder{color:#9aa3ae}
    .page.hc .field input.error,.page.hc .field textarea.error{border-color:#f87171;background:#1f1b1b}
    .page.hc .field-error{color:#f87171;background:#1f1b1b;border-color:#7f1d1d}
    .page.hc .btn{background:#1f2937;color:#fff;border-color:#475569}
    .page.hc .btn-primary{background:#60a5fa;color:#111}
</style>

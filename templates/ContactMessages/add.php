<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ContactMessage $contact
 * @var int $a
 * @var int $b
 */
?>
<div class="row">
    <div class="column-responsive column-80" style="max-width:680px;margin:0 auto;">
        <h2>Contact Us</h2>
        <?= $this->Flash->render() ?>
        <?= $this->Form->create($contact) ?>
        <?= $this->Form->control('name') ?>
        <?= $this->Form->control('email') ?>
        <?= $this->Form->control('message', ['type' => 'textarea', 'rows' => 6]) ?>

        <fieldset style="margin-top:1rem;padding:1rem;border:1px solid #ddd;border-radius:.5rem;">
            <legend>Captcha</legend>
            <p>Please answer: <strong><?= h($a) ?> + <?= h($b) ?> = ?</strong></p>
            <?= $this->Form->control('captcha', ['label' => 'Your answer']) ?>
        </fieldset>
        <div style="display:flex; gap:.75rem; margin-top:1rem;">
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->reset(__('Reset')) ?>
            <?= $this->Form->button(__('Back'), [
                'type' => 'button',
                'onclick' => 'window.history.back();',
                'formnovalidate' => true
            ]) ?>
        </div>



        <?= $this->Form->end() ?>
    </div>
</div>
<style>
@media (max-width:600px){
    .column-responsive{ padding: 0 1rem; }
}
</style>
